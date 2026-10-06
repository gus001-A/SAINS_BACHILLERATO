-- ============================================================================
-- PASO 2 de 2 — Preguntas con 3-4 opciones (producción)
-- ============================================================================
-- Qué hace: elimina las columnas viejas `respuesta_correcta`, `respuesta1` y
-- `respuesta2` de la tabla `preguntas`. Es IRREVERSIBLE (ese texto solo queda
-- disponible después en `pregunta_opciones`).
--
-- CUÁNDO CORRERLO: solo DESPUÉS de que el código nuevo ya esté funcionando
-- en producción (el que lee `pregunta_opciones` en vez de estas columnas) y
-- ya verificaste el script 01 (conteos correctos, preguntas_sin_correcta = 0).
-- Si corres esto antes de subir el código nuevo, el sitio viejo se rompe al
-- intentar leer estas columnas.
-- ============================================================================

START TRANSACTION;

ALTER TABLE preguntas
    DROP COLUMN respuesta_correcta,
    DROP COLUMN respuesta1,
    DROP COLUMN respuesta2;

COMMIT;
