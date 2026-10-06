<script setup>
import { computed, h, onMounted, onUnmounted, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    HomeOutlined, FileTextOutlined, LaptopOutlined,
    UserOutlined, LogoutOutlined, MenuOutlined, DownOutlined, CrownOutlined, BellOutlined,
} from '@ant-design/icons-vue';
import { antdTheme, antdLocale } from '@/theme';
import { showFlash, confirmAction } from '@/lib/notify';
import NotifBell from '@/Components/NotifBell.vue';

defineProps({ title: { type: String, default: null } });

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const estudiante = computed(() => page.props.auth?.estudiante ?? null);
const nombre = computed(() => estudiante.value?.nombre_completo?.trim() || user.value?.correo || 'Estudiante');
const fotoUrl = computed(() => estudiante.value?.foto_url ?? null);
const planActivo = computed(() => estudiante.value?.plan_activo ?? false);

const drawerOpen = ref(false);
const userMenuOpen = ref(false);
function goMenu(routeName) {
    userMenuOpen.value = false;
    router.visit(route(routeName));
}
const scrolled = ref(false);
function onScroll() { scrolled.value = window.scrollY > 6; }
onMounted(() => { onScroll(); window.addEventListener('scroll', onScroll, { passive: true }); });
onUnmounted(() => window.removeEventListener('scroll', onScroll));

const initials = computed(() => {
    const n = nombre.value.split(' ').filter(Boolean);
    return ((n[0]?.[0] ?? 'E') + (n[1]?.[0] ?? '')).toUpperCase();
});

const ico = (c) => () => h(c);
const nav = [
    { key: 'estudiante.dashboard', icon: ico(HomeOutlined), label: 'Inicio', route: 'estudiante.dashboard' },
    { key: 'estudiante.clases-premium', icon: ico(CrownOutlined), label: 'Clases', route: 'estudiante.clases-premium' },
    { key: 'estudiante.examenes', icon: ico(FileTextOutlined), label: 'Mis exámenes', route: 'estudiante.examenes' },
    { key: 'estudiante.simulador', icon: ico(LaptopOutlined), label: 'Simulador', route: 'estudiante.simulador' },
];

const currentKey = computed(() => {
    let current;
    try { current = route().current(); } catch (e) { return null; }
    const hit = nav.find((n) => current === n.key || (current && current.startsWith(n.key + '.')));
    return hit ? hit.key : null;
});
const selectedKeys = computed(() => (currentKey.value ? [currentKey.value] : []));

function go({ key }) {
    const item = nav.find((n) => n.key === key);
    if (item) {
        router.visit(route(item.route));
        drawerOpen.value = false;
    }
}

function logout() {
    confirmAction({
        title: '¿Cerrar sesión?',
        content: 'Tendrás que volver a iniciar sesión para continuar.',
        okText: 'Sí, salir',
        danger: true,
        tone: 'logout',
        onOk: () => router.post(route('logout')),
    });
}

watch(() => page.props.flash, showFlash, { deep: true });
onMounted(() => showFlash(page.props.flash));
</script>

