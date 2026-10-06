<script setup>
import { reactive, ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    UserOutlined, UploadOutlined, DeleteOutlined,
    FilePdfOutlined, CheckCircleOutlined, ClockCircleOutlined,
    CloseCircleOutlined, EyeOutlined, LockOutlined,
} from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';
import PhoneInput from '@/Components/PhoneInput.vue';
import DateField from '@/Components/DateField.vue';
import ModalDocumentos from '@/Components/ModalDocumentos.vue';
import { message, confirmAction } from '@/lib/notify';

const props = defineProps({
    estudianteData: { type: Object, required: true },
    universidades: { type: Array, default: () => [] },
});

const d = props.estudianteData;

const perfil = reactive({
    nombre: d.nombre ?? '', paterno: d.paterno ?? '', materno: d.materno ?? '',
    telefono: d.telefono ?? '', fecha_nacimiento: d.fecha_nacimiento ?? null,
    sexo: d.sexo ?? undefined, correo: d.correo ?? '',
});
const perfilErrors = ref({});
const guardando = ref(false);

const pass = reactive({ password_actual: '', password_nueva: '', password_nueva_confirmation: '' });
const passErrors = ref({});
const cambiandoPass = ref(false);

const fotoUrl = ref(d.foto_url);
const subiendoFoto = ref(false);

/* =========================================================
   DOCUMENTOS
   ========================================================= */
const TIPOS = [
    { value: 'acta_nacimiento',        label: 'Acta de nacimiento',        hint: 'PDF · máx. 5 MB' },
    { value: 'curp',                    label: 'CURP',                       hint: 'PDF · máx. 5 MB' },
    { value: 'certificado_secundaria',  label: 'Certificado de secundaria',  hint: 'PDF · máx. 5 MB' },
    { value: 'ine',                     label: 'INE',                        hint: 'PDF · máx. 5 MB' },
];

const documentos = ref({});
const subiendoDoc = ref({});
const cargandoDocs = ref(true);

/* Estado del modal (controlado desde aquí y pasado al componente) */
const pdfModal = ref(false);
const pdfActivo = ref(null);

function estatusMeta(estatus) {
    switch (estatus) {
        case 'aprobado':  return { color: 'success', icon: CheckCircleOutlined, label: 'Aprobado' };
        case 'rechazado': return { color: 'error',   icon: CloseCircleOutlined, label: 'Rechazado' };
        default:          return { color: 'warning', icon: ClockCircleOutlined, label: 'En revisión' };
    }
}

/**
 * ✅ Regla de negocio para el ESTUDIANTE:
 *  - APROBADO → es final, ya no puede reemplazar ni eliminar.
 *  - RECHAZADO → puede reemplazar y eliminar para corregir.
 *  - PENDIENTE → puede reemplazar y eliminar (por si se equivocó al subir).
 */
function estaAprobadoFinal(doc) {
    return doc && doc.estatus === 'aprobado';
}

function puedeReemplazar(doc) {
    return !estaAprobadoFinal(doc);
}

function puedeEliminar(doc) {
    return !estaAprobadoFinal(doc);
}

async function cargarDocumentos() {
    cargandoDocs.value = true;
    try {
        const { data } = await axios.get(route('estudiante.documentos.index'));
        const mapa = {};
        TIPOS.forEach((t) => { mapa[t.value] = null; });
        (data.documentos || []).forEach((doc) => { mapa[doc.tipo] = doc; });
        documentos.value = mapa;
    } catch (e) {
        message.error(e.response?.data?.message || 'No se pudieron cargar tus documentos');
    } finally {
        cargandoDocs.value = false;
    }
}

function subirDocumento(tipo, file) {
    const docActual = documentos.value[tipo];

    // Guarda: no permitir reemplazar si ya está aprobado
    if (estaAprobadoFinal(docActual)) {
        message.warning('Este documento ya fue aprobado y no se puede reemplazar.');
        return false;
    }

    const esPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
    const pesoOk = file.size / 1024 / 1024 < 5;
    if (!esPdf) { message.error('El archivo debe ser un PDF.'); return false; }
    if (!pesoOk) { message.error('El PDF supera los 5 MB.'); return false; }

    subiendoDoc.value = { ...subiendoDoc.value, [tipo]: true };

    const fd = new FormData();
    fd.append('tipo', tipo);
    fd.append('archivo', file);

    axios.post(route('estudiante.documentos.store'), fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
    })
        .then(({ data }) => {
            if (data.ok) {
                documentos.value = { ...documentos.value, [tipo]: data.documento };
                message.success(data.mensaje || 'Documento subido correctamente');
            } else {
                message.error(data.mensaje || 'No se pudo subir el documento');
            }
        })
        .catch((e) => {
            message.error(e.response?.data?.mensaje || e.response?.data?.message || 'Error al subir el documento');
        })
        .finally(() => {
            subiendoDoc.value = { ...subiendoDoc.value, [tipo]: false };
        });

    return false;
}

