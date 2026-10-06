<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import axios from 'axios';
import {
    BellOutlined, CheckCircleFilled, CloseCircleFilled, ClockCircleFilled,
    DollarCircleFilled, FileTextFilled, PaperClipOutlined, InfoCircleFilled,
    CheckOutlined, SoundOutlined, SoundFilled,
} from '@ant-design/icons-vue';
import { message } from '@/lib/notify';
import { ahora, desde } from '@/lib/tiempo';
import { reproducirDing, sonidoActivo, setSonido } from '@/lib/sound';

defineProps({
    scope: { type: String, default: 'estudiante' },
});

const page = usePage();
const open = ref(false);
const items = ref([]);
const noLeidas = ref(page.props.notificaciones?.no_leidas ?? 0);
const pulso = ref(false);
const conSonido = ref(sonidoActivo());
function toggleSonido() {
    conSonido.value = !conSonido.value;
    setSonido(conSonido.value);
    if (conSonido.value) reproducirDing({ force: true });
}
let timer = null;
let primeraCarga = true;
let ultimoIdVisto = 0;

const ICONS = {
    check: CheckCircleFilled, close: CloseCircleFilled, clock: ClockCircleFilled,
    dollar: DollarCircleFilled, file: FileTextFilled, paperclip: PaperClipOutlined,
    bell: BellOutlined, info: InfoCircleFilled,
};
const iconFor = (n) => ICONS[n?.icono] || BellOutlined;

// tiempo relativo reactivo (se refresca con el reloj compartido)
const conTiempo = computed(() => {
    const _ = ahora.value; // dependencia reactiva
    return items.value.map((n) => ({ ...n, rel: n.creada_iso ? desde(n.creada_iso) : n.fecha }));
});

async function cargar({ avisar = true } = {}) {
    try {
        const { data } = await axios.get(route('notificaciones.feed'));
        const nuevas = data.items ?? [];

        // ¿llegó alguna realmente nueva desde la última vez?
        const maxId = nuevas.reduce((m, n) => Math.max(m, n.id), 0);
        const hayNueva = !primeraCarga && maxId > ultimoIdVisto;

        items.value = nuevas;
        noLeidas.value = data.no_leidas ?? 0;
        ultimoIdVisto = Math.max(ultimoIdVisto, maxId);

        if (hayNueva && avisar && !open.value) {
            const n = nuevas.find((x) => x.id === maxId);
            pulso.value = true;
            setTimeout(() => (pulso.value = false), 1600);
            message.info(n?.titulo || 'Tienes una notificación nueva');
            reproducirDing();
        }
        primeraCarga = false;
    } catch (e) { /* silencioso */ }
}

async function marcarLeida(n) {
    if (n.leida) return;
    try { await axios.post(route('notificaciones.leer', n.id)); } catch (e) { /* noop */ }
    n.leida = true;
    noLeidas.value = Math.max(0, noLeidas.value - 1);
}
async function abrir(n) {
    await marcarLeida(n);
    open.value = false;
    if (n.url) {
        if (n.url.startsWith('http')) window.location.href = n.url;
        else router.visit(n.url);
    }
}
async function marcarTodas() {
    try { await axios.post(route('notificaciones.leer-todas')); } catch (e) { /* noop */ }
    items.value.forEach((n) => (n.leida = true));
    noLeidas.value = 0;
}

// Sincroniza el badge en cada navegación (prop compartido de Inertia)
watch(() => page.props.notificaciones?.no_leidas, (v) => {
    if (typeof v === 'number') noLeidas.value = v;
});

onMounted(() => {
    cargar({ avisar: false });
    timer = setInterval(() => cargar(), 45000);
});
onUnmounted(() => clearInterval(timer));
</script>

<template>
    <a-dropdown v-model:open="open" placement="bottomRight" :trigger="['click']">
        <button class="nb-btn" :class="{ 'is-pulse': pulso }" aria-label="Notificaciones" @click="!open && cargar({ avisar: false })">
            <a-badge :count="noLeidas" :offset="[-2, 3]" size="small">
                <BellOutlined style="font-size: 17px" />
            </a-badge>
        </button>
        <template #overlay>
            <div class="nb-panel">
                <div class="nb-head">
                    <strong>Notificaciones <span v-if="noLeidas" class="nb-badge">{{ noLeidas }}</span></strong>
                    <span class="nb-head__actions">
                        <button
                            class="nb-sound"
                            :class="{ 'is-off': !conSonido }"
                            :title="conSonido ? 'Silenciar sonido' : 'Activar sonido'"
                            @click.stop="toggleSonido"
                        >
                            <component :is="conSonido ? SoundFilled : SoundOutlined" />
                        </button>
                        <button v-if="noLeidas" class="nb-mark" @click.stop="marcarTodas">Marcar todas</button>
                    </span>
                </div>
                <div class="nb-body">
                    <div v-if="!items.length" class="nb-empty">
                        <span class="nb-empty__ic"><BellOutlined /></span>
                        <span>Todo tranquilo por aquí</span>
                        <small>Te avisaremos cuando haya novedades</small>
                    </div>
                    <div
                        v-for="n in conTiempo" :key="n.id"
                        class="nb-item" :class="{ 'is-unread': !n.leida }"
                        role="button" @click="abrir(n)"
                    >
                        <span class="nb-ico" :class="`c-${n.color}`"><component :is="iconFor(n)" /></span>
                        <span class="nb-txt">
                            <span class="nb-title">{{ n.titulo }}</span>
                            <span class="nb-msg">{{ n.mensaje }}</span>
                            <span class="nb-date">{{ n.rel }}</span>
                        </span>
                        <button v-if="!n.leida" class="nb-check" title="Marcar como leída" @click.stop="marcarLeida(n)">
                            <CheckOutlined />
                        </button>
                    </div>
                </div>
                <Link :href="route('notificaciones.index')" class="nb-foot" @click="open = false">Ver todas →</Link>
            </div>
        </template>
    </a-dropdown>
