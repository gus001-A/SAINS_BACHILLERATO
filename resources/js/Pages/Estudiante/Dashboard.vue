<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    RocketOutlined, CrownOutlined, BarChartOutlined, FileTextOutlined,
    TrophyOutlined, ClockCircleOutlined, FileDoneOutlined, ArrowRightOutlined,
    PlayCircleFilled, ThunderboltFilled, UploadOutlined, SafetyCertificateOutlined,
    DownloadOutlined, CheckCircleFilled, BookOutlined,
} from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
    estudiante: { type: Object, default: null },
    perfilCompleto: { type: Boolean, default: false },
    tieneFoto: { type: Boolean, default: false },
    documentos: { type: Object, default: () => ({ total_requeridos: 4, total_subidos: 0, total_aprobados: 0, total_pendientes: 0, total_rechazados: 0 }) },
    certificado: { type: Object, default: () => ({ generado: false, generado_en: null }) },
});

// Estudiantes registrados antes de ISSFAM: les faltan carrera / CURP / domicilio.
const faltanDatosIssfam = computed(() => !!props.estudiante && (!props.estudiante.carrera_id || !props.estudiante.curp));

const sinDocumentos = computed(() => (props.documentos?.total_subidos ?? 0) === 0);
const porcentajeDocumentos = computed(() => {
    const req = props.documentos?.total_requeridos || 1;
    return Math.round(((props.documentos?.total_aprobados ?? 0) / req) * 100);
});

function descargarCertificado() {
    window.open(route('estudiante.certificado.descargar'), '_blank');
}

const page = usePage();
const planActivo = computed(() => page.props.auth?.estudiante?.plan_activo ?? false);
const nombre = computed(() => page.props.auth?.estudiante?.nombre || 'estudiante');

const loading = ref(true);
const stats = ref({});
const ultimos = ref([]);
const tiempo = ref({});
let hb = null;

const avance = computed(() => Math.round(stats.value.progreso ?? 0));

onMounted(async () => {
    try {
        const [s, u, t] = await Promise.allSettled([
            axios.get('/estudiante/api/estadisticas'),
            axios.get('/estudiante/api/ultimos-examenes'),
            axios.get('/estudiante/api/tiempo-estudio'),
        ]);
        if (s.status === 'fulfilled') stats.value = s.value.data;
        if (u.status === 'fulfilled') ultimos.value = u.value.data.examenes ?? [];
        if (t.status === 'fulfilled') tiempo.value = t.value.data;
    } finally {
        loading.value = false;
    }
    axios.post('/estudiante/heartbeat').catch(() => {});
    hb = setInterval(() => axios.post('/estudiante/heartbeat').catch(() => {}), 60000);
});

onBeforeUnmount(() => clearInterval(hb));

const accesos = [
    { title: 'Mi carrera', desc: 'Videos y material por materia', icon: CrownOutlined, color: '#ec4899', route: 'estudiante.clases-premium' },
    { title: 'Guías', desc: 'Guías de estudio de tu carrera', icon: BookOutlined, color: '#d99a00', route: 'estudiante.guias' },
    { title: 'Simulador', desc: 'Examen de prueba de 50, 150 o 250', icon: RocketOutlined, color: '#1851ad', route: 'estudiante.simulador' },
    { title: 'Mis exámenes', desc: 'Historial y resultados', icon: FileTextOutlined, color: '#0ea5e9', route: 'estudiante.examenes' },
];
</script>

