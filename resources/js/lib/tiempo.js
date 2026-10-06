import { ref } from 'vue';

// Un solo "reloj" compartido que se actualiza cada 30 s.
export const ahora = ref(Date.now());
setInterval(() => { ahora.value = Date.now(); }, 30000);

/**
 * "hace 5 s" / "hace 3 min" / "hace 2 h" / "ayer" / "hace 4 d" / "12/03/2026"
 * a partir de una fecha ISO. Es reactivo si se usa dentro de un computed
 * que lea `ahora.value`.
 */
export function desde(iso, nowMs = ahora.value) {
    if (!iso) return '';
    const t = new Date(iso).getTime();
    if (Number.isNaN(t)) return '';
    const s = Math.max(0, Math.round((nowMs - t) / 1000));

    if (s < 45) return 'hace un momento';
    if (s < 90) return 'hace 1 min';
    const m = Math.round(s / 60);
    if (m < 60) return `hace ${m} min`;
    const h = Math.round(m / 60);
    if (h < 24) return `hace ${h} h`;
    const d = Math.round(h / 24);
    if (d === 1) return 'ayer';
    if (d < 7) return `hace ${d} d`;
    return new Date(iso).toLocaleDateString('es-MX');
}
