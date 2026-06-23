<?php

namespace Dropc\JwtApi;

use PDO;
use PDOException;

/**
 * Database
 * Clase simple (singleton) para abrir UNA sola conexión PDO a MySQL
 * y reutilizarla en todo el proyecto. Evita inyecciones SQL porque
 * usamos siempre consultas preparadas (prepare + bindParam).
 */
class Database
{
    private static ?PDO $instancia = null;

    public static function getConexion(): PDO
    {
        if (self::$instancia === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                self::$instancia = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    "success" => false,
                    "message" => "Error de conexión a la base de datos: " . $e->getMessage()
                ]);
                exit;
            }
        }

        return self::$instancia;
    }
}