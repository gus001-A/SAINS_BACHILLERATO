<script setup>
import { Link } from '@inertiajs/vue3';
import { EyeOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons-vue';

defineProps({
    viewHref: { type: String, default: null },
    editHref: { type: String, default: null },
    editable: { type: Boolean, default: false },   // usa el evento @edit (modal) en vez de un enlace
    deletable: { type: Boolean, default: true },
    deleteDisabled: { type: Boolean, default: false },
    viewLabel: { type: String, default: 'Ver' },
    editLabel: { type: String, default: 'Editar' },
    deleteLabel: { type: String, default: 'Eliminar' },
});

defineEmits(['edit', 'delete']);
</script>

<template>
    <div class="row-actions" @click.stop>
        <a-tooltip v-if="viewHref" :title="viewLabel">
            <Link :href="viewHref" class="row-actions__btn is-view">
                <EyeOutlined />
            </Link>
        </a-tooltip>

        <a-tooltip v-if="editHref" :title="editLabel">
            <Link :href="editHref" class="row-actions__btn is-edit">
                <EditOutlined />
            </Link>
        </a-tooltip>
        <a-tooltip v-else-if="editable" :title="editLabel">
            <button type="button" class="row-actions__btn is-edit" @click="$emit('edit')">
                <EditOutlined />
            </button>
        </a-tooltip>

        <span v-if="$slots.extra" class="row-actions__extra"><slot name="extra" /></span>

        <a-tooltip v-if="deletable" :title="deleteDisabled ? 'No se puede eliminar' : deleteLabel">
            <button
                type="button"
                class="row-actions__btn is-delete"
                :class="{ 'is-disabled': deleteDisabled }"
                :disabled="deleteDisabled"
                @click="!deleteDisabled && $emit('delete')"
            >
                <DeleteOutlined />
            </button>
        </a-tooltip>
    </div>
</template>

<style>
.row-actions {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid var(--sains-line, #e9edf4);
}
.row-actions__btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 9px;
    font-size: 14px;
    cursor: pointer;
    background: #ffffff;
    color: #64748b;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
    transition: transform .14s cubic-bezier(.16,1,.3,1), color .14s ease, background .14s ease, box-shadow .14s ease;
}
.row-actions__extra { display: inline-flex; gap: 5px; }
.row-actions__btn:hover { transform: translateY(-2px); }
.row-actions__btn:active { transform: translateY(0); }
.row-actions__btn.is-disabled,
.row-actions__btn:disabled { opacity: .45; cursor: not-allowed; }
.row-actions__btn.is-disabled:hover,
.row-actions__btn:disabled:hover { transform: none; box-shadow: 0 1px 2px rgba(15, 23, 42, .06); }

/* Color al pasar el cursor (reposo neutro, color en hover) */
.row-actions__btn.is-view:hover { background: #0ea5e9; color: #fff; box-shadow: 0 8px 16px -6px rgba(14, 165, 233, .55); }
.row-actions__btn.is-edit:hover { background: #4f46e5; color: #fff; box-shadow: 0 8px 16px -6px rgba(79, 70, 229, .55); }
.row-actions__btn.is-delete:hover { background: #dc2626; color: #fff; box-shadow: 0 8px 16px -6px rgba(220, 38, 38, .55); }
.row-actions__btn.is-copy:hover { background: #d97706; color: #fff; box-shadow: 0 8px 16px -6px rgba(217, 119, 6, .55); }
.row-actions__btn.is-ok:hover { background: #16a34a; color: #fff; box-shadow: 0 8px 16px -6px rgba(22, 163, 74, .55); }
.row-actions__btn.is-no:hover { background: #dc2626; color: #fff; box-shadow: 0 8px 16px -6px rgba(220, 38, 38, .55); }

/* Tinte de reposo sutil por tipo */
.row-actions__btn.is-view { color: #0284c7; }
.row-actions__btn.is-edit { color: #4f46e5; }
.row-actions__btn.is-delete { color: #dc2626; }
.row-actions__btn.is-copy { color: #b45309; }
.row-actions__btn.is-ok { color: #16a34a; }
.row-actions__btn.is-no { color: #dc2626; }
</style>
