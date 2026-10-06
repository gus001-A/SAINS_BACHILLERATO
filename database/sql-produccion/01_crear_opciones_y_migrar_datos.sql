-- ============================================================================
-- PASO 1 de 2 — Preguntas con 3-4 opciones (producción)
-- ============================================================================
-- Qué hace:
--   1) Crea la tabla `pregunta_opciones` (una fila por opción de respuesta).
--   2) Copia las 3 opciones actuales de cada pregunta (respuesta_correcta,
--      respuesta1, respuesta2) a la tabla nueva, marcando cuál es la correcta.
--
-- NO borra ni modifica las columnas viejas (`respuesta_correcta`, `respuesta1`,
-- `respuesta2`) — eso lo hace el script 02, en un paso aparte.
--
-- CUÁNDO CORRERLO: con el código VIEJO todavía en producción (el que lee esas
-- 3 columnas). Es un cambio aditivo: no rompe nada de lo que ya funciona.
--
-- ORDEN COMPLETO EN PRODUCCIÓN:
--   1. Haz un respaldo completo de la base de datos.
--   2. Corre este script (01).
--   3. Corre la consulta de verificación al final de este archivo y confirma
--      que los conteos cuadran.
--   4. Sube el código nuevo (composer install / npm run build / deploy).
--   5. Ya con el código nuevo funcionando bien, corre el script 02 para
--      eliminar las columnas viejas.
-- ============================================================================

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `pregunta_opciones` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `pregunta_id` BIGINT UNSIGNED NOT NULL,
    `texto` VARCHAR(255) NOT NULL,
    `es_correcta` TINYINT(1) NOT NULL DEFAULT 0,
    `orden` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `pregunta_opciones_pregunta_id_foreign` (`pregunta_id`),
    CONSTRAINT `pregunta_opciones_pregunta_id_foreign`
        FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Opción 1 = la que hoy está en `respuesta_correcta` (se marca como correcta)
INSERT INTO pregunta_opciones (pregunta_id, texto, es_correcta, orden)
SELECT p.id, p.respuesta_correcta, 1, 1
FROM preguntas p
WHERE NOT EXISTS (
    SELECT 1 FROM pregunta_opciones po WHERE po.pregunta_id = p.id AND po.orden = 1
);

-- Opción 2 = `respuesta1` (distractor)
INSERT INTO pregunta_opciones (pregunta_id, texto, es_correcta, orden)
SELECT p.id, p.respuesta1, 0, 2
FROM preguntas p
WHERE NOT EXISTS (
    SELECT 1 FROM pregunta_opciones po WHERE po.pregunta_id = p.id AND po.orden = 2
);

-- Opción 3 = `respuesta2` (distractor)
INSERT INTO pregunta_opciones (pregunta_id, texto, es_correcta, orden)
SELECT p.id, p.respuesta2, 0, 3
FROM preguntas p
WHERE NOT EXISTS (
    SELECT 1 FROM pregunta_opciones po WHERE po.pregunta_id = p.id AND po.orden = 3
);

COMMIT;

-- ============================================================================
-- VERIFICACIÓN — corre esto después y confirma que ambos números coinciden
-- (total_preguntas * 3 debe ser igual a total_opciones), y que
-- preguntas_sin_correcta sea 0.
-- ============================================================================
SELECT
    (SELECT COUNT(*) FROM preguntas)                                   AS total_preguntas,
    (SELECT COUNT(*) FROM pregunta_opciones)                           AS total_opciones,
    (SELECT COUNT(*) FROM preguntas p
        WHERE NOT EXISTS (
            SELECT 1 FROM pregunta_opciones po
            WHERE po.pregunta_id = p.id AND po.es_correcta = 1
        )
    ) AS preguntas_sin_correcta;
