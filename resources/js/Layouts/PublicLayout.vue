<script setup>
import { computed, onMounted, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { antdTheme, antdLocale } from '@/theme';
import { showFlash } from '@/lib/notify';
import PublicNav from '@/Components/PublicNav.vue';

defineProps({ title: { type: String, default: null } });

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

// CTAs dentro de las páginas (hero, plan premium…): navegan a login/registro.
function openAuth(v = 'login') {
    router.visit(route(v === 'register' ? 'registro' : 'login'));
}

watch(() => page.props.flash, showFlash, { deep: true });
onMounted(() => showFlash(page.props.flash));
</script>

<template>
    <a-config-provider :theme="antdTheme" :locale="antdLocale">
        <Head :title="title" />

        <PublicNav />

        <main>
            <slot :open-auth="openAuth" :user="user" />
        </main>

        <footer class="pub-footer" id="contacto">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-4">
                        <img src="/images/logo_u.png" alt="SAINS" height="50" class="mb-3 pub-footer-logo" />
                        <p class="small text-white-50">SAINS es una empresa graduada de la Incubadora de Base Tecnológica MIDAS UAEM y galardonada con el Premio Nacional Cuezcomate 2016.</p>
                    </div>
                    <div class="col-md-4">
                        <h5 class="mb-3">Enlaces rápidos</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="#nosotros">Nosotros</a></li>
                            <li class="mb-2"><a href="#metodo">Método</a></li>
                            <li class="mb-2"><a href="#docentes">Docentes</a></li>
                            <li class="mb-2"><a href="#plan">Plan Premium</a></li>
                            <li><Link :href="route('terms')">Términos y condiciones</Link></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h5 class="mb-3">Contacto</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fab fa-whatsapp me-2"></i> <a href="https://wa.me/527771886018">777 188 6018</a></li>
                            <li class="mb-2"><i class="fab fa-whatsapp me-2"></i> <a href="https://wa.me/527772505603">777 250 5603</a></li>
                            <li><i class="fas fa-envelope me-2"></i> <a href="mailto:ayuda@ingresoalauni.com">ayuda@ingresoalauni.com</a></li>
                        </ul>
                    </div>
                </div>
                <hr class="my-4 border-light opacity-25" />
                <p class="text-center small text-white-50 mb-0">© {{ new Date().getFullYear() }} SAINS. Todos los derechos reservados. Convocatoria 2026</p>
            </div>
        </footer>

    </a-config-provider>
</template>

<style scoped>
.pub-footer {
    background: #0f172a;
    color: #fff;
    padding: 4rem 0 2rem;
}

.pub-footer a {
    color: rgba(255, 255, 255, 0.6);
    text-decoration: none;
}

.pub-footer a:hover {
    color: #fff;
}

.pub-footer-logo {
    filter: brightness(0) invert(1);
}

</style>
