<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    TeamOutlined, FileTextOutlined, DollarOutlined, PhoneOutlined,
    TrophyOutlined, ClockCircleOutlined, CheckCircleOutlined,
    PercentageOutlined, UserSwitchOutlined, ThunderboltFilled, RightOutlined,
} from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
    stats: { type: Object, required: true },
    ultimosExamenes: { type: Array, default: () => [] },
    ultimasInteracciones: { type: Array, default: () => [] },
    topEstudiantes: { type: Array, default: () => [] },
});

const page = usePage();
const adminNombre = computed(() => page.props.auth?.admin?.nombre?.trim() || 'administrador');

const money = (n) => '$' + Number(n || 0).toLocaleString('es-MX', { maximumFractionDigits: 0 });

const mainCards = computed(() => [
    { label: 'Total estudiantes', value: props.stats.totalUsuarios, icon: TeamOutlined, color: 'indigo', hint: `+${props.stats.nuevosUsuariosMes} este mes`, route: 'admin.estudiantes.index' },
    { label: 'Exámenes generados', value: props.stats.totalExamenes, icon: FileTextOutlined, color: 'violet', hint: `+${props.stats.nuevosExamenes} esta semana`, route: 'admin.examenes.index' },
    { label: 'Ingresos totales', value: money(props.stats.totalPagos), icon: DollarOutlined, color: 'green', hint: `${money(props.stats.ingresosMes)} este mes`, route: 'admin.pagos.index' },
    { label: 'Interacciones', value: props.stats.totalInteracciones, icon: PhoneOutlined, color: 'pink', hint: `+${props.stats.interaccionesMes} en 30 días`, route: 'admin.callcenter.index' },
]);

const metricCards = computed(() => [
    { label: 'Exámenes realizados', value: props.stats.examenesRealizados, icon: CheckCircleOutlined, color: 'amber', hint: `${props.stats.examenesRealizadosSemana} esta semana` },
    { label: 'Calificación promedio', value: `${props.stats.calificacionPromedio} / 100`, icon: TrophyOutlined, color: 'indigo', hint: 'de todos los intentos' },
    { label: 'Tasa de aprobación', value: `${props.stats.tasaAprobacion}%`, icon: PercentageOutlined, color: 'green', hint: 'calificación ≥ 60' },
    { label: 'Estudiantes activos', value: props.stats.estudiantesActivos, icon: UserSwitchOutlined, color: 'pink', hint: `${props.stats.cuponesCanjeados} cupones canjeados` },
]);

const maxTop = computed(() => Math.max(1, ...props.topEstudiantes.map((t) => Number(t.promedio) || 0)));

const examColumns = [
    { title: 'Estudiante', key: 'est' },
    { title: 'Examen', dataIndex: 'examen_titulo', key: 'exam' },
    { title: 'Calif.', dataIndex: 'calificacion', key: 'calif', align: 'center', width: 84 },
    { title: 'Fecha', dataIndex: 'fecha', key: 'fecha', align: 'right', width: 120 },
];
</script>

