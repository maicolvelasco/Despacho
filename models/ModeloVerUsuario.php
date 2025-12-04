<?php

class ModeloVerUsuario
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerTodosUsuarios()
    {
        $stmt = $this->pdo->query("SELECT u.*, d.nombre AS departamento_nombre, r.nombre AS rol_nombre 
                                    FROM usuarios u
                                    JOIN departamentos d ON u.departamento_id = d.id
                                    JOIN roles r ON u.rol_id = r.id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerUsuariosPorRol($rolId)
    {
        $stmt = $this->pdo->prepare("SELECT u.*, d.nombre AS departamento_nombre, r.nombre AS rol_nombre 
                                     FROM usuarios u
                                     JOIN departamentos d ON u.departamento_id = d.id
                                     JOIN roles r ON u.rol_id = r.id
                                     WHERE u.rol_id = ?");
        $stmt->execute([$rolId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerUsuariosExceptoRol($rolId)
    {
        $stmt = $this->pdo->prepare("SELECT u.*, d.nombre AS departamento_nombre, r.nombre AS rol_nombre 
                                     FROM usuarios u
                                     JOIN departamentos d ON u.departamento_id = d.id
                                     JOIN roles r ON u.rol_id = r.id
                                     WHERE u.rol_id != ?");
        $stmt->execute([$rolId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerRoles()
    {
        $stmt = $this->pdo->query("SELECT id, nombre FROM roles");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDepartamentos()
    {
        $stmt = $this->pdo->query("SELECT id, nombre FROM departamentos");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarUsuario($nombre, $direccion, $codigo, $password, $rol_id, $departamento_id)
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre, direccion, codigo, password, rol_id, departamento_id) VALUES (?, ?, ?, ?, ?, ?)");
            $resultado = $stmt->execute([$nombre, $direccion, $codigo, $password, $rol_id, $departamento_id]);
    
            if (!$resultado) {
                // Captura el error del PDO si falla la inserción
                $errorInfo = $stmt->errorInfo();
                error_log("Error al registrar usuario: " . implode(", ", $errorInfo));
                return ['success' => false, 'error' => "Error en la base de datos: " . $errorInfo[2]];
            }
    
            return ['success' => true];
        } catch (PDOException $e) {
            // Captura excepciones de PDO y registra el error
            error_log("Excepción al registrar usuario: " . $e->getMessage());
            return ['success' => false, 'error' => "Excepción de PDO: " . $e->getMessage()];
        }
    }
    

    public function obtenerUsuarioPorId($id)
    {
        $stmt = $this->pdo->prepare("SELECT id, nombre, direccion, codigo, password, rol_id, departamento_id FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarUsuario($id, $nombre, $direccion, $codigo, $password, $rol_id, $departamento_id)
    {
        // Verificar si se está actualizando la contraseña
        if (!empty($password)) {
            $sql = "UPDATE usuarios SET nombre = ?, direccion = ?, codigo = ?, password = ?, rol_id = ?, departamento_id = ? WHERE id = ?";
            $params = [$nombre, $direccion, $codigo, $password, $rol_id, $departamento_id, $id];
        } else {
            // No actualizar la contraseña
            $sql = "UPDATE usuarios SET nombre = ?, direccion = ?, codigo = ?, rol_id = ?, departamento_id = ? WHERE id = ?";
            $params = [$nombre, $direccion, $codigo, $rol_id, $departamento_id, $id];
        }

        $stmt = $this->pdo->prepare($sql);
        $resultado = $stmt->execute($params);

        if (!$resultado) {
            // Captura el error del PDO y lo registra
            $errorInfo = $stmt->errorInfo();
            error_log("Error en la actualización: " . implode(", ", $errorInfo));
            return ['success' => false, 'error' => implode(", ", $errorInfo)];
        }

        return ['success' => true];
    }

    public function eliminarUsuarioPorId($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $resultado = $stmt->execute([$id]);

        if (!$resultado) {
            // Captura el error del PDO y lo registra
            $errorInfo = $stmt->errorInfo();
            error_log("Error al eliminar usuario: " . implode(", ", $errorInfo));
            return ['success' => false, 'error' => implode(", ", $errorInfo)];
        }

        return ['success' => true];
    }
}