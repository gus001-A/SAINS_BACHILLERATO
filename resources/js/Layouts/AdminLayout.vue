<script setup>
import { computed, h, onMounted, onUnmounted, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    UserOutlined, BellOutlined, LogoutOutlined, MenuOutlined,
    TeamOutlined, SolutionOutlined, DollarOutlined, PhoneOutlined,
    SafetyOutlined, DatabaseOutlined, DownOutlined,
    HomeOutlined,
} from '@ant-design/icons-vue';
import { antdTheme, antdLocale } from '@/theme';
import { showFlash, confirmAction } from '@/lib/notify';
import NotifBell from '@/Components/NotifBell.vue';

defineProps({ title: { type: String, default: null } });

const page = usePage();
const admin = computed(() => page.props.auth?.admin ?? null);
const adminName = computed(() => admin.value?.nombre_completo?.trim() || 'Administrador');
const adminEmail = computed(() => page.props.auth?.user?.correo || admin.value?.correo || '');
const adminInitials = computed(() => {
    const n = adminName.value.split(' ').filter(Boolean);
    return ((n[0]?.[0] ?? 'A') + (n[1]?.[0] ?? '')).toUpperCase();
});
const userMenuOpen = ref(false);
function goMenu(routeName) {
    userMenuOpen.value = false;
    router.visit(route(routeName));
}

const drawerOpen = ref(false);
const scrolled = ref(false);
function onScroll() { scrolled.value = window.scrollY > 6; }
onMounted(() => { onScroll(); window.addEventListener('scroll', onScroll, { passive: true }); });
onUnmounted(() => window.removeEventListener('scroll', onScroll));

const ico = (comp) => () => h(comp);

const nav = [
    { key: 'admin.dashboard', icon: ico(HomeOutlined), label: 'Inicio', route: 'admin.dashboard' },
    { key: 'admin.estudiantes', icon: ico(TeamOutlined), label: 'Estudiantes', route: 'admin.estudiantes.index' },
    {
        key: 'evaluacion', icon: ico(SolutionOutlined), label: 'Evaluación',
        children: [
            { key: 'admin.examenes', label: 'Exámenes', route: 'admin.examenes.index' },
            { key: 'admin.preguntas', label: 'Preguntas', route: 'admin.preguntas.index' },
            { key: 'admin.examenes-realizados', label: 'Exámenes realizados', route: 'admin.examenes-realizados.index' },
        ],
    },
    {
        key: 'financiero', icon: ico(DollarOutlined), label: 'Financiero',
        children: [
            { key: 'admin.pagos', label: 'Pagos', route: 'admin.pagos.index' },
            { key: 'admin.cupones', label: 'Cupones', route: 'admin.cupones.index' },
        ],
    },
    { key: 'admin.callcenter', icon: ico(PhoneOutlined), label: 'Call Center', route: 'admin.callcenter.index' },
    { key: 'admin.administradores', icon: ico(SafetyOutlined), label: 'Admins', route: 'admin.administradores.index' },
    {
        key: 'catalogos', icon: ico(DatabaseOutlined), label: 'Catálogos',
        children: [
            { key: 'admin.asignaturas', label: 'Materias', route: 'admin.asignaturas.index' },
            { key: 'admin.clases', label: 'Clases', route: 'admin.clases.index' },
            { key: 'admin.videos', label: 'Videos', route: 'admin.videos.index' },
        ],
    },
];

const routeByKey = {};
(function walk(items) {
    for (const it of items) {
        if (it.route) routeByKey[it.key] = it.route;
        if (it.children) walk(it.children);
    }
})(nav);

const currentKey = computed(() => {
    let current;
    try { current = route().current(); } catch (e) { return null; }
    if (!current) return null;
    const hit = Object.keys(routeByKey).find((k) => current === k || current.startsWith(k + '.'));
    return hit ?? null;
});

function isActive(item) {
    if (item.route && currentKey.value === item.key) return true;
    if (item.children) return item.children.some((c) => c.key === currentKey.value);
    return false;
}

const drawerItems = computed(() =>
    nav.map((m) => ({
        key: m.key,
        icon: m.icon,
        label: m.label,
        children: m.children?.map((c) => ({ key: c.key, label: c.label })),
    })),
);
const drawerSelected = computed(() => (currentKey.value ? [currentKey.value] : []));
const drawerOpenKeys = computed(() => {
    const parent = nav.find((m) => m.children?.some((c) => c.key === currentKey.value));
    return parent ? [parent.key] : [];
});