function eliminarDocumento(tipo, doc) {
    if (estaAprobadoFinal(doc)) {
        message.warning('Este documento ya fue aprobado y no se puede eliminar.');
        return;
    }
    confirmAction({
        title: '¿Eliminar este documento?',
        content: 'Tendrás que volver a subirlo si lo necesitas.',
        okText: 'Sí, eliminar',
        danger: true,
        tone: 'delete',
        onOk: async () => {
            try {
                const { data } = await axios.delete(route('estudiante.documentos.destroy', doc.id));
                if (data.ok) {
                    documentos.value = { ...documentos.value, [tipo]: null };
                    message.success('Documento eliminado');
                } else {
                    message.error(data.mensaje || 'No se pudo eliminar');
                }
            } catch (e) {
                message.error(e.response?.data?.mensaje || 'Error al eliminar');
            }
        },
    });
}

function verDocumento(doc) {
    if (!doc) return;
    pdfActivo.value = doc;
    pdfModal.value = true;
}

function cerrarPdfModal() {
    pdfModal.value = false;
}

const totalAprobados = computed(() =>
    TIPOS.filter((t) => documentos.value[t.value]?.estatus === 'aprobado').length
);

const tipoLabelActivo = computed(() => {
    const t = TIPOS.find(x => x.value === pdfActivo.value?.tipo);
    return t?.label || 'Documento';
});

onMounted(cargarDocumentos);

/* =========================================================
   PERFIL / FOTO / PASSWORD
   ========================================================= */
async function guardarPerfil() {
    guardando.value = true;
    perfilErrors.value = {};
    try {
        const { data } = await axios.put(route('estudiante.perfil.actualizar'), perfil);
        if (data.success) {
            message.success(data.message || 'Perfil actualizado');
            router.reload({ only: ['auth'] });
        } else {
            message.error(data.message || 'No se pudo actualizar');
        }
    } catch (e) {
        if (e.response?.status === 422) perfilErrors.value = e.response.data.errors || {};
        message.error(e.response?.data?.message || 'Error al actualizar el perfil');
    } finally {
        guardando.value = false;
    }
}

async function cambiarPassword() {
    if (pass.password_nueva !== pass.password_nueva_confirmation) {
        passErrors.value = { password_nueva_confirmation: ['Las contraseñas no coinciden'] };
        return;
    }
    cambiandoPass.value = true;
    passErrors.value = {};
    try {
        const { data } = await axios.post(route('estudiante.perfil.cambiar-password'), pass);
        if (data.success) {
            message.success(data.message || 'Contraseña cambiada');
            pass.password_actual = pass.password_nueva = pass.password_nueva_confirmation = '';
        } else {
            message.error(data.message || 'No se pudo cambiar la contraseña');
        }
    } catch (e) {
        if (e.response?.status === 422) passErrors.value = e.response.data.errors || {};
        message.error(e.response?.data?.message || 'Error al cambiar la contraseña');
    } finally {
        cambiandoPass.value = false;
    }
}

async function subirFoto(file) {
    const okType = /^image\//.test(file.type);
    const okSize = file.size / 1024 / 1024 < 2;
    if (!okType) { message.error('Selecciona una imagen.'); return false; }
    if (!okSize) { message.error('La imagen supera los 2 MB.'); return false; }
    subiendoFoto.value = true;
    const fd = new FormData();
    fd.append('foto', file);
    try {
        const { data } = await axios.post(route('estudiante.subir.foto'), fd);
        if (data.success) {
            fotoUrl.value = data.foto_url;
            message.success('Foto actualizada');
            router.reload({ only: ['auth'] });
        } else {
            message.error(data.message || 'No se pudo subir la foto');
        }
    } catch (e) {
        message.error(e.response?.data?.message || 'Error al subir la foto');
    } finally {
        subiendoFoto.value = false;
    }
    return false;
}

