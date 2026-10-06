<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { PlusOutlined, CopyOutlined, ThunderboltOutlined, SearchOutlined, FileTextOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';
import RowActions from '@/Components/RowActions.vue';
import { confirmDelete, confirmAction } from '@/lib/notify';

const props = defineProps({
    examenes: { type: Object, required: true },
    areas: { type: Array, default: () => [] },
    tiposExamen: { type: Array, default: () => [] },
    stats: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const state = reactive({
    search: props.filters.search ?? '',
    tipo: props.filters.tipo ?? undefined,
    rango: props.filters.rango ?? undefined,
});
let debounce = null;
function reload(extra = {}) {
    router.get(route('admin.examenes.index'), {
        search: state.search || undefined, tipo: state.tipo || undefined, rango: state.rango || undefined, ...extra,
    }, { preserveState: true, replace: true, preserveScroll: true });
}
watch(() => state.search, () => { clearTimeout(debounce); debounce = setTimeout(() => reload(), 350); });
watch([() => state.tipo, () => state.rango], () => reload());

function eliminar(r) {
    confirmDelete({ title: '¿Eliminar examen?', content: `#${r.id} · ${r.tipo_examen}`, onOk: () => router.delete(route('admin.examenes.destroy', r.id), { preserveScroll: true }) });
}
function duplicar(r) {
    confirmAction({ title: '¿Duplicar examen?', content: `Se creará una copia de #${r.id}`, okText: 'Duplicar', onOk: () => router.post(route('admin.examenes.duplicar', r.id), {}, { preserveScroll: true }) });
}

const autoOpen = ref(false);
const auto = useForm({ tipo_examen: undefined, tiempo: 30, numero_preguntas: 20, areas: [] });
function generar() {
    auto.post(route('admin.examenes.generar.automatico'), { onSuccess: () => { autoOpen.value = false; auto.reset(); } });
}

const pagination = computed(() => ({
    current: props.examenes.current_page, pageSize: props.examenes.per_page, total: props.examenes.total,
    showSizeChanger: false, showTotal: (t) => `${t} exámenes`,
}));
function onChange(pag) { reload({ page: pag.current }); }

const tipoColor = (t) => {
    const s = (t || '').toLowerCase();
    if (s.includes('simul')) return 'green';
    if (s.includes('materia')) return 'purple';
    if (s.includes('curso') || s.includes('general')) return 'geekblue';
    return 'default';
};

const columns = [
    { title: 'Examen', key: 'id', width: 100 },
    { title: 'Tipo', dataIndex: 'tipo_examen', key: 'tipo' },
    { title: 'Preguntas', dataIndex: 'numero_preguntas', key: 'preguntas', width: 120, align: 'center' },
    { title: 'Tiempo', key: 'tiempo', width: 110, align: 'center' },
    { title: 'Creado', dataIndex: 'created_at', key: 'created_at', width: 150 },
    { title: '', key: 'acciones', width: 160, align: 'right' },
];
</script>

<template>
    <AdminLayout title="Exámenes">
        <PageHead title="Exámenes" subtitle="Plantillas de examen del sistema" :icon="FileTextOutlined">
            <template #actions>
                <Link :href="route('admin.examenes.dashboard')"><a-button>Dashboard</a-button></Link>
                <a-button @click="autoOpen = true"><template #icon><ThunderboltOutlined /></template>Generar automático</a-button>
                <Link :href="route('admin.examenes.create')"><a-button type="primary"><template #icon><PlusOutlined /></template>Nuevo examen</a-button></Link>
            </template>
        </PageHead>

        <div class="sains-stats sains-stats--4">
            <StatCard label="Total" :value="stats.total" color="indigo" />
            <StatCard label="Materia" :value="stats.materia" color="violet" />
            <StatCard label="Simulación" :value="stats.simulacion" color="green" />
            <StatCard label="Preguntas asignadas" :value="stats.preguntasAsignadas" color="amber" />
        </div>

        <a-card :bordered="false">
            <a-table class="filtered-table" :columns="columns" :data-source="examenes.data" :pagination="pagination" row-key="id" size="middle" :scroll="{ x: 800 }" @change="onChange">
                <template #summary>
                    <a-table-summary-row>
                        <a-table-summary-cell v-for="(col, i) in columns" :key="col.key ?? i" :index="i">
                            <a-input v-if="col.key === 'id'" v-model:value="state.search" size="small" allow-clear placeholder="ID/tipo…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-select v-else-if="col.key === 'tipo'" v-model:value="state.tipo" size="small" allow-clear placeholder="Todos"
                                :options="tiposExamen.map((t) => ({ value: t, label: t }))" />
                            <a-select v-else-if="col.key === 'preguntas'" v-model:value="state.rango" size="small" allow-clear placeholder="Todos"
                                :options="[{ value: '0-20', label: '0–20' }, { value: '21-50', label: '21–50' }, { value: '51-100', label: '51–100' }, { value: '100+', label: '100+' }]" />
                        </a-table-summary-cell>
                    </a-table-summary-row>
                </template>
                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'id'"><span style="font-weight:600">#{{ record.id }}</span></template>
                    <template v-else-if="column.key === 'tipo'">
                        <a-tag :bordered="false" :color="tipoColor(record.tipo_examen)">{{ record.tipo_examen || '—' }}</a-tag>
                    </template>
                    <template v-else-if="column.key === 'tiempo'">{{ record.tiempo }} min</template>
                    <template v-else-if="column.key === 'created_at'">
                        <span class="sains-faint" style="font-size:12.5px">{{ record.created_at || '—' }}</span>
                    </template>
                    <template v-else-if="column.key === 'acciones'">
                        <RowActions
                            :view-href="route('admin.examenes.show', record.id)"
                            :edit-href="route('admin.examenes.edit', record.id)"
                            @delete="eliminar(record)"
                        >
                            <template #extra>
                                <a-tooltip title="Duplicar">
                                    <button type="button" class="row-actions__btn is-copy" @click="duplicar(record)">
                                        <CopyOutlined />
                                    </button>
                                </a-tooltip>
                            </template>
                        </RowActions>
                    </template>
                </template>
            </a-table>
        </a-card>

        <a-modal v-model:open="autoOpen" title="Generar examen automático" class="sains-modal" :confirm-loading="auto.processing" ok-text="Generar" @ok="generar">
            <a-form layout="vertical" style="margin-top: 8px">
                <a-form-item label="Tipo de examen" required :validate-status="auto.errors.tipo_examen ? 'error' : undefined" :help="auto.errors.tipo_examen">
                    <a-select v-model:value="auto.tipo_examen" :options="tiposExamen.map((t) => ({ value: t, label: t }))" placeholder="Selecciona…" />
                </a-form-item>
                <a-row :gutter="16">
                    <a-col :span="12">
                        <a-form-item label="N.º de preguntas" required :validate-status="auto.errors.numero_preguntas ? 'error' : undefined" :help="auto.errors.numero_preguntas">
                            <a-input-number v-model:value="auto.numero_preguntas" :min="1" :max="200" style="width: 100%" />
                        </a-form-item>
                    </a-col>
                    <a-col :span="12">
                        <a-form-item label="Tiempo (min)" required>
                            <a-input-number v-model:value="auto.tiempo" :min="1" :max="180" style="width: 100%" />
                        </a-form-item>
                    </a-col>
                </a-row>
                <a-form-item label="Áreas (opcional — todas si se deja vacío)">
                    <a-select v-model:value="auto.areas" mode="multiple" :options="areas.map((a) => ({ value: a.id, label: a.nombre }))" placeholder="Todas las áreas" />
                </a-form-item>
            </a-form>
        </a-modal>
    </AdminLayout>
</template>
