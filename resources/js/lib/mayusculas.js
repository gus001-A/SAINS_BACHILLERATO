/**
 * Convierte en mayúsculas todo lo que el usuario escribe en inputs/textareas de
 * texto, en toda la aplicación, excepto contraseñas. Se engancha una sola vez
 * en la fase de captura del evento "input" sobre `document`, ANTES de que
 * Vue/Ant Design lean el valor, así que el v-model ya recibe el texto en
 * mayúsculas sin necesidad de tocar cada formulario.
 */

const TIPOS_EXCLUIDOS = new Set([
    'password', 'file', 'date', 'month', 'week', 'time', 'datetime-local',
    'color', 'range', 'number', 'checkbox', 'radio', 'hidden',
    'submit', 'reset', 'button', 'image',
]);

// Ant Design cambia el type de <a-input-password> a "text" al mostrar la
// contraseña, así que no basta con revisar el type: se detecta el contenedor
// y el atributo autocomplete.
function esCampoContrasena(el) {
    if (el.closest('.ant-input-password')) return true;
    const auto = (el.getAttribute('autocomplete') || '').toLowerCase();
    return auto.includes('password');
}

function elegible(el) {
    if (!(el instanceof HTMLInputElement) && !(el instanceof HTMLTextAreaElement)) return false;
    if (el.dataset && 'noMayusculas' in el.dataset) return false;
    if (el instanceof HTMLInputElement && TIPOS_EXCLUIDOS.has(el.type)) return false;
    if (esCampoContrasena(el)) return false;
    return true;
}

export function activarMayusculasGlobales() {
    document.addEventListener('input', (event) => {
        if (event.isComposing) return;

        const el = event.target;
        if (!elegible(el)) return;

        const original = el.value;
        const mayus = original.toUpperCase();
        if (mayus === original) return;

        let inicio = null;
        let fin = null;
        try {
            inicio = el.selectionStart;
            fin = el.selectionEnd;
        } catch (e) { /* algunos tipos de input no exponen selección */ }

        el.value = mayus;

        if (inicio !== null && fin !== null) {
            try {
                el.setSelectionRange(inicio, fin);
            } catch (e) { /* ignorar */ }
        }
    }, true);
}