function eliminarFoto() {
    confirmAction({
        title: '¿Eliminar tu foto de perfil?',
        content: 'Volverás a mostrar tus iniciales como avatar.',
        okText: 'Sí, eliminar',
        danger: true,
        tone: 'delete',
        onOk: async () => {
            try {
                const { data } = await axios.delete(route('estudiante.eliminar.foto'));
                if (data.success) {
                    fotoUrl.value = null;
                    message.success('Foto eliminada');
                    router.reload({ only: ['auth'] });
                } else {
                    message.error(data.message || 'No se pudo eliminar');
                }
            } catch (e) {
                message.error(e.response?.data?.message || 'Error al eliminar la foto');
            }
        },
    });
}

const err = (bag, k) => (bag && bag[k] ? bag[k][0] : '');
</script>

<template>
    <EstudianteLayout title="Mi perfil">
        <section class="sains-hero">
            <div class="sains-hero__grid">
                <div>
                    <span class="sains-hero__eyebrow"><UserOutlined /> Mi cuenta</span>
                    <h1 class="sains-hero__title">{{ perfil.nombre }} {{ perfil.paterno }}</h1>
                    <p class="sains-hero__sub">Administra tus datos personales, tu foto, tu contraseña y tus documentos.</p>
                </div>
            </div>
        </section>

        <a-row :gutter="[16, 16]">
            <!-- ==================== COLUMNA IZQUIERDA ==================== -->
            <a-col :xs="24" :md="8">
                <a-card :bordered="false" class="perfil-foto">
                    <div class="perfil-foto__cover"></div>
                    <div class="perfil-foto__avatar">
                        <a-avatar :src="fotoUrl" :size="96">
                            <template v-if="!fotoUrl" #icon><UserOutlined /></template>
                        </a-avatar>
                    </div>
                    <h3>{{ perfil.nombre }} {{ perfil.paterno }}</h3>
                    <p class="perfil-foto__mail">{{ perfil.correo }}</p>
                    <a-tag :color="estudianteData.plan_activo ? 'green' : 'default'" style="margin-bottom: 4px">
                        {{ estudianteData.plan_activo ? 'Plan Premium activo' : 'Plan básico' }}
                    </a-tag>
                    <a-space direction="vertical" style="width: 100%; margin-top: 14px">
                        <a-upload :before-upload="subirFoto" :show-upload-list="false" accept="image/*">
                            <a-button block :loading="subiendoFoto"><template #icon><UploadOutlined /></template>Cambiar foto</a-button>
                        </a-upload>
                        <a-button v-if="fotoUrl" block danger @click="eliminarFoto">
                            <template #icon><DeleteOutlined /></template>Quitar foto
                        </a-button>
                    </a-space>
                </a-card>

                <!-- Documentos -->
                <a-card :bordered="false" style="margin-top: 16px">
                    <template #title>
                        <span class="docs-title">
                            <FilePdfOutlined /> Mis documentos
                            <a-tag color="blue" style="margin-left: 8px">
                                {{ totalAprobados }} / {{ TIPOS.length }}
                            </a-tag>
                        </span>
                    </template>

                    <a-spin :spinning="cargandoDocs">
                        <p class="docs-hint">
                            Sube tus documentos en formato <b>PDF</b> (máximo 5 MB cada uno).
                            Todos inician en revisión.
                        </p>

                        <a-row :gutter="[12, 12]">
                            <a-col v-for="t in TIPOS" :key="t.value" :xs="24">
                                <div class="doc" :class="{ 'doc--ok': documentos[t.value]?.estatus === 'aprobado',
                                                          'doc--rej': documentos[t.value]?.estatus === 'rechazado' }">
                                    <div class="doc__head">
                                        <span class="doc__ic"><FilePdfOutlined /></span>
                                        <div class="doc__meta">
                                            <b>{{ t.label }}</b>
                                            <small>{{ t.hint }}</small>
                                        </div>
                                    </div>

                                    <!-- Sin documento aún -->
                                    <div v-if="!documentos[t.value]" class="doc__empty">
                                        <a-upload
                                            :before-upload="(f) => subirDocumento(t.value, f)"
                                            :show-upload-list="false"
                                            accept="application/pdf,.pdf"
                                            :disabled="subiendoDoc[t.value]"
                                        >
                                            <a-button block :loading="subiendoDoc[t.value]">
                                                <template #icon><UploadOutlined /></template>
                                                Subir PDF
                                            </a-button>
                                        </a-upload>
                                    </div>

                                    <!-- Con documento -->
                                    <div v-else class="doc__body">
                                        <div class="doc__row">
                                            <a-tag :color="estatusMeta(documentos[t.value].estatus).color">
                                                <component :is="estatusMeta(documentos[t.value].estatus).icon" />
                                                {{ estatusMeta(documentos[t.value].estatus).label }}
                                            </a-tag>
                                            <small class="doc__peso">{{ documentos[t.value].peso_formateado }}</small>
                                        </div>
                                        <p class="doc__name" :title="documentos[t.value].nombre_original">
                                            {{ documentos[t.value].nombre_original || 'documento.pdf' }}
                                        </p>
                                        <p v-if="documentos[t.value].observaciones" class="doc__obs">
                                            <b>Observaciones:</b> {{ documentos[t.value].observaciones }}
                                        </p>

                                        <!-- 👇 Nota si ya está aprobado (final) -->
                                        <p v-if="estaAprobadoFinal(documentos[t.value])" class="doc__final">
                                            <LockOutlined />
                                            Documento aprobado. Ya no se puede modificar.
                                        </p>

                                        <a-space direction="vertical" style="width: 100%; margin-top: 10px">
                                            <a-button
                                                block size="small"
                                                type="primary"
                                                class="doc__btn-ver"
                                                @click="verDocumento(documentos[t.value])"
                                            >
                                                <EyeOutlined class="doc__btn-ver-icon" />
                                                <span>Ver PDF</span>
                                            </a-button>

                                            <!-- Reemplazar: solo si NO está aprobado -->
                                            <a-upload
                                                :before-upload="(f) => subirDocumento(t.value, f)"
                                                :show-upload-list="false"
                                                accept="application/pdf,.pdf"
                                                :disabled="subiendoDoc[t.value] || !puedeReemplazar(documentos[t.value])"
                                            >
                                                <a-button
                                                    block size="small"
                                                    :loading="subiendoDoc[t.value]"
                                                    :disabled="!puedeReemplazar(documentos[t.value])"
                                                >
                                                    <template #icon><UploadOutlined /></template>
                                                    {{ estaAprobadoFinal(documentos[t.value]) ? 'Aprobado' : 'Reemplazar' }}
                                                </a-button>
                                            </a-upload>

                                            <!-- Eliminar: solo si NO está aprobado -->
                                            <a-button
                                                block size="small" danger
                                                :disabled="!puedeEliminar(documentos[t.value])"
                                                @click="eliminarDocumento(t.value, documentos[t.value])"
                                            >
                                                <template #icon><DeleteOutlined /></template>
                                                {{ estaAprobadoFinal(documentos[t.value]) ? 'Aprobado' : 'Eliminar' }}
                                            </a-button>
                                        </a-space>
                                    </div>
                                </div>
                            </a-col>
                        </a-row>
                    </a-spin>
                </a-card>
            </a-col>

            <!-- ==================== COLUMNA DERECHA ==================== -->
            <a-col :xs="24" :md="16">
                <a-card :bordered="false" title="Datos personales">
                    <a-form layout="vertical">
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="12">
                                <a-form-item label="Nombre(s)" :validate-status="err(perfilErrors,'nombre') ? 'error' : ''" :help="err(perfilErrors,'nombre')">
                                    <a-input v-model:value="perfil.nombre" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="12">
                                <a-form-item label="Apellido paterno">
                                    <a-input v-model:value="perfil.paterno" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="12">
                                <a-form-item label="Apellido materno">
                                    <a-input v-model:value="perfil.materno" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="12">
                                <a-form-item label="Teléfono">
                                    <PhoneInput v-model:value="perfil.telefono" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="12">
                                <a-form-item label="Fecha de nacimiento">
                                    <DateField v-model:value="perfil.fecha_nacimiento" limite="nacimiento" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="12">
                                <a-form-item label="Sexo">
                                    <a-select v-model:value="perfil.sexo" :options="[
                                        { value: 'M', label: 'Masculino' }, { value: 'F', label: 'Femenino' }]" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24">
                                <a-form-item label="Correo electrónico" :validate-status="err(perfilErrors,'correo') ? 'error' : ''" :help="err(perfilErrors,'correo')">
                                    <a-input v-model:value="perfil.correo" />
                                </a-form-item>
                            </a-col>
                        </a-row>
                        <div class="sains-section-actions">
                            <a-button type="primary" :loading="guardando" @click="guardarPerfil">Guardar cambios</a-button>
                        </div>
                    </a-form>
                </a-card>

                <a-card :bordered="false" title="Cambiar contraseña" style="margin-top: 16px">
                    <a-form layout="vertical">
                        <a-form-item label="Contraseña actual" :validate-status="err(passErrors,'password_actual') ? 'error' : ''" :help="err(passErrors,'password_actual')">
                            <a-input-password v-model:value="pass.password_actual" />
                        </a-form-item>
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="12">
                                <a-form-item label="Nueva contraseña" :validate-status="err(passErrors,'password_nueva') ? 'error' : ''" :help="err(passErrors,'password_nueva')">
                                    <a-input-password v-model:value="pass.password_nueva" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="12">
                                <a-form-item label="Confirmar contraseña" :validate-status="err(passErrors,'password_nueva_confirmation') ? 'error' : ''" :help="err(passErrors,'password_nueva_confirmation')">
                                    <a-input-password v-model:value="pass.password_nueva_confirmation" />
                                </a-form-item>
                            </a-col>
                        </a-row>
                        <div class="sains-section-actions">
                            <a-button :loading="cambiandoPass" @click="cambiarPassword">Actualizar contraseña</a-button>
                        </div>
                    </a-form>
                </a-card>
            </a-col>
        </a-row>

        <!-- ================= MODAL DOCUMENTOS (componente) ================= -->
        <ModalDocumentos
            v-model:open="pdfModal"
            :documento="pdfActivo"
            :tipo-label="tipoLabelActivo"
            @close="cerrarPdfModal"
        />
    </EstudianteLayout>
