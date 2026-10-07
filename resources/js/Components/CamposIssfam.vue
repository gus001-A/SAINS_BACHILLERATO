<script setup>
/**
 * Campos que pide ISSFAM: carrera, domicilio (y CURP, opcional).
 * Se usa en el registro del estudiante, su perfil y el formulario del admin.
 * Recibe el `form` de useForm y escribe directo sobre él.
 *
 * Domicilio dinámico: al escribir el código postal se consulta el catálogo SEPOMEX
 * y se llenan estado y municipio; la colonia se elige de la lista (o se escribe).
 */
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import {
    CheckCircleFilled, LoadingOutlined, EnvironmentOutlined, ExclamationCircleOutlined,
} from '@ant-design/icons-vue';

const props = defineProps({
    form: { type: Object, required: true },
    carreras: { type: Array, default: () => [] },
    entidades: { type: Array, default: () => [] },
    requerido: { type: Boolean, default: true },
    disabled: { type: Boolean, default: false },
    mostrarCarrera: { type: Boolean, default: true },
    mostrarCurp: { type: Boolean, default: true },
});

const err = (k) => (props.form.errors?.[k] ? 'error' : undefined);
function soloDigitos(k, max) {
    props.form[k] = String(props.form[k] ?? '').replace(/\D/g, '').slice(0, max);
}
function curpMayus() {
    props.form.curp = String(props.form.curp ?? '').toUpperCase().replace(/\s/g, '').slice(0, 18);
}

/* ---------------- Código postal → estado, municipio, colonias ---------------- */
const cpEstado = ref('idle'); // idle | buscando | ok | no
const cpInfo = ref(null);
const colonias = ref([]);
let ultimoCp = null;

async function buscarCp(cp, { conservar = false } = {}) {
    if (!/^\d{5}$/.test(cp) || cp === ultimoCp) return;
    ultimoCp = cp;
    cpEstado.value = 'buscando';
    try {
        const { data } = await axios.get(route('codigos-postales.show', cp));
        if (cp !== ultimoCp) return; // llegó una respuesta vieja
        if (!data.encontrado) {
            cpEstado.value = 'no';
            cpInfo.value = data;
            colonias.value = [];
            return;
        }
        cpEstado.value = 'ok';
        cpInfo.value = data;
        colonias.value = data.colonias;
        if (!conservar) {
            props.form.entidad_federativa = props.entidades.includes(data.estado) ? data.estado : props.form.entidad_federativa;
            props.form.municipio = data.municipio;
            // Si el CP tiene una sola colonia se elige sola; si no, se limpia para que la escojan.
            props.form.colonia = data.colonias.length === 1
                ? data.colonias[0]
                : (data.colonias.includes(props.form.colonia) ? props.form.colonia : '');
        }
    } catch (e) {
        cpEstado.value = 'no';
        cpInfo.value = { mensaje: 'No pudimos consultar el código postal. Escribe tu domicilio a mano.' };
    }
}

function onCpInput() {
    soloDigitos('codigo_postal', 5);
    const cp = props.form.codigo_postal;
    if (cp.length === 5) buscarCp(cp);
    else { cpEstado.value = 'idle'; ultimoCp = null; colonias.value = []; }
}

// Perfil / edición: si ya trae CP, se cargan sus colonias sin pisar lo guardado.
onMounted(() => {
    if (/^\d{5}$/.test(String(props.form.codigo_postal ?? ''))) buscarCp(props.form.codigo_postal, { conservar: true });
});
watch(() => props.form.codigo_postal, (v) => {
    if (String(v ?? '').length < 5) { cpEstado.value = 'idle'; ultimoCp = null; }
});

const opcionesColonia = computed(() => {
    const q = String(props.form.colonia ?? '').toLowerCase();
    return colonias.value
        .filter((c) => !q || c.toLowerCase().includes(q))
        .map((c) => ({ value: c }));
});
</script>

