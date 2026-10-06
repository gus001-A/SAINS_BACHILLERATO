<script setup>
import { Head } from '@inertiajs/vue3';
import {
    ReadFilled, TeamOutlined, BarChartOutlined,
} from '@ant-design/icons-vue';
import { authTheme, antdLocale } from '@/theme';
import PublicNav from '@/Components/PublicNav.vue';

defineProps({
    title: { type: String, default: 'SAINS' },
});

const features = [
    { icon: ReadFilled, t: 'Método comprobado', d: 'Aprendizaje eficaz para tu ingreso al bachillerato.' },
    { icon: TeamOutlined, t: 'Docentes expertos', d: 'Profesionales con experiencia y vocación docente.' },
    { icon: BarChartOutlined, t: 'Plan Premium', d: 'Recursos exclusivos y seguimiento personalizado.' },
];
</script>

<template>
    <a-config-provider :theme="authTheme" :locale="antdLocale">
        <Head :title="title" />

        <PublicNav />

        <div class="au">
            <!-- Panel de marca -->
            <aside class="au-brand">
                <span class="au-brand__dots" aria-hidden="true"></span>
                <span class="au-brand__arc" aria-hidden="true"></span>
                <span class="au-brand__ring" aria-hidden="true"></span>

                <div class="au-brand__inner">
                    <h2 class="au-brand__title">
                        Asegura <span>tu lugar</span> en el bachillerato de tus sueños
                    </h2>
                    <p class="au-brand__sub">
                        Fórmate con nuestro método, docentes expertos y acompañamiento personalizado.
                    </p>

                    <ul class="au-feats">
                        <li v-for="(f, i) in features" :key="f.t" :style="{ '--d': i * 90 + 'ms' }">
                            <span class="au-feats__ic"><component :is="f.icon" /></span>
                            <b>{{ f.t }}</b>
                            <small>{{ f.d }}</small>
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- Panel del formulario -->
            <main class="au-side">
                <div class="au-card">
                    <img src="/images/logo_u.png" alt="SAINS" class="au-card__logo" />

                    <h1 class="au-card__title"><slot name="title">Bienvenido</slot></h1>
                    <p class="au-card__sub"><slot name="subtitle" /></p>

                    <slot />

                    <template v-if="$slots.google">
                        <div class="au-divider"><span>o continúa con</span></div>
                        <slot name="google" />
                    </template>

                    <p class="au-card__foot"><slot name="footer" /></p>
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
    background: linear-gradient(158deg, #1e3a8a 0%, #1d4ed8 52%, #1e40af 100%);
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
    background: radial-gradient(circle at 38% 38%, #fbbf24, #f59e0b 60%, transparent 68%);
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
    max-width: 500px;
    width: 100%;
    animation: au-slide-in .6s cubic-bezier(.16, 1, .3, 1);
}
@keyframes au-slide-in { from { opacity: 0; transform: translateX(-22px); } to { opacity: 1; transform: none; } }

.au-brand__title {
    font-size: clamp(1.7rem, 2.6vw, 2.35rem);
    font-weight: 800;
    line-height: 1.22;
    margin: 0 0 14px;
    letter-spacing: -.01em;
}
.au-brand__title span {
    color: #fbbf24;
    position: relative;
    white-space: nowrap;
}
.au-brand__title span::after {
    content: '';
    position: absolute;
    left: 0; right: 0; bottom: 2px;
    height: 8px;
    background: rgba(251, 191, 36, .28);
    border-radius: 4px;
    z-index: -1;
}
.au-brand__sub {
    font-size: 1rem;
    line-height: 1.6;
    color: rgba(255, 255, 255, .78);
    margin: 0 0 40px;
    max-width: 440px;
}
.au-feats {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
}
.au-feats li {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding-left: 2px;
    opacity: 0;
    animation: au-rise .5s cubic-bezier(.16, 1, .3, 1) forwards;
    animation-delay: calc(300ms + var(--d));
}
@keyframes au-rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
.au-feats li:not(:last-child)::after {
    content: '';
    position: absolute;
    right: -11px;
    top: 6px;
    bottom: 6px;
    width: 1px;
    background: rgba(255, 255, 255, .2);
}
.au-feats__ic {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    border: 1.5px solid rgba(255, 255, 255, .35);
    background: rgba(255, 255, 255, .06);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #fbbf24;
    margin-bottom: 6px;
    transition: transform .25s ease, background .25s ease;
}
.au-feats li:hover .au-feats__ic { transform: translateY(-3px); background: rgba(255, 255, 255, .14); }
.au-feats b { font-size: 13.5px; font-weight: 700; }
.au-feats small { font-size: 11.5px; color: rgba(255, 255, 255, .62); line-height: 1.4; }

/* ---------- Panel del formulario ---------- */
.au-side {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 28px;
}
.au-card {
    width: 100%;
    max-width: 416px;
    background: #fff;
    border: 1px solid #eef1f6;
    border-radius: 24px;
    padding: 40px 38px 30px;
    box-shadow: 0 40px 90px -34px rgba(15, 23, 42, .3);
    text-align: center;
    animation: au-card-in .5s cubic-bezier(.16, 1, .3, 1);
}
@keyframes au-card-in { from { opacity: 0; transform: translateY(16px) scale(.99); } to { opacity: 1; transform: none; } }
.au-card__logo { height: 46px; margin-bottom: 14px; }
.au-card__title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1e3a8a;
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
    .au-feats { gap: 16px; }
}
@media (max-width: 1024px) {
    .au { grid-template-columns: 1fr; }
    .au-brand { display: none; }
    .au-side {
        padding: 36px 18px;
        min-height: calc(100vh - 64px);
        background: linear-gradient(158deg, #1e3a8a 0%, #1d4ed8 60%, #1e40af 100%);
    }
    .au-card { box-shadow: 0 30px 70px -20px rgba(15, 23, 42, .45); }
}

@media (prefers-reduced-motion: reduce) {
    .au *, .au *::before, .au *::after { animation: none !important; }
    .au-card :deep(.au-form) > *,
    .au-feats li,
    .au-brand__inner { opacity: 1 !important; }
}
</style>

<style>
/* Ajustes globales para inputs/botones dentro de las pantallas de auth */
.au .ant-input-affix-wrapper { border-radius: 12px; }
.au .ant-btn { border-radius: 12px; font-weight: 600; }
.au .ant-input-affix-wrapper > .ant-input-prefix { color: #1d4ed8; margin-inline-end: 8px; }
</style>