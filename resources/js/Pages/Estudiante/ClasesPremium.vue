<script setup>
import { computed, onBeforeUnmount, reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    PlayCircleFilled, CheckCircleFilled, LockOutlined, CrownOutlined, FileTextOutlined,
    ThunderboltFilled, BookOutlined, FormOutlined, TrophyFilled,
} from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';
import { message } from '@/lib/notify';

const PREVIEW_SEGUNDOS = 15;

const props = defineProps({
    asignaturas: { type: Array, default: () => [] },
    tieneAccesoPremium: { type: Boolean, default: false },
    examenFinal: { type: Object, default: null },
});

const vistas = reactive({});
props.asignaturas.forEach((a) => a.clases.forEach((c) => { vistas[c.id] = c.vista; }));

const clasesConVideo = computed(() => props.asignaturas.flatMap((a) => a.clases.filter((c) => c.link)));
const totalVideos = computed(() => clasesConVideo.value.length);
const videosVistos = computed(() => clasesConVideo.value.filter((c) => vistas[c.id]).length);
const avance = computed(() => (totalVideos.value ? Math.round((videosVistos.value / totalVideos.value) * 100) : 0));

// Clases gratuitas de todas las materias, agrupadas en su propio panel del acordeón.
const clasesGratuitas = computed(() => props.asignaturas.flatMap(
    (a) => a.clases.filter((c) => c.link && c.gratis).map((c) => ({ ...c, materia: a.nombre })),
));
const progresoGratuitas = computed(() => {
    if (!clasesGratuitas.value.length) return 0;
    return Math.round((clasesGratuitas.value.filter((c) => vistas[c.id]).length / clasesGratuitas.value.length) * 100);
});

// Primera clase sin ver (para el botón "Continuar")
const siguienteClase = computed(() => {
    for (const a of props.asignaturas) {
        for (const c of a.clases) {
            if (c.link && !vistas[c.id] && puedeVer(c)) return c;
        }
    }
    return null;
});

const modal = ref(false);
const claseActiva = ref(null);
const marcando = ref(false);

// Acordeón: sólo una asignatura abierta a la vez (la primera al entrar).
const asigAbierta = ref(props.asignaturas.length ? String(props.asignaturas[0].id) : undefined);

// Vista previa limitada (estudiantes sin plan): 15 s y luego muro de pago.
const esPreview = ref(false);
const previewRestante = ref(PREVIEW_SEGUNDOS);
const previewBloqueado = ref(false);
let previewTimer = null;
let previewTick = null;

function limpiarPreview() {
    clearTimeout(previewTimer);
    clearInterval(previewTick);
    previewTimer = null;
    previewTick = null;
}
function iniciarPreview() {
    esPreview.value = true;
    previewBloqueado.value = false;
    previewRestante.value = PREVIEW_SEGUNDOS;
    limpiarPreview();
    previewTick = setInterval(() => {
        previewRestante.value = Math.max(0, previewRestante.value - 1);
    }, 1000);
    previewTimer = setTimeout(() => {
        previewBloqueado.value = true;
        limpiarPreview();
    }, PREVIEW_SEGUNDOS * 1000);
}
function irACheckout() {
    modal.value = false;
    router.visit(route('estudiante.checkout'));
}

onBeforeUnmount(limpiarPreview);

function parseVideo(link) {
    if (!link) return { embed: null, thumb: null };
    try {
        if (link.includes('vimeo.com/')) {
            const id = link.split('/').pop().split('?')[0];
            return { embed: `https://player.vimeo.com/video/${id}`, thumb: `https://vumbnail.com/${id}.jpg` };
        }
        if (link.includes('youtube.com/watch')) {
            const id = new URL(link).searchParams.get('v');
            return { embed: `https://www.youtube.com/embed/${id}`, thumb: `https://img.youtube.com/vi/${id}/mqdefault.jpg` };
        }
        if (link.includes('youtu.be/')) {
            const id = link.split('youtu.be/')[1].split(/[?&]/)[0];
            return { embed: `https://www.youtube.com/embed/${id}`, thumb: `https://img.youtube.com/vi/${id}/mqdefault.jpg` };
        }
        if (link.includes('drive.google.com')) {
            return { embed: link.replace(/\/view.*$/, '/preview'), thumb: null };
        }
    } catch (e) { /* ignore */ }
    return { embed: link, thumb: null };
}

