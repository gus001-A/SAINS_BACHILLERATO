<script setup>
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { MailOutlined, LockOutlined, CheckCircleFilled, LoginOutlined } from '@ant-design/icons-vue';
import AuthShell from '@/Components/AuthShell.vue';
import { message } from '@/lib/notify';

const props = defineProps({
    token: { type: String, required: true },
    email: { type: String, default: '' },
});

const form = reactive({
    email: props.email || '',
    password: '',
    password_confirmation: '',
    token: props.token,
});
const errors = ref({});
const loading = ref(false);
const done = ref(false);

const err = (k) => (errors.value[k] ? errors.value[k][0] : '');

async function submit() {
    errors.value = {};
    if (!form.email) { errors.value.email = ['Ingresa tu correo.']; return; }
    if (form.password.length < 6) { errors.value.password = ['Mínimo 6 caracteres.']; return; }
    if (form.password !== form.password_confirmation) {
        errors.value.password_confirmation = ['Las contraseñas no coinciden.'];
        return;
    }
    loading.value = true;
    try {
        const { data } = await axios.post(route('password.update'), form);
        if (data.success) {
            done.value = true;
            message.success('Contraseña actualizada.');
        } else {
            message.error(data.message || 'No se pudo actualizar la contraseña.');
        }
    } catch (e) {
        if (e.response?.status === 422) errors.value = e.response.data.errors || {};
        message.error(e.response?.data?.message || 'Ocurrió un error. Intenta de nuevo.');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <AuthShell title="Restablecer contraseña">
        <template #title>{{ done ? '¡Listo!' : 'Nueva contraseña' }}</template>
        <template #subtitle>{{ done ? 'Contraseña actualizada' : 'Elige una nueva contraseña para tu cuenta' }}</template>

        <template v-if="!done">
            <a-form layout="vertical" class="au-form" @submitcapture.prevent>
                <a-form-item :validate-status="err('email') ? 'error' : ''" :help="err('email')">
                    <a-input v-model:value="form.email" type="email" size="large" placeholder="Correo">
                        <template #prefix><MailOutlined /></template>
                    </a-input>
                </a-form-item>
                <a-form-item :validate-status="err('password') ? 'error' : ''" :help="err('password')">
                    <a-input-password v-model:value="form.password" size="large" placeholder="Nueva contraseña (mín. 6)">
                        <template #prefix><LockOutlined /></template>
                    </a-input-password>
                </a-form-item>
                <a-form-item :validate-status="err('password_confirmation') ? 'error' : ''" :help="err('password_confirmation')">
                    <a-input-password v-model:value="form.password_confirmation" size="large" placeholder="Confirmar contraseña" @press-enter="submit">
                        <template #prefix><LockOutlined /></template>
                    </a-input-password>
                </a-form-item>
                <a-button type="primary" size="large" block :loading="loading" @click="submit">
                    Actualizar contraseña
                </a-button>
            </a-form>
        </template>

        <template v-else>
            <div class="au-done">
                <CheckCircleFilled />
                <p>Ya puedes iniciar sesión con tu nueva contraseña.</p>
                <a-button type="primary" size="large" block @click="router.visit(route('login'))">Iniciar sesión</a-button>
            </div>
        </template>

        <template #footer>
            <Link :href="route('login')" class="au-link au-link--strong">
                <LoginOutlined /> Ir al inicio de sesión
            </Link>
        </template>
    </AuthShell>
</template>

<style scoped>
.au-link { color: #1d4ed8; font-weight: 600; }
.au-link:hover { color: #1e3a8a; }
.au-link--strong { display: inline-flex; align-items: center; gap: 5px; }
.au-done { display: flex; flex-direction: column; align-items: center; gap: 10px; text-align: center; padding: 6px 0; }
.au-done :deep(.anticon) { font-size: 42px; color: #16a34a; }
.au-done p { font-size: 13px; color: #64748b; margin: 0 0 6px; }
</style>
