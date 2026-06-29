// Prueba E2E real (Chromium headless) contra la app servida localmente.
// Inicia sesión, crea un registro por la INTERFAZ en cada módulo, verifica que
// aparece en la tabla y que persiste tras recargar, y prueba el webhook entrante
// del bot. Sale con código 0 sólo si todo pasa.

// Los navegadores de Playwright se instalan dentro de node_modules
// (PLAYWRIGHT_BROWSERS_PATH=0) porque ~/.cache no es escribible en este entorno.
// Lo fijamos ANTES de importar playwright para que resuelva el binario correcto.
if (!process.env.PLAYWRIGHT_BROWSERS_PATH) {
    process.env.PLAYWRIGHT_BROWSERS_PATH = '0';
}
const { chromium } = await import('playwright');

const BASE = process.env.E2E_BASE || 'http://127.0.0.1:8123';
const EMAIL = 'david-michan@overcloud.us';
const PASSWORD = '3WfHBI4dMjKR';
const GATEWAY_TOKEN = process.env.GATEWAY_TOKEN || 'change-me';

const stamp = Date.now().toString().slice(-6);
let browser;

function fail(msg) {
    console.error(`\n❌ FALLO: ${msg}`);
    process.exitCode = 1;
    throw new Error(msg);
}

async function assertVisible(page, text, context) {
    const count = await page.locator(`text=${text}`).first().count();
    if (count === 0) fail(`No se encontró "${text}" (${context}).`);
}

