-- ============================================================
-- ISSFAM · carreras, guías y datos nuevos del estudiante
-- Equivale a las migraciones 2026_10_06_100000_issfam_carreras_guias_datos_estudiante
-- y 2026_10_06_110000_guias_tronco_comun (guías con carrera_id NULL = tronco común).
-- Correr UNA sola vez en producción (phpMyAdmin), con respaldo previo.
-- ============================================================

CREATE TABLE IF NOT EXISTS `carreras_bachillerato` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(120) NOT NULL,
    `descripcion` TEXT NULL,
    `icono` VARCHAR(40) NULL,
    `orden` INT UNSIGNED NOT NULL DEFAULT 0,
    `activa` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `carreras_bachillerato_nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `carreras_bachillerato` (`nombre`, `descripcion`, `icono`, `orden`, `activa`, `created_at`, `updated_at`) VALUES
('Informática Administrativa', 'Uso de herramientas informáticas para la gestión y el control administrativo de las organizaciones.', 'fa-laptop-code', 1, 1, NOW(), NOW()),
('Administración de Recursos Humanos', 'Reclutamiento, capacitación y desarrollo del personal dentro de las organizaciones.', 'fa-people-group', 2, 1, NOW(), NOW()),
('Administración', 'Planeación, organización y control de los recursos de una empresa o institución.', 'fa-chart-column', 3, 1, NOW(), NOW()),
('Programador', 'Desarrollo de programas y aplicaciones con lógica de programación y buenas prácticas.', 'fa-code', 4, 1, NOW(), NOW());

CREATE TABLE IF NOT EXISTS `guias` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `carrera_id` BIGINT UNSIGNED NULL, -- NULL = tronco común (todas las carreras)
    `titulo` VARCHAR(180) NOT NULL,
    `descripcion` TEXT NULL,
    `archivo` VARCHAR(255) NULL,
    `enlace` VARCHAR(500) NULL,
    `orden` INT UNSIGNED NOT NULL DEFAULT 0,
    `activa` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `guias_carrera_id_foreign` (`carrera_id`),
    CONSTRAINT `guias_carrera_id_foreign` FOREIGN KEY (`carrera_id`)
        REFERENCES `carreras_bachillerato` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `estudiante`
    ADD COLUMN `curp` VARCHAR(18) NULL,
    ADD COLUMN `calle_numero` VARCHAR(150) NULL,
    ADD COLUMN `colonia` VARCHAR(120) NULL,
    ADD COLUMN `codigo_postal` VARCHAR(5) NULL,
    ADD COLUMN `municipio` VARCHAR(120) NULL,
    ADD COLUMN `entidad_federativa` VARCHAR(60) NULL,
    ADD COLUMN `carrera_id` BIGINT UNSIGNED NULL,
    ADD COLUMN `ediciones_total` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    ADD COLUMN `ediciones_dia` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    ADD COLUMN `ediciones_fecha` DATE NULL,
    ADD CONSTRAINT `estudiante_carrera_id_foreign` FOREIGN KEY (`carrera_id`)
        REFERENCES `carreras_bachillerato` (`id`) ON DELETE SET NULL;

-- Registro de la migración para que Laravel no intente correrla de nuevo.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_06_100000_issfam_carreras_guias_datos_estudiante', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`;
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_10_06_110000_guias_tronco_comun', COALESCE(MAX(`batch`), 0) FROM `migrations`;
