<script setup>
import { computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { SolutionOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import FormPage from '@/Components/FormPage.vue';
import ExamenForm from './ExamenForm.vue';

const props = defineProps({
    preguntas: { type: Array, required: true },
    areas: { type: Array, required: true },
    tiposExamen: { type: Array, required: true },
});

const form = useForm({
    tipo_examen: undefined,
    tiempo: 30,
    numero_preguntas: 20,
    completar_aleatorio: false,
    preguntas_seleccionadas: [],
});

const back = () => router.visit(route('admin.examenes.index'));
const submit = () => form.post(route('admin.examenes.store'));

const disabled = computed(() =>
    !form.numero_preguntas ||
    (form.preguntas_seleccionadas.length === 0 && !form.completar_aleatorio));
const label = computed(() => `Crear examen · ${form.numero_preguntas || 0} preguntas`);
</script>

<template>
    <AdminLayout title="Nuevo examen">
        <PageHead title="Nuevo examen" subtitle="Generar un examen con preguntas del banco" :icon="SolutionOutlined" back @back="back" />
        <FormPage :submit-label="label" :processing="form.processing" :disabled="disabled" max-width="1040px" @submit="submit" @cancel="back">
            <ExamenForm :form="form" :preguntas="preguntas" :areas="areas" :tipos-examen="tiposExamen" />
        </FormPage>
    </AdminLayout>
</template>
