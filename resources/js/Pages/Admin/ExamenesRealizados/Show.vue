<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import {
    CheckCircleFilled, CloseCircleFilled, FileDoneOutlined, EditOutlined, SaveOutlined,
    HistoryOutlined, UndoOutlined,
} from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
    examen: { type: Object, required: true },
    resumen: { type: Object, required: true },
    respuestas: { type: Array, default: () => [] },
    ediciones: { type: Array, default: () => [] },
});

const aprobado = computed(() => (props.examen.calificacion ?? 0) >= 60);

/* ---------- Modo edición: corregir respuestas y calificación final ---------- */
const editando = ref(false);
const seleccion = reactive({});
const form = useForm({ cambios: [], calificacion_manual: null, motivo: '' });
const manual = ref(false);

function iniciar() {
    props.respuestas.forEach((r) => { seleccion[r.indice] = r.respuesta; });
    form.reset();
    manual.value = false;
    editando.value = true;
}
watch(() => props.respuestas, () => { if (!editando.value) return; iniciar(); });

const esCorrectaSel = (r) => {
    const op = r.opciones.find((o) => o.texto === seleccion[r.indice]);
    return op ? op.correcta : r.correcta;
};
const cambios = computed(() => props.respuestas
    .filter((r) => r.opciones.length && seleccion[r.indice] !== undefined && seleccion[r.indice] !== r.respuesta)
    .map((r) => ({ indice: r.indice, respuesta: seleccion[r.indice] })));
const aciertosPrevios = computed(() => props.respuestas.filter((r) => (editando.value ? esCorrectaSel(r) : r.correcta)).length);
const calculada = computed(() => (props.respuestas.length ? Math.round((aciertosPrevios.value / props.respuestas.length) * 100) : 0));

function guardar() {
    form.cambios = cambios.value;
    form.calificacion_manual = manual.value ? form.calificacion_manual : null;
    form.put(route('admin.examenes-realizados.update', props.examen.id), {
        preserveScroll: true,
        onSuccess: () => { editando.value = false; },
    });
}
</script>

