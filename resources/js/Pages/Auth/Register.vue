<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import {
    MailOutlined, LockOutlined, UserAddOutlined, GoogleOutlined, LoginOutlined,
} from '@ant-design/icons-vue';
import AuthShell from '@/Components/AuthShell.vue';
import { message } from '@/lib/notify';

const form = useForm({ email: '', password: '', password_confirmation: '' });
const acepta = ref(false);

const fuerza = computed(() => {
    const p = form.password || '';
    let s = 0;
    if (p.length >= 6) s++;
    if (p.length >= 10) s++;
    if (/[0-9]/.test(p) && /[a-zA-Z]/.test(p)) s++;
    if (/[^a-zA-Z0-9]/.test(p)) s++;
    return Math.min(s, 4);
});
const fuerzaTxt = computed(() => ['', 'Débil', 'Aceptable', 'Buena', 'Excelente'][fuerza.value]);
const fuerzaColor = computed(() => ['#e2e8f0', '#ef4444', '#f59e0b', '#3b82f6', '#16a34a'][fuerza.value]);

function submit() {
    if (!acepta.value) {
        message.warning('Debes aceptar los términos y condiciones.');
        return;
    }
    form.post(route('register'), {
        onSuccess: () => message.success('¡Cuenta creada! Completa tu perfil para empezar.'),
        onError: (e) => {
            form.reset('password', 'password_confirmation');
            message.error(e.email || e.password || 'No pudimos crear tu cuenta. Revisa los datos.');
        },
    });
}
</script>

<template>
    <AuthShell title="Crear cuenta" class="auth-shell-custom">
        <template #title>Crea tu cuenta gratis</template>
        <template #subtitle>Empieza tu preparación en minutos</template>

        <a-form layout="vertical" class="au-form" @submitcapture.prevent>
            <a-form-item :validate-status="form.errors.email ? 'error' : ''" :help="form.errors.email">
                <a-input v-model:value="form.email" type="email" size="large" placeholder="Correo" autocomplete="email">
                    <template #prefix><MailOutlined /></template>
                </a-input>
            </a-form-item>

            <a-form-item :validate-status="form.errors.password ? 'error' : ''" :help="form.errors.password">
                <a-input-password v-model:value="form.password" size="large" placeholder="Contraseña (mín. 6)" autocomplete="new-password">
                    <template #prefix><LockOutlined /></template>
                </a-input-password>
                <div v-if="form.password" class="au-strength">
                    <span v-for="n in 4" :key="n" :style="{ background: n <= fuerza ? fuerzaColor : '#e2e8f0' }"></span>
                    <small :style="{ color: fuerzaColor }">{{ fuerzaTxt }}</small>
                </div>
            </a-form-item>

            <a-form-item :validate-status="form.errors.password_confirmation ? 'error' : ''" :help="form.errors.password_confirmation">
                <a-input-password v-model:value="form.password_confirmation" size="large" placeholder="Confirmar contraseña" autocomplete="new-password" @press-enter="submit">
                    <template #prefix><LockOutlined /></template>
                </a-input-password>
            </a-form-item>

            <div class="au-terms">
                <a-checkbox v-model:checked="acepta" />
                <span @click="acepta = !acepta">
                    Acepto los
                    <a :href="route('terms')" target="_blank" class="au-link" @click.stop>Términos y condiciones</a>
                </span>
            </div>

            <a-button type="primary" size="large" block :loading="form.processing" @click="submit">
                <template #icon><UserAddOutlined /></template>
                Crear cuenta gratis
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
            ¿Ya tienes cuenta?
            <Link :href="route('login')" class="au-link au-link--strong">
                <LoginOutlined /> Iniciar sesión
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
    width: 260px !important;         /* 👈 Más grande aún */
    height: auto !important;
    max-width: 100%;
    transform: scale(1.8);            /* 👈 Escala visual extra */
    transform-origin: center;
    margin: 0 auto 20px !important;
    display: block;
}

/* ===== Enlaces SIN subrayado ===== */
.au-link {
    color: #1d4ed8;
    font-weight: 600;
    text-decoration: none !important;
}
.au-link:hover,
.au-link:focus,
.au-link:visited {
    color: #1e3a8a;
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

/* ===== Botón Google alineado ===== */
.au-google {
    color: #334155;
    display: flex !important;
    align-items: center;
    justify-content: center;
    padding: 0 16px;
    text-decoration: none !important;
}
.au-google:hover {
    border-color: #1d4ed8;
    color: #1e3a8a;
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

/* ===== Términos ===== */
.au-terms {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 2px 0 16px;
    font-size: 13px;
    color: #475569;
    cursor: pointer;
    user-select: none;
}
.au-terms :deep(.ant-checkbox) { top: 0; }
.au-terms :deep(.ant-checkbox + span) { padding: 0; }
.au-terms span { line-height: 1.4; }

/* ===== Barra de fuerza ===== */
.au-strength { display: flex; align-items: center; gap: 4px; margin-top: 8px; }
.au-strength span { height: 4px; flex: 1; border-radius: 999px; transition: background .2s ease; }
.au-strength small { font-size: 11px; font-weight: 600; flex: none; margin-left: 4px; }
</style>