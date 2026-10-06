<script setup>
import { computed, watch } from 'vue';
import { PhoneOutlined } from '@ant-design/icons-vue';
import { soloDigitos } from '@/lib/forms';

/**
 * Input de teléfono: sólo admite dígitos, con longitud máxima (10 por defecto).
 * Uso: <PhoneInput v-model:value="form.telefono" />
 */
const props = defineProps({
    value: { type: [String, Number], default: '' },
    max: { type: Number, default: 10 },
    placeholder: { type: String, default: '10 dígitos' },
    disabled: { type: Boolean, default: false },
    icon: { type: Boolean, default: true },
});
const emit = defineEmits(['update:value']);

// Normaliza el valor entrante (p. ej. números guardados con guiones/espacios).
watch(() => props.value, (v) => {
    const limpio = soloDigitos(v, props.max);
    if (limpio !== String(v ?? '')) emit('update:value', limpio);
}, { immediate: true });

const inner = computed({
    get: () => soloDigitos(props.value, props.max),
    set: (v) => emit('update:value', soloDigitos(v, props.max)),
});

function onKeydown(e) {
    // Permite teclas de control; bloquea cualquier carácter no numérico.
    if (e.ctrlKey || e.metaKey || e.altKey) return;
    if (e.key.length === 1 && !/[0-9]/.test(e.key)) e.preventDefault();
}
function onPaste(e) {
    e.preventDefault();
    const txt = (e.clipboardData || window.clipboardData).getData('text');
    inner.value = (inner.value + soloDigitos(txt, props.max)).slice(0, props.max);
}
</script>

<template>
    <a-input
        v-model:value="inner"
        :maxlength="max"
        :disabled="disabled"
        :placeholder="placeholder"
        inputmode="numeric"
        autocomplete="tel"
        @keydown="onKeydown"
        @paste="onPaste"
    >
        <template v-if="icon" #prefix><PhoneOutlined /></template>
    </a-input>
</template>
