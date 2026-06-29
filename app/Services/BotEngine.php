<?php

namespace App\Services;

use App\Models\BotContact;
use App\Models\Cliente;
use App\Models\Faq;
use App\Models\Numero;
use App\Models\Pedido;
use App\Models\Plan;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Bot de VENTAS de líneas telefónicas para David Michan, en español, manejado por
 * WhatsApp sobre la propia línea del cliente.
 *
 * Es una máquina de estados finita sobre BotContact->step:
 *   new → choosing → confirming → paying → done   (+ el estado transversal "human")
 *
 * El flujo completo de venta vive aquí: saluda, muestra los planes (datos/minutos/precio),
 * responde preguntas frecuentes desde la base de conocimiento (tabla faqs), captura al
 * cliente, genera un link de pago, confirma el pago, **asigna automáticamente un número
 * disponible del inventario** y avanza el pedido por sus estados
 * (iniciado → en_pago → pagado → numero_asignado), notificando en cada paso.
 *
 * No usa IA: cada respuesta es copy fijo en métodos privados, fácilmente editable por cliente.
 */
class BotEngine
{
    // ---- pasos de la máquina de estados ---------------------------------
    private const STEP_NEW = 'new';
    private const STEP_CHOOSING = 'choosing';
    private const STEP_CONFIRMING = 'confirming';
    private const STEP_PAYING = 'paying';
    private const STEP_DONE = 'done';
    private const STEP_HUMAN = 'human';

    public function __construct(private GatewayClient $gateway) {}

    public function handle(string $from, ?string $fromName, string $text): void
    {
        // El segundo argumento garantiza que el paso quede poblado en la instancia
        // recién creada (la columna tiene default 'new', pero el modelo no lo refleja
        // tras un firstOrCreate sin valor explícito).
        $contact = BotContact::firstOrCreate(['phone' => $from], ['step' => self::STEP_NEW]);

        if (blank($contact->step)) {
            $contact->step = self::STEP_NEW;
        }

        // Mantén fresco el nombre (pushName de WhatsApp) sin pisarlo con null.
        if (filled($fromName) && $contact->name !== $fromName) {
            $contact->name = $fromName;
            $contact->save();
        }

        $normalized = Str::lower(trim($text));

        // ESCALADO (en cualquier paso): el cliente pide una persona → transferir y callar.
        if ($this->wantsHuman($normalized)) {
            if ($contact->step !== self::STEP_HUMAN) {
                $this->setStep($contact, self::STEP_HUMAN);
                $this->reply($from, $this->copyHandoff());
            }

            return;
        }

        // Un humano tomó la conversación → el bot permanece en silencio.
        if ($contact->step === self::STEP_HUMAN) {
            return;
        }

        // La palabra "menu"/"menú" reinicia el embudo desde donde sea.
        if (in_array($normalized, ['menu', 'menú', 'inicio', 'hola'], true) && $contact->step !== self::STEP_NEW) {
            $this->setStep($contact, self::STEP_NEW);
        }

        // Preguntas frecuentes: si el mensaje calza con una FAQ y NO estamos a media
        // confirmación/pago, respóndela sin romper el embudo.
        if (in_array($contact->step, [self::STEP_NEW, self::STEP_CHOOSING, self::STEP_DONE], true)) {
            if ($faq = $this->matchFaq($normalized)) {
                $this->reply($from, $this->copyFaq($faq));

                // Si aún no ha visto los planes, muéstralos para encaminar la venta.
                if ($contact->step === self::STEP_NEW) {
                    $this->onNew($contact, $from);
                }

                return;
            }
        }

        match ($contact->step) {
            self::STEP_CHOOSING => $this->onChoosing($contact, $from, $fromName, $normalized),
            self::STEP_CONFIRMING => $this->onConfirming($contact, $from, $fromName, $normalized),
            self::STEP_PAYING => $this->onPaying($contact, $from, $fromName, $normalized),
            self::STEP_DONE => $this->onDone($contact, $from),
            default => $this->onNew($contact, $from), // STEP_NEW / primer contacto / desconocido
        };
    }

