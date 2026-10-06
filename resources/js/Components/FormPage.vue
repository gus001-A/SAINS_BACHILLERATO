<script setup>
import { SaveOutlined } from '@ant-design/icons-vue';

defineProps({
    submitLabel: { type: String, default: 'Guardar' },
    cancelLabel: { type: String, default: 'Cancelar' },
    processing: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    maxWidth: { type: String, default: '980px' },
});
defineEmits(['submit', 'cancel']);
</script>

<template>
    <div class="sains-form" :style="{ maxWidth }">
        <a-card :bordered="false">
            <slot />
            <div class="sains-form-actions">
                <a-button @click="$emit('cancel')">{{ cancelLabel }}</a-button>
                <a-button type="primary" :loading="processing" :disabled="disabled" @click="$emit('submit')">
                    <template #icon><slot name="submit-icon"><SaveOutlined /></slot></template>
                    {{ submitLabel }}
                </a-button>
            </div>
        </a-card>
    </div>
</template>
