<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { authTheme, antdLocale } from '@/theme';
import PublicNav from '@/Components/PublicNav.vue';

defineProps({
    title: { type: String, default: 'ISSFAM · SAINS' },
});

const carreras = computed(() => usePage().props.carrerasOferta ?? []);
</script>

<template>
    <a-config-provider :theme="authTheme" :locale="antdLocale">
        <Head :title="title" />

        <PublicNav />

        <div class="au">
            <!-- Panel de marca -->
            <aside class="au-brand">
                <span class="au-brand__arc" aria-hidden="true"></span>
                <span class="au-brand__ring" aria-hidden="true"></span>

                <div class="au-brand__inner">
                    <div class="au-org">
                        <img src="/images/bachillerato-nacional-sm.png" alt="Bachillerato Nacional SAINS" class="au-org__logo" />
                    </div>

                    <p class="au-brand__lead">
                        Una <b>prestación de servicio estratégico</b> para personal y beneficiarios de la
                        <b>Guardia Nacional</b>, que les permite acceder a <b>Educación Media Superior</b>.
                    </p>
                    <p class="au-brand__sub">
                        Esta oferta educativa consiste en un <b>Bachillerato Tecnológico con Formación Profesional</b>,
                        el cual se imparte en modalidades <b>autoplaneada y mixta</b> a través de planteles oficiales.
                    </p>

                    <template v-if="carreras.length">
                        <div class="au-sep" aria-hidden="true"><i class="fas fa-book-open"></i></div>
                        <h3 class="au-carr__title">Carreras que se ofertan</h3>
                        <ul class="au-carr">
                            <li v-for="(c, i) in carreras" :key="c.id" :style="{ '--d': i * 90 + 'ms' }">
                                <span class="au-carr__ic"><i class="fas" :class="c.icono"></i></span>
                                <b>{{ c.nombre }}</b>
                            </li>
                        </ul>
                    </template>
                </div>
            </aside>

            <!-- Panel del formulario -->
            <main class="au-side">
                <div class="au-card">
                    <div class="au-card__logos">
                        <img src="/images/bachillerato-nacional-sm.png" alt="Bachillerato Nacional SAINS" />
                    </div>

                    <h1 class="au-card__title"><slot name="title">Bienvenido</slot></h1>
                    <p class="au-card__sub"><slot name="subtitle" /></p>

                    <slot />

                    <template v-if="$slots.google">
                        <div class="au-divider"><span>o continúa con</span></div>
                        <slot name="google" />
                    </template>

                    <p class="au-card__foot"><slot name="footer" /></p>
                </div>

                <div v-if="carreras.length" class="au-carr-movil">
                    <h3 class="au-carr__title">Carreras que se ofertan</h3>
                    <ul class="au-carr">
                        <li v-for="c in carreras" :key="c.id" style="opacity: 1; animation: none">
                            <span class="au-carr__ic"><i class="fas" :class="c.icono"></i></span>
                            <b>{{ c.nombre }}</b>
                        </li>
                    </ul>
                </div>
            </main>
        </div>
    </a-config-provider>
</template>

<style scoped>
.au {
    position: relative;
    display: grid;
    grid-template-columns: 1.08fr 1fr;
    min-height: calc(100vh - 64px);
    background: #f4f7fb;
    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
}

