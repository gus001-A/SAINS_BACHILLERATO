<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import {
    CheckOutlined, CloseOutlined, EditOutlined, DollarOutlined,
    FileImageOutlined, DownloadOutlined, FilePdfOutlined, UserOutlined,
} from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import { confirmAction } from '@/lib/notify';

const props = defineProps({ pago: { type: Object, required: true } });

const money = (n) => '$' + Number(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 });
const estadoColor = (s) => ({ aprobado: 'green', completado: 'green', rechazado: 'red', cancelado: 'red', pendiente: 'orange' }[s] || 'blue');
const estadoTone = computed(() => {
    if (['aprobado', 'completado'].includes(props.pago.estatus)) return 'ok';
    if (['rechazado', 'cancelado'].includes(props.pago.estatus)) return 'bad';
    return 'wait';
});
const esPdf = computed(() => (props.pago.comprobante_url || '').toLowerCase().endsWith('.pdf'));
const revisado = ['aprobado', 'rechazado', 'completado'].includes(props.pago.estatus);
const iniciales = computed(() => (props.pago.alumno?.nombre || '?').trim().split(/\s+/).slice(0, 2).map((w) => w[0]).join('').toUpperCase());

const detalles = computed(() => [
    { label: 'Referencia', value: props.pago.referencia || '—', mono: true },
    { label: 'Método de pago', value: props.pago.tipo_pago || '—' },
    { label: 'Fecha de pago', value: props.pago.fecha_pago || '—' },
    { label: 'Fecha de revisión', value: props.pago.fecha_aprueba || '—' },
    { label: 'Revisado por', value: props.pago.revisor || 'Sin revisar' },
    { label: 'ID interno', value: `#${props.pago.id}` },
]);

function aprobar() {
    confirmAction({
        title: '¿Aprobar este pago?',
        content: 'Se activará el plan del estudiante y se le enviará un correo.',
        okText: 'Sí, aprobar',
        onOk: () => router.post(route('admin.pagos.aprobar', props.pago.id), {}, { preserveScroll: true }),
    });
}

const rechazoOpen = ref(false);
const rechazoForm = useForm({ motivo_rechazo: '' });
function rechazar() {
    rechazoForm.post(route('admin.pagos.rechazar', props.pago.id), {
        preserveScroll: true,
        onSuccess: () => { rechazoOpen.value = false; rechazoForm.reset(); },
    });
}
</script>

