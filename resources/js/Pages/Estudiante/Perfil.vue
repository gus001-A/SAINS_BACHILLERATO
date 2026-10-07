<script setup>
import { reactive, ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    UserOutlined, UploadOutlined, DeleteOutlined,
    FilePdfOutlined, CheckCircleOutlined, ClockCircleOutlined,
    CloseCircleOutlined, EyeOutlined, LockOutlined, PlayCircleFilled,
    ArrowRightOutlined, InfoCircleOutlined, CameraOutlined, MailOutlined,
    IdcardOutlined, HomeOutlined, KeyOutlined, SaveOutlined, CrownFilled,
    CloseOutlined,
} from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';
import PhoneInput from '@/Components/PhoneInput.vue';
import DateField from '@/Components/DateField.vue';
import ModalDocumentos from '@/Components/ModalDocumentos.vue';
import CamposIssfam from '@/Components/CamposIssfam.vue';
import { message, confirmAction } from '@/lib/notify';

const props = defineProps({
    estudianteData: { type: Object, required: true },
    universidades: { type: Array, default: () => [] },
    carreras: { type: Array, default: () => [] },
    entidades: { type: Array, default: () => [] },
    candado: { type: Object, required: true },
    videoDocumentos: {
        type: Object,
        default: () => ({
            tipo: 'video',
            src: '/videos/DOCUMENTOS_SAINS.mp4',
        }),
    },
});

const d = props.estudianteData;

const perfil = reactive({
    nombre: d.nombre ?? '', paterno: d.paterno ?? '', materno: d.materno ?? '',
    telefono: d.telefono ?? '', fecha_nacimiento: d.fecha_nacimiento ?? null,
    sexo: d.sexo ?? undefined, correo: d.correo ?? '',
    carrera_id: d.carrera_id ?? undefined, curp: d.curp ?? '', calle_numero: d.calle_numero ?? '',
    colonia: d.colonia ?? '', codigo_postal: d.codigo_postal ?? '', municipio: d.municipio ?? '',
    entidad_federativa: d.entidad_federativa ?? undefined,
});
const perfilErrors = ref({});
const perfilForm = perfil;
Object.defineProperty(perfilForm, 'errors', {
    enumerable: false,
    get: () => Object.fromEntries(Object.entries(perfilErrors.value).map(([k, v]) => [k, v?.[0]])),
});

const candado = ref({ ...props.candado });
const bloqueado = computed(() => candado.value.bloqueado);

const videoOpen = ref(false);
const tab = ref('datos');
const carreraNombre = computed(() => props.carreras.find((c) => c.value === perfil.carrera_id)?.label ?? null);
const iniciales = computed(() => `${perfil.nombre?.[0] ?? ''}${perfil.paterno?.[0] ?? ''}`.toUpperCase() || 'E');
const guardando = ref(false);

const pass = reactive({ password_actual: '', password_nueva: '', password_nueva_confirmation: '' });
const passErrors = ref({});
const cambiandoPass = ref(false);

const fotoUrl = ref(d.foto_url);
const subiendoFoto = ref(false);

const TIPOS = [
    { value: 'acta_nacimiento',        label: 'Acta de nacimiento',        hint: 'PDF · máx. 5 MB' },
    { value: 'curp',                    label: 'CURP',                       hint: 'PDF · máx. 5 MB' },
    { value: 'certificado_secundaria',  label: 'Certificado de secundaria',  hint: 'PDF · máx. 5 MB' },
    { value: 'ine',                     label: 'INE',                        hint: 'PDF · máx. 5 MB' },
];

const documentos = ref({});
const subiendoDoc = ref({});
const cargandoDocs = ref(true);

const pdfModal = ref(false);
const pdfActivo = ref(null);

function estatusMeta(estatus) {
    switch (estatus) {
        case 'aprobado':  return { color: 'success', icon: CheckCircleOutlined, label: 'Aprobado' };
        case 'rechazado': return { color: 'error',   icon: CloseCircleOutlined, label: 'Rechazado' };
        default:          return { color: 'warning', icon: ClockCircleOutlined, label: 'En revisión' };
    }
}

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

