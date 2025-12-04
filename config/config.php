<?php

require_once 'autoload.php';

$host = 'localhost';
$db   = 'duralitc_despacho';
$user = 'duralitc_despacho'; // Reemplaza con tu usuario de DB
$pass = '$gp2079700'; // Reemplaza con tu contraseña de DB
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
