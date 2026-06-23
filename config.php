<?php
/**
 * config.php
 * Punto central de configuración. Carga el .env y deja todo listo
 * para que cualquier archivo del proyecto pueda usar las constantes.
 */

require_once __DIR__ . '/env.php';

cargarEnv(__DIR__ . '/.env');

// Constantes globales de configuración
define('JWT_SECRET_KEY', $_ENV['JWT_SECRET_KEY'] ?? '');
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? '');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

if (empty(JWT_SECRET_KEY)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error de configuración: JWT_SECRET_KEY no definida."
    ]);
    exit;
}