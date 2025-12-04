<?php

class ModeloPallets {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllPallets() {
        $stmt = $this->pdo->query("SELECT * FROM pallets");
        return $stmt->fetchAll();
    }

    public function updateStock($id, $stock) {
        $stmt = $this->pdo->prepare("UPDATE pallets SET stock = :stock WHERE id = :id");
        $stmt->execute([':stock' => $stock, ':id' => $id]);
    }

    public function addPallet($tamano, $stock) {
        $stmt = $this->pdo->prepare("INSERT INTO pallets (tamano, stock) VALUES (:tamano, :stock)");
        $stmt->execute([':tamano' => $tamano, ':stock' => $stock]);
    }

    public function deletePallet($id) {
        $stmt = $this->pdo->prepare("DELETE FROM pallets WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
    
    public function getPalletsByDepartamento($departamento_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM pallets WHERE departamento_id = :departamento_id");
        $stmt->execute([':departamento_id' => $departamento_id]);
        return $stmt->fetchAll();
    }
    
    public function getDepartamentosConPallets() {
        $stmt = $this->pdo->query("SELECT DISTINCT d.id, d.nombre 
                                    FROM departamentos d
                                    JOIN pallets p ON d.id = p.departamento_id");
        return $stmt->fetchAll();
    }
    
    public function getPalletsSinRepetir() {
        $stmt = $this->pdo->query("SELECT DISTINCT tamano FROM pallets");
        return $stmt->fetchAll();
    }
    
    public function addPalletConDepartamento($tamano, $departamento_id) {
        $stmt = $this->pdo->prepare("INSERT INTO pallets (tamano, stock, departamento_id) VALUES (:tamano, 0, :departamento_id)");
        $stmt->execute([':tamano' => $tamano, ':departamento_id' => $departamento_id]);
    }
    
    public function getTodosDepartamentos() {
        $stmt = $this->pdo->query("SELECT * FROM departamentos");
        return $stmt->fetchAll();
    }
}
?>