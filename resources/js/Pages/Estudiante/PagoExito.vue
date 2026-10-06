<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { CheckCircleFilled, ClockCircleFilled } from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';

const props = defineProps({
    pago: { type: Object, required: true },
});

const money = (n) => `$${Number(n).toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;
const completado = computed(() => ['completado', 'aprobado'].includes(props.pago.estatus));

const pasos = [
    { n: 1, t: 'Validación de pago', d: 'Nuestro equipo revisa tu comprobante en 24-48 horas hábiles.' },
    { n: 2, t: 'Activación del plan', d: 'Recibirás un correo cuando tu plan premium esté activo.' },
    { n: 3, t: 'Acceso total', d: 'Todas las clases premium y simuladores ilimitados.' },
];
</script>

<template>
    <EstudianteLayout title="Comprobante recibido">
        <section class="pe-hero" :class="{ ok: completado }">
            <span class="pe-hero__ico">
                <CheckCircleFilled v-if="completado" />
                <ClockCircleFilled v-else />
            </span>
            <h1>{{ completado ? '¡Pago confirmado!' : '¡Comprobante recibido!' }}</h1>
            <p>{{ completado
                ? 'Tu plan premium ya está activo. Disfruta de todo el contenido.'
                : 'Hemos recibido tu comprobante. Lo revisaremos en 24-48 horas hábiles.' }}</p>
        </section>

        <div class="pe-grid">
            <a-card :bordered="false" title="Detalles de tu solicitud">
                <a-descriptions bordered :column="1" size="small">
                    <a-descriptions-item label="Referencia">
                        <a-typography-text copyable code>{{ pago.referencia_pago }}</a-typography-text>
                    </a-descriptions-item>
                    <a-descriptions-item label="Monto">{{ money(pago.monto_pago) }} MXN</a-descriptions-item>
                    <a-descriptions-item label="Método">{{ pago.tipo_pago }}</a-descriptions-item>
                    <a-descriptions-item label="Fecha">{{ pago.fecha_pago_formato }}</a-descriptions-item>
                    <a-descriptions-item label="Estatus">
                        <a-tag :color="completado ? 'green' : 'orange'">{{ pago.estatus }}</a-tag>
                    </a-descriptions-item>
                </a-descriptions>
                <a-space wrap style="margin-top: 16px">
                    <a-button type="primary" @click="router.visit(route(completado ? 'estudiante.clases-premium' : 'estudiante.dashboard'))">
                        {{ completado ? 'Ir a mis clases' : 'Ir al inicio' }}
                    </a-button>
                    <a-button @click="router.visit(route('estudiante.mis-pagos'))">Ver mi pago</a-button>
                </a-space>
            </a-card>

            <a-card v-if="!completado" :bordered="false" title="¿Qué sigue?">
                <ol class="pe-steps">
                    <li v-for="p in pasos" :key="p.n">
                        <span class="pe-steps__n">{{ p.n }}</span>
                        <span>
                            <b>{{ p.t }}</b>
                            <small>{{ p.d }}</small>
                        </span>
                    </li>
                </ol>
            </a-card>
        </div>
    </EstudianteLayout>
</template>

<style scoped>
.pe-hero {
    position: relative; overflow: hidden; isolation: isolate;
    border-radius: 22px; padding: 34px 30px; margin-bottom: 20px; text-align: center; color: #fff;
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
    box-shadow: 0 20px 45px -22px rgba(245, 158, 11, .6);
}
.pe-hero.ok { background: linear-gradient(135deg, #059669 0%, #34d399 100%); box-shadow: 0 20px 45px -22px rgba(16, 185, 129, .6); }
.pe-hero::before {
    content: ''; position: absolute; width: 240px; height: 240px; border-radius: 50%; z-index: -1;
    background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, .28), transparent 70%);
    top: -120px; right: -40px; animation: sains-blob 15s ease-in-out infinite;
}
.pe-hero__ico { font-size: 52px; display: block; margin-bottom: 8px; }
.pe-hero h1 { font-size: 1.5rem; font-weight: 800; margin: 0 0 6px; color: #fff; }
.pe-hero p { margin: 0; opacity: .92; max-width: 46ch; margin-inline: auto; }

.pe-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 16px; align-items: start; }
@media (max-width: 820px) { .pe-grid { grid-template-columns: 1fr; } }

.pe-steps { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 16px; }
.pe-steps li { display: flex; gap: 12px; }
.pe-steps__n {
    width: 26px; height: 26px; border-radius: 8px; flex: none;
    display: flex; align-items: center; justify-content: center;
    background: #eef2ff; color: #4f46e5; font-weight: 700; font-size: 13px;
}
.pe-steps li span:last-child { display: flex; flex-direction: column; line-height: 1.35; }
.pe-steps b { font-size: 13.5px; color: #0f172a; }
.pe-steps small { color: #64748b; }
</style>
