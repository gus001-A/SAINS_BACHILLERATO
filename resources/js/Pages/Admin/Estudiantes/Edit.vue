<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { TeamOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import FormPage from '@/Components/FormPage.vue';
import EstudianteForm from './EstudianteForm.vue';

const props = defineProps({
    estudiante: { type: Object, required: true },
    opciones: { type: Object, required: true },
});

const e = props.estudiante;
const form = useForm({
    nombre: e.nombre ?? '',
    paterno: e.paterno ?? '',
    materno: e.materno ?? '',
    fecha_nacimiento: e.fecha_nacimiento,
    sexo: e.sexo || undefined,
    telefono: e.telefono ?? '',
    telefono_casa: e.telefono_casa ?? '',
    email: e.correo ?? '',
    password: '',
    password_confirmation: '',
    plan_activo: !!e.plan_activo,
    cupon_id: undefined,
    carrera_id: e.carrera_id ?? undefined, curp: e.curp ?? '', calle_numero: e.calle_numero ?? '',
    colonia: e.colonia ?? '', codigo_postal: e.codigo_postal ?? '', municipio: e.municipio ?? '',
    entidad_federativa: e.entidad_federativa ?? undefined,
    reiniciar_candado: false,
});

const back = () => router.visit(route('admin.estudiantes.index'));
const submit = () => form.put(route('admin.estudiantes.update', e.id));
</script>

<template>
    <AdminLayout title="Editar estudiante">
        <PageHead :title="`Editar: ${e.nombre} ${e.paterno}`" subtitle="Actualizar datos del alumno" :icon="TeamOutlined" back @back="back" />
        <FormPage submit-label="Guardar cambios" :processing="form.processing" @submit="submit" @cancel="back">
            <a-alert v-if="e.cupon" type="info" show-icon style="margin-bottom: 16px"
                :message="`Cupón aplicado actualmente: ${e.cupon}`" />
            <EstudianteForm :form="form" :opciones="opciones" modo="edit" />
            <a-alert v-if="e.candado" :type="e.candado.restantes_total === 0 ? 'warning' : 'info'" show-icon style="margin-top: 8px"
                :message="`Candado de datos del alumno: ${e.candado.total} de ${e.candado.max_total} cambios usados`
                    + (e.candado.restantes_total === 0 ? ' · sus datos están bloqueados' : '')">
                <template #description>
                    <a-checkbox v-model:checked="form.reiniciar_candado">
                        Reiniciar el candado (le devuelve sus {{ e.candado.max_total }} cambios al guardar)
                    </a-checkbox>
                </template>
            </a-alert>
        </FormPage>
    </AdminLayout>
</template>
