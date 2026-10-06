<script setup>
import { computed, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    FileTextOutlined, PlayCircleOutlined, RiseOutlined, TrophyFilled,
    CheckCircleFilled, CloseCircleFilled, RightOutlined,
} from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';
import StatCard from '@/Components/StatCard.vue';

const loading = ref(true);
const stats = ref({});
const examenes = ref([]);
const filtro = ref('todos');
const buscar = ref('');

const filtrados = computed(() => {
    let out = examenes.value;
    if (filtro.value === 'aprobados') out = out.filter((e) => e.aprobado);
    if (filtro.value === 'reprobados') out = out.filter((e) => !e.aprobado);
    const q = buscar.value.trim().toLowerCase();
    if (q) out = out.filter((e) => `${e.tipo_examen} ${e.fecha}`.toLowerCase().includes(q));
    return out;
});

// Últimos intentos para la mini-gráfica (del más antiguo al más reciente)
const recientes = computed(() => [...examenes.value].slice(0, 8).reverse());

const notaColor = (n) => (n >= 85 ? '#16a34a' : n >= 70 ? '#4f46e5' : n >= 50 ? '#d97706' : '#dc2626');

const cols = [
    { title: 'Examen', key: 'tipo' },
    { title: 'Fecha', dataIndex: 'fecha', key: 'fecha', width: 150 },
    { title: 'Intento', dataIndex: 'intento', key: 'intento', width: 90, align: 'center' },
    { title: 'Resultado', key: 'calif', width: 150, align: 'center' },
    { title: '', key: 'accion', width: 70, align: 'right' },
];

onMounted(async () => {
    try {
        const [s, h] = await Promise.allSettled([
            axios.get('/estudiante/api/estadisticas'),
            axios.get('/estudiante/historial-examenes'),
        ]);
        if (s.status === 'fulfilled') stats.value = s.value.data;
        if (h.status === 'fulfilled') examenes.value = h.value.data.examenes ?? [];
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <EstudianteLayout title="Mis exámenes">
        <section class="sains-hero">
            <div class="sains-hero__grid">
                <div>
                    <span class="sains-hero__eyebrow"><FileTextOutlined /> Historial de exámenes</span>
                    <h1 class="sains-hero__title">Mis exámenes</h1>
                    <p class="sains-hero__sub">Cada intento cuenta. Repasa tus resultados y vuelve a intentarlo.</p>
                    <a-button size="large" class="hero-up" @click="router.visit(route('estudiante.simulador'))">
                        <template #icon><PlayCircleOutlined /></template>Iniciar simulador
                    </a-button>
                </div>
                <div class="sains-hero__aside">
                    <div class="sains-hero__ring" :style="{ '--v': Math.round(stats.promedio ?? 0) }">
                        <div><span>
                            <b>{{ Math.round(stats.promedio ?? 0) }}%</b>
                            <span>promedio</span>
                        </span></div>
                    </div>
                </div>
            </div>
        </section>

        <a-spin :spinning="loading">
            <div class="sains-stats sains-stats--4 sains-stagger">
                <StatCard label="Promedio general" :value="`${Math.round(stats.promedio ?? 0)}%`" color="indigo" :icon="RiseOutlined" />
                <StatCard label="Mejor puntaje" :value="`${Math.round(stats.mejor_puntaje ?? 0)}%`" color="green" :icon="TrophyFilled" />
                <StatCard label="Intentos aprobados" :value="stats.aprobados ?? 0" color="amber" :icon="CheckCircleFilled" />
                <StatCard label="Total de intentos" :value="stats.total_examenes ?? 0" color="violet" :icon="FileTextOutlined" />
            </div>

            <a-card v-if="recientes.length" :bordered="false" class="ex-trend" style="margin-top: 16px">
                <div class="ex-trend__head">
                    <span>Tu evolución reciente</span>
                    <small>últimos {{ recientes.length }} intentos</small>
                </div>
                <div class="ex-spark">
                    <div v-for="(e, i) in recientes" :key="i" class="ex-spark__col">
                        <span class="ex-spark__val" :style="{ color: notaColor(e.calificacion) }">{{ e.calificacion }}</span>
                        <span class="ex-spark__bar" :style="{ height: `${Math.max(6, e.calificacion)}%`, background: notaColor(e.calificacion) }"></span>
                    </div>
                </div>
                <div class="ex-spark__base"><span>0</span><span>aprobación 70%</span><span>100</span></div>
            </a-card>

            <a-card :bordered="false" style="margin-top: 16px">
                <div class="ex-toolbar">
                    <a-segmented
                        v-model:value="filtro"
                        :options="[
                            { label: 'Todos', value: 'todos' },
                            { label: 'Aprobados', value: 'aprobados' },
                            { label: 'Por mejorar', value: 'reprobados' },
                        ]"
                    />
                    <a-input-search v-model:value="buscar" placeholder="Buscar por tipo o fecha…" style="max-width: 280px" allow-clear />
                </div>

                <a-table
                    class="ex-table"
                    :data-source="filtrados"
                    :columns="cols"
                    :pagination="{ pageSize: 10, hideOnSinglePage: true }"
                    row-key="id"
                    size="middle"
                >
                    <template #emptyText>
                        <a-empty description="Aún no tienes exámenes registrados">
                            <a-button type="primary" @click="router.visit(route('estudiante.simulador'))">
                                Hacer mi primer simulador
                            </a-button>
                        </a-empty>
                    </template>
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'tipo'">
                            <div class="ex-cell">
                                <span class="ex-cell__ic" :class="record.aprobado ? 'ok' : 'no'">
                                    <CheckCircleFilled v-if="record.aprobado" />
                                    <CloseCircleFilled v-else />
                                </span>
                                <div>
                                    <div class="ex-cell__name">{{ record.tipo_examen }}</div>
                                    <div class="ex-cell__sub">{{ record.aprobado ? 'Aprobado' : 'Por mejorar' }}</div>
                                </div>
                            </div>
                        </template>
                        <template v-else-if="column.key === 'intento'">
                            <span class="ex-intento">#{{ record.intento }}</span>
                        </template>
                        <template v-else-if="column.key === 'calif'">
                            <span class="ex-score" :style="{ '--c': notaColor(record.calificacion) }">
                                {{ record.calificacion }}<i>%</i>
                            </span>
                        </template>
                        <template v-else-if="column.key === 'accion'">
                            <a-button type="text" shape="circle" @click="router.visit(route('estudiante.resultados', record.id))">
                                <RightOutlined />
                            </a-button>
                        </template>
                    </template>
                </a-table>
            </a-card>
        </a-spin>
    </EstudianteLayout>
</template>

<style scoped>
.hero-up { background: #fff !important; border-color: #fff !important; color: #4f46e5 !important; font-weight: 650; margin-top: 16px; }
.hero-up:hover { background: #f1f0ff !important; }

.ex-toolbar { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }

/* Mini-gráfica de evolución */
.ex-trend__head { display: flex; align-items: baseline; gap: 8px; margin-bottom: 14px; }
.ex-trend__head span { font-weight: 650; color: #0f172a; font-size: 14px; }
.ex-trend__head small { color: #94a3b8; font-size: 12px; }
.ex-spark {
    display: flex; align-items: flex-end; gap: 10px; height: 118px;
    padding: 0 4px; border-bottom: 1px dashed var(--sains-line); position: relative;
}
.ex-spark::before {
    content: ''; position: absolute; left: 0; right: 0; bottom: 30%;
    border-top: 1px dashed #cbd5e1;
}
.ex-spark__col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; gap: 4px; height: 100%; }
.ex-spark__val { font-size: 11px; font-weight: 700; font-variant-numeric: tabular-nums; }
.ex-spark__bar { width: 100%; max-width: 34px; border-radius: 7px 7px 0 0; transition: height .4s cubic-bezier(.16,1,.3,1); }
.ex-spark__base { display: flex; justify-content: space-between; font-size: 10.5px; color: #94a3b8; margin-top: 6px; }

/* Tabla */
.ex-cell { display: flex; align-items: center; gap: 11px; }
.ex-cell__ic {
    width: 34px; height: 34px; border-radius: 10px; flex: none;
    display: flex; align-items: center; justify-content: center; font-size: 15px;
}
.ex-cell__ic.ok { background: #dcfce7; color: #16a34a; }
.ex-cell__ic.no { background: #fee2e2; color: #dc2626; }
.ex-cell__name { font-weight: 600; color: #0f172a; font-size: 13.5px; }
.ex-cell__sub { font-size: 11.5px; color: #94a3b8; }
.ex-intento {
    font-size: 12px; font-weight: 600; color: #475569;
    background: #f1f5f9; padding: 2px 9px; border-radius: 999px;
}
.ex-score {
    font-size: 20px; font-weight: 800; color: var(--c); letter-spacing: -.02em;
    font-variant-numeric: tabular-nums;
}
.ex-score i { font-size: 12px; font-style: normal; font-weight: 700; opacity: .7; margin-left: 1px; }
</style>
