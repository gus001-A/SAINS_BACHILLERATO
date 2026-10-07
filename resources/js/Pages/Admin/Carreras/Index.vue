<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { PlusOutlined, SearchOutlined, ApartmentOutlined, FilterOutlined, FileTextOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';
import RowActions from '@/Components/RowActions.vue';
import { confirmDelete } from '@/lib/notify';

const props = defineProps({
    carreras: { type: Array, required: true },
    stats: { type: Object, required: true },
    iconos: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
});

const filtros = reactive({ search: props.filters.search ?? '' });
let t = null;
watch(filtros, () => {
    clearTimeout(t);
    t = setTimeout(() => router.get(route('admin.carreras.index'), Object.fromEntries(
        Object.entries(filtros).filter(([, v]) => v !== '' && v !== undefined && v !== null),
    ), { preserveState: true, preserveScroll: true, replace: true }), 300);
});
const hayFiltros = computed(() => !!filtros.search);
function limpiar() { filtros.search = ''; }

const modalOpen = ref(false);
const editing = ref(null);
const form = useForm({ nombre: '', descripcion: '', icono: 'fa-graduation-cap' });

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}
function openEdit(c) {
    editing.value = c;
    Object.assign(form, { nombre: c.nombre, descripcion: c.descripcion ?? '', icono: c.icono || 'fa-graduation-cap' });
    form.clearErrors();
    modalOpen.value = true;
}
function submit() {
    const opts = { onSuccess: () => (modalOpen.value = false), preserveScroll: true };
    editing.value ? form.put(route('admin.carreras.update', editing.value.id), opts) : form.post(route('admin.carreras.store'), opts);
}
function eliminar(c) {
    confirmDelete({
        title: '¿Eliminar carrera?',
        content: c.guias_count ? `${c.nombre} — también se eliminarán sus ${c.guias_count} guías.` : c.nombre,
        onOk: () => router.delete(route('admin.carreras.destroy', c.id), { preserveScroll: true }),
    });
}

const columns = [
    { title: 'Carrera', key: 'nombre' },
    { title: 'Guías', key: 'guias', width: 100, align: 'center' },
    { title: 'Estudiantes', key: 'estudiantes', width: 120, align: 'center' },
    { title: 'Acciones', key: 'acciones', width: 110, align: 'right' },
];
</script>

<template>
    <AdminLayout title="Carreras">
        <PageHead title="Carreras" subtitle="Carreras del Bachillerato Tecnológico que se ofertan" :icon="ApartmentOutlined">
            <template #actions>
                <a-button v-if="hayFiltros" @click="limpiar"><template #icon><FilterOutlined /></template>Limpiar</a-button>
                <a-button @click="router.visit(route('admin.guias.index'))"><template #icon><FileTextOutlined /></template>Guías</a-button>
                <a-button type="primary" @click="openCreate"><template #icon><PlusOutlined /></template>Nueva carrera</a-button>
            </template>
        </PageHead>

        <div class="sains-stats sains-stats--3">
            <StatCard label="Carreras" :value="stats.total" color="indigo" />
            <StatCard label="Guías" :value="stats.guias" color="violet" />
            <StatCard label="Estudiantes inscritos" :value="stats.estudiantes" color="amber" />
        </div>

        <a-alert type="info" show-icon style="margin-bottom: 14px"
            message="Las carreras aparecen, en el orden en que se registraron, en la página de inicio, en el login y en el registro del estudiante." />

        <a-card :bordered="false" :body-style="{ padding: '4px 4px 12px' }">
            <a-table class="filtered-table" :columns="columns" :data-source="carreras" :pagination="false" row-key="id" size="middle">
                <template #emptyText><a-empty :image="null" description="Sin carreras" /></template>

                <template #summary>
                    <a-table-summary-row>
                        <a-table-summary-cell v-for="(col, i) in columns" :key="col.key" :index="i">
                            <a-input v-if="col.key === 'nombre'" v-model:value="filtros.search" size="small" allow-clear placeholder="Buscar carrera…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                        </a-table-summary-cell>
                    </a-table-summary-row>
                </template>

                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'nombre'">
                        <div class="carr">
                            <span class="carr__ic"><i class="fas" :class="record.icono || 'fa-graduation-cap'"></i></span>
                            <div>
                                <div class="sains-strong">{{ record.nombre }}</div>
                                <div v-if="record.descripcion" class="carr__desc">{{ record.descripcion }}</div>
                            </div>
                        </div>
                    </template>
                    <template v-else-if="column.key === 'guias'">
                        <a class="count-pill" :class="{ 'is-zero': !record.guias_count }"
                            @click="router.visit(route('admin.guias.index', { carrera_id: record.id }))">{{ record.guias_count }}</a>
                    </template>
                    <template v-else-if="column.key === 'estudiantes'">
                        <span class="count-pill" :class="{ 'is-zero': !record.estudiantes_count }">{{ record.estudiantes_count }}</span>
                    </template>
                    <template v-else-if="column.key === 'acciones'">
                        <RowActions editable :delete-disabled="record.estudiantes_count > 0" @edit="openEdit(record)" @delete="eliminar(record)" />
                    </template>
                </template>
            </a-table>
        </a-card>

        <a-modal v-model:open="modalOpen" :title="editing ? 'Editar carrera' : 'Nueva carrera'" class="sains-modal"
            :confirm-loading="form.processing" ok-text="Guardar" @ok="submit">
            <a-form layout="vertical">
                <a-form-item label="Nombre" required :validate-status="form.errors.nombre ? 'error' : undefined" :help="form.errors.nombre">
                    <a-input v-model:value="form.nombre" placeholder="p. ej. Informática Administrativa" />
                </a-form-item>
                <a-form-item label="Descripción" :validate-status="form.errors.descripcion ? 'error' : undefined" :help="form.errors.descripcion">
                    <a-textarea v-model:value="form.descripcion" :rows="3" :maxlength="600" show-count placeholder="Breve descripción que verá el estudiante" />
                </a-form-item>
                <a-form-item label="Ícono" :validate-status="form.errors.icono ? 'error' : undefined" :help="form.errors.icono">
                    <div class="ico-grid">
                        <button v-for="ic in iconos" :key="ic" type="button" class="ico-opt" :class="{ on: form.icono === ic }" @click="form.icono = ic">
                            <i class="fas" :class="ic"></i>
                        </button>
                    </div>
                </a-form-item>
            </a-form>
        </a-modal>
    </AdminLayout>
</template>

<style scoped>
.carr { display: flex; align-items: center; gap: 12px; }
.carr__ic {
    flex: none; width: 38px; height: 38px; border-radius: 50%;
    display: grid; place-items: center;
    background: #eef3f9; color: var(--sains-primary); font-size: 16px;
}
.carr__desc { font-size: 12px; color: #64748b; margin-top: 2px; max-width: 520px; }
.ico-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(42px, 1fr)); gap: 6px; }
.ico-opt {
    height: 42px; border-radius: 10px; border: 1px solid var(--sains-line); background: #fff;
    color: #475569; font-size: 16px; cursor: pointer; transition: all .15s ease;
}
.ico-opt:hover { border-color: var(--sains-primary); color: var(--sains-primary); }
.ico-opt.on { border-color: var(--sains-primary); background: var(--sains-primary); color: #fff; }
a.count-pill { cursor: pointer; }
</style>
