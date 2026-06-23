<?php

namespace Dropc\JwtApi;

use PDO;
use Exception;

/**
 * ProductController
 * Contiene la lógica de negocio para cada operación CRUD sobre productos.
 * Cada método maneja sus propios errores y responde directamente en JSON.
 */
class ProductController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConexion();
    }

    /** GET /productos  o  GET /productos?id=1 */
    public function obtener(): void
    {
        $id = $_GET['id'] ?? null;

        try {
            if ($id !== null) {
                $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $producto = $stmt->fetch();

                if (!$producto) {
                    $this->responder(404, false, "Producto no encontrado.");
                    return;
                }

                $this->responder(200, true, "Producto encontrado.", $producto);
            } else {
                $stmt = $this->db->query("SELECT * FROM productos ORDER BY id DESC");
                $productos = $stmt->fetchAll();
                $this->responder(200, true, "Listado de productos.", $productos);
            }
        } catch (Exception $e) {
            $this->responder(500, false, "Error al consultar: " . $e->getMessage());
        }
    }

    /** POST /productos */
    public function crear(): void
    {
        $datos = $this->leerJsonBody();

        $errores = $this->validar($datos);
        if (!empty($errores)) {
            $this->responder(422, false, "Datos inválidos.", null, $errores);
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO productos (codigo, producto, precio, cantidad)
                 VALUES (:codigo, :producto, :precio, :cantidad)"
            );
            $stmt->execute([
                ':codigo' => $datos['codigo'],
                ':producto' => $datos['producto'],
                ':precio' => $datos['precio'],
                ':cantidad' => $datos['cantidad'],
            ]);

            $nuevoId = $this->db->lastInsertId();
            http_response_code(201); // 201 = recurso creado
            header('Content-Type: application/json');
            echo json_encode([
                "success" => true,
                "message" => "Producto creado correctamente.",
                "data" => ["id" => $nuevoId] + $datos
            ]);
        } catch (Exception $e) {
            $this->responder(500, false, "Error al crear: " . $e->getMessage());
        }
    }

    /** PUT /productos?id=1 */
    public function actualizar(): void
    {
        $id = $_GET['id'] ?? null;

        if ($id === null) {
            $this->responder(400, false, "Debe indicar el id del producto a actualizar.");
            return;
        }

        $datos = $this->leerJsonBody();

        $errores = $this->validar($datos);
        if (!empty($errores)) {
            $this->responder(422, false, "Datos inválidos.", null, $errores);
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE productos
                 SET codigo = :codigo, producto = :producto, precio = :precio, cantidad = :cantidad
                 WHERE id = :id"
            );
            $stmt->execute([
                ':codigo' => $datos['codigo'],
                ':producto' => $datos['producto'],
                ':precio' => $datos['precio'],
                ':cantidad' => $datos['cantidad'],
                ':id' => $id,
            ]);

            if ($stmt->rowCount() === 0) {
                $this->responder(404, false, "Producto no encontrado o sin cambios.");
                return;
            }

            $this->responder(200, true, "Producto actualizado correctamente.", ["id" => $id] + $datos);
        } catch (Exception $e) {
            $this->responder(500, false, "Error al actualizar: " . $e->getMessage());
        }
    }

    /** DELETE /productos?id=1 */
    public function eliminar(): void
    {
        $id = $_GET['id'] ?? null;

        if ($id === null) {
            $this->responder(400, false, "Debe indicar el id del producto a eliminar.");
            return;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM productos WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $this->responder(404, false, "Producto no encontrado.");
                return;
            }

            $this->responder(200, true, "Producto eliminado correctamente.");
        } catch (Exception $e) {
            $this->responder(500, false, "Error al eliminar: " . $e->getMessage());
        }
    }

    /** Valida que los campos obligatorios existan y tengan el tipo correcto */
    private function validar(array $datos): array
    {
        $errores = [];

        if (empty($datos['codigo'])) {
            $errores[] = "El campo 'codigo' es obligatorio.";
        }
        if (empty($datos['producto'])) {
            $errores[] = "El campo 'producto' es obligatorio.";
        }
        if (!isset($datos['precio']) || !is_numeric($datos['precio'])) {
            $errores[] = "El campo 'precio' es obligatorio y debe ser numérico.";
        }
        if (!isset($datos['cantidad']) || !is_numeric($datos['cantidad'])) {
            $errores[] = "El campo 'cantidad' es obligatorio y debe ser numérico.";
        }

        return $errores;
    }

    /** Lee el body de la petición como JSON (para POST y PUT) */
    private function leerJsonBody(): array
    {
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
        return is_array($datos) ? $datos : [];
    }

    /** Respuesta JSON estándar para toda la API */
    private function responder(int $codigoHttp, bool $success, string $message, $data = null, array $errors = []): void
    {
        http_response_code($codigoHttp);
        header('Content-Type: application/json');
        echo json_encode([
            "success" => $success,
            "message" => $message,
            "data" => $data,
            "errors" => $errors,
        ]);
    }
}