<template>
    <AdminLayout :title="`Pago #${pago.id}`">
        <PageHead :title="`Pago #${pago.id}`" :subtitle="pago.referencia" :icon="DollarOutlined"
            back @back="router.visit(route('admin.pagos.index'))">
            <template #actions>
                <template v-if="!revisado">
                    <a-button type="primary" @click="aprobar"><template #icon><CheckOutlined /></template>Aprobar</a-button>
                    <a-button danger @click="rechazoOpen = true"><template #icon><CloseOutlined /></template>Rechazar</a-button>
                </template>
                <Link :href="route('admin.pagos.edit', pago.id)"><a-button><template #icon><EditOutlined /></template>Editar</a-button></Link>
            </template>
        </PageHead>

        <div class="pay">
            <!-- Resumen -->
            <section class="pay__hero" :class="`is-${estadoTone}`">
                <div class="pay__hero-main">
                    <span class="pay__hero-label">Monto del pago</span>
                    <b class="pay__hero-amount">{{ money(pago.monto) }}</b>
                </div>
                <div class="pay__hero-tags">
                    <span class="pay__chip">{{ pago.estatus }}</span>
                    <span class="pay__chip pay__chip--soft">{{ pago.tipo_pago }}</span>
                    <span class="pay__chip pay__chip--soft">{{ pago.fecha_pago || 'Sin fecha' }}</span>
                </div>
            </section>

            <!-- Cuerpo -->
            <div class="pay__cols">
                <div class="pay__col pay__col--main">
                    <div class="pcard">
                        <div class="pcard__title">Detalle del pago</div>
                        <dl class="kv">
                            <div v-for="d in detalles" :key="d.label" class="kv__row">
                                <dt>{{ d.label }}</dt>
                                <dd :class="{ 'kv--mono': d.mono }">{{ d.value }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="pcard">
                        <div class="pcard__title">
                            <FileImageOutlined /> Comprobante de pago
                            <a v-if="pago.comprobante_url" :href="pago.comprobante_url" target="_blank" rel="noopener" class="pcard__link">
                                <DownloadOutlined /> Abrir
                            </a>
                        </div>

                        <a v-if="pago.comprobante_url && !esPdf" :href="pago.comprobante_url" target="_blank" rel="noopener" class="pay__proof-img">
                            <img :src="pago.comprobante_url" alt="Comprobante" />
                        </a>
                        <a v-else-if="pago.comprobante_url" :href="pago.comprobante_url" target="_blank" rel="noopener" class="pay__proof-pdf">
                            <FilePdfOutlined />
                            <span>Comprobante en PDF</span>
                            <small>Haz clic para abrirlo en otra pestaña</small>
                        </a>
                        <div v-else class="pay__proof-empty">
                            <FileImageOutlined />
                            <span>El estudiante aún no ha subido su comprobante</span>
                        </div>
                    </div>
                </div>

                <div class="pay__col pay__col--side">
                    <div class="pcard pay__student">
                        <div class="pcard__title">Estudiante</div>
                        <template v-if="pago.alumno">
                            <div class="stu">
                                <span class="stu__avatar">{{ iniciales }}</span>
                                <div class="stu__meta">
                                    <span class="stu__name">{{ pago.alumno.nombre }}</span>
                                    <span class="stu__mail">{{ pago.alumno.correo || '—' }}</span>
                                </div>
                            </div>
                            <div class="stu__plan">
                                <span>Plan</span>
                                <a-tag :bordered="false" :color="pago.alumno.plan_activo ? 'green' : 'default'">
                                    {{ pago.alumno.plan_activo ? 'Activo' : 'Sin plan' }}
                                </a-tag>
                            </div>
                            <Link :href="route('admin.estudiantes.show', pago.alumno.id)" class="stu__link">
                                <a-button block><template #icon><UserOutlined /></template>Ver ficha completa</a-button>
                            </Link>
                        </template>
                        <a-empty v-else :image="null" description="Sin estudiante asociado" />
                    </div>

                    <div class="pcard">
                        <div class="pcard__title">Nota del pago</div>
                        <p class="pay__note">{{ pago.nota_usuario || 'Sin nota.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <a-modal v-model:open="rechazoOpen" title="Rechazar pago" class="sains-modal"
            :confirm-loading="rechazoForm.processing" ok-text="Rechazar" ok-type="danger" @ok="rechazar">
            <a-form layout="vertical" style="margin-top: 8px">
                <a-form-item label="Motivo del rechazo (opcional)">
                    <a-textarea v-model:value="rechazoForm.motivo_rechazo" :rows="3" placeholder="Se incluirá en el correo al estudiante" />
                </a-form-item>
            </a-form>
        </a-modal>
    </AdminLayout>
</template>

<style scoped>
.pay { display: flex; flex-direction: column; gap: 14px; }

/* ---- Resumen ---- */
.pay__hero {
    display: flex; align-items: center; justify-content: space-between; gap: 18px; flex-wrap: wrap;
    border-radius: 16px; padding: 20px 24px; color: #fff;
}
.pay__hero.is-ok { background: linear-gradient(135deg, #059669, #34d399); }
.pay__hero.is-bad { background: linear-gradient(135deg, #dc2626, #f87171); }
.pay__hero.is-wait { background: linear-gradient(135deg, #d97706, #fbbf24); }
.pay__hero-label { font-size: 11px; text-transform: uppercase; letter-spacing: .09em; opacity: .85; }
.pay__hero-amount { display: block; font-size: 2rem; font-weight: 800; letter-spacing: -.02em; margin-top: 2px; }
.pay__hero-tags { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.pay__chip {
    font-size: 12.5px; font-weight: 700; text-transform: capitalize;
    padding: 4px 12px; border-radius: 999px;
    background: rgba(255, 255, 255, .95); color: #0f172a;
}
.pay__chip--soft { background: rgba(255, 255, 255, .18); color: #fff; font-weight: 600; text-transform: none; }

/* ---- Tarjetas ---- */
.pay__cols { display: flex; gap: 14px; align-items: flex-start; }
.pay__col { display: flex; flex-direction: column; gap: 14px; min-width: 0; }
.pay__col--main { flex: 1.7; }
.pay__col--side { flex: 1; }
@media (max-width: 900px) {
    .pay__cols { flex-direction: column; }
    .pay__col { width: 100%; }
}

.pcard {
    background: #fff;
    border: 1px solid var(--sains-line);
    border-radius: 16px;
    padding: 18px 20px;
}
.pcard__title {
    display: flex; align-items: center; gap: 8px;
    font-size: 14px; font-weight: 700; color: #0f172a;
    padding-bottom: 12px; margin-bottom: 4px;
    border-bottom: 1px solid var(--sains-line);
}
.pcard__title :deep(.anticon) { color: #4f46e5; }
.pcard__link {
    margin-left: auto; font-size: 12.5px; font-weight: 600; color: #4f46e5;
    display: inline-flex; align-items: center; gap: 4px;
}
.pcard__link :deep(.anticon) { color: inherit; }

/* ---- Grilla clave / valor ---- */
.kv { display: grid; grid-template-columns: 1fr 1fr; gap: 0; margin: 4px 0 0; }
.kv__row {
    display: flex; flex-direction: column; gap: 2px;
    padding: 11px 4px;
    border-bottom: 1px solid #f1f5f9;
}
.kv__row:nth-child(odd) { border-right: 1px solid #f1f5f9; padding-right: 16px; }
.kv__row:nth-child(even) { padding-left: 16px; }
.kv dt { font-size: 11.5px; color: var(--sains-faint); font-weight: 600; text-transform: uppercase; letter-spacing: .03em; }
.kv dd { margin: 0; font-size: 13.5px; font-weight: 600; color: #1e293b; word-break: break-word; }
.kv--mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12.5px; }

.pay__note {
    margin: 4px 0 0; padding: 12px 14px;
    background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px;
    font-size: 13px; color: #475569; white-space: pre-wrap; line-height: 1.5;
}

/* ---- Estudiante ---- */
.pay__student { display: flex; flex-direction: column; }
.stu { display: flex; align-items: center; gap: 12px; margin-top: 6px; }
.stu__avatar {
    width: 46px; height: 46px; flex: none; border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 16px; color: #fff;
    background: linear-gradient(135deg, #4f46e5, #9333ea);
}
.stu__meta { display: flex; flex-direction: column; min-width: 0; }
.stu__name { font-weight: 700; font-size: 14.5px; color: #0f172a; }
.stu__mail { font-size: 12.5px; color: var(--sains-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.stu__plan {
    display: flex; align-items: center; justify-content: space-between;
    margin: 14px 0; padding: 10px 12px; border-radius: 10px; background: #f8fafc;
    font-size: 12.5px; font-weight: 600; color: #475569;
}
.stu__link { display: block; margin-top: 2px; }

/* ---- Comprobante ---- */
.pay__proof-img { display: block; }
.pay__proof-img img {
    width: 100%; max-height: 520px; object-fit: contain;
    border-radius: 12px; border: 1px solid var(--sains-line); background: #f8fafc;
}
.pay__proof-pdf, .pay__proof-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
    padding: 44px 20px; margin-top: 6px;
    border-radius: 12px; border: 1px dashed #cbd5e1; background: #f8fafc;
    text-align: center; text-decoration: none;
}
.pay__proof-pdf { border-color: #c7d2fe; }
.pay__proof-pdf :deep(.anticon) { font-size: 34px; color: #ef4444; }
.pay__proof-pdf span { font-weight: 700; color: #0f172a; font-size: 14px; }
.pay__proof-pdf small { font-size: 12px; color: var(--sains-muted); }
.pay__proof-empty :deep(.anticon) { font-size: 30px; color: #cbd5e1; }
.pay__proof-empty span { font-size: 13px; color: var(--sains-muted); }

@media (max-width: 520px) {
    .kv { grid-template-columns: 1fr; }
    .kv__row:nth-child(odd) { border-right: 0; padding-right: 4px; }
    .kv__row:nth-child(even) { padding-left: 4px; }
}
</style>
