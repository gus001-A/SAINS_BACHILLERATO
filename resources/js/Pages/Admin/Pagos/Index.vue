<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import {
    PlusOutlined, SearchOutlined, DollarOutlined, FileImageOutlined, DownloadOutlined,
    CheckOutlined, CloseOutlined,
} from '@ant-design/icons-vue';
import RowActions from '@/Components/RowActions.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';
import { confirmDelete, confirmAction, message } from '@/lib/notify';

const props = defineProps({
    pagos: { type: Object, required: true },
    estadosPago: { type: Array, default: () => [] },
    tiposPago: { type: Array, default: () => [] },
    stats: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const state = reactive({
    search: props.filters.search ?? '',
    estudiante: props.filters.estudiante ?? '',
    estatus: props.filters.estatus ?? undefined,
    tipo_pago: props.filters.tipo_pago ?? undefined,
});
let debounce = null;
function reload(extra = {}) {
    router.get(route('admin.pagos.index'), {
        search: state.search || undefined,
        estudiante: state.estudiante || undefined,
        estatus: state.estatus || undefined,
        tipo_pago: state.tipo_pago || undefined,
        ...extra,
    }, { preserveState: true, replace: true, preserveScroll: true });
}
watch([() => state.search, () => state.estudiante], () => { clearTimeout(debounce); debounce = setTimeout(() => reload(), 350); });
watch([() => state.estatus, () => state.tipo_pago], () => reload());

function eliminar(r) {
    confirmDelete({ title: '¿Eliminar pago?', content: `#${r.id} · ${r.alumno || ''}`, onOk: () => router.delete(route('admin.pagos.destroy', r.id), { preserveScroll: true }) });
}

const comprobante = ref(null);
function verComprobante(r) { comprobante.value = r; }

// --- Acciones rápidas: aprobar / rechazar ---
const pendientes = ['pendiente', 'revisando', 'procesando'];
const esPendiente = (r) => pendientes.includes(r.estatus);

function aprobar(r) {
    confirmAction({
        title: `¿Aprobar el pago de ${r.alumno || 'este estudiante'}?`,
        content: 'Se activará el plan premium y se enviará un correo de confirmación al estudiante.',
        okText: 'Sí, aprobar',
        tone: 'ask',
        onOk: () => router.post(route('admin.pagos.aprobar', r.id), {}, {
            preserveScroll: true,
            onSuccess: () => message.success('Pago aprobado y correo enviado.'),
        }),
    });
}

const rechazo = useForm({ motivo_rechazo: '' });
const rechazando = ref(null);
const motivosRapidos = [
    'El comprobante está borroso o incompleto.',
    'El monto no coincide con $800.00 MXN.',
    'La referencia no coincide con la ficha de pago.',
    'El comprobante no corresponde a este pago.',
    'No se adjuntó un comprobante válido.',
];
function abrirRechazo(r) {
    rechazando.value = r;
    rechazo.reset();
    rechazo.clearErrors();
}
function confirmarRechazo() {
    rechazo.transform((d) => ({ ...d, motivo_rechazo: d.motivo_rechazo?.trim() || 'Pago rechazado por el administrador.' }))
        .post(route('admin.pagos.rechazar', rechazando.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                message.success('Pago rechazado. Se envió un correo al estudiante para que lo corrija.');
                rechazando.value = null;
            },
        });
}

const money = (n) => '$' + Number(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 });
function estadoColor(s) {
    return { aprobado: 'green', completado: 'green', rechazado: 'red', cancelado: 'red', pendiente: 'orange', revisando: 'blue', procesando: 'blue' }[s] || 'default';
}
const estadoLabel = (s) => ({ revisando: 'En revisión', procesando: 'Procesando' }[s] || (s ? s[0].toUpperCase() + s.slice(1) : '—'));

const pagination = computed(() => ({
    current: props.pagos.current_page,
    pageSize: props.pagos.per_page,
    total: props.pagos.total,
    showSizeChanger: false,
    showTotal: (t) => `${t} pagos`,
}));
function onChange(pag) { reload({ page: pag.current }); }