<template>
    <a-config-provider :theme="antdTheme" :locale="antdLocale">
        <Head :title="title" />

        <div class="stu-shell">
            <div class="stu-bg" aria-hidden="true">
                <span class="stu-blob stu-blob--1"></span>
                <span class="stu-blob stu-blob--2"></span>
            </div>

            <header class="stu-header" :class="{ 'is-scrolled': scrolled }">
                <div class="stu-header__inner">
                    <button class="stu-burger" @click="drawerOpen = true"><MenuOutlined /></button>

                    <Link :href="route('estudiante.dashboard')" class="stu-brand">
                        <img src="/images/logo-sm.png" alt="SAINS" />
                    </Link>

                    <nav class="stu-nav">
                        <a-tooltip
                            v-for="n in nav"
                            :key="n.key"
                            :title="n.label"
                            placement="bottom"
                            :mouse-enter-delay="0.4"
                        >
                            <button
                                class="stu-nav__item"
                                :class="{ 'is-active': currentKey === n.key }"
                                @click="go({ key: n.key })"
                            >
                                <component :is="n.icon()" />
                                <span>{{ n.label }}</span>
                            </button>
                        </a-tooltip>
                    </nav>

                    <div class="stu-header__right">
                        <NotifBell scope="estudiante" />

                        <Link
                            v-if="!planActivo"
                            :href="route('estudiante.checkout')"
                            class="stu-upgrade"
                        >
                            <CrownOutlined /> <span>Hazte Premium</span>
                        </Link>

                        <a-dropdown v-model:open="userMenuOpen" placement="bottomRight" :trigger="['click']" overlay-class-name="usr-drop">
                            <button class="stu-user" :class="{ 'is-open': userMenuOpen }">
                                <a-avatar v-if="fotoUrl" :src="fotoUrl" :size="30" />
                                <span v-else class="stu-user__avatar">{{ initials }}</span>
                                <span class="stu-user__meta">
                                    <span class="stu-user__name">{{ nombre }}</span>
                                    <span class="stu-user__role">{{ planActivo ? 'Premium' : 'Plan básico' }}</span>
                                </span>
                                <DownOutlined class="stu-user__caret" />
                            </button>
                            <template #overlay>
                                <div class="usr-menu">
                                    <div class="usr-menu__head" :class="{ 'is-premium': planActivo }">
                                        <a-avatar v-if="fotoUrl" :src="fotoUrl" :size="40" shape="square" style="border-radius:12px;flex:none" />
                                        <span v-else class="usr-menu__avatar">{{ initials }}</span>
                                        <div>
                                            <div class="usr-menu__name">{{ nombre }}</div>
                                            <div class="usr-menu__mail">
                                                <CrownOutlined v-if="planActivo" /> {{ planActivo ? 'Plan Premium activo' : 'Plan básico' }}
                                            </div>
                                        </div>
                                    </div>
                                    <button class="usr-menu__item" @click="goMenu('estudiante.perfil')">
                                        <UserOutlined /><span>Mi perfil</span>
                                    </button>
                                    <button class="usr-menu__item" @click="goMenu('notificaciones.index')">
                                        <BellOutlined /><span>Notificaciones</span>
                                    </button>
                                    <button v-if="!planActivo" class="usr-menu__item is-up" @click="goMenu('estudiante.checkout')">
                                        <CrownOutlined /><span>Adquirir Premium</span>
                                    </button>
                                    <div class="usr-menu__sep"></div>
                                    <button class="usr-menu__item is-danger" @click="userMenuOpen = false; logout()">
                                        <LogoutOutlined /><span>Cerrar sesión</span>
                                    </button>
                                </div>
                            </template>
                        </a-dropdown>
                    </div>
                </div>
            </header>

            <a-drawer v-model:open="drawerOpen" placement="left" :width="260" :body-style="{ padding: '8px 0' }">
                <template #title>
                    <img src="/images/logo-sm.png" alt="SAINS" style="height: 26px" />
                </template>
                <a-menu mode="inline" :selected-keys="selectedKeys"
                    :items="nav.map((n) => ({ key: n.key, icon: n.icon, label: n.label }))" @click="go" />
            </a-drawer>

            <main class="stu-content">
                <div class="sains-page">
                    <slot />
                </div>
            </main>

            <footer class="stu-footer">
                © {{ new Date().getFullYear() }} SAINS · Sistema de Aprendizaje Integral
            </footer>
        </div>
    </a-config-provider>
</template>

<style scoped>
.stu-shell { min-height: 100vh; display: flex; flex-direction: column; position: relative; }

