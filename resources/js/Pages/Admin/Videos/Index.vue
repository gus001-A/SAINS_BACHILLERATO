<script setup>
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import {
    PlusOutlined, SearchOutlined, VideoCameraOutlined, CrownFilled,
    GlobalOutlined, PlayCircleFilled, FieldTimeOutlined,
} from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';
import RowActions from '@/Components/RowActions.vue';
import { confirmDelete, message } from '@/lib/notify';

const props = defineProps({
    videos: { type: Object, required: true },
    materias: { type: Array, default: () => [] },
    stats: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const materia = ref(props.filters.materia ?? undefined);
const tema = ref(props.filters.tema ?? '');
const plan = ref(props.filters.plan === 1 ? 'premium' : props.filters.plan === 0 ? 'publico' : 'todos');
let debounce = null;

function reload(extra = {}) {
    const planParam = plan.value === 'premium' ? 1 : plan.value === 'publico' ? 0 : undefined;
    router.get(route('admin.videos.index'),
        { search: search.value || undefined, materia: materia.value || undefined, tema: tema.value || undefined, plan: planParam, ...extra },
        { preserveState: true, replace: true, preserveScroll: true });
}
watch([search, tema], () => { clearTimeout(debounce); debounce = setTimeout(() => reload(), 350); });
watch([materia, plan], () => reload());

const materiaOptions = computed(() => props.materias.map((m) => ({ value: m, label: m })));
const planOptions = [
    { value: 'todos', label: 'Todos' },
    { value: 'premium', label: 'Premium' },
    { value: 'publico', label: 'Públicos' },
];

function thumb(link) {
    if (!link) return null;
    try {
        if (link.includes('vimeo.com/')) {
            const id = link.split('/').pop().split('?')[0];
            return `https://vumbnail.com/${id}.jpg`;
        }
        if (link.includes('youtube.com/watch')) return `https://img.youtube.com/vi/${new URL(link).searchParams.get('v')}/mqdefault.jpg`;
        if (link.includes('youtu.be/')) return `https://img.youtube.com/vi/${link.split('youtu.be/')[1].split(/[?&]/)[0]}/mqdefault.jpg`;
    } catch (e) { /* ignore */ }
    return null;
}
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

const videoOpen = ref(false);
const videoEmbed = ref(null);
const videoTitulo = ref('');
function verVideo(v) {
    videoEmbed.value = parseVideo(v.link);
    videoTitulo.value = v.titulo;
    videoOpen.value = true;
}

const modalOpen = ref(false);
const editing = ref(null);
const form = useForm({ materia: undefined, tema: '', titulo: '', link: '', duracion: '', plan: true });

const cargandoDuracion = ref(false);
async function obtenerDuracion() {
    if (!form.link) { message.warning('Primero ingresa el enlace del video'); return; }
    cargandoDuracion.value = true;
    try {
        const { data } = await axios.get(route('admin.videos.duracion'), { params: { link: form.link } });
        if (data.success) {
            form.duracion = data.duracion;
            message.success(`Duración detectada: ${data.duracion}`);
        } else {
            message.info(data.message || 'No se pudo obtener la duración');
        }
    } catch (e) {
        message.error('No se pudo consultar la duración');
    } finally {
        cargandoDuracion.value = false;
    }
}

function openCreate() {
    editing.value = null;
    form.reset();
    form.plan = true;
    form.clearErrors();
    modalOpen.value = true;
}
function openEdit(r) {
    editing.value = r;
    form.materia = r.materia;
    form.tema = r.tema;
    form.titulo = r.titulo;
    form.link = r.link;
    form.duracion = r.duracion && r.duracion !== '00:00:00' ? r.duracion : '';
    form.plan = !!r.plan;
    form.clearErrors();
    modalOpen.value = true;
}
function submit() {
    const opts = { onSuccess: () => { modalOpen.value = false; }, preserveScroll: true };
    if (editing.value) form.put(route('admin.videos.update', editing.value.id), opts);
    else form.post(route('admin.videos.store'), opts);
}
function eliminar(r) {
    confirmDelete({ title: '¿Eliminar video?', content: r.titulo, onOk: () => router.delete(route('admin.videos.destroy', r.id), { preserveScroll: true }) });
}

const pagination = computed(() => ({
    current: props.videos.current_page,
    pageSize: props.videos.per_page,
    total: props.videos.total,
    showSizeChanger: false,
    showTotal: (t) => `${t} videos`,
}));
function onChange(pag) { reload({ page: pag.current }); }

const columns = [
    { title: 'Título', dataIndex: 'titulo', key: 'titulo' },
    { title: 'Materia', dataIndex: 'materia', key: 'materia', width: 200 },
    { title: 'Tema', dataIndex: 'tema', key: 'tema', width: 190 },
    { title: 'Visibilidad', key: 'plan', width: 140, align: 'center' },
    { title: 'Duración', dataIndex: 'duracion', key: 'duracion', width: 110, align: 'center' },
    { title: '', key: 'acciones', width: 100, align: 'right' },
];
</script>

<template>
    <AdminLayout title="Videos">
        <PageHead title="Videos" subtitle="Biblioteca de video del curso" :icon="VideoCameraOutlined">
            <template #actions>
                <a-button type="primary" @click="openCreate"><template #icon><PlusOutlined /></template>Nuevo video</a-button>
            </template>
        </PageHead>

        <div class="sains-stats sains-stats--4">
            <StatCard label="Total" :value="stats.total" color="indigo" />
            <StatCard label="Premium" :value="stats.premium" color="amber" />
            <StatCard label="Públicos" :value="stats.publicos" color="green" />
            <StatCard label="Con progreso" :value="stats.conProgresos" color="violet" />
        </div>

        <a-card :bordered="false" :body-style="{ padding: '4px 4px 12px' }">
            <a-table class="filtered-table" :columns="columns" :data-source="videos.data" :pagination="pagination"
                row-key="id" size="middle" :scroll="{ x: 1040 }" @change="onChange">
                <template #emptyText><a-empty :image="null" description="Sin videos" /></template>

                <template #summary>
                    <a-table-summary-row>
                        <a-table-summary-cell v-for="(col, i) in columns" :key="col.key ?? i" :index="i">
                            <a-input v-if="col.key === 'titulo'" v-model:value="search" size="small" allow-clear placeholder="Buscar título…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-select v-else-if="col.key === 'materia'" v-model:value="materia" size="small" allow-clear show-search
                                option-filter-prop="label" placeholder="Todas" :options="materiaOptions" />
                            <a-input v-else-if="col.key === 'tema'" v-model:value="tema" size="small" allow-clear placeholder="Tema…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-select v-else-if="col.key === 'plan'" v-model:value="plan" size="small" :options="planOptions" />
                        </a-table-summary-cell>
                    </a-table-summary-row>
                </template>

                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'titulo'">
                        <div class="vid-row">
                            <button type="button" class="vid-row__thumb" title="Reproducir video" @click="verVideo(record)">
                                <img v-if="thumb(record.link)" :src="thumb(record.link)" alt="" loading="lazy" />
                                <span class="vid-row__play"><PlayCircleFilled /></span>
                            </button>
                            <span class="sains-strong">{{ record.titulo }}</span>
                        </div>
                    </template>
                    <template v-else-if="column.key === 'materia'">
                        <a-tag :bordered="false" color="blue">{{ record.materia }}</a-tag>
                    </template>
                    <template v-else-if="column.key === 'tema'">
                        <span class="sains-faint">{{ record.tema || '—' }}</span>
                    </template>
                    <template v-else-if="column.key === 'plan'">
                        <a-tag :bordered="false" :color="record.plan ? 'gold' : 'green'">
                            <component :is="record.plan ? CrownFilled : GlobalOutlined" />
                            {{ record.plan ? 'Premium' : 'Público' }}
                        </a-tag>
                    </template>
                    <template v-else-if="column.key === 'duracion'">
                        <span :class="record.duracion && record.duracion !== '00:00:00' ? '' : 'sains-faint'">
                            {{ record.duracion && record.duracion !== '00:00:00' ? record.duracion : '—' }}
                        </span>
                    </template>
                    <template v-else-if="column.key === 'acciones'">
                        <RowActions editable @edit="openEdit(record)" @delete="eliminar(record)" />
                    </template>
                </template>
            </a-table>
        </a-card>

        <a-modal v-model:open="videoOpen" :title="videoTitulo || 'Video'" :footer="null" :width="820" centered destroy-on-close>
            <div v-if="videoEmbed" class="video-frame">
                <iframe :src="videoEmbed" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
            </div>
            <a-empty v-else description="Este video no tiene un enlace válido" />
        </a-modal>

        <a-modal v-model:open="modalOpen" :title="editing ? 'Editar video' : 'Nuevo video'" class="sains-modal" :width="620" :confirm-loading="form.processing" ok-text="Guardar" @ok="submit">
            <a-form layout="vertical">
                <a-form-item label="Título" required :validate-status="form.errors.titulo ? 'error' : undefined" :help="form.errors.titulo">
                    <a-input v-model:value="form.titulo" />
                </a-form-item>
                <a-row :gutter="16">
                    <a-col :xs="24" :md="12">
                        <a-form-item label="Materia" required :validate-status="form.errors.materia ? 'error' : undefined" :help="form.errors.materia">
                            <a-select v-model:value="form.materia" show-search :options="materiaOptions" placeholder="Selecciona…" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :md="12">
                        <a-form-item label="Tema" required :validate-status="form.errors.tema ? 'error' : undefined" :help="form.errors.tema">
                            <a-input v-model:value="form.tema" />
                        </a-form-item>
                    </a-col>
                </a-row>
                <a-form-item label="Enlace (URL)" required :validate-status="form.errors.link ? 'error' : undefined" :help="form.errors.link">
                    <a-input v-model:value="form.link" placeholder="https://…" />
                </a-form-item>
                <a-row :gutter="16">
                    <a-col :xs="24" :md="14">
                        <a-form-item label="Duración (HH:MM:SS)">
                            <a-input-group compact>
                                <a-input v-model:value="form.duracion" placeholder="00:12:34" style="width: calc(100% - 130px)" />
                                <a-button :loading="cargandoDuracion" @click="obtenerDuracion">
                                    <template #icon><FieldTimeOutlined /></template>Detectar
                                </a-button>
                            </a-input-group>
                            <span style="font-size: 11px; color: #94a3b8">Detecta automáticamente en enlaces de Vimeo y YouTube.</span>
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :md="10">
                        <a-form-item label="Visibilidad">
                            <a-switch v-model:checked="form.plan" checked-children="Premium" un-checked-children="Público" />
                            <span style="margin-left: 10px; font-size: 12px; color: #94a3b8">
                                {{ form.plan ? 'Solo con plan activo.' : 'Visible para todos.' }}
                            </span>
                        </a-form-item>
                    </a-col>
                </a-row>
            </a-form>
        </a-modal>
    </AdminLayout>
</template>

<style scoped>
.vid-row { display: flex; align-items: center; gap: 11px; }
.vid-row__thumb {
    position: relative; flex: none; width: 58px; height: 34px; border-radius: 8px; overflow: hidden;
    border: 0; padding: 0; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #6366f1, #a855f7); color: #fff;
}
.vid-row__thumb img { width: 100%; height: 100%; object-fit: cover; }
.vid-row__play {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    font-size: 15px; color: #fff; background: rgba(15, 23, 42, .22);
    opacity: .75; transition: opacity .14s ease, background .14s ease;
}
.vid-row__thumb:hover .vid-row__play { opacity: 1; background: rgba(15, 23, 42, .4); }

.video-frame { position: relative; padding-top: 56.25%; border-radius: 12px; overflow: hidden; background: #000; }
.video-frame iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
</style>
