<?php

namespace Dropc\JwtApi;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

/**
 * AuthService
 * "El Guardián" — centraliza toda la lógica de autenticación:
 *  - Generar tokens cuando el usuario hace login correctamente.
 *  - Validar tokens en cada petición a la API.
 */
class AuthService
{
    private string $secretKey;
    private string $algoritmo = 'HS256';

    public function __construct()
    {
        $this->secretKey = JWT_SECRET_KEY;
    }

    /**
     * Genera un token JWT para un usuario autenticado.
     * El token expira en 1 hora (3600 segundos).
     */
    public function generarToken(string $username): string
    {
        $ahora = time();

        $payload = [
            'iat' => $ahora,           // issued at (cuándo se creó)
            'exp' => $ahora + 3600,    // expiración (1 hora)
            'username' => $username,
        ];

        return JWT::encode($payload, $this->secretKey, $this->algoritmo);
    }

    /**
     * Extrae el token del header "Authorization: Bearer <token>".
     * Devuelve null si no existe o el formato es inválido.
     */
    public function obtenerTokenDesdeHeader(): ?string
    {
        $headers = $this->obtenerHeaders();

        if (!isset($headers['Authorization'])) {
            return null;
        }

        $authHeader = $headers['Authorization'];

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Valida el token. Si es válido, devuelve el payload decodificado.
     * Si es inválido o no existe, responde 401 y detiene la ejecución.
     */
    public function validarTokenOMorir(): object
    {
        $token = $this->obtenerTokenDesdeHeader();

        if ($token === null) {
            $this->responderNoAutorizado("Token no proporcionado.");
        }

        try {
            return JWT::decode($token, new Key($this->secretKey, $this->algoritmo));
        } catch (Exception $e) {
            $this->responderNoAutorizado("Token inválido o expirado: " . $e->getMessage());
        }
    }

    private function responderNoAutorizado(string $mensaje): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            "success" => false,
            "message" => $mensaje
        ]);
        exit;
    }

    /**
     * Obtiene los headers de la petición de forma compatible
     * con distintos servidores (Apache/WAMP a veces no expone
     * getallheaders() igual que otros).
     */
    private function obtenerHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        // Fallback manual por si getallheaders() no existe
        $headers = [];
        foreach ($_SERVER as $nombre => $valor) {
            if (substr($nombre, 0, 5) === 'HTTP_') {
                $headerNombre = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($nombre, 5)))));
                $headers[$headerNombre] = $valor;
            }
        }
        return $headers;
    }
}