<template>
    <AdminLayout title="Panel de control">
        <section class="sains-hero">
            <div class="sains-hero__grid">
                <div>
                    <span class="sains-hero__eyebrow"><ThunderboltFilled /> Panel de administración</span>
                    <h1 class="sains-hero__title">Hola, {{ adminNombre }}</h1>
                    <p class="sains-hero__sub">Resumen general del sistema en tiempo real.</p>
                </div>
                <div class="hero-figs">
                    <div class="hero-fig">
                        <b>{{ Number(stats.totalUsuarios || 0).toLocaleString('es-MX') }}</b>
                        <span>estudiantes</span>
                    </div>
                    <div class="hero-fig">
                        <b>{{ money(stats.ingresosMes) }}</b>
                        <span>ingresos del mes</span>
                    </div>
                    <div class="hero-fig">
                        <b>{{ stats.tasaAprobacion }}%</b>
                        <span>tasa de aprobación</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="sains-stats sains-stats--4">
            <button v-for="c in mainCards" :key="c.label" class="stat-link" @click="router.visit(route(c.route))">
                <StatCard :label="c.label" :value="c.value" :hint="c.hint" :icon="c.icon" :color="c.color" />
            </button>
        </div>

        <div class="sains-stats sains-stats--4">
            <StatCard v-for="m in metricCards" :key="m.label" :label="m.label" :value="m.value" :hint="m.hint" :icon="m.icon" :color="m.color" />
        </div>

        <a-row :gutter="16" style="margin-top: 4px">
            <a-col :xs="24" :lg="15">
                <a-card :bordered="false">
                    <template #title><CheckCircleOutlined style="color: #4f46e5" /> Últimos exámenes realizados</template>
                    <template #extra><Link :href="route('admin.examenes-realizados.index')">Ver todos <RightOutlined style="font-size: 10px" /></Link></template>
                    <a-table :columns="examColumns" :data-source="ultimosExamenes" :pagination="false" row-key="id" size="small">
                        <template #emptyText><a-empty :image="null" description="Sin registros" /></template>
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.key === 'est'">
                                <div class="sains-strong" style="font-size: 13px">{{ record.estudiante_nombre }}</div>
                                <div class="sains-cell-sub">{{ record.estudiante_email }}</div>
                            </template>
                            <template v-else-if="column.key === 'calif'">
                                <a-tag :bordered="false" :color="record.calificacion >= 60 ? 'green' : 'red'">{{ record.calificacion }}</a-tag>
                            </template>
                        </template>
                    </a-table>
                </a-card>
            </a-col>

            <a-col :xs="24" :lg="9">
                <a-card :bordered="false" style="margin-bottom: 16px">
                    <template #title><TrophyOutlined style="color: #f59e0b" /> Top 5 estudiantes</template>
                    <div v-if="topEstudiantes.length" class="top-list">
                        <div v-for="(t, i) in topEstudiantes" :key="t.estudiante" class="top-row">
                            <span class="rank" :class="`rank-${i + 1}`">{{ i + 1 }}</span>
                            <div class="top-row__body">
                                <div class="top-row__name">{{ t.nombre_completo }}</div>
                                <div class="top-bar">
                                    <div class="top-bar__fill" :style="{ width: ((Number(t.promedio) / maxTop) * 100) + '%' }"></div>
                                </div>
                            </div>
                            <span class="top-row__score">{{ Math.round(Number(t.promedio)) }}</span>
                        </div>
                    </div>
                    <a-empty v-else :image="null" description="Sin datos" />
                </a-card>

                <a-card :bordered="false">
                    <template #title><ClockCircleOutlined style="color: #ec4899" /> Interacciones recientes</template>
                    <template #extra><Link :href="route('admin.callcenter.index')">Ver todas <RightOutlined style="font-size: 10px" /></Link></template>
                    <a-list :data-source="ultimasInteracciones" size="small">
                        <template #renderItem="{ item }">
                            <a-list-item>
                                <a-list-item-meta :description="item.tipo || 'Interacción'">
                                    <template #title>{{ item.estudiante_nombre }}</template>
                                    <template #avatar>
                                        <span class="cc-dot"><PhoneOutlined /></span>
                                    </template>
                                </a-list-item-meta>
                                <span class="sains-faint">{{ item.fecha }}</span>
                            </a-list-item>
                        </template>
                        <template #emptyText><a-empty :image="null" description="Sin registros" /></template>
                    </a-list>
                </a-card>
            </a-col>
        </a-row>
    </AdminLayout>
</template>

<style scoped>
.hero-figs { display: flex; gap: 26px; flex-wrap: wrap; }
.hero-fig { line-height: 1.15; }
.hero-fig b { display: block; font-size: 1.5rem; font-weight: 800; letter-spacing: -.02em; }
.hero-fig span { font-size: 11.5px; opacity: .82; text-transform: uppercase; letter-spacing: .05em; }
@media (max-width: 720px) { .hero-figs { gap: 18px; } .hero-fig b { font-size: 1.25rem; } }

.stat-link { display: block; width: 100%; padding: 0; border: 0; background: transparent; cursor: pointer; text-align: left; }

.top-list { display: flex; flex-direction: column; gap: 14px; }
.top-row { display: flex; align-items: center; gap: 12px; }
.top-row__body { flex: 1; min-width: 0; }
.top-row__name { font-size: 13px; font-weight: 600; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.top-bar { height: 6px; border-radius: 999px; background: #eef1f6; margin-top: 5px; overflow: hidden; }
.top-bar__fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #6366f1, #ec4899); }
.top-row__score { font-weight: 800; color: #4f46e5; font-size: 15px; flex: none; }

.rank {
    width: 28px; height: 28px; border-radius: 50%; flex: none;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 12px;
    background: #f1f5f9; color: #475569;
}
.rank-1 { background: linear-gradient(135deg, #fcd34d, #f59e0b); color: #7c2d12; }
.rank-2 { background: linear-gradient(135deg, #e2e8f0, #cbd5e1); color: #475569; }
.rank-3 { background: linear-gradient(135deg, #fdba74, #fb923c); color: #7c2d12; }

.cc-dot {
    width: 30px; height: 30px; border-radius: 9px; display: flex; align-items: center; justify-content: center;
    background: #fce7f3; color: #db2777; font-size: 12px;
}
</style>