function puedeVer(clase) {
    return props.tieneAccesoPremium || clase.gratis;
}

function abrir(clase) {
    claseActiva.value = { ...clase, ...parseVideo(clase.link) };
    esPreview.value = false;
    previewBloqueado.value = false;
    limpiarPreview();

    if (!puedeVer(clase)) {
        modal.value = true;
        iniciarPreview();
        return;
    }

    modal.value = true;
    axios.post(route('estudiante.registrar.progreso.video'), {
        video_id: clase.id, es_primer_registro: true, porcentaje_visto: 0,
    }).catch(() => {});
}

function cerrarModal() {
    modal.value = false;
    limpiarPreview();
    esPreview.value = false;
    previewBloqueado.value = false;
}

async function marcarVista() {
    if (!claseActiva.value) return;
    marcando.value = true;
    try {
        const { data } = await axios.post(route('estudiante.registrar.progreso.video'), {
            video_id: claseActiva.value.id, porcentaje_visto: 100, es_primer_registro: false,
        });
        if (data.success) {
            vistas[claseActiva.value.id] = true;
            message.success('Clase marcada como completada');
            modal.value = false;
        }
    } catch (e) {
        message.error('No se pudo registrar el progreso');
    } finally {
        marcando.value = false;
    }
}

function progresoAsig(a) {
    const con = a.clases.filter((c) => c.link);
    if (!con.length) return 0;
    return Math.round((con.filter((c) => vistas[c.id]).length / con.length) * 100);
}

function irAExamenFinal() {
    if (!props.examenFinal) return;
    router.visit(route('estudiante.examen-curso', props.examenFinal.id));
}
</script>

