<script setup>
import { router } from '@inertiajs/vue3';
import { FileTextOutlined, CopyOutlined, DownloadOutlined } from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';
import ComprobanteUpload from '@/Components/ComprobanteUpload.vue';
import { message } from '@/lib/notify';

const props = defineProps({
    pago: { type: Object, required: true },
    fichaUrl: { type: String, default: null },
});

const money = (n) => `$${Number(n).toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;

function copiarRef() {
    navigator.clipboard?.writeText(props.pago.referencia_pago)
        .then(() => message.success('Referencia copiada'))
        .catch(() => message.error('No se pudo copiar'));
}
</script>

<template>
    <EstudianteLayout title="Ficha de pago">
        <section class="sains-hero">
            <div class="sains-hero__grid">
                <div>
                    <span class="sains-hero__eyebrow"><FileTextOutlined /> Ficha de pago</span>
                    <h1 class="sains-hero__title">Realiza tu pago</h1>
                    <p class="sains-hero__sub">Transfiere el monto con tu referencia única y luego sube el comprobante.</p>
                </div>
                <div class="sains-hero__aside">
                    <div class="hero-monto">
                        <span>A pagar</span>
                        <b>{{ money(pago.monto_pago) }}</b>
                        <small>MXN</small>
                    </div>
                </div>
            </div>
        </section>

        <div class="ficha">
            <a-card :bordered="false" title="Información de tu pago">
                <a-descriptions bordered :column="1" size="small">
                    <a-descriptions-item label="Referencia">
                        <a-typography-text copyable code>{{ pago.referencia_pago }}</a-typography-text>
                    </a-descriptions-item>
                    <a-descriptions-item label="Monto a pagar">
                        <b style="font-size: 1.1rem; color: #4f46e5">{{ money(pago.monto_pago) }} MXN</b>
                    </a-descriptions-item>
                    <a-descriptions-item label="Método">{{ pago.tipo_pago }}</a-descriptions-item>
                    <a-descriptions-item label="Fecha de solicitud">{{ pago.fecha_pago_formato }}</a-descriptions-item>
                </a-descriptions>

                <a-space wrap style="margin-top: 16px">
                    <a-button @click="copiarRef"><template #icon><CopyOutlined /></template>Copiar referencia</a-button>
                    <a-button type="link" :href="route('estudiante.descargar-ficha', pago.id)">
                        <template #icon><DownloadOutlined /></template>Descargar ficha
                    </a-button>
                    <a-button @click="router.visit(route('estudiante.mis-pagos'))">Ver mi pago</a-button>
                </a-space>

                <a-alert
                    type="info" show-icon style="margin-top: 16px"
                    message="La ficha es la misma para todos los pagos. Usa tu referencia única al transferir."
                />
            </a-card>

            <a-card :bordered="false" title="Ficha" class="ficha-img">
                <img v-if="fichaUrl" :src="fichaUrl" alt="Ficha de pago" />
                <a-empty v-else description="Ficha no disponible" />
            </a-card>
        </div>

        <a-card :bordered="false" title="¿Ya realizaste el pago?" style="margin-top: 16px; max-width: 620px">
            <p style="color: #64748b; margin-bottom: 14px">
                Sube tu comprobante para que nuestro equipo lo valide y active tu plan premium.
            </p>
            <ComprobanteUpload :pago-id="pago.id" label="Subir comprobante de pago" />
        </a-card>
    </EstudianteLayout>
</template>

<style scoped>
.hero-monto { text-align: right; line-height: 1.1; }
.hero-monto span { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; opacity: .8; }
.hero-monto b { display: block; font-size: 2rem; font-weight: 800; margin: 2px 0; }
.hero-monto small { opacity: .8; }

.ficha { display: flex; gap: 18px; flex-wrap: wrap; align-items: flex-start; }
.ficha > .ant-card { flex: 1; min-width: 300px; }
.ficha-img img { width: 100%; border-radius: 12px; border: 1px solid #e2e8f0; }
</style>