function go(item) {
    const target = item.route ? item : null;
    const key = item.key ?? item;
    const routeName = target?.route ?? routeByKey[key];
    if (routeName) {
        router.visit(route(routeName));
        drawerOpen.value = false;
    }
}

function logout() {
    confirmAction({
        title: '¿Cerrar sesión?',
        content: 'Se cerrará tu sesión de administrador.',
        okText: 'Sí, salir',
        danger: true,
        onOk: () => router.post(route('logout')),
    });
}

watch(() => page.props.flash, showFlash, { deep: true });
onMounted(() => showFlash(page.props.flash));
</script>

<template>
    <a-config-provider :theme="antdTheme" :locale="antdLocale">
        <Head :title="title" />

        <div class="adm-shell">
            <div class="adm-bg" aria-hidden="true">
                <span class="adm-blob adm-blob--1"></span>
                <span class="adm-blob adm-blob--2"></span>
            </div>

            <header class="adm-header" :class="{ 'is-scrolled': scrolled }">
                <div class="adm-header__inner">
                    <button class="adm-burger" @click="drawerOpen = true"><MenuOutlined /></button>

                    <Link :href="route('admin.dashboard')" class="adm-brand">
                        <img src="/images/logo-sm.png" alt="SAINS" />
                    </Link>

                    <nav class="adm-nav">
                        <template v-for="item in nav" :key="item.key">
                            <a-dropdown v-if="item.children" :trigger="['hover']" placement="bottom">
                                <button class="adm-nav__item" :class="{ 'is-active': isActive(item) }">
                                    <component :is="item.icon()" />
                                    <span>{{ item.label }}</span>
                                    <DownOutlined class="adm-nav__caret" />
                                </button>
                                <template #overlay>
                                    <a-menu class="adm-submenu" @click="({ key }) => go(key)">
                                        <a-menu-item v-for="c in item.children" :key="c.key">{{ c.label }}</a-menu-item>
                                    </a-menu>
                                </template>
                            </a-dropdown>
                            <a-tooltip v-else :title="item.label" placement="bottom" :mouse-enter-delay="0.4">
                                <button
                                    class="adm-nav__item"
                                    :class="{ 'is-active': isActive(item) }"
                                    @click="go(item)"
                                >
                                    <component :is="item.icon()" />
                                    <span>{{ item.label }}</span>
                                </button>
                            </a-tooltip>
                        </template>
                    </nav>

                    <div class="adm-header__right">
                        <NotifBell scope="admin" />

                        <a-dropdown v-model:open="userMenuOpen" placement="bottomRight" :trigger="['click']" overlay-class-name="usr-drop">
                            <button class="adm-user" :class="{ 'is-open': userMenuOpen }">
                                <span class="adm-user__avatar">{{ adminInitials }}</span>
                                <span class="adm-user__meta">
                                    <span class="adm-user__name">{{ adminName }}</span>
                                    <span class="adm-user__role">Administrador</span>
                                </span>
                                <DownOutlined class="adm-user__caret" />
                            </button>
                            <template #overlay>
                                <div class="usr-menu">
                                    <div class="usr-menu__head">
                                        <span class="usr-menu__avatar">{{ adminInitials }}</span>
                                        <div>
                                            <div class="usr-menu__name">{{ adminName }}</div>
                                            <div class="usr-menu__mail">{{ adminEmail || 'Administrador' }}</div>
                                        </div>
                                    </div>
                                    <button class="usr-menu__item" @click="goMenu('admin.perfil')">
                                        <UserOutlined /><span>Mi perfil</span>
                                    </button>
                                    <button class="usr-menu__item" @click="goMenu('notificaciones.index')">
                                        <BellOutlined /><span>Notificaciones</span>
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

            <a-drawer v-model:open="drawerOpen" placement="left" :width="272" :body-style="{ padding: '8px 0' }">
                <template #title>
                    <img src="/images/logo-sm.png" alt="SAINS" style="height: 26px" />
                </template>
                <a-menu
                    mode="inline"
                    :items="drawerItems"
                    :selected-keys="drawerSelected"
                    :open-keys="drawerOpenKeys"
                    @click="({ key }) => go(key)"
                />
            </a-drawer>

            <main class="adm-content">
                <div class="sains-page">
                    <slot />
                </div>
            </main>

            <footer class="adm-footer">
                © {{ new Date().getFullYear() }} SAINS · Sistema de Administración Integral
            </footer>
        </div>
    </a-config-provider>
