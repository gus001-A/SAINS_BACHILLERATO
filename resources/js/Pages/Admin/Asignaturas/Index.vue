<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { PlusOutlined, SearchOutlined, BookOutlined, FilterOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';
import RowActions from '@/Components/RowActions.vue';
import { useIndex, laravelPagination } from '@/lib/useIndex';
import { confirmDelete } from '@/lib/notify';

const props = defineProps({
    asignaturas: { type: Object, required: true },
    stats: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const { filters, loading, go, reset, toPage, hasActiveFilters } = useIndex({
    routeName: 'admin.asignaturas.index',
    initial: { search: props.filters.search ?? '' },
    debounced: ['search'],
    only: ['asignaturas', 'stats', 'filters'],
});

const pagination = computed(() => laravelPagination(props.asignaturas, 'materias'));

function onTableChange(pag, _f, sorter) {
    if (sorter && sorter.order) {
        go({
            orden_campo: sorter.columnKey || sorter.field,
            orden_direccion: sorter.order === 'ascend' ? 'asc' : 'desc',
            page: pag.current,
        });
    } else {
        toPage(pag.current);
    }
}

const modalOpen = ref(false);
const editing = ref(null);
const form = useForm({ nombre: '' });

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}
function openEdit(record) {
    editing.value = record;
    form.nombre = record.nombre;
    form.clearErrors();
    modalOpen.value = true;
}
function submit() {
    const opts = { onSuccess: () => (modalOpen.value = false), preserveScroll: true };
    editing.value
        ? form.put(route('admin.asignaturas.update', editing.value.id), opts)
        : form.post(route('admin.asignaturas.store'), opts);
}
function eliminar(record) {
    confirmDelete({
        title: '¿Eliminar materia?',
        content: record.nombre,
        onOk: () => router.delete(route('admin.asignaturas.destroy', record.id), { preserveScroll: true }),
    });
}

const columns = [
    { title: 'Materia', dataIndex: 'nombre', key: 'nombre' },
    { title: 'Clases', dataIndex: 'total_clases', key: 'clases', width: 110, align: 'center', sorter: true },
    { title: '', key: 'acciones', width: 96, align: 'right' },
];
</script>

<template>
    <AdminLayout title="Materias">
        <PageHead title="Materias" subtitle="Asignaturas del plan de estudios" :icon="BookOutlined">
            <template #actions>
                <a-button v-if="hasActiveFilters()" @click="reset"><template #icon><FilterOutlined /></template>Limpiar</a-button>
                <a-button type="primary" @click="openCreate"><template #icon><PlusOutlined /></template>Nueva materia</a-button>
            </template>
        </PageHead>

        <div class="sains-stats sains-stats--3">
            <StatCard label="Total" :value="stats.total" color="indigo" />
            <StatCard label="Con clases" :value="stats.conClases" color="violet" />
            <StatCard label="En carreras" :value="stats.enCarreras" color="green" />
        </div>

        <a-card :bordered="false" :body-style="{ padding: '4px 4px 12px' }">
            <a-table class="filtered-table" :columns="columns" :data-source="asignaturas.data" :pagination="pagination"
                :loading="loading" row-key="id" size="middle" @change="onTableChange">
                <template #emptyText><a-empty :image="null" description="Sin resultados" /></template>

                <template #summary>
                    <a-table-summary-row>
                        <a-table-summary-cell v-for="(col, i) in columns" :key="col.key ?? i" :index="i">
                            <a-input v-if="col.key === 'nombre'" v-model:value="filters.search" size="small" allow-clear placeholder="Buscar materia…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                        </a-table-summary-cell>
                    </a-table-summary-row>
                </template>

                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'nombre'"><span class="sains-strong">{{ record.nombre }}</span></template>
                    <template v-else-if="column.key === 'clases'">
                        <span class="count-pill" :class="{ 'is-zero': !record.total_clases }">{{ record.total_clases }}</span>
                    </template>
                    <template v-else-if="column.key === 'acciones'">
                        <RowActions editable @edit="openEdit(record)" @delete="eliminar(record)" />
                    </template>
                </template>
            </a-table>
        </a-card>

        <a-modal v-model:open="modalOpen" :title="editing ? 'Editar materia' : 'Nueva materia'" class="sains-modal" :confirm-loading="form.processing" ok-text="Guardar" @ok="submit">
            <a-form layout="vertical">
                <a-form-item label="Nombre" required :validate-status="form.errors.nombre ? 'error' : undefined" :help="form.errors.nombre">
                    <a-input v-model:value="form.nombre" placeholder="p. ej. Pensamiento matemático" @press-enter="submit" />
                </a-form-item>
            </a-form>
        </a-modal>
    </AdminLayout>
</template>
