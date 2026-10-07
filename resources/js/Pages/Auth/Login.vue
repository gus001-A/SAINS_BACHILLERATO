<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import {
    MailOutlined, LockOutlined, LoginOutlined, GoogleOutlined, UserAddOutlined,
} from '@ant-design/icons-vue';
import AuthShell from '@/Components/AuthShell.vue';
import { message } from '@/lib/notify';

const form = useForm({ email: '', password: '', remember: false });

function submit() {
    form.post(route('login'), {
        onSuccess: () => message.success('¡Bienvenido de vuelta!'),
        onError: (e) => {
            form.reset('password');
            message.error(e.email || e.password || 'No pudimos iniciar tu sesión. Revisa tus datos.');
        },
    });
}
</script>

<template>
    <AuthShell title="Iniciar sesión" class="auth-shell-custom">
        <template #title>Bienvenido de vuelta</template>
        <template #subtitle>Ingresa a tu cuenta para continuar</template>

        <a-form layout="vertical" class="au-form" @submitcapture.prevent>
            <a-form-item :validate-status="form.errors.email ? 'error' : ''" :help="form.errors.email">
                <a-input v-model:value="form.email" type="email" size="large" placeholder="Correo" autocomplete="email"
                    @press-enter="submit">
                    <template #prefix>
                        <MailOutlined />
                    </template>
                </a-input>
            </a-form-item>

            <a-form-item :validate-status="form.errors.password ? 'error' : ''" :help="form.errors.password">
                <a-input-password v-model:value="form.password" size="large" placeholder="Contraseña"
                    autocomplete="current-password" @press-enter="submit">
                    <template #prefix>
                        <LockOutlined />
                    </template>
                </a-input-password>
            </a-form-item>

            <div class="au-row">
                <a-checkbox v-model:checked="form.remember">Recordarme</a-checkbox>
                <Link :href="route('password.request')" class="au-link">¿Olvidaste tu contraseña?</Link>
            </div>

            <a-button type="primary" size="large" block :loading="form.processing" @click="submit">
                <template #icon>
                    <LoginOutlined />
                </template>
                Iniciar sesión
            </a-button>
        </a-form>

        <template #google>
            <a-button size="large" block class="au-google" :href="route('auth.google')">
                <span class="au-google-content">
                    <GoogleOutlined class="au-google-icon" />
                    <span>Continuar con Google</span>
                </span>
            </a-button>
        </template>

        <template #footer>
            ¿No tienes cuenta?
            <Link :href="route('registro')" class="au-link au-link--strong">
                <UserAddOutlined /> Crear cuenta gratis
            </Link>
        </template>
    </AuthShell>
</template>

<style scoped>
/* ===== LOGO SAINS GIGANTE 🔥 ===== */
.auth-shell-custom :deep(.logo),
.auth-shell-custom :deep(.sains-logo),
.auth-shell-custom :deep(img[alt*="SAINS" i]),
.auth-shell-custom :deep(svg[class*="logo" i]),
.auth-shell-custom :deep(.auth-logo),
.auth-shell-custom :deep(.brand-logo) {
    width: 560px !important;
    /* 👈 Más grande aún */
    height: auto !important;
    max-width: 100%;
    transform: scale(1.8);
    /* 👈 Escala visual extra */
    transform-origin: center;
    margin: 0 auto 20px !important;
    display: block;
}

/* ===== Botón Google alineado ===== */
.au-google {
    color: #334155;
    display: flex !important;
    align-items: center;
    justify-content: center;
    padding: 0 16px;
    text-decoration: none !important;
    /* 👈 Sin subrayado */
}

.au-google:hover {
    border-color: #15509b;
    color: #163964;
    text-decoration: none !important;
}

.au-google-content {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    line-height: 1;
}

.au-google-icon {
    font-size: 18px;
    display: inline-flex;
    align-items: center;
    vertical-align: middle;
}

/* ===== Estilos generales ===== */
.au-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: -2px 0 16px;
    font-size: 13px;
}

/* 👇 Enlaces SIN subrayado */
.au-link {
    color: #15509b;
    font-weight: 600;
    text-decoration: none !important;
}

.au-link:hover,
.au-link:focus,
.au-link:visited {
    color: #163964;
    text-decoration: none !important;
}

.au-link--strong {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    text-decoration: none !important;
}

/* Por si el subrayado viene de estilos globales de <a> */
.auth-shell-custom :deep(a),
.auth-shell-custom a {
    text-decoration: none !important;
}
</style>