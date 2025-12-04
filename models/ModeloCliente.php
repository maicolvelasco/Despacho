<?php
// /models/ModeloCliente.php

class ModeloCliente
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtener todos los clientes con informaci車n del titular y usuario asociado.
     *
     * @return array
     */
// /models/ModeloCliente.php

public function obtenerTodosClientes($offset = 0, $limit = 10)
{
    $stmt = $this->pdo->prepare("
        SELECT c.*, t.nombre AS titular_nombre, u.nombre AS usuario_nombre
        FROM clientes c
        JOIN titular t ON c.titular_id = t.id
        JOIN usuarios u ON t.usuario_id = u.id
        LIMIT :offset, :limit
    ");
    
    // Asegúrate de que estás vinculando los parámetros correctamente
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
    public function contarClientes()
{
    $stmt = $this->pdo->query("SELECT COUNT(*) FROM clientes");
    return $stmt->fetchColumn();
}

    /**
     * Obtener un cliente por su ID con informaci車n del titular y usuario asociado.
     *
     * @param int $id
     * @return array|false
     */
    public function obtenerClientePorId($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, t.nombre AS titular_nombre, u.nombre AS usuario_nombre
            FROM clientes c
            JOIN titular t ON c.titular_id = t.id
            JOIN usuarios u ON t.usuario_id = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar un nuevo cliente.
     *
     * @param string $nombre
     * @param string|null $codigo
     * @param int $titular_id
     * @return array
     */
    public function registrarCliente($nombre, $codigo, $titular_id)
    {
        $stmt = $this->pdo->prepare("INSERT INTO clientes (nombre, codigo, titular_id) VALUES (?, ?, ?)");
        $resultado = $stmt->execute([$nombre, $codigo, $titular_id]);

        if (!$resultado) {
            $errorInfo = $stmt->errorInfo();
            error_log("Error en la inserci車n del cliente: " . implode(", ", $errorInfo));
            return ['success' => false, 'error' => implode(", ", $errorInfo)];
        }

        return ['success' => true, 'id' => $this->pdo->lastInsertId()];
    }

    /**
     * Actualizar un cliente existente.
     *
     * @param int $id
     * @param string $nombre
     * @param string|null $codigo
     * @param int $titular_id
     * @return array
     */
    public function actualizarCliente($id, $nombre, $codigo, $titular_id)
    {
        $stmt = $this->pdo->prepare("UPDATE clientes SET nombre = ?, codigo = ?, titular_id = ? WHERE id = ?");
        $resultado = $stmt->execute([$nombre, $codigo, $titular_id, $id]);

        if (!$resultado) {
            $errorInfo = $stmt->errorInfo();
            error_log("Error en la actualizaci車n del cliente: " . implode(", ", $errorInfo));
            return ['success' => false, 'error' => implode(", ", $errorInfo)];
        }
    
        return ['success' => true];
    }

    /**
     * Eliminar un cliente por su ID.
     *
     * @param int $id
     * @return array
     */
    public function eliminarClientePorId($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM clientes WHERE id = ?");
        $resultado = $stmt->execute([$id]);

        if (!$resultado) {
            $errorInfo = $stmt->errorInfo();
            error_log("Error al eliminar el cliente: " . implode(", ", $errorInfo));
            return ['success' => false, 'error' => implode(", ", $errorInfo)];
        }
    
        return ['success' => true];
    }

    /**
     * Obtener todos los titulares para poblar un dropdown en las vistas.
     *
     * @return array
     */
    public function obtenerTitulares()
    {
        $stmt = $this->pdo->prepare("
            SELECT t.id, t.nombre, t.codigo, u.nombre AS usuario_nombre 
            FROM titular t 
            JOIN usuarios u ON t.usuario_id = u.id
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>