</template>

<style scoped>
.adm-shell { min-height: 100vh; display: flex; flex-direction: column; position: relative; }

/* Fondo con blobs suaves --------------------------------------- */
.adm-bg { position: fixed; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
.adm-blob { position: absolute; border-radius: 50%; filter: blur(100px); opacity: .45; }
.adm-blob--1 {
    width: 560px; height: 560px; top: -240px; right: -180px;
    background: radial-gradient(circle, rgba(99, 102, 241, .32), transparent 70%);
    animation: sains-blob 22s ease-in-out infinite;
}
.adm-blob--2 {
    width: 480px; height: 480px; bottom: -220px; left: -160px;
    background: radial-gradient(circle, rgba(124, 58, 237, .18), transparent 70%);
    animation: sains-blob 28s ease-in-out infinite reverse;
}

/* Header ------------------------------------------------------- */
.adm-header {
    position: sticky; top: 0; z-index: 30;
    background: rgba(255, 255, 255, .82);
    backdrop-filter: saturate(180%) blur(16px);
    border-bottom: 1px solid rgba(226, 232, 240, .8);
    box-shadow: 0 1px 0 rgba(255, 255, 255, .6) inset, 0 8px 24px -20px rgba(79, 70, 229, .5);
    transition: box-shadow .25s ease, background .25s ease;
}
.adm-header.is-scrolled {
    background: rgba(255, 255, 255, .94);
    box-shadow: 0 1px 0 rgba(255, 255, 255, .6) inset, 0 14px 30px -18px rgba(15, 23, 42, .35);
}
.adm-header::before {
    content: ''; position: absolute; inset: 0 0 auto 0; height: 3px;
    background: linear-gradient(90deg, #4f46e5, #7c3aed 40%, #ec4899 75%, #f59e0b);
    background-size: 300% 100%;
    animation: sains-shimmer 8s ease-in-out infinite;
}
.adm-header__inner {
    max-width: 1480px; margin: 0 auto; height: 64px;
    display: flex; align-items: center; gap: 14px; padding: 0 22px;
}
.adm-brand { display: flex; align-items: center; flex: none; }
.adm-brand img { height: 30px; display: block; transition: transform .2s ease; }
.adm-brand:hover img { transform: scale(1.04); }

.adm-nav {
    flex: 1 1 auto; min-width: 0;
    display: flex; align-items: center; justify-content: center; gap: 2px;
    overflow-x: auto; overflow-y: hidden;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 4px 0;
}
.adm-nav::-webkit-scrollbar { display: none; }
.adm-nav__item {
    position: relative;
    display: inline-flex; align-items: center; gap: 7px; flex: none;
    padding: 8px 12px; border: 0; background: transparent; cursor: pointer;
    font-size: 13px; font-weight: 600; color: #475569; border-radius: 10px;
    white-space: nowrap;
    transition: color .16s ease, background .16s ease;
}
.adm-nav__item :deep(.anticon) { font-size: 14px; opacity: .85; transition: transform .16s ease; }
.adm-nav__caret { font-size: 9px !important; opacity: .55; }
.adm-nav__item:hover { color: #4338ca; background: #eef2ff; }
.adm-nav__item:hover :deep(.anticon:not(.adm-nav__caret)) { transform: translateY(-1px); opacity: 1; }
.adm-nav__item.is-active {
    color: #ffffff; font-weight: 650;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    box-shadow: 0 8px 18px -8px rgba(79, 70, 229, .55);
}
.adm-nav__item.is-active :deep(.anticon) { opacity: 1; }
@keyframes sains-nav-underline { from { transform: scaleX(0); opacity: 0; } to { transform: scaleX(1); opacity: 1; } }

/* En anchos intermedios: compactar sin ocultar las etiquetas */
@media (max-width: 1320px) {
    .adm-nav__item { padding: 7px 9px; gap: 5px; font-size: 12.5px; }
    .adm-nav { gap: 0; }
}
@media (max-width: 1180px) {
    .adm-nav__caret { display: none; }
    .adm-nav__item :deep(.anticon:not(.adm-nav__caret)) { display: none; }
    .adm-nav__item { padding: 7px 10px; }
}

.adm-header__right { display: flex; align-items: center; gap: 6px; flex: none; }

.adm-icon-btn {
    width: 38px; height: 38px; border-radius: 11px;
    border: 1px solid transparent; background: transparent; color: #475569; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s ease, border-color .15s ease, color .15s ease;
}
.adm-icon-btn:hover { background: #eef1fe; border-color: #e0e3f5; color: #4f46e5; }

.adm-user {
    display: flex; align-items: center; gap: 9px;
    background: linear-gradient(135deg, #f6f7fb, #eef1fb); border: 1px solid #e8eaf6; border-radius: 999px;
    padding: 4px 12px 4px 5px; cursor: pointer;
    transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
}
.adm-user:hover { border-color: #c7d2fe; box-shadow: 0 6px 16px -10px rgba(79, 70, 229, .5); transform: translateY(-1px); }
.adm-user.is-open { border-color: #a5b4fc; box-shadow: 0 8px 20px -10px rgba(79, 70, 229, .55); }
.adm-user__caret { font-size: 10px; color: #94a3b8; transition: transform .2s ease; }
.adm-user.is-open .adm-user__caret { transform: rotate(180deg); color: #6366f1; }
.adm-user__avatar {
    width: 30px; height: 30px; border-radius: 50%; flex: none;
    background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; font-size: 12px; font-weight: 700;
    box-shadow: 0 4px 10px -4px rgba(79, 70, 229, .6);
    display: flex; align-items: center; justify-content: center;
}
.adm-user__meta { display: flex; flex-direction: column; line-height: 1.15; text-align: left; }
.adm-user__name {
    font-size: 12.5px; font-weight: 650; color: #0f172a;
    max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.adm-user__role { font-size: 10.5px; color: #94a3b8; }

.adm-burger { display: none; border: 0; background: transparent; font-size: 18px; color: #334155; cursor: pointer; padding: 6px; }

.adm-content { position: relative; z-index: 1; max-width: 1480px; width: 100%; margin: 0 auto; padding: 28px 22px 44px; flex: 1; }
.adm-footer { position: relative; z-index: 1; text-align: center; color: #94a3b8; font-size: 12.5px; padding: 8px 0 26px; }

/* Notificaciones */
.notif-panel {
    width: 360px; max-width: 92vw; background: #fff; border-radius: 14px;
    box-shadow: 0 18px 44px -14px rgba(15, 23, 42, .22); border: 1px solid #eef1f6; overflow: hidden;
}
.notif-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 14px; border-bottom: 1px solid #eef1f6; background: #fafbfd; font-size: 13px;
}
.notif-body { max-height: 320px; overflow-y: auto; }
.notif-item {
    display: flex; gap: 10px; padding: 11px 14px; border-bottom: 1px solid #f4f6fa;
    color: #0f172a; text-decoration: none;
}
.notif-item:hover { background: #f7f8fc; }
.notif-dot { width: 7px; height: 7px; border-radius: 50%; background: #f59e0b; margin-top: 6px; flex: none; }
.notif-msg { display: block; font-size: 12.5px; font-weight: 550; }
.notif-date { display: block; font-size: 11px; color: #94a3b8; margin-top: 1px; }
.notif-foot {
    display: block; text-align: center; padding: 11px; font-size: 12.5px; font-weight: 600;
    color: #4f46e5; border-top: 1px solid #eef1f6; text-decoration: none;
}

@media (max-width: 1040px) {
    .adm-nav { display: none; }
    .adm-burger { display: inline-flex; }
    .adm-user__meta { display: none; }
    .adm-header__inner { justify-content: space-between; }
    .adm-brand { flex: 1; }
}
</style>

<style>
.adm-submenu.ant-dropdown-menu {
    border-radius: 13px !important;
    padding: 6px !important;
    box-shadow: 0 16px 40px -12px rgba(15, 23, 42, .22) !important;
    border: 1px solid #eef1f6 !important;
    min-width: 196px;
}
.adm-submenu .ant-dropdown-menu-item {
    padding: 8px 12px !important;
    margin: 1px 0 !important;
    border-radius: 9px !important;
    font-size: 13px !important;
    font-weight: 500;
    color: #475569 !important;
    transition: background .14s ease, color .14s ease;
}
.adm-submenu .ant-dropdown-menu-item:hover {
    background: #eef2ff !important;
    color: #4338ca !important;
}
</style>