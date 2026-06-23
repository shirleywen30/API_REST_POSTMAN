<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dropc\JwtApi\Database;
use Dropc\JwtApi\AuthService;

header('Content-Type: application/json');

// Solo aceptamos POST para login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Método no permitido. Use POST."]);
    exit;
}

// Leer el body (acepta JSON o form-data)
$datos = json_decode(file_get_contents('php://input'), true);
if (!$datos) {
    $datos = $_POST;
}

$username = $datos['username'] ?? null;
$password = $datos['password'] ?? null;

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Usuario y contraseña son obligatorios."]);
    exit;
}

try {
    $db = Database::getConexion();
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $usuario = $stmt->fetch();

    // password_verify compara el texto plano contra el hash guardado
    if (!$usuario || !password_verify($password, $usuario['password'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Credenciales incorrectas."]);
        exit;
    }

    $auth = new AuthService();
    $token = $auth->generarToken($usuario['username']);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Login exitoso.",
        "token" => $token
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error en el servidor: " . $e->getMessage()]);
}