<script setup>
import { router } from '@inertiajs/vue3';
import { ClockCircleOutlined } from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';
import ComprobanteUpload from '@/Components/ComprobanteUpload.vue';

const props = defineProps({
    pago: { type: Object, required: true },
});

const money = (n) => `$${Number(n).toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;
</script>

<template>
    <EstudianteLayout title="Pago pendiente">
        <section class="sains-hero">
            <div class="sains-hero__grid">
                <div>
                    <span class="sains-hero__eyebrow"><ClockCircleOutlined /> Pago en proceso</span>
                    <h1 class="sains-hero__title">Ya tienes un pago pendiente</h1>
                    <p class="sains-hero__sub">Completa tu pago con la referencia de abajo y sube el comprobante para activar tu plan.</p>
                </div>
            </div>
        </section>

        <a-card :bordered="false" style="max-width: 620px">
            <a-descriptions bordered :column="1" size="small">
                <a-descriptions-item label="Referencia">{{ pago.referencia_pago }}</a-descriptions-item>
                <a-descriptions-item label="Monto">{{ money(pago.monto_pago) }} MXN</a-descriptions-item>
                <a-descriptions-item label="Método">{{ pago.tipo_pago }}</a-descriptions-item>
                <a-descriptions-item label="Estatus">
                    <a-tag color="orange">{{ pago.estatus }}</a-tag>
                </a-descriptions-item>
                <a-descriptions-item label="Solicitado">{{ pago.fecha_pago_formato }}</a-descriptions-item>
            </a-descriptions>

            <a-space wrap style="margin-top: 18px">
                <a-button type="primary" @click="router.visit(route('estudiante.ficha-pago', pago.id))">Ver ficha de pago</a-button>
                <ComprobanteUpload v-if="!pago.comprobante_url" :pago-id="pago.id" label="Subir comprobante" />
                <a-button @click="router.visit(route('estudiante.mis-pagos'))">Ver mi pago</a-button>
            </a-space>
        </a-card>
    </EstudianteLayout>
</template>
