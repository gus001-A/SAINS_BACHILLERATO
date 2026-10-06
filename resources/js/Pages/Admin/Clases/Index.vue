<script setup>
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import {
    PlusOutlined, SearchOutlined, ReadOutlined, PlayCircleFilled,
    VideoCameraOutlined, ThunderboltOutlined,
    PaperClipOutlined, DeleteOutlined, LinkOutlined, FolderAddOutlined,
    CrownFilled, UnlockFilled,
} from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';
import RowActions from '@/Components/RowActions.vue';
import { confirmDelete, message } from '@/lib/notify';
import { esUrl } from '@/lib/forms';

const props = defineProps({
    clases: { type: Object, required: true },
    asignaturas: { type: Array, default: () => [] },
    videos: { type: Array, default: () => [] },
    tiposRecurso: { type: Array, default: () => [] },
    stats: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const asignaturaId = ref(props.filters.asignatura_id ?? undefined);
const recurso = ref(props.filters.recurso ?? undefined);
const acceso = ref(props.filters.acceso ?? undefined);
let debounce = null;

function reload(extra = {}) {
    router.get(route('admin.clases.index'),
        {
            search: search.value || undefined,
            asignatura_id: asignaturaId.value || undefined,
            recurso: recurso.value || undefined,
            acceso: acceso.value || undefined,
            ...extra,
        },
        { preserveState: true, replace: true, preserveScroll: true });
}
watch(search, () => { clearTimeout(debounce); debounce = setTimeout(() => reload(), 350); });
watch([asignaturaId, recurso, acceso], () => reload());

const recursoOpts = [
    { value: 'video', label: 'Con video' },
    { value: 'material', label: 'Con material' },
    { value: 'sin', label: 'Sin recursos' },
];
const accesoOpts = [
    { value: 'gratis', label: 'Gratuita (muestra)' },
    { value: 'premium', label: 'Premium' },
];

const asignaturaOptions = computed(() => props.asignaturas.map((a) => ({ value: a.id, label: a.nombre })));
const asignaturaNombre = (id) => props.asignaturas.find((a) => a.id === id)?.nombre ?? null;

// --- Modal crear/editar ---
const modalOpen = ref(false);
const editing = ref(null);
const form = useForm({
    id_asignatura: undefined, id_video: undefined, num_clase: 1, nombre_clase: '',
    url: '', gratis: false,
    recursos: [],          // [{ id?, titulo, tipo, url, descripcion }]
    recursos_eliminar: [],  // ids de recursos existentes a borrar
});

const tipoRecursoOpts = computed(() =>
    props.tiposRecurso.length
        ? props.tiposRecurso
        : [{ value: 'enlace', label: '🔗 Enlace externo' }, { value: 'pdf', label: '📄 PDF' }],
);

function nuevoRecurso() {
    form.recursos.push({ id: null, titulo: '', tipo: 'enlace', url: '', descripcion: '' });
}
function quitarRecurso(i) {
    const r = form.recursos[i];
    if (r?.id) form.recursos_eliminar.push(r.id);
    form.recursos.splice(i, 1);
}

// Videos del catálogo ("los que se suben"). Se muestran todos, pero los de la
// materia elegida aparecen primero.
const videosDeMateria = computed(() => {
    const mat = (asignaturaNombre(form.id_asignatura) || '').toLowerCase();
    const coincide = (v) => mat && (v.materia || '').toLowerCase() === mat;
    return [...props.videos]
        .sort((a, b) => (coincide(b) ? 1 : 0) - (coincide(a) ? 1 : 0))
        .map((v) => ({
            value: v.id,
            label: `${v.titulo}${v.tema ? ' · ' + v.tema : ''} — ${v.materia}${v.duracion ? ' (' + v.duracion + ')' : ''}`,
        }));
});
const videoSel = computed(() => props.videos.find((v) => v.id === form.id_video) || null);

function parseVideo(link) {
    if (!link) return null;
    try {
        if (link.includes('vimeo.com/')) return `https://player.vimeo.com/video/${link.split('/').pop().split('?')[0]}`;
        if (link.includes('youtube.com/watch')) return `https://www.youtube.com/embed/${new URL(link).searchParams.get('v')}`;
        if (link.includes('youtu.be/')) return `https://www.youtube.com/embed/${link.split('youtu.be/')[1].split(/[?&]/)[0]}`;
        if (link.includes('drive.google.com')) return link.replace(/\/view.*$/, '/preview');
    } catch (e) { /* ignore */ }
    return link;
}

async function detectarNumero() {
    if (!form.id_asignatura || editing.value) return;
    try {
        const { data } = await axios.get(route('admin.clases.siguiente-numero', form.id_asignatura));
        if (data.success) form.num_clase = data.siguiente_numero;
    } catch (e) { /* noop */ }
}

function onMateriaChange() {
    form.id_video = undefined;
    detectarNumero();
}
function onVideoChange() {
    const v = videoSel.value;
    if (v && !form.nombre_clase) form.nombre_clase = v.tema || v.titulo;
}

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}
function openEdit(r) {
    editing.value = r;
    form.id_asignatura = r.id_asignatura;
    form.id_video = r.id_video ?? undefined;
    form.num_clase = r.num_clase;
    form.nombre_clase = r.nombre_clase;
    form.url = r.url ?? '';
    form.gratis = !!r.gratis;
    form.recursos = (r.recursos ?? []).map((x) => ({ ...x }));
    form.recursos_eliminar = [];
    form.clearErrors();
    modalOpen.value = true;
}
function submit() {
    if (!form.id_video) { message.warning('Elige el video de la clase desde el catálogo.'); return; }
    const malos = form.recursos.filter((r) => (r.titulo || r.url) && (!r.titulo || !esUrl(r.url)));
    if (malos.length) {
        message.warning('Cada material adicional necesita un título y un enlace válido (http/https).');
        return;
    }
    const opts = {
        onSuccess: () => { modalOpen.value = false; },
        preserveScroll: true,
    };
    if (editing.value) form.put(route('admin.clases.update', editing.value.id), opts);
    else form.post(route('admin.clases.store'), opts);
}
function eliminar(r) {
    confirmDelete({ title: '¿Eliminar clase?', content: r.nombre_clase, onOk: () => router.delete(route('admin.clases.destroy', r.id), { preserveScroll: true }) });
}

const pagination = computed(() => ({
    current: props.clases.current_page,
    pageSize: props.clases.per_page,
    total: props.clases.total,
    showSizeChanger: false,
    showTotal: (t) => `${t} clases`,
}));
function onChange(pag) { reload({ page: pag.current }); }

// --- Modal ver video ---
const videoOpen = ref(false);
const videoEmbed = ref(null);
const videoTitulo = ref('');
function verVideo(r) {
    videoEmbed.value = parseVideo(r.link);
    videoTitulo.value = r.nombre_clase;
    videoOpen.value = true;
}

const columns = [
    { title: 'Clase', dataIndex: 'nombre_clase', key: 'nombre', width: 300, ellipsis: true },
    { title: 'Materia', dataIndex: 'asignatura', key: 'materia', width: 190 },
    { title: 'Acceso', key: 'acceso', width: 120, align: 'center' },
    { title: 'Recursos', key: 'recursos', width: 150, align: 'center' },
    { title: '', key: 'acciones', width: 96, align: 'right' },
];
</script>

<template>
    <AdminLayout title="Clases">
        <PageHead title="Clases" subtitle="Sesiones del curso premium" :icon="ReadOutlined">
            <template #actions>
                <a-button type="primary" @click="openCreate"><template #icon><PlusOutlined /></template>Nueva clase</a-button>
            </template>
        </PageHead>

        <div class="sains-stats sains-stats--4">
            <StatCard label="Total" :value="stats.total" color="indigo" />
            <StatCard label="Con video" :value="stats.conVideo" color="green" />
            <StatCard label="Con material" :value="stats.conMaterial" color="violet" />
            <StatCard label="Gratuitas (muestra)" :value="stats.gratuitas" color="amber" />
        </div>

        <a-card :bordered="false">
            <a-table class="filtered-table" :columns="columns" :data-source="clases.data" :pagination="pagination" row-key="id" size="middle" :scroll="{ x: 860 }" @change="onChange">
                <template #summary>
                    <a-table-summary-row>
                        <a-table-summary-cell v-for="(col, i) in columns" :key="col.key ?? i" :index="i">
                            <a-input v-if="col.key === 'nombre'" v-model:value="search" size="small" allow-clear placeholder="Buscar clase…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-select v-else-if="col.key === 'materia'" v-model:value="asignaturaId" size="small" allow-clear show-search
                                option-filter-prop="label" placeholder="Todas" :options="asignaturaOptions" />
                            <a-select v-else-if="col.key === 'acceso'" v-model:value="acceso" size="small" allow-clear
                                placeholder="Todas" :options="accesoOpts" />
                            <a-select v-else-if="col.key === 'recursos'" v-model:value="recurso" size="small" allow-clear
                                placeholder="Todos" :options="recursoOpts" />
                        </a-table-summary-cell>
                    </a-table-summary-row>
                </template>
                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'nombre'">
                        <div class="clase-cell">
                            <button v-if="record.link" type="button" class="clase-play" title="Ver video de la clase" @click="verVideo(record)">
                                <PlayCircleFilled />
                            </button>
                            <span v-else class="clase-play is-empty"><ReadOutlined /></span>
                            <span class="clase-cell__txt">
                                <span class="clase-cell__name">{{ record.nombre_clase }}</span>
                                <span class="clase-cell__sub">
                                    Clase {{ record.num_clase }}
                                    <template v-if="record.video_titulo"> · {{ record.video_titulo }}</template>
                                    <template v-if="record.video_duracion"> · {{ record.video_duracion }}</template>
                                </span>
                            </span>
                        </div>
                    </template>
                    <template v-else-if="column.key === 'acceso'">
                        <a-tag :bordered="false" :color="record.gratis ? 'green' : 'gold'">
                            {{ record.gratis ? 'Gratuita' : 'Premium' }}
                        </a-tag>
                    </template>
                    <template v-else-if="column.key === 'recursos'">
                        <a-tag v-if="record.link" color="blue">Video</a-tag>
                        <a-tag v-if="record.url" color="green">Material</a-tag>
                        <a-tag v-if="record.recursos_count" color="purple">+{{ record.recursos_count }}</a-tag>
                        <span v-if="!record.link && !record.url && !record.recursos_count">—</span>
                    </template>
                    <template v-else-if="column.key === 'acciones'">
                        <RowActions editable @edit="openEdit(record)" @delete="eliminar(record)" />
                    </template>
                </template>
            </a-table>
        </a-card>

        <a-modal v-model:open="modalOpen" :title="editing ? 'Editar clase' : 'Nueva clase'" class="sains-modal sains-modal--wide" :width="680" :confirm-loading="form.processing" ok-text="Guardar clase" @ok="submit">
            <a-form layout="vertical">
                <p class="sains-modal__section"><ReadOutlined /> Datos de la clase</p>
                <a-row :gutter="16">
                    <a-col :xs="24" :md="16">
                        <a-form-item required :validate-status="form.errors.id_asignatura ? 'error' : undefined" :help="form.errors.id_asignatura">
                            <template #label><ReadOutlined />Materia</template>
                            <a-select v-model:value="form.id_asignatura" show-search option-filter-prop="label"
                                :options="asignaturaOptions" placeholder="Selecciona…" @change="onMateriaChange" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :md="8">
                        <a-form-item required :validate-status="form.errors.num_clase ? 'error' : undefined"
                            :help="form.errors.num_clase || (editing ? '' : 'Se detecta según la materia')">
                            <template #label><ThunderboltOutlined />N.º de clase</template>
                            <a-input-number v-model:value="form.num_clase" :min="1" style="width: 100%" />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-form-item required :validate-status="form.errors.id_video ? 'error' : undefined" :help="form.errors.id_video">
                    <template #label><VideoCameraOutlined />Video de la clase</template>
                    <a-select
                        v-model:value="form.id_video" show-search option-filter-prop="label"
                        :options="videosDeMateria" :disabled="!form.id_asignatura"
                        :placeholder="form.id_asignatura ? 'Elige un video subido…' : 'Primero elige la materia'"
                        @change="onVideoChange"
                    />
                    <div v-if="!videos.length" class="vid-hint">
                        Aún no hay videos subidos.
                        <a :href="route('admin.videos.index')">Súbelos en la sección Videos</a>.
                    </div>
                    <div v-if="videoSel" class="vid-frame">
                        <iframe :src="parseVideo(videoSel.link)" allow="fullscreen; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </a-form-item>

                <a-form-item :validate-status="form.errors.nombre_clase ? 'error' : undefined" :help="form.errors.nombre_clase" required>
                    <template #label><ReadOutlined />Nombre de la clase</template>
                    <a-input v-model:value="form.nombre_clase" placeholder="Ej. Introducción al álgebra" />
                </a-form-item>

                <a-form-item>
                    <template #label><ThunderboltOutlined />Acceso a la clase</template>
                    <div class="acc-opts">
                        <button type="button" class="acc-opt" :class="{ on: !form.gratis }" @click="form.gratis = false">
                            <CrownFilled />
                            <b>Premium</b>
                            <small>Requiere plan activo</small>
                        </button>
                        <button type="button" class="acc-opt" :class="{ on: form.gratis }" @click="form.gratis = true">
                            <UnlockFilled />
                            <b>Gratuita</b>
                            <small>Muestra abierta para todos</small>
                        </button>
                    </div>
                </a-form-item>

                <p class="sains-modal__section"><PaperClipOutlined /> Material adicional</p>
                <p class="rec-help">
                    Agrega todos los recursos que el estudiante necesite para esta clase: PDFs, presentaciones,
                    enlaces, podcasts, videos de apoyo… Los que quieras.
                </p>

                <div v-if="form.recursos.length" class="rec-list">
                    <div v-for="(r, i) in form.recursos" :key="i" class="rec-item">
                        <span class="rec-item__n">{{ i + 1 }}</span>
                        <div class="rec-item__body">
                            <div class="rec-item__row">
                                <a-input v-model:value="r.titulo" placeholder="Título del material" class="rec-item__titulo" />
                                <a-select v-model:value="r.tipo" :options="tipoRecursoOpts" class="rec-item__tipo" />
                            </div>
                            <a-input v-model:value="r.url" placeholder="https://… (enlace al recurso)"
                                :status="r.url && !esUrl(r.url) ? 'error' : undefined">
                                <template #prefix><LinkOutlined /></template>
                            </a-input>
                            <a-input v-model:value="r.descripcion" placeholder="Descripción breve (opcional)" class="rec-item__desc" />
                        </div>
                        <button type="button" class="rec-item__del" title="Quitar material" @click="quitarRecurso(i)">
                            <DeleteOutlined />
                        </button>
                    </div>
                </div>

                <a-button block type="dashed" class="rec-add" @click="nuevoRecurso">
                    <template #icon><FolderAddOutlined /></template>
                    {{ form.recursos.length ? 'Agregar otro material' : 'Agregar material adicional' }}
                </a-button>
            </a-form>
        </a-modal>

        <a-modal v-model:open="videoOpen" :title="videoTitulo || 'Video de la clase'" :footer="null" :width="820" centered destroy-on-close>
            <div v-if="videoEmbed" class="video-frame">
                <iframe :src="videoEmbed" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
            </div>
            <a-empty v-else description="Esta clase no tiene un video enlazado" />
        </a-modal>
    </AdminLayout>
</template>

<style scoped>
.video-frame { position: relative; padding-top: 56.25%; border-radius: 12px; overflow: hidden; background: #000; }
.video-frame iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }

.vid-hint { font-size: 12px; color: #b45309; margin-top: 6px; }
.vid-hint a { color: #4f46e5; font-weight: 600; }
.vid-frame {
    position: relative; padding-top: 52%; margin-top: 12px;
    border-radius: 12px; overflow: hidden; background: #000; border: 1px solid var(--sains-line);
}
.vid-frame iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }

/* Acceso: dos tarjetas seleccionables */
.acc-opts { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.acc-opt {
    display: flex; flex-direction: column; align-items: flex-start; gap: 2px;
    padding: 12px 14px; border-radius: 12px; cursor: pointer; text-align: left;
    background: #f8fafc; border: 1.5px solid var(--sains-line);
    transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
}
.acc-opt .anticon { font-size: 16px; color: #94a3b8; margin-bottom: 4px; }
.acc-opt b { font-size: 13.5px; color: #334155; }
.acc-opt small { font-size: 11px; color: #94a3b8; }
.acc-opt.on { border-color: #6366f1; background: #eef2ff; box-shadow: 0 6px 16px -8px rgba(79, 70, 229, .4); }
.acc-opt.on .anticon { color: #4f46e5; }
.acc-opt.on b { color: #4338ca; }

/* Material adicional (recursos) */
.rec-help { font-size: 12px; color: #94a3b8; margin: -4px 0 12px; line-height: 1.5; }
.rec-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 12px; }
.rec-item {
    display: flex; gap: 10px; align-items: flex-start;
    padding: 12px; border-radius: 13px; background: #f8fafc; border: 1px solid var(--sains-line);
}
.rec-item__n {
    flex: none; width: 24px; height: 24px; border-radius: 8px; margin-top: 3px;
    display: flex; align-items: center; justify-content: center;
    background: #6366f1; color: #fff; font-size: 12px; font-weight: 700;
}
.rec-item__body { flex: 1; display: flex; flex-direction: column; gap: 8px; min-width: 0; }
.rec-item__row { display: flex; gap: 8px; }
.rec-item__titulo { flex: 1; }
.rec-item__tipo { width: 200px; flex: none; }
.rec-item__desc :deep(input) { font-size: 12.5px; }
.rec-item__del {
    flex: none; width: 32px; height: 32px; border: 0; border-radius: 9px; cursor: pointer; margin-top: 3px;
    background: #fee2e2; color: #dc2626; font-size: 13px; transition: all .14s ease;
}
.rec-item__del:hover { background: #dc2626; color: #fff; transform: translateY(-1px); }
.rec-add { margin-bottom: 4px; font-weight: 600; height: 40px; }

@media (max-width: 560px) {
    .acc-opts { grid-template-columns: 1fr; }
    .rec-item__row { flex-wrap: wrap; }
    .rec-item__tipo { width: 100%; }
}

.clase-cell { display: flex; align-items: center; gap: 11px; min-width: 0; }
.clase-play {
    flex: none; width: 32px; height: 32px; border: 0; border-radius: 10px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-size: 15px;
    background: #eef2ff; color: #4f46e5; transition: transform .14s ease, background .14s ease, box-shadow .14s ease;
}
.clase-play:hover { background: #4f46e5; color: #fff; transform: translateY(-1px); box-shadow: 0 6px 14px -6px rgba(79, 70, 229, .6); }
.clase-play.is-empty { background: #f1f5f9; color: #cbd5e1; cursor: default; }
.clase-cell__txt { display: flex; flex-direction: column; min-width: 0; }
.clase-cell__name { font-weight: 600; color: #0f172a; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.clase-cell__sub { font-size: 11px; color: #94a3b8; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>
