<script setup>
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import {
    PlusOutlined, SearchOutlined, QuestionCircleOutlined, CheckCircleFilled, MinusCircleOutlined,
    DownloadOutlined, UploadOutlined, InboxOutlined, FileExcelOutlined, DeleteOutlined, PlusCircleOutlined,
} from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';
import RowActions from '@/Components/RowActions.vue';
import { confirmDelete, message } from '@/lib/notify';

const props = defineProps({
    preguntas: { type: Object, required: true },
    areas: { type: Array, default: () => [] },
    stats: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const respCorrecta = ref(props.filters.respuesta_correcta ?? '');
const idArea = ref(props.filters.id_area ?? undefined);
const hasJust = ref(props.filters.has_justificacion ?? undefined);
let debounce = null;

function reload(extra = {}) {
    router.get(route('admin.preguntas.index'),
        {
            search: search.value || undefined,
            respuesta_correcta: respCorrecta.value || undefined,
            id_area: idArea.value || undefined,
            has_justificacion: hasJust.value || undefined,
            ...extra,
        },
        { preserveState: true, replace: true, preserveScroll: true });
}
watch([search, respCorrecta], () => { clearTimeout(debounce); debounce = setTimeout(() => reload(), 350); });
watch([idArea, hasJust], () => reload());

const areaOptions = computed(() => props.areas.map((a) => ({ value: a.id, label: a.nombre })));

/* ---------- Alta / edición ---------- */
const MIN_OPCIONES = 3;
const MAX_OPCIONES = 4;

function opcionesVacias() {
    return [
        { texto: '', es_correcta: true },
        { texto: '', es_correcta: false },
        { texto: '', es_correcta: false },
    ];
}

const modalOpen = ref(false);
const editing = ref(null);
const form = useForm({
    id_area: undefined, pregunta: '', justificacion: '', opciones: opcionesVacias(),
});

function agregarOpcion() {
    if (form.opciones.length < MAX_OPCIONES) form.opciones.push({ texto: '', es_correcta: false });
}
function quitarOpcion(i) {
    if (form.opciones.length <= MIN_OPCIONES) return;
    const eraCorrecta = form.opciones[i].es_correcta;
    form.opciones.splice(i, 1);
    if (eraCorrecta) form.opciones[0].es_correcta = true;
}
function marcarCorrecta(i) {
    form.opciones.forEach((o, idx) => { o.es_correcta = idx === i; });
}

function openCreate() {
    editing.value = null;
    form.reset();
    form.opciones = opcionesVacias();
    form.clearErrors();
    modalOpen.value = true;
}
function openEdit(r) {
    editing.value = r;
    form.id_area = r.id_area;
    form.pregunta = r.pregunta;
    form.justificacion = r.justificacion ?? '';
    form.opciones = (r.opciones ?? []).map((o) => ({ texto: o.texto, es_correcta: !!o.es_correcta }));
    if (form.opciones.length === 0) form.opciones = opcionesVacias();
    form.clearErrors();
    modalOpen.value = true;
}
function submit() {
    const opts = { onSuccess: () => { modalOpen.value = false; } };
    if (editing.value) form.put(route('admin.preguntas.update', editing.value.id), opts);
    else form.post(route('admin.preguntas.store'), opts);
}
function eliminar(r) {
    confirmDelete({ title: '¿Eliminar pregunta?', content: r.pregunta.slice(0, 80), onOk: () => router.delete(route('admin.preguntas.destroy', r.id), { preserveScroll: true }) });
}

/* ---------- Importar desde Excel / CSV ---------- */
const importOpen = ref(false);
const importForm = useForm({ archivo: null });

function beforeUploadArchivo(file) {
    const okType = /\.(xlsx|xls|csv)$/i.test(file.name);
    if (!okType) { message.error('Solo se aceptan archivos .xlsx, .xls o .csv.'); return false; }
    importForm.archivo = file;
    return false;
}
function quitarArchivo() {
    importForm.archivo = null;
}
function enviarImportacion() {
    if (!importForm.archivo) { message.warning('Selecciona un archivo primero.'); return; }
    importForm.post(route('admin.preguntas.importar'), {
        forceFormData: true,
        onSuccess: () => { importOpen.value = false; importForm.reset(); },
    });
}
function alCerrarImport() {
    importForm.reset();
    importForm.clearErrors();
}

const pagination = computed(() => ({
    current: props.preguntas.current_page,
    pageSize: props.preguntas.per_page,
    total: props.preguntas.total,
    showSizeChanger: false,
    showTotal: (t) => `${t} preguntas`,
}));
function onChange(pag) { reload({ page: pag.current }); }

const columns = [
    { title: 'Pregunta', key: 'pregunta' },
    { title: 'Área', dataIndex: 'area', key: 'area', width: 170 },
    { title: 'Respuesta correcta', dataIndex: 'respuesta_correcta', key: 'correcta', width: 200 },
    { title: 'Opciones', key: 'numOpciones', width: 90, align: 'center' },
    { title: 'Justif.', key: 'justif', width: 90, align: 'center' },
    { title: '', key: 'acciones', width: 100, align: 'right' },
];
</script>

<template>
    <AdminLayout title="Preguntas">
        <PageHead title="Preguntas" subtitle="Banco de reactivos por área" :icon="QuestionCircleOutlined">
            <template #actions>
                <a-button :href="route('admin.preguntas.exportar-excel')">
                    <template #icon><DownloadOutlined /></template>Descargar Excel
                </a-button>
                <a-button :href="route('admin.preguntas.exportar-csv')">
                    <template #icon><DownloadOutlined /></template>Descargar CSV
                </a-button>
                <a-button @click="importOpen = true"><template #icon><UploadOutlined /></template>Cargar archivo</a-button>
                <a-button type="primary" @click="openCreate"><template #icon><PlusOutlined /></template>Nueva pregunta</a-button>
            </template>
        </PageHead>

        <div class="sains-stats sains-stats--4">
            <StatCard label="Preguntas" :value="stats.total" color="indigo" />
            <StatCard label="Áreas" :value="stats.areas" color="violet" />
            <StatCard label="Con justificación" :value="stats.conJustificacion" color="green" />
            <StatCard label="Sin justificación" :value="stats.sinJustificacion" color="slate" />
        </div>

        <a-card :bordered="false">
            <a-table class="filtered-table" :columns="columns" :data-source="preguntas.data" :pagination="pagination" row-key="id" size="middle" :scroll="{ x: 980 }" @change="onChange">
                <template #summary>
                    <a-table-summary-row>
                        <a-table-summary-cell v-for="(col, i) in columns" :key="col.key ?? i" :index="i">
                            <a-input v-if="col.key === 'pregunta'" v-model:value="search" size="small" allow-clear placeholder="Buscar texto…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-input v-else-if="col.key === 'correcta'" v-model:value="respCorrecta" size="small" allow-clear placeholder="Respuesta…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-select v-else-if="col.key === 'area'" v-model:value="idArea" size="small" allow-clear show-search
                                option-filter-prop="label" placeholder="Todas" :options="areaOptions" />
                            <a-select v-else-if="col.key === 'justif'" v-model:value="hasJust" size="small" allow-clear placeholder="Todas"
                                :options="[{ value: 'si', label: 'Con' }, { value: 'no', label: 'Sin' }]" />
                        </a-table-summary-cell>
                    </a-table-summary-row>
                </template>
                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'pregunta'">
                        <div style="max-width: 420px">{{ record.pregunta }}</div>
                    </template>
                    <template v-else-if="column.key === 'correcta'">
                        <span class="sains-strong">{{ record.respuesta_correcta }}</span>
                    </template>
                    <template v-else-if="column.key === 'numOpciones'">
                        <span class="count-pill">{{ record.opciones?.length ?? 0 }}</span>
                    </template>
                    <template v-else-if="column.key === 'justif'">
                        <a-tooltip :title="record.justificacion || 'Sin justificación'">
                            <span class="just-flag" :class="record.justificacion ? 'is-yes' : 'is-no'">
                                <CheckCircleFilled v-if="record.justificacion" />
                                <MinusCircleOutlined v-else />
                                {{ record.justificacion ? 'Sí' : 'No' }}
                            </span>
                        </a-tooltip>
                    </template>
                    <template v-else-if="column.key === 'acciones'">
                        <RowActions editable @edit="openEdit(record)" @delete="eliminar(record)" />
                    </template>
                </template>
            </a-table>
        </a-card>

        <a-modal v-model:open="modalOpen" :title="editing ? 'Editar pregunta' : 'Nueva pregunta'" class="sains-modal" :width="640" :confirm-loading="form.processing" ok-text="Guardar" @ok="submit">
            <a-form layout="vertical">
                <a-form-item label="Área" required :validate-status="form.errors.id_area ? 'error' : undefined" :help="form.errors.id_area">
                    <a-select v-model:value="form.id_area" show-search option-filter-prop="label" :options="areaOptions" placeholder="Selecciona…" />
                </a-form-item>
                <a-form-item label="Pregunta" required :validate-status="form.errors.pregunta ? 'error' : undefined" :help="form.errors.pregunta">
                    <a-textarea v-model:value="form.pregunta" :rows="3" />
                </a-form-item>

                <a-form-item label="Opciones de respuesta (3 a 4, marca la correcta)" required :validate-status="form.errors.opciones ? 'error' : undefined" :help="form.errors.opciones">
                    <a-radio-group :value="form.opciones.findIndex((o) => o.es_correcta)" style="width: 100%" @change="(e) => marcarCorrecta(e.target.value)">
                        <div v-for="(op, i) in form.opciones" :key="i" class="opcion-row">
                            <a-radio :value="i" class="opcion-row__radio" />
                            <a-input v-model:value="op.texto" :placeholder="`Opción ${String.fromCharCode(65 + i)}`" :status="form.errors[`opciones.${i}.texto`] ? 'error' : undefined" />
                            <a-button v-if="form.opciones.length > MIN_OPCIONES" size="small" type="text" danger @click="quitarOpcion(i)">
                                <template #icon><DeleteOutlined /></template>
                            </a-button>
                        </div>
                    </a-radio-group>
                    <a-button v-if="form.opciones.length < MAX_OPCIONES" type="dashed" block class="opcion-add" @click="agregarOpcion">
                        <template #icon><PlusCircleOutlined /></template>Agregar una 4ª opción
                    </a-button>
                </a-form-item>

                <a-form-item label="Justificación (opcional)">
                    <a-textarea v-model:value="form.justificacion" :rows="2" />
                </a-form-item>
            </a-form>
        </a-modal>

        <a-modal
            v-model:open="importOpen"
            title="Cargar preguntas desde Excel o CSV"
            class="sains-modal"
            :width="540"
            :confirm-loading="importForm.processing"
            ok-text="Importar"
            cancel-text="Cancelar"
            :ok-button-props="{ disabled: !importForm.archivo }"
            @ok="enviarImportacion"
            @cancel="alCerrarImport"
            @after-close="alCerrarImport"
        >
            <a-form layout="vertical">
                <a-form-item label="Archivo" :validate-status="importForm.errors.archivo ? 'error' : undefined" :help="importForm.errors.archivo">
                    <a-upload-dragger
                        v-if="!importForm.archivo"
                        :before-upload="beforeUploadArchivo"
                        :max-count="1"
                        :show-upload-list="false"
                        accept=".xlsx,.xls,.csv"
                    >
                        <p class="ant-upload-drag-icon"><InboxOutlined /></p>
                        <p class="ant-upload-text">Haz clic o arrastra tu archivo aquí</p>
                        <p class="ant-upload-hint">.xlsx, .xls o .csv</p>
                    </a-upload-dragger>

                    <div v-else class="import-file">
                        <span class="import-file__name"><FileExcelOutlined /> {{ importForm.archivo.name }}</span>
                        <a-button size="small" type="text" danger @click="quitarArchivo">
                            <template #icon><DeleteOutlined /></template>Quitar
                        </a-button>
                    </div>
                </a-form-item>
                <a-alert type="info" show-icon message="Usa las mismas columnas que la descarga: ID, Área, Pregunta, Opción A, Opción B, Opción C, Opción D, Respuesta Correcta (A-D), Justificación. La Opción D puede ir vacía (mínimo 3 opciones). Deja el ID vacío para crear preguntas nuevas; si coincide con una existente, la actualiza (reemplazando sus opciones). Si el área no existe, se crea automáticamente." />
            </a-form>
        </a-modal>
    </AdminLayout>
</template>

<style scoped>
.just-flag {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 999px;
}
.just-flag.is-yes { background: #dcfce7; color: #15803d; }
.just-flag.is-no { background: #f1f5f9; color: #94a3b8; }

.import-file {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 8px 10px 14px; border: 1px solid var(--sains-line); border-radius: 10px; background: #f8fafc;
}
.import-file__name { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #334155; }
.import-file__name .anticon { color: #16a34a; }

.opcion-row {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 8px;
}
.opcion-row__radio { flex: none; }
.opcion-row .ant-input { flex: 1; }
.opcion-add { margin-top: 2px; }
</style>
