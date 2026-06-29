<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({ site: Object })
const s = props.site
const cart = ref(0)
const subscribed = ref(false)
const menuOpen = ref(false)
const waLink = `https://wa.me/${s.built_by_whatsapp}`
</script>

<template>
  <Head :title="`${s.business} — ${s.tagline}`" />
  <div class="page" :style="{ '--primary': s.theme.primary, '--accent': s.theme.accent }">
    <header class="nav">
      <div class="wrap nav__inner">
        <a href="#top" class="brand">{{ s.business }}</a>
        <nav class="nav__links" :class="{ open: menuOpen }">
          <a v-for="n in s.nav" :key="n" href="#" @click="menuOpen=false">{{ n }}</a>
        </nav>
        <div class="nav__actions">
          <button class="cart">🛍️ {{ cart }}</button>
          <button class="burger" @click="menuOpen=!menuOpen">☰</button>
        </div>
      </div>
    </header>

    <section id="top" class="hero">
      <div class="wrap hero__inner">
        <div>
          <p class="eyebrow">{{ s.hero.eyebrow }}</p>
          <h1>{{ s.hero.title }}</h1>
          <p class="lead">{{ s.hero.subtitle }}</p>
          <a href="#prod" class="btn">{{ s.hero.cta }}</a>
          <div class="stats">
            <div v-for="st in s.stats" :key="st.label"><strong>{{ st.value }}</strong><span>{{ st.label }}</span></div>
          </div>
        </div>
        <div class="hero__photo" :style="{ backgroundImage: `url(${s.hero.image})` }"></div>
      </div>
    </section>

    <section class="strip"><div class="wrap strip__inner"><span v-for="t in s.strip" :key="t">{{ t }}</span></div></section>

    <section id="prod" class="section">
      <div class="wrap">
        <h2 class="center">{{ s.products_title }}</h2>
        <div class="grid">
          <article v-for="p in s.products" :key="p.seed" class="card">
            <div class="card__img" :style="{ backgroundImage: `url(https://picsum.photos/seed/${p.seed}/400/520)` }">
              <span v-if="p.tag" class="tag">{{ p.tag }}</span>
            </div>
            <div class="card__body">
              <h3>{{ p.name }}</h3>
              <div class="price">{{ p.price }}</div>
              <button class="add" @click="cart++">Agregar 🛍️</button>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="section alt">
      <div class="wrap about">
        <div class="about__img" :style="{ backgroundImage: `url(${s.about.image})` }"></div>
        <div>
          <h2>{{ s.about.title }}</h2>
          <p class="muted">{{ s.about.text }}</p>
          <ul class="ticks"><li v-for="pt in s.about.points" :key="pt">{{ pt }}</li></ul>
        </div>
      </div>
    </section>

    <section class="news">
      <div class="wrap">
        <h2>{{ s.contact.title }}</h2>
        <p>{{ s.contact.text }}</p>
        <form v-if="!subscribed" @submit.prevent="subscribed=true" class="news__form">
          <input type="email" placeholder="tu@correo.com" required>
          <button class="btn light">Suscribirme</button>
        </form>
        <p v-else class="ok">¡Listo! Bienvenido al club. 🎉</p>
      </div>
    </section>

    <footer class="footer">
      <div class="wrap">
        <div class="brand light">{{ s.business }}</div>
        <p>{{ s.tagline }} · Hecho en México 🇲🇽</p>
        <p class="credit">
          Desarrollado por <a :href="waLink" target="_blank" rel="noopener">Overcloud</a> · ¿Quieres tu sitio?
          <a :href="waLink" target="_blank" rel="noopener">Escríbenos por WhatsApp</a>
        </p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
