<script setup>
import { router } from '@inertiajs/vue3';
import { TeamOutlined, DeleteOutlined, ExclamationCircleOutlined, MailOutlined, GoogleOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import { confirmDelete } from '@/lib/notify';

const props = defineProps({
    usuario: { type: Object, required: true },
});

function eliminar() {
    confirmDelete({
        title: '¿Eliminar este usuario?',
        content: `Se eliminará la cuenta ${props.usuario.correo}. Podrá volver a registrarse con el mismo correo.`,
        onOk: () => router.delete(route('admin.estudiantes.destroy', props.usuario.id)),
    });
}
</script>

<template>
    <AdminLayout title="Registro incompleto">
        <PageHead :title="usuario.correo" subtitle="Registro incompleto" :icon="TeamOutlined"
            back @back="router.visit(route('admin.estudiantes.index'))">
            <template #actions>
                <a-button danger @click="eliminar"><template #icon><DeleteOutlined /></template>Eliminar usuario</a-button>
            </template>
        </PageHead>

        <a-card :bordered="false" class="inc-card">
            <div class="inc">
                <span class="inc__ico"><ExclamationCircleOutlined /></span>
                <div class="inc__body">
                    <h3 class="inc__title">Este usuario no terminó su registro</h3>
                    <p class="inc__text">
                        Creó su cuenta pero nunca llenó sus datos de estudiante, por eso no tiene ficha, pagos ni documentos.
                        Puedes eliminar la cuenta; si la persona quiere entrar después, solo tiene que registrarse de nuevo.
                    </p>
                    <ul class="inc__meta">
                        <li><MailOutlined /> {{ usuario.correo }}</li>
                        <li v-if="usuario.con_google"><GoogleOutlined /> Se registró con Google</li>
                    </ul>
                    <a-button danger type="primary" @click="eliminar">
                        <template #icon><DeleteOutlined /></template>Eliminar usuario
                    </a-button>
                </div>
            </div>
        </a-card>
    </AdminLayout>
</template>

<style scoped>
.inc-card { max-width: 720px; }
.inc { display: flex; gap: 18px; align-items: flex-start; }
.inc__ico {
    flex: none;
    display: grid;
    place-items: center;
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: #fff7ed;
    color: #ea580c;
    font-size: 22px;
}
.inc__title { margin: 2px 0 6px; font-size: 17px; font-weight: 700; color: var(--sains-ink, #0f172a); }
.inc__text { margin: 0 0 14px; color: var(--sains-muted, #64748b); line-height: 1.6; }
.inc__meta { list-style: none; padding: 0; margin: 0 0 18px; display: grid; gap: 6px; color: #334155; }
.inc__meta li { display: flex; align-items: center; gap: 8px; }

@media (max-width: 576px) {
    .inc { flex-direction: column; }
}
</style>
