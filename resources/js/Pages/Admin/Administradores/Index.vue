<script setup>
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { PlusOutlined, SearchOutlined, SafetyOutlined } from '@ant-design/icons-vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHead from '@/Components/PageHead.vue';
import RowActions from '@/Components/RowActions.vue';
import { confirmDelete } from '@/lib/notify';

const props = defineProps({
    admins: { type: Object, required: true },
    currentUserId: { type: Number, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');
const correo = ref(props.filters.correo ?? '');
const telefono = ref(props.filters.telefono ?? '');
const sexo = ref(props.filters.sexo ?? undefined);
let debounce = null;

function reload(extra = {}) {
    router.get(route('admin.administradores.index'),
        {
            search: search.value || undefined,
            correo: correo.value || undefined,
            telefono: telefono.value || undefined,
            sexo: sexo.value || undefined,
            ...extra,
        },
        { preserveState: true, replace: true, preserveScroll: true });
}
watch([search, correo, telefono], () => { clearTimeout(debounce); debounce = setTimeout(() => reload(), 350); });
watch(sexo, () => reload());

const modalOpen = ref(false);
const editing = ref(null);
const form = useForm({
    email: '', password: '', password_confirmation: '',
    nombre: '', apellido_paterno: '', apellido_materno: '',
    telefono: '', sexo: undefined, fecha_nacimiento: null,
});

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}
function openEdit(r) {
    editing.value = r;
    form.reset();
    form.email = r.correo;
    form.nombre = r.nombre ?? '';
    form.apellido_paterno = r.apellido_paterno ?? '';
    form.apellido_materno = r.apellido_materno ?? '';
    form.telefono = r.telefono ?? '';
    form.sexo = r.sexo || undefined;
    form.fecha_nacimiento = r.fecha_nacimiento;
    form.clearErrors();
    modalOpen.value = true;
}
function submit() {
    const opts = { onSuccess: () => { modalOpen.value = false; } };
    if (editing.value) form.put(route('admin.administradores.update', editing.value.id), opts);
    else form.post(route('admin.administradores.store'), opts);
}
function eliminar(r) {
    confirmDelete({ title: '¿Eliminar administrador?', content: r.nombre_completo, onOk: () => router.delete(route('admin.administradores.destroy', r.id), { preserveScroll: true }) });
}

const pagination = computed(() => ({
    current: props.admins.current_page,
    pageSize: props.admins.per_page,
    total: props.admins.total,
    showSizeChanger: false,
    showTotal: (t) => `${t} administradores`,
}));
function onChange(pag) { reload({ page: pag.current }); }

const sexoOpts = [{ value: 'M', label: 'Masculino' }, { value: 'F', label: 'Femenino' }];
const columns = [
    { title: 'Nombre', key: 'nombre' },
    { title: 'Correo', dataIndex: 'correo', key: 'correo', width: 260 },
    { title: 'Teléfono', dataIndex: 'telefono', key: 'telefono', width: 170 },
    { title: 'Sexo', dataIndex: 'sexo', key: 'sexo', width: 140 },
    { title: '', key: 'acciones', width: 100, align: 'right' },
];
</script>

<template>
    <AdminLayout title="Administradores">
        <PageHead title="Administradores" subtitle="Cuentas con acceso al panel" :icon="SafetyOutlined">
            <template #actions>
                <a-button type="primary" @click="openCreate"><template #icon><PlusOutlined /></template>Nuevo administrador</a-button>
            </template>
        </PageHead>

        <a-card :bordered="false" :body-style="{ padding: '4px 4px 12px' }">
            <a-table class="filtered-table" :columns="columns" :data-source="admins.data" :pagination="pagination" row-key="id" size="middle" :scroll="{ x: 800 }" @change="onChange">
                <template #summary>
                    <a-table-summary-row>
                        <a-table-summary-cell v-for="(col, i) in columns" :key="col.key ?? i" :index="i">
                            <a-input v-if="col.key === 'nombre'" v-model:value="search" size="small" allow-clear placeholder="Buscar nombre…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-input v-else-if="col.key === 'correo'" v-model:value="correo" size="small" allow-clear placeholder="Correo…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-input v-else-if="col.key === 'telefono'" v-model:value="telefono" size="small" allow-clear placeholder="Teléfono…">
                                <template #prefix><SearchOutlined /></template>
                            </a-input>
                            <a-select v-else-if="col.key === 'sexo'" v-model:value="sexo" size="small" allow-clear placeholder="Todos" :options="sexoOpts" />
                        </a-table-summary-cell>
                    </a-table-summary-row>
                </template>
                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'nombre'">
                        <span style="font-weight:600">{{ record.nombre_completo }}</span>
                        <a-tag v-if="record.id === currentUserId" color="blue" style="margin-left:8px">Tú</a-tag>
                    </template>
                    <template v-else-if="column.key === 'telefono'">{{ record.telefono || '—' }}</template>
                    <template v-else-if="column.key === 'sexo'">{{ record.sexo === 'M' ? 'Masculino' : record.sexo === 'F' ? 'Femenino' : '—' }}</template>
                    <template v-else-if="column.key === 'acciones'">
                        <RowActions editable :delete-disabled="record.id === currentUserId" @edit="openEdit(record)" @delete="eliminar(record)" />
                    </template>
                </template>
            </a-table>
        </a-card>

        <a-modal v-model:open="modalOpen" :title="editing ? 'Editar administrador' : 'Nuevo administrador'" class="sains-modal" :width="620" :confirm-loading="form.processing" ok-text="Guardar" @ok="submit">
            <a-form layout="vertical">
                <div class="sains-modal__section">Datos personales</div>
                <a-row :gutter="16">
                    <a-col :xs="24" :md="8">
                        <a-form-item label="Nombre" required :validate-status="form.errors.nombre ? 'error' : undefined" :help="form.errors.nombre">
                            <a-input v-model:value="form.nombre" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :md="8">
                        <a-form-item label="Apellido paterno" required :validate-status="form.errors.apellido_paterno ? 'error' : undefined" :help="form.errors.apellido_paterno">
                            <a-input v-model:value="form.apellido_paterno" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :md="8">
                        <a-form-item label="Apellido materno">
                            <a-input v-model:value="form.apellido_materno" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :md="12">
                        <a-form-item label="Teléfono">
                            <a-input v-model:value="form.telefono" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :md="6">
                        <a-form-item label="Sexo">
                            <a-select v-model:value="form.sexo" allow-clear :options="sexoOpts" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :md="6">
                        <a-form-item label="Fecha de nacimiento">
                            <a-date-picker v-model:value="form.fecha_nacimiento" value-format="YYYY-MM-DD" style="width: 100%" />
                        </a-form-item>
                    </a-col>
                </a-row>

                <div class="sains-modal__section">Datos de acceso</div>
                <a-row :gutter="16">
                    <a-col :xs="24" :md="24">
                        <a-form-item label="Correo" required :validate-status="form.errors.email ? 'error' : undefined" :help="form.errors.email">
                            <a-input v-model:value="form.email" type="email" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :md="12">
                        <a-form-item :label="editing ? 'Nueva contraseña' : 'Contraseña'" :required="!editing" :validate-status="form.errors.password ? 'error' : undefined" :help="form.errors.password || (editing ? 'Deja en blanco para no cambiarla' : '')">
                            <a-input-password v-model:value="form.password" />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :md="12">
                        <a-form-item label="Confirmar contraseña">
                            <a-input-password v-model:value="form.password_confirmation" />
                        </a-form-item>
                    </a-col>
                </a-row>
            </a-form>
        </a-modal>
    </AdminLayout>
</template>
