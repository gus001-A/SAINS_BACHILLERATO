import dayjs from 'dayjs';

/**
 * Helpers de formularios compartidos: validación de teléfono, fechas máximas,
 * precio del curso. Se usan en los formularios de admin y estudiante.
 */

/** Precio fijo del curso premium (MXN). No editable desde los formularios. */
export const PRECIO_CURSO = 800;

/**
 * Deja sólo dígitos en el valor (para inputs de teléfono).
 * @param {string} v
 * @param {number} max  máximo de dígitos (default 10)
 */
export function soloDigitos(v, max = 10) {
    return String(v ?? '').replace(/\D+/g, '').slice(0, max);
}

/**
 * disabled-date para <a-date-picker>: bloquea cualquier fecha posterior a hoy.
 */
export function maxHoy(current) {
    return current && current.valueOf() > dayjs().endOf('day').valueOf();
}

/**
 * disabled-date que además bloquea fechas demasiado antiguas (para nacimiento).
 */
export function fechaNacimientoValida(current) {
    if (!current) return false;
    return current.valueOf() > dayjs().endOf('day').valueOf()
        || current.valueOf() < dayjs('1920-01-01').valueOf();
}

/**
 * Igual que fechaNacimientoValida, pero además exige al menos 15 años de edad
 * (para el registro de estudiantes).
 */
export function fechaNacimientoEstudianteValida(current) {
    if (!current) return false;
    return current.valueOf() > dayjs().subtract(15, 'year').endOf('day').valueOf()
        || current.valueOf() < dayjs('1920-01-01').valueOf();
}

/** Hora actual en formato HH:mm. */
export function ahoraHM() {
    return dayjs().format('HH:mm');
}

/** Fecha de hoy en formato YYYY-MM-DD. */
export function hoyISO() {
    return dayjs().format('YYYY-MM-DD');
}

/** ¿La cadena es una URL http(s) válida? */
export function esUrl(v) {
    if (!v) return false;
    try {
        const u = new URL(v);
        return u.protocol === 'http:' || u.protocol === 'https:';
    } catch (e) {
        return false;
    }
}
