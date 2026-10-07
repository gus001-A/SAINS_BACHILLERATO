<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import {
    FilePdfOutlined,
    CheckCircleOutlined,
    ClockCircleOutlined,
    CloseCircleOutlined,
    DownloadOutlined,
    EyeOutlined,
    ExportOutlined,
} from '@ant-design/icons-vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    documento: { type: Object, default: null },
    tipoLabel: { type: String, default: 'Documento' },
    /** ID del USER (no del estudiante). El controlador admin resuelve la relación. */
    estudianteId: { type: [Number, String], required: true },
});

const emit = defineEmits(['update:open', 'close']);

const animando = ref(false);

function estatusMeta(estatus) {
    switch (estatus) {
        case 'aprobado': return { color: 'success', icon: CheckCircleOutlined, label: 'Aprobado' };
        case 'rechazado': return { color: 'error', icon: CloseCircleOutlined, label: 'Rechazado' };
        default: return { color: 'warning', icon: ClockCircleOutlined, label: 'En revisión' };
    }
}

const meta = computed(() => estatusMeta(props.documento?.estatus));

const urlVer = computed(() =>
    props.documento
        ? route('admin.estudiantes.documentos.ver', [props.estudianteId, props.documento.id])
        : '#'
);

const urlDescargar = computed(() =>
    props.documento
        ? route('admin.estudiantes.documentos.descargar', [props.estudianteId, props.documento.id])
        : '#'
);

/* Animación de entrada (solo al abrir, no al cerrar) */
watch(() => props.open, (val) => {
    if (val) {
        animando.value = false;
        nextTick(() => {
            animando.value = true;
            setTimeout(() => { animando.value = false; }, 450);
        });
    }
});

function cerrar() {
    emit('update:open', false);
    emit('close');
}

function descargar() {
    if (!props.documento) return;
    window.open(urlDescargar.value, '_blank');
}

function abrirEnNuevaPestana() {
    if (!props.documento) return;
    window.open(urlVer.value, '_blank');
}
</script>

<template>
    <a-modal :open="open" :footer="null" :closable="true" :mask-closable="true" :keyboard="true" width="1100px" centered
        destroy-on-close @cancel="cerrar" class="pdf-admin-modal" :class="{ 'pdf-admin-modal--animando': animando }">
        <!-- Header: icono + nombre + tag de estatus al lado -->
        <template #title>
            <div class="pdf-head">
                <span class="pdf-head__ic">
                    <FilePdfOutlined />
                </span>
                <div class="pdf-head__meta">
                    <div class="pdf-head__title-row">
                        <b>{{ documento?.nombre_original || 'Documento' }}</b>
                        <a-tag v-if="documento" :color="meta.color" class="pdf-head__tag">
                            <component :is="meta.icon" />
                            {{ meta.label }}
                        </a-tag>
                    </div>
                    <small>
                        {{ tipoLabel }}
                        <template v-if="documento?.peso_formateado"> · {{ documento.peso_formateado }}</template>
                    </small>
                </div>
            </div>
        </template>

        <!-- Cuerpo: visor -->
        <div class="pdf-viewer">
            <iframe v-if="documento" :src="urlVer" title="Visor de PDF" frameborder="0"></iframe>
        </div>

        <!-- Footer: acciones ANCHAS -->
        <div class="pdf-actions">
            <a-button class="pdf-actions__btn pdf-actions__btn--ghost" size="large" @click="abrirEnNuevaPestana">
                <template #icon>
                    <ExportOutlined />
                </template>
                Abrir en pestaña nueva
            </a-button>

            <a-space :size="10">
                <a-button class="pdf-actions__btn" size="large" @click="cerrar">
                    Cerrar
                </a-button>
                <a-button type="primary" size="large" class="pdf-actions__btn pdf-actions__btn--download"
                    @click="descargar">
                    <template #icon>
                        <DownloadOutlined />
                    </template>
                    Descargar PDF
                </a-button>
            </a-space>
        </div>
    </a-modal>
</template>

<style scoped>
/* ================= Modal ================= */
:deep(.pdf-admin-modal .ant-modal-content) {
    border-radius: 22px;
    overflow: hidden;
    padding: 0;
    box-shadow: 0 50px 120px -30px rgba(15, 23, 42, .55);
}

