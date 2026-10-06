<script setup>
import { useForm } from '@inertiajs/vue3';
import { UserOutlined } from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';
import PhoneInput from '@/Components/PhoneInput.vue';
import DateField from '@/Components/DateField.vue';
import { message } from '@/lib/notify';

const props = defineProps({
    correo: { type: String, default: '' },
});

const form = useForm({
    nombre: '', paterno: '', materno: '',
    telefono: '', telefono_casa: '',
    fecha_nacimiento: null, sexo: undefined,
    cupon: '',
});

function submit() {
    if (!form.nombre || !form.paterno || !form.telefono || !form.fecha_nacimiento || !form.sexo) {
        message.warning('Completa los campos obligatorios.');
        return;
    }
    form.post(route('estudiante.completar.perfil'));
}
</script>

<template>
    <EstudianteLayout title="Completar perfil">
        <section class="sains-hero">
            <div class="sains-hero__grid">
                <div>
                    <span class="sains-hero__eyebrow"><UserOutlined /> Bienvenido a SAINS</span>
                    <h1 class="sains-hero__title">¡Casi listo!</h1>
                    <p class="sains-hero__sub">Completa estos datos una sola vez para comenzar tu preparación.</p>
                </div>
            </div>
        </section>

        <a-card :bordered="false" style="max-width: 820px">
            <a-form layout="vertical">
                <a-divider orientation="left">Información personal</a-divider>
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="12">
                        <a-form-item label="Nombre(s)" required :validate-status="form.errors.nombre ? 'error' : ''" :help="form.errors.nombre">
                            <a-input v-model:value="form.nombre" placeholder="Ej: Juan Carlos" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12">
                        <a-form-item label="Apellido paterno" required :validate-status="form.errors.paterno ? 'error' : ''" :help="form.errors.paterno">
                            <a-input v-model:value="form.paterno" placeholder="Ej: Rodríguez" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12">
                        <a-form-item label="Apellido materno">
                            <a-input v-model:value="form.materno" placeholder="Ej: Pérez" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12">
                        <a-form-item label="Teléfono celular" required :validate-status="form.errors.telefono ? 'error' : ''" :help="form.errors.telefono">
                            <PhoneInput v-model:value="form.telefono" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12">
                        <a-form-item label="Teléfono de casa">
                            <PhoneInput v-model:value="form.telefono_casa" placeholder="Opcional" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12">
                        <a-form-item label="Fecha de nacimiento" required :validate-status="form.errors.fecha_nacimiento ? 'error' : ''" :help="form.errors.fecha_nacimiento">
                            <DateField v-model:value="form.fecha_nacimiento" limite="nacimiento" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12">
                        <a-form-item label="Sexo" required :validate-status="form.errors.sexo ? 'error' : ''" :help="form.errors.sexo">
                            <a-select v-model:value="form.sexo" placeholder="Selecciona" :options="[
                                { value: 'M', label: 'Masculino' },
                                { value: 'F', label: 'Femenino' },
                            ]" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="12">
                        <a-form-item label="Correo electrónico">
                            <a-input :value="correo" disabled />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-divider orientation="left">Cupón de descuento (opcional)</a-divider>
                <a-form-item label="Código de cupón" :validate-status="form.errors.cupon ? 'error' : ''" :help="form.errors.cupon">
                    <a-input v-model:value="form.cupon" placeholder="Ej: SAINS2026" style="max-width: 260px" />
                </a-form-item>

                <div class="sains-section-actions">
                    <a-button type="primary" size="large" :loading="form.processing" @click="submit">Guardar perfil</a-button>
                </div>
            </a-form>
        </a-card>
    </EstudianteLayout>
</template>