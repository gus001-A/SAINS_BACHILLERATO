<script setup>
import { computed, h } from 'vue';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], default: '—' },
    hint: { type: String, default: null },
    icon: { type: [Object, Function], default: null },
    color: { type: String, default: 'indigo' }, // indigo|violet|green|pink|amber|slate|red
});

const display = computed(() =>
    typeof props.value === 'number' ? props.value.toLocaleString('es-MX') : props.value,
);
const iconNode = computed(() => (props.icon ? h(props.icon) : null));
</script>

<template>
    <div class="sains-stat" :class="[`sains-stat--${color}`]">
        <div class="sains-stat__row">
            <div style="min-width: 0">
                <div class="sains-stat__label">{{ label }}</div>
                <div class="sains-stat__value">{{ display }}</div>
                <div v-if="hint" class="sains-stat__hint">{{ hint }}</div>
            </div>
            <div class="sains-stat__icon" :class="`sains-stat__icon--${color}`">
                <component :is="iconNode" v-if="iconNode" />
                <span v-else class="sains-stat__pulse"></span>
            </div>
        </div>
    </div>
</template>