const columns = [
    { title: 'Referencia', key: 'ref', width: 210 },
    { title: 'Estudiante', dataIndex: 'alumno', key: 'alumno', width: 200 },
    { title: 'Monto', dataIndex: 'monto', key: 'monto', width: 110, align: 'right' },
    { title: 'Método', dataIndex: 'tipo_pago', key: 'tipo', width: 130 },
    { title: 'Estado', dataIndex: 'estatus', key: 'estatus', width: 130 },
    { title: 'Fecha y hora', dataIndex: 'fecha_pago', key: 'fecha', width: 150 },
    { title: 'Acciones', key: 'acciones', width: 240, align: 'right' },
];
</script>

<template>
    <AdminLayout title="Pagos">
        <PageHead title="Pagos" subtitle="Transacciones del curso premium" :icon="DollarOutlined">
            <template #actions>
                <Link :href="route('admin.pagos.dashboard')"><a-button>Ver dashboard</a-button></Link>
                <Link :href="route('admin.pagos.create')"><a-button type="primary"><template #icon><PlusOutlined /></template>Registrar pago</a-button></Link>
            </template>
        </PageHead>

        <div class="sains-stats sains-stats--4">
            <StatCard label="Ingresos" :value="money(stats.ingresos)" color="green" />
            <StatCard label="Pendientes" :value="stats.pendientes" color="amber" />
            <StatCard label="Aprobados" :value="stats.aprobados" color="indigo" />
            <StatCard label="Rechazados" :value="stats.rechazados" color="red" />
        </div>

        <a-alert
            v-if="stats.pendientes"
            type="warning"
            show-icon
            class="pagos-alerta"
            :message="`Tienes ${stats.pendientes} pago${stats.pendientes === 1 ? '' : 's'} por revisar. Usa los botones de aprobar o rechazar en cada fila.`"
        />

        <a-card :bordered="false">
            <a-table class="filtered-table" :columns="columns" :data-source="pagos.data" :pagination="pagination" row-key="id" size="middle" :scroll="{ x: 1090 }" @change="onChange">
                <template #summary>
                    <a-table-summary-row>
                        <a-table-summary-cell v-for="(col, i) in columns" :key="col.key ?? i" :index="i">
                            <a-input v-if="col.key === 'ref'" v-model:value="state.search" size="small" allow-clear placeholder="Referencia…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-input v-else-if="col.key === 'alumno'" v-model:value="state.estudiante" size="small" allow-clear placeholder="Estudiante…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-select v-else-if="col.key === 'estatus'" v-model:value="state.estatus" size="small" allow-clear placeholder="Todos"
                                :options="estadosPago.map((e) => ({ value: e, label: e }))" />
                            <a-select v-else-if="col.key === 'tipo'" v-model:value="state.tipo_pago" size="small" allow-clear placeholder="Todos"
                                :options="tiposPago.map((t) => ({ value: t, label: t }))" />
                        </a-table-summary-cell>
                    </a-table-summary-row>
                </template>

                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'ref'">
                        <span v-if="record.referencia" class="sains-strong">{{ record.referencia }}</span>
                        <span v-else class="sains-faint">Sin referencia</span>
                    </template>
                    <template v-else-if="column.key === 'monto'">{{ money(record.monto) }}</template>
                    <template v-else-if="column.key === 'fecha'">
                        <div class="pago-fecha">
                            <span>{{ record.fecha_pago || '—' }}</span>
                            <small v-if="record.hora_pago">{{ record.hora_pago }}</small>
                        </div>
                    </template>
                    <template v-else-if="column.key === 'estatus'">
                        <a-tag :color="estadoColor(record.estatus)" :bordered="false">{{ estadoLabel(record.estatus) }}</a-tag>
                    </template>
                    <template v-else-if="column.key === 'acciones'">
                        <RowActions
                            :view-href="route('admin.pagos.show', record.id)"
                            :edit-href="route('admin.pagos.edit', record.id)"
                            @delete="eliminar(record)"
                        >
                            <template #extra>
                                <template v-if="esPendiente(record)">
                                    <a-tooltip title="Aprobar pago y activar curso">
                                        <button type="button" class="row-actions__btn is-ok" @click="aprobar(record)"><CheckOutlined /></button>
                                    </a-tooltip>
                                    <a-tooltip title="Rechazar y pedir corrección por correo">
                                        <button type="button" class="row-actions__btn is-no" @click="abrirRechazo(record)"><CloseOutlined /></button>
                                    </a-tooltip>
                                </template>
                                <a-tooltip :title="record.comprobante_url ? 'Ver comprobante' : 'Sin comprobante'">
                                    <button
                                        type="button"
                                        class="row-actions__btn is-view"
                                        :disabled="!record.comprobante_url"
                                        @click="record.comprobante_url && verComprobante(record)"
                                    >
                                        <FileImageOutlined />
                                    </button>
                                </a-tooltip>
                            </template>
                        </RowActions>
                    </template>
                </template>
            </a-table>
        </a-card>

        <!-- Modal: rechazar con motivo -->
        <a-modal
            :open="!!rechazando"
            title="Rechazar pago"
            class="sains-modal"
            :width="520"
            :confirm-loading="rechazo.processing"
            ok-text="Rechazar y enviar correo"
            :ok-button-props="{ danger: true }"
            @ok="confirmarRechazo"
            @cancel="rechazando = null"
        >
            <a-form layout="vertical">
                <p class="rj-intro">
                    Se marcará el pago de <strong>{{ rechazando?.alumno }}</strong> como rechazado y se enviará
                    un correo al estudiante con el motivo para que <strong>corrija y reenvíe</strong> su comprobante.
                </p>
                <a-form-item label="Motivo (aparece en el correo)"
                    :validate-status="rechazo.errors.motivo_rechazo ? 'error' : undefined"
                    :help="rechazo.errors.motivo_rechazo">
                    <a-textarea v-model:value="rechazo.motivo_rechazo" :rows="3" :maxlength="500" show-count
                        placeholder="Explica qué debe corregir el estudiante…" />
                </a-form-item>
                <div class="rj-chips">
                    <button v-for="m in motivosRapidos" :key="m" type="button" class="rj-chip" @click="rechazo.motivo_rechazo = m">
                        {{ m }}
                    </button>
                </div>
            </a-form>
        </a-modal>

        <!-- Modal: ver comprobante -->
        <a-modal
            :open="!!comprobante"
            :title="`Comprobante · pago #${comprobante?.id}`"
            :footer="null"
            width="640px"
            centered
            @cancel="comprobante = null"
        >
            <div v-if="comprobante" class="cmp-view">
                <template v-if="comprobante.comprobante_es_pdf">
                    <a-result status="info" title="Comprobante en PDF">
                        <template #extra>
                            <a-button type="primary" :href="comprobante.comprobante_url" target="_blank">
                                <template #icon><DownloadOutlined /></template>Abrir PDF
                            </a-button>
                        </template>
                    </a-result>
                </template>
                <template v-else>
                    <img :src="comprobante.comprobante_url" alt="Comprobante" />
                    <a-button block :href="comprobante.comprobante_url" target="_blank" style="margin-top: 12px">
                        <template #icon><DownloadOutlined /></template>Abrir en tamaño completo
                    </a-button>
                </template>
                <div v-if="esPendiente(comprobante)" class="cmp-quick">
                    <a-button type="primary" style="background:#16a34a" @click="aprobar(comprobante); comprobante = null">
                        <template #icon><CheckOutlined /></template>Aprobar
                    </a-button>
                    <a-button danger @click="abrirRechazo(comprobante); comprobante = null">
                        <template #icon><CloseOutlined /></template>Rechazar
                    </a-button>
                </div>
            </div>
        </a-modal>
    </AdminLayout>
</template>

<style scoped>
.pagos-alerta { margin-bottom: 16px; border-radius: 12px; }

.cmp-view img { width: 100%; border-radius: 10px; border: 1px solid var(--sains-line); }
.cmp-quick { display: flex; gap: 10px; margin-top: 14px; }

.pago-fecha { display: flex; flex-direction: column; line-height: 1.25; }
.pago-fecha small { color: #94a3b8; font-size: 11px; }

.rj-intro { font-size: 13px; color: #64748b; line-height: 1.55; margin: 0 0 16px; }
.rj-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
.rj-chip {
    border: 1px solid var(--sains-line); background: #f8fafc; color: #475569;
    font-size: 11.5px; padding: 5px 10px; border-radius: 999px; cursor: pointer;
    transition: all .12s ease; text-align: left;
}
.rj-chip:hover { border-color: #c7d2fe; background: #eef2ff; color: #4338ca; }
</style>
