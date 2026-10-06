<script setup>
import { computed, h } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: null },
    icon: { type: [Object, Function], default: null },
    back: { type: [String, Boolean], default: false }, // route url
});

const iconNode = computed(() => (props.icon ? h(props.icon) : null));
</script>

<template>
    <div class="sains-pagehead">
        <div class="sains-pagehead__left">
            <a
                v-if="back"
                :href="typeof back === 'string' ? back : undefined"
                class="sains-pagehead__icon"
                style="background: #eef2ff; color: #4f46e5; box-shadow: none; cursor: pointer"
                @click.prevent="$emit('back')"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <div v-else-if="iconNode" class="sains-pagehead__icon">
                <component :is="iconNode" />
            </div>
            <div>
                <h1 class="sains-pagehead__title">{{ title }}</h1>
                <p v-if="subtitle" class="sains-pagehead__sub">{{ subtitle }}</p>
            </div>
        </div>
        <div v-if="$slots.actions" class="sains-pagehead__actions">
            <slot name="actions" />
        </div>
    </div>
</template>
