<script setup>
import { BellOutlined } from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';
import NotificacionesLista from '@/Components/NotificacionesLista.vue';

defineProps({
    notificaciones: { type: Array, default: () => [] },
    noLeidas: { type: Number, default: 0 },
});
</script>

<template>
    <EstudianteLayout title="Notificaciones">
        <section class="sains-hero">
            <div class="sains-hero__grid">
                <div>
                    <span class="sains-hero__eyebrow"><BellOutlined /> Tu actividad</span>
                    <h1 class="sains-hero__title">Notificaciones</h1>
                    <p class="sains-hero__sub">
                        {{ noLeidas ? `Tienes ${noLeidas} sin leer` : 'Estás al día con tus notificaciones' }}
                    </p>
                </div>
            </div>
        </section>

        <NotificacionesLista :notificaciones="notificaciones" />
    </EstudianteLayout>
</template>