/* ---------- Panel de marca ---------- */
.au-brand {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    padding: 56px 132px 56px 7%;
    color: #fff;
    background:
        linear-gradient(180deg, rgba(10, 33, 92, .18) 0%, rgba(10, 33, 92, .32) 40%, rgba(9, 30, 84, .62) 75%, rgba(9, 30, 84, .82) 100%),
        linear-gradient(90deg, rgba(9, 30, 84, .5) 0%, rgba(9, 30, 84, .2) 55%, rgba(9, 30, 84, 0) 100%),
        url('/images/fondo-auth-issfam.jpg') 82% 100% / auto 112% no-repeat,
        #0b2a63;
}
/* curva blanca que empalma con el lado del formulario */
.au-brand::after {
    content: '';
    position: absolute;
    top: -14%;
    right: -190px;
    width: 300px;
    height: 128%;
    background: #f4f7fb;
    border-radius: 50%;
}
.au-brand__dots {
    position: absolute;
    top: 46px;
    right: 130px;
    width: 150px;
    height: 118px;
    background-image: radial-gradient(rgba(255, 255, 255, .38) 1.7px, transparent 1.8px);
    background-size: 19px 19px;
    opacity: .7;
}
.au-brand__arc {
    position: absolute;
    bottom: -140px;
    right: -60px;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: radial-gradient(circle at 38% 38%, #ffc933, #f5b301 60%, transparent 68%);
    opacity: .92;
    animation: au-float 9s ease-in-out infinite;
}
.au-brand__ring {
    position: absolute;
    top: -120px;
    left: -120px;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, .12);
    animation: au-float 12s ease-in-out infinite reverse;
}
@keyframes au-float {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(-14px, 16px); }
}
.au-brand__inner {
    position: relative;
    z-index: 2;
    max-width: 560px;
    width: 100%;
    animation: au-slide-in .6s cubic-bezier(.16, 1, .3, 1);
}
@keyframes au-slide-in { from { opacity: 0; transform: translateX(-22px); } to { opacity: 1; transform: none; } }

.au-org { margin-bottom: 26px; }
.au-org__logo {
    display: block;
    height: 104px;
    max-width: 100%;
    object-fit: contain;
    padding: 12px 22px;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 18px 40px -18px rgba(0, 0, 0, .55);
}

