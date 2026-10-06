<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { DollarOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import FormPage from '@/Components/FormPage.vue';
import PagoForm from './PagoForm.vue';

const props = defineProps({
    pago: { type: Object, required: true },
    estudiantes: { type: Array, required: true },
});

const p = props.pago;
const form = useForm({
    _method: 'put',
    alumno_pago: p.alumno_pago,
    tipo_pago: p.tipo_pago,
    estatus: p.estatus,
    fecha_pago: p.fecha_pago,
    monto_pago: p.monto_pago,
    referencia_pago: p.referencia_pago ?? '',
    nota_usuario: p.nota_usuario ?? '',
    comprobante: null,
    eliminar_comprobante: false,
});

const back = () => router.visit(route('admin.pagos.index'));
const submit = () => form.post(route('admin.pagos.update', p.id));
</script>

<template>
    <AdminLayout title="Editar pago">
        <PageHead :title="`Editar pago #${p.id}`" subtitle="Modificar datos del pago" :icon="DollarOutlined" back @back="back" />
        <FormPage submit-label="Guardar cambios" :processing="form.processing" @submit="submit" @cancel="back">
            <PagoForm :form="form" :estudiantes="estudiantes" modo="edit" :comprobante-actual="p.comprobante_url" />
        </FormPage>
    </AdminLayout>
</template>
