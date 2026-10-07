<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { RocketOutlined, SafetyCertificateOutlined, ClockCircleOutlined } from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';
import QuizRunner from '@/Components/QuizRunner.vue';

const props = defineProps({
    examen: { type: Object, required: true },
    preguntas: { type: Array, default: () => [] },
    intento: { type: [Number, String], default: 1 },
    planActivo: { type: Boolean, default: true },
    intentosRestantes: { type: Number, default: null },
    maxPreguntas: { type: Number, default: null },
    maxIntentos: { type: Number, default: null },
    limiteAlcanzado: { type: Boolean, default: false },
    examenes: { type: Array, default: () => [] },
    responderUrl: { type: String, required: true },
    // Examen de prueba (ISSFAM): el estudiante elige 50, 150 o 250 preguntas.
    elegirTamano: { type: Boolean, default: false },
    tamanos: { type: Array, default: () => [50, 150, 250] },
    tamano: { type: Number, default: null },
    disponibles: { type: Number, default: null },
    certificacion: { type: Boolean, default: false },
});

// Muchos simuladores comparten el mismo nombre ("Simulación"); si es así,
// los numeramos para poder distinguirlos en el selector.
const nombresColisionan = computed(() => {
    const n = new Set(props.examenes.map((e) => e.nombre));
    return n.size < props.examenes.length;
});
const etiqueta = (e, i) => (nombresColisionan.value ? `Simulador ${i + 1}` : e.nombre);

const descTamano = { 50: 'Práctica rápida', 150: 'Práctica intermedia', 250: 'Examen completo' };
function minutos(n) {
    const total = Number(props.examen.numero_preguntas) || props.disponibles || n;
    const base = Number(props.examen.tiempo) || 0;
    return base ? Math.max(10, Math.ceil((base * n) / total)) : Math.ceil(n * 1.2);
}
function empezar(n) {
    router.visit(route('estudiante.simulador.cargar', { id: props.examen.id, preguntas: n }));
}
const pocas = computed(() => props.tamano && props.preguntas.length < props.tamano);
</script>