function guardarPerfil() {
    if (bloqueado.value) {
        message.warning(candado.value.motivo);
        return;
    }
    confirmAction({
        title: '¿Guardar tus datos?',
        content: `Si hay cambios, contarán como 1 de tus ${candado.value.max_total} cambios permitidos ` +
            `(te quedan ${candado.value.restantes_total} en total y ${candado.value.restantes_hoy} hoy).`,
        okText: 'Sí, guardar',
        onOk: enviarPerfil,
    });
}

async function enviarPerfil() {
    guardando.value = true;
    perfilErrors.value = {};
    try {
        const { data } = await axios.put(route('estudiante.perfil.actualizar'), { ...perfil });
        if (data.candado) candado.value = data.candado;
        if (data.success) {
            message.success(data.message || 'Perfil actualizado');
            router.reload({ only: ['auth'] });
        } else {
            message.error(data.message || 'No se pudo actualizar');
        }
    } catch (e) {
        if (e.response?.data?.candado) candado.value = e.response.data.candado;
        if (e.response?.status === 422) perfilErrors.value = e.response.data.errors || {};
        message.error(e.response?.data?.message || 'Error al actualizar el perfil');
    } finally {
        guardando.value = false;
    }
}

function continuar() {
    router.visit(route('estudiante.dashboard'));
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
        <!-- ==================== ENCABEZADO: foto + datos + candado ==================== -->
        <section class="pf-hero">
            <div class="pf-hero__foto">
                <a-avatar :src="fotoUrl || undefined" :size="88" class="pf-hero__avatar">
                    <template v-if="!fotoUrl">{{ iniciales }}</template>
                </a-avatar>
                <a-upload :before-upload="subirFoto" :show-upload-list="false" accept="image/*">
                    <a-tooltip title="Cambiar foto">
                        <button type="button" class="pf-hero__cam" :disabled="subiendoFoto" aria-label="Cambiar foto">
                            <CameraOutlined />
                        </button>
                    </a-tooltip>
                </a-upload>
            </div>

            <div class="pf-hero__info">
                <span class="pf-hero__eyebrow"><UserOutlined /> Mi cuenta</span>
                <h1 class="pf-hero__name">{{ perfil.nombre }} {{ perfil.paterno }} {{ perfil.materno }}</h1>
                <div class="pf-hero__meta">
                    <span><MailOutlined /> {{ perfil.correo }}</span>
                    <span v-if="carreraNombre" class="pf-chip"><i class="fas fa-graduation-cap"></i> {{ carreraNombre }}</span>
                    <span class="pf-chip" :class="{ 'pf-chip--gold': estudianteData.plan_activo }">
                        <CrownFilled v-if="estudianteData.plan_activo" /> {{ estudianteData.plan_activo ? 'Plan Premium' : 'Plan básico' }}
                    </span>
                    <a v-if="fotoUrl" class="pf-hero__quitar" @click="eliminarFoto">Quitar foto</a>
                </div>
            </div>

            <div class="pf-hero__side">
                <a-tooltip :title="bloqueado ? candado.motivo : `Puedes cambiar tus datos ${candado.max_dia} veces por día y ${candado.max_total} en total`">
                    <div class="pf-lock" :class="{ 'is-lock': bloqueado }">
                        <div class="pf-lock__top"><LockOutlined /> Candado de datos</div>
                        <div class="pf-lock__bar">
                            <span v-for="i in candado.max_total" :key="i" :class="{ used: i <= candado.total }"></span>
                        </div>
                        <small>{{ bloqueado ? 'Bloqueado' : `${candado.restantes_total} de ${candado.max_total} cambios · ${candado.restantes_hoy} hoy` }}</small>
                    </div>
                </a-tooltip>
                <a-button type="primary" size="large" class="pf-hero__cont" @click="continuar">
                    Continuar <ArrowRightOutlined />
                </a-button>
            </div>
        </section>

        <div class="pf-grid">
            <!-- ==================== DATOS (pestañas) ==================== -->
            <a-card :bordered="false" class="pf-card pf-card--datos" :body-style="{ padding: '6px 22px 18px' }">
                <a-tabs v-model:activeKey="tab" class="pf-tabs">
                    <a-tab-pane key="datos">
                        <template #tab><IdcardOutlined /> Datos personales</template>
                        <a-form layout="vertical" :disabled="bloqueado" class="pf-form">
                            <a-row :gutter="14">
                                <a-col :xs="24" :sm="8">
                                    <a-form-item label="Nombre(s)" :validate-status="err(perfilErrors,'nombre') ? 'error' : ''" :help="err(perfilErrors,'nombre')">
                                        <a-input v-model:value="perfil.nombre" />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="8">
                                    <a-form-item label="Apellido paterno">
                                        <a-input v-model:value="perfil.paterno" />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="8">
                                    <a-form-item label="Apellido materno">
                                        <a-input v-model:value="perfil.materno" />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="8">
                                    <a-form-item label="Teléfono">
                                        <PhoneInput v-model:value="perfil.telefono" />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="8">
                                    <a-form-item label="Fecha de nacimiento">
                                        <DateField v-model:value="perfil.fecha_nacimiento" limite="nacimiento" />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="8">
                                    <a-form-item label="Sexo">
                                        <a-select v-model:value="perfil.sexo" :options="[
                                            { value: 'M', label: 'Masculino' }, { value: 'F', label: 'Femenino' }]" />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="12">
                                    <a-form-item label="Correo electrónico" :validate-status="err(perfilErrors,'correo') ? 'error' : ''" :help="err(perfilErrors,'correo')">
                                        <a-input v-model:value="perfil.correo" />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="12">
                                    <a-form-item label="CURP" :validate-status="err(perfilErrors,'curp') ? 'error' : ''" :help="err(perfilErrors,'curp')">
                                        <a-input v-model:value="perfil.curp" :maxlength="18" class="pf-curp"
                                            @input="perfil.curp = String(perfil.curp ?? '').toUpperCase().replace(/\s/g, '')" />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </a-form>
                    </a-tab-pane>

                    <a-tab-pane key="domicilio">
                        <template #tab><HomeOutlined /> Carrera y domicilio</template>
                        <a-form layout="vertical" :disabled="bloqueado" class="pf-form pf-form--issfam">
                            <CamposIssfam :form="perfilForm" :carreras="carreras" :entidades="entidades"
                                :disabled="bloqueado" :mostrar-curp="false" />
                        </a-form>
                    </a-tab-pane>

                    <a-tab-pane key="password">
                        <template #tab><KeyOutlined /> Contraseña</template>
                        <a-form layout="vertical" class="pf-form">
                            <a-row :gutter="14">
                                <a-col :xs="24" :sm="8">
                                    <a-form-item label="Contraseña actual" :validate-status="err(passErrors,'password_actual') ? 'error' : ''" :help="err(passErrors,'password_actual')">
                                        <a-input-password v-model:value="pass.password_actual" />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="8">
                                    <a-form-item label="Nueva contraseña" :validate-status="err(passErrors,'password_nueva') ? 'error' : ''" :help="err(passErrors,'password_nueva')">
                                        <a-input-password v-model:value="pass.password_nueva" />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="8">
                                    <a-form-item label="Confirmar contraseña" :validate-status="err(passErrors,'password_nueva_confirmation') ? 'error' : ''" :help="err(passErrors,'password_nueva_confirmation')">
                                        <a-input-password v-model:value="pass.password_nueva_confirmation" />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                            <p class="pf-note">Mínimo 6 caracteres. Cambiar tu contraseña no cuenta para el candado de datos.</p>
                        </a-form>
                    </a-tab-pane>
                </a-tabs>

                <div class="pf-actions">
                    <template v-if="tab !== 'password'">
                        <span class="pf-actions__hint">
                            <LockOutlined /> {{ bloqueado ? candado.motivo : `Guardar cuenta como 1 de tus ${candado.max_total} cambios.` }}
                        </span>
                        <a-button type="primary" :loading="guardando" :disabled="bloqueado" @click="guardarPerfil">
                            <template #icon><SaveOutlined /></template>Guardar cambios
                        </a-button>
                    </template>
                    <template v-else>
                        <span></span>
                        <a-button type="primary" :loading="cambiandoPass" @click="cambiarPassword">
                            <template #icon><KeyOutlined /></template>Actualizar contraseña
                        </a-button>
                    </template>
                </div>
            </a-card>

            <!-- ==================== DOCUMENTOS ==================== -->
            <a-card :bordered="false" class="pf-card pf-card--docs" :body-style="{ padding: '16px 18px' }">
                <div class="pf-docs__head">
                    <h3><FilePdfOutlined /> Mis documentos</h3>
                    <span class="pf-docs__count" :class="{ ok: totalAprobados === TIPOS.length }">{{ totalAprobados }}/{{ TIPOS.length }} aprobados</span>
                </div>

                <button type="button" class="video-cta" @click="videoOpen = true">
                    <PlayCircleFilled class="video-cta__ic" />
                    <span><b>¿Cómo subo mis documentos?</b><small>Mira el video tutorial</small></span>
                </button>
                <div class="docs-legible">
                    <InfoCircleOutlined />
                    <span>Sube cada documento en <b>PDF</b> (máx. 5 MB), <b>claro y legible</b>: completo, sin cortes ni partes borrosas.</span>
                </div>

                <a-spin :spinning="cargandoDocs">
                    <ul class="pf-docs">
                        <li v-for="t in TIPOS" :key="t.value" class="pf-doc"
                            :class="{ 'is-ok': documentos[t.value]?.estatus === 'aprobado', 'is-rej': documentos[t.value]?.estatus === 'rechazado' }">
                            <span class="pf-doc__ic"><FilePdfOutlined /></span>
                            <div class="pf-doc__txt">
                                <b>{{ t.label }}</b>
                                <small v-if="!documentos[t.value]">Pendiente de subir</small>
                                <a-tooltip v-else-if="documentos[t.value].observaciones" :title="documentos[t.value].observaciones">
                                    <small class="pf-doc__obs">{{ estatusMeta(documentos[t.value].estatus).label }} · ver motivo</small>
                                </a-tooltip>
                                <small v-else :class="`st-${documentos[t.value].estatus}`">
                                    <component :is="estatusMeta(documentos[t.value].estatus).icon" />
                                    {{ estatusMeta(documentos[t.value].estatus).label }}
                                </small>
                            </div>
                            <div class="pf-doc__acc">
                                <a-upload v-if="!documentos[t.value]" :before-upload="(f) => subirDocumento(t.value, f)"
                                    :show-upload-list="false" accept="application/pdf,.pdf" :disabled="subiendoDoc[t.value]">
                                    <a-button size="small" type="primary" :loading="subiendoDoc[t.value]">
                                        <template #icon><UploadOutlined /></template>Subir
                                    </a-button>
                                </a-upload>
                                <template v-else>
                                    <a-tooltip title="Ver documento">
                                        <button type="button" class="doc-btn doc-btn--ver" @click="verDocumento(documentos[t.value])">
                                            <EyeOutlined />
                                        </button>
                                    </a-tooltip>
                                    <template v-if="!estaAprobadoFinal(documentos[t.value])">
                                        <a-upload :before-upload="(f) => subirDocumento(t.value, f)" :show-upload-list="false"
                                            accept="application/pdf,.pdf" :disabled="subiendoDoc[t.value]">
                                            <a-tooltip title="Reemplazar">
                                                <button type="button" class="doc-btn doc-btn--rep" :disabled="subiendoDoc[t.value]">
                                                    <UploadOutlined />
                                                </button>
                                            </a-tooltip>
                                        </a-upload>
                                        <a-tooltip title="Eliminar">
                                            <button type="button" class="doc-btn doc-btn--del" @click="eliminarDocumento(t.value, documentos[t.value])">
                                                <DeleteOutlined />
                                            </button>
                                        </a-tooltip>
                                    </template>
                                    <a-tooltip v-else title="Aprobado: ya no se puede modificar">
                                        <LockOutlined class="pf-doc__lock" />
                                    </a-tooltip>
                                </template>
                            </div>
                        </li>
                    </ul>
                </a-spin>
            </a-card>
        </div>

        <!-- ==================== MODAL DEL VIDEO ==================== -->
        <a-modal
            v-model:open="videoOpen"
            :footer="null"
            :closable="false"
            width="820px"
            centered
            destroy-on-close
            class="video-modal"
            :body-style="{ padding: 0 }"
        >
            <!-- Encabezado personalizado -->
            <div class="vm-head">
                <div class="vm-head__left">
                    <span class="vm-head__icon"><PlayCircleFilled /></span>
                    <div class="vm-head__txt">
                        <h3>Cómo subir tus documentos</h3>
                        <p>Video tutorial · menos de 2 minutos</p>
                    </div>
                </div>
                <button type="button" class="vm-close" @click="videoOpen = false" aria-label="Cerrar">
                    <CloseOutlined />
                </button>
            </div>

            <!-- Contenido -->
            <div class="vm-body">
                <div v-if="videoDocumentos" class="vm-player">
                    <iframe
                        v-if="videoDocumentos.tipo === 'iframe'"
                        :src="videoDocumentos.src"
                        title="Cómo subir tus documentos"
                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                        allowfullscreen
                    ></iframe>
                    <video v-else :src="videoDocumentos.src" controls autoplay playsinline></video>
                </div>
                <div v-else class="vm-empty">
                    <a-result
                        status="info"
                        title="El video estará disponible muy pronto"
                        sub-title="Mientras tanto: sube cada documento en PDF, completo y legible, en su recuadro correspondiente."
                    />
                </div>
            </div>

            <!-- Pie con tips rápidos -->
            <div class="vm-foot">
                <span class="vm-tip"><FilePdfOutlined /> Sube cada documento en PDF</span>
                <span class="vm-tip"><InfoCircleOutlined /> Máximo 5 MB por archivo</span>
                <span class="vm-tip"><CheckCircleOutlined /> Claro, completo y legible</span>
            </div>
        </a-modal>

        <ModalDocumentos
            v-model:open="pdfModal"
            :documento="pdfActivo"
            :tipo-label="tipoLabelActivo"
            @close="cerrarPdfModal"
        />
    </EstudianteLayout>
