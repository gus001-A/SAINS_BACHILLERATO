<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';
import {
    UserOutlined, CreditCardOutlined, FlagOutlined, CalendarOutlined,
    DollarOutlined, NumberOutlined, FileTextOutlined, PaperClipOutlined,
    CloudUploadOutlined, CheckCircleFilled, LockOutlined, FilePdfOutlined,
    DeleteOutlined,
} from '@ant-design/icons-vue';
import DateField from '@/Components/DateField.vue';
import { PRECIO_CURSO } from '@/lib/forms';
import { message } from '@/lib/notify';

const props = defineProps({
    form: { type: Object, required: true },
    estudiantes: { type: Array, required: true },
    modo: { type: String, default: 'create' },
    comprobanteActual: { type: String, default: null },
});

const estudianteOpts = props.estudiantes.map((e) => ({ value: e.id, label: e.label }));
const tipoOpts = ['Bancario', 'Oxxo', 'Transferencia'].map((t) => ({ value: t, label: t }));
const estatusOpts = [
    { value: 'pendiente', label: 'Pendiente' },
    { value: 'aprobado', label: 'Aprobado' },
    { value: 'rechazado', label: 'Rechazado' },
    { value: 'cancelado', label: 'Cancelado' },
];

const err = (f) => (props.form.errors[f] ? 'error' : undefined);
const money = (n) => `$ ${Number(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;

// El monto del curso es fijo y no editable.
if (props.modo === 'create') props.form.monto_pago = PRECIO_CURSO;

const fileInput = ref(null);
const previewUrl = ref(null);
const previewIsPdf = ref(false);
const fileName = computed(() => props.form.comprobante?.name ?? '');

function limpiarPreview() {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;
    previewIsPdf.value = false;
}
function onFile(e) {
    const f = e.target.files?.[0] ?? null;
    limpiarPreview();
    if (!f) { props.form.comprobante = null; return; }
    const okType = /\.(jpe?g|png|pdf)$/i.test(f.name);
    const okSize = f.size / 1024 / 1024 < 5;
    if (!okType) { message.error('Formato no permitido (JPG, PNG o PDF).'); clearFile(); return; }
    if (!okSize) { message.error('El archivo supera los 5 MB.'); clearFile(); return; }
    props.form.comprobante = f;
    previewIsPdf.value = /\.pdf$/i.test(f.name);
    previewUrl.value = URL.createObjectURL(f);
}
function clearFile() {
    props.form.comprobante = null;
    limpiarPreview();
    if (fileInput.value) fileInput.value.value = '';
}
onBeforeUnmount(limpiarPreview);
</script>

<template>
    <a-form layout="vertical" class="sains-form-inner">
        <a-divider orientation="left"><span class="fsec"><DollarOutlined />Datos del pago</span></a-divider>
        <a-row :gutter="16">
            <a-col :xs="24" :md="12">
                <a-form-item required :validate-status="err('alumno_pago')" :help="form.errors.alumno_pago">
                    <template #label><UserOutlined />Estudiante</template>
                    <a-select v-model:value="form.alumno_pago" show-search option-filter-prop="label" :options="estudianteOpts" placeholder="Busca al estudiante…" />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :md="6">
                <a-form-item required :validate-status="err('tipo_pago')" :help="form.errors.tipo_pago">
                    <template #label><CreditCardOutlined />Método de pago</template>
                    <a-select v-model:value="form.tipo_pago" :options="tipoOpts" placeholder="Selecciona…" />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :md="6">
                <a-form-item required :validate-status="err('estatus')" :help="form.errors.estatus">
                    <template #label><FlagOutlined />Estado</template>
                    <a-select v-model:value="form.estatus" :options="estatusOpts" />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :md="8">
                <a-form-item required :validate-status="err('fecha_pago')" :help="form.errors.fecha_pago">
                    <template #label><CalendarOutlined />Fecha de pago</template>
                    <DateField v-model:value="form.fecha_pago" />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :md="8">
                <a-form-item :validate-status="err('monto_pago')" :help="form.errors.monto_pago">
                    <template #label><LockOutlined />Monto (MXN)</template>
                    <a-input :value="money(form.monto_pago)" disabled readonly />
                    <div class="fixed-hint">El monto del curso es fijo ({{ money(PRECIO_CURSO) }}) y no se puede editar.</div>
                </a-form-item>
            </a-col>
            <a-col :xs="24" :md="8">
                <a-form-item :validate-status="err('referencia_pago')" :help="form.errors.referencia_pago">
                    <template #label><NumberOutlined />Referencia</template>
                    <a-input v-model:value="form.referencia_pago" placeholder="Folio o referencia bancaria" />
                </a-form-item>
            </a-col>
        </a-row>

        <a-divider orientation="left"><span class="fsec"><PaperClipOutlined />Comprobante y notas</span></a-divider>
        <a-row :gutter="16">
            <a-col :xs="24" :md="12">
                <a-form-item :validate-status="err('comprobante')" :help="form.errors.comprobante">
                    <template #label><PaperClipOutlined />Comprobante de pago</template>
                    <label class="file-drop" :class="{ 'is-set': !!form.comprobante }">
                        <span class="file-drop__icon">
                            <CheckCircleFilled v-if="form.comprobante" />
                            <CloudUploadOutlined v-else />
                        </span>
                        <span class="file-drop__title">{{ fileName || 'Haz clic para seleccionar un archivo' }}</span>
                        <span class="file-drop__hint">JPG, PNG o PDF · máximo 5 MB</span>
                        <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png,.pdf" @change="onFile" />
                    </label>

                    <div v-if="previewUrl" class="cmp-preview">
                        <div class="cmp-preview__head">
                            <span><FilePdfOutlined v-if="previewIsPdf" /><FileTextOutlined v-else /> Vista previa</span>
                            <a-button size="small" type="text" danger @click="clearFile">
                                <template #icon><DeleteOutlined /></template>Quitar
                            </a-button>
                        </div>
                        <iframe v-if="previewIsPdf" :src="previewUrl" class="cmp-preview__pdf" title="Vista previa del comprobante"></iframe>
                        <img v-else :src="previewUrl" alt="Vista previa del comprobante" class="cmp-preview__img" />
                    </div>

                    <div v-if="comprobanteActual && !form.comprobante" class="cmp-actual">
                        <a :href="comprobanteActual" target="_blank" rel="noopener">
                            <FileTextOutlined /> Ver comprobante actual
                        </a>
                        <a-checkbox v-model:checked="form.eliminar_comprobante">Eliminar comprobante actual</a-checkbox>
                    </div>
                </a-form-item>
            </a-col>
            <a-col :xs="24" :md="12">
                <a-form-item>
                    <template #label><FileTextOutlined />Nota</template>
                    <a-textarea v-model:value="form.nota_usuario" :rows="5" placeholder="Observaciones sobre el pago…" />
                </a-form-item>
            </a-col>
        </a-row>
    </a-form>
</template>

<style scoped>
.fixed-hint { font-size: 11.5px; color: #94a3b8; margin-top: 5px; }

.cmp-preview {
    margin-top: 12px; border: 1px solid var(--sains-line); border-radius: 12px; overflow: hidden;
    background: #0f172a;
}
.cmp-preview__head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 7px 8px 7px 12px; background: #f8fafc; border-bottom: 1px solid var(--sains-line);
    font-size: 12px; font-weight: 600; color: #475569;
}
.cmp-preview__head .anticon { color: #6366f1; margin-inline-end: 4px; }
.cmp-preview__img { display: block; width: 100%; max-height: 260px; object-fit: contain; background: #0f172a; }
.cmp-preview__pdf { display: block; width: 100%; height: 320px; border: 0; background: #fff; }

.cmp-actual {
    margin-top: 10px; display: flex; flex-direction: column; gap: 8px;
    padding: 10px 12px; border-radius: 10px; background: #f8fafc; border: 1px solid var(--sains-line);
    font-size: 13px;
}
.cmp-actual a { color: #4f46e5; font-weight: 550; }
</style>
