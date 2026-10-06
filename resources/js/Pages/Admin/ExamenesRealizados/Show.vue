<script setup>
import { computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { CheckCircleFilled, CloseCircleFilled, FileDoneOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
    examen: { type: Object, required: true },
    resumen: { type: Object, required: true },
    respuestas: { type: Array, default: () => [] },
});

const aprobado = computed(() => (props.examen.calificacion ?? 0) >= 60);
</script>

<template>
    <AdminLayout :title="`Examen realizado #${examen.id}`">
        <PageHead :title="`Examen de ${examen.estudiante}`" :subtitle="examen.tipo_examen" :icon="FileDoneOutlined"
            back @back="router.visit(route('admin.examenes-realizados.index'))">
            <template #actions>
                <Link :href="route('admin.estudiantes.show', examen.estudiante_id)"><a-button>Ver estudiante</a-button></Link>
            </template>
        </PageHead>

        <section class="er-banner" :class="aprobado ? 'is-ok' : 'is-bad'">
            <div class="er-score">{{ examen.calificacion ?? '—' }}<small>/100</small></div>
            <div>
                <b>{{ aprobado ? 'Aprobado' : 'No aprobado' }}</b>
                <span>{{ resumen.correctas }} correctas · {{ resumen.incorrectas }} incorrectas · intento {{ examen.intento }}</span>
            </div>
        </section>

        <div class="sains-stats sains-stats--4">
            <StatCard label="Calificación" :value="`${examen.calificacion ?? '—'} / 100`" color="indigo" />
            <StatCard label="Correctas" :value="resumen.correctas" color="green" />
            <StatCard label="Incorrectas" :value="resumen.incorrectas" color="red" />
            <StatCard label="Intento" :value="examen.intento" color="slate" />
        </div>

        <a-card :bordered="false" title="Respuestas">
            <a-list :data-source="respuestas" item-layout="vertical">
                <template #renderItem="{ item }">
                    <a-list-item>
                        <a-list-item-meta>
                            <template #title>
                                <CheckCircleFilled v-if="item.correcta" style="color:#52c41a" />
                                <CloseCircleFilled v-else style="color:#ff4d4f" />
                                <span style="margin-left:8px">{{ item.numero }}. {{ item.pregunta }}</span>
                            </template>
                            <template #description>
                                <div>Respuesta del estudiante: <strong>{{ item.respuesta }}</strong></div>
                                <div v-if="!item.correcta">Respuesta correcta: <strong style="color:#52c41a">{{ item.respuesta_correcta }}</strong></div>
                                <div v-if="item.justificacion" style="color:#94a3b8; margin-top:4px">{{ item.justificacion }}</div>
                            </template>
                        </a-list-item-meta>
                    </a-list-item>
                </template>
            </a-list>
        </a-card>
    </AdminLayout>
</template>

<style scoped>
.er-banner {
    display: flex; align-items: center; gap: 20px; border-radius: 18px; padding: 18px 24px; margin-bottom: 18px; color: #fff;
}
.er-banner.is-ok { background: linear-gradient(135deg, #059669, #34d399); box-shadow: 0 18px 40px -22px rgba(16, 185, 129, .55); }
.er-banner.is-bad { background: linear-gradient(135deg, #dc2626, #f87171); box-shadow: 0 18px 40px -22px rgba(239, 68, 68, .5); }
.er-score { font-size: 2.2rem; font-weight: 800; letter-spacing: -.02em; }
.er-score small { font-size: 1rem; opacity: .8; }
.er-banner b { display: block; font-size: 1.1rem; }
.er-banner span { font-size: 12.5px; opacity: .92; }
</style>
