<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    InboxOutlined, FilePdfOutlined, FileImageOutlined, DeleteOutlined,
} from '@ant-design/icons-vue';
import { message } from '@/lib/notify';

const props = defineProps({
    pagoId: { type: [Number, String], required: true },
    block: { type: Boolean, default: false },
    label: { type: String, default: 'Subir comprobante' },
});

const open = ref(false);
const previewUrl = ref(null);
const previewIsPdf = ref(false);

const form = useForm({
    pago_id: props.pagoId,
    comprobante: null,
    nota_usuario: '',
});

const fileMeta = computed(() => {
    if (!form.comprobante) return null;
    const kb = form.comprobante.size / 1024;
    return {
        name: form.comprobante.name,
        size: kb > 1024 ? `${(kb / 1024).toFixed(1)} MB` : `${Math.round(kb)} KB`,
    };
});

function limpiarPreview() {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;
    previewIsPdf.value = false;
}

function beforeUpload(file) {
    const okType = /\.(jpe?g|png|pdf)$/i.test(file.name);
    const okSize = file.size / 1024 / 1024 < 5;
    if (!okType) { message.error('Formato no permitido (JPG, PNG o PDF).'); return false; }
    if (!okSize) { message.error('El archivo supera los 5 MB.'); return false; }

    limpiarPreview();
    form.comprobante = file;
    previewIsPdf.value = /\.pdf$/i.test(file.name);
    previewUrl.value = URL.createObjectURL(file);
    return false;
}

function quitarArchivo() {
    form.comprobante = null;
    limpiarPreview();
}

function enviar() {
    if (!form.comprobante) {
        message.warning('Selecciona tu comprobante primero.');
        return;
    }
    form.post(route('estudiante.subir-comprobante'), {
        forceFormData: true,
        onSuccess: () => { open.value = false; quitarArchivo(); },
    });
}

function alCerrar() {
    quitarArchivo();
    form.nota_usuario = '';
    form.clearErrors();
}

onBeforeUnmount(limpiarPreview);
</script>

<template>
    <a-button type="primary" :block="block" @click="open = true">{{ label }}</a-button>

    <a-modal
        v-model:open="open"
        title="Subir comprobante de pago"
        class="sains-modal"
        :width="540"
        :confirm-loading="form.processing"
        ok-text="Enviar comprobante"
        cancel-text="Cancelar"
        :ok-button-props="{ disabled: !form.comprobante }"
        @ok="enviar"
        @cancel="alCerrar"
        @after-close="alCerrar"
    >
        <a-form layout="vertical">
            <a-form-item label="Comprobante" :validate-status="form.errors.comprobante ? 'error' : ''" :help="form.errors.comprobante">
                <a-upload-dragger
                    v-if="!form.comprobante"
                    :before-upload="beforeUpload"
                    :max-count="1"
                    :show-upload-list="false"
                    accept="image/png,image/jpeg,.pdf"
                >
                    <p class="ant-upload-drag-icon"><InboxOutlined /></p>
                    <p class="ant-upload-text">Haz clic o arrastra tu archivo aquí</p>
                    <p class="ant-upload-hint">JPG, PNG o PDF · máximo 5 MB</p>
                </a-upload-dragger>

                <div v-else class="cu-preview">
                    <div class="cu-preview__bar">
                        <span class="cu-preview__name">
                            <FilePdfOutlined v-if="previewIsPdf" />
                            <FileImageOutlined v-else />
                            {{ fileMeta?.name }} <small>· {{ fileMeta?.size }}</small>
                        </span>
                        <a-button size="small" type="text" danger @click="quitarArchivo">
                            <template #icon><DeleteOutlined /></template>Cambiar
                        </a-button>
                    </div>
                    <iframe v-if="previewIsPdf" :src="previewUrl" class="cu-preview__pdf" title="Vista previa"></iframe>
                    <img v-else :src="previewUrl" alt="Vista previa del comprobante" class="cu-preview__img" />
                </div>
            </a-form-item>

            <a-form-item label="Nota adicional (opcional)">
                <a-textarea v-model:value="form.nota_usuario" :rows="3" placeholder="Ej: transferencia realizada el…" />
            </a-form-item>
            <a-alert type="info" show-icon message="Revisa que el comprobante se vea completo y legible antes de enviarlo. Tu pago se valida en un plazo de 24-48 horas hábiles." />
        </a-form>
    </a-modal>
</template>

<style scoped>
.cu-preview { border: 1px solid var(--sains-line); border-radius: 12px; overflow: hidden; }
.cu-preview__bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 6px 6px 6px 12px; background: #f8fafc; border-bottom: 1px solid var(--sains-line);
}
.cu-preview__name { font-size: 12.5px; font-weight: 600; color: #475569; display: inline-flex; align-items: center; gap: 6px; }
.cu-preview__name .anticon { color: #6366f1; }
.cu-preview__name small { color: #94a3b8; font-weight: 500; }
.cu-preview__img { display: block; width: 100%; max-height: 300px; object-fit: contain; background: #0f172a; }
.cu-preview__pdf { display: block; width: 100%; height: 340px; border: 0; }
</style>
