<script setup>
import { router, Link } from '@inertiajs/vue3';
import { EditOutlined, SolutionOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';

defineProps({
    examen: { type: Object, required: true },
    preguntas: { type: Array, default: () => [] },
    porArea: { type: Array, default: () => [] },
});

const columns = [
    { title: '#', key: 'idx', width: 50 },
    { title: 'Pregunta', dataIndex: 'texto', key: 'texto' },
    { title: 'Respuesta correcta', dataIndex: 'respuesta_correcta', key: 'correcta', width: 220 },
    { title: 'Área', dataIndex: 'area', key: 'area', width: 160 },
];
</script>

<template>
    <AdminLayout :title="`Examen #${examen.id}`">
        <PageHead :title="`Examen #${examen.id}`" :subtitle="examen.tipo_examen" :icon="SolutionOutlined"
            back @back="router.visit(route('admin.examenes.index'))">
            <template #actions>
                <Link :href="route('admin.examenes.edit', examen.id)"><a-button type="primary"><template #icon><EditOutlined /></template>Editar</a-button></Link>
            </template>
        </PageHead>

        <div class="sains-stats sains-stats--3">
            <StatCard label="Preguntas configuradas" :value="examen.numero_preguntas" color="indigo" />
            <StatCard label="Preguntas reales" :value="preguntas.length" color="violet" />
            <StatCard label="Tiempo" :value="`${examen.tiempo} min`" color="amber" />
        </div>

        <a-card v-if="porArea.length" :bordered="false" title="Distribución por área" style="margin-bottom: 16px">
            <a-space wrap>
                <a-tag v-for="a in porArea" :key="a.area" color="blue">{{ a.area }}: {{ a.total }}</a-tag>
            </a-space>
        </a-card>

        <a-card :bordered="false" title="Preguntas del examen">
            <a-table :columns="columns" :data-source="preguntas" row-key="id" size="small" :pagination="{ pageSize: 20 }">
                <template #bodyCell="{ column, index }">
                    <template v-if="column.key === 'idx'">{{ index + 1 }}</template>
                </template>
            </a-table>
        </a-card>
    </AdminLayout>
</template>
