<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { Modal } from 'ant-design-vue';
import {
    ClockCircleOutlined, CheckCircleOutlined, ArrowLeftOutlined,
    ArrowRightOutlined, FlagOutlined, CheckOutlined,
} from '@ant-design/icons-vue';
import { message } from '@/lib/notify';

const props = defineProps({
    examen: { type: Object, required: true },
    preguntas: { type: Array, default: () => [] },
    intento: { type: [Number, String], default: 1 },
    responderUrl: { type: String, required: true },
    planActivo: { type: Boolean, default: true },
    maxPreguntas: { type: Number, default: null },
    intentosRestantes: { type: Number, default: null },
    storageKey: { type: String, default: 'quiz' },
    accent: { type: String, default: '#4f46e5' },
});

const lsKey = `${props.storageKey}_${props.examen.id}`;
const total = props.preguntas.length;
const current = ref(0);
const respuestas = reactive({});
const enviando = ref(false);

const limiteSegundos = Math.max(60, (Number(props.examen.tiempo) || 60) * 60);
const restante = ref(limiteSegundos);
let timer = null;

const respondidas = computed(() => Object.keys(respuestas).length);
const progreso = computed(() => (total ? Math.round((respondidas.value / total) * 100) : 0));
const preguntaActual = computed(() => props.preguntas[current.value] ?? null);
const esUltima = computed(() => current.value === total - 1);

const reloj = computed(() => {
    const m = Math.floor(restante.value / 60);
    const s = restante.value % 60;
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
});
const relojBajo = computed(() => restante.value <= 300);
const tiempoTranscurrido = computed(() => Math.round(((limiteSegundos - restante.value) / limiteSegundos) * 100));

// Avisos de tiempo (una sola vez cada uno)
const avisos = reactive({ mitad: false, tresCuartos: false, cinco: false, uno: false });
function revisarAvisos() {
    const t = restante.value;
    if (!avisos.mitad && t <= limiteSegundos / 2 && t > limiteSegundos / 4) {
        avisos.mitad = true;
        message.warning('Ya transcurrió la mitad del tiempo. Llevas ' + respondidas.value + ' de ' + total + ' preguntas.', 5);
    }
    if (!avisos.tresCuartos && t <= limiteSegundos / 4 && t > 300) {
        avisos.tresCuartos = true;
        message.warning('Te queda una cuarta parte del tiempo. Revisa las preguntas que te faltan.', 5);
    }
    if (!avisos.cinco && t <= 300 && t > 60) {
        avisos.cinco = true;
        message.error('¡Últimos 5 minutos! Asegúrate de enviar tu examen.', 6);
    }
    if (!avisos.uno && t <= 60) {
        avisos.uno = true;
        message.error('¡Último minuto! El examen se enviará solo al terminar el tiempo.', 6);
    }
}

function persistir() {
    try {
        localStorage.setItem(`${lsKey}_r`, JSON.stringify(respuestas));
        localStorage.setItem(`${lsKey}_t`, String(restante.value));
    } catch (e) { /* ignore */ }
}

function limpiarPersistencia() {
    try {
        localStorage.removeItem(`${lsKey}_r`);
        localStorage.removeItem(`${lsKey}_t`);
    } catch (e) { /* ignore */ }
}

function elegir(preguntaId, valor) {
    if (!props.planActivo && props.maxPreguntas
        && respondidas.value >= props.maxPreguntas && !respuestas[preguntaId]) {
        message.warning(`En modo básico solo puedes responder ${props.maxPreguntas} preguntas.`);
        return;
    }
    respuestas[preguntaId] = valor;
    persistir();
}

function ir(i) {
    if (i < 0 || i > total - 1) return;
    current.value = i;
}

function finalizar() {
    if (enviando.value) return;
    const faltan = total - respondidas.value;
    if (faltan > 0) {
        Modal.confirm({
            title: 'Preguntas sin responder',
            content: `Has respondido ${respondidas.value} de ${total}. ¿Finalizar de todas formas?`,
            okText: 'Sí, finalizar',
            cancelText: 'Seguir respondiendo',
            centered: true,
            onOk: enviar,
        });
        return;
    }
    enviar();
}

