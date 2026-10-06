<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { MailOutlined, SendOutlined, ArrowLeftOutlined, CheckCircleFilled } from '@ant-design/icons-vue';
import AuthShell from '@/Components/AuthShell.vue';
import { message } from '@/lib/notify';

const email = ref('');
const enviando = ref(false);
const listo = ref(false);
const errorMsg = ref('');

async function submit() {
    errorMsg.value = '';
    if (!email.value) { errorMsg.value = 'Ingresa tu correo.'; return; }
    enviando.value = true;
    try {
        const { data } = await axios.post(route('password.email'), { email: email.value });
        if (data.success) {
            listo.value = true;
            message.success(data.message || 'Revisa tu bandeja de entrada.');
        } else {
            errorMsg.value = data.message || 'No se pudo enviar el correo.';
        }
    } catch (e) {
        errorMsg.value = e.response?.data?.message || e.response?.data?.errors?.email?.[0] || 'Error de conexión. Intenta de nuevo.';
    } finally {
        enviando.value = false;
    }
}
</script>

<template>
    <AuthShell title="Recuperar contraseña">
        <template #title>Recuperar contraseña</template>
        <template #subtitle>{{ listo ? 'Correo enviado' : 'Te enviaremos un enlace para restablecerla' }}</template>

        <template v-if="!listo">
            <a-form layout="vertical" class="au-form" @submitcapture.prevent>
                <a-form-item :validate-status="errorMsg ? 'error' : ''" :help="errorMsg">
                    <a-input v-model:value="email" type="email" size="large" placeholder="Correo" autocomplete="email" @press-enter="submit">
                        <template #prefix><MailOutlined /></template>
                    </a-input>
                </a-form-item>
                <a-button type="primary" size="large" block :loading="enviando" @click="submit">
                    <template #icon><SendOutlined /></template>
                    Enviar enlace
                </a-button>
            </a-form>
        </template>

        <template v-else>
            <div class="au-done">
                <CheckCircleFilled />
                <p>Si el correo existe en SAINS, recibirás un enlace para restablecer tu contraseña en unos minutos.</p>
            </div>
        </template>

        <template #footer>
            <Link :href="route('login')" class="au-link au-link--strong">
                <ArrowLeftOutlined /> Volver al inicio de sesión
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
.au-done p { font-size: 13px; color: #64748b; line-height: 1.5; margin: 0; }
</style>