<template>
    <!-- ==================== CARRERA ==================== -->
    <template v-if="mostrarCarrera">
        <a-divider orientation="left">Carrera</a-divider>
        <a-form-item :required="requerido" :validate-status="err('carrera_id')" :help="form.errors?.carrera_id">
            <template #label>¿Qué carrera vas a cursar?</template>
            <div class="ci-carreras" role="radiogroup" aria-label="Carrera">
                <button
                    v-for="c in carreras"
                    :key="c.value"
                    type="button"
                    role="radio"
                    :aria-checked="form.carrera_id === c.value"
                    class="ci-carrera"
                    :class="{ on: form.carrera_id === c.value }"
                    :disabled="disabled"
                    @click="form.carrera_id = c.value"
                >
                    <span class="ci-carrera__ic"><i class="fas" :class="c.icono || 'fa-graduation-cap'"></i></span>
                    <span class="ci-carrera__txt">
                        <b>{{ c.label }}</b>
                    </span>
                    <CheckCircleFilled class="ci-carrera__check" />
                </button>
            </div>
        </a-form-item>
    </template>

    <!-- ==================== DOMICILIO (2 filas) ==================== -->
    <a-divider orientation="left">Domicilio</a-divider>

    <!-- FILA 1: Código Postal · Estado · Municipio -->
    <a-row :gutter="16">
        <a-col :xs="24" :sm="8">
            <a-form-item label="Código postal" :required="requerido"
                :validate-status="err('codigo_postal') || (cpEstado === 'no' ? 'warning' : undefined)"
                :help="form.errors?.codigo_postal">
                <a-input v-model:value="form.codigo_postal" :disabled="disabled" inputmode="numeric"
                    :maxlength="5" placeholder="5 dígitos" @input="onCpInput">
                    <template #suffix>
                        <LoadingOutlined v-if="cpEstado === 'buscando'" />
                        <CheckCircleFilled v-else-if="cpEstado === 'ok'" style="color: #16a34a" />
                        <ExclamationCircleOutlined v-else-if="cpEstado === 'no'" style="color: #d97706" />
                        <EnvironmentOutlined v-else style="color: #94a3b8" />
                    </template>
                </a-input>
                <div v-if="!form.errors?.codigo_postal" class="ci-cp-hint" :class="`is-${cpEstado}`">
                    <template v-if="cpEstado === 'ok'">{{ cpInfo.colonias.length }} {{ cpInfo.colonias.length === 1 ? 'colonia' : 'colonias' }} encontradas</template>
                    <template v-else-if="cpEstado === 'no'">{{ cpInfo?.mensaje }}</template>
                    <template v-else-if="cpEstado === 'buscando'">Buscando…</template>
                    <template v-else>Escríbelo y llenamos estado, municipio y colonias.</template>
                </div>
            </a-form-item>
        </a-col>

        <a-col :xs="24" :sm="8">
            <a-form-item label="Estado" :required="requerido"
                :validate-status="err('entidad_federativa')" :help="form.errors?.entidad_federativa">
                <a-select v-model:value="form.entidad_federativa" :disabled="disabled" placeholder="Se llena con el CP"
                    :options="entidades.map((e) => ({ value: e, label: e }))" show-search :class="{ 'ci-auto': cpEstado === 'ok' }" />
            </a-form-item>
        </a-col>

        <a-col :xs="24" :sm="8">
            <a-form-item label="Municipio o alcaldía" :required="requerido"
                :validate-status="err('municipio')" :help="form.errors?.municipio">
                <a-input v-model:value="form.municipio" :disabled="disabled" :maxlength="120"
                    placeholder="Se llena con el CP" :class="{ 'ci-auto': cpEstado === 'ok' }" />
            </a-form-item>
        </a-col>
    </a-row>

    <!-- FILA 2: Colonia · Calle y número · (CURP si aplica) -->
    <a-row :gutter="16">
        <a-col :xs="24" :sm="8">
            <a-form-item label="Colonia" :required="requerido"
                :validate-status="err('colonia')" :help="form.errors?.colonia">
                <a-auto-complete v-model:value="form.colonia" :disabled="disabled" :options="opcionesColonia"
                    :placeholder="colonias.length ? `Elige entre ${colonias.length} colonias` : 'Ej: Centro'"
                    :default-active-first-option="false" style="width: 100%" />
            </a-form-item>
        </a-col>

        <a-col :xs="24" :sm="8">
            <a-form-item label="Calle y número" :required="requerido"
                :validate-status="err('calle_numero')" :help="form.errors?.calle_numero">
                <a-input v-model:value="form.calle_numero" :disabled="disabled" :maxlength="150"
                    placeholder="Ej: Av. Morelos 123, int. 4" />
            </a-form-item>
        </a-col>

        <a-col v-if="mostrarCurp" :xs="24" :sm="8">
            <a-form-item label="CURP" :required="requerido"
                :validate-status="err('curp')"
                :help="form.errors?.curp || '18 caracteres, como aparece en tu constancia de CURP.'">
                <a-input v-model:value="form.curp" :disabled="disabled" :maxlength="18"
                    placeholder="Ej: GODE561231HDFRRN09"
                    class="ci-curp" @input="curpMayus" />
            </a-form-item>
        </a-col>
    </a-row>
