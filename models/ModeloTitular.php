<?php
// /models/ModeloTitular.php

class ModeloTitular
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtener todos los titulares con informaci��n del usuario asociado.
     *
     * @return array
     */
    public function obtenerTodosTitulares()
    {
        $stmt = $this->pdo->query("
            SELECT t.*, u.nombre AS usuario_nombre
            FROM titular t
            JOIN usuarios u ON t.usuario_id = u.id
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

    /**
     * Obtener un titular por su ID.
     *
     * @param int $id
     * @return array|false
     */
    public function obtenerTitularPorId($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM titular WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar un nuevo titular.
     *
     * @param string $nombre
     * @param string $codigo
     * @param int $usuario_id
     * @return array
     */
    public function registrarTitular($nombre, $codigo, $usuario_id)
    {
        $stmt = $this->pdo->prepare("INSERT INTO titular (nombre, codigo, usuario_id) VALUES (?, ?, ?)");
        $resultado = $stmt->execute([$nombre, $codigo, $usuario_id]);

        if (!$resultado) {
            $errorInfo = $stmt->errorInfo();
            error_log("Error en la inserci��n del titular: " . implode(", ", $errorInfo));
            return ['success' => false, 'error' => implode(", ", $errorInfo)];
        }

        return ['success' => true, 'id' => $this->pdo->lastInsertId()];
    }

    /**
     * Actualizar un titular existente.
     *
     * @param int $id
     * @param string $nombre
     * @param string $codigo
     * @param int $usuario_id
     * @return array
     */
    public function actualizarTitular($id, $nombre, $codigo, $usuario_id)
    {
        $stmt = $this->pdo->prepare("UPDATE titular SET nombre = ?, codigo = ?, usuario_id = ? WHERE id = ?");
        $resultado = $stmt->execute([$nombre, $codigo, $usuario_id, $id]);

        if (!$resultado) {
            $errorInfo = $stmt->errorInfo();
            error_log("Error en la actualizaci��n del titular: " . implode(", ", $errorInfo));
            return ['success' => false, 'error' => implode(", ", $errorInfo)];
        }

        return ['success' => true];
    }

    /**
     * Eliminar un titular por su ID.
     *
     * @param int $id
     * @return array
     */
    public function eliminarTitularPorId($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM titular WHERE id = ?");
        $resultado = $stmt->execute([$id]);

        if (!$resultado) {
            $errorInfo = $stmt->errorInfo();
            error_log("Error al eliminar el titular: " . implode(", ", $errorInfo));
            return ['success' => false, 'error' => implode(", ", $errorInfo)];
        }

        return ['success' => true];
    }

    /**
     * Obtener todos los usuarios con rol vendedor (rol_id = 2) para poblar el dropdown en el modal.
     *
     * @return array
     */
    public function obtenerUsuariosPorRoles()
    {
        $stmt = $this->pdo->query("SELECT id, nombre FROM usuarios WHERE rol_id IN (2, 4)"); // Roles 2 y 4
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerTitularesPorUsuario($usuario_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT t.*, u.nombre AS usuario_nombre
            FROM titular t
            JOIN usuarios u ON t.usuario_id = u.id
            WHERE t.usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reasignar titulares de un usuario a otro.
     *
     * @param int $usuario_id_original
     * @param int $usuario_id_nuevo
     * @return array
     */
    public function reasignarTitulares($usuario_id_original, $usuario_id_nuevo)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                UPDATE titular 
                SET usuario_id = ?
                WHERE usuario_id = ?
            ");
            $stmt->execute([$usuario_id_nuevo, $usuario_id_original]);

            $this->pdo->commit();
            return ['success' => true];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error al reasignar titulares: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>