<template>
    <AdminLayout :title="`Examen realizado #${examen.id}`">
        <PageHead :title="`Examen de ${examen.estudiante}`" :subtitle="examen.tipo_examen" :icon="FileDoneOutlined"
            back @back="router.visit(route('admin.examenes-realizados.index'))">
            <template #actions>
                <a-button v-if="!editando" type="primary" @click="iniciar"><template #icon><EditOutlined /></template>Corregir examen</a-button>
                <Link v-if="examen.estudiante_id" :href="route('admin.estudiantes.show', examen.estudiante_id)"><a-button>Ver estudiante</a-button></Link>
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

        <a-card v-if="editando" :bordered="false" class="er-edit">
            <div class="er-edit__row">
                <div>
                    <b>Corrigiendo examen</b>
                    <span>Cambia la respuesta de las preguntas que necesites; la calificación se recalcula sola.</span>
                </div>
                <div class="er-edit__score">
                    <small>Aciertos</small>
                    <b>{{ aciertosPrevios }} / {{ respuestas.length }}</b>
                </div>
                <div class="er-edit__score">
                    <small>Calificación calculada</small>
                    <b>{{ calculada }}</b>
                </div>
            </div>
            <a-row :gutter="16" style="margin-top: 14px">
                <a-col :xs="24" :md="8">
                    <a-checkbox v-model:checked="manual">Capturar calificación final manualmente</a-checkbox>
                    <a-input-number v-if="manual" v-model:value="form.calificacion_manual" :min="0" :max="100" :step="0.5"
                        style="width: 100%; margin-top: 8px" placeholder="0 a 100" />
                    <div v-if="form.errors.calificacion_manual" class="er-err">{{ form.errors.calificacion_manual }}</div>
                </a-col>
                <a-col :xs="24" :md="16">
                    <a-input v-model:value="form.motivo" :maxlength="500" placeholder="Motivo de la corrección (opcional, queda en el historial)" />
                </a-col>
            </a-row>
            <div class="er-edit__actions">
                <a-button @click="editando = false"><template #icon><UndoOutlined /></template>Cancelar</a-button>
                <a-button type="primary" :loading="form.processing" @click="guardar">
                    <template #icon><SaveOutlined /></template>Guardar corrección
                    <span v-if="cambios.length">&nbsp;({{ cambios.length }})</span>
                </a-button>
            </div>
        </a-card>

        <a-card :bordered="false" title="Respuestas">
            <a-list :data-source="respuestas" item-layout="vertical">
                <template #renderItem="{ item }">
                    <a-list-item>
                        <a-list-item-meta>
                            <template #title>
                                <CheckCircleFilled v-if="editando ? esCorrectaSel(item) : item.correcta" style="color:#52c41a" />
                                <CloseCircleFilled v-else style="color:#ff4d4f" />
                                <span style="margin-left:8px">{{ item.numero }}. {{ item.pregunta }}</span>
                            </template>
                            <template #description>
                                <div v-if="editando && item.opciones.length" class="er-opts">
                                    <a-radio-group v-model:value="seleccion[item.indice]">
                                        <a-radio v-for="op in item.opciones" :key="op.texto" :value="op.texto" class="er-opt">
                                            {{ op.texto }}
                                            <a-tag v-if="op.correcta" color="green" :bordered="false" style="margin-left: 6px">Correcta</a-tag>
                                        </a-radio>
                                    </a-radio-group>
                                </div>
                                <template v-else>
                                <div>Respuesta del estudiante: <strong>{{ item.respuesta }}</strong>
                                    <a-tag v-if="item.editada" color="gold" :bordered="false" style="margin-left: 6px">Corregida por admin</a-tag>
                                </div>
                                <div v-if="!item.correcta">Respuesta correcta: <strong style="color:#52c41a">{{ item.respuesta_correcta }}</strong></div>
                                <div v-if="item.justificacion" style="color:#94a3b8; margin-top:4px">{{ item.justificacion }}</div>
                                </template>
                            </template>
                        </a-list-item-meta>
                    </a-list-item>
                </template>
            </a-list>
        </a-card>

        <a-card v-if="ediciones.length" :bordered="false" style="margin-top: 16px">
            <template #title><HistoryOutlined /> Historial de correcciones</template>
            <a-timeline style="margin-top: 8px">
                <a-timeline-item v-for="(e, i) in ediciones" :key="i">
                    <b>{{ e.admin }}</b> · {{ e.fecha }}<br />
                    {{ e.respuestas_modificadas }} respuesta(s) modificada(s) · calificación {{ e.calificacion_anterior ?? '—' }} → <b>{{ e.calificacion_nueva }}</b>
                    <span v-if="e.manual"> (manual)</span>
                    <div v-if="e.motivo" style="color:#64748b">Motivo: {{ e.motivo }}</div>
                </a-timeline-item>
            </a-timeline>
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
.er-edit { margin-bottom: 16px; border: 1px solid #f5d27a !important; background: #fffbeb; }
.er-edit__row { display: flex; align-items: center; gap: 24px; flex-wrap: wrap; }
.er-edit__row > div:first-child { flex: 1; min-width: 240px; }
.er-edit__row b { display: block; font-size: 15px; color: #0f172a; }
.er-edit__row span { font-size: 12.5px; color: #64748b; }
.er-edit__score { text-align: center; }
.er-edit__score small { display: block; font-size: 11.5px; color: #64748b; }
.er-edit__score b { font-size: 22px; color: var(--sains-primary); }
.er-edit__actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 14px; }
.er-err { color: #dc2626; font-size: 12px; margin-top: 4px; }
.er-opts :deep(.ant-radio-group) { display: flex; flex-direction: column; gap: 6px; margin-top: 4px; }
.er-opt { white-space: normal; }
</style>
