<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { DollarOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import FormPage from '@/Components/FormPage.vue';
import PagoForm from './PagoForm.vue';

defineProps({ estudiantes: { type: Array, required: true } });

const form = useForm({
    alumno_pago: undefined, tipo_pago: undefined, estatus: 'pendiente',
    fecha_pago: null, monto_pago: 800, referencia_pago: '', nota_usuario: '',
    comprobante: null,
});

const back = () => router.visit(route('admin.pagos.index'));
const submit = () => form.post(route('admin.pagos.store'));
</script>

<template>
    <AdminLayout title="Registrar pago">
        <PageHead title="Registrar pago" subtitle="Alta manual de un pago" :icon="DollarOutlined" back @back="back" />
        <FormPage submit-label="Registrar pago" :processing="form.processing" @submit="submit" @cancel="back">
            <PagoForm :form="form" :estudiantes="estudiantes" modo="create" />
        </FormPage>
    </AdminLayout>
</template>
