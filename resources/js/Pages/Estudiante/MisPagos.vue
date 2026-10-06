<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { CreditCardOutlined } from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';
import ComprobanteUpload from '@/Components/ComprobanteUpload.vue';

const props = defineProps({
    pago: { type: Object, default: null },
});

const money = (n) => `$${Number(n).toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;

const estado = computed(() => {
    const map = {
        pendiente: { color: 'orange', text: 'Pendiente de validación', desc: 'Tu pago está siendo revisado por nuestro equipo.' },
        revisando: { color: 'blue', text: 'En revisión', desc: 'Estamos validando tu comprobante.' },
        aprobado: { color: 'green', text: '¡Pago aprobado!', desc: 'Tu plan premium ya está activo.' },
        completado: { color: 'green', text: '¡Pago completado!', desc: 'Tu plan premium ya está activo.' },
        rechazado: { color: 'red', text: 'Pago rechazado', desc: 'Hubo un problema con tu comprobante. Súbelo nuevamente.' },
        cancelado: { color: 'default', text: 'Cancelado', desc: 'Este pago fue cancelado.' },
    };
    return map[props.pago?.estatus] || { color: 'default', text: props.pago?.estatus, desc: '' };
});

const puedeSubir = computed(() => ['pendiente', 'rechazado'].includes(props.pago?.estatus) && !props.pago?.comprobante_url);
</script>

<template>
    <EstudianteLayout title="Mi pago">
        <section class="sains-hero">
            <div class="sains-hero__grid">
                <div>
                    <span class="sains-hero__eyebrow"><CreditCardOutlined /> Mi pago</span>
                    <h1 class="sains-hero__title">Estado de tu solicitud</h1>
                    <p class="sains-hero__sub">Aquí puedes seguir la validación de tu pago y subir tu comprobante.</p>
                </div>
            </div>
        </section>

        <a-empty v-if="!pago" description="No tienes pagos registrados">
            <a-button type="primary" @click="router.visit(route('estudiante.checkout'))">Adquirir plan</a-button>
        </a-empty>

        <a-card v-else :bordered="false" style="max-width: 640px">
            <a-result :status="estado.color === 'red' ? 'error' : (estado.color === 'green' ? 'success' : 'info')"
                :title="estado.text" :sub-title="estado.desc" style="padding-top: 0" />

            <a-descriptions bordered :column="1" size="small">
                <a-descriptions-item label="Referencia">
                    <a-typography-text copyable code>{{ pago.referencia_pago }}</a-typography-text>
                </a-descriptions-item>
                <a-descriptions-item label="Monto">
                    <b style="color: #4f46e5">{{ money(pago.monto_pago) }} MXN</b>
                </a-descriptions-item>
                <a-descriptions-item label="Método">{{ pago.tipo_pago }}</a-descriptions-item>
                <a-descriptions-item label="Fecha de solicitud">{{ pago.fecha_pago_formato }}</a-descriptions-item>
                <a-descriptions-item label="Comprobante">
                    <a-button v-if="pago.comprobante_url" type="link" :href="pago.comprobante_url" target="_blank" style="padding: 0">
                        Ver comprobante
                    </a-button>
                    <span v-else style="color: #94a3b8">Sin comprobante</span>
                </a-descriptions-item>
            </a-descriptions>

            <a-space wrap style="margin-top: 18px">
                <ComprobanteUpload v-if="puedeSubir" :pago-id="pago.id"
                    :label="pago.estatus === 'rechazado' ? 'Subir nuevo comprobante' : 'Subir comprobante'" />
                <a-button v-if="pago.estatus === 'pendiente'" @click="router.visit(route('estudiante.ficha-pago', pago.id))">
                    Descargar ficha de pago
                </a-button>
                <a-button @click="router.visit(route('estudiante.dashboard'))">Volver al inicio</a-button>
            </a-space>
        </a-card>
    </EstudianteLayout>
</template>
