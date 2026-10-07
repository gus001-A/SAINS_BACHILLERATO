<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    BookOutlined, FilePdfOutlined, FileImageOutlined, FileOutlined, LinkOutlined,
    EyeOutlined, DownloadOutlined, ExportOutlined, SearchOutlined, UserOutlined,
} from '@ant-design/icons-vue';
import EstudianteLayout from '@/Layouts/EstudianteLayout.vue';

const props = defineProps({
    carrera: { type: Object, default: null },
    guias: { type: Array, default: () => [] },
});

const busqueda = ref('');
const filtradas = computed(() => {
    const q = busqueda.value.trim().toLowerCase();
    if (!q) return props.guias;
    return props.guias.filter((g) => `${g.titulo} ${g.descripcion ?? ''}`.toLowerCase().includes(q));
});
// Dos bloques: tronco común (todos) y las de su carrera.
const secciones = computed(() => [
    { key: 'tronco', titulo: 'Tronco común', sub: 'Guías para todas las carreras', icono: 'fa-layer-group',
        items: filtradas.value.filter((g) => g.tronco_comun) },
    { key: 'carrera', titulo: props.carrera?.nombre ?? 'Mi carrera', sub: 'Guías de tu formación profesional',
        icono: props.carrera?.icono ?? 'fa-graduation-cap', items: filtradas.value.filter((g) => !g.tronco_comun) },
].filter((s) => s.items.length));

const meta = {
    pdf: { icon: FilePdfOutlined, label: 'PDF', cls: 'is-pdf' },
    imagen: { icon: FileImageOutlined, label: 'Imagen', cls: 'is-img' },
    documento: { icon: FileOutlined, label: 'Documento', cls: 'is-doc' },
    enlace: { icon: LinkOutlined, label: 'Enlace', cls: 'is-link' },
};

const visor = ref(null);
function abrir(g) {
    if (g.tipo === 'pdf' || g.tipo === 'imagen') visor.value = g;
    else window.open(g.url, '_blank', 'noopener');
}
</script>

<template>
    <EstudianteLayout title="Guías">
        <section class="sains-hero">
            <div class="sains-hero__grid">
                <div>
                    <span class="sains-hero__eyebrow"><BookOutlined /> Guías de estudio</span>
                    <h1 class="sains-hero__title">{{ carrera ? carrera.nombre : 'Guías' }}</h1>
                    <p class="sains-hero__sub">
                        {{ carrera ? 'Guías de tronco común y de tu carrera. Descárgalas o consúltalas en línea cuando quieras.' : 'Guías de tronco común. Elige tu carrera para ver también las de tu formación profesional.' }}
                    </p>
                </div>
                <div v-if="carrera" class="guias-hero__ic"><i class="fas" :class="carrera.icono"></i></div>
            </div>
        </section>

        <a-alert v-if="!carrera" type="info" show-icon style="margin-bottom: 16px"
            message="Aún no tienes una carrera asignada"
            description="Por ahora ves las guías de tronco común. Elige tu carrera en tu perfil para ver también las de tu carrera.">
            <template #action>
                <a-button type="primary" size="small" @click="router.visit(route('estudiante.perfil'))"><template #icon><UserOutlined /></template>Ir a mi perfil</a-button>
            </template>
        </a-alert>

        <div>
            <div class="guias-bar">
                <span class="guias-bar__count"><b>{{ guias.length }}</b> {{ guias.length === 1 ? 'guía disponible' : 'guías disponibles' }}</span>
                <a-input v-if="guias.length > 4" v-model:value="busqueda" allow-clear placeholder="Buscar guía…" style="max-width: 280px">
                    <template #prefix><SearchOutlined /></template>
                </a-input>
            </div>

            <a-card v-if="!guias.length" :bordered="false">
                <a-empty description="Todavía no hay guías publicadas. Vuelve pronto." />
            </a-card>

            <template v-else>
            <section v-for="sec in secciones" :key="sec.key" class="guias-sec">
            <h2 class="guias-sec__title">
                <span class="guias-sec__ic" :class="{ 'is-tronco': sec.key === 'tronco' }"><i class="fas" :class="sec.icono"></i></span>
                <span>{{ sec.titulo }}<small>{{ sec.sub }} · {{ sec.items.length }}</small></span>
            </h2>
            <div class="guias-grid">
                <article v-for="(g, i) in sec.items" :key="g.id" class="guia">
                    <div class="guia__top">
                        <span class="guia__num">{{ String(i + 1).padStart(2, '0') }}</span>
                        <span class="guia__tipo" :class="meta[g.tipo].cls"><component :is="meta[g.tipo].icon" /> {{ meta[g.tipo].label }}</span>
                    </div>
                    <h3 class="guia__title">{{ g.titulo }}</h3>
                    <p v-if="g.descripcion" class="guia__desc">{{ g.descripcion }}</p>
                    <div class="guia__actions">
                        <a-button type="primary" @click="abrir(g)">
                            <template #icon><EyeOutlined v-if="g.tipo === 'pdf' || g.tipo === 'imagen'" /><ExportOutlined v-else /></template>
                            {{ g.tipo === 'pdf' || g.tipo === 'imagen' ? 'Ver guía' : 'Abrir' }}
                        </a-button>
                        <a-button v-if="g.es_archivo" :href="g.descarga_url">
                            <template #icon><DownloadOutlined /></template>Descargar
                        </a-button>
                    </div>
                </article>
            </div>
            </section>
            <a-empty v-if="!filtradas.length" description="Ninguna guía coincide con tu búsqueda." />
            </template>
        </div>

        <a-modal :open="!!visor" :title="visor?.titulo" :footer="null" width="min(1000px, 96vw)" centered destroy-on-close
            @cancel="visor = null">
            <template v-if="visor">
                <iframe v-if="visor.tipo === 'pdf'" :src="visor.url" class="visor-pdf" :title="visor.titulo"></iframe>
                <img v-else :src="visor.url" :alt="visor.titulo" class="visor-img" />
                <div class="visor-foot">
                    <a-button :href="visor.descarga_url"><template #icon><DownloadOutlined /></template>Descargar</a-button>
                </div>
            </template>
        </a-modal>
    </EstudianteLayout>
