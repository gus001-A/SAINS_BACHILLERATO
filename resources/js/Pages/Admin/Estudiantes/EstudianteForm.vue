<script setup>
import {
    IdcardOutlined, PhoneOutlined, HomeOutlined, CalendarOutlined, ManOutlined,
    MailOutlined, LockOutlined, CrownOutlined, TagOutlined,
} from '@ant-design/icons-vue';
import PhoneInput from '@/Components/PhoneInput.vue';
import DateField from '@/Components/DateField.vue';

const props = defineProps({
    form: { type: Object, required: true },
    opciones: { type: Object, required: true },
    modo: { type: String, default: 'create' }, // create | edit
});

const sexoOpts = [{ value: 'M', label: 'Masculino' }, { value: 'F', label: 'Femenino' }];
const cuponOpts = props.opciones.cupones.map((c) => ({ value: c.id, label: c.label }));

const err = (f) => (props.form.errors[f] ? 'error' : undefined);
</script>

<template>
    <a-form layout="vertical" class="sains-form-inner">
        <a-divider orientation="left" style="margin-top: 0"><span class="fsec"><IdcardOutlined />Datos personales</span></a-divider>
        <a-row :gutter="16">
            <a-col :xs="24" :md="8">
                <a-form-item label="Nombre(s)" required :validate-status="err('nombre')" :help="form.errors.nombre">
                    <a-input v-model:value="form.nombre"><template #prefix><IdcardOutlined /></template></a-input>
                </a-form-item>
            </a-col>
            <a-col :xs="24" :md="8">
                <a-form-item label="Apellido paterno" required :validate-status="err('paterno')" :help="form.errors.paterno">
                    <a-input v-model:value="form.paterno" />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :md="8">
                <a-form-item label="Apellido materno" :validate-status="err('materno')" :help="form.errors.materno">
                    <a-input v-model:value="form.materno" />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :md="8">
                <a-form-item :validate-status="err('fecha_nacimiento')" :help="form.errors.fecha_nacimiento || 'El estudiante debe tener al menos 15 años'">
                    <template #label><CalendarOutlined />Fecha de nacimiento</template>
                    <DateField v-model:value="form.fecha_nacimiento" limite="nacimiento-estudiante" />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :md="8">
                <a-form-item required :validate-status="err('sexo')" :help="form.errors.sexo">
                    <template #label><ManOutlined />Sexo</template>
                    <a-select v-model:value="form.sexo" :options="sexoOpts" placeholder="Selecciona…" />
                </a-form-item>
            </a-col>
            <a-col :xs="12" :md="4">
                <a-form-item required :validate-status="err('telefono')" :help="form.errors.telefono">
                    <template #label><PhoneOutlined />Teléfono</template>
                    <PhoneInput v-model:value="form.telefono" :icon="false" />
                </a-form-item>
            </a-col>
            <a-col :xs="12" :md="4">
                <a-form-item :validate-status="err('telefono_casa')" :help="form.errors.telefono_casa">
                    <template #label><HomeOutlined />Tel. casa</template>
                    <PhoneInput v-model:value="form.telefono_casa" :icon="false" placeholder="Opcional" />
                </a-form-item>
            </a-col>
        </a-row>

        <a-divider orientation="left"><span class="fsec"><LockOutlined />Cuenta y plan</span></a-divider>
        <a-row :gutter="16">
            <a-col :xs="24" :md="8">
                <a-form-item required :validate-status="err('email')" :help="form.errors.email">
                    <template #label><MailOutlined />Correo</template>
                    <a-input v-model:value="form.email" type="email"><template #prefix><MailOutlined /></template></a-input>
                </a-form-item>
            </a-col>
            <a-col :xs="24" :md="6">
                <a-form-item :required="modo === 'create'"
                    :validate-status="err('password')" :help="form.errors.password || (modo === 'edit' ? 'Deja en blanco para no cambiarla' : '')">
                    <template #label><LockOutlined />{{ modo === 'edit' ? 'Nueva contraseña' : 'Contraseña' }}</template>
                    <a-input-password v-model:value="form.password" />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :md="6">
                <a-form-item label="Confirmar contraseña">
                    <a-input-password v-model:value="form.password_confirmation" />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :md="4">
                <a-form-item>
                    <template #label><CrownOutlined />Plan activo</template>
                    <a-switch v-model:checked="form.plan_activo" checked-children="Sí" un-checked-children="No" />
                </a-form-item>
            </a-col>
            <a-col :xs="24" :md="12">
                <a-form-item :validate-status="err('cupon_id')" :help="form.errors.cupon_id">
                    <template #label><TagOutlined />Aplicar cupón (opcional)</template>
                    <a-select v-model:value="form.cupon_id" allow-clear show-search option-filter-prop="label" :options="cuponOpts" placeholder="Sin cupón" />
                </a-form-item>
            </a-col>
        </a-row>
    </a-form>
</template>
