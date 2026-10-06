/**
 * Sonido de notificación sintetizado con Web Audio API (sin archivos).
 * Un "ding" de dos notas, suave. Respeta la preferencia guardada del usuario.
 */

const LS_KEY = 'sains:notif-sound';

export function sonidoActivo() {
    try {
        return localStorage.getItem(LS_KEY) !== 'off';
    } catch (e) {
        return true;
    }
}

export function setSonido(activo) {
    try {
        localStorage.setItem(LS_KEY, activo ? 'on' : 'off');
    } catch (e) { /* noop */ }
}

let ctx = null;

/**
 * Reproduce el "ding". `force` ignora la preferencia (para la vista previa del toggle).
 */
export function reproducirDing({ force = false } = {}) {
    if (!force && !sonidoActivo()) return;
    try {
        const AC = window.AudioContext || window.webkitAudioContext;
        if (!AC) return;
        ctx = ctx || new AC();
        if (ctx.state === 'suspended') ctx.resume();

        const now = ctx.currentTime;
        const master = ctx.createGain();
        master.gain.value = 0.0001;
        master.connect(ctx.destination);
        master.gain.exponentialRampToValueAtTime(0.16, now + 0.02);
        master.gain.exponentialRampToValueAtTime(0.0001, now + 0.7);

        // Dos notas ascendentes (E5 -> A5)
        [[659.25, 0], [880.0, 0.12]].forEach(([freq, delay]) => {
            const osc = ctx.createOscillator();
            const g = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.value = freq;
            g.gain.value = 0.0001;
            g.gain.setValueAtTime(0.0001, now + delay);
            g.gain.exponentialRampToValueAtTime(1, now + delay + 0.03);
            g.gain.exponentialRampToValueAtTime(0.0001, now + delay + 0.45);
            osc.connect(g);
            g.connect(master);
            osc.start(now + delay);
            osc.stop(now + delay + 0.5);
        });
    } catch (e) { /* silencioso */ }
}