<template>
    <EstudianteLayout title="Clases premium">
        <section class="sains-hero">
            <div class="sains-hero__grid">
                <div>
                    <span class="sains-hero__eyebrow"><CrownOutlined /> Curso Premium SAINS 2026</span>
                    <h1 class="sains-hero__title">Clases en video</h1>
                    <p class="sains-hero__sub">
                        {{ asignaturas.length }} asignaturas · {{ totalVideos }} clases ·
                        {{ tieneAccesoPremium ? `${videosVistos} completadas` : 'vista previa limitada' }}
                    </p>

                    <div class="hero-actions">
                        <a-button v-if="!tieneAccesoPremium" size="large" class="hero-up"
                            @click="router.visit(route('estudiante.checkout'))">
                            <template #icon><CrownOutlined /></template>Desbloquear todo el curso
                        </a-button>

                        <template v-else>
                            <a-button v-if="siguienteClase" size="large" class="hero-up"
                                @click="abrir(siguienteClase)">
                                <template #icon><PlayCircleFilled /></template>
                                {{ videosVistos ? 'Continuar' : 'Empezar' }}: {{ siguienteClase.nombre_clase }}
                            </a-button>
                        </template>
                    </div>
                </div>

                <div class="sains-hero__aside">
                    <div class="sains-hero__ring" :style="{ '--v': avance }">
                        <div><span>
                            <b>{{ avance }}%</b>
                            <span>completado</span>
                        </span></div>
                    </div>
                </div>
            </div>
        </section>

        <div class="asigs sains-stagger">
            <a-collapse v-model:activeKey="asigAbierta" accordion ghost :bordered="false">
                <a-collapse-panel v-if="clasesGratuitas.length" key="gratuitas">
                    <template #header>
                        <div class="asig-head" :class="{ 'is-open': asigAbierta === 'gratuitas' }">
                            <span class="asig-head__ico asig-head__ico--gratis"><ThunderboltFilled /></span>
                            <div class="asig-head__txt">
                                <b>Clases gratuitas</b>
                                <small>
                                    {{ clasesGratuitas.length }} clases ·
                                    {{ clasesGratuitas.filter((c) => vistas[c.id]).length }} completadas
                                </small>
                            </div>
                            <div class="asig-head__ring" :class="{ full: progresoGratuitas === 100 }">
                                <svg viewBox="0 0 44 44">
                                    <circle class="bg" cx="22" cy="22" r="19" />
                                    <circle class="fg" cx="22" cy="22" r="19"
                                        :stroke-dasharray="`${(progresoGratuitas / 100) * 119.4} 119.4`" />
                                </svg>
                                <span>{{ progresoGratuitas }}<i>%</i></span>
                            </div>
                        </div>
                    </template>

                    <div class="clases-grid">
                        <button
                            v-for="c in clasesGratuitas"
                            :key="c.id"
                            class="clase-card"
                            :class="{ done: vistas[c.id] }"
                            @click="abrir(c)"
                        >
                            <span class="clase-card__thumb">
                                <img v-if="parseVideo(c.link).thumb" :src="parseVideo(c.link).thumb" alt="" loading="lazy" />
                                <span class="clase-card__badge">Gratis</span>
                                <span v-if="vistas[c.id]" class="clase-card__badge is-done is-right">Completada</span>
                                <span v-if="c.duracion" class="clase-card__dur">{{ c.duracion }}</span>
                                <span class="clase-card__play">
                                    <CheckCircleFilled v-if="vistas[c.id]" />
                                    <PlayCircleFilled v-else />
                                </span>
                            </span>
                            <span class="clase-card__meta">
                                <small>{{ c.materia }}</small>
                                <b>{{ c.nombre_clase }}</b>
                            </span>
                        </button>
                    </div>
                </a-collapse-panel>

                <a-collapse-panel v-for="a in asignaturas" :key="String(a.id)">
                    <template #header>
                        <div class="asig-head" :class="{ 'is-open': asigAbierta === String(a.id) }">
                            <span class="asig-head__ico"><BookOutlined /></span>
                            <div class="asig-head__txt">
                                <b>{{ a.nombre }}</b>
                                <small>
                                    {{ a.clases.filter((c) => c.link).length }} clases ·
                                    <template v-if="tieneAccesoPremium">
                                        {{ a.clases.filter((c) => c.link && vistas[c.id]).length }} completadas
                                    </template>
                                    <template v-else>vista previa gratuita</template>
                                </small>
                            </div>
                            <div v-if="tieneAccesoPremium" class="asig-head__ring" :class="{ full: progresoAsig(a) === 100 }">
                                <svg viewBox="0 0 44 44">
                                    <circle class="bg" cx="22" cy="22" r="19" />
                                    <circle class="fg" cx="22" cy="22" r="19"
                                        :stroke-dasharray="`${(progresoAsig(a) / 100) * 119.4} 119.4`" />
                                </svg>
                                <span>{{ progresoAsig(a) }}<i>%</i></span>
                            </div>
                            <span v-else class="asig-head__lock"><LockOutlined /></span>
                        </div>
                    </template>
                    <template #extra>
                        <button
                            v-if="a.examen && tieneAccesoPremium"
                            type="button" class="asig-examen"
                            @click.stop="router.visit(route('estudiante.examen.materia', a.examen.id))"
                        >
                            <FileTextOutlined /><span>Examen de la materia</span>
                        </button>
                    </template>

                    <div class="clases-grid">
                        <button
                            v-for="c in a.clases.filter((x) => x.link)"
                            :key="c.id"
                            class="clase-card"
                            :class="{ locked: !puedeVer(c), done: vistas[c.id] }"
                            @click="abrir(c)"
                        >
                            <span class="clase-card__thumb">
                                <img v-if="parseVideo(c.link).thumb" :src="parseVideo(c.link).thumb" alt="" loading="lazy" />
                                <span v-if="c.gratis && !tieneAccesoPremium" class="clase-card__badge">Gratis</span>
                                <span v-else-if="vistas[c.id]" class="clase-card__badge is-done">Completada</span>
                                <span v-if="c.duracion" class="clase-card__dur">{{ c.duracion }}</span>
                                <span class="clase-card__play">
                                    <LockOutlined v-if="!puedeVer(c)" />
                                    <CheckCircleFilled v-else-if="vistas[c.id]" />
                                    <PlayCircleFilled v-else />
                                </span>
                            </span>
                            <span class="clase-card__meta">
                                <small>Clase {{ c.orden ?? c.num_clase }} de {{ a.clases.filter((x) => x.link).length }}</small>
                                <b>{{ c.nombre_clase }}</b>
                            </span>
                        </button>
                    </div>
                </a-collapse-panel>
            </a-collapse>
        </div>

        <section v-if="examenFinal" class="examen-final" :class="{ 'is-listo': tieneAccesoPremium }">
            <span class="examen-final__ico"><TrophyFilled /></span>
            <div class="examen-final__txt">
                <b>Examen final del curso</b>
                <small v-if="!tieneAccesoPremium">Necesitas el Plan Premium para desbloquearlo.</small>
                <small v-else>Evalúa todas las materias del curso · {{ examenFinal.numero_preguntas }} preguntas · {{ examenFinal.tiempo }} min.</small>
            </div>
            <a-button
                type="primary" size="large" class="examen-final__btn"
                :disabled="!tieneAccesoPremium"
                @click="irAExamenFinal"
            >
                <template #icon><FormOutlined /></template>
                {{ tieneAccesoPremium ? 'Comenzar examen final' : 'Bloqueado' }}
            </a-button>
        </section>

        <a-modal
            :open="modal"
            :title="claseActiva?.nombre_clase"
            :footer="null"
            width="840px"
            centered
            destroy-on-close
            @cancel="cerrarModal"
        >
            <div v-if="esPreview && !previewBloqueado" class="preview-banner">
                <ThunderboltFilled />
                <span>Vista previa gratuita · quedan <b>{{ previewRestante }}s</b></span>
            </div>

            <div class="video-frame">
                <iframe
                    v-if="claseActiva && !previewBloqueado"
                    :src="claseActiva.embed"
                    allow="autoplay; fullscreen; picture-in-picture"
                    allowfullscreen
                ></iframe>

                <div v-else-if="previewBloqueado" class="paywall">
                    <span class="paywall__ic"><CrownOutlined /></span>
                    <h3>Paga la suscripción para tener acceso completo a los cursos</h3>
                    <p>Terminó tu vista previa de {{ PREVIEW_SEGUNDOS }} segundos. Con el plan Premium ves todas las clases completas, sin límites.</p>
                    <a-button type="primary" size="large" @click="irACheckout">
                        <template #icon><CrownOutlined /></template>Hazte Premium
                    </a-button>
                </div>
            </div>

            <!-- 👇 LEYENDA LEGAL -->
            <p class="video-disclaimer">
                Uso educativo / informativo sin fines de lucro. No reclamamos la propiedad del material original.
            </p>

            <div class="video-actions">
                <a-button v-if="claseActiva?.url && !esPreview" type="link" :href="claseActiva.url" target="_blank">
                    <template #icon><FileTextOutlined /></template>Material de apoyo
                </a-button>
                <span v-else></span>
                <a-button
                    v-if="!esPreview"
                    type="primary" :loading="marcando" :disabled="vistas[claseActiva?.id]" @click="marcarVista"
                >
                    <template #icon><ThunderboltFilled /></template>
                    {{ vistas[claseActiva?.id] ? 'Clase completada' : 'Marcar como vista' }}
                </a-button>
                <a-button v-else-if="!previewBloqueado" type="primary" @click="irACheckout">
                    <template #icon><CrownOutlined /></template>Desbloquear clase completa
                </a-button>
            </div>
        </a-modal>
    </EstudianteLayout>
