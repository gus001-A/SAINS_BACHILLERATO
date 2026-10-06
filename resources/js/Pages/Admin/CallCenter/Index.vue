<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import {
    PlusOutlined, SearchOutlined, PhoneOutlined, RedoOutlined,
    EditOutlined, DeleteOutlined, MailOutlined, WhatsAppOutlined, ClockCircleOutlined,
} from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';
import DateField from '@/Components/DateField.vue';
import { confirmDelete } from '@/lib/notify';
import { hoyISO, ahoraHM } from '@/lib/forms';

const props = defineProps({
    grupos: { type: Array, default: () => [] },
    pagination: { type: Object, default: () => ({}) },
    estudiantes: { type: Array, default: () => [] },
    stats: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const state = reactive({
    busqueda: props.filters.busqueda ?? '',
    estado: props.filters.estado ?? undefined,
});
let debounce = null;
function reload(extra = {}) {
    router.get(route('admin.callcenter.index'), {
        busqueda: state.busqueda || undefined, estado: state.estado || undefined, ...extra,
    }, { preserveState: true, replace: true, preserveScroll: true });
}
watch(() => state.busqueda, () => { clearTimeout(debounce); debounce = setTimeout(() => reload(), 350); });
watch(() => state.estado, () => reload());

const estudianteOpts = computed(() => props.estudiantes.map((e) => ({ value: e.id, label: e.label })));
const tipoOpts = [{ value: 'llamada', label: 'Llamada' }, { value: 'email', label: 'Email' }, { value: 'whatsapp', label: 'WhatsApp' }];
const estadoOpts = [{ value: 'pendiente', label: 'Pendiente' }, { value: 'en_proceso', label: 'En proceso' }, { value: 'finalizado', label: 'Finalizado' }];

const modalOpen = ref(false);
const editing = ref(null);
const form = useForm({
    id_estudiante: undefined, fecha_contacto: null, hora_contacto: null,
    tipo_contacto: 'llamada', estado_seguimiento: 'pendiente',
    motivo_contacto: '', nota: '', resultado: '', proximo_contacto: null,
});

function openCreate(idEstudiante = undefined) {
    editing.value = null;
    form.reset();
    form.tipo_contacto = 'llamada';
    form.estado_seguimiento = 'pendiente';
    form.fecha_contacto = hoyISO();
    form.hora_contacto = ahoraHM();
    if (idEstudiante) form.id_estudiante = idEstudiante;
    form.clearErrors();
    modalOpen.value = true;
}
function openEdit(r) {
    editing.value = r;
    form.id_estudiante = r.id_estudiante;
    form.fecha_contacto = r.fecha_contacto;
    form.hora_contacto = r.hora_contacto;
    form.tipo_contacto = r.tipo_contacto;
    form.estado_seguimiento = r.estado_seguimiento;
    form.motivo_contacto = r.motivo_contacto;
    form.nota = r.nota ?? '';
    form.resultado = r.resultado ?? '';
    form.proximo_contacto = r.proximo_contacto;
    form.clearErrors();
    modalOpen.value = true;
}
function submit() {
    const opts = { onSuccess: () => { modalOpen.value = false; }, preserveScroll: true };
    if (editing.value) form.put(route('admin.callcenter.update', editing.value.id), opts);
    else form.post(route('admin.callcenter.store'), opts);
}
function eliminar(r) {
    confirmDelete({
        title: '¿Eliminar interacción?',
        content: `${r.motivo_contacto || 'Interacción'} · ${r.fecha_contacto}`,
        onOk: () => router.delete(route('admin.callcenter.destroy', r.id), { preserveScroll: true }),
    });
}

const pag = computed(() => ({
    current: props.pagination.current_page || 1,
    pageSize: props.pagination.per_page || 12,
    total: props.pagination.total || 0,
    showSizeChanger: false,
    showTotal: (t) => `${t} estudiantes contactados`,
}));
function onChange(p) { reload({ page: p.current }); }

const estadoColor = (s) => ({ pendiente: 'orange', en_proceso: 'blue', finalizado: 'green' }[s] || 'default');
const tipoIcon = (t) => ({ llamada: PhoneOutlined, email: MailOutlined, whatsapp: WhatsAppOutlined }[t] || PhoneOutlined);

const columns = [
    { title: 'Estudiante', key: 'estudiante' },
    { title: 'Interacciones', key: 'total', width: 130, align: 'center' },
    { title: 'Pendientes', key: 'pendientes', width: 120, align: 'center' },
    { title: 'Último contacto', dataIndex: 'ultima_fecha', key: 'ultima', width: 150 },
    { title: '', key: 'acciones', width: 170, align: 'right' },
];
</script>

<template>
    <AdminLayout title="Call Center">
        <PageHead title="Call Center" subtitle="Seguimiento de contactos, agrupado por estudiante" :icon="PhoneOutlined">
            <template #actions>
                <a-button type="primary" @click="openCreate()"><template #icon><PlusOutlined /></template>Nueva interacción</a-button>
            </template>
        </PageHead>

        <div class="sains-stats sains-stats--4">
            <StatCard label="Total interacciones" :value="stats.total" color="indigo" />
            <StatCard label="Pendientes" :value="stats.pendientes" color="amber" />
            <StatCard label="En proceso" :value="stats.enProceso" color="violet" />
            <StatCard label="Estudiantes contactados" :value="stats.estudiantesContactados" color="green" />
        </div>

        <a-card :bordered="false">
            <a-table
                class="filtered-table"
                :columns="columns"
                :data-source="grupos"
                :pagination="pag"
                row-key="id_estudiante"
                size="middle"
                :expand-row-by-click="true"
                @change="onChange"
            >
                <template #emptyText><a-empty :image="null" description="Sin interacciones registradas" /></template>

                <template #summary>
                    <a-table-summary-row>
                        <a-table-summary-cell :index="0" />
                        <a-table-summary-cell v-for="(col, i) in columns" :key="col.key ?? i" :index="i + 1">
                            <a-input v-if="col.key === 'estudiante'" v-model:value="state.busqueda" size="small" allow-clear placeholder="Buscar estudiante, motivo o nota…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-select v-else-if="col.key === 'ultima'" v-model:value="state.estado" size="small" allow-clear placeholder="Todos los estados" :options="estadoOpts" />
                        </a-table-summary-cell>
                    </a-table-summary-row>
                </template>

                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'estudiante'">
                        <div class="cc-student">
                            <span class="cc-avatar">{{ (record.estudiante || '?').slice(0, 1).toUpperCase() }}</span>
                            <div>
                                <div style="font-weight: 650">{{ record.estudiante }}</div>
                                <div class="sains-cell-sub">{{ record.correo || '—' }}</div>
                            </div>
                        </div>
                    </template>
                    <template v-else-if="column.key === 'total'">
                        <a-tag :bordered="false" color="blue">{{ record.total }}</a-tag>
                    </template>
                    <template v-else-if="column.key === 'pendientes'">
                        <a-tag v-if="record.pendientes" :bordered="false" color="orange">{{ record.pendientes }}</a-tag>
                        <span v-else class="sains-faint">0</span>
                    </template>
                    <template v-else-if="column.key === 'acciones'">
                        <a-tag v-if="record.estado_ultima === 'finalizado'" :bordered="false" color="green">Finalizado</a-tag>
                        <a-tooltip v-else title="Registrar una nueva interacción para este estudiante">
                            <a-button type="primary" ghost size="small" @click.stop="openCreate(record.id_estudiante)">
                                <template #icon><RedoOutlined /></template>Retomar
                            </a-button>
                        </a-tooltip>
                    </template>
                </template>

                <template #expandedRowRender="{ record }">
                    <div class="cc-timeline">
                        <div v-for="it in record.interacciones" :key="it.id" class="cc-item">
                            <span class="cc-item__icon" :class="`is-${it.estado_seguimiento}`">
                                <component :is="tipoIcon(it.tipo_contacto)" />
                            </span>
                            <div class="cc-item__body">
                                <div class="cc-item__head">
                                    <b>{{ it.motivo_contacto || 'Sin motivo' }}</b>
                                    <a-tag :bordered="false" :color="estadoColor(it.estado_seguimiento)">{{ it.estado_seguimiento }}</a-tag>
                                </div>
                                <div class="cc-item__meta">
                                    {{ it.tipo_contacto }} · {{ it.fecha_contacto }} {{ it.hora_contacto }} · {{ it.admin }}
                                    <template v-if="it.proximo_contacto"> · próximo: {{ it.proximo_contacto }}</template>
                                </div>
                                <div v-if="it.resultado" class="cc-item__res">Resultado: {{ it.resultado }}</div>
                                <div v-if="it.nota" class="cc-item__nota">{{ it.nota }}</div>
                            </div>
                            <div class="cc-item__actions">
                                <button class="cc-mini is-edit" title="Editar" @click.stop="openEdit(it)"><EditOutlined /></button>
                                <button class="cc-mini is-del" title="Eliminar" @click.stop="eliminar(it)"><DeleteOutlined /></button>
                            </div>
                        </div>
                    </div>
                </template>
            </a-table>
        </a-card>

        <a-modal v-model:open="modalOpen" :title="editing ? 'Editar interacción' : 'Nueva interacción'" class="sains-modal" :width="640" :confirm-loading="form.processing" ok-text="Guardar" @ok="submit">
            <a-form layout="vertical">
                <a-form-item label="Estudiante" required :validate-status="form.errors.id_estudiante ? 'error' : undefined" :help="form.errors.id_estudiante">
                    <a-select v-model:value="form.id_estudiante" show-search option-filter-prop="label" :options="estudianteOpts" placeholder="Busca al estudiante…" />
                </a-form-item>
                <a-row :gutter="16">
                    <a-col :xs="24" :md="8">
                        <a-form-item label="Fecha de contacto" required :validate-status="form.errors.fecha_contacto ? 'error' : undefined" :help="form.errors.fecha_contacto">
                            <DateField v-model:value="form.fecha_contacto" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :md="8">
                        <a-form-item :validate-status="form.errors.hora_contacto ? 'error' : undefined"
                            :help="form.errors.hora_contacto || (editing ? 'Hora registrada al crear la interacción' : 'Se registra automáticamente')">
                            <template #label><ClockCircleOutlined />Hora</template>
                            <a-input :value="form.hora_contacto" disabled readonly>
                                <template #prefix><ClockCircleOutlined /></template>
                            </a-input>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :md="8">
                        <a-form-item label="Medio" required>
                            <a-select v-model:value="form.tipo_contacto" :options="tipoOpts" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :md="12">
                        <a-form-item label="Estado de seguimiento" required>
                            <a-select v-model:value="form.estado_seguimiento" :options="estadoOpts" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :md="12">
                        <a-form-item label="Próximo contacto (opcional)">
                            <a-date-picker v-model:value="form.proximo_contacto" show-time
                                value-format="YYYY-MM-DD HH:mm" format="DD/MM/YYYY HH:mm" style="width: 100%" />
                        </a-form-item>
                    </a-col>
                </a-row>
                <a-form-item label="Motivo del contacto" required :validate-status="form.errors.motivo_contacto ? 'error' : undefined" :help="form.errors.motivo_contacto">
                    <a-input v-model:value="form.motivo_contacto" />
                </a-form-item>
                <a-form-item label="Resultado">
                    <a-input v-model:value="form.resultado" placeholder="p. ej. no contestó, interesado, agendó pago…" />
                </a-form-item>
                <a-form-item label="Nota">
                    <a-textarea v-model:value="form.nota" :rows="3" />
                </a-form-item>
            </a-form>
        </a-modal>
    </AdminLayout>
</template>

<style scoped>
.cc-toolbar { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }

.cc-student { display: flex; align-items: center; gap: 11px; }
.cc-avatar {
    width: 34px; height: 34px; border-radius: 10px; flex: none;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 13px; color: #fff;
    background: linear-gradient(135deg, #6366f1, #a855f7);
}

.cc-timeline { display: flex; flex-direction: column; gap: 10px; padding: 6px 4px; }
.cc-item {
    display: flex; gap: 12px; padding: 12px 14px; border-radius: 12px;
    background: #fff; border: 1px solid var(--sains-line);
}
.cc-item__icon {
    width: 32px; height: 32px; border-radius: 9px; flex: none;
    display: flex; align-items: center; justify-content: center; font-size: 14px; color: #fff;
    background: #94a3b8;
}
.cc-item__icon.is-pendiente { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
.cc-item__icon.is-en_proceso { background: linear-gradient(135deg, #60a5fa, #3b82f6); }
.cc-item__icon.is-finalizado { background: linear-gradient(135deg, #34d399, #10b981); }
.cc-item__body { flex: 1; min-width: 0; }
.cc-item__head { display: flex; align-items: center; gap: 8px; }
.cc-item__head b { font-size: 13.5px; color: #0f172a; }
.cc-item__meta { font-size: 11.5px; color: #94a3b8; margin-top: 2px; }
.cc-item__res { font-size: 12px; color: #475569; margin-top: 5px; }
.cc-item__nota { font-size: 12px; color: #64748b; margin-top: 4px; background: #f8fafc; border-radius: 8px; padding: 6px 9px; }
.cc-item__actions { display: flex; gap: 4px; align-items: flex-start; }
.cc-mini {
    width: 28px; height: 28px; border: 0; background: #f4f6fb; border-radius: 8px; cursor: pointer;
    color: #64748b; font-size: 12px; transition: all .14s ease;
}
.cc-mini.is-edit:hover { background: #eef2ff; color: #4f46e5; }
.cc-mini.is-del:hover { background: #fee2e2; color: #dc2626; }
</style>