</template>

<style scoped>
.guias-hero__ic {
    width: 84px; height: 84px; border-radius: 50%;
    display: grid; place-items: center; justify-self: end;
    border: 2px solid rgba(255, 255, 255, .7); background: rgba(255, 255, 255, .1);
    font-size: 34px; color: #fff;
}
.guias-bar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
.guias-bar__count { color: #64748b; }
.guias-bar__count b { color: #0f172a; }

.guias-sec { margin-bottom: 26px; }
.guias-sec__title { display: flex; align-items: center; gap: 12px; margin: 0 0 14px; font-size: 17px; font-weight: 700; color: #0f172a; }
.guias-sec__title small { display: block; font-size: 12px; font-weight: 500; color: #64748b; }
.guias-sec__ic {
    width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center; flex: none;
    color: #fff; font-size: 16px; background: linear-gradient(135deg, #0e2d66, #1851ad);
}
.guias-sec__ic.is-tronco { background: linear-gradient(135deg, #d99a00, #f5b301); }
.guias-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
.guia {
    display: flex; flex-direction: column;
    padding: 18px; border-radius: 16px; background: #fff; border: 1px solid var(--sains-line);
    box-shadow: var(--sains-shadow-sm);
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
.guia:hover { transform: translateY(-2px); box-shadow: var(--sains-shadow-md); border-color: #c5d5e9; }
.guia__top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.guia__num { font-size: 22px; font-weight: 800; color: #c5d5e9; letter-spacing: -.02em; }
.guia__tipo {
    display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px;
    font-size: 11.5px; font-weight: 700; background: #eef3f9; color: var(--sains-primary);
}
.guia__tipo.is-pdf { background: #fef2f2; color: #dc2626; }
.guia__tipo.is-img { background: #f0fdf4; color: #16a34a; }
.guia__title { margin: 0 0 6px; font-size: 15.5px; font-weight: 700; color: #0f172a; line-height: 1.35; }
.guia__desc { margin: 0 0 14px; font-size: 13px; color: #64748b; line-height: 1.5; }
.guia__actions { display: flex; gap: 8px; margin-top: auto; flex-wrap: wrap; }

.visor-pdf { width: 100%; height: 72vh; border: 0; border-radius: 10px; background: #f1f5f9; }
.visor-img { display: block; max-width: 100%; max-height: 72vh; margin: 0 auto; border-radius: 10px; }
.visor-foot { display: flex; justify-content: flex-end; margin-top: 12px; }
</style>
