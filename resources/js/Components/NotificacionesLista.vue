<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    BellOutlined, CheckCircleFilled, CloseCircleFilled, ClockCircleFilled,
    DollarCircleFilled, FileTextFilled, PaperClipOutlined, InfoCircleFilled,
    CheckOutlined, DeleteOutlined, InboxOutlined, UserOutlined, DollarOutlined,
    FireOutlined, MailOutlined,
} from '@ant-design/icons-vue';
import { confirmAction } from '@/lib/notify';
import { ahora, desde } from '@/lib/tiempo';

const props = defineProps({
    notificaciones: { type: Array, default: () => [] },
});

const lista = ref(props.notificaciones.map((n) => ({ ...n })));
const filtroEstado = ref('todas');
const filtroTipo = ref('todos');

const ICONS = {
    check: CheckCircleFilled, close: CloseCircleFilled, clock: ClockCircleFilled,
    dollar: DollarCircleFilled, file: FileTextFilled, paperclip: PaperClipOutlined,
    bell: BellOutlined, info: InfoCircleFilled, mail: MailOutlined,
};
const iconFor = (n) => ICONS[n?.icono] || BellOutlined;

const noLeidasCount = computed(() => lista.value.filter((n) => !n.leida).length);
const leidasCount = computed(() => lista.value.filter((n) => n.leida).length);
const semanaCount = computed(() =>
    lista.value.filter((n) => ['Hoy', 'Ayer', 'Esta semana'].includes(n.grupo)).length);

const tipoChips = [
    { value: 'todos', label: 'Todas', icon: InboxOutlined },
    { value: 'pagos', label: 'Pagos', icon: DollarOutlined },
    { value: 'cuenta', label: 'Cuenta', icon: UserOutlined },
];
const tipoCount = (t) => (t === 'todos' ? lista.value.length : lista.value.filter((n) => n.grupo_tipo === t).length);

const visibles = computed(() => {
    const _ = ahora.value; // reactividad para el tiempo relativo
    return lista.value
        .filter((n) => (filtroEstado.value === 'sin_leer' ? !n.leida : true))
        .filter((n) => (filtroTipo.value === 'todos' ? true : n.grupo_tipo === filtroTipo.value))
        .map((n) => ({ ...n, rel: n.creada_iso ? desde(n.creada_iso) : n.fecha }));
});

const grupos = computed(() => {
    const out = [];
    let actual = null;
    for (const n of visibles.value) {
        const g = n.grupo || 'Antes';
        if (g !== actual) { actual = g; out.push({ label: g, items: [] }); }
        out[out.length - 1].items.push(n);
    }
    return out;
});

async function abrir(n) {
    if (!n.leida) {
        try { await axios.post(route('notificaciones.leer', n.id)); } catch (e) { /* noop */ }
        n.leida = true;
    }
    if (n.url) {
        if (n.url.startsWith('http')) window.location.href = n.url;
        else router.visit(n.url);
    }
}
async function marcarLeida(n) {
    if (n.leida) return;
    try { await axios.post(route('notificaciones.leer', n.id)); } catch (e) { /* noop */ }
    n.leida = true;
}
async function marcarTodas() {
    try { await axios.post(route('notificaciones.leer-todas')); } catch (e) { /* noop */ }
    lista.value.forEach((n) => (n.leida = true));
}
async function eliminar(n) {
    try { await axios.delete(route('notificaciones.eliminar', n.id)); } catch (e) { /* noop */ }
    lista.value = lista.value.filter((x) => x.id !== n.id);
}
function eliminarLeidas() {
    confirmAction({
        title: '¿Eliminar las notificaciones leídas?',
        content: `Se borrarán ${leidasCount.value} notificación${leidasCount.value === 1 ? '' : 'es'}.`,
        okText: 'Sí, eliminar',
        danger: true,
        onOk: async () => {
            try { await axios.delete(route('notificaciones.eliminar-leidas')); } catch (e) { /* noop */ }
            lista.value = lista.value.filter((n) => !n.leida);
        },
    });
}