</template>

<style scoped>
.nb-btn {
    display: flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border: 0; border-radius: 11px; cursor: pointer;
    background: rgba(99, 102, 241, .08); color: #4f46e5;
    transition: background .15s ease;
}
.nb-btn:hover { background: rgba(99, 102, 241, .16); }
.nb-btn.is-pulse { animation: nb-shake .5s ease-in-out 3; }
@keyframes nb-shake {
    0%, 100% { transform: rotate(0); }
    25% { transform: rotate(-12deg); }
    75% { transform: rotate(12deg); }
}

.nb-panel {
    width: 372px; max-width: 92vw; background: #fff; border-radius: 14px;
    box-shadow: 0 18px 44px -14px rgba(15, 23, 42, .28); border: 1px solid #eef1f6; overflow: hidden;
}
.nb-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 15px; border-bottom: 1px solid #eef1f6; background: #fafbfd; font-size: 13.5px;
}
.nb-head__actions { display: inline-flex; align-items: center; gap: 8px; }
.nb-mark { border: 0; background: transparent; color: #4f46e5; font-size: 12px; font-weight: 600; cursor: pointer; }
.nb-mark:hover { text-decoration: underline; }
.nb-sound {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border: 0; border-radius: 7px; cursor: pointer;
    background: #eef2ff; color: #4f46e5; font-size: 12px; transition: background .14s ease, color .14s ease;
}
.nb-sound:hover { background: #4f46e5; color: #fff; }
.nb-sound.is-off { background: #f1f5f9; color: #94a3b8; }
.nb-badge {
    display: inline-flex; align-items: center; justify-content: center; min-width: 18px; height: 18px;
    padding: 0 5px; border-radius: 999px; background: #ef4444; color: #fff; font-size: 10.5px; font-weight: 700;
    margin-left: 5px; vertical-align: middle;
}
.nb-check {
    width: 24px; height: 24px; flex: none; border: 0; border-radius: 7px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; align-self: center;
    background: #eef2ff; color: #4f46e5; font-size: 11px; opacity: 0; transition: opacity .12s ease;
}
.nb-item:hover .nb-check { opacity: 1; }
.nb-check:hover { background: #4f46e5; color: #fff; }

.nb-body { max-height: 384px; overflow-y: auto; }
.nb-empty {
    display: flex; flex-direction: column; align-items: center; gap: 3px;
    padding: 34px 20px; text-align: center; color: #64748b; font-size: 13px;
}
.nb-empty__ic {
    width: 44px; height: 44px; border-radius: 13px; margin-bottom: 6px;
    display: flex; align-items: center; justify-content: center; font-size: 19px;
    background: #eef2ff; color: #a5b4fc;
}
.nb-empty small { font-size: 11.5px; color: #94a3b8; }

.nb-item {
    display: flex; gap: 11px; width: 100%; text-align: left; border: 0; background: transparent;
    padding: 12px 15px; border-bottom: 1px solid #f4f6fa; cursor: pointer; position: relative;
    transition: background .12s ease;
}
.nb-item:hover { background: #f7f8fc; }
.nb-item.is-unread { background: #f5f3ff; }
.nb-item.is-unread::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: #6366f1;
}
.nb-item.is-unread:hover { background: #eef2ff; }
.nb-ico {
    width: 34px; height: 34px; flex: none; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 15px; color: #fff;
}
.nb-ico.c-green { background: linear-gradient(135deg, #34d399, #10b981); }
.nb-ico.c-red { background: linear-gradient(135deg, #f87171, #ef4444); }
.nb-ico.c-amber { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
.nb-ico.c-blue { background: linear-gradient(135deg, #60a5fa, #3b82f6); }
.nb-ico.c-violet { background: linear-gradient(135deg, #a78bfa, #8b5cf6); }
.nb-ico.c-indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }
.nb-txt { display: flex; flex-direction: column; gap: 1px; min-width: 0; flex: 1; }
.nb-title { font-size: 12.8px; font-weight: 650; color: #0f172a; }
.nb-msg { font-size: 12px; color: #64748b; line-height: 1.4; }
.nb-date { font-size: 10.5px; color: #94a3b8; margin-top: 2px; }

.nb-foot {
    display: block; text-align: center; padding: 11px; font-size: 12.5px; font-weight: 600;
    color: #4f46e5; border-top: 1px solid #eef1f6; text-decoration: none;
}
.nb-foot:hover { background: #f7f8fc; }
</style>
