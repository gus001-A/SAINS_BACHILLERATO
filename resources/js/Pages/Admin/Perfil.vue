<script setup>
import { useForm } from '@inertiajs/vue3';
import { UserOutlined, LockOutlined, SaveOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import PhoneInput from '@/Components/PhoneInput.vue';
import DateField from '@/Components/DateField.vue';

const props = defineProps({
    perfil: { type: Object, required: true },
});

const datos = useForm({
    email: props.perfil.correo ?? '',
    nombre: props.perfil.nombre ?? '',
    apellido_paterno: props.perfil.apellido_paterno ?? '',
    apellido_materno: props.perfil.apellido_materno ?? '',
    telefono: props.perfil.telefono ?? '',
    sexo: props.perfil.sexo || undefined,
    fecha_nacimiento: props.perfil.fecha_nacimiento,
});

const pass = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const sexoOpts = [{ value: 'M', label: 'Masculino' }, { value: 'F', label: 'Femenino' }];

function guardarDatos() {
    datos.put(route('admin.perfil.update'), { preserveScroll: true });
}
function guardarPass() {
    pass.put(route('admin.perfil.password'), {
        preserveScroll: true,
        onSuccess: () => pass.reset(),
    });
}
</script>

<template>
    <AdminLayout title="Mi perfil">
        <PageHead title="Mi perfil" subtitle="Datos de tu cuenta de administrador" :icon="UserOutlined" />

        <a-row :gutter="16">
            <a-col :xs="24" :lg="14">
                <a-card :bordered="false" title="Información personal">
                    <a-form layout="vertical">
                        <a-row :gutter="16">
                            <a-col :xs="24" :md="8">
                                <a-form-item label="Nombre" required :validate-status="datos.errors.nombre ? 'error' : undefined" :help="datos.errors.nombre">
                                    <a-input v-model:value="datos.nombre" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :md="8">
                                <a-form-item label="Apellido paterno" required :validate-status="datos.errors.apellido_paterno ? 'error' : undefined" :help="datos.errors.apellido_paterno">
                                    <a-input v-model:value="datos.apellido_paterno" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :md="8">
                                <a-form-item label="Apellido materno">
                                    <a-input v-model:value="datos.apellido_materno" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :md="12">
                                <a-form-item label="Correo" required :validate-status="datos.errors.email ? 'error' : undefined" :help="datos.errors.email">
                                    <a-input v-model:value="datos.email" type="email" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :md="6">
                                <a-form-item label="Teléfono">
                                    <PhoneInput v-model:value="datos.telefono" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :md="6">
                                <a-form-item label="Sexo">
                                    <a-select v-model:value="datos.sexo" allow-clear :options="sexoOpts" />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :md="12">
                                <a-form-item label="Fecha de nacimiento">
                                    <DateField v-model:value="datos.fecha_nacimiento" limite="nacimiento" />
                                </a-form-item>
                            </a-col>
                        </a-row>
                        <a-button type="primary" :loading="datos.processing" @click="guardarDatos">
                            <template #icon><SaveOutlined /></template> Guardar cambios
                        </a-button>
                    </a-form>
                </a-card>
            </a-col>

            <a-col :xs="24" :lg="10">
                <a-card :bordered="false" title="Cambiar contraseña">
                    <a-form layout="vertical">
                        <a-form-item label="Contraseña actual" required :validate-status="pass.errors.current_password ? 'error' : undefined" :help="pass.errors.current_password">
                            <a-input-password v-model:value="pass.current_password">
                                <template #prefix><LockOutlined /></template>
                            </a-input-password>
                        </a-form-item>
                        <a-form-item label="Nueva contraseña" required :validate-status="pass.errors.new_password ? 'error' : undefined" :help="pass.errors.new_password">
                            <a-input-password v-model:value="pass.new_password" />
                        </a-form-item>
                        <a-form-item label="Confirmar nueva contraseña" required>
                            <a-input-password v-model:value="pass.new_password_confirmation" />
                        </a-form-item>
                        <a-button :loading="pass.processing" @click="guardarPass">
                            <template #icon><LockOutlined /></template> Actualizar contraseña
                        </a-button>
                    </a-form>
                </a-card>
            </a-col>
        </a-row>
    </AdminLayout>
</template>
