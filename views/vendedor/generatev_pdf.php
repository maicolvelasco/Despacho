<?php
require_once '../../libs/fpdf/fpdf.php'; 
require_once '../../config/config.php';
require_once '../../models/ModeloVendedor.php';

class PDF extends FPDF {
    function Header() {
        $this->Image('../../src/LOGO ESQUINA WEB ICONO.png', 10, 10, 30);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Remisiones Pendientes', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Verificar autenticación
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Vendedor') {
    die('Acceso denegado');
}

// Obtener fechas desde los parámetros de la URL
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

try {
    // Crear instancia del modelo de vendedor
    $vendedorModel = new ModeloVendedor($pdo);
    
    // Obtener el ID del usuario logueado
    $usuario_id = $_SESSION['usuario_id'];

    // Obtener envíos completados del vendedor
    $enviosCompletados = $vendedorModel->obtenerEnviosCompletados($usuario_id);
    
    // Obtener recepciones del vendedor
    $recibirRecibidos = $vendedorModel->obtenerRecibirRecibidos($usuario_id);

    // Filtrar envíos por fechas
    if ($fechaInicio && $fechaFin) {
        $enviosCompletados = array_filter($enviosCompletados, function($envio) use ($fechaInicio, $fechaFin) {
            return ($envio['fecha_inicio'] >= $fechaInicio && $envio['fecha_inicio'] <= $fechaFin);
        });
    }
    
    // Crear nuevo PDF
    $pdf = new PDF();
    $pdf->AddPage('L'); // Landscape para más espacio

    // Configurar tabla
    $pdf->SetFont('Arial', '', 10);
    
    // Encabezados de tabla
    $pdf->SetFillColor(240, 240, 240); // Color de fondo
    $pdf->Cell(30, 10, 'N° Remisión', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Cliente', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Cantidad Enviada', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Cantidad Recibida', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Cantidad Pendiente', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Fecha Inicio', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Fecha Final', 1, 1, 'C', true);

    // Fecha actual para comparación
    $today = new DateTime();

    // Array para almacenar totales por remisión
    $totalesPorRemision = [];

    // Primero, calcular totales de envíos
    foreach ($enviosCompletados as $envio) {
        $remision = $envio['remision_numero'];
        $cantidadEnviada = $envio['total_pallets'];

        if (!isset($totalesPorRemision[$remision])) {
            $totalesPorRemision[$remision] = [
                'enviada' => 0,
                'recibida' => 0,
                'cliente' => !empty($envio['cliente_nombre']) ? $envio['cliente_nombre'] : $envio['titular_nombre'],
                'fecha_inicio' => $envio['fecha_inicio'],
                'fecha_fin' => $envio['fecha_fin']
            ];
        }
        $totalesPorRemision[$remision]['enviada'] += $cantidadEnviada;
    }

    // Luego, sumar totales recibidos
    foreach ($recibirRecibidos as $recepcion) {
        $remision = $recepcion['remision_numero'];
        $cantidadRecibida = $recepcion['total_pallets_recibidos_total'] ?? 0;

        if (!isset($totalesPorRemision[$remision])) {
            continue; // Saltar si no hay envío correspondiente
        }

        $totalesPorRemision[$remision]['recibida'] += $cantidadRecibida;
    }

    // Mostrar solo remisiones con cantidad pendiente
    foreach ($totalesPorRemision as $remision => $datos) {
        $cantidadPendiente = $datos['enviada'] - $datos['recibida'];

        // Solo mostrar si hay cantidad pendiente mayor a 0
        if ($cantidadPendiente > 0) {
            // Determinar color de fondo
            $fechaFin = new DateTime($datos['fecha_fin']);
            $fillColor = ($fechaFin <= $today) ? [255, 230, 230] : [255, 255, 255]; // Rojo suave si la fecha ha pasado
    
            $pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
            $pdf->Cell(30, 10, $remision, 1, 0, 'C', true);
            
            // Asegurarse de que se muestre el cliente o titular
            $clienteOMarca = !empty($datos['cliente']) ? $datos['cliente'] : 'N/A';
            $pdf->Cell(50, 10, $clienteOMarca, 1, 0, 'C', true);
            
            $pdf->Cell(40, 10, $datos['enviada'], 1, 0, 'C', true);
            $pdf->Cell(40, 10, $datos['recibida'], 1, 0, 'C', true);
            $pdf->Cell(40, 10, $cantidadPendiente, 1, 0, 'C', true);
            $pdf->Cell(40, 10, date('d/m/Y', strtotime($datos['fecha_inicio'])), 1, 0, 'C', true);
            $pdf->Cell(40, 10, date('d/m/Y', strtotime($datos['fecha_fin'])), 1, 1, 'C', true);
        }
    }

    // Salida del PDF
    $pdf->Output('D', 'remisiones_pendientes_vendedor_' . date('Y-m-d') . '.pdf');
} catch (Exception $e) {
    die('Error al generar el PDF: ' . $e->getMessage());
}