</template>

<style scoped>
/* ==================== CARRERAS (tarjetas) ==================== */
.ci-carreras {
    display: grid;
    /* Todas las carreras en una sola línea (en pantallas angostas pasan a 2 columnas). */
    grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
    grid-auto-flow: column;
    gap: 10px;
}
@media (max-width: 900px) {
    .ci-carreras { grid-auto-flow: row; grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

.ci-carrera {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 58px;
    padding: 10px 30px 10px 12px;
    border-radius: 12px;
    text-align: left;
    border: 1.5px solid var(--sains-line, #e2e8f0);
    background: #fff;
    color: #0f172a;
    cursor: pointer;
    transition: border-color .15s ease, background .15s ease, box-shadow .15s ease, transform .15s ease;
}
.ci-carrera:hover:not(:disabled) {
    border-color: #9db4d8;
    transform: translateY(-1px);
    box-shadow: 0 10px 22px -16px rgba(14, 45, 102, .45);
}
.ci-carrera.on {
    border-color: var(--sains-primary);
    background: linear-gradient(135deg, #eef3f9 0%, #fff 70%);
    box-shadow: 0 0 0 3px rgba(24, 81, 173, .12);
}
.ci-carrera:disabled { cursor: not-allowed; opacity: .6; }

.ci-carrera__ic {
    flex: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 15px;
    color: var(--sains-primary);
    background: #eef3f9;
    transition: background .15s ease, color .15s ease;
}
.ci-carrera.on .ci-carrera__ic {
    color: #fff;
    background: linear-gradient(135deg, #0e2d66, #1851ad);
}
.ci-carrera__txt { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.ci-carrera__txt b { font-size: 13px; font-weight: 700; line-height: 1.25; }
.ci-carrera__check {
    position: absolute;
    top: 8px;
    right: 8px;
    font-size: 15px;
    color: var(--sains-primary);
    opacity: 0;
    transform: scale(.6);
    transition: opacity .15s ease, transform .15s ease;
}
.ci-carrera.on .ci-carrera__check { opacity: 1; transform: none; }

/* ==================== DOMICILIO ==================== */
.ci-cp-hint { margin-top: 4px; font-size: 12px; color: #94a3b8; line-height: 1.35; }
.ci-cp-hint.is-ok { color: #16a34a; }
.ci-cp-hint.is-no { color: #b45309; }
.ci-auto :deep(.ant-select-selector),
.ci-auto.ant-input,
:deep(.ci-auto.ant-input) { background: #f6faf7 !important; }

/* ==================== CURP ==================== */
.ci-curp :deep(input),
.ci-curp {
    text-transform: uppercase;
    letter-spacing: .04em;
}
</style>
