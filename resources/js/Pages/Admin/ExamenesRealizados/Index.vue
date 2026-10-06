<script setup>
import { computed, reactive, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { SearchOutlined, CheckCircleOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';
import RowActions from '@/Components/RowActions.vue';
import { confirmDelete } from '@/lib/notify';

const props = defineProps({
    examenes: { type: Object, required: true },
    stats: { type: Object, required: true },
    tiposExamen: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const state = reactive({
    estudiante: props.filters.estudiante ?? '',
    tipo_examen: props.filters.tipo_examen ?? undefined,
    calificacion: props.filters.calificacion ?? undefined,
    intento: props.filters.intento ?? undefined,
});
let debounce = null;
function reload(extra = {}) {
    router.get(route('admin.examenes-realizados.index'), {
        estudiante: state.estudiante || undefined, tipo_examen: state.tipo_examen || undefined,
        calificacion: state.calificacion || undefined, intento: state.intento || undefined, ...extra,
    }, { preserveState: true, replace: true, preserveScroll: true });
}
watch([() => state.estudiante, () => state.intento], () => { clearTimeout(debounce); debounce = setTimeout(() => reload(), 350); });
watch([() => state.tipo_examen, () => state.calificacion], () => reload());

function eliminar(r) {
    confirmDelete({ title: '¿Eliminar registro?', content: `Examen de ${r.estudiante}`, onOk: () => router.delete(route('admin.examenes-realizados.destroy', r.id), { preserveScroll: true }) });
}

const pagination = computed(() => ({
    current: props.examenes.current_page, pageSize: props.examenes.per_page, total: props.examenes.total,
    showSizeChanger: false, showTotal: (t) => `${t} registros`,
}));
function onChange(pag) { reload({ page: pag.current }); }

function califColor(c) {
    if (c == null) return 'default';
    if (c >= 80) return 'green';
    if (c >= 60) return 'blue';
    return 'red';
}

const columns = [
    { title: 'Estudiante', dataIndex: 'estudiante', key: 'estudiante' },
    { title: 'Tipo', dataIndex: 'tipo_examen', key: 'tipo', width: 150 },
    { title: 'Calificación', dataIndex: 'calificacion', key: 'calif', width: 150, align: 'center' },
    { title: 'Intento', dataIndex: 'intento', key: 'intento', width: 110, align: 'center' },
    { title: 'Fecha y hora', dataIndex: 'fecha', key: 'fecha', width: 160 },
    { title: '', key: 'acciones', width: 90, align: 'right' },
];
</script>

<template>
    <AdminLayout title="Exámenes realizados">
        <PageHead title="Exámenes realizados" subtitle="Intentos de los estudiantes" :icon="CheckCircleOutlined" />

        <div class="sains-stats sains-stats--4">
            <StatCard label="Total" :value="stats.total" color="indigo" />
            <StatCard label="Promedio" :value="`${stats.promedio} / 100`" color="violet" />
            <StatCard label="Excelentes (≥80)" :value="stats.excelentes" color="green" />
            <StatCard label="Reprobados (<60)" :value="stats.reprobados" color="red" />
        </div>

        <a-card :bordered="false">
            <a-table class="filtered-table" :columns="columns" :data-source="examenes.data" :pagination="pagination" row-key="id" size="middle" :scroll="{ x: 800 }" @change="onChange">
                <template #summary>
                    <a-table-summary-row>
                        <a-table-summary-cell v-for="(col, i) in columns" :key="col.key ?? i" :index="i">
                            <a-input v-if="col.key === 'estudiante'" v-model:value="state.estudiante" size="small" allow-clear placeholder="Buscar estudiante…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-select v-else-if="col.key === 'tipo'" v-model:value="state.tipo_examen" size="small" allow-clear placeholder="Todos"
                                :options="tiposExamen.map((t) => ({ value: t, label: t }))" />
                            <a-select v-else-if="col.key === 'calif'" v-model:value="state.calificacion" size="small" allow-clear placeholder="Todas"
                                :options="[{ value: 'excelente', label: 'Excelente ≥80' }, { value: 'aprobado', label: 'Aprobado 60–79' }, { value: 'reprobado', label: 'Reprobado <60' }]" />
                            <a-input-number v-else-if="col.key === 'intento'" v-model:value="state.intento" size="small" :min="1" placeholder="N.º" />
                        </a-table-summary-cell>
                    </a-table-summary-row>
                </template>
                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'estudiante'">
                        <span style="font-weight:600">{{ record.estudiante }}</span>
                    </template>
                    <template v-else-if="column.key === 'calif'">
                        <a-tag :color="califColor(record.calificacion)">{{ record.calificacion ?? '—' }}</a-tag>
                    </template>
                    <template v-else-if="column.key === 'acciones'">
                        <RowActions :view-href="route('admin.examenes-realizados.show', record.id)" @delete="eliminar(record)" />
                    </template>
                </template>
            </a-table>
        </a-card>
    </AdminLayout>
</template>
