<script setup>
import { computed, ref } from 'vue';
import {
    SolutionOutlined, ClockCircleOutlined, OrderedListOutlined,
    DatabaseOutlined, ThunderboltFilled, SearchOutlined,
} from '@ant-design/icons-vue';

const props = defineProps({
    form: { type: Object, required: true },
    preguntas: { type: Array, required: true },
    areas: { type: Array, required: true },
    tiposExamen: { type: Array, required: true },
});

const areaFilter = ref(undefined);
const search = ref('');

const filtered = computed(() => {
    let list = props.preguntas;
    if (areaFilter.value) list = list.filter((p) => p.id_area === areaFilter.value);
    if (search.value) {
        const s = search.value.toLowerCase();
        list = list.filter((p) => p.pregunta.toLowerCase().includes(s));
    }
    return list;
});

const seleccionadas = computed(() => props.form.preguntas_seleccionadas.length);
const faltan = computed(() => Math.max(0, (Number(props.form.numero_preguntas) || 0) - seleccionadas.value));
const sobran = computed(() => Math.max(0, seleccionadas.value - (Number(props.form.numero_preguntas) || 0)));
const bancoDisponible = computed(() => props.preguntas.length - seleccionadas.value);

const rowSelection = computed(() => ({
    selectedRowKeys: props.form.preguntas_seleccionadas,
    onChange: (keys) => { props.form.preguntas_seleccionadas = keys; },
    preserveSelectedRowKeys: true,
}));

const columns = [
    { title: 'Pregunta', dataIndex: 'pregunta', key: 'pregunta' },
    { title: 'Área', dataIndex: 'area', key: 'area', width: 180 },
];
</script>

<template>
    <a-form layout="vertical" class="sains-form-inner">
        <a-divider orientation="left"><span class="fsec"><SolutionOutlined />Configuración del examen</span></a-divider>
        <a-row :gutter="16">
            <a-col :xs="24" :md="8">
                <a-form-item required :validate-status="form.errors.tipo_examen ? 'error' : undefined" :help="form.errors.tipo_examen">
                    <template #label><SolutionOutlined />Tipo de examen</template>
                    <a-select v-model:value="form.tipo_examen" :options="tiposExamen.map((t) => ({ value: t, label: t }))" placeholder="Selecciona…" />
                </a-form-item>
            </a-col>
            <a-col :xs="12" :md="8">
                <a-form-item required :validate-status="form.errors.tiempo ? 'error' : undefined" :help="form.errors.tiempo">
                    <template #label><ClockCircleOutlined />Tiempo (minutos)</template>
                    <a-input-number v-model:value="form.tiempo" :min="1" :max="180" style="width: 100%" />
                </a-form-item>
            </a-col>
            <a-col :xs="12" :md="8">
                <a-form-item required :validate-status="form.errors.numero_preguntas ? 'error' : undefined" :help="form.errors.numero_preguntas">
                    <template #label><OrderedListOutlined />N.º de preguntas del examen</template>
                    <a-input-number v-model:value="form.numero_preguntas" :min="1" :max="200" style="width: 100%" />
                </a-form-item>
            </a-col>
        </a-row>

        <div class="rand-box" :class="{ 'is-on': form.completar_aleatorio }">
            <div class="rand-box__head">
                <span class="rand-box__ic"><ThunderboltFilled /></span>
                <div>
                    <div class="rand-box__title">Completar con preguntas aleatorias</div>
                    <div class="rand-box__sub">
                        Elige algunas preguntas manualmente abajo y deja que el sistema rellene el resto al azar
                        desde el banco.
                    </div>
                </div>
                <a-switch v-model:checked="form.completar_aleatorio" />
            </div>
            <div v-if="form.completar_aleatorio" class="rand-box__stats">
                <span class="rand-chip">Objetivo: <b>{{ form.numero_preguntas || 0 }}</b></span>
                <span class="rand-chip is-pick">Elegidas: <b>{{ seleccionadas }}</b></span>
                <span v-if="faltan" class="rand-chip is-rand">Aleatorias: <b>{{ faltan }}</b></span>
                <span v-if="sobran" class="rand-chip is-warn">Sobran {{ sobran }} — reduce la selección o sube el objetivo</span>
                <span v-if="faltan > bancoDisponible" class="rand-chip is-warn">
                    Solo hay {{ bancoDisponible }} preguntas disponibles para el relleno
                </span>
            </div>
        </div>

        <a-divider orientation="left"><span class="fsec"><DatabaseOutlined />Banco de preguntas</span></a-divider>
        <div v-if="form.errors.preguntas_seleccionadas" class="err-line">{{ form.errors.preguntas_seleccionadas }}</div>
        <a-row :gutter="12" style="margin-bottom: 12px">
            <a-col :xs="24" :md="16">
                <a-input v-model:value="search" placeholder="Buscar pregunta…" allow-clear>
                    <template #prefix><SearchOutlined /></template>
                </a-input>
            </a-col>
            <a-col :xs="24" :md="8">
                <a-select v-model:value="areaFilter" allow-clear placeholder="Todas las áreas" style="width: 100%"
                    :options="areas.map((a) => ({ value: a.id, label: a.nombre }))" />
            </a-col>
        </a-row>

        <a-alert v-if="!form.completar_aleatorio" type="info" show-icon style="margin-bottom: 12px"
            :message="`Selecciona exactamente ${form.numero_preguntas || 0} preguntas, o activa el relleno aleatorio de arriba.`" />

        <a-table
            class="banco-tbl"
            :columns="columns"
            :data-source="filtered"
            :row-selection="rowSelection"
            row-key="id"
            size="small"
            :pagination="{ pageSize: 10, showTotal: (t) => `${t} preguntas`, size: 'small' }"
        >
            <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'area'">
                    <a-tag :bordered="false" color="blue">{{ record.area }}</a-tag>
                </template>
            </template>
        </a-table>
    </a-form>
</template>

<style scoped>
.err-line { color: #ef4444; font-size: 12.5px; margin-bottom: 10px; font-weight: 600; }

.rand-box {
    border: 1.5px solid var(--sains-line); border-radius: 14px;
    background: #fbfbfe; padding: 16px 18px; margin: 6px 0 4px;
    transition: border-color .15s ease, background .15s ease;
}
.rand-box.is-on { border-color: #c7d2fe; background: #f5f3ff; }
.rand-box__head { display: flex; align-items: flex-start; gap: 13px; }
.rand-box__ic {
    width: 40px; height: 40px; flex: none; border-radius: 11px;
    display: flex; align-items: center; justify-content: center; font-size: 17px; color: #fff;
    background: linear-gradient(135deg, #f59e0b, #f97316);
}
.rand-box__head > div:nth-child(2) { flex: 1; }
.rand-box__title { font-weight: 700; color: #0f172a; font-size: 14px; }
.rand-box__sub { font-size: 12px; color: #64748b; margin-top: 2px; line-height: 1.45; }
.rand-box__stats { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
.rand-chip {
    font-size: 12px; font-weight: 550; padding: 4px 11px; border-radius: 999px;
    background: #eef2ff; color: #4338ca;
}
.rand-chip.is-pick { background: #dbeafe; color: #1d4ed8; }
.rand-chip.is-rand { background: #fef3c7; color: #b45309; }
.rand-chip.is-warn { background: #fee2e2; color: #b91c1c; }
</style>
