<?php
// models/ModeloFechas.php

class ModeloFechas {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene los años distintos de la tabla recibir.
     *
     * @return array
     */
    public function getYears() {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT YEAR(fecha) AS year FROM recibir
            ORDER BY year ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Obtiene los meses distintos para un año específico de la tabla recibir.
     *
     * @param int $year
     * @return array
     */
    public function getMonths($year) {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT MONTH(fecha) AS month FROM recibir 
            WHERE YEAR(fecha) = :year
            ORDER BY month ASC
        ");
        $stmt->execute(['year' => $year]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Obtiene los datos de recibir para un año y mes específicos, incluyendo el tamaño del pallet y la cantidad.
     *
     * @param int $year
     * @param int $month
     * @return array
     */
    public function getRecibirPorMes($year, $month) {
        $stmt = $this->pdo->prepare("
            SELECT 
                r.fecha,
                c.codigo AS codigo_cliente,
                c.nombre AS nombre_cliente,
                t.codigo AS codigo_titular,
                t.nombre AS nombre_titular,
                p.tamano AS tamano_pallet,
                rp.cantidad AS cantidad,
                rm.numero AS remision_num,
                r.conductor,
                r.placa,
                r.observacion
            FROM recibir r
            LEFT JOIN clientes c ON r.cliente_id = c.id
            JOIN titular t ON r.titular_id = t.id
            JOIN remision_pallets rp ON rp.recibir_id = r.id AND rp.tipo = 'recibir'
            JOIN pallets p ON rp.pallet_id = p.id
            JOIN remision rm ON r.remision_id = rm.id
            WHERE YEAR(r.fecha) = :year AND MONTH(r.fecha) = :month
            ORDER BY rm.numero, rp.id
        ");
        $stmt->execute(['year' => $year, 'month' => $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene las cantidades de envios por remision_num.
     *
     * @return array
     */
    public function getEnviosPorRemision() {
        $stmt = $this->pdo->prepare("
            SELECT 
                rm.numero AS remision_num,
                SUM(rp.cantidad) AS cantidad_envio
            FROM remision_pallets rp
            JOIN remision rm ON rp.remision_id = rm.id
            WHERE rp.tipo = 'envio'
            GROUP BY rm.numero
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Obtiene los datos de los clientes con el nombre del titular asociado.
     *
     * @return array
     */
    public function getClientesParaExcel() {
        $stmt = $this->pdo->prepare("
            SELECT 
                c.codigo AS codigo_cliente,
                c.nombre AS nombre_cliente,
                t.nombre AS nombre_titular
            FROM clientes c
            JOIN titular t ON c.titular_id = t.id
            ORDER BY c.id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los datos de los titulares con el nombre del vendedor asociado.
     *
     * @return array
     */
    public function getTitularesParaExcel() {
        $stmt = $this->pdo->prepare("
            SELECT 
                t.codigo AS codigo_titular,
                t.nombre AS nombre_titular,
                u.nombre AS nombre_vendedor
            FROM titular t
            JOIN usuarios u ON t.usuario_id = u.id
            ORDER BY t.id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>