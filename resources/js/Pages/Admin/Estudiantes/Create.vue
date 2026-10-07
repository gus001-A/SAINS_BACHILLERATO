<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { TeamOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import FormPage from '@/Components/FormPage.vue';
import EstudianteForm from './EstudianteForm.vue';

defineProps({ opciones: { type: Object, required: true } });

const form = useForm({
    nombre: '', paterno: '', materno: '', fecha_nacimiento: null, sexo: undefined,
    telefono: '', telefono_casa: '',
    email: '', password: '', password_confirmation: '',
    plan_activo: false, cupon_id: undefined,
    carrera_id: undefined, curp: '', calle_numero: '', colonia: '',
    codigo_postal: '', municipio: '', entidad_federativa: undefined,
});

const back = () => router.visit(route('admin.estudiantes.index'));
const submit = () => form.post(route('admin.estudiantes.store'));
</script>

<template>
    <AdminLayout title="Nuevo estudiante">
        <PageHead title="Nuevo estudiante" subtitle="Registrar un alumno en la plataforma" :icon="TeamOutlined" back @back="back" />
        <FormPage submit-label="Registrar estudiante" :processing="form.processing" @submit="submit" @cancel="back">
            <EstudianteForm :form="form" :opciones="opciones" modo="create" />
        </FormPage>
    </AdminLayout>
</template>
