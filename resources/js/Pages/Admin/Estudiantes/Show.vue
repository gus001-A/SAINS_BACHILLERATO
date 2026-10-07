<script setup>
import { useForm, router, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import axios from 'axios';
import {
    EditOutlined, KeyOutlined, TeamOutlined, BarChartOutlined, FileDoneOutlined,
    FilePdfOutlined, CheckCircleOutlined, ClockCircleOutlined, CloseCircleOutlined,
    EyeOutlined, DownloadOutlined, CheckOutlined, CloseOutlined, UploadOutlined,
    ExclamationCircleOutlined, FilePdfOutlined as FilePdfOutlinedAlt, SafetyCertificateOutlined,
} from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';
import ModalDocumentosAdmin from '@/Components/ModalDocumentosAdmin.vue';
import { message, confirmAction } from '@/lib/notify';

const props = defineProps({
    estudiante: { type: Object, required: true },
    stats: { type: Object, required: true },
    estudioDiario: { type: Array, default: () => [] },
    examenes: { type: Array, default: () => [] },
    pagos: { type: Array, default: () => [] },
});

const pwdOpen = ref(false);
const pwd = useForm({ new_password: '', new_password_confirmation: '' });
function resetPwd() {
    pwd.post(route('admin.estudiantes.reset-password', props.estudiante.id), {
        preserveScroll: true,
        onSuccess: () => { pwdOpen.value = false; pwd.reset(); },
    });
}

/* ---- Exportar PDF del estudiante ---- */
const exportandoPdf = ref(false);
function exportarPdf() {
    if (exportandoPdf.value) return;
    exportandoPdf.value = true;
    const url = route('admin.estudiantes.pdf', props.estudiante.id);
    // Abre en una pestaña nueva y descarga automáticamente (según Content-Disposition del backend)
    window.open(url, '_blank');
    setTimeout(() => { exportandoPdf.value = false; }, 1200);
}

/* ---- Exportar PDF del estatus de documentos ---- */
const exportandoDocsPdf = ref(false);
function exportarDocumentosPdf() {
    if (exportandoDocsPdf.value) return;
    exportandoDocsPdf.value = true;
    const url = route('admin.estudiantes.documentos-pdf', props.estudiante.id);
    window.open(url, '_blank');
    setTimeout(() => { exportandoDocsPdf.value = false; }, 1200);
}

/* ---- Generar certificado de finalización ---- */
const generandoCertificado = ref(false);
function generarCertificado() {
    confirmAction({
        title: '¿Generar certificado de finalización?',
        content: `Se enviará por correo a ${props.estudiante.correo} y se le avisará dentro del sistema.`,
        okText: 'Sí, generar y enviar',
        onOk: async () => {
            generandoCertificado.value = true;
            try {
                const { data } = await axios.post(route('admin.estudiantes.generar-certificado', props.estudiante.id));
                if (data.ok) {
                    message.success(data.mensaje || 'Certificado generado y enviado.');
                    router.reload({ only: ['estudiante'] });
                } else {
                    message.error(data.mensaje || 'No se pudo generar el certificado.');
                }
            } catch (e) {
                message.error(e.response?.data?.mensaje || 'Error al generar el certificado.');
            } finally {
                generandoCertificado.value = false;
            }
        },
    });
}

function descargarCertificado() {
    window.open(route('admin.estudiantes.certificado.descargar', props.estudiante.id), '_blank');
}

const maxHoras = computed(() => Math.max(1, ...props.estudioDiario.map((d) => d.horas)));
const hayEstudio = computed(() => props.estudioDiario.some((d) => d.horas > 0));
const iniciales = computed(() =>
    (props.estudiante.nombre_completo || '?').trim().split(/\s+/).slice(0, 2).map((w) => w[0]).join('').toUpperCase()
);

const fotoOk = ref(!!props.estudiante.foto_url);

const facts = computed(() => [
    { label: 'Correo', value: props.estudiante.correo },
    { label: 'Teléfono', value: props.estudiante.telefono || '—' },
    { label: 'Tel. casa', value: props.estudiante.telefono_casa || '—' },
    { label: 'Sexo', value: props.estudiante.sexo === 'M' ? 'Masculino' : props.estudiante.sexo === 'F' ? 'Femenino' : '—' },
    { label: 'Nacimiento', value: props.estudiante.fecha_nacimiento || '—' },
    { label: 'Inscripción', value: props.estudiante.fecha_inscripcion || '—' },
    { label: 'Cupón', value: props.estudiante.cupon || '—' },
    { label: 'Carrera', value: props.estudiante.carrera || 'Sin carrera' },
    { label: 'CURP', value: props.estudiante.curp || '—' },
    { label: 'Domicilio', value: props.estudiante.domicilio || '—', ancho: true },
    {
        label: 'Cambios de datos',
        value: props.estudiante.ediciones_datos
            ? `${props.estudiante.ediciones_datos.total} de ${props.estudiante.ediciones_datos.max_total}${props.estudiante.ediciones_datos.restantes_total === 0 ? ' · bloqueado' : ''}`
            : '—',
    },
]);

/* ---- Progreso de videos ---- */
const videosSinVer = computed(() =>
    Math.max(0, (props.stats.total_videos || 0) - (props.stats.videos_completos || 0) - (props.stats.videos_en_progreso || 0))
);
const totalV = computed(() => Math.max(1, props.stats.total_videos || 0));
const seg = computed(() => ({
    done: (props.stats.videos_completos || 0) / totalV.value * 100,
    prog: (props.stats.videos_en_progreso || 0) / totalV.value * 100,
    none: videosSinVer.value / totalV.value * 100,
}));

/* ---- Exámenes ---- */
const tipoMeta = (t) => {
    const k = String(t || '').toLowerCase();
    if (k.includes('materia')) return { color: '#135fbc', bg: '#f2f6fa', label: 'Por materia' };
    if (k.includes('curso')) return { color: '#16a34a', bg: '#f0fdf4', label: 'Por curso' };
    if (k.includes('simula')) return { color: '#dc2626', bg: '#fef2f2', label: 'Simulación' };
    if (k.includes('certific')) return { color: '#b45309', bg: '#fffbeb', label: 'Certificado' };
    return { color: '#1550d0', bg: '#f2f5fa', label: t ? (t[0].toUpperCase() + t.slice(1)) : 'Examen' };
};
const califColor = (n) => (n >= 80 ? '#16a34a' : n >= 60 ? '#f59e0b' : '#dc2626');
const examColumns = [
    { title: 'Tipo', key: 'tipo' },
    { title: 'Aciertos', key: 'aciertos', width: 110, align: 'center' },
    { title: 'Calificación', key: 'calif' },
    { title: 'Fecha', dataIndex: 'fecha', key: 'fecha', width: 110, align: 'right' },
    { title: '', key: 'accion', width: 120, align: 'right' },
];

/* =========================================================
   DOCUMENTOS (admin)
   ========================================================= */
const TIPOS = [
    { value: 'acta_nacimiento',        label: 'Acta de nacimiento',        short: 'Acta' },
    { value: 'curp',                    label: 'CURP',                       short: 'CURP' },
    { value: 'certificado_secundaria',  label: 'Certificado de secundaria',  short: 'Certificado' },
    { value: 'ine',                     label: 'INE',                        short: 'INE' },
];

const documentos = ref({});
const cargandoDocs = ref(true);

/* Modal visor PDF admin */
const pdfModal = ref(false);
const pdfActivo = ref(null);

/* Modal rechazo */
const rechazoOpen = ref(false);
const rechazo = ref({ id: null, tipo: null, motivo: '' });
const rechazando = ref(false);

/* Aprobando (por id) */
const aprobando = ref({});

function estatusMeta(estatus) {
    switch (estatus) {
        case 'aprobado':  return { color: 'success', icon: CheckCircleOutlined, label: 'Aprobado', css: 'ok' };
        case 'rechazado': return { color: 'error',   icon: CloseCircleOutlined, label: 'Rechazado', css: 'rej' };
        default:          return { color: 'warning', icon: ClockCircleOutlined, label: 'En revisión', css: 'rev' };
    }
}

/**
 * ✅ Regla de negocio:
 *  - APROBADO   → bloqueado PARA SIEMPRE (estado final, no se puede rehacer).
 *  - RECHAZADO  → bloqueado hasta que el estudiante suba uno nuevo.
 *  - PENDIENTE  → abierto a revisión.
 */
function estaBloqueado(doc) {
    return doc && (doc.estatus === 'aprobado' || doc.estatus === 'rechazado');
}

/**
 * Solo los aprobados son definitivos. Los rechazados se desbloquean
 * cuando el estudiante sube un nuevo archivo (pasa a "pendiente").
 */
function estaAprobadoFinal(doc) {
    return doc && doc.estatus === 'aprobado';
}

async function cargarDocumentos() {
    cargandoDocs.value = true;
    try {
        const { data } = await axios.get(route('admin.estudiantes.documentos.index', props.estudiante.id));
        const mapa = {};
        TIPOS.forEach((t) => { mapa[t.value] = null; });
        (data.documentos || []).forEach((doc) => { mapa[doc.tipo] = doc; });
        documentos.value = mapa;
    } catch (e) {
        message.error(e.response?.data?.message || 'No se pudieron cargar los documentos');
    } finally {
        cargandoDocs.value = false;
    }
}

function verDocumento(doc) {
    if (!doc) return;
    pdfActivo.value = doc;
    pdfModal.value = true;
}

function cerrarPdfModal() {
    pdfModal.value = false;
}

function descargarDocumento(doc) {
    if (!doc) return;
    window.open(route('admin.estudiantes.documentos.descargar', [props.estudiante.id, doc.id]), '_blank');
}

async function aprobarDocumento(doc) {
    if (!doc) return;
    if (estaBloqueado(doc)) {
        message.warning(
            estaAprobadoFinal(doc)
                ? 'Este documento ya está aprobado y no se puede modificar.'
                : 'Este documento ya fue revisado. El estudiante debe volver a subirlo para revisarlo de nuevo.'
        );
        return;
    }
    aprobando.value = { ...aprobando.value, [doc.id]: true };
    try {
        const { data } = await axios.post(
            route('admin.estudiantes.documentos.aprobar', [props.estudiante.id, doc.id]),
            { observaciones: null }
        );
        if (data.ok) {
            documentos.value = { ...documentos.value, [doc.tipo]: data.documento };
            message.success('Documento aprobado');
        } else {
            message.error(data.message || 'No se pudo aprobar');
        }
    } catch (e) {
        message.error(e.response?.data?.message || 'Error al aprobar');
    } finally {
        aprobando.value = { ...aprobando.value, [doc.id]: false };
    }
}

function abrirRechazo(doc) {
    if (!doc) return;
    if (estaBloqueado(doc)) {
        message.warning(
            estaAprobadoFinal(doc)
                ? 'Este documento ya está aprobado y no se puede rechazar.'
                : 'Este documento ya fue revisado. El estudiante debe volver a subirlo para revisarlo de nuevo.'
        );
        return;
    }
    rechazo.value = { id: doc.id, tipo: doc.tipo, motivo: '' };
    rechazoOpen.value = true;
}

async function confirmarRechazo() {
    if (!rechazo.value.motivo.trim()) {
        message.warning('Escribe el motivo del rechazo.');
        return;
    }
    rechazando.value = true;
    try {
        const { data } = await axios.post(
            route('admin.estudiantes.documentos.rechazar', [props.estudiante.id, rechazo.value.id]),
            { observaciones: rechazo.value.motivo }
        );
        if (data.ok) {
            documentos.value = { ...documentos.value, [rechazo.value.tipo]: data.documento };
            message.success('Documento rechazado');
            rechazoOpen.value = false;
            rechazo.value = { id: null, tipo: null, motivo: '' };
        } else {
            message.error(data.message || 'No se pudo rechazar');
        }
    } catch (e) {
        message.error(e.response?.data?.message || 'Error al rechazar');
    } finally {
        rechazando.value = false;
    }
}

const totalDocumentos = computed(() =>
    TIPOS.filter((t) => documentos.value[t.value]).length
);
const totalAprobados = computed(() =>
    TIPOS.filter((t) => documentos.value[t.value]?.estatus === 'aprobado').length
);

const tipoLabelActivo = computed(() => {
    const t = TIPOS.find((x) => x.value === pdfActivo.value?.tipo);
    return t?.label || 'Documento';
});

const tipoRechazoLabel = computed(() => {
    const t = TIPOS.find((x) => x.value === rechazo.value.tipo);
    return t?.label || 'Documento';
});

onMounted(cargarDocumentos);
</script>

<template>
    <AdminLayout :title="estudiante.nombre_completo">
        <PageHead :title="estudiante.nombre_completo" :subtitle="estudiante.correo" :icon="TeamOutlined"
            back @back="router.visit(route('admin.estudiantes.index'))">
            <template #actions>
                <a-button @click="pwdOpen = true"><template #icon><KeyOutlined /></template>Restablecer contraseña</a-button>
                <a-button
                    class="btn-export-pdf"
                    :loading="exportandoPdf"
                    @click="exportarPdf"
                >
                    <template #icon><FilePdfOutlined /></template>
                    Exportar PDF
                </a-button>
                <a-button
                    class="btn-export-pdf"
                    :loading="exportandoDocsPdf"
                    @click="exportarDocumentosPdf"
                >
                    <template #icon><FilePdfOutlinedAlt /></template>
                    Exportar documentos
                </a-button>
                <a-button
                    type="primary"
                    ghost
                    :loading="generandoCertificado"
                    @click="generarCertificado"
                >
                    <template #icon><SafetyCertificateOutlined /></template>
                    {{ estudiante.certificado_generado ? 'Regenerar certificado' : 'Generar certificado' }}
                </a-button>
                <a-button v-if="estudiante.certificado_generado" @click="descargarCertificado">
                    <template #icon><DownloadOutlined /></template>
                    Descargar certificado
                </a-button>
                <Link :href="route('admin.estudiantes.edit', estudiante.id)">
                    <a-button type="primary"><template #icon><EditOutlined /></template>Editar</a-button>
                </Link>
            </template>
        </PageHead>

        <div class="bento">
            <!-- 1. KPIs -->
            <div class="sains-stats sains-stats--4 bento__stats">
                <StatCard label="Horas de estudio" :value="stats.tiempo_horas" color="indigo" />
                <StatCard label="Sesiones" :value="stats.total_sesiones" color="violet" />
                <StatCard label="Exámenes" :value="stats.total_examenes" color="amber" />
                <StatCard label="Promedio" :value="`${stats.promedio_calificaciones} / 100`" color="green" />
            </div>

            <!-- 2. Datos del estudiante -->
            <section class="card profile">
                <span class="profile__avatar">
                    <img v-if="fotoOk" :src="estudiante.foto_url" :alt="estudiante.nombre_completo" @error="fotoOk = false" />
                    <template v-else>{{ iniciales }}</template>
                </span>
                <div class="profile__body">
                    <div class="profile__top">
                        <h2 class="profile__name">{{ estudiante.nombre_completo }}</h2>
                        <a-tag :bordered="false" :color="estudiante.plan_activo ? 'green' : 'default'">
                            {{ estudiante.plan_activo ? 'Plan Premium activo' : 'Sin plan' }}
                        </a-tag>
                    </div>
                    <div class="facts">
                        <div v-for="f in facts" :key="f.label" class="fact" :class="{ 'fact--ancho': f.ancho }">
                            <span class="fact__label">{{ f.label }}</span>
                            <span class="fact__value">{{ f.value }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 3. Progreso + Estudio | Documentos -->
            <div class="bento__row">
                <div class="bento__col">
                    <section class="card">
                        <div class="card__title">Progreso de videos</div>
                        <div class="vp">
                            <a-progress type="circle" :percent="stats.porcentaje_progreso" :size="128" :stroke-width="9"
                                :stroke-color="{ '0%': '#1664db', '100%': '#1751df' }" />
                            <div class="vp__side">
                                <div class="vp__bar">
                                    <span class="vp__bar-seg vp--done" :style="{ width: seg.done + '%' }"></span>
                                    <span class="vp__bar-seg vp--prog" :style="{ width: seg.prog + '%' }"></span>
                                    <span class="vp__bar-seg vp--none" :style="{ width: seg.none + '%' }"></span>
                                </div>
                                <ul class="vp__legend">
                                    <li><i class="vp--done"></i><b>{{ stats.videos_completos }}</b> completados</li>
                                    <li><i class="vp--prog"></i><b>{{ stats.videos_en_progreso }}</b> en progreso</li>
                                    <li><i class="vp--none"></i><b>{{ videosSinVer }}</b> sin ver</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <section class="card">
                        <div class="card__title">Estudio · últimos 7 días</div>
                        <div v-if="hayEstudio" class="week">
                            <div v-for="(d, i) in estudioDiario" :key="i" class="week__day">
                                <div class="week__bar-wrap">
                                    <span class="week__bar" :class="{ 'is-zero': !d.horas }"
                                        :style="{ height: Math.max(4, d.horas / maxHoras * 100) + '%' }"></span>
                                </div>
                                <span class="week__lbl">{{ d.dia.slice(0, 3) }}</span>
                                <span class="week__val">{{ d.horas }}h</span>
                            </div>
                        </div>
                        <div v-else class="week-empty">
                            <BarChartOutlined />
                            <span>Sin sesiones de estudio esta semana</span>
                        </div>
                    </section>
                </div>

                <!-- Documentos compactos con botones anchos -->
                <section class="card card--docs">
                    <div class="card__title">
                        <FilePdfOutlined /> Documentos
                        <span class="card__meta">
                            {{ totalAprobados }} / {{ TIPOS.length }} aprobados
                        </span>
                    </div>

                    <a-spin :spinning="cargandoDocs">
                        <div v-if="!totalDocumentos" class="docs-empty">
                            <FilePdfOutlined />
                            <span>Aún no ha subido documentos</span>
                        </div>

                        <div v-else class="docs-list">
                            <div v-for="t in TIPOS" :key="t.value"
                                class="doc-row"
                                :class="documentos[t.value] ? `doc-row--${estatusMeta(documentos[t.value].estatus).css}` : 'doc-row--none'">
                                <span class="doc-row__ic">
                                    <FilePdfOutlined />
                                </span>

                                <div class="doc-row__body">
                                    <div class="doc-row__top">
                                        <b class="doc-row__label">{{ t.short }}</b>
                                        <a-tag v-if="documentos[t.value]"
                                            :color="estatusMeta(documentos[t.value].estatus).color"
                                            class="doc-row__tag">
                                            <component :is="estatusMeta(documentos[t.value].estatus).icon" />
                                            {{ estatusMeta(documentos[t.value].estatus).label }}
                                        </a-tag>
                                        <span v-else class="doc-row__tag doc-row__tag--none">
                                            <ClockCircleOutlined /> Sin subir
                                        </span>
                                    </div>

                                    <template v-if="documentos[t.value]">
                                        <span class="doc-row__name" :title="documentos[t.value].nombre_original">
                                            {{ documentos[t.value].nombre_original || 'documento.pdf' }}
                                        </span>
                                        <span class="doc-row__peso">{{ documentos[t.value].peso_formateado }}</span>

                                        <p v-if="documentos[t.value].observaciones" class="doc-row__obs" :title="documentos[t.value].observaciones">
                                            <ExclamationCircleOutlined />
                                            {{ documentos[t.value].observaciones }}
                                        </p>

                                        <!-- 👇 Nota contextual según el estado -->
                                        <p v-if="documentos[t.value].estatus === 'rechazado'" class="doc-row__locked">
                                            <ClockCircleOutlined />
                                            Rechazado. Esperando que el estudiante suba uno nuevo.
                                        </p>
                                    </template>
                                    <span v-else class="doc-row__empty">Aún no subido</span>
                                </div>

                                <!-- Acciones ANCHAS en una línea horizontal -->
                                <div v-if="documentos[t.value]" class="doc-row__actions">
                                    <button class="btn-action btn-action--ver" title="Ver PDF" @click="verDocumento(documentos[t.value])">
                                        <EyeOutlined />
                                        <span>Ver</span>
                                    </button>
                                    <button class="btn-action btn-action--dl" title="Descargar" @click="descargarDocumento(documentos[t.value])">
                                        <DownloadOutlined />
                                        <span>Descargar</span>
                                    </button>
                                    <button class="btn-action btn-action--ok"
                                        :title="estaAprobadoFinal(documentos[t.value]) ? 'Documento aprobado y final' : 'Aprobar'"
                                        :disabled="estaBloqueado(documentos[t.value]) || aprobando[documentos[t.value].id]"
                                        @click="aprobarDocumento(documentos[t.value])">
                                        <CheckOutlined />
                                        <span>Aprobar</span>
                                    </button>
                                    <button class="btn-action btn-action--rej"
                                        :title="estaAprobadoFinal(documentos[t.value]) ? 'Documento aprobado y final' : 'Rechazar'"
                                        :disabled="estaBloqueado(documentos[t.value])"
                                        @click="abrirRechazo(documentos[t.value])">
                                        <CloseOutlined />
                                        <span>Rechazar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </a-spin>
                </section>
            </div>

            <!-- 4. Exámenes realizados -->
            <section class="card card--flush">
                <div class="card__title">
                    Exámenes realizados
                    <span class="card__meta">{{ stats.total_examenes }} en total · promedio {{ stats.promedio_calificaciones }}/100</span>
                </div>
                <a-table v-if="examenes.length" :columns="examColumns" :data-source="examenes" :pagination="examenes.length > 10 ? { pageSize: 10, size: 'small' } : false" row-key="id" size="middle" :scroll="{ x: 640 }">
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'tipo'">
                            <span class="exam-tipo" :style="{ color: tipoMeta(record.tipo).color, background: tipoMeta(record.tipo).bg }">
                                {{ tipoMeta(record.tipo).label }}
                            </span>
                        </template>
                        <template v-else-if="column.key === 'aciertos'">
                            <span v-if="record.total_preguntas"><b>{{ record.aciertos }}</b> / {{ record.total_preguntas }}</span>
                            <span v-else>—</span>
                        </template>
                        <template v-else-if="column.key === 'accion'">
                            <a-tag v-if="record.corregido" color="gold" :bordered="false">Corregido</a-tag>
                            <Link :href="route('admin.examenes-realizados.show', record.id)">
                                <a-button size="small">Ver / corregir</a-button>
                            </Link>
                        </template>
                        <template v-else-if="column.key === 'calif'">
                            <div class="exam-score">
                                <span class="exam-score__bar">
                                    <span :style="{ width: Math.min(100, record.calificacion) + '%', background: califColor(record.calificacion) }"></span>
                                </span>
                                <b :style="{ color: califColor(record.calificacion) }">{{ record.calificacion }}</b>
                            </div>
                        </template>
                    </template>
                </a-table>
                <div v-else class="exam-empty">
                    <FileDoneOutlined />
                    <span>Este estudiante aún no ha presentado exámenes</span>
                </div>
            </section>
        </div>

        <!-- Modal restablecer contraseña -->
        <a-modal v-model:open="pwdOpen" title="Restablecer contraseña" class="sains-modal" :confirm-loading="pwd.processing" ok-text="Guardar" @ok="resetPwd">
            <a-form layout="vertical">
                <a-form-item label="Nueva contraseña" required :validate-status="pwd.errors.new_password ? 'error' : undefined" :help="pwd.errors.new_password">
                    <a-input-password v-model:value="pwd.new_password" />
                </a-form-item>
                <a-form-item label="Confirmar nueva contraseña" required>
                    <a-input-password v-model:value="pwd.new_password_confirmation" />
                </a-form-item>
            </a-form>
        </a-modal>

        <!-- Modal visor PDF (ADMIN) -->
        <ModalDocumentosAdmin
            v-model:open="pdfModal"
            :documento="pdfActivo"
            :tipo-label="tipoLabelActivo"
            :estudiante-id="estudiante.id"
            @close="cerrarPdfModal"
        />

        <!-- Modal rechazo -->
        <a-modal
            v-model:open="rechazoOpen"
            :footer="null"
            width="480px"
            centered
            class="reject-modal"
            destroy-on-close
        >
            <div class="reject">
                <span class="reject__ic"><CloseCircleOutlined /></span>
                <h3 class="reject__title">Rechazar documento</h3>
                <p class="reject__sub">
                    Vas a rechazar <b>{{ tipoRechazoLabel }}</b>. El estudiante verá tu motivo y podrá subirlo de nuevo.
                </p>

                <div class="reject__field">
                    <label>Motivo del rechazo <span>*</span></label>
                    <a-textarea
                        v-model:value="rechazo.motivo"
                        :rows="4"
                        placeholder="Ej. El archivo está borroso, no es legible, no corresponde al documento solicitado…"
                        show-count
                        :maxlength="500"
                    />
                </div>

                <div class="reject__actions">
                    <a-button @click="rechazoOpen = false" :disabled="rechazando">Cancelar</a-button>
                    <a-button
                        danger
                        type="primary"
                        :loading="rechazando"
                        @click="confirmarRechazo"
                    >
                        <template #icon><CloseOutlined /></template>
                        Rechazar documento
                    </a-button>
                </div>
            </div>
        </a-modal>
    </AdminLayout>
</template>

<style scoped>
.bento { display: flex; flex-direction: column; gap: 14px; }
.bento__stats { margin-bottom: 0 !important; }
.bento__row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    align-items: stretch;
}
.bento__col { display: flex; flex-direction: column; gap: 14px; }
@media (max-width: 1024px) { .bento__row { grid-template-columns: 1fr; } }

.card {
    background: #fff;
    border: 1px solid var(--sains-line);
    border-radius: 16px;
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
}
.card--flush { padding-bottom: 8px; }
.card--docs { padding: 18px 18px 14px; }
.card__title {
    display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap;
    font-size: 14px; font-weight: 700; color: #0f172a;
    padding-bottom: 12px; margin-bottom: 14px;
    border-bottom: 1px solid var(--sains-line);
}
.card--flush .card__title { margin-bottom: 4px; }
.card--docs .card__title { margin-bottom: 12px; padding-bottom: 10px; gap: 8px; }
.card--docs .card__title :deep(.anticon) { color: #dc2626; }
.card__meta { font-size: 12px; font-weight: 500; color: var(--sains-faint); margin-left: auto; }

/* ---- Cabecera de perfil ---- */
.profile { flex-direction: row; align-items: flex-start; gap: 18px; }
@media (max-width: 640px) {
    .profile { flex-direction: column; gap: 14px; }
    .profile__avatar { width: 56px; height: 56px; }
    .fact__value { white-space: normal; }
}
.profile__avatar {
    width: 64px; height: 64px; flex: none; border-radius: 18px; overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 22px; color: #fff;
    background: linear-gradient(135deg, #1851ad, #1751df);
}
.profile__avatar img { width: 100%; height: 100%; object-fit: cover; }
.profile__body { flex: 1; min-width: 0; }
.profile__top { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
.profile__name { font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -.02em; }

.facts {
    display: flex; flex-wrap: wrap; gap: 1px;
    background: var(--sains-line); border: 1px solid var(--sains-line);
    border-radius: 12px; overflow: hidden;
}
.fact--ancho { flex-basis: 100% !important; }
.fact { flex: 1 1 160px; display: flex; flex-direction: column; gap: 2px; padding: 9px 13px; background: #fff; }
.fact__label { font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; color: var(--sains-faint); font-weight: 600; }
.fact__value { font-size: 13px; font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ---- Progreso de videos ---- */
.vp { display: flex; align-items: center; gap: 26px; flex: 1; padding: 6px 0; }
.vp__side { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 14px; }
.vp__bar { display: flex; height: 10px; border-radius: 999px; overflow: hidden; background: #eef2f7; }
.vp__bar-seg { height: 100%; transition: width .5s cubic-bezier(.16, 1, .3, 1); }
.vp__legend { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.vp__legend li { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: var(--sains-muted); }
.vp__legend b { color: #0f172a; font-weight: 800; font-size: 14px; }
.vp__legend i { width: 9px; height: 9px; border-radius: 3px; flex: none; }
.vp--done { background: #1664db; }
.vp--prog { background: #f59e0b; }
.vp--none { background: #cbd5e1; }
@media (max-width: 480px) { .vp { flex-direction: column; gap: 18px; } }

/* ---- Gráfica semanal ---- */
.week { display: flex; align-items: flex-end; gap: 10px; flex: 1; min-height: 156px; }
.week__day { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; }
.week__bar-wrap { flex: 1; width: 100%; display: flex; align-items: flex-end; }
.week__bar {
    width: 100%; border-radius: 6px 6px 0 0; min-height: 4px;
    background: linear-gradient(180deg, #1664db, #1550d0);
    transition: height .4s cubic-bezier(.16, 1, .3, 1);
}
.week__bar.is-zero { background: #e2e8f0; }
.week__lbl { font-size: 11px; color: var(--sains-muted); margin-top: 8px; }
.week__val { font-size: 11px; font-weight: 700; color: #475569; }
.week-empty {
    flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
    min-height: 130px; color: var(--sains-faint);
}
.week-empty :deep(.anticon) { font-size: 30px; }
.week-empty span { font-size: 13px; }

/* ===================== DOCUMENTOS compactos ===================== */
.docs-list { display: flex; flex-direction: column; gap: 8px; flex: 1; }

.docs-empty {
    flex: 1;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 8px; color: var(--sains-faint);
    min-height: 180px;
}
.docs-empty :deep(.anticon) { font-size: 32px; }
.docs-empty span { font-size: 13px; }

.doc-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    border: 1.5px solid var(--sains-line);
    background: #fafbfd;
    transition: border-color .2s ease, background .2s ease, transform .15s ease;
}
.doc-row:hover { border-color: #c5d5e9; transform: translateY(-1px); }
.doc-row--ok  { border-color: #bbf7d0; background: #f0fdf4; }
.doc-row--ok:hover { border-color: #86efac; }
.doc-row--rej { border-color: #fecaca; background: #fef2f2; }
.doc-row--rej:hover { border-color: #fca5a5; }
.doc-row--rev { border-color: #fde68a; background: #fffbeb; }
.doc-row--rev:hover { border-color: #fcd34d; }
.doc-row--none { opacity: .85; }

.doc-row__ic {
    width: 34px; height: 34px; flex: none;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 10px; font-size: 16px;
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #dc2626;
}
.doc-row--ok .doc-row__ic { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #059669; }
.doc-row--rej .doc-row__ic { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626; }
.doc-row--rev .doc-row__ic { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706; }

.doc-row__body {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.doc-row__top { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.doc-row__label { font-size: 13px; font-weight: 700; color: #0f172a; }
.doc-row__tag {
    margin: 0;
    font-weight: 700;
    font-size: 10.5px;
    display: inline-flex !important;
    align-items: center;
    gap: 4px;
    line-height: 1;
    padding: 2px 8px;
    border-radius: 999px;
}
.doc-row__tag :deep(.anticon) { font-size: 10px; }
.doc-row__tag--none {
    color: #64748b;
    background: #f1f5f9;
    padding: 2px 8px;
    border-radius: 999px;
    font-weight: 700;
    font-size: 10.5px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.doc-row__name { font-size: 11.5px; color: #475569; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.doc-row__peso { font-size: 10.5px; color: #94a3b8; }
.doc-row__obs {
    font-size: 11px;
    color: #b91c1c;
    background: #fff1f2;
    padding: 4px 8px;
    border-radius: 6px;
    margin: 4px 0 0;
    display: flex;
    align-items: center;
    gap: 5px;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.doc-row__obs :deep(.anticon) { flex: none; }

/* 👇 Nota de bloqueo temporal (rechazado) */
.doc-row__locked {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin: 4px 0 0;
    font-size: 10.5px;
    font-weight: 600;
    color: #92400e;
    background: #fef3c7;
    border: 1px solid #fde68a;
    padding: 3px 8px;
    border-radius: 6px;
    width: fit-content;
}
.doc-row__locked :deep(.anticon) { font-size: 11px; }

/* 👇 Nota de estado final (aprobado, ya no se puede rehacer) */
.doc-row__final {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin: 4px 0 0;
    font-size: 10.5px;
    font-weight: 700;
    color: #065f46;
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    padding: 3px 8px;
    border-radius: 6px;
    width: fit-content;
}
.doc-row__final :deep(.anticon) { font-size: 11px; }

.doc-row__empty { font-size: 11.5px; color: #94a3b8; font-style: italic; }

/* Botones ANCHOS en una sola línea horizontal */
.doc-row__actions {
    display: flex;
    flex-direction: row;
    gap: 6px;
    flex: none;
    align-items: center;
    flex-wrap: wrap;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 12px;
    min-height: 32px;
    border-radius: 9px;
    border: 1.5px solid transparent;
    cursor: pointer;
    font-size: 12px;
    font-weight: 650;
    transition: background .18s ease, color .18s ease, border-color .18s ease, transform .12s ease, box-shadow .18s ease;
    white-space: nowrap;
    line-height: 1;
}
.btn-action :deep(.anticon),
.btn-action svg { font-size: 13px; }
.btn-action:hover { transform: translateY(-1px); }
.btn-action:active { transform: scale(.97); }
.btn-action:disabled { opacity: .45; cursor: not-allowed; transform: none; }

.btn-action--ver {
    background: linear-gradient(135deg, #f2f6fa, #e1e9f4);
    color: #135fbc;
    border-color: #ccdaeb;
    box-shadow: 0 4px 10px -6px rgba(19, 95, 188, .4);
}
.btn-action--ver:hover {
    background: linear-gradient(135deg, #e1e9f4, #ccdaeb);
    border-color: #7ba5d9;
    color: #15509b;
    box-shadow: 0 8px 16px -8px rgba(19, 95, 188, .55);
}

.btn-action--dl {
    background: linear-gradient(135deg, #f2f5fa, #e6ecf6);
    color: #1550d0;
    border-color: #d1dbee;
    box-shadow: 0 4px 10px -6px rgba(21, 80, 208, .4);
}
.btn-action--dl:hover {
    background: linear-gradient(135deg, #e6ecf6, #d1dbee);
    border-color: #7e9dd9;
    color: #1d48a5;
    box-shadow: 0 8px 16px -8px rgba(21, 80, 208, .55);
}

.btn-action--ok {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    color: #16a34a;
    border-color: #bbf7d0;
    box-shadow: 0 4px 10px -6px rgba(22, 163, 74, .4);
}
.btn-action--ok:hover:not(:disabled) {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    border-color: #86efac;
    color: #15803d;
    box-shadow: 0 8px 16px -8px rgba(22, 163, 74, .55);
}

.btn-action--rej {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    color: #dc2626;
    border-color: #fecaca;
    box-shadow: 0 4px 10px -6px rgba(220, 38, 38, .4);
}
.btn-action--rej:hover:not(:disabled) {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    border-color: #fca5a5;
    color: #b91c1c;
    box-shadow: 0 8px 16px -8px rgba(220, 38, 38, .55);
}

/* ---- Exámenes ---- */
.exam-tipo { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.exam-score { display: flex; align-items: center; gap: 12px; max-width: 240px; }
.exam-score__bar { flex: 1; height: 8px; border-radius: 999px; background: #eef2f7; overflow: hidden; }
.exam-score__bar span { display: block; height: 100%; border-radius: 999px; transition: width .4s ease; }
.exam-score b { font-size: 14px; font-weight: 800; min-width: 26px; text-align: right; }
.exam-empty {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    padding: 40px 20px; color: var(--sains-faint);
}
.exam-empty :deep(.anticon) { font-size: 30px; }
.exam-empty span { font-size: 13px; }

:deep(.ant-table-wrapper) { flex: 1; }
:deep(.ant-table) { background: transparent; }
:deep(.ant-table-thead > tr > th) { background: #f8fafc; font-size: 11.5px; text-transform: uppercase; letter-spacing: .03em; color: var(--sains-faint); }

/* ---- Botón Exportar PDF (cabecera) ---- */
.btn-export-pdf {
    background: linear-gradient(135deg, #fef2f2, #fee2e2) !important;
    border-color: #fecaca !important;
    color: #dc2626 !important;
    font-weight: 650;
    box-shadow: 0 4px 10px -6px rgba(220, 38, 38, .4);
    transition: background .18s ease, color .18s ease, border-color .18s ease, transform .12s ease, box-shadow .18s ease;
}
.btn-export-pdf:hover {
    background: linear-gradient(135deg, #fee2e2, #fecaca) !important;
    border-color: #fca5a5 !important;
    color: #b91c1c !important;
    transform: translateY(-1px);
    box-shadow: 0 8px 16px -8px rgba(220, 38, 38, .55);
}
.btn-export-pdf:active { transform: scale(.97); }

/* ===================== Modal Rechazo ===================== */
:deep(.reject-modal .ant-modal-content) {
    border-radius: 20px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 40px 100px -30px rgba(220, 38, 38, .35);
}
:deep(.reject-modal .ant-modal-body) { padding: 0; }
:deep(.reject-modal .ant-modal-close) {
    top: 14px; right: 14px;
    width: 34px; height: 34px;
    border-radius: 50%;
    color: #94a3b8;
    transition: background .2s ease, color .2s ease, transform .3s ease;
}
:deep(.reject-modal .ant-modal-close:hover) {
    background: #fee2e2;
    color: #dc2626;
    transform: rotate(90deg);
}

.reject { padding: 26px 26px 22px; text-align: center; }
.reject__ic {
    width: 58px; height: 58px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 18px;
    font-size: 28px; color: #fff;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: 0 14px 28px -12px rgba(220, 38, 38, .6);
    margin-bottom: 12px;
}
.reject__title { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 6px; letter-spacing: -.02em; }
.reject__sub { font-size: 13px; color: #64748b; line-height: 1.5; margin: 0 0 18px; }
.reject__sub b { color: #0f172a; }

.reject__field { text-align: left; margin-bottom: 18px; }
.reject__field label { display: block; font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 6px; }
.reject__field label span { color: #dc2626; }
.reject__field :deep(.ant-input) { border-radius: 12px; font-size: 13px; padding: 10px 12px; }
.reject__field :deep(.ant-input:focus),
.reject__field :deep(.ant-input-focused) {
    border-color: #fca5a5;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, .12);
}

.reject__actions { display: flex; justify-content: flex-end; gap: 8px; }
.reject__actions :deep(.ant-btn) { border-radius: 10px; font-weight: 600; }
.reject__actions :deep(.ant-btn-dangerous) {
    background: linear-gradient(135deg, #ef4444, #dc2626) !important;
    border-color: transparent !important;
    box-shadow: 0 12px 24px -10px rgba(220, 38, 38, .65);
}
.reject__actions :deep(.ant-btn-dangerous:hover) {
    filter: brightness(1.06);
    box-shadow: 0 16px 32px -10px rgba(220, 38, 38, .8);
}
</style>