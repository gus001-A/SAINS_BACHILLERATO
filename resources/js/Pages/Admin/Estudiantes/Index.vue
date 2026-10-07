<script setup>
import { computed, reactive, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { PlusOutlined, SearchOutlined, TeamOutlined, FileExcelOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';
import RowActions from '@/Components/RowActions.vue';
import { confirmDelete } from '@/lib/notify';

const props = defineProps({
    estudiantes: { type: Object, required: true },
    stats: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    carreras: { type: Array, default: () => [] },
});

function fecha(iso) {
    if (!iso) return '—';
    const [y, m, d] = iso.split('-');
    return `${d}/${m}/${y}`;
}

const state = reactive({
    search: props.filters.search ?? '',
    telefono: props.filters.telefono ?? '',
    sexo: props.filters.sexo ?? undefined,
    plan_activo: props.filters.plan_activo ?? undefined,
    carrera_id: props.filters.carrera_id ? Number(props.filters.carrera_id) : undefined,
});

let debounce = null;
function reload(extra = {}) {
    router.get(route('admin.estudiantes.index'), {
        search: state.search || undefined,
        telefono: state.telefono || undefined,
        sexo: state.sexo || undefined,
        plan_activo: state.plan_activo ?? undefined,
        carrera_id: state.carrera_id || undefined,
        ...extra,
    }, { preserveState: true, replace: true, preserveScroll: true });
}
watch([() => state.search, () => state.telefono], () => { clearTimeout(debounce); debounce = setTimeout(() => reload(), 350); });
watch([() => state.sexo, () => state.plan_activo, () => state.carrera_id], () => reload());

function eliminar(r) {
    confirmDelete({ title: r.sin_perfil ? '¿Eliminar usuario?' : '¿Eliminar estudiante?', content: r.sin_perfil ? `${r.correo} no terminó su registro. Se eliminará su cuenta.` : r.nombre_completo, onOk: () => router.delete(route('admin.estudiantes.destroy', r.id), { preserveScroll: true }) });
}

const pagination = computed(() => ({
    current: props.estudiantes.current_page,
    pageSize: props.estudiantes.per_page,
    total: props.estudiantes.total,
    showSizeChanger: false,
    showTotal: (t) => `${t} estudiantes`,
}));
function onChange(pag) { reload({ page: pag.current }); }

const columns = [
    { title: 'Estudiante', key: 'nombre' },
    { title: 'Correo', key: 'correo', width: 220 },
    { title: 'Teléfono', dataIndex: 'telefono', key: 'telefono', width: 130 },
    { title: 'Carrera', key: 'carrera', width: 190 },
    { title: 'Fecha de registro', key: 'registro', width: 140, align: 'center' },
    { title: 'Documentos', key: 'documentos', width: 150, align: 'center' },
    { title: 'Plan', key: 'plan', width: 120 },
    { title: '', key: 'acciones', width: 130, align: 'right' },
];
</script>

<template>
    <AdminLayout title="Estudiantes">
        <PageHead title="Estudiantes" subtitle="Alumnos registrados en la plataforma" :icon="TeamOutlined">
            <template #actions>
                <a-button :href="route('admin.estudiantes.exportar.excel')">
                    <template #icon><FileExcelOutlined /></template>Exportar
                </a-button>
                <Link :href="route('admin.estudiantes.create')">
                    <a-button type="primary"><template #icon><PlusOutlined /></template>Nuevo estudiante</a-button>
                </Link>
            </template>
        </PageHead>

        <div class="sains-stats sains-stats--4">
            <StatCard label="Total" :value="stats.total" color="indigo" />
            <StatCard label="Con plan activo" :value="stats.activos" color="green" />
            <StatCard label="Sin plan" :value="stats.inactivos" color="slate" />
            <StatCard label="Con cupón" :value="stats.conCupon" color="pink" />
        </div>

        <a-card :bordered="false">
            <a-table class="filtered-table" :columns="columns" :data-source="estudiantes.data" :pagination="pagination" row-key="id" size="middle" :scroll="{ x: 1300 }" @change="onChange">
                <template #summary>
                    <a-table-summary-row>
                        <a-table-summary-cell v-for="(col, i) in columns" :key="col.key ?? i" :index="i">
                            <a-input v-if="col.key === 'nombre'" v-model:value="state.search" size="small" allow-clear placeholder="Nombre o correo…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-input v-else-if="col.key === 'telefono'" v-model:value="state.telefono" size="small" allow-clear placeholder="Teléfono…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-select v-else-if="col.key === 'carrera'" v-model:value="state.carrera_id" size="small" allow-clear placeholder="Todas"
                                style="width: 100%" :options="carreras" />
                            <a-select v-else-if="col.key === 'plan'" v-model:value="state.plan_activo" size="small" allow-clear placeholder="Todos"
                                :options="[{ value: 1, label: 'Activo' }, { value: 0, label: 'Sin plan' }]" />
                        </a-table-summary-cell>
                    </a-table-summary-row>
                </template>

                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'nombre'">
                        <div style="font-weight:600">{{ record.nombre_completo }}</div>
                        <a-tag v-if="record.sin_perfil" color="orange" style="margin-top:2px">Perfil incompleto</a-tag>
                    </template>
                    <template v-else-if="column.key === 'correo'">{{ record.correo }}</template>
                    <template v-else-if="column.key === 'telefono'">{{ record.telefono || '—' }}</template>
                    <template v-else-if="column.key === 'carrera'">
                        <span v-if="record.carrera">{{ record.carrera }}</span>
                        <a-tag v-else-if="!record.sin_perfil" color="gold">Sin carrera</a-tag>
                        <span v-else>—</span>
                    </template>
                    <template v-else-if="column.key === 'registro'">{{ fecha(record.fecha_registro) }}</template>
                    <template v-else-if="column.key === 'documentos'">
                        <a-tag :color="record.documentos_aprobados === record.documentos_requeridos ? 'green' : record.documentos_aprobados > 0 ? 'blue' : 'default'">
                            {{ record.documentos_aprobados }} / {{ record.documentos_requeridos }}
                        </a-tag>
                    </template>
                    <template v-else-if="column.key === 'plan'">
                        <a-tag :color="record.plan_activo ? 'green' : 'default'">{{ record.plan_activo ? 'Activo' : 'Sin plan' }}</a-tag>
                        <a-tag v-if="record.cupon" color="blue">{{ record.cupon }}</a-tag>
                    </template>
                    <template v-else-if="column.key === 'acciones'">
                        <RowActions
                            :view-href="route('admin.estudiantes.show', record.id)"
                            :edit-href="record.sin_perfil ? null : route('admin.estudiantes.edit', record.id)"
                            @delete="eliminar(record)"
                        />
                    </template>
                </template>
            </a-table>
        </a-card>
    </AdminLayout>
</template>
