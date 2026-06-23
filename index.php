<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dropc\JwtApi\AuthService;
use Dropc\JwtApi\ProductController;

header('Content-Type: application/json');

// 1) Verificar el token ANTES de hacer cualquier otra cosa.
//    Si el token no es válido, validarTokenOMorir() ya responde 401 y detiene todo.
$auth = new AuthService();
$auth->validarTokenOMorir();

// 2) Si llegamos aquí, el token es válido. Ahora sí, derivamos según el método HTTP.
$metodo = $_SERVER['REQUEST_METHOD'];
$controller = new ProductController();

switch ($metodo) {
    case 'GET':
        $controller->obtener();
        break;

    case 'POST':
        $controller->crear();
        break;

    case 'PUT':
        $controller->actualizar();
        break;

    case 'DELETE':
        $controller->eliminar();
        break;

    default:
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "message" => "Método HTTP no soportado: $metodo"
        ]);
        break;
}