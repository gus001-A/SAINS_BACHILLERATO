<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { DollarOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
    stats: { type: Object, required: true },
    porTipo: { type: Array, default: () => [] },
    porMes: { type: Array, default: () => [] },
    ultimosPagos: { type: Array, default: () => [] },
});

const money = (n) => '$' + Number(n || 0).toLocaleString('es-MX', { maximumFractionDigits: 0 });
const maxMes = computed(() => Math.max(1, ...props.porMes.map((m) => m.monto)));
const estadoColor = (s) => ({ aprobado: 'green', completado: 'green', rechazado: 'red', cancelado: 'red', pendiente: 'orange' }[s] || 'blue');

const ultimosColumns = [
    { title: 'Estudiante', dataIndex: 'alumno', key: 'alumno' },
    { title: 'Monto', dataIndex: 'monto', key: 'monto', width: 120, align: 'right' },
    { title: 'Estado', dataIndex: 'estatus', key: 'estatus', width: 130 },
    { title: 'Fecha', dataIndex: 'fecha', key: 'fecha', width: 120, align: 'right' },
];
</script>

<template>
    <AdminLayout title="Dashboard de pagos">
        <PageHead title="Dashboard de pagos" subtitle="Ingresos y transacciones del curso premium" :icon="DollarOutlined"
            back @back="router.visit(route('admin.pagos.index'))" />

        <div class="sains-stats sains-stats--4">
            <StatCard label="Ingresos aprobados" :value="money(stats.ingresos)" color="green" />
            <StatCard label="Monto pendiente" :value="money(stats.pendientes_monto)" color="amber" />
            <StatCard label="Transacciones" :value="stats.transacciones" color="indigo" />
            <StatCard label="Aprobados / Rechazados" :value="`${stats.aprobados} / ${stats.rechazados}`" color="violet" />
        </div>

        <a-row :gutter="16" style="margin-top: 16px">
            <a-col :xs="24" :lg="14">
                <a-card :bordered="false" title="Ingresos por mes (aprobados)">
                    <div v-if="porMes.length" class="month-chart">
                        <div v-for="m in [...porMes].reverse()" :key="m.mes" class="mbar">
                            <div class="mbar-wrap"><div class="mbar-fill" :style="{ height: (m.monto / maxMes * 100) + '%' }"></div></div>
                            <div class="mbar-label">{{ m.mes.slice(5) }}/{{ m.mes.slice(2, 4) }}</div>
                            <div class="mbar-val">{{ money(m.monto) }}</div>
                        </div>
                    </div>
                    <a-empty v-else description="Sin pagos aprobados" />
                </a-card>
            </a-col>
            <a-col :xs="24" :lg="10">
                <a-card :bordered="false" title="Por método de pago">
                    <a-list :data-source="porTipo" size="small">
                        <template #renderItem="{ item }">
                            <a-list-item>
                                <a-list-item-meta :title="item.tipo || 'Sin método'" :description="`${item.total} transacciones`" />
                                <strong>{{ money(item.monto) }}</strong>
                            </a-list-item>
                        </template>
                    </a-list>
                </a-card>
            </a-col>
        </a-row>

        <a-card :bordered="false" title="Últimos pagos" style="margin-top: 16px">
            <a-table :columns="ultimosColumns" :data-source="ultimosPagos" :pagination="false" row-key="id" size="small">
                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'monto'">{{ money(record.monto) }}</template>
                    <template v-else-if="column.key === 'estatus'"><a-tag :color="estadoColor(record.estatus)">{{ record.estatus }}</a-tag></template>
                </template>
            </a-table>
        </a-card>
    </AdminLayout>
</template>

<style scoped>
.month-chart {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    height: 180px;
}
.mbar { flex: 1; display: flex; flex-direction: column; align-items: center; }
.mbar-wrap { flex: 1; width: 100%; display: flex; align-items: flex-end; }
.mbar-fill { width: 100%; background: linear-gradient(180deg, #4361ee, #7c3aed); border-radius: 6px 6px 0 0; min-height: 3px; }
.mbar-label { font-size: 10px; color: #64748b; margin-top: 4px; }
.mbar-val { font-size: 10px; font-weight: 600; }
</style>