<template>
    <EstudianteLayout title="Panel del estudiante">
        <a-alert v-if="faltanDatosIssfam" type="warning" show-icon class="faltan-datos"
            message="Completa tu carrera, CURP y domicilio"
            description="Los necesitamos para tu inscripción y para mostrarte las guías de tu carrera.">
            <template #action>
                <a-button type="primary" size="small" @click="router.visit(route('estudiante.perfil'))">Completar ahora</a-button>
            </template>
        </a-alert>
        <section class="sains-hero">
            <div class="sains-hero__grid">
                <div>
                    <span class="sains-hero__eyebrow">
                        <ThunderboltFilled /> {{ planActivo ? 'Plan Premium activo' : 'Plan básico' }}
                    </span>
                    <h1 class="sains-hero__title">Hola, {{ nombre }}</h1>
                    <p class="sains-hero__sub">
                        {{ planActivo
                            ? 'Continúa donde lo dejaste y sigue avanzando hacia tu certificado de bachillerato.'
                            : 'Desbloquea todas las clases y simuladores ilimitados con el Plan Premium.' }}
                    </p>
                    <div class="hero-cta">
                        <a-button size="large" ghost @click="router.visit(route('estudiante.simulador'))">
                            <template #icon><PlayCircleFilled /></template>Iniciar simulador
                        </a-button>
                        <a-button v-if="!planActivo" size="large" type="primary" class="hero-cta__up"
                            @click="router.visit(route('estudiante.checkout'))">
                            <template #icon><CrownOutlined /></template>Hazte Premium
                        </a-button>
                        <a-button v-else size="large" type="primary" class="hero-cta__up"
                            @click="router.visit(route('estudiante.clases-premium'))">
                            Continuar clases <ArrowRightOutlined />
                        </a-button>
                    </div>
                </div>
                <div class="sains-hero__aside">
                    <div class="sains-hero__ring" :style="{ '--v': avance }">
                        <div><span style="display:block">
                            <b>{{ avance }}%</b>
                            <span>avance</span>
                        </span></div>
                    </div>
                </div>
            </div>
        </section>

        <a-spin :spinning="loading">
            <div class="sains-stats sains-stats--4 sains-stagger">
                <StatCard label="Promedio general" :value="`${Math.round(stats.promedio ?? 0)}%`" :icon="BarChartOutlined" color="indigo" />
                <StatCard label="Mejor puntaje" :value="`${Math.round(stats.mejor_puntaje ?? 0)}%`" :icon="TrophyOutlined" color="green" />
                <StatCard label="Exámenes realizados" :value="stats.total_examenes ?? 0" :icon="FileDoneOutlined" color="violet" />
                <StatCard label="Horas de estudio" :value="tiempo.total?.horas ?? stats.horas_estudio ?? 0" :icon="ClockCircleOutlined" color="amber" />
            </div>

            <div class="quick sains-stagger">
                <button v-for="a in accesos" :key="a.title" class="quick-card sains-lift" @click="router.visit(route(a.route))">
                    <span class="quick-card__ico" :style="{ background: a.color }">
                        <component :is="a.icon" />
                    </span>
                    <span class="quick-card__body">
                        <b>{{ a.title }}</b>
                        <small>{{ a.desc }}</small>
                    </span>
                    <ArrowRightOutlined class="quick-card__arrow" />
                </button>
            </div>

            <a-row :gutter="[16, 16]" style="margin-top: 4px">
                <a-col :xs="24" :md="14">
                    <a-card :bordered="false" title="Últimos exámenes">
                        <template #extra>
                            <Link :href="route('estudiante.examenes')">Ver todos</Link>
                        </template>
                        <a-list :data-source="ultimos" size="small">
                            <template #renderItem="{ item }">
                                <a-list-item>
                                    <a-list-item-meta :title="`Examen del ${item.fecha}`" description="Simulador">
                                        <template #avatar>
                                            <span class="exam-dot" :class="item.calificacion >= 70 ? 'ok' : 'bad'">
                                                {{ item.calificacion }}
                                            </span>
                                        </template>
                                    </a-list-item-meta>
                                    <template #actions>
                                        <a @click="router.visit(route('estudiante.resultados', item.id))">Ver</a>
                                    </template>
                                </a-list-item>
                            </template>
                            <template #empty>
                                <a-empty description="Aún no has realizado exámenes">
                                    <a-button type="primary" @click="router.visit(route('estudiante.simulador'))">Empezar ahora</a-button>
                                </a-empty>
                            </template>
                        </a-list>
                    </a-card>
                </a-col>
                <a-col :xs="24" :md="10">
                    <a-card :bordered="false" title="Documentos y certificado">
                        <div class="docs-status">
                            <div class="docs-status__row">
                                <span>Documentos aprobados</span>
                                <b>{{ documentos.total_aprobados }}/{{ documentos.total_requeridos }}</b>
                            </div>
                            <a-progress
                                :percent="porcentajeDocumentos"
                                :status="porcentajeDocumentos >= 100 ? 'success' : 'active'"
                                :stroke-color="{ from: '#1664db', to: '#ec4899' }"
                            />
                            <a-typography-text type="secondary" v-if="!sinDocumentos">
                                <template v-if="documentos.total_pendientes">{{ documentos.total_pendientes }} en revisión</template>
                                <template v-if="documentos.total_rechazados"> · {{ documentos.total_rechazados }} por corregir</template>
                            </a-typography-text>
                        </div>
                        <a-empty v-if="sinDocumentos" description="Aún no has subido ningún documento" style="margin: 10px 0">
                            <a-button type="primary" @click="router.visit(route('estudiante.perfil'))">
                                <template #icon><UploadOutlined /></template>Subir documentos
                            </a-button>
                        </a-empty>
                        <a-button v-else style="margin-top: 8px" @click="router.visit(route('estudiante.perfil'))">
                            Ver mis documentos <ArrowRightOutlined />
                        </a-button>

                        <a-divider style="margin: 16px 0" />

                        <div class="cert-status">
                            <template v-if="certificado.generado">
                                <div class="cert-status__ready">
                                    <CheckCircleFilled style="color:#22c55e" />
                                    <span>Tu certificado de finalización ya está disponible.</span>
                                </div>
                                <a-button type="primary" ghost style="margin-top: 10px" @click="descargarCertificado">
                                    <template #icon><DownloadOutlined /></template>Descargar certificado
                                </a-button>
                            </template>
                            <a-typography-text v-else type="secondary">
                                <SafetyCertificateOutlined /> Tu certificado de finalización aparecerá aquí cuando esté listo.
                            </a-typography-text>
                        </div>
                    </a-card>
                </a-col>
            </a-row>
        </a-spin>
    </EstudianteLayout>