// Refresco suave para no quedar desincronizada con la campana
let timer = null;
async function refrescar() {
    try {
        const { data } = await axios.get(route('notificaciones.feed'));
        const feed = data.items ?? [];
        const idsActuales = new Set(lista.value.map((n) => n.id));
        const nuevas = feed.filter((n) => !idsActuales.has(n.id));
        if (nuevas.length) {
            lista.value = [...nuevas.map((n) => ({ ...n })), ...lista.value].sort((a, b) => b.id - a.id);
        }
        const porId = Object.fromEntries(feed.map((n) => [n.id, n]));
        lista.value.forEach((n) => { if (porId[n.id]) n.leida = porId[n.id].leida; });
    } catch (e) { /* silencioso */ }
}
onMounted(() => { timer = setInterval(refrescar, 60000); });
onUnmounted(() => clearInterval(timer));
</script>

<template>
    <div class="ni">
        <!-- Resumen -->
        <div class="ni-stats sains-stats sains-stats--3">
            <div class="ni-stat">
                <span class="ni-stat__ic c-red"><FireOutlined /></span>
                <div><b>{{ noLeidasCount }}</b><small>Sin leer</small></div>
            </div>
            <div class="ni-stat">
                <span class="ni-stat__ic c-indigo"><ClockCircleFilled /></span>
                <div><b>{{ semanaCount }}</b><small>Esta semana</small></div>
            </div>
            <div class="ni-stat">
                <span class="ni-stat__ic c-slate"><InboxOutlined /></span>
                <div><b>{{ lista.length }}</b><small>En total</small></div>
            </div>
        </div>

        <div class="ni-panel">
            <div class="ni-panel__head">
                <div class="ni-chips">
                    <button
                        v-for="c in tipoChips" :key="c.value"
                        class="ni-chip" :class="{ on: filtroTipo === c.value }"
                        @click="filtroTipo = c.value"
                    >
                        <component :is="c.icon" />
                        {{ c.label }}
                        <span class="ni-chip__n">{{ tipoCount(c.value) }}</span>
                    </button>
                </div>
                <div class="ni-head-right">
                    <a-segmented
                        v-model:value="filtroEstado"
                        :options="[
                            { label: 'Todas', value: 'todas' },
                            { label: `Sin leer · ${noLeidasCount}`, value: 'sin_leer' },
                        ]"
                    />
                    <a-button v-if="noLeidasCount" type="text" size="small" class="ni-tbtn" @click="marcarTodas">
                        <template #icon><CheckOutlined /></template>Marcar todas
                    </a-button>
                    <a-button v-if="leidasCount" type="text" size="small" danger class="ni-tbtn" @click="eliminarLeidas">
                        <template #icon><DeleteOutlined /></template>Limpiar leídas
                    </a-button>
                </div>
            </div>

            <div class="ni-panel__body">
                <div v-if="!visibles.length" class="ni-empty">
                    <span class="ni-empty__ic"><BellOutlined /></span>
                    <b>{{ filtroEstado === 'sin_leer' ? '¡Todo leído!' : 'Sin notificaciones aquí' }}</b>
                    <small>{{ filtroEstado === 'sin_leer'
                        ? 'No tienes nada pendiente por revisar.'
                        : 'Te avisaremos en cuanto haya novedades.' }}</small>
                </div>

                <div v-for="g in grupos" :key="g.label" class="ni-group">
                    <div class="ni-daysep">
                        <span>{{ g.label }}</span>
                        <i></i>
                        <small>{{ g.items.length }}</small>
                    </div>
                    <div class="ni-cards sains-stagger">
                        <article
                            v-for="n in g.items" :key="n.id"
                            class="ni-card" :class="{ 'is-unread': !n.leida }"
                            @click="abrir(n)"
                        >
                            <span class="ni-card__bar" :class="`c-${n.color}`"></span>
                            <span class="ni-card__ic" :class="`c-${n.color}`">
                                <component :is="iconFor(n)" />
                                <i v-if="!n.leida" class="ni-card__pip"></i>
                            </span>
                            <div class="ni-card__main">
                                <div class="ni-card__row">
                                    <h4>{{ n.titulo }}</h4>
                                    <time :title="n.fecha_completa">{{ n.rel }}</time>
                                </div>
                                <p>{{ n.mensaje }}</p>
                                <span v-if="n.url" class="ni-card__cta">Ver detalle →</span>
                            </div>
                            <div class="ni-card__act" @click.stop>
                                <button v-if="!n.leida" title="Marcar como leída" @click="marcarLeida(n)">
                                    <CheckOutlined />
                                </button>
                                <button class="is-del" title="Eliminar" @click="eliminar(n)">
                                    <DeleteOutlined />
                                </button>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ni { max-width: 780px; margin: 0 auto; }