async function enviar() {
    if (enviando.value) return;
    enviando.value = true;
    clearInterval(timer);
    const usados = Math.max(0, limiteSegundos - restante.value);
    try {
        const { data } = await axios.post(props.responderUrl, {
            examen_id: props.examen.id,
            respuestas: { ...respuestas },
            tiempo_utilizado_segundos: usados,
        });
        if (data.success) {
            limpiarPersistencia();
            message.success(`Examen enviado — ${data.calificacion}%`);
            router.visit(data.redirect);
        } else {
            message.error(data.message || 'No se pudo enviar el examen');
            enviando.value = false;
            arrancarTimer();
        }
    } catch (e) {
        message.error(e.response?.data?.message || 'Error al enviar el examen');
        enviando.value = false;
        arrancarTimer();
    }
}

function arrancarTimer() {
    clearInterval(timer);
    timer = setInterval(() => {
        if (restante.value > 0) {
            restante.value -= 1;
            if (restante.value % 15 === 0) persistir();
            revisarAvisos();
        }
        if (restante.value <= 0) {
            clearInterval(timer);
            message.info('Tiempo agotado, enviando respuestas…');
            enviar();
        }
    }, 1000);
}

function cambiarExamen(id) {
    if (id === props.examen.id) return;
    const go = () => router.visit(route('estudiante.simulador.cargar', id));
    if (respondidas.value > 0) {
        Modal.confirm({
            title: '¿Cambiar de examen?',
            content: 'Perderás las respuestas que llevas en este simulador.',
            okText: 'Sí, cambiar',
            okType: 'danger',
            cancelText: 'Cancelar',
            centered: true,
            onOk: go,
        });
    } else {
        go();
    }
}
defineExpose({ cambiarExamen });

onMounted(() => {
    try {
        const savedR = JSON.parse(localStorage.getItem(`${lsKey}_r`) || 'null');
        if (savedR && typeof savedR === 'object') {
            Object.entries(savedR).forEach(([k, v]) => { respuestas[k] = v; });
        }
        const savedT = parseInt(localStorage.getItem(`${lsKey}_t`) || '0', 10);
        if (savedT > 0 && savedT <= limiteSegundos) restante.value = savedT;
    } catch (e) { /* ignore */ }
    if (total > 0) arrancarTimer();
});

onBeforeUnmount(() => clearInterval(timer));
</script>

