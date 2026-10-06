<script setup>
import { computed } from 'vue';
import { maxHoy, fechaNacimientoValida, fechaNacimientoEstudianteValida } from '@/lib/forms';

/**
 * <a-date-picker> con formato español (DD/MM/YYYY) y límite de fecha.
 *  - modo "hoy" (default): no permite fechas futuras.
 *  - modo "nacimiento": no permite futuras ni anteriores a 1920.
 *  - modo "nacimiento-estudiante": igual que "nacimiento", pero exige al menos 15 años.
 *  - modo "libre": sin límite.
 * Uso: <DateField v-model:value="form.fecha" />
 */
const props = defineProps({
    value: { type: [String, Object], default: null },
    limite: { type: String, default: 'hoy' }, // hoy | nacimiento | nacimiento-estudiante | libre
    disabled: { type: Boolean, default: false },
    placeholder: { type: String, default: 'DD/MM/AAAA' },
    valueFormat: { type: String, default: 'YYYY-MM-DD' },
});
const emit = defineEmits(['update:value']);

const inner = computed({
    get: () => props.value,
    set: (v) => emit('update:value', v),
});

const disabledDate = computed(() => {
    if (props.limite === 'libre') return undefined;
    if (props.limite === 'nacimiento-estudiante') return fechaNacimientoEstudianteValida;
    if (props.limite === 'nacimiento') return fechaNacimientoValida;
    return maxHoy;
});
</script>

<template>
    <a-date-picker
        v-model:value="inner"
        :value-format="valueFormat"
        format="DD/MM/YYYY"
        :disabled="disabled"
        :disabled-date="disabledDate"
        :placeholder="placeholder"
        style="width: 100%"
    />
</template>