<template>
    <EstudianteLayout :title="certificacion ? 'Examen para certificar' : 'Examen de prueba'">
        <a-result
            v-if="limiteAlcanzado"
            status="info"
            title="Has alcanzado el límite de intentos gratuitos"
            :sub-title="`El modo básico permite ${maxIntentos} intentos por simulador. Mejora a Premium para intentos ilimitados.`"
        >
            <template #extra>
                <a-button type="primary" @click="router.visit(route('estudiante.checkout'))">Mejorar a Premium</a-button>
                <a-button @click="router.visit(route('estudiante.dashboard'))">Volver al inicio</a-button>
            </template>
        </a-result>

        <!-- Paso 1: elegir el tamaño del examen de prueba -->
        <template v-else-if="elegirTamano">
            <section class="sains-hero">
                <div class="sains-hero__grid">
                    <div>
                        <span class="sains-hero__eyebrow"><RocketOutlined /> Examen de prueba</span>
                        <h1 class="sains-hero__title">¿De cuántas preguntas quieres tu examen?</h1>
                        <p class="sains-hero__sub">Practica con un examen de prueba; las preguntas se eligen al azar en cada intento.</p>
                    </div>
                </div>
            </section>

            <a-card v-if="examenes.length > 1" :bordered="false" style="margin-bottom: 16px">
                <div style="font-size: 13px; color: #64748b; margin-bottom: 10px">Simulador</div>
                <div class="sim-picker">
                    <button v-for="(e, i) in examenes" :key="e.id" class="sim-chip" :class="{ on: e.id === examen.id }"
                        @click="router.visit(route('estudiante.simulador.cargar', e.id))">
                        <b>{{ etiqueta(e, i) }}</b>
                        <small>{{ e.numero_preguntas }} preguntas en el banco</small>
                    </button>
                </div>
            </a-card>

            <div class="tam-grid">
                <button v-for="n in tamanos" :key="n" class="tam" @click="empezar(n)">
                    <span class="tam__n">{{ n }}</span>
                    <span class="tam__lbl">preguntas</span>
                    <span class="tam__desc">{{ descTamano[n] ?? '' }}</span>
                    <span class="tam__time"><ClockCircleOutlined /> ~{{ minutos(n) }} min</span>
                    <span v-if="disponibles !== null && disponibles < n" class="tam__warn">
                        El banco tiene {{ disponibles }}; se usarán todas.
                    </span>
                </button>
            </div>
        </template>

        <a-empty v-else-if="!preguntas.length" description="Este examen no tiene preguntas configuradas." />

        <template v-else>
            <a-alert v-if="certificacion" type="warning" show-icon style="margin-bottom: 14px"
                message="Examen para certificar"
                :description="`Son ${preguntas.length} preguntas. Contesta con calma: tu resultado quedará registrado para tu certificación.`">
                <template #icon><SafetyCertificateOutlined /></template>
            </a-alert>
            <a-alert v-else-if="pocas" type="info" show-icon style="margin-bottom: 14px"
                :message="`Elegiste ${tamano} preguntas, pero este simulador solo tiene ${preguntas.length}; se usarán todas.`" />

            <QuizRunner
                :examen="examen"
                :preguntas="preguntas"
                :intento="intento"
                :responder-url="responderUrl"
                :plan-activo="planActivo"
                :max-preguntas="maxPreguntas"
                :intentos-restantes="intentosRestantes"
                :tamano="tamano"
                :storage-key="certificacion ? 'certificacion' : `simulador${tamano ? '_' + tamano : ''}`"
            >
                <template #selector="{ cambiar }">
                    <a-card v-if="!certificacion && examenes.length > 1" :bordered="false" style="margin-bottom: 16px">
                        <div style="font-size: 13px; color: #64748b; margin-bottom: 10px">Elige un simulador</div>
                        <div class="sim-picker">
                            <button
                                v-for="(e, i) in examenes"
                                :key="e.id"
                                class="sim-chip"
                                :class="{ on: e.id === examen.id }"
                                @click="cambiar(e.id)"
                            >
                                <b>{{ etiqueta(e, i) }}</b>
                                <small>{{ e.numero_preguntas }} preguntas · {{ e.tiempo }} min</small>
                            </button>
                        </div>
                    </a-card>
                </template>
            </QuizRunner>
        </template>
    </EstudianteLayout>
</template>

<style scoped>
.sim-picker { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; }
.sim-chip {
    text-align: left; cursor: pointer; padding: 12px 14px; border-radius: 12px;
    border: 1px solid var(--sains-line); background: #fff;
    display: flex; flex-direction: column; gap: 2px; transition: all .15s ease;
}
.sim-chip:hover { border-color: #c5d5e9; background: #f8faff; }
.sim-chip.on { border-color: #1851ad; background: #eef3f9; box-shadow: 0 0 0 2px rgba(24, 81, 173, .12); }
.sim-chip b { font-size: 13.5px; color: #0f172a; }
.sim-chip small { font-size: 11.5px; color: #64748b; }

.tam-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
.tam {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: 28px 20px; border-radius: 18px; cursor: pointer; text-align: center;
    background: #fff; border: 1.5px solid var(--sains-line); box-shadow: var(--sains-shadow-sm);
    transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
}
.tam:hover { transform: translateY(-3px); border-color: var(--sains-primary); box-shadow: var(--sains-shadow-md); }
.tam__n { font-size: 46px; font-weight: 800; line-height: 1; color: var(--sains-primary); letter-spacing: -.03em; }
.tam__lbl { font-size: 13px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: .06em; }
.tam__desc { margin-top: 8px; font-size: 14px; font-weight: 600; color: #0f172a; }
.tam__time { font-size: 12.5px; color: #64748b; display: inline-flex; gap: 5px; align-items: center; }
.tam__warn { margin-top: 6px; font-size: 11.5px; color: #b45309; }
</style>
