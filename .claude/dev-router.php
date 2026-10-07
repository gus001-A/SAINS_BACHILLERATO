<?php
// Router SOLO para la vista previa local: usa la BD local `sains_2026`
// sin tocar el .env (que tiene las credenciales de producción).
foreach (['DB_DATABASE' => 'sains_2026', 'DB_USERNAME' => 'root', 'DB_PASSWORD' => '', 'APP_URL' => 'http://localhost:8040'] as $k => $v) {
    putenv("$k=$v");
    $_ENV[$k] = $v;
    $_SERVER[$k] = $v;
}
chdir(__DIR__ . '/../public');
return require __DIR__ . '/../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php';