/* Resumen */
.ni-stats { margin-bottom: 18px; }
.ni-stat {
    display: flex; align-items: center; gap: 13px;
    background: #fff; border: 1px solid var(--sains-line); border-radius: 16px; padding: 14px 16px;
}
.ni-stat__ic {
    width: 42px; height: 42px; flex: none; border-radius: 12px; font-size: 18px; color: #fff;
    display: flex; align-items: center; justify-content: center;
}
.ni-stat__ic.c-red { background: linear-gradient(135deg, #fb7185, #ef4444); }
.ni-stat__ic.c-indigo { background: linear-gradient(135deg, #818cf8, #4f46e5); }
.ni-stat__ic.c-slate { background: linear-gradient(135deg, #94a3b8, #64748b); }
.ni-stat b { display: block; font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -.02em; line-height: 1; }
.ni-stat small { font-size: 12px; color: #94a3b8; }

/* Panel */
.ni-panel {
    background: #fff; border: 1px solid var(--sains-line); border-radius: 18px; overflow: hidden;
    box-shadow: var(--sains-shadow-sm);
}
.ni-panel__head {
    display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    padding: 14px 16px; border-bottom: 1px solid var(--sains-line);
    background: linear-gradient(180deg, #fbfbfe, #f7f8fd);
}
.ni-chips { display: flex; gap: 7px; flex-wrap: wrap; }
.ni-chip {
    display: inline-flex; align-items: center; gap: 6px; cursor: pointer;
    border: 1px solid var(--sains-line); background: #fff; color: #64748b;
    font-size: 12.5px; font-weight: 600; padding: 6px 12px; border-radius: 999px;
    transition: all .14s ease;
}
.ni-chip .anticon { font-size: 12px; }
.ni-chip__n {
    display: inline-flex; align-items: center; justify-content: center; min-width: 16px; height: 16px;
    padding: 0 4px; border-radius: 999px; background: #eef2ff; color: #4f46e5; font-size: 10.5px; font-weight: 700;
}
.ni-chip:hover { border-color: #c7d2fe; color: #4f46e5; }
.ni-chip.on { background: var(--sains-grad); border-color: transparent; color: #fff; box-shadow: 0 6px 14px -6px rgba(79, 70, 229, .5); }
.ni-chip.on .ni-chip__n { background: rgba(255, 255, 255, .25); color: #fff; }

.ni-head-right { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
.ni-tbtn { font-size: 12px; font-weight: 600; }

.ni-panel__body { padding: 8px 14px 16px; }

/* Empty */
.ni-empty {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: 54px 24px; text-align: center;
}
.ni-empty__ic {
    width: 58px; height: 58px; border-radius: 17px; margin-bottom: 8px;
    display: flex; align-items: center; justify-content: center; font-size: 24px;
    background: #eef2ff; color: #a5b4fc;
}
.ni-empty b { font-size: 15px; color: #0f172a; }
.ni-empty small { font-size: 12.5px; color: #94a3b8; }

/* Separador de día */
.ni-group { padding-top: 8px; }
.ni-daysep { display: flex; align-items: center; gap: 10px; margin: 6px 4px 10px; }
.ni-daysep span {
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8;
}
.ni-daysep i { flex: 1; height: 1px; background: linear-gradient(90deg, var(--sains-line), transparent); }
.ni-daysep small {
    font-size: 10.5px; font-weight: 700; color: #a5b4fc;
    background: #eef2ff; padding: 1px 7px; border-radius: 999px;
}

/* Tarjetas */
.ni-cards { display: flex; flex-direction: column; gap: 9px; }
.ni-card {
    position: relative; display: flex; align-items: flex-start; gap: 13px;
    padding: 13px 14px 13px 17px; border-radius: 14px; cursor: pointer;
    background: #fff; border: 1px solid var(--sains-line);
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease;
}
.ni-card:hover { transform: translateY(-2px); box-shadow: var(--sains-shadow-md); border-color: #d7ddec; }
.ni-card.is-unread { background: #fbfaff; border-color: #dcdcfb; }

.ni-card__bar {
    position: absolute; left: 0; top: 10px; bottom: 10px; width: 3px; border-radius: 0 3px 3px 0;
    opacity: 0; transition: opacity .15s ease;
}
.ni-card.is-unread .ni-card__bar { opacity: 1; }

.ni-card__ic {
    position: relative; width: 40px; height: 40px; flex: none; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 17px; color: #fff;
}
.ni-card__pip {
    position: absolute; top: -3px; right: -3px; width: 10px; height: 10px; border-radius: 50%;
    background: #ef4444; border: 2px solid #fff; animation: sains-pop .3s ease;
}
.ni-card__main { flex: 1; min-width: 0; }
.ni-card__row { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; }
.ni-card__row h4 { margin: 0; font-size: 14px; font-weight: 700; color: #0f172a; letter-spacing: -.01em; }
.ni-card__row time { font-size: 11px; color: #94a3b8; flex: none; }
.ni-card__main p { margin: 3px 0 0; font-size: 13px; color: #475569; line-height: 1.5; }
.ni-card__cta { display: inline-block; margin-top: 7px; font-size: 12px; font-weight: 700; color: #4f46e5; }

.ni-card__act { display: flex; flex-direction: column; gap: 6px; flex: none; }
.ni-card__act button {
    width: 28px; height: 28px; border: 0; border-radius: 8px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    background: #f1f5f9; color: #64748b; font-size: 12px;
    opacity: 0; transform: translateX(4px); transition: all .13s ease;
}
.ni-card:hover .ni-card__act button { opacity: 1; transform: none; }
.ni-card__act button:hover { background: #4f46e5; color: #fff; }
.ni-card__act button.is-del:hover { background: #ef4444; }
@media (hover: none) { .ni-card__act button { opacity: 1; transform: none; } }

/* Paletas de color (icono + barra + fondo del pip) */
.c-green  { background: linear-gradient(135deg, #34d399, #10b981); }
.c-red    { background: linear-gradient(135deg, #f87171, #ef4444); }
.c-amber  { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
.c-blue   { background: linear-gradient(135deg, #60a5fa, #3b82f6); }
.c-violet { background: linear-gradient(135deg, #a78bfa, #8b5cf6); }
.c-indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }
.ni-card__bar.c-green  { background: #10b981; }
.ni-card__bar.c-red    { background: #ef4444; }
.ni-card__bar.c-amber  { background: #f59e0b; }
.ni-card__bar.c-blue   { background: #3b82f6; }
.ni-card__bar.c-violet { background: #8b5cf6; }
.ni-card__bar.c-indigo { background: #6366f1; }

@media (max-width: 640px) {
    .ni-panel__head { flex-direction: column; align-items: stretch; }
    .ni-head-right { justify-content: space-between; }
}
</style>