</template>

<style scoped>
/* ================== Hero ================== */
.hero-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }

.hero-up {
    background: #fff !important; border-color: #fff !important;
    color: #4f46e5 !important; font-weight: 650;
}
.hero-up:hover { background: #f1f0ff !important; }

/* ================== Acordeón ================== */
.asigs :deep(.ant-collapse-item) {
    background: #fff;
    border: 1px solid var(--sains-line) !important;
    border-radius: 16px !important;
    margin-bottom: 12px;
    overflow: hidden;
    transition: box-shadow .2s ease, border-color .2s ease;
}
.asigs :deep(.ant-collapse-item-active) {
    box-shadow: 0 12px 32px -16px rgba(79, 70, 229, .32);
    border-color: #c7d2fe !important;
}
.asigs :deep(.ant-collapse-header) { padding: 14px 16px !important; align-items: center !important; }
.asigs :deep(.ant-collapse-content-box) { padding: 4px 14px 16px !important; }

.asig-head { display: flex; align-items: center; gap: 13px; width: 100%; }
.asig-head__ico {
    width: 40px; height: 40px; border-radius: 12px; flex: none;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #eef2ff, #e0e7ff); color: #4f46e5; font-size: 16px;
    transition: background .2s ease, color .2s ease, transform .2s ease;
}
.asig-head.is-open .asig-head__ico {
    background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; transform: scale(1.05);
}
.asig-head__txt { flex: 1; display: flex; flex-direction: column; line-height: 1.3; min-width: 0; }
.asig-head__txt b { font-size: 14.5px; color: #0f172a; }
.asig-head__txt small { font-size: 12px; color: #94a3b8; }
.asig-head__lock {
    width: 32px; height: 32px; border-radius: 10px; flex: none;
    display: flex; align-items: center; justify-content: center;
    background: #fef3c7; color: #d97706; font-size: 13px;
}

.asig-head__ring { position: relative; width: 44px; height: 44px; flex: none; }
.asig-head__ring svg { width: 44px; height: 44px; transform: rotate(-90deg); }
.asig-head__ring circle { fill: none; stroke-width: 4; }
.asig-head__ring circle.bg { stroke: #eef2ff; }
.asig-head__ring circle.fg {
    stroke: #6366f1; stroke-linecap: round;
    transition: stroke-dasharray .5s cubic-bezier(.16, 1, .3, 1);
}
.asig-head__ring.full circle.fg { stroke: #16a34a; }
.asig-head__ring span {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 800; color: #4338ca; letter-spacing: -.03em;
}
.asig-head__ring.full span { color: #15803d; }
.asig-head__ring span i { font-size: 7px; font-style: normal; font-weight: 700; margin-left: 1px; }

.asig-examen {
    display: inline-flex; align-items: center; gap: 7px;
    border: 0; cursor: pointer;
    padding: 7px 14px; border-radius: 999px;
    font-size: 12.5px; font-weight: 650; color: #fff;
    background: linear-gradient(135deg, #f59e0b, #ec4899);
    box-shadow: 0 8px 18px -8px rgba(236, 72, 153, .55);
    transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
    white-space: nowrap;
}
.asig-examen .anticon { font-size: 13px; }
.asig-examen:hover { transform: translateY(-1px); box-shadow: 0 12px 24px -8px rgba(236, 72, 153, .6); filter: brightness(1.03); }
@media (max-width: 560px) { .asig-examen span { display: none; } .asig-examen { padding: 8px; } }

/* ================== Examen final ================== */
.examen-final {
    display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    margin: 20px 0 8px; padding: 20px 22px; border-radius: 18px;
    background: linear-gradient(135deg, #1e1b4b 0%, #4c1d95 60%, #831843 100%);
    color: #fff;
}
.examen-final__ico {
    width: 52px; height: 52px; border-radius: 14px; flex: none;
    display: flex; align-items: center; justify-content: center;
    background: rgba(255, 255, 255, .16); font-size: 24px; color: #fde68a;
}
.examen-final__txt { flex: 1; min-width: 200px; display: flex; flex-direction: column; gap: 2px; }
.examen-final__txt b { font-size: 16px; }
.examen-final__txt small { font-size: 12.5px; color: rgba(255, 255, 255, .78); }
.examen-final__btn {
    background: #fff !important; border-color: #fff !important; color: #4c1d95 !important; font-weight: 700;
}
.examen-final__btn:hover:not(:disabled) { background: #f5f3ff !important; border-color: #f5f3ff !important; }
.examen-final__btn:disabled { background: rgba(255, 255, 255, .25) !important; border-color: transparent !important; color: rgba(255, 255, 255, .7) !important; }
.examen-final.is-listo { box-shadow: 0 16px 36px -18px rgba(76, 29, 149, .6); }

.asig-head__ico--gratis { background: linear-gradient(135deg, #6ee7b7, #16a34a); color: #fff; }

/* ================== Clases ================== */
.clases-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(214px, 1fr)); gap: 14px;
    padding: 6px 2px 6px;
}
.clase-card {
    display: flex; flex-direction: column; text-align: left; cursor: pointer; overflow: hidden;
    background: #fff; border: 1px solid var(--sains-line); border-radius: 16px;
    transition: transform .18s cubic-bezier(.16,1,.3,1), box-shadow .18s ease, border-color .18s ease;
}
.clase-card:hover { transform: translateY(-4px); box-shadow: 0 16px 34px -18px rgba(15, 23, 42, .28); border-color: #c7d2fe; }
.clase-card.done { border-color: #86efac; background: #f0fdf4; }
.clase-card__thumb {
    position: relative; aspect-ratio: 16 / 9; background: linear-gradient(135deg, #6366f1, #a855f7);
    display: block; overflow: hidden;
}
.clase-card__thumb img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .3s ease; }
.clase-card:hover .clase-card__thumb img { transform: scale(1.06); }
.clase-card.locked .clase-card__thumb img { filter: blur(4px) brightness(.7); }
.clase-card__badge {
    position: absolute; top: 8px; left: 8px; z-index: 2;
    font-size: 10px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
    color: #065f46; background: #6ee7b7; padding: 3px 8px; border-radius: 999px;
    box-shadow: 0 4px 10px -3px rgba(6, 95, 70, .4);
}
.clase-card__badge.is-done { color: #fff; background: #16a34a; box-shadow: 0 4px 10px -3px rgba(22, 163, 74, .5); }
.clase-card__badge.is-right { left: auto; right: 8px; }
.clase-card__dur {
    position: absolute; bottom: 8px; right: 8px; z-index: 2;
    font-size: 10.5px; font-weight: 600; color: #fff;
    background: rgba(15, 23, 42, .78); padding: 2px 7px; border-radius: 6px;
}
.clase-card__play {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 34px; text-shadow: 0 2px 12px rgba(0, 0, 0, .45);
    background: rgba(15, 23, 42, .1); transition: background .18s ease, transform .18s ease;
}
.clase-card:hover .clase-card__play { background: rgba(15, 23, 42, .3); transform: scale(1.08); }
.clase-card.done .clase-card__play { color: #4ade80; }
.clase-card.locked .clase-card__play { color: #fde68a; font-size: 26px; }
.clase-card__meta { padding: 11px 13px 13px; display: flex; flex-direction: column; gap: 3px; }
.clase-card__meta small { font-size: 10.5px; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; font-weight: 600; }
.clase-card__meta b { font-size: 13px; color: #1e293b; line-height: 1.35; }

/* ================== Modal del video ================== */
.video-frame { position: relative; padding-top: 56.25%; border-radius: 12px; overflow: hidden; background: #000; }
.video-frame iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }

/* 👇 Leyenda legal */
.video-disclaimer {
    margin: 12px 0 0;
    font-size: 11.5px;
    color: #94a3b8;
    text-align: center;
    line-height: 1.5;
    font-style: italic;
}

.video-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; flex-wrap: wrap; gap: 8px; }

.preview-banner {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    background: linear-gradient(135deg, #f59e0b, #ec4899); color: #fff;
    font-size: 13px; font-weight: 600; padding: 8px 14px; border-radius: 10px; margin-bottom: 12px;
}
.preview-banner b { font-variant-numeric: tabular-nums; }

.paywall {
    position: absolute; inset: 0; z-index: 2;
    display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;
    gap: 12px; padding: 28px;
    background: linear-gradient(160deg, #1e1b4b 0%, #4c1d95 60%, #831843 100%);
    color: #fff;
}
.paywall__ic {
    width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center;
    background: rgba(255, 255, 255, .16); font-size: 26px; color: #fde68a;
}
.paywall h3 { font-size: 18px; font-weight: 800; line-height: 1.3; max-width: 460px; margin: 0; }
.paywall p { font-size: 13px; color: rgba(255, 255, 255, .8); max-width: 420px; margin: 0; line-height: 1.5; }
.paywall .ant-btn { margin-top: 6px; background: #fff; border-color: #fff; color: #4c1d95; font-weight: 700; }
.paywall .ant-btn:hover { background: #f5f3ff; border-color: #f5f3ff; color: #4c1d95; }
</style>