</template>

<style scoped>
.perfil-foto { text-align: center; overflow: hidden; position: relative; padding-top: 0; }
.perfil-foto :deep(.ant-card-body) { padding-top: 0; }
.perfil-foto__cover {
    height: 78px; margin: 0 -24px 0; background: linear-gradient(135deg, #4f46e5, #9333ea);
}
.perfil-foto__avatar {
    margin-top: -48px; display: inline-block; padding: 4px; border-radius: 50%; background: #fff;
    box-shadow: 0 6px 18px -8px rgba(15, 23, 42, .3);
}
.perfil-foto h3 { margin: 12px 0 2px; }
.perfil-foto__mail { color: #94a3b8; font-size: 12.5px; margin: 0 0 10px; word-break: break-all; }

/* ============ Documentos ============ */
.docs-title { display: inline-flex; align-items: center; gap: 6px; }
.docs-hint { color: #64748b; font-size: 12.5px; margin: 0 0 14px; line-height: 1.5; }

.doc {
    border: 1.5px solid #eef1f8;
    border-radius: 14px;
    padding: 12px 12px 10px;
    background: #fafbff;
    transition: border-color .2s ease, background .2s ease;
}
.doc--ok  { border-color: #bbf7d0; background: #f0fdf4; }
.doc--rej { border-color: #fecaca; background: #fef2f2; }

.doc__head { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.doc__ic {
    width: 34px; height: 34px; flex: none;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 9px; font-size: 16px;
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #dc2626;
}
.doc__meta { display: flex; flex-direction: column; line-height: 1.2; min-width: 0; }
.doc__meta b { font-size: 13px; color: #0f172a; }
.doc__meta small { color: #94a3b8; font-size: 11px; }

.doc__row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.doc__peso { color: #94a3b8; font-size: 11px; }
.doc__name {
    font-size: 12px; color: #334155; margin: 8px 0 4px;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.doc__obs {
    font-size: 11.5px; color: #b91c1c; background: #fff1f2;
    padding: 6px 8px; border-radius: 8px; margin: 4px 0 0;
}

/* 👇 Nota de estado final (aprobado) */
.doc__final {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin: 6px 0 0;
    font-size: 11px;
    font-weight: 700;
    color: #065f46;
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    padding: 4px 9px;
    border-radius: 7px;
    width: fit-content;
}
.doc__final :deep(.anticon) { font-size: 11px; }

.doc__btn-ver {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-weight: 600;
}
.doc__btn-ver-icon {
    display: inline-flex;
    align-items: center;
    font-size: 14px;
    line-height: 1;
    vertical-align: middle;
}
.doc__btn-ver span { line-height: 1; }
</style>