</template>

<style scoped>
.hero-cta { display: flex; gap: 10px; margin-top: 18px; flex-wrap: wrap; }
.hero-cta :deep(.ant-btn-background-ghost) { border-color: rgba(255,255,255,.6); color: #fff; }
.hero-cta :deep(.ant-btn-background-ghost:hover) { border-color: #fff; color: #fff; background: rgba(255,255,255,.12); }
.hero-cta__up { background: #fff !important; border-color: #fff !important; color: #1851ad !important; font-weight: 650; }
.hero-cta__up:hover { background: #eef2f8 !important; }

.faltan-datos { margin-bottom: 16px; border-radius: 12px; }
.quick { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin: 4px 0 18px; }
@media (max-width: 1100px) { .quick { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .quick { grid-template-columns: 1fr; } }
.quick-card {
    display: flex; align-items: center; gap: 14px; text-align: left; cursor: pointer;
    background: #fff; border: 1px solid var(--sains-line); border-radius: var(--sains-radius);
    padding: 16px 18px;
}
.quick-card__ico {
    width: 44px; height: 44px; border-radius: 12px; flex: none; color: #fff; font-size: 18px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 10px 20px -10px currentColor;
}
.quick-card__body { display: flex; flex-direction: column; flex: 1; min-width: 0; }
.quick-card__body b { font-size: 14.5px; color: #0f172a; }
.quick-card__body small { color: #64748b; }
.quick-card__arrow { color: #cbd5e1; transition: transform .18s ease, color .18s ease; }
.quick-card:hover .quick-card__arrow { color: #1851ad; transform: translateX(3px); }

.exam-dot {
    width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 13px; color: #fff;
}
.exam-dot.ok { background: linear-gradient(135deg, #22c55e, #16a34a); }
.exam-dot.bad { background: linear-gradient(135deg, #f87171, #dc2626); }

.docs-status__row { display: flex; justify-content: space-between; font-size: 13.5px; color: #475569; margin-bottom: 6px; }
.cert-status__ready { display: flex; align-items: center; gap: 8px; font-size: 13.5px; color: #0f172a; }
</style>