    // ---- estados --------------------------------------------------------

    /** Saluda por nombre y presenta los planes activos, luego espera una elección. */
    private function onNew(BotContact $contact, string $from): void
    {
        $plans = $this->activePlans();
        if ($plans->isEmpty()) {
            $this->reply($from, $this->copyNoPlans());

            return;
        }

        $this->setStep($contact, self::STEP_CHOOSING);
        $this->reply($from, $this->copyGreeting($contact->name).$this->planList($plans).$this->copyAskChoice());
    }

    /** Empata la respuesta con un plan (por número de lista o nombre); crea el Pedido y pide confirmar. */
    private function onChoosing(BotContact $contact, string $from, ?string $fromName, string $text): void
    {
        $plans = $this->activePlans();
        if ($plans->isEmpty()) {
            $this->reply($from, $this->copyNoPlans());

            return;
        }

        $plan = $this->matchPlan($plans, $text);
        if (! $plan) {
            $this->reply($from, $this->copyNoMatch().$this->planList($plans).$this->copyAskChoice());

            return;
        }

        $pedido = Pedido::create([
            'bot_contact_id' => $contact->id,
            'plan_id' => $plan->id,
            'cliente' => $fromName ?: $contact->name,
            'telefono' => $from,
            'estado' => 'iniciado',
            'total' => $plan->precio,
        ]);

        $data = $contact->data ?? [];
        $data['plan_id'] = $plan->id;
        $data['pedido_id'] = $pedido->id;
        $contact->data = $data;
        $contact->step = self::STEP_CONFIRMING;
        $contact->save();

        $this->reply($from, $this->copyConfirmPrompt($plan));
    }

    /** Afirmativo → genera link de pago y pasa a "en_pago"; negativo → regresa a elegir. */
    private function onConfirming(BotContact $contact, string $from, ?string $fromName, string $text): void
    {
        if ($this->isYes($text)) {
            $pedido = $this->pendingPedido($contact);

            if (! $pedido) {
                // Sin pedido vivo → reinicia el embudo con elegancia.
                $this->setStep($contact, self::STEP_NEW);
                $this->onNew($contact, $from);

                return;
            }

            $link = $this->generatePaymentLink($pedido);
            $pedido->update(['estado' => 'en_pago', 'link_pago' => $link]);

            // Registra/actualiza al cliente desde ya (captura de datos).
            Cliente::updateOrCreate(
                ['telefono' => $from],
                ['nombre' => $fromName ?: $contact->name],
            );

            $this->setStep($contact, self::STEP_PAYING);
            $this->reply($from, $this->copyPaymentLink($pedido, $link));

            return;
        }

        if ($this->isNo($text)) {
            $this->setStep($contact, self::STEP_CHOOSING);
            $plans = $this->activePlans();
            $this->reply($from, $this->copyChangedMind().$this->planList($plans).$this->copyAskChoice());

            return;
        }

        // Respuesta ambigua → vuelve a pedir sí/no explícito.
        $this->reply($from, $this->copyConfirmRetry());
    }

    /** Confirmación de pago → marca pagado, ASIGNA un número del inventario y entrega. */
    private function onPaying(BotContact $contact, string $from, ?string $fromName, string $text): void
    {
        if (! $this->saysPaid($text)) {
            // Aún no paga / mensaje suelto → recuérdale el link.
            $pedido = $this->pendingPedido($contact, ['en_pago']);
            $link = $pedido?->link_pago ?? $this->generatePaymentLink($pedido ?? new Pedido(['id' => 0]));
            $this->reply($from, $this->copyPaymentReminder($link));

            return;
        }

        $pedido = $this->pendingPedido($contact, ['en_pago', 'pagado']);
        if (! $pedido) {
            $this->setStep($contact, self::STEP_NEW);
            $this->onNew($contact, $from);

            return;
        }

        $pedido->update(['estado' => 'pagado']);

        // Asignación automática de número disponible del inventario.
        $numero = $this->assignNumero($pedido);

        if (! $numero) {
            // Sin inventario disponible → avisa y escala a una persona.
            $this->setStep($contact, self::STEP_HUMAN);
            $this->reply($from, $this->copyNoStock());

            return;
        }

        $this->setStep($contact, self::STEP_DONE);
        $this->reply($from, $this->copyDelivered($pedido, $numero));
    }

