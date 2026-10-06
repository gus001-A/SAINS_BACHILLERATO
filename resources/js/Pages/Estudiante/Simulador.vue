<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
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
});

// Muchos simuladores comparten el mismo nombre ("Simulación"); si es así,
// los numeramos para poder distinguirlos en el selector.
const nombresColisionan = computed(() => {
    const n = new Set(props.examenes.map((e) => e.nombre));
    return n.size < props.examenes.length;
});
const etiqueta = (e, i) => (nombresColisionan.value ? `Simulador ${i + 1}` : e.nombre);
</script>

<template>
    <EstudianteLayout title="Simulador de examen">
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

        <a-empty v-else-if="!preguntas.length" description="Este simulador no tiene preguntas configuradas." />

        <QuizRunner
            v-else
            :examen="examen"
            :preguntas="preguntas"
            :intento="intento"
            :responder-url="responderUrl"
            :plan-activo="planActivo"
            :max-preguntas="maxPreguntas"
            :intentos-restantes="intentosRestantes"
            storage-key="simulador"
        >
            <template #selector="{ cambiar }">
                <a-card v-if="examenes.length > 1" :bordered="false" style="margin-bottom: 16px">
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
    </EstudianteLayout>
</template>

<style scoped>
.sim-picker { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; }
.sim-chip {
    text-align: left; cursor: pointer; padding: 12px 14px; border-radius: 12px;
    border: 1px solid var(--sains-line); background: #fff;
    display: flex; flex-direction: column; gap: 2px; transition: all .15s ease;
}
.sim-chip:hover { border-color: #c7d2fe; background: #f8faff; }
.sim-chip.on { border-color: #4f46e5; background: #eef2ff; box-shadow: 0 0 0 2px rgba(79, 70, 229, .12); }
.sim-chip b { font-size: 13.5px; color: #0f172a; }
.sim-chip small { font-size: 11.5px; color: #64748b; }
</style>
