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
            <div class="pub-footer__glow" aria-hidden="true"></div>
            <div class="container pub-footer__inner">
                <div class="row g-4">
                    <div class="col-lg-5 col-md-12">
                        <span class="pub-footer-logo"><img src="/images/bachillerato-nacional-sm.png" alt="Bachillerato Nacional SAINS" /></span>
                        <p class="pub-footer__about">SAINS es una empresa graduada de la Incubadora de Base Tecnológica MIDAS UAEM y galardonada con el Premio Nacional Cuezcomate 2016.</p>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <h5 class="pub-footer__h">Enlaces rápidos</h5>
                        <ul class="pub-footer__links">
                            <li><a href="#nosotros">Nosotros</a></li>
                            <li><a href="#metodo">Método</a></li>
                            <li><a href="#docentes">Docentes</a></li>
                            <li><a href="#plan">Plan Premium</a></li>
                            <li><Link :href="route('terms')">Términos y condiciones</Link></li>
                        </ul>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <h5 class="pub-footer__h">Contacto</h5>
                        <ul class="pub-footer__contact">
                            <li>
                                <a href="https://wa.me/527771886018" target="_blank" rel="noopener">
                                    <span class="pub-footer__ico pub-footer__ico--wa"><i class="fab fa-whatsapp"></i></span>
                                    <span><small>WhatsApp</small>777 188 6018</span>
                                </a>
                            </li>
                            <li>
                                <a href="https://wa.me/527772505603" target="_blank" rel="noopener">
                                    <span class="pub-footer__ico pub-footer__ico--wa"><i class="fab fa-whatsapp"></i></span>
                                    <span><small>WhatsApp</small>777 250 5603</span>
                                </a>
                            </li>
                            <li>
                                <a href="mailto:sains.bachillerato@gmail.com">
                                    <span class="pub-footer__ico"><i class="fas fa-envelope"></i></span>
                                    <span><small>Correo</small>sains.bachillerato@gmail.com</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="pub-footer__bottom">
                    <span>© {{ new Date().getFullYear() }} SAINS. Todos los derechos reservados.</span>
                    <span class="pub-footer__badge">Convocatoria 2026</span>
                </div>
            </div>
        </footer>

    </a-config-provider>
</template>

<style scoped>
.pub-footer {
    position: relative;
    overflow: hidden;
    background: #0b1222;
    color: #fff;
    padding: 3.5rem 0 1.75rem;
}
.pub-footer__glow {
    position: absolute;
    top: -260px;
    right: -160px;
    width: 560px;
    height: 560px;
    background: radial-gradient(circle, rgba(24, 81, 173, .35), transparent 65%);
    pointer-events: none;
}
.pub-footer__inner { position: relative; }


.pub-footer-logo {
    display: inline-block;
    padding: 10px 16px;
    margin-bottom: 1rem;
    border-radius: 14px;
    background: #fff;
}
.pub-footer-logo img { height: 52px; display: block; }
.pub-footer__about { color: rgba(255, 255, 255, .55); font-size: .9rem; line-height: 1.65; max-width: 380px; margin: 0; }

.pub-footer__h {
    font-size: .8rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, .9);
    margin-bottom: 1.1rem;
}

.pub-footer__links { list-style: none; padding: 0; margin: 0; display: grid; gap: .6rem; }
.pub-footer__links a {
    color: rgba(255, 255, 255, .6);
    text-decoration: none;
    font-size: .93rem;
    transition: color .15s ease, padding-left .15s ease;
}
.pub-footer__links a:hover { color: #fff; padding-left: 4px; }

.pub-footer__contact { list-style: none; padding: 0; margin: 0; display: grid; gap: .75rem; }
.pub-footer__contact a {
    display: flex;
    align-items: center;
    gap: .8rem;
    color: #fff;
    text-decoration: none;
    font-size: .95rem;
    word-break: break-word;
}
.pub-footer__contact small {
    display: block;
    font-size: .72rem;
    color: rgba(255, 255, 255, .5);
    line-height: 1.2;
}
.pub-footer__ico {
    flex: none;
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(255, 255, 255, .08);
    color: #9cbae2;
    transition: background .15s ease;
}
.pub-footer__ico--wa { color: #4ade80; }
.pub-footer__contact a:hover .pub-footer__ico { background: rgba(255, 255, 255, .16); }

.pub-footer__bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    flex-wrap: wrap;
    margin-top: 2.75rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, .1);
    color: rgba(255, 255, 255, .5);
    font-size: .85rem;
}
.pub-footer__badge {
    padding: .25rem .75rem;
    border-radius: 999px;
    background: rgba(24, 81, 173, .25);
    color: #c5d5e9;
    font-size: .78rem;
    font-weight: 600;
}

@media (max-width: 576px) {
    .pub-footer__bottom { justify-content: center; text-align: center; }
}

</style>