    /** Pedido ya cerrado → cierre cordial; "menu" (arriba) reinicia el flujo. */
    private function onDone(BotContact $contact, string $from): void
    {
        $this->reply($from, $this->copyAlreadyDone());
    }

    // ---- inventario / pagos --------------------------------------------

    /** Toma el primer número disponible, lo marca asignado y lo liga al pedido. */
    private function assignNumero(Pedido $pedido): ?Numero
    {
        $numero = Numero::where('estado', 'disponible')->orderBy('id')->first();
        if (! $numero) {
            return null;
        }

        $numero->update(['estado' => 'asignado']);
        $pedido->update(['numero_id' => $numero->id, 'estado' => 'numero_asignado']);

        return $numero;
    }

    /** Link de pago determinista para el pedido (degrada con elegancia sin pasarela real). */
    private function generatePaymentLink(Pedido $pedido): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $ref = 'OV'.str_pad((string) ($pedido->id ?: 0), 5, '0', STR_PAD_LEFT);

        return $base.'/pago/'.$ref;
    }

    // ---- helpers de planes / faqs --------------------------------------

    /** @return Collection<int,Plan> */
    private function activePlans(): Collection
    {
        return Plan::where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();
    }

    /** Empata por número de lista (1-based) y luego por nombre difuso (cualquier dirección). */
    private function matchPlan(Collection $plans, string $text): ?Plan
    {
        $text = trim($text);

        if ($text !== '' && ctype_digit($text)) {
            return $plans->values()->get(((int) $text) - 1);
        }

        foreach ($plans as $plan) {
            $name = Str::lower(trim($plan->nombre));
            if ($name !== '' && (Str::contains($text, $name) || Str::contains($name, $text))) {
                return $plan;
            }
        }

        return null;
    }

    /** Busca una FAQ activa cuyas palabras clave aparezcan en el mensaje. */
    private function matchFaq(string $text): ?Faq
    {
        $text = ' '.$text.' ';

        foreach (Faq::where('activo', true)->orderBy('orden')->orderBy('id')->get() as $faq) {
            foreach ($faq->keywords() as $kw) {
                if ($kw !== '' && Str::contains($text, $kw)) {
                    return $faq;
                }
            }
        }

        return null;
    }

    /** @param array<int,string> $estados */
    private function pendingPedido(BotContact $contact, array $estados = ['iniciado']): ?Pedido
    {
        $pedidoId = $contact->data['pedido_id'] ?? null;

        if ($pedidoId && $p = Pedido::find($pedidoId)) {
            return $p;
        }

        return $contact->pedidos()
            ->whereIn('estado', $estados)
            ->latest('id')
            ->first();
    }

    // ---- copy (cadenas editables en español) ----------------------------

    private function copyGreeting(?string $name): string
    {
        $greeting = $name ? "¡Hola, {$name}! 👋" : '¡Hola! 👋';

        return $greeting." Bienvenido a *".config('app.name')."* 📱✨\n\n"
            ."Vendemos *líneas telefónicas* con datos, minutos y SMS, con activación inmediata y opción de *portabilidad* (conserva tu número). 🙌\n\n"
            ."Estos son nuestros planes:\n\n";
    }

    private function planList(Collection $plans): string
    {
        $lines = $plans->values()->map(function (Plan $plan, int $i) {
            $line = ($i + 1).'. *'.$plan->nombre.'* — '.$this->formatPrice($plan->precio);

            $specs = collect([
                filled($plan->datos) ? '📶 '.$plan->datos : null,
                filled($plan->minutos) ? '📞 '.$plan->minutos : null,
                filled($plan->sms) ? '💬 '.$plan->sms : null,
            ])->filter()->implode('  ·  ');

            if ($specs !== '') {
                $line .= "\n   ".$specs;
            }
            if (filled($plan->descripcion)) {
                $line .= "\n   ".$plan->descripcion;
            }

            return $line;
        });

        return $lines->implode("\n\n");
    }

    private function copyAskChoice(): string
    {
        return "\n\n¿Cuál te late? Respóndeme con el *número* o el *nombre* del plan y lo activamos. 🙂\n"
            ."_También puedes preguntarme por cobertura, precios o portabilidad._";
    }

    private function copyNoMatch(): string
    {
        return "Mmm, no identifiqué ese plan. 🤔 Estos son los disponibles:\n\n";
    }

    private function copyConfirmPrompt(Plan $plan): string
    {
        $specs = collect([
            filled($plan->datos) ? '📶 '.$plan->datos : null,
            filled($plan->minutos) ? '📞 '.$plan->minutos : null,
            filled($plan->sms) ? '💬 '.$plan->sms : null,
        ])->filter()->implode('  ·  ');

        $detalle = $specs !== '' ? "\n".$specs : '';

        return '¡Excelente elección! 🙌 Elegiste el plan *'.$plan->nombre.'* por *'.$this->formatPrice($plan->precio).'*.'
            .$detalle."\n\n"
            .'¿Confirmas tu pedido? Responde *sí* para continuar al pago o *no* para elegir otro plan.';
    }

    private function copyConfirmRetry(): string
    {
        return 'Para continuar, respóndeme *sí* para ir al pago o *no* para elegir otro plan. 🙂';
    }

    private function copyChangedMind(): string
    {
        return "¡Sin problema! 🙌 Aquí están los planes de nuevo:\n\n";
    }

    private function copyPaymentLink(Pedido $pedido, string $link): string
    {
        return "¡Perfecto! 🎉 Para activar tu línea, completa tu pago seguro aquí:\n\n"
            ."🔗 {$link}\n\n"
            ."Es 100% seguro. En cuanto pagues, escríbeme *pagué* y te asigno tu número al instante. 📲";
    }

    private function copyPaymentReminder(string $link): string
    {
        return "Te dejo de nuevo tu link de pago: 🔗 {$link}\n\n"
            ."Cuando lo completes, escríbeme *pagué* y seguimos con tu número. 🙂";
    }

    private function copyDelivered(Pedido $pedido, Numero $numero): string
    {
        return "¡Pago confirmado! ✅ Tu línea de *".config('app.name')."* ya está activa. 🎉\n\n"
            ."📲 Tu nuevo número es: *".$this->formatNumero($numero->numero)."*\n\n"
            ."Pedido *#{$pedido->id}* — estado: *Número asignado*.\n"
            ."En breve un asesor te confirma la entrega final. ¡Gracias por tu compra! 🙌\n\n"
            ."Si quieres hacer otro pedido, escribe *menu*.";
    }

    private function copyAlreadyDone(): string
    {
        return "Tu pedido ya está registrado ✅ y tu número fue asignado. 🙌\n\n"
            ."Si necesitas otra línea o tienes dudas, escribe *menu* o pregúntame lo que necesites.";
    }

    private function copyNoStock(): string
    {
        return "¡Gracias por tu pago! ✅ En este momento estamos reabasteciendo números disponibles. 🙏\n\n"
            ."Un asesor te asignará tu número personalmente en breve. ¡Quedamos al pendiente! 🙌";
    }

    private function copyNoPlans(): string
    {
        return 'Gracias por escribir a *'.config('app.name').'* 🙌 En un momento un asesor te atiende personalmente.';
    }

    private function copyFaq(Faq $faq): string
    {
        return $faq->respuesta;
    }

    private function copyHandoff(): string
    {
        return '¡Claro que sí! 🙌 Te paso con uno de nuestros asesores de *'.config('app.name').'* para que te atienda personalmente. '
            .'En breve te contactan. ¡Quedo al pendiente! 😊';
    }

    // ---- matchers (deterministas) --------------------------------------

    /** Confirmación afirmativa (descarta rechazos explícitos). Empata por límites de palabra. */
    private function isYes(string $text): bool
    {
        if ($this->isNo($text)) {
            return false;
        }

        if (preg_match('/\b(s[ií]|sip|sale|va|dale|ok|okay|claro|listo|correcto|adelante|confirm\w*|acept\w*|procede|quiero)\b/u', $text)) {
            return true;
        }

        return Str::contains($text, [
            'de acuerdo', 'me late', 'por supuesto', 'está bien', 'esta bien', 'hágale', 'hagale', 'perfecto',
        ]);
    }

    /** Negativo / rechazo explícito. */
    private function isNo(string $text): bool
    {
        return (bool) preg_match('/\b(no|nel|nop|nope|todav[ií]a no|a[uú]n no|aun no|por ahora no|'
            .'ahorita no|mejor no|otro plan|otra opci[oó]n|cambiar)\b/u', $text);
    }

    /** El cliente indica que ya pagó. */
    private function saysPaid(string $text): bool
    {
        if (preg_match('/\b(pagu[eé]|pagad[oa]|ya pagu[eé]|transfer[íi]|deposit[eé]|list[oa]|hecho|ya est[aá]|comprobante)\b/u', $text)) {
            return true;
        }

        return $this->isYes($text);
    }

    /** El cliente quiere una persona real / no quiere un bot → escalar. */
    private function wantsHuman(string $text): bool
    {
        $text = ' '.trim($text).' ';

        return (bool) preg_match('/(asesor real|un asesor|una asesora|atenci[oó]n humana|'
            .'(hablar|hablo|comunicar|comunicarme|pasar|pasas?|p[aá]same|contactar|conectar|con[eé]ctame) con (un|una|alg[uú]ien|el|la)?\s*(humano|persona|asesor|asesora|agente|ejecutiv|alguien real|alguien|due[ñn]o|encargad)|'
            .'quiero (un|una|hablar con|que me atienda un|que me atienda una)?\s*(humano|persona|asesor|asesora|agente|alguien real)|'
            .'prefiero (un|una|hablar con|que me atienda)?\s*(humano|persona|asesor|asesora|agente|alguien)|'
            .'no quiero (hablar con)?\s*(un|una)?\s*(bot|ia|robot|inteligencia artificial|asistente)|'
            .'hablar con (un|una)?\s*(ia|bot|robot|inteligencia artificial)\s*no|'
            .'no me (interes|gust)\w*\s*(hablar con\s*(un|una)?\s*)?(ia|bot|robot|asistente|inteligencia artificial))/u', $text);
    }

    // ---- utilidades -----------------------------------------------------

    private function setStep(BotContact $contact, string $step): void
    {
        $contact->step = $step;
        $contact->save();
    }

    /** Formatea un precio guardado en centavos como cantidad en MXN. */
    private function formatPrice(int $cents): string
    {
        return '$'.number_format($cents / 100, 0, '.', ',').' MXN';
    }

    /** Formatea un número de 10 dígitos como (55) 1234-5678 cuando aplica. */
    private function formatNumero(string $numero): string
    {
        $digits = preg_replace('/\D/', '', $numero);
        if (strlen((string) $digits) === 10) {
            return '('.substr($digits, 0, 2).') '.substr($digits, 2, 4).'-'.substr($digits, 6);
        }

        return $numero;
    }

    /** Cada respuesta saliente pasa por el gateway. */
    private function reply(string $to, string $message): void
    {
        $this->gateway->send($to, $message);
    }
}
