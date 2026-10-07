<script setup>
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import {
    PlusOutlined, SearchOutlined, QuestionCircleOutlined, CheckCircleFilled, MinusCircleOutlined,
    DownloadOutlined, UploadOutlined, InboxOutlined, FileExcelOutlined, DeleteOutlined, PlusCircleOutlined,
    FileAddOutlined, ArrowLeftOutlined, CheckOutlined,
} from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';
import RowActions from '@/Components/RowActions.vue';
import { confirmDelete, message } from '@/lib/notify';

const props = defineProps({
    preguntas: { type: Object, required: true },
    areas: { type: Array, default: () => [] },
    examenes: { type: Array, default: () => [] },
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

/* ---------- Importar desde Excel / CSV (2 pasos: vista previa → confirmar) ---------- */
const importOpen = ref(false);
const paso = ref(0); // 0 = subir, 1 = revisar
const archivo = ref(null);
const analizando = ref(false);
const analisis = ref(null);
const filtroImport = ref('todas');
const importForm = useForm({ token: '', omitir_duplicadas: true, examen_id: undefined });

function beforeUploadArchivo(file) {
    const okType = /\.(xlsx|xls|csv)$/i.test(file.name);
    if (!okType) { message.error('Solo se aceptan archivos .xlsx, .xls o .csv.'); return false; }
    if (file.size > 10 * 1024 * 1024) { message.error('El archivo no puede pesar más de 10 MB.'); return false; }
    archivo.value = file;
    analizar();
    return false;
}
async function analizar() {
    analizando.value = true;
    const fd = new FormData();
    fd.append('archivo', archivo.value);
    try {
        const { data } = await axios.post(route('admin.preguntas.importar.analizar'), fd);
        analisis.value = data;
        importForm.token = data.token;
        filtroImport.value = data.resumen.error ? 'error' : 'todas';
        paso.value = 1;
    } catch (e) {
        message.error(e.response?.data?.message || e.response?.data?.errors?.archivo?.[0] || 'No se pudo leer el archivo.');
        archivo.value = null;
    } finally {
        analizando.value = false;
    }
}
const aGuardar = computed(() => {
    const r = analisis.value?.resumen;
    if (!r) return 0;
    return r.nueva + r.actualiza + (importForm.omitir_duplicadas ? 0 : r.duplicada);
});
const filasFiltradas = computed(() => {
    const filas = analisis.value?.filas ?? [];
    return filtroImport.value === 'todas' ? filas : filas.filter((f) => f.estado === filtroImport.value);
});
const ESTADOS_IMPORT = {
    nueva: { color: 'green', label: 'Nueva' },
    actualiza: { color: 'blue', label: 'Actualiza' },
    duplicada: { color: 'gold', label: 'Duplicada' },
    error: { color: 'red', label: 'Error' },
};
const colsImport = [
    { title: 'Fila', dataIndex: 'fila', key: 'fila', width: 64, align: 'center' },
    { title: 'Estado', key: 'estado', width: 110 },
    { title: 'Pregunta', key: 'pregunta', ellipsis: true },
    { title: 'Área', key: 'area', width: 150, ellipsis: true },
    { title: 'Correcta', key: 'correcta', width: 170, ellipsis: true },
];
function enviarImportacion() {
    if (!aGuardar.value) { message.warning('No hay filas para importar.'); return; }
    importForm.transform((d) => ({ ...d, omitir_duplicadas: d.omitir_duplicadas ? 1 : 0 }))
        .post(route('admin.preguntas.importar'), {
            preserveScroll: true,
            onSuccess: () => { importOpen.value = false; },
        });
}
function volverASubir() {
    paso.value = 0;
    archivo.value = null;
    analisis.value = null;
}
function alCerrarImport() {
    volverASubir();
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
                <a-button :href="route('admin.preguntas.plantilla')"><template #icon><FileAddOutlined /></template>Descargar plantilla</a-button>
                <a-button @click="importOpen = true"><template #icon><UploadOutlined /></template>Importar preguntas</a-button>
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
            :width="paso === 1 ? 980 : 600"
            :footer="null"
            destroy-on-close
            @after-close="alCerrarImport"
        >
            <a-steps :current="paso" size="small" class="imp-steps" :items="[{ title: 'Subir archivo' }, { title: 'Revisar' }, { title: 'Importar' }]" />

            <!-- Paso 1: subir -->
            <template v-if="paso === 0">
                <a-spin :spinning="analizando" tip="Leyendo y revisando el archivo…">
                    <a-upload-dragger :before-upload="beforeUploadArchivo" :max-count="1" :show-upload-list="false" accept=".xlsx,.xls,.csv">
                        <p class="ant-upload-drag-icon"><InboxOutlined /></p>
                        <p class="ant-upload-text">Haz clic o arrastra tu archivo aquí</p>
                        <p class="ant-upload-hint">.xlsx, .xls o .csv · máx. 10 MB. Nada se guarda hasta que confirmes.</p>
                    </a-upload-dragger>
                </a-spin>
                <div class="imp-tpl">
                    <div>
                        <b>¿Primera vez?</b>
                        <span>Descarga la plantilla: trae una pregunta de ejemplo y una hoja de instrucciones.</span>
                    </div>
                    <a-button :href="route('admin.preguntas.plantilla')"><template #icon><FileAddOutlined /></template>Descargar plantilla</a-button>
                </div>
                <ul class="imp-rules">
                    <li>Las columnas se reconocen por su encabezado (Área, Pregunta, Opción A…E, Respuesta correcta, Justificación), en cualquier orden.</li>
                    <li>La respuesta correcta puede ser la letra, el número de la opción o su texto.</li>
                    <li>Con ID se actualiza la pregunta existente; sin ID se crea una nueva. Las áreas que no existan se crean solas.</li>
                </ul>
            </template>

            <!-- Paso 2: revisar -->
            <template v-else-if="analisis">
                <div class="imp-file">
                    <span><FileExcelOutlined /> {{ analisis.nombre }} · {{ analisis.resumen.total }} filas</span>
                    <a-button size="small" type="link" @click="volverASubir"><template #icon><ArrowLeftOutlined /></template>Cambiar archivo</a-button>
                </div>

                <div class="imp-cards">
                    <button v-for="(cfg, k) in { todas: { label: 'Todas' }, ...ESTADOS_IMPORT }" :key="k" type="button"
                        class="imp-card" :class="[`is-${k}`, { on: filtroImport === k }]" @click="filtroImport = k">
                        <b>{{ k === 'todas' ? analisis.resumen.total : analisis.resumen[k] }}</b>
                        <span>{{ cfg.label }}</span>
                    </button>
                </div>

                <a-table :columns="colsImport" :data-source="filasFiltradas" row-key="fila" size="small"
                    :pagination="{ pageSize: 8, size: 'small', showSizeChanger: false }" class="imp-table">
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'estado'">
                            <a-tag :color="ESTADOS_IMPORT[record.estado].color" :bordered="false">{{ ESTADOS_IMPORT[record.estado].label }}</a-tag>
                        </template>
                        <template v-else-if="column.key === 'pregunta'">
                            <div class="imp-q">{{ record.pregunta || '—' }}</div>
                            <div v-if="record.motivo" class="imp-why" :class="`is-${record.estado}`">{{ record.motivo }}</div>
                        </template>
                        <template v-else-if="column.key === 'area'">
                            {{ record.area || '—' }} <a-tag v-if="record.area_nueva" color="purple" :bordered="false">nueva</a-tag>
                        </template>
                        <template v-else-if="column.key === 'correcta'">
                            <span v-if="record.letra"><b>{{ record.letra }})</b> {{ record.correcta }}</span>
                            <span v-else>—</span>
                        </template>
                    </template>
                </a-table>
                <p v-if="analisis.resumen.total > analisis.filas.length" class="imp-note">
                    La vista previa muestra las primeras {{ analisis.filas.length }} filas; se importarán todas.
                </p>

                <div class="imp-opts">
                    <a-checkbox v-model:checked="importForm.omitir_duplicadas" :disabled="!analisis.resumen.duplicada">
                        Omitir las {{ analisis.resumen.duplicada }} duplicadas
                    </a-checkbox>
                    <div class="imp-opts__exam">
                        <span>Agregarlas también a un examen (opcional)</span>
                        <a-select v-model:value="importForm.examen_id" allow-clear show-search option-filter-prop="label"
                            placeholder="Sin agregar a examen" :options="examenes" style="width: 300px" />
                    </div>
                </div>

                <div class="imp-actions">
                    <span v-if="analisis.resumen.error" class="imp-actions__warn">
                        Las {{ analisis.resumen.error }} filas con error no se importarán. Corrígelas en el archivo y vuelve a subirlo si las necesitas.
                    </span>
                    <span v-else></span>
                    <a-space>
                        <a-button @click="importOpen = false">Cancelar</a-button>
                        <a-button type="primary" :loading="importForm.processing" :disabled="!aGuardar" @click="enviarImportacion">
                            <template #icon><CheckOutlined /></template>Importar {{ aGuardar }} pregunta(s)
                        </a-button>
                    </a-space>
                </div>
            </template>
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

/* ---------- Importación ---------- */
.imp-steps { margin: 4px 0 18px; }
.imp-tpl {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    margin-top: 14px; padding: 12px 14px; border-radius: 12px; background: #eef3f9; border: 1px solid #c5d5e9;
}
.imp-tpl b { display: block; font-size: 13px; color: #0f172a; }
.imp-tpl span { font-size: 12.5px; color: #475569; }
.imp-rules { margin: 12px 0 0; padding-left: 18px; font-size: 12.5px; color: #64748b; line-height: 1.6; }
.imp-file { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; font-size: 13px; font-weight: 600; color: #334155; }
.imp-file .anticon { color: #16a34a; }
.imp-cards { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; margin-bottom: 12px; }
.imp-card {
    display: flex; flex-direction: column; align-items: flex-start; gap: 2px;
    padding: 10px 12px; border-radius: 12px; border: 1.5px solid #e2e8f0; background: #fff; cursor: pointer;
    transition: border-color .15s ease, box-shadow .15s ease;
}
.imp-card b { font-size: 20px; line-height: 1.1; color: #0f172a; }
.imp-card span { font-size: 12px; color: #64748b; font-weight: 600; }
.imp-card.on { border-color: var(--sains-primary); box-shadow: 0 0 0 3px rgba(24, 81, 173, .12); }
.imp-card.is-nueva b { color: #16a34a; }
.imp-card.is-actualiza b { color: #1851ad; }
.imp-card.is-duplicada b { color: #d99a00; }
.imp-card.is-error b { color: #dc2626; }
.imp-q { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.imp-why { font-size: 11.5px; margin-top: 2px; white-space: normal; }
.imp-why.is-error { color: #dc2626; }
.imp-why.is-duplicada { color: #b45309; }
.imp-note { font-size: 12px; color: #64748b; margin: 6px 0 0; }
.imp-opts {
    display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;
    margin-top: 12px; padding: 12px 14px; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;
}
.imp-opts__exam { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #334155; }
.imp-actions { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-top: 14px; }
.imp-actions__warn { font-size: 12.5px; color: #b45309; }
@media (max-width: 760px) { .imp-cards { grid-template-columns: repeat(3, 1fr); } }
</style>