:deep(.pdf-admin-modal .ant-modal-header) {
    padding: 22px 68px 22px 26px;
    margin: 0;
    background: linear-gradient(135deg, #f8fafc, #eef3f9);
    border-bottom: 1px solid #e2e8f0;
}

:deep(.pdf-admin-modal .ant-modal-body) {
    padding: 22px 26px 24px;
    background: #f8fafc;
}

/* ================= Header ================= */
.pdf-head {
    display: flex;
    align-items: center;
    gap: 14px;
    width: 100%;
}

.pdf-head__ic {
    width: 48px;
    height: 48px;
    flex: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 22px;
    color: #fff;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: 0 12px 24px -10px rgba(220, 38, 38, .55);
}

.pdf-head__meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
    flex: 1;
}

.pdf-head__title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    min-width: 0;
}

.pdf-head__title-row b {
    font-size: 16px;
    color: #0f172a;
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

.pdf-head__meta small {
    color: #64748b;
    font-size: 12.5px;
}

.pdf-head__tag {
    margin: 0;
    font-weight: 700;
    font-size: 12px;
    display: inline-flex !important;
    align-items: center;
    gap: 5px;
    line-height: 1;
    padding: 3px 10px;
    border-radius: 999px;
}

.pdf-head__tag :deep(.anticon) {
    display: inline-flex;
    align-items: center;
    font-size: 12px;
}

/* ================= Visor ================= */
.pdf-viewer {
    position: relative;
    width: 100%;
    height: 72vh;
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
    border: 1px solid #e2e8f0;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .6), 0 16px 40px -24px rgba(15, 23, 42, .4);
}

.pdf-viewer iframe {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}

/* ================= Footer de acciones ================= */
.pdf-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 18px;
    flex-wrap: wrap;
}

.pdf-actions__btn {
    border-radius: 12px;
    font-weight: 650;
    min-width: 160px;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.pdf-actions__btn--ghost {
    background: #fff;
    border: 1.5px solid #cbd5e1;
    color: #475569;
    min-width: 220px;
}

.pdf-actions__btn--ghost:hover {
    border-color: #94a3b8;
    color: #1e293b;
    background: #f8fafc;
}

.pdf-actions__btn--download {
    background: linear-gradient(135deg, #1851ad, #1550d0) !important;
    border-color: transparent !important;
    color: #fff !important;
    box-shadow: 0 14px 28px -12px rgba(24, 81, 173, .75);
    min-width: 200px;
}

.pdf-actions__btn--download:hover {
    filter: brightness(1.05);
    box-shadow: 0 18px 36px -12px rgba(24, 81, 173, .9);
    color: #fff !important;
}

/* ================= X de cerrar ================= */
:deep(.pdf-admin-modal .ant-modal-close) {
    top: 18px;
    right: 18px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    color: #64748b;
    background: rgba(255, 255, 255, .6);
    transition: background .3s ease, color .3s ease, transform .45s cubic-bezier(.34, 1.56, .64, 1);
}

:deep(.pdf-admin-modal .ant-modal-close:hover) {
    background: #fee2e2;
    color: #dc2626;
    transform: rotate(180deg) scale(1.12);
}

:deep(.pdf-admin-modal .ant-modal-close:active) {
    transform: rotate(270deg) scale(.92);
}

:deep(.pdf-admin-modal .ant-modal-close-x) {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    font-size: 20px;
    font-weight: 700;
}

/* Animación SOLO al abrir */
:deep(.pdf-admin-modal--animando .ant-modal-content) {
    animation: pdf-admin-pop .38s cubic-bezier(.34, 1.56, .64, 1);
}

@keyframes pdf-admin-pop {
    0% {
        opacity: 0;
        transform: scale(.92) translateY(10px);
    }

    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

:deep(.pdf-admin-modal--animando .ant-modal-close) {
    animation: pdf-admin-x .45s cubic-bezier(.34, 1.56, .64, 1);
}

@keyframes pdf-admin-x {
    0% {
        transform: scale(.4) rotate(-90deg);
        opacity: 0;
    }

    60% {
        transform: scale(1.15) rotate(10deg);
        opacity: 1;
    }

    100% {
        transform: scale(1) rotate(0);
        opacity: 1;
    }
}

:deep(.pdf-admin-modal .ant-modal-zoom-enter-active),
:deep(.pdf-admin-modal .ant-modal-zoom-appear-active) {
    animation-duration: .35s;
}

:deep(.pdf-admin-modal .ant-modal-zoom-leave-active) {
    animation-duration: .22s;
}
</style>