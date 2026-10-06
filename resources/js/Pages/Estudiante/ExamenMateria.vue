<script setup>
import { router } from '@inertiajs/vue3';
import { ArrowLeftOutlined } from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';
import QuizRunner from '@/Components/QuizRunner.vue';

defineProps({
    examen: { type: Object, required: true },
    preguntas: { type: Array, default: () => [] },
    intento: { type: [Number, String], default: 1 },
    mejorCalificacion: { type: Number, default: null },
    asignaturaNombre: { type: String, default: 'Materia' },
    responderUrl: { type: String, required: true },
});
</script>

<template>
    <EstudianteLayout title="Examen de materia">
        <div class="quiz-bar">
            <a-button type="text" size="small" @click="router.visit(route('estudiante.clases-premium'))">
                <template #icon><ArrowLeftOutlined /></template>Clases
            </a-button>
            <a-tag v-if="mejorCalificacion != null" color="blue">Mejor: {{ mejorCalificacion }}%</a-tag>
        </div>

        <a-empty v-if="!preguntas.length" description="Este examen no tiene preguntas configuradas." />

        <QuizRunner
            v-else
            :examen="examen"
            :preguntas="preguntas"
            :intento="intento"
            :responder-url="responderUrl"
            storage-key="examen_materia"
            accent="#0ea5e9"
        />
    </EstudianteLayout>
</template>

<style scoped>
.quiz-bar { max-width: 820px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: space-between; }
</style>