<template>
    <div class="quiz">
        <div class="quiz-top" :style="{ '--accent': accent }">
            <div>
                <div class="quiz-kicker">Intento #{{ intento }} · {{ examen.tipo_examen }}</div>
                <h1 class="quiz-title">{{ examen.nombre }}</h1>
                <div class="quiz-meta">
                    <span>{{ total }} preguntas</span>
                    <span>·</span>
                    <span>{{ examen.tiempo }} min</span>
                </div>
            </div>
            <div class="quiz-timer" :class="{ low: relojBajo, half: !relojBajo && avisos.mitad }">
                <ClockCircleOutlined />
                <div>
                    <span class="quiz-timer-label">
                        {{ relojBajo ? '¡Poco tiempo!' : avisos.mitad ? 'Mitad del tiempo' : 'Tiempo restante' }}
                    </span>
                    <span class="quiz-timer-value">{{ reloj }}</span>
                    <span class="quiz-timer-bar"><i :style="{ width: tiempoTranscurrido + '%' }"></i></span>
                </div>
            </div>
        </div>

        <a-alert
            v-if="!planActivo"
            type="warning"
            show-icon
            style="margin-bottom: 16px"
            :message="`Modo básico — ${maxPreguntas} preguntas máximo` + (intentosRestantes != null ? ` · ${intentosRestantes} intentos restantes` : '')"
        >
            <template #action>
                <a-button size="small" type="primary" @click="router.visit(route('estudiante.checkout'))">Mejorar plan</a-button>
            </template>
        </a-alert>

        <slot name="selector" :cambiar="cambiarExamen" />

        <a-card :bordered="false" class="quiz-progress-card">
            <div class="quiz-progress-head">
                <span>Progreso del examen</span>
                <span><b>{{ respondidas }}</b> / {{ total }} · {{ progreso }}%</span>
            </div>
            <a-progress :percent="progreso" :stroke-color="accent" :show-info="false" />
            <div class="quiz-nav-grid">
                <button
                    v-for="(p, i) in preguntas"
                    :key="p.id"
                    class="quiz-nav-dot"
                    :class="{ done: respuestas[p.id], active: i === current }"
                    @click="ir(i)"
                >{{ i + 1 }}</button>
            </div>
        </a-card>

        <Transition name="q" mode="out-in">
        <a-card v-if="preguntaActual" :key="current" :bordered="false" class="quiz-question">
            <div class="quiz-question-head">
                <span class="quiz-question-num">Pregunta {{ current + 1 }} <i>de {{ total }}</i></span>
            </div>
            <p class="quiz-question-text">{{ preguntaActual.pregunta }}</p>
            <div class="quiz-options">
                <label
                    v-for="(op, oi) in preguntaActual.opciones"
                    :key="op.valor"
                    class="quiz-option"
                    :class="{ selected: respuestas[preguntaActual.id] === op.valor }"
                >
                    <input
                        type="radio"
                        :name="`p_${preguntaActual.id}`"
                        :value="op.valor"
                        :checked="respuestas[preguntaActual.id] === op.valor"
                        @change="elegir(preguntaActual.id, op.valor)"
                    />
                    <span class="quiz-option-letter">{{ 'ABCDE'[oi] }}</span>
                    <span class="quiz-option-text">{{ op.texto }}</span>
                    <span class="quiz-option-mark"><CheckOutlined /></span>
                </label>
            </div>

            <div class="quiz-actions">
                <a-button :disabled="current === 0" @click="ir(current - 1)">
                    <template #icon><ArrowLeftOutlined /></template>Anterior
                </a-button>
                <a-button v-if="!esUltima" type="primary" @click="ir(current + 1)">
                    Siguiente <ArrowRightOutlined />
                </a-button>
                <a-button v-else type="primary" :loading="enviando" @click="finalizar">
                    <template #icon><FlagOutlined /></template>Finalizar examen
                </a-button>
            </div>
        </a-card>
        </Transition>

        <div class="quiz-submit">
            <a-button type="primary" size="large" :loading="enviando" @click="finalizar">
                <template #icon><CheckCircleOutlined /></template>
                Finalizar y enviar respuestas
            </a-button>
        </div>
    </div>
</template>

<style scoped>
.quiz { max-width: 820px; margin: 0 auto; }
.quiz-top {
    position: relative; overflow: hidden; isolation: isolate;
    display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;
    background: linear-gradient(135deg, var(--accent), #6366f1);
    color: #fff; border-radius: 20px; padding: 24px 28px; margin-bottom: 18px;
    box-shadow: 0 20px 45px -24px var(--accent);
}
.quiz-top::before {
    content: ''; position: absolute; width: 240px; height: 240px; border-radius: 50%; z-index: -1;
    background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, .25), transparent 70%);
    top: -110px; right: -50px; animation: sains-blob 16s ease-in-out infinite;
}
.quiz-kicker { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; opacity: .85; }
.quiz-title { font-size: 1.4rem; font-weight: 700; margin: 4px 0 6px; color: #fff; }
.quiz-meta { display: flex; gap: 8px; font-size: 13px; opacity: .9; }
.quiz-timer {
    display: flex; align-items: center; gap: 12px; min-width: 168px;
    background: rgba(255,255,255,.16); border-radius: 14px; padding: 10px 18px; font-size: 20px;
    transition: background .3s ease;
}
.quiz-timer.half { background: rgba(245, 158, 11, .92); }
.quiz-timer.low { background: rgba(239,68,68,.92); animation: pulse 1s infinite; }
.quiz-timer > div { display: flex; flex-direction: column; line-height: 1.1; flex: 1; }
.quiz-timer-label { font-size: 10px; opacity: .85; text-transform: uppercase; letter-spacing: .05em; }
.quiz-timer-value { font-size: 1.5rem; font-weight: 700; letter-spacing: 1px; font-variant-numeric: tabular-nums; }
.quiz-timer-bar {
    display: block; height: 4px; border-radius: 999px; margin-top: 6px;
    background: rgba(255, 255, 255, .28); overflow: hidden;
}
.quiz-timer-bar i { display: block; height: 100%; background: #fff; border-radius: 999px; transition: width 1s linear; }
@keyframes pulse { 50% { opacity: .85; } }

.quiz-progress-card { margin-bottom: 16px; }
.quiz-progress-head { display: flex; justify-content: space-between; font-size: 13px; color: #64748b; margin-bottom: 6px; }
.quiz-nav-grid { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 14px; }
.quiz-nav-dot {
    width: 36px; height: 36px; border-radius: 9px; border: 1px solid #e2e8f0;
    background: #fff; font-weight: 600; font-size: 13px; cursor: pointer; color: #475569;
    transition: all .15s ease;
}
.quiz-nav-dot.done { background: #dcfce7; border-color: #86efac; color: #15803d; }
.quiz-nav-dot.active { border-color: var(--accent, #4f46e5); box-shadow: 0 0 0 2px rgba(79,70,229,.18); }

.quiz-question-head { margin-bottom: 12px; }
.quiz-question-num { font-weight: 700; color: #0f172a; }
.quiz-question-num i { color: #94a3b8; font-weight: 400; font-style: normal; }
.quiz-question-text { font-size: 1.08rem; line-height: 1.55; color: #1e293b; margin-bottom: 18px; }
.quiz-options { display: flex; flex-direction: column; gap: 10px; }
.quiz-option {
    display: flex; align-items: center; gap: 14px; padding: 13px 16px;
    border: 1px solid #e2e8f0; border-radius: 13px; cursor: pointer; transition: all .15s ease;
}
.quiz-option:hover { background: #f8fafc; transform: translateX(2px); }
.quiz-option.selected { border-color: var(--accent, #4f46e5); background: #eef2ff; }
.quiz-option input { position: absolute; opacity: 0; pointer-events: none; }
.quiz-option-letter {
    width: 28px; height: 28px; border-radius: 8px; flex: none;
    display: flex; align-items: center; justify-content: center;
    font-size: 12.5px; font-weight: 700; color: #64748b;
    background: #f1f5f9; transition: all .15s ease;
}
.quiz-option.selected .quiz-option-letter { background: var(--accent, #4f46e5); color: #fff; }
.quiz-option-mark {
    width: 22px; height: 22px; border-radius: 50%; border: 2px solid #cbd5e1; flex: none;
    display: flex; align-items: center; justify-content: center; font-size: 11px; color: transparent;
    transition: all .15s ease;
}
.quiz-option.selected .quiz-option-mark {
    border-color: var(--accent, #4f46e5); background: var(--accent, #4f46e5); color: #fff;
}
.quiz-option-text { flex: 1; font-size: .95rem; color: #1e293b; }
.quiz-actions { display: flex; justify-content: space-between; gap: 12px; margin-top: 22px; }
.quiz-submit { text-align: center; margin: 24px 0 8px; }

/* Transición entre preguntas */
.q-enter-active, .q-leave-active { transition: opacity .22s ease, transform .22s cubic-bezier(.16,1,.3,1); }
.q-enter-from { opacity: 0; transform: translateX(18px); }
.q-leave-to { opacity: 0; transform: translateX(-18px); }

@media (max-width: 640px) {
    .quiz-top { padding: 18px; }
    .quiz-actions { flex-wrap: wrap; }
}
</style>