async function run() {
    browser = await chromium.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-dev-shm-usage', '--disable-gpu', '--single-process', '--no-zygote'],
    });
    const page = await browser.newPage();
    page.setDefaultTimeout(15000);

    // --- 1) LOGIN ---------------------------------------------------------
    console.log('→ Iniciando sesión…');
    await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input#email', EMAIL);
    await page.fill('input#password', PASSWORD);
    await page.click('button:has-text("Iniciar sesión")');
    await page.waitForURL('**/dashboard', { timeout: 15000 }).catch(() => fail('No llegó al dashboard tras el login.'));
    await assertVisible(page, 'David Michan', 'dashboard');
    console.log('  ✓ Sesión iniciada y "David Michan" visible en el dashboard.');

    // --- 2) CRUD por módulo via UI ---------------------------------------
    const modulos = [
        {
            nombre: 'Planes', path: '/planes', boton: 'Nuevo plan', guardar: 'Guardar plan',
            valor: `Plan E2E ${stamp}`,
            llenar: async (p, valor) => {
                await p.getByPlaceholder('Línea Plus').fill(valor);
                await p.getByPlaceholder('249').fill('299');
            },
        },
        {
            nombre: 'Números', path: '/numeros', boton: 'Agregar número', guardar: 'Guardar número',
            valor: `55${stamp}0001`,
            llenar: async (p, valor) => {
                await p.getByPlaceholder('5512345678').fill(valor);
                await p.getByPlaceholder('55', { exact: true }).fill('55');
            },
        },
        {
            nombre: 'Clientes', path: '/clientes', boton: 'Nuevo cliente', guardar: 'Guardar cliente',
            valor: `Cliente E2E ${stamp}`,
            llenar: async (p, valor) => {
                await p.getByPlaceholder('Nombre del cliente').fill(valor);
                await p.getByPlaceholder('5215512345678').fill(`52155${stamp}11`);
            },
        },
        {
            nombre: 'Preguntas frecuentes', path: '/faqs', boton: 'Nueva pregunta', guardar: 'Guardar pregunta',
            valor: `¿Pregunta E2E ${stamp}?`,
            llenar: async (p, valor) => {
                await p.getByPlaceholder('¿Tienen cobertura en mi zona?').fill(valor);
                await p.getByPlaceholder('Respuesta que enviará el bot…').fill('Respuesta automática de prueba.');
                await p.getByPlaceholder('cobertura, señal, alcance').fill('prueba e2e');
            },
        },
        {
            nombre: 'Pedidos', path: '/pedidos', boton: 'Nuevo pedido', guardar: 'Guardar pedido',
            valor: `Pedido E2E ${stamp}`,
            llenar: async (p, valor) => {
                await p.getByPlaceholder('Nombre del cliente').fill(valor);
                await p.getByPlaceholder('5215512345678').fill(`52155${stamp}22`);
            },
        },
    ];

    for (const m of modulos) {
        console.log(`→ Módulo ${m.nombre}: creando registro por la UI…`);
        await page.goto(`${BASE}${m.path}`, { waitUntil: 'domcontentloaded' });

        // Usar el botón de crear de la interfaz.
        await page.getByRole('link', { name: m.boton }).first().click();
        await page.waitForURL(`**${m.path}/create`, { timeout: 15000 })
            .catch(() => fail(`No abrió el formulario de crear de ${m.nombre}.`));

        await m.llenar(page, m.valor);
        await page.click(`button:has-text("${m.guardar}")`);

        // Regresa al listado.
        await page.waitForURL(`**${m.path}`, { timeout: 15000 })
            .catch(() => fail(`No regresó al listado de ${m.nombre} tras guardar.`));
        await assertVisible(page, m.valor, `${m.nombre} — listado tras guardar`);
        console.log(`  ✓ "${m.valor}" aparece en el listado.`);

        // Persistencia: recargar y volver a verificar.
        await page.reload({ waitUntil: 'domcontentloaded' });
        await assertVisible(page, m.valor, `${m.nombre} — tras recargar`);
        console.log('  ✓ Persiste tras recargar.');
    }

    // --- 3) Anti-genérico: nada de "Laravel" ------------------------------
    console.log('→ Verificando que no haya marca genérica…');
    await page.goto(`${BASE}/dashboard`, { waitUntil: 'domcontentloaded' });
    const html = await page.content();
    if (/Laravel/i.test(html)) fail('Apareció "Laravel" en el dashboard.');
    if (/You.?re logged in/i.test(html)) fail('Apareció "You\'re logged in" en el dashboard.');
    console.log('  ✓ Sin marca genérica de Laravel.');

    // --- 4) Webhook del bot: un mensaje entrante genera respuesta ---------
    console.log('→ Probando webhook /api/wa/inbound…');
    const phone = `52155${stamp}99`;
    const res = await fetch(`${BASE}/api/wa/inbound`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'x-gateway-token': GATEWAY_TOKEN },
        body: JSON.stringify({ from: phone, fromName: 'Cliente E2E', text: 'hola', isGroup: false }),
    });
    if (res.status !== 200) fail(`El webhook respondió ${res.status}.`);
    const body = await res.json();
    if (!body.ok) fail('El webhook no respondió ok:true.');

    // El bot debió crear/avanzar la conversación → verificarlo en el panel.
    await page.goto(`${BASE}/conversaciones`, { waitUntil: 'domcontentloaded' });
    await assertVisible(page, phone, 'conversaciones tras webhook');
    await assertVisible(page, 'Eligiendo plan', 'estado del bot tras saludo');
    console.log('  ✓ El mensaje entrante generó respuesta del bot (conversación en "Eligiendo plan").');

    // Token inválido debe ser rechazado.
    const bad = await fetch(`${BASE}/api/wa/inbound`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'x-gateway-token': 'token-malo' },
        body: JSON.stringify({ from: '5210000000000', text: 'hola' }),
    });
    if (bad.status !== 401) fail(`El webhook aceptó un token inválido (status ${bad.status}).`);
    console.log('  ✓ El webhook rechaza tokens inválidos (401).');

    console.log('\n✅ TODO EN VERDE: login, CRUD por módulo, persistencia y webhook del bot.');
}

run()
    .then(async () => { await browser?.close(); process.exit(process.exitCode || 0); })
    .catch(async (e) => { console.error(e.message); await browser?.close(); process.exit(1); });