/* Fondo con blobs suaves --------------------------------------- */
.stu-bg { position: fixed; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
.stu-blob { position: absolute; border-radius: 50%; filter: blur(90px); opacity: .5; }
.stu-blob--1 {
    width: 520px; height: 520px; top: -220px; right: -160px;
    background: radial-gradient(circle, rgba(99, 102, 241, .35), transparent 70%);
    animation: sains-blob 20s ease-in-out infinite;
}
.stu-blob--2 {
    width: 460px; height: 460px; bottom: -200px; left: -140px;
    background: radial-gradient(circle, rgba(236, 72, 153, .22), transparent 70%);
    animation: sains-blob 26s ease-in-out infinite reverse;
}

/* Header ------------------------------------------------------- */
.stu-header {
    position: sticky; top: 0; z-index: 30;
    background: rgba(255, 255, 255, .82);
    backdrop-filter: saturate(180%) blur(16px);
    border-bottom: 1px solid rgba(226, 232, 240, .8);
    box-shadow: 0 1px 0 rgba(255, 255, 255, .6) inset, 0 8px 24px -20px rgba(79, 70, 229, .5);
    transition: box-shadow .25s ease, background .25s ease;
}
.stu-header.is-scrolled {
    background: rgba(255, 255, 255, .94);
    box-shadow: 0 1px 0 rgba(255, 255, 255, .6) inset, 0 14px 30px -18px rgba(15, 23, 42, .35);
}
.stu-header::before {
    content: ''; position: absolute; inset: 0 0 auto 0; height: 3px;
    background: linear-gradient(90deg, #4f46e5, #7c3aed 40%, #ec4899 75%, #f59e0b);
    background-size: 300% 100%;
    animation: sains-shimmer 8s ease-in-out infinite;
}
.stu-header__inner {
    max-width: 1200px; margin: 0 auto; height: 66px;
    display: flex; align-items: center; gap: 14px; padding: 0 22px;
}
.stu-brand { display: flex; align-items: center; flex: none; }
.stu-brand img { height: 34px; display: block; transition: transform .2s ease; }
.stu-brand:hover img { transform: scale(1.04); }

.stu-nav { flex: 1; display: flex; align-items: center; justify-content: center; gap: 4px; }
.stu-nav__item {
    position: relative;
    display: inline-flex; align-items: center; gap: 7px;
    padding: 8px 15px; border: 0; background: transparent; cursor: pointer;
    font-size: 13.5px; font-weight: 550; color: #64748b; border-radius: 10px;
    transition: color .18s ease, background .18s ease, box-shadow .18s ease;
}
.stu-nav__item :deep(.anticon) { font-size: 15px; transition: transform .18s ease; }
.stu-nav__item:hover { color: #4f46e5; background: rgba(79, 70, 229, .07); }
.stu-nav__item:hover :deep(.anticon) { transform: translateY(-1px); }
.stu-nav__item.is-active {
    color: #4338ca; font-weight: 650;
    background: linear-gradient(180deg, rgba(99, 102, 241, .12), rgba(99, 102, 241, .06));
    box-shadow: inset 0 0 0 1px rgba(99, 102, 241, .18);
}
.stu-nav__item.is-active::after {
    content: ''; position: absolute; left: 15px; right: 15px; bottom: -1px; height: 2.5px;
    background: var(--sains-grad); border-radius: 3px;
    animation: sains-nav-underline .3s cubic-bezier(.16, 1, .3, 1);
}
@keyframes sains-nav-underline { from { transform: scaleX(0); opacity: 0; } to { transform: scaleX(1); opacity: 1; } }

.stu-header__right { display: flex; align-items: center; gap: 10px; flex: none; }
.stu-upgrade {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 650; color: #fff;
    padding: 6px 12px; border-radius: 999px;
    background: linear-gradient(135deg, #f59e0b, #ec4899);
    box-shadow: 0 8px 18px -8px rgba(236, 72, 153, .6);
    transition: transform .16s ease, box-shadow .16s ease;
}
.stu-upgrade:hover { transform: translateY(-1px); box-shadow: 0 10px 22px -8px rgba(236, 72, 153, .7); color: #fff; }

.stu-user {
    display: flex; align-items: center; gap: 9px;
    background: linear-gradient(135deg, #f6f7fb, #eef1fb); border: 1px solid #e8eaf6; border-radius: 999px;
    padding: 4px 12px 4px 5px; cursor: pointer;
    transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
}
.stu-user:hover { border-color: #c7d2fe; box-shadow: 0 6px 16px -10px rgba(79, 70, 229, .5); transform: translateY(-1px); }
.stu-user.is-open { border-color: #a5b4fc; box-shadow: 0 8px 20px -10px rgba(79, 70, 229, .55); }
.stu-user__caret { font-size: 10px; color: #94a3b8; transition: transform .2s ease; }
.stu-user.is-open .stu-user__caret { transform: rotate(180deg); color: #6366f1; }
.stu-user__avatar {
    width: 30px; height: 30px; border-radius: 50%; flex: none;
    background: var(--sains-grad); color: #fff; font-size: 12px; font-weight: 700;
    box-shadow: 0 4px 10px -4px rgba(79, 70, 229, .6);
    display: flex; align-items: center; justify-content: center;
}
.stu-user__meta { display: flex; flex-direction: column; line-height: 1.15; text-align: left; }
.stu-user__name { font-size: 12.5px; font-weight: 650; color: #0f172a; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.stu-user__role { font-size: 10.5px; color: #94a3b8; }

.stu-burger { display: none; border: 0; background: transparent; font-size: 18px; color: #334155; cursor: pointer; padding: 6px; }

.stu-content { position: relative; z-index: 1; max-width: 1200px; width: 100%; margin: 0 auto; padding: 28px 22px 44px; flex: 1; }
.stu-footer { position: relative; z-index: 1; text-align: center; color: #94a3b8; font-size: 12.5px; padding: 8px 0 26px; }

@media (max-width: 940px) {
    .stu-nav { display: none; }
    .stu-burger { display: inline-flex; }
    .stu-user__meta { display: none; }
    .stu-upgrade span { display: none; }
    .stu-header__inner { gap: 10px; }
    .stu-brand { flex: 1; }
}
</style>
