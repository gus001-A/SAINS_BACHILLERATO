<script setup>
import { computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { SolutionOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import FormPage from '@/Components/FormPage.vue';
import ExamenForm from './ExamenForm.vue';

const props = defineProps({
    examen: { type: Object, required: true },
    preguntas: { type: Array, required: true },
    areas: { type: Array, required: true },
    tiposExamen: { type: Array, required: true },
});

const form = useForm({
    tipo_examen: props.examen.tipo_examen,
    tiempo: props.examen.tiempo,
    numero_preguntas: props.examen.numero_preguntas || props.examen.preguntas_seleccionadas.length,
    completar_aleatorio: false,
    preguntas_seleccionadas: [...props.examen.preguntas_seleccionadas],
});

const back = () => router.visit(route('admin.examenes.index'));
const submit = () => form.put(route('admin.examenes.update', props.examen.id));

const disabled = computed(() =>
    !form.numero_preguntas ||
    (form.preguntas_seleccionadas.length === 0 && !form.completar_aleatorio));
const label = computed(() => `Guardar · ${form.numero_preguntas || 0} preguntas`);
</script>

<template>
    <AdminLayout title="Editar examen">
        <PageHead :title="`Editar examen #${examen.id}`" subtitle="Modificar preguntas y configuración" :icon="SolutionOutlined" back @back="back" />
        <FormPage :submit-label="label" :processing="form.processing" :disabled="disabled" max-width="1040px" @submit="submit" @cancel="back">
            <ExamenForm :form="form" :preguntas="preguntas" :areas="areas" :tipos-examen="tiposExamen" />
        </FormPage>
    </AdminLayout>
</template>