*{box-sizing:border-box}
.page{font-family:'Poppins',system-ui,Avenir,Helvetica,Arial,sans-serif;color:#171327;line-height:1.55;--primary:#7c3aed;--accent:#db2777}
.wrap{width:min(1140px,92%);margin:0 auto}
h1,h2{font-family:Georgia,'Times New Roman',serif;line-height:1.1;margin:0}
a{text-decoration:none;color:inherit}
.center{text-align:center;margin-bottom:38px;font-size:34px}
.btn{display:inline-block;background:var(--primary);color:#fff;padding:13px 28px;border-radius:999px;font-weight:600;border:none;cursor:pointer;transition:.2s}
.btn:hover{transform:translateY(-2px);filter:brightness(1.05)}
.btn.light{background:#fff;color:var(--primary)}
.nav{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.9);backdrop-filter:blur(10px);border-bottom:1px solid #ece9f5}
.nav__inner{display:flex;align-items:center;justify-content:space-between;height:68px}
.brand{font-family:Georgia,serif;font-weight:700;font-size:23px;background:linear-gradient(90deg,var(--primary),var(--accent));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.brand.light{-webkit-text-fill-color:#fff;background:none;color:#fff}
.nav__links{display:flex;gap:28px}.nav__links a{font-weight:500;color:#3b3550}.nav__links a:hover{color:var(--primary)}
.nav__actions{display:flex;align-items:center;gap:10px}
.cart{background:#f6f4fb;border:none;border-radius:999px;padding:9px 15px;font-weight:600;cursor:pointer}
.burger{display:none;background:none;border:none;font-size:22px;cursor:pointer}
.hero{background:linear-gradient(180deg,#faf8ff,#fff)}
.hero__inner{display:grid;grid-template-columns:1.05fr .95fr;gap:30px;align-items:center;padding:60px 0}
.eyebrow{color:var(--primary);font-weight:600;text-transform:uppercase;letter-spacing:1px;font-size:13px}
.hero h1{font-size:56px;margin:12px 0 16px}
.lead{color:#6b7280;font-size:18px;max-width:440px;margin-bottom:24px}
.stats{display:flex;gap:32px;margin-top:34px}.stats strong{display:block;font-size:23px;font-family:Georgia,serif}.stats span{color:#6b7280;font-size:13px}
.hero__photo{height:430px;border-radius:24px;background-size:cover;background-position:center;box-shadow:0 30px 60px rgba(124,58,237,.25)}
.strip{background:#171327;color:#fff}.strip__inner{display:flex;flex-wrap:wrap;gap:22px;justify-content:space-between;padding:15px 0;font-size:14px;font-weight:500}
.section{padding:70px 0}.section.alt{background:#f6f4fb}
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:22px}
.card{background:#fff;border:1px solid #ece9f5;border-radius:16px;overflow:hidden;transition:.25s}.card:hover{transform:translateY(-6px);box-shadow:0 20px 40px rgba(23,19,39,.1)}
.card__img{aspect-ratio:4/5;background-size:cover;background-position:center;position:relative}
.tag{position:absolute;top:12px;left:12px;background:#fff;color:var(--primary);font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px}
.card__body{padding:15px}.card__body h3{font-size:15px;margin:0 0 4px}.price{color:var(--primary);font-weight:700}
.add{margin-top:11px;width:100%;border:1px solid #ece9f5;background:#fff;padding:10px;border-radius:10px;font-weight:600;cursor:pointer;transition:.2s}.add:hover{background:var(--primary);color:#fff;border-color:var(--primary)}
.about{display:grid;grid-template-columns:1fr 1fr;gap:46px;align-items:center}
.about__img{height:370px;border-radius:24px;background-size:cover;background-position:center;box-shadow:0 24px 50px rgba(23,19,39,.14)}
.about h2{font-size:32px;margin-bottom:14px}.muted{color:#6b7280;margin-bottom:16px}
.ticks{list-style:none;padding:0;display:grid;gap:9px}.ticks li{padding-left:28px;position:relative;font-weight:500}.ticks li::before{content:'✓';position:absolute;left:0;width:19px;height:19px;border-radius:50%;background:var(--primary);color:#fff;font-size:12px;display:flex;align-items:center;justify-content:center}
.news{background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;text-align:center;padding:68px 0}
.news h2{font-size:32px;margin-bottom:8px}.news p{opacity:.92;margin-bottom:22px}
.news__form{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}.news__form input{padding:14px 18px;border-radius:999px;border:none;min-width:270px}
.ok{font-weight:600;margin-top:14px}
.footer{background:#171327;color:#cfc9e0;padding:46px 0 30px;text-align:center}
.footer p{margin:8px 0;font-size:14px;opacity:.85}
.footer .credit a{color:#fff;font-weight:600;text-decoration:underline}
@media(max-width:860px){
  .nav__links{position:fixed;inset:68px 0 auto 0;background:#fff;flex-direction:column;padding:16px 6%;gap:14px;border-bottom:1px solid #ece9f5;transform:translateY(-130%);transition:.3s}
  .nav__links.open{transform:translateY(0)}.burger{display:block}
  .hero__inner{grid-template-columns:1fr;text-align:center}.hero h1{font-size:42px}.lead,.stats{margin-inline:auto}.stats{justify-content:center}
  .hero__photo{height:320px;max-width:380px;margin:0 auto}
  .grid{grid-template-columns:repeat(2,1fr)}.about{grid-template-columns:1fr}
}
</style>