.au-brand__lead,
.au-brand__sub { color: rgba(255, 255, 255, .9); line-height: 1.5; margin: 0 0 12px; }
.au-brand__lead { font-size: 1.12rem; }
.au-brand__sub { font-size: .98rem; margin-bottom: 0; }
.au-brand__lead b,
.au-brand__sub b { color: #f5b301; font-weight: 700; }

.au-sep {
    position: relative;
    margin: 24px 0 18px;
    height: 2px;
    background: linear-gradient(90deg, #f5b301 0 46%, transparent 46% 54%, #3b82f6 54% 100%);
    border-radius: 2px;
}
.au-sep i {
    position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);
    color: #f5b301; font-size: 18px;
}
.au-carr__title {
    margin: 0 0 16px;
    font-size: 1.02rem;
    font-weight: 800;
    letter-spacing: .03em;
    text-transform: uppercase;
    color: #fff;
}
.au-carr {
    list-style: none; margin: 0; padding: 0;
    display: grid; grid-template-columns: repeat(auto-fit, minmax(96px, 1fr)); gap: 14px;
}
.au-carr li {
    position: relative;
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    text-align: center;
    opacity: 0;
    animation: au-rise .5s cubic-bezier(.16, 1, .3, 1) forwards;
    animation-delay: calc(300ms + var(--d));
}
@keyframes au-rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
.au-carr li:not(:last-child)::after {
    content: ''; position: absolute; right: -7px; top: 8px; bottom: 8px;
    width: 1px; background: rgba(255, 255, 255, .2);
}
.au-carr__ic {
    width: 66px; height: 66px; border-radius: 50%;
    display: grid; place-items: center;
    border: 2px solid rgba(255, 255, 255, .85);
    background: rgba(255, 255, 255, .06);
    font-size: 24px; color: #fff;
    transition: transform .25s ease, background .25s ease, border-color .25s ease;
}
.au-carr li:hover .au-carr__ic { transform: translateY(-3px); background: rgba(245, 179, 1, .18); border-color: #f5b301; }
.au-carr b { font-size: 12.5px; font-weight: 600; line-height: 1.3; color: #fff; }

/* ---------- Panel del formulario ---------- */
.au-carr-movil { display: none; }
.au-side {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 28px;
}
.au-card {
    width: 100%;
    max-width: 520px;
    background: #fff;
    border: 1px solid #eef1f6;
    border-radius: 24px;
    padding: 44px 48px 34px;
    box-shadow: 0 40px 90px -34px rgba(15, 23, 42, .3);
    text-align: center;
    animation: au-card-in .5s cubic-bezier(.16, 1, .3, 1);
}
@keyframes au-card-in { from { opacity: 0; transform: translateY(16px) scale(.99); } to { opacity: 1; transform: none; } }
.au-card__logos {
    display: flex; align-items: center; justify-content: center; gap: 16px;
    margin-bottom: 18px;
}
.au-card__logos img { height: 78px; max-width: 70%; object-fit: contain; }
.au-card__logos-sep { width: 2px; align-self: stretch; margin: 4px 0; background: #f5b301; border-radius: 2px; }
.au-card__title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #163964;
    margin: 0 0 4px;
    letter-spacing: -.01em;
}
.au-card__sub { font-size: .88rem; color: #64748b; margin: 0 0 22px; }
.au-card > :deep(form),
.au-card > :deep(.au-form) { text-align: left; }

/* Entrada escalonada de los campos del formulario */
.au-card :deep(.au-form) > * {
    opacity: 0;
    animation: au-field-in .45s cubic-bezier(.16, 1, .3, 1) forwards;
}
.au-card :deep(.au-form) > *:nth-child(1) { animation-delay: .12s; }
.au-card :deep(.au-form) > *:nth-child(2) { animation-delay: .19s; }
.au-card :deep(.au-form) > *:nth-child(3) { animation-delay: .26s; }
.au-card :deep(.au-form) > *:nth-child(4) { animation-delay: .33s; }
.au-card :deep(.au-form) > *:nth-child(5) { animation-delay: .40s; }
.au-card :deep(.au-form) > *:nth-child(6) { animation-delay: .47s; }
@keyframes au-field-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }

.au-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 18px 0;
    color: #94a3b8;
    font-size: 12px;
}
.au-divider::before,
.au-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e2e8f0;
}
.au-card__foot { margin: 18px 0 0; font-size: 13px; color: #64748b; }

/* ---------- Responsive ---------- */
@media (max-width: 1100px) {
    .au-brand { padding-right: 96px; }
    .au-brand::after { right: -220px; }
}
@media (max-width: 1024px) {
    .au { grid-template-columns: 1fr; }
    .au-brand { display: none; }
    .au-side {
        padding: 36px 18px;
        min-height: calc(100vh - 64px);
        background:
            linear-gradient(180deg, rgba(10, 33, 92, .55), rgba(9, 30, 84, .92)),
            url('/images/fondo-auth-issfam.jpg') center / cover no-repeat, #0b2a63;
    }
    .au-card { box-shadow: 0 30px 70px -20px rgba(15, 23, 42, .45); }
    .au-carr-movil { display: block; width: 100%; max-width: 520px; margin-top: 26px; color: #fff; }
    .au-carr-movil .au-carr__title { text-align: center; }
}
@media (max-width: 520px) {
    .au-card { padding: 34px 22px 26px; }
    .au-card__logos { gap: 10px; }
    .au-card__logos img { height: 50px; }
}

@media (prefers-reduced-motion: reduce) {
    .au *, .au *::before, .au *::after { animation: none !important; }
    .au-card :deep(.au-form) > *,
    .au-carr li,
    .au-brand__inner { opacity: 1 !important; }
}
</style>

<style>
/* Ajustes globales para inputs/botones dentro de las pantallas de auth */
.au .ant-input-affix-wrapper { border-radius: 12px; }
.au .ant-btn { border-radius: 12px; font-weight: 600; }
.au .ant-input-affix-wrapper > .ant-input-prefix { color: #15509b; margin-inline-end: 8px; }
</style>