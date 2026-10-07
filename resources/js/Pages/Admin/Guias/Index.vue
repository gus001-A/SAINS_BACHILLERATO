<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import {
    PlusOutlined, SearchOutlined, FileTextOutlined, FilterOutlined, InboxOutlined,
    LinkOutlined, FilePdfOutlined, FileImageOutlined, FileOutlined, ApartmentOutlined,
} from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';
import RowActions from '@/Components/RowActions.vue';
import { laravelPagination } from '@/lib/useIndex';
import { confirmDelete } from '@/lib/notify';

const props = defineProps({
    guias: { type: Object, required: true },
    carreras: { type: Array, required: true },
    stats: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const filtros = reactive({
    search: props.filters.search ?? '',
    carrera_id: props.filters.carrera_id === 'tronco' ? 'tronco'
        : props.filters.carrera_id ? Number(props.filters.carrera_id) : undefined,
});
const opcionesFiltro = computed(() => [{ value: 'tronco', label: 'Tronco común' }, ...props.carreras]);
let t = null;
function recargar(extra = {}) {
    router.get(route('admin.guias.index'), {
        ...Object.fromEntries(Object.entries(filtros).filter(([, v]) => v !== '' && v !== undefined && v !== null)),
        ...extra,
    }, { preserveState: true, preserveScroll: true, replace: true });
}
watch(filtros, () => { clearTimeout(t); t = setTimeout(recargar, 300); });
const hayFiltros = computed(() => !!filtros.search || !!filtros.carrera_id);
function limpiar() { filtros.search = ''; filtros.carrera_id = undefined; }

const pagination = computed(() => laravelPagination(props.guias, 'guías'));

const modalOpen = ref(false);
const editing = ref(null);
// 'tronco' = para todas las carreras (carrera_id vacío); 'carrera' = una carrera específica.
const alcance = ref('tronco');
const archivoLista = ref([]);
const form = useForm({
    carrera_id: undefined, titulo: '', descripcion: '', enlace: '',
    archivo: null, quitar_archivo: false,
});

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    alcance.value = filtros.carrera_id && filtros.carrera_id !== 'tronco' ? 'carrera' : 'tronco';
    form.carrera_id = alcance.value === 'carrera' ? filtros.carrera_id : undefined;
    archivoLista.value = [];
    modalOpen.value = true;
}
function openEdit(g) {
    editing.value = g;
    form.reset();
    Object.assign(form, {
        carrera_id: g.carrera_id, titulo: g.titulo, descripcion: g.descripcion ?? '', enlace: g.enlace ?? '',
        archivo: null, quitar_archivo: false,
    });
    alcance.value = g.tronco_comun ? 'tronco' : 'carrera';
    form.clearErrors();
    archivoLista.value = [];
    modalOpen.value = true;
}
function beforeUpload(file) {
    form.archivo = file;
    form.quitar_archivo = false;
    archivoLista.value = [file];
    return false;
}
function quitarSeleccion() {
    form.archivo = null;
    archivoLista.value = [];
}
function submit() {
    if (alcance.value === 'carrera' && !form.carrera_id) {
        form.setError('carrera_id', 'Elige la carrera o marca la guía como tronco común.');
        return;
    }
    if (alcance.value === 'tronco') form.carrera_id = null;
    const opts = { onSuccess: () => (modalOpen.value = false), preserveScroll: true, forceFormData: true };
    if (editing.value) {
        form.transform((d) => ({ ...d, carrera_id: d.carrera_id ?? '', quitar_archivo: d.quitar_archivo ? 1 : 0, _method: 'put' }))
            .post(route('admin.guias.update', editing.value.id), opts);
    } else {
        form.transform((d) => ({ ...d, carrera_id: d.carrera_id ?? '' })).post(route('admin.guias.store'), opts);
    }
}
function eliminar(g) {
    confirmDelete({
        title: '¿Eliminar guía?',
        content: g.titulo,
        onOk: () => router.delete(route('admin.guias.destroy', g.id), { preserveScroll: true }),
    });
}

const iconoTipo = { pdf: FilePdfOutlined, imagen: FileImageOutlined, documento: FileOutlined, enlace: LinkOutlined };
const columns = [
    { title: 'Guía', key: 'titulo' },
    { title: 'Carrera', key: 'carrera', width: 240 },
    { title: 'Material', key: 'material', width: 150 },
    { title: 'Acciones', key: 'acciones', width: 110, align: 'right' },
];
</script>

<template>
    <AdminLayout title="Guías">
        <PageHead title="Guías" subtitle="Tronco común (para todos) y guías por carrera" :icon="FileTextOutlined">
            <template #actions>
                <a-button v-if="hayFiltros" @click="limpiar"><template #icon><FilterOutlined /></template>Limpiar</a-button>
                <a-button @click="router.visit(route('admin.carreras.index'))"><template #icon><ApartmentOutlined /></template>Carreras</a-button>
                <a-button type="primary" :disabled="!carreras.length" @click="openCreate"><template #icon><PlusOutlined /></template>Nueva guía</a-button>
            </template>
        </PageHead>

        <div class="sains-stats sains-stats--4">
            <StatCard label="Guías" :value="stats.total" color="indigo" />
            <StatCard label="Tronco común" :value="stats.tronco" color="violet" />
            <StatCard label="Con archivo" :value="stats.conArchivo" color="green" />
            <StatCard label="Carreras sin guías" :value="stats.carrerasSinGuia" color="amber" />
        </div>

        <a-card :bordered="false" :body-style="{ padding: '4px 4px 12px' }">
            <a-table class="filtered-table" :columns="columns" :data-source="guias.data" :pagination="pagination"
                row-key="id" size="middle" @change="(p) => recargar({ page: p.current })">
                <template #emptyText><a-empty :image="null" description="Sin guías" /></template>

                <template #summary>
                    <a-table-summary-row>
                        <a-table-summary-cell v-for="(col, i) in columns" :key="col.key" :index="i">
                            <a-input v-if="col.key === 'titulo'" v-model:value="filtros.search" size="small" allow-clear placeholder="Buscar guía…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-select v-else-if="col.key === 'carrera'" v-model:value="filtros.carrera_id" size="small" allow-clear
                                placeholder="Todas" style="width: 100%" :options="opcionesFiltro" />
                        </a-table-summary-cell>
                    </a-table-summary-row>
                </template>

                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'titulo'">
                        <div class="sains-strong">{{ record.titulo }}</div>
                        <div v-if="record.descripcion" class="g-desc">{{ record.descripcion }}</div>
                    </template>
                    <template v-else-if="column.key === 'carrera'">
                        <a-tag v-if="record.tronco_comun" color="gold" :bordered="false">Tronco común · todas</a-tag>
                        <span v-else>{{ record.carrera }}</span>
                    </template>
                    <template v-else-if="column.key === 'material'">
                        <a :href="record.archivo_url || record.enlace" target="_blank" rel="noopener" class="g-mat" :class="`is-${record.tipo}`">
                            <component :is="iconoTipo[record.tipo]" />
                            {{ record.tipo === 'enlace' ? 'Enlace' : record.tipo === 'pdf' ? 'PDF' : record.tipo === 'imagen' ? 'Imagen' : 'Documento' }}
                        </a>
                    </template>
                    <template v-else-if="column.key === 'acciones'">
                        <RowActions editable @edit="openEdit(record)" @delete="eliminar(record)" />
                    </template>
                </template>
            </a-table>
        </a-card>

        <a-modal v-model:open="modalOpen" :title="editing ? 'Editar guía' : 'Nueva guía'" class="sains-modal" width="620px"
            :confirm-loading="form.processing" ok-text="Guardar" @ok="submit">
            <a-form layout="vertical">
                <a-form-item label="¿Para quién es esta guía?" required>
                    <a-segmented v-model:value="alcance" block :options="[
                        { value: 'tronco', label: 'Tronco común (todas las carreras)' },
                        { value: 'carrera', label: 'Una carrera' },
                    ]" />
                </a-form-item>
                <a-form-item v-if="alcance === 'carrera'" label="Carrera" required
                    :validate-status="form.errors.carrera_id ? 'error' : undefined" :help="form.errors.carrera_id">
                    <a-select v-model:value="form.carrera_id" :options="carreras" placeholder="Selecciona la carrera" />
                </a-form-item>
                <a-alert v-else type="info" show-icon style="margin-bottom: 16px"
                    message="La verán todos los estudiantes, sin importar su carrera." />
                <a-form-item label="Título" required :validate-status="form.errors.titulo ? 'error' : undefined" :help="form.errors.titulo">
                    <a-input v-model:value="form.titulo" placeholder="p. ej. Guía 1 · Fundamentos de administración" />
                </a-form-item>
                <a-form-item label="Descripción" :validate-status="form.errors.descripcion ? 'error' : undefined" :help="form.errors.descripcion">
                    <a-textarea v-model:value="form.descripcion" :rows="2" :maxlength="1000" placeholder="Opcional" />
                </a-form-item>

                <a-form-item label="Archivo" :validate-status="form.errors.archivo ? 'error' : undefined"
                    :help="form.errors.archivo || 'PDF, Word, PowerPoint, Excel o imagen · máx. 20 MB'">
                    <div v-if="editing && editing.archivo_url && !form.archivo && !form.quitar_archivo" class="g-actual">
                        <a :href="editing.archivo_url" target="_blank" rel="noopener"><FileOutlined /> {{ editing.archivo_nombre }}</a>
                        <a-button size="small" danger type="text" @click="form.quitar_archivo = true">Quitar</a-button>
                    </div>
                    <a-upload-dragger :file-list="archivoLista" :before-upload="beforeUpload" :max-count="1" @remove="quitarSeleccion"
                        accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.webp">
                        <p class="ant-upload-drag-icon"><InboxOutlined /></p>
                        <p class="ant-upload-text">{{ editing && editing.archivo_url ? 'Arrastra un archivo para reemplazar el actual' : 'Haz clic o arrastra el archivo de la guía' }}</p>
                    </a-upload-dragger>
                </a-form-item>

                <a-form-item label="…o un enlace" :validate-status="form.errors.enlace ? 'error' : undefined"
                    :help="form.errors.enlace || 'Por ejemplo, un Google Drive o un documento en línea.'">
                    <a-input v-model:value="form.enlace" placeholder="https://…"><template #prefix><LinkOutlined /></template></a-input>
                </a-form-item>
            </a-form>
        </a-modal>
    </AdminLayout>
</template>

<style scoped>
.g-desc { font-size: 12px; color: #64748b; margin-top: 2px; max-width: 480px; }
.g-mat {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;
    background: #eef3f9; color: var(--sains-primary);
}
.g-mat.is-pdf { background: #fef2f2; color: #dc2626; }
.g-mat.is-imagen { background: #f0fdf4; color: #16a34a; }
.g-actual {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 12px; margin-bottom: 8px; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0;
}
</style>
