<script setup>
import { computed, ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ShoppingCartOutlined, TagOutlined, BankOutlined, ShopOutlined, CreditCardOutlined,
    LockOutlined,
} from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';
import { message } from '@/lib/notify';

const props = defineProps({
    estudianteData: { type: Object, required: true },
    precios: { type: Object, required: true },
    mpPublicKey: { type: String, default: null },
});

const money = (n) => `$${Number(n).toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;
const mpDisponible = computed(() => !!props.mpPublicKey);

const cuponForm = useForm({ codigo: props.precios.cupon_aplicado || '' });
const aplicarCupon = () => cuponForm.post(route('estudiante.aplicar-cupon'), { preserveScroll: true });
const quitarCupon = () => router.delete(route('estudiante.eliminar-cupon'), { preserveScroll: true });

const metodo = ref(mpDisponible.value ? 'mercadopago' : 'transferencia');
const pagoForm = useForm({ metodo_pago: 'transferencia' });
const mpLoading = ref(false);

function generarFicha() {
    pagoForm.metodo_pago = metodo.value;
    pagoForm.post(route('estudiante.procesar-solicitud-pago'));
}

async function pagarMercadoPago() {
    mpLoading.value = true;
    try {
        const { data } = await axios.post(route('estudiante.pago.mercadopago.crear'));
        if (data.success && data.init_point) {
            window.location.href = data.init_point;
        } else {
            message.error(data.message || 'No se pudo iniciar el pago con Mercado Pago');
        }
    } catch (e) {
        message.error(e.response?.data?.message || 'Error de conexión con Mercado Pago');
    } finally {
        mpLoading.value = false;
    }
}

const metodos = computed(() => [
    {
        key: 'mercadopago',
        title: 'Mercado Pago',
        desc: mpDisponible.value ? 'Tarjeta de crédito o débito · pago inmediato' : 'No disponible por ahora',
        icon: CreditCardOutlined,
        disabled: !mpDisponible.value,
    },
    { key: 'transferencia', title: 'Transferencia bancaria', desc: 'BBVA, Banorte, Santander… · sube tu comprobante', icon: BankOutlined },
    { key: 'oxxo', title: 'Pago en tienda', desc: 'OXXO, 7-Eleven, Circle K · sube tu comprobante', icon: ShopOutlined },
]);

function elegir(m) {
    if (m.disabled) return;
    metodo.value = m.key;
}
</script>

<template>
    <EstudianteLayout title="Finalizar compra">
        <section class="sains-hero">
            <div class="sains-hero__grid">
                <div>
                    <span class="sains-hero__eyebrow"><ShoppingCartOutlined /> Curso Premium SAINS 2026</span>
                    <h1 class="sains-hero__title">Desbloquea todo el curso</h1>
                    <p class="sains-hero__sub">Más de 50 clases, simuladores ilimitados y material descargable. Acceso por 1 año.</p>
                </div>
                <div class="sains-hero__aside">
                    <div class="hero-price">
                        <span>Total</span>
                        <b>{{ money(precios.precio_final) }}</b>
                        <small v-if="precios.tiene_descuento">antes {{ money(precios.precio_original) }}</small>
                    </div>
                </div>
            </div>
        </section>

        <div class="checkout">
            <div class="checkout-main">
                <a-card :bordered="false" title="Datos personales" size="small">
                    <a-descriptions :column="{ xs: 1, sm: 2 }" size="small">
                        <a-descriptions-item label="Nombre">{{ estudianteData.nombre_completo }}</a-descriptions-item>
                        <a-descriptions-item label="Correo">{{ estudianteData.correo }}</a-descriptions-item>
                        <a-descriptions-item label="Teléfono">{{ estudianteData.telefono || 'No especificado' }}</a-descriptions-item>
                    </a-descriptions>
                </a-card>

                <a-card :bordered="false" title="¿Tienes un cupón?" size="small" style="margin-top: 16px">
                    <template #extra><TagOutlined style="color: #f59e0b" /></template>
                    <a-space :style="{ width: '100%', display: 'flex' }">
                        <a-input
                            v-model:value="cuponForm.codigo"
                            placeholder="Código del cupón"
                            :disabled="!!precios.cupon_aplicado"
                            style="flex: 1"
                        />
                        <a-button v-if="precios.cupon_aplicado" danger @click="quitarCupon">Quitar</a-button>
                        <a-button v-else type="primary" :loading="cuponForm.processing" @click="aplicarCupon">Aplicar</a-button>
                    </a-space>
                    <a-typography-text v-if="precios.cupon_aplicado" type="success" style="display: block; margin-top: 8px">
                        Cupón «{{ precios.cupon_aplicado }}» aplicado.
                    </a-typography-text>
                </a-card>

                <a-card :bordered="false" title="Método de pago" size="small" style="margin-top: 16px">
                    <div class="pay-methods">
                        <button
                            v-for="m in metodos" :key="m.key" type="button"
                            class="pay-method"
                            :class="{ on: metodo === m.key, off: m.disabled }"
                            :disabled="m.disabled"
                            @click="elegir(m)"
                        >
                            <span class="pay-radio" :class="{ on: metodo === m.key }"></span>
                            <component :is="m.icon" class="pay-method-ico" />
                            <span class="pay-method-txt">
                                <b>{{ m.title }}</b>
                                <small>{{ m.desc }}</small>
                            </span>
                        </button>
                    </div>

                    <a-button
                        v-if="metodo === 'mercadopago'"
                        type="primary" block size="large" :loading="mpLoading" :disabled="!mpDisponible"
                        style="margin-top: 18px" @click="pagarMercadoPago"
                    >
                        <template #icon><CreditCardOutlined /></template>
                        Pagar {{ money(precios.precio_final) }} con Mercado Pago
                    </a-button>
                    <a-button
                        v-else
                        type="primary" block size="large" :loading="pagoForm.processing"
                        style="margin-top: 18px" @click="generarFicha"
                    >Generar ficha de pago</a-button>

                    <p class="pay-note">
                        <LockOutlined /> Pago protegido. Nunca compartas tu información con terceros.
                    </p>
                </a-card>
            </div>

            <a-card :bordered="false" class="checkout-summary" title="Resumen">
                <div class="sum-row"><span>Precio original</span><span>{{ money(precios.precio_original) }}</span></div>
                <div v-if="precios.tiene_descuento" class="sum-row disc">
                    <span>Descuento</span><span>-{{ money(precios.monto_descuento) }}</span>
                </div>
                <a-divider style="margin: 12px 0" />
                <div class="sum-row total"><span>Total a pagar</span><span>{{ money(precios.precio_final) }}</span></div>

                <a-alert
                    v-if="precios.porcentaje_descuento"
                    type="success" show-icon style="margin-top: 14px"
                    :message="`Cupón: ${precios.porcentaje_descuento}% de descuento`"
                />

                <ul class="sum-list">
                    <li>Más de 50 clases en video</li>
                    <li>Material descargable en PDF</li>
                    <li>Simuladores de examen ilimitados</li>
                    <li>Acceso por 1 año</li>
                </ul>
                <a-typography-text type="secondary" style="font-size: 12px">
                    Pago 100% seguro · Datos encriptados
                </a-typography-text>
            </a-card>
        </div>
    </EstudianteLayout>
</template>

<style scoped>
.hero-price { text-align: right; line-height: 1.1; }
.hero-price span { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; opacity: .8; }
.hero-price b { display: block; font-size: 2rem; font-weight: 800; margin: 2px 0; }
.hero-price small { opacity: .75; text-decoration: line-through; }

.checkout { display: flex; gap: 22px; flex-wrap: wrap; align-items: flex-start; }
.checkout-main { flex: 1; min-width: 300px; }
.checkout-summary { width: 340px; position: sticky; top: 84px; }
@media (max-width: 860px) { .checkout-summary { width: 100%; position: static; } }

.pay-methods { display: flex; flex-direction: column; gap: 10px; width: 100%; }
.pay-method {
    display: flex; align-items: center; gap: 12px; padding: 13px 16px; width: 100%; text-align: left;
    border: 1px solid #e2e8f0; border-radius: 13px; cursor: pointer; background: #fff;
    transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
}
.pay-method:hover:not(.off) { border-color: #c7d2fe; }
.pay-method.on { border-color: #4f46e5; background: #eef2ff; box-shadow: 0 0 0 3px rgba(79, 70, 229, .1); }
.pay-method.off { opacity: .55; cursor: not-allowed; }
.pay-radio {
    width: 18px; height: 18px; flex: none; border-radius: 50%; border: 2px solid #cbd5e1;
    position: relative; transition: border-color .15s ease;
}
.pay-radio.on { border-color: #4f46e5; }
.pay-radio.on::after {
    content: ''; position: absolute; inset: 3px; border-radius: 50%; background: #4f46e5;
}
.pay-method-ico { font-size: 20px; color: #4f46e5; flex: none; }
.pay-method-txt { display: flex; flex-direction: column; line-height: 1.3; flex: 1; min-width: 0; }
.pay-method-txt b { font-size: 14px; color: #0f172a; }
.pay-method-txt small { font-size: 12px; color: #64748b; }
.pay-note {
    display: flex; align-items: center; gap: 7px; margin: 14px 0 0;
    font-size: 11.5px; color: #94a3b8;
}

.sum-row { display: flex; justify-content: space-between; margin-bottom: 10px; color: #475569; }
.sum-row.disc { color: #10b981; }
.sum-row.total { font-size: 1.15rem; font-weight: 800; color: #0f172a; }
.sum-list { margin: 16px 0 12px; padding-left: 18px; color: #334155; font-size: 13px; }
.sum-list li { margin-bottom: 6px; }
</style>
