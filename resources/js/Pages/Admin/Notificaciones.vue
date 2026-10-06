<script setup>
import { BellOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import NotificacionesLista from '@/Components/NotificacionesLista.vue';

defineProps({
    notificaciones: { type: Array, default: () => [] },
    noLeidas: { type: Number, default: 0 },
});
</script>

<template>
    <AdminLayout title="Notificaciones">
        <PageHead
            title="Notificaciones"
            :subtitle="noLeidas ? `${noLeidas} sin leer` : 'Todo al día'"
            :icon="BellOutlined"
        />
        <NotificacionesLista :notificaciones="notificaciones" />
    </AdminLayout>
</template>
