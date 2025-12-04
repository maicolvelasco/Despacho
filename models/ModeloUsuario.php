<?php
class ModeloUsuario {
    private $pdo;

    // Constructor que recibe la conexión PDO
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Autenticación de usuario: verifica si el código y contraseña son correctos
    public function autenticar($codigo, $password) {
        $stmt = $this->pdo->prepare("SELECT u.id, u.nombre, u.password, r.nombre AS rol_nombre 
                                     FROM usuarios u 
                                     JOIN roles r ON u.rol_id = r.id 
                                     WHERE u.codigo = ?");
        $stmt->execute([$codigo]);
        $usuario = $stmt->fetch();

        // Verifica si la contraseña coincide
        if ($usuario && $usuario['password'] === $password) {
            return $usuario;
        }
        return null;
    }
}
