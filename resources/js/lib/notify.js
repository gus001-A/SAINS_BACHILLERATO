import { h } from 'vue';
import { message, Modal } from 'ant-design-vue';
import {
    DeleteOutlined, ExclamationCircleFilled, QuestionCircleFilled, LogoutOutlined,
} from '@ant-design/icons-vue';

message.config({ top: '72px', duration: 3, maxCount: 3 });

/**
 * Muestra los mensajes flash de Inertia (page.props.flash) como toasts.
 */
export function showFlash(flash) {
    if (!flash) return;
    if (flash.success) message.success(flash.success);
    if (flash.error) message.error(flash.error);
    if (flash.warning) message.warning(flash.warning);
    if (flash.info) message.info(flash.info);
}

/**
 * Diálogo de confirmación con cabecera ilustrada.
 *
 * @param {object}  opts
 * @param {string}  opts.title
 * @param {string}  opts.content     texto descriptivo
 * @param {string}  opts.okText
 * @param {boolean} opts.danger      botón principal en rojo
 * @param {'delete'|'warn'|'ask'|'logout'} opts.tone   estilo del icono
 * @param {Function} opts.onOk
 */
function baseConfirm({ title, content = '', okText = 'Confirmar', danger = false, tone = 'ask', onOk }) {
    const tones = {
        delete: { icon: DeleteOutlined, color: '#dc2626', bg: '#fee2e2' },
        warn: { icon: ExclamationCircleFilled, color: '#d97706', bg: '#fef3c7' },
        ask: { icon: QuestionCircleFilled, color: '#4f46e5', bg: '#eef2ff' },
        logout: { icon: LogoutOutlined, color: '#dc2626', bg: '#fee2e2' },
    };
    const t = tones[tone] ?? tones.ask;

    Modal.confirm({
        icon: null,
        centered: true,
        width: 420,
        wrapClassName: 'sains-confirm',
        okText,
        okButtonProps: { danger, size: 'large', shape: 'round' },
        cancelText: 'Cancelar',
        cancelButtonProps: { size: 'large', shape: 'round' },
        title: h('div', { class: 'sains-confirm__head' }, [
            h('span', {
                class: 'sains-confirm__icon',
                style: { background: t.bg, color: t.color },
            }, [h(t.icon)]),
            h('span', { class: 'sains-confirm__title' }, title),
        ]),
        content: content
            ? h('p', { class: 'sains-confirm__text' }, content)
            : null,
        onOk,
    });
}

/**
 * Diálogo de confirmación de borrado.
 */
export function confirmDelete({ title = '¿Eliminar registro?', content = '', onOk }) {
    baseConfirm({ title, content, okText: 'Sí, eliminar', danger: true, tone: 'delete', onOk });
}

/**
 * Diálogo de confirmación genérico.
 */
export function confirmAction({ title, content = '', okText = 'Confirmar', danger = false, tone, onOk }) {
    baseConfirm({ title, content, okText, danger, tone: tone ?? (danger ? 'warn' : 'ask'), onOk });
}

export { message };