</template>

<style scoped>
/* ==================== Encabezado ==================== */
.pf-hero {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 22px;
    padding: 20px 26px;
    margin-bottom: 16px;
    border-radius: 20px;
    color: #fff;
    background:
        radial-gradient(circle at 92% -30%, rgba(245, 179, 1, .35), transparent 45%),
        linear-gradient(120deg, #0e2d66 0%, #1851ad 70%, #1d63c9 100%);
    box-shadow: 0 22px 44px -26px rgba(14, 45, 102, .7);
}
.pf-hero__foto { position: relative; flex: none; }
.pf-hero__avatar {
    border: 4px solid rgba(255, 255, 255, .9);
    background: linear-gradient(135deg, #f5b301, #d99a00);
    font-size: 30px; font-weight: 800; color: #fff;
    box-shadow: 0 10px 24px -10px rgba(0, 0, 0, .5);
}
.pf-hero__cam {
    position: absolute; right: -2px; bottom: -2px;
    width: 32px; height: 32px; border-radius: 50%;
    display: grid; place-items: center;
    border: 3px solid #1851ad; background: #f5b301; color: #0e2d66;
    font-size: 14px; cursor: pointer; transition: transform .15s ease;
}
.pf-hero__cam:hover { transform: scale(1.1); }
.pf-hero__info { flex: 1; min-width: 0; }
.pf-hero__eyebrow { font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; opacity: .8; }
.pf-hero__name { margin: 2px 0 6px; font-size: 1.55rem; font-weight: 800; color: #fff; line-height: 1.2; letter-spacing: -.01em; }
.pf-hero__meta { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 14px; font-size: 13px; color: rgba(255, 255, 255, .88); }
.pf-chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 3px 11px; border-radius: 999px; font-size: 12px; font-weight: 600;
    background: rgba(255, 255, 255, .15); border: 1px solid rgba(255, 255, 255, .22);
}
.pf-chip--gold { background: #f5b301; border-color: #f5b301; color: #0e2d66; }
.pf-hero__quitar { color: rgba(255, 255, 255, .7); font-size: 12px; text-decoration: underline; cursor: pointer; }
.pf-hero__quitar:hover { color: #fff; }
.pf-hero__side { flex: none; display: flex; align-items: center; gap: 14px; }
.pf-lock {
    padding: 10px 14px; border-radius: 14px; min-width: 210px;
    background: rgba(255, 255, 255, .12); border: 1px solid rgba(255, 255, 255, .2);
}
.pf-lock.is-lock { background: rgba(220, 38, 38, .25); border-color: rgba(254, 202, 202, .5); }
.pf-lock__top { font-size: 12px; font-weight: 700; display: flex; gap: 6px; align-items: center; }
.pf-lock__bar { display: flex; gap: 4px; margin: 7px 0 5px; }
.pf-lock__bar span { flex: 1; height: 6px; border-radius: 3px; background: rgba(255, 255, 255, .25); }
.pf-lock__bar span.used { background: #f5b301; }
.pf-lock small { font-size: 11.5px; opacity: .85; }
.pf-hero__cont { background: #fff !important; color: #0e2d66 !important; border: 0 !important; font-weight: 700; }

/* ==================== Rejilla ==================== */
.pf-grid { display: grid; grid-template-columns: minmax(0, 1fr) 380px; gap: 16px; align-items: start; }
.pf-card { border-radius: 18px; overflow: hidden; }

.pf-tabs :deep(.ant-tabs-nav) { margin-bottom: 10px; }
.pf-tabs :deep(.ant-tabs-tab) { font-weight: 600; }
.pf-form :deep(.ant-form-item) { margin-bottom: 12px; }
.pf-form :deep(.ant-form-item-label) { padding-bottom: 4px; }
.pf-form--issfam :deep(.ant-divider) { margin: 4px 0 10px; font-size: 13px; }
.pf-curp :deep(input), .pf-curp { text-transform: uppercase; letter-spacing: .04em; }
.pf-note { margin: 0; font-size: 12.5px; color: #64748b; }

.pf-actions {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding-top: 12px; margin-top: 2px; border-top: 1px solid var(--sains-line);
}
.pf-actions__hint { font-size: 12.5px; color: #64748b; display: inline-flex; gap: 6px; align-items: center; }

/* ==================== Documentos ==================== */
.pf-docs__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.pf-docs__head h3 { margin: 0; font-size: 15px; font-weight: 700; display: flex; gap: 8px; align-items: center; }
.pf-docs__count { font-size: 12px; font-weight: 700; padding: 2px 10px; border-radius: 999px; background: #eef3f9; color: var(--sains-primary); }
.pf-docs__count.ok { background: #dcfce7; color: #15803d; }

.video-cta {
    display: flex; align-items: center; gap: 12px; width: 100%;
    padding: 9px 12px; margin-bottom: 10px; border-radius: 12px; cursor: pointer; text-align: left;
    border: 1px solid #c5d5e9; background: linear-gradient(135deg, #eef3f9, #fff);
    transition: border-color .15s ease, box-shadow .15s ease;
}
.video-cta:hover { border-color: var(--sains-primary); box-shadow: 0 6px 16px -10px rgba(24, 81, 173, .5); }
.video-cta__ic { font-size: 30px; color: var(--sains-gold, #f5b301); }
.video-cta b { display: block; font-size: 13px; color: #0f172a; }
.video-cta small { font-size: 11.5px; color: #64748b; }
.docs-legible {
    display: flex; gap: 8px; align-items: flex-start;
    padding: 8px 10px; margin-bottom: 12px; border-radius: 10px;
    background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: 12px; line-height: 1.45;
}
.docs-legible :deep(.anticon) { margin-top: 2px; }

.pf-docs { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
.pf-doc {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 10px; border-radius: 12px; border: 1px solid #eef1f8; background: #fafbfd;
    overflow: hidden;
}
.pf-doc.is-ok { border-color: #bbf7d0; background: #f0fdf4; }
.pf-doc.is-rej { border-color: #fecaca; background: #fef2f2; }
.pf-doc__ic {
    flex: none; width: 34px; height: 34px; border-radius: 9px; display: grid; place-items: center;
    background: #fee2e2; color: #dc2626; font-size: 15px;
}
.pf-doc__txt { flex: 1; min-width: 0; display: flex; flex-direction: column; line-height: 1.25; }
.pf-doc__txt b { font-size: 13px; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pf-doc__txt small { font-size: 11.5px; color: #94a3b8; display: inline-flex; gap: 4px; align-items: center; }
.pf-doc__txt .st-aprobado { color: #15803d; }
.pf-doc__txt .st-pendiente, .pf-doc__txt .st-revision { color: #b45309; }
.pf-doc__obs { color: #b91c1c !important; text-decoration: underline dotted; cursor: help; }
.pf-doc__acc { flex: none; display: flex; gap: 6px; align-items: center; }
.pf-doc__lock { color: #15803d; font-size: 15px; padding: 0 6px; }

/* ==================== Botones de acción documento ==================== */
.doc-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; padding: 0;
    border-radius: 8px; border: 1px solid transparent;
    font-size: 14px; cursor: pointer;
    transition: background .15s ease, border-color .15s ease, color .15s ease, transform .1s ease, box-shadow .15s ease;
}
.doc-btn:active { transform: scale(.92); }
.doc-btn:disabled { opacity: .55; cursor: not-allowed; }

/* Ver (ojito) */
.doc-btn--ver {
    background: #e8f0fe; color: #1851ad; border-color: #cddffb;
}
.doc-btn--ver:hover {
    background: #1851ad; color: #fff; border-color: #1851ad;
    box-shadow: 0 4px 12px -4px rgba(24, 81, 173, .55);
}

/* Reemplazar */
.doc-btn--rep {
    background: #fff7e6; color: #d97706; border-color: #fde3b0;
}
.doc-btn--rep:hover {
    background: #d97706; color: #fff; border-color: #d97706;
    box-shadow: 0 4px 12px -4px rgba(217, 119, 6, .55);
}

/* Eliminar */
.doc-btn--del {
    background: #fef2f2; color: #dc2626; border-color: #fadcdc;
}
.doc-btn--del:hover {
    background: #dc2626; color: #fff; border-color: #dc2626;
    box-shadow: 0 4px 12px -4px rgba(220, 38, 38, .55);
}

/* ==================== Modal del video ==================== */
.video-modal :deep(.ant-modal-content) {
    padding: 0;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 30px 60px -20px rgba(14, 45, 102, .45);
}
.video-modal :deep(.ant-modal-body) {
    padding: 0;
}

/* Encabezado personalizado */
.vm-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 16px 18px;
    background: linear-gradient(120deg, #0e2d66 0%, #1851ad 70%, #1d63c9 100%);
    color: #fff;
}
.vm-head__left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.vm-head__icon {
    flex: none;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: #f5b301;
    color: #0e2d66;
    font-size: 22px;
    box-shadow: 0 6px 14px -6px rgba(245, 179, 1, .6);
}
.vm-head__txt { min-width: 0; }
.vm-head__txt h3 {
    margin: 0;
    font-size: 15.5px;
    font-weight: 700;
    color: #fff;
    line-height: 1.2;
}
.vm-head__txt p {
    margin: 2px 0 0;
    font-size: 12px;
    color: rgba(255, 255, 255, .75);
}
.vm-close {
    flex: none;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, .25);
    background: rgba(255, 255, 255, .12);
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: background .15s ease, transform .15s ease;
}
.vm-close:hover {
    background: rgba(255, 255, 255, .25);
    transform: rotate(90deg);
}

/* Reproductor */
.vm-body {
    background: #0b1220;
    padding: 0;
}
.vm-player {
    position: relative;
    aspect-ratio: 16 / 9;
    background: #000;
}
.vm-player iframe,
.vm-player video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
}
.vm-empty {
    padding: 20px;
    background: #fff;
}

/* Pie con tips */
.vm-foot {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 14px;
    padding: 12px 18px;
    background: #f8fafc;
    border-top: 1px solid #eef1f8;
}
.vm-tip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #475569;
    font-weight: 600;
}
.vm-tip :deep(.anticon) {
    color: #1851ad;
}

/* ==================== Responsive ==================== */
@media (max-width: 1100px) {
    .pf-grid { grid-template-columns: 1fr; }
}
@media (max-width: 760px) {
    .pf-hero { flex-wrap: wrap; padding: 18px; }
    .pf-hero__side { width: 100%; justify-content: space-between; }
    .pf-lock { flex: 1; min-width: 0; }

    .vm-head { padding: 12px 14px; }
    .vm-head__txt h3 { font-size: 14px; }
    .vm-head__txt p { font-size: 11px; }
    .vm-foot { padding: 10px 14px; }
}
</style>