<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    CheckCircleTwoTone, CloseCircleTwoTone, ReloadOutlined, HomeOutlined,
} from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
    examen: { type: Object, required: true },
    mejorCalificacion: { type: Number, default: 0 },
    intentos: { type: Array, default: () => [] },
    preguntas: { type: Array, default: () => [] },
});

const aprobado = computed(() => props.examen.calificacion >= 70);
const correctas = computed(() => props.preguntas.filter((p) => p.es_correcta).length);
const incorrectas = computed(() => props.preguntas.length - correctas.value);

const filtro = ref('all');
const preguntasFiltradas = computed(() => {
    if (filtro.value === 'correct') return props.preguntas.filter((p) => p.es_correcta);
    if (filtro.value === 'incorrect') return props.preguntas.filter((p) => !p.es_correcta);
    return props.preguntas;
});

function reintentar() {
    router.visit(route('estudiante.simulador'));
}
</script>

<template>
    <EstudianteLayout title="Resultados del examen">
        <section class="result-hero" :class="aprobado ? 'is-ok' : 'is-bad'">
            <div class="result-hero__grid">
                <div>
                    <span class="sains-hero__eyebrow">
                        {{ examen.tipo_examen }} · Intento {{ examen.intento }}
                    </span>
                    <h1 class="sains-hero__title">
                        {{ aprobado ? '¡Felicidades! Aprobaste' : 'Sigue practicando' }}
                    </h1>
                    <p class="sains-hero__sub">
                        {{ aprobado
                            ? 'Demostraste un buen dominio del tema.'
                            : 'Cada error es una oportunidad para mejorar.' }}
                    </p>
                    <div class="result-hero__pills">
                        <span class="result-pill ok">{{ correctas }} correctas</span>
                        <span class="result-pill bad">{{ incorrectas }} incorrectas</span>
                    </div>
                </div>
                <div class="sains-hero__aside">
                    <div class="result-ring" :style="{ '--v': examen.calificacion }">
                        <div><b>{{ examen.calificacion }}%</b><span>calificación</span></div>
                    </div>
                </div>
            </div>
        </section>

        <div class="sains-stats sains-stats--3 sains-stagger" style="margin-top: 16px">
            <StatCard label="Tiempo total" :value="examen.tiempo || '—'" color="violet" />
            <StatCard label="Calificación" :value="`${examen.calificacion}%`" color="indigo" />
            <StatCard label="Mejor intento" :value="`${mejorCalificacion}%`" color="green" />
        </div>

        <a-card v-if="intentos.length > 1" :bordered="false" title="Evolución de tus intentos" style="margin-top: 16px">
            <div class="timeline">
                <div
                    v-for="it in intentos"
                    :key="it.id"
                    class="timeline-bar"
                    :class="{ current: it.id === examen.id }"
                >
                    <div class="timeline-fill" :style="{
                        height: Math.max(6, it.calificacion) + '%',
                        background: it.calificacion >= 70 ? '#10b981' : (it.calificacion >= 50 ? '#f59e0b' : '#ef4444'),
                    }"></div>
                    <span class="timeline-score">{{ it.calificacion }}%</span>
                    <span class="timeline-label">#{{ it.intento }}</span>
                </div>
            </div>
        </a-card>

        <a-card :bordered="false" title="Revisión de respuestas" style="margin-top: 16px">
            <template #extra>
                <a-radio-group v-model:value="filtro" size="small" button-style="solid">
                    <a-radio-button value="all">Todas</a-radio-button>
                    <a-radio-button value="correct">Correctas ({{ correctas }})</a-radio-button>
                    <a-radio-button value="incorrect">Incorrectas ({{ incorrectas }})</a-radio-button>
                </a-radio-group>
            </template>

            <a-collapse ghost>
                <a-collapse-panel v-for="(p, i) in preguntasFiltradas" :key="p.id">
                    <template #header>
                        <div class="q-head">
                            <CheckCircleTwoTone v-if="p.es_correcta" two-tone-color="#10b981" />
                            <CloseCircleTwoTone v-else two-tone-color="#ef4444" />
                            <span class="q-head-text">{{ i + 1 }}. {{ p.texto }}</span>
                        </div>
                    </template>
                    <div class="q-body">
                        <p>
                            <b>Tu respuesta:&nbsp;</b>
                            <span :class="p.es_correcta ? 'ok' : 'bad'">{{ p.respuesta_usuario || 'Sin responder' }}</span>
                        </p>
                        <p v-if="!p.es_correcta">
                            <b>Respuesta correcta:&nbsp;</b>
                            <span class="ok">{{ p.respuesta_correcta || '—' }}</span>
                        </p>
                        <a-alert v-if="p.justificacion" type="info" :message="p.justificacion" show-icon style="margin-top: 8px" />
                    </div>
                </a-collapse-panel>
            </a-collapse>
        </a-card>

        <div style="text-align: center; margin: 24px 0 8px">
            <a-space wrap>
                <a-button type="primary" @click="reintentar">
                    <template #icon><ReloadOutlined /></template>Otro simulador
                </a-button>
                <a-button @click="router.visit(route('estudiante.examenes'))">Mis exámenes</a-button>
                <a-button @click="router.visit(route('estudiante.dashboard'))">
                    <template #icon><HomeOutlined /></template>Inicio
                </a-button>
            </a-space>
        </div>
    </EstudianteLayout>
</template>

<style scoped>
.result-hero {
    position: relative; overflow: hidden; border-radius: 22px; padding: 26px 30px; margin-bottom: 22px; color: #fff;
    isolation: isolate;
}
.result-hero.is-ok { background: linear-gradient(135deg, #059669 0%, #10b981 55%, #34d399 100%); box-shadow: 0 20px 45px -22px rgba(16, 185, 129, .7); }
.result-hero.is-bad { background: linear-gradient(135deg, #dc2626 0%, #ef4444 55%, #fb7185 100%); box-shadow: 0 20px 45px -22px rgba(239, 68, 68, .6); }
.result-hero::before {
    content: ''; position: absolute; width: 260px; height: 260px; border-radius: 50%; z-index: -1;
    background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, .28), transparent 70%);
    top: -120px; right: -60px; animation: sains-blob 15s ease-in-out infinite;
}
.result-hero__grid { display: flex; align-items: center; justify-content: space-between; gap: 22px; flex-wrap: wrap; }
.result-hero__pills { display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap; }
.result-pill { font-size: 12.5px; font-weight: 650; padding: 5px 12px; border-radius: 999px; background: rgba(255, 255, 255, .18); }
.result-ring {
    width: 130px; height: 130px; border-radius: 50%; display: grid; place-items: center; padding: 9px;
    background: conic-gradient(#fff calc(var(--v, 0) * 1%), rgba(255, 255, 255, .22) 0);
}
.result-ring > div { width: 100%; height: 100%; border-radius: 50%; background: rgba(0, 0, 0, .16); display: grid; place-items: center; text-align: center; }
.result-ring b { font-size: 1.6rem; font-weight: 800; display: block; }
.result-ring span { font-size: 10px; opacity: .85; letter-spacing: .06em; text-transform: uppercase; }
@media (max-width: 640px) { .result-hero { padding: 22px; } .sains-hero__title { font-size: 1.35rem; } }

.timeline { display: flex; gap: 18px; align-items: flex-end; height: 160px; padding: 10px 4px; overflow-x: auto; }
.timeline-bar {
    display: flex; flex-direction: column; align-items: center; justify-content: flex-end;
    height: 100%; min-width: 52px; position: relative;
}
.timeline-fill { width: 26px; border-radius: 8px 8px 0 0; transition: height .3s ease; }
.timeline-score { font-size: 12px; font-weight: 700; margin-top: 6px; }
.timeline-label { font-size: 11px; color: #94a3b8; }
.timeline-bar.current .timeline-label { color: #4f46e5; font-weight: 700; }

.q-head { display: flex; gap: 8px; align-items: flex-start; }
.q-head-text { flex: 1; }
.q-body p { margin: 4px 0; }
.q-body .ok { color: #15803d; font-weight: 600; }
.q-body .bad { color: #b91c1c; font-weight: 600; }
</style>
