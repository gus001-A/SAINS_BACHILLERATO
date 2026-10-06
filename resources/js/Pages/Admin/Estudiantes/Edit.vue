<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { TeamOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import FormPage from '@/Components/FormPage.vue';
import EstudianteForm from './EstudianteForm.vue';

const props = defineProps({
    estudiante: { type: Object, required: true },
    opciones: { type: Object, required: true },
});

const e = props.estudiante;
const form = useForm({
    nombre: e.nombre ?? '',
    paterno: e.paterno ?? '',
    materno: e.materno ?? '',
    fecha_nacimiento: e.fecha_nacimiento,
    sexo: e.sexo || undefined,
    telefono: e.telefono ?? '',
    telefono_casa: e.telefono_casa ?? '',
    email: e.correo ?? '',
    password: '',
    password_confirmation: '',
    plan_activo: !!e.plan_activo,
    cupon_id: undefined,
});

const back = () => router.visit(route('admin.estudiantes.index'));
const submit = () => form.put(route('admin.estudiantes.update', e.id));
</script>

<template>
    <AdminLayout title="Editar estudiante">
        <PageHead :title="`Editar: ${e.nombre} ${e.paterno}`" subtitle="Actualizar datos del alumno" :icon="TeamOutlined" back @back="back" />
        <FormPage submit-label="Guardar cambios" :processing="form.processing" @submit="submit" @cancel="back">
            <a-alert v-if="e.cupon" type="info" show-icon style="margin-bottom: 16px"
                :message="`Cupón aplicado actualmente: ${e.cupon}`" />
            <EstudianteForm :form="form" :opciones="opciones" modo="edit" />
        </FormPage>
    </AdminLayout>
</template>
