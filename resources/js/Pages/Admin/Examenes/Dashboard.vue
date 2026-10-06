<script setup>
import { Link, router } from '@inertiajs/vue3';
import { FileTextOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';

defineProps({
    stats: { type: Object, required: true },
    porTipo: { type: Array, default: () => [] },
    ultimosExamenes: { type: Array, default: () => [] },
});

const columns = [
    { title: 'Examen', key: 'id', width: 90 },
    { title: 'Tipo', dataIndex: 'tipo_examen', key: 'tipo' },
    { title: 'Config.', dataIndex: 'numero_preguntas', key: 'config', width: 90, align: 'center' },
    { title: 'Reales', dataIndex: 'preguntas_reales', key: 'reales', width: 90, align: 'center' },
    { title: 'Tiempo', key: 'tiempo', width: 100, align: 'center' },
    { title: 'Creado', dataIndex: 'created_at', key: 'created_at', width: 120, align: 'right' },
];
</script>

<template>
    <AdminLayout title="Dashboard de exámenes">
        <PageHead title="Dashboard de exámenes" subtitle="Configuración y volumen de exámenes generados" :icon="FileTextOutlined"
            back @back="router.visit(route('admin.examenes.index'))" />

        <div class="sains-stats sains-stats--4">
            <StatCard label="Exámenes" :value="stats.total" color="indigo" />
            <StatCard label="Preguntas asignadas" :value="stats.preguntasAsignadas" color="violet" />
            <StatCard label="Prom. preguntas" :value="stats.promedioPreguntas" color="green" />
            <StatCard label="Prom. tiempo" :value="`${stats.promedioTiempo} min`" color="amber" />
        </div>

        <a-card :bordered="false" title="Por tipo de examen" style="margin-top: 16px">
            <a-space wrap>
                <a-tag v-for="t in porTipo" :key="t.tipo" color="blue" style="font-size: 14px; padding: 4px 10px">{{ t.tipo }}: {{ t.total }}</a-tag>
            </a-space>
        </a-card>

        <a-card :bordered="false" title="Últimos exámenes" style="margin-top: 16px">
            <a-table :columns="columns" :data-source="ultimosExamenes" row-key="id" size="small" :pagination="false">
                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'id'">
                        <Link :href="route('admin.examenes.show', record.id)">#{{ record.id }}</Link>
                    </template>
                    <template v-else-if="column.key === 'tiempo'">{{ record.tiempo }} min</template>
                </template>
            </a-table>
        </a-card>
    </AdminLayout>
</template>
