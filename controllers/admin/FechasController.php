<?php
// controllers/admin/FechasController.php 

require_once '../../models/ModeloFechas.php';
require_once '../../config/config.php'; // Asegúrate de que la ruta es correcta
require_once '../../vendor/autoload.php'; // Asegúrate de ajustar la ruta según tu estructura de carpetas

session_start();

// Verificar si el usuario está autenticado y tiene el rol correcto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: ../login.php");
    exit();
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FechasController {
    public $years = [];
    public $selectedYear = null;
    public $months = [];

    private $model;

    public function __construct($pdo) {
        $this->model = new ModeloFechas($pdo);
    }

    /**
     * Maneja la solicitud y prepara los datos para la vista.
     */
    public function handleRequest() {
        // Iniciar sesión si aún no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar si el usuario está autenticado y tiene el rol correcto
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
            header("Location: ../../login.php");
            exit();
        }

        // Obtener la lista de años
        $this->years = $this->model->getYears();

        // Obtener el año seleccionado del formulario
        $this->selectedYear = isset($_GET['year']) ? $_GET['year'] : null;

        // Obtener los meses correspondientes si se ha seleccionado un año
        if ($this->selectedYear) {
            $this->months = $this->model->getMonths($this->selectedYear);
        }

        // Verificar si se solicita la exportación
        if (isset($_GET['export']) && isset($_GET['year'])) {
            $exportType = $_GET['export'];
            $year = intval($_GET['year']);

            if ($exportType === 'recibir' && isset($_GET['month'])) {
                $month = intval($_GET['month']);
                if ($this->validarMes($month) && $this->validarAnio($year)) {
                    $this->exportarRecibirExcel($year, $month);
                } else {
                    // Mes o año inválido, redireccionar o mostrar error
                    header("Location: Fechas.php");
                    exit();
                }
            }

            if ($exportType === 'clientes') {
                $this->exportarClientesExcel();
            }

            if ($exportType === 'titulares') {
                $this->exportarTitularesExcel();
            }

            if ($exportType === 'todos') {
                $this->exportarTodosExcel($year);
            }
        }
    }

    /**
     * Valida si el número de mes es válido.
     *
     * @param int $month
     * @return bool
     */
    private function validarMes($month) {
        return ($month >= 1 && $month <= 12);
    }

    /**
     * Valida si el año es válido (por ejemplo, entre 2000 y el año actual + 1).
     *
     * @param int $year
     * @return bool
     */
    private function validarAnio($year) {
        $currentYear = (int)date('Y') + 1;
        return ($year >= 2000 && $year <= $currentYear);
    }

    /**
     * Función auxiliar para obtener el nombre del mes.
     *
     * @param int $monthNumber
     * @return string
     */
    private function getMonthName($monthNumber) {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return isset($months[$monthNumber]) ? $months[$monthNumber] : '';
    }

    /**
     * Exporta los datos de recibir a un archivo Excel.
     *
     * @param int $year
     * @param int $month
     */
    public function exportarRecibirExcel($year, $month) {
        // Obtener los datos de recibir para el año y mes especificados
        $recibir = $this->model->getRecibirPorMes($year, $month);

        // Obtener las cantidades de envios por remision_num
        $enviosPorRemision = $this->model->getEnviosPorRemision();

        // Crear una nueva hoja de cálculo
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Añadir el logo de la empresa
        $logoPath = '../../src/LOGO ESQUINA WEB.png'; // Asegúrate de que la ruta es correcta
        if (file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo Empresa');
            $drawing->setDescription('Logo Empresa');
            $drawing->setPath($logoPath);
            $drawing->setHeight(50); // Ajusta la altura según sea necesario
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);
        }

        // Añadir el título después del logo
        $sheet->setCellValue('A3', "Reporte de Recibir - " . $this->getMonthName($month) . " $year");
        $sheet->mergeCells('A3:M3');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Títulos
        $sheet->setCellValue('A5', 'N°');
        $sheet->setCellValue('B5', 'Fecha');
        $sheet->setCellValue('C5', 'Código Cliente');
        $sheet->setCellValue('D5', 'Nombre Cliente');
        $sheet->setCellValue('E5', 'Código Titular');
        $sheet->setCellValue('F5', 'Nombre Titular');
        $sheet->setCellValue('G5', 'Tamaño del Pallet');
        $sheet->setCellValue('H5', 'Cantidad');
        $sheet->setCellValue('I5', 'Número de Remisión');
        $sheet->setCellValue('J5', 'Conductor');
        $sheet->setCellValue('K5', 'Placa');
        $sheet->setCellValue('L5', 'Observación');

        // Aplicar estilos a los títulos
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A5:L5')->applyFromArray($headerStyle);

        // Añadir datos
        $row = 6;
        $numero = 1;

        // Preparar arrays para la suma y control de devoluciones
        $sumaRecibirActual = []; // Suma acumulada por remision_num
        $devolucionSet = []; // Controla si ya se ha establecido 'si' para remision_num

        foreach ($recibir as $record) {
            $remision = $record['remision_num'];
            if (!isset($sumaRecibirActual[$remision])) {
                $sumaRecibirActual[$remision] = 0;
                $devolucionSet[$remision] = false;
            }
            $sumaRecibirActual[$remision] += $record['cantidad'];

            // Obtener cantidad_envio para esta remision
            $cantidadEnvio = isset($enviosPorRemision[$remision]) ? $enviosPorRemision[$remision] : 0;

            // Determinar 'devolucion' solo si no ha sido seteado antes y la suma alcanza la cantidad_envio
            if (!$devolucionSet[$remision] && $sumaRecibirActual[$remision] == $cantidadEnvio && $cantidadEnvio > 0) {
                $devolucion = 'si';
                $devolucionSet[$remision] = true;
            } else {
                $devolucion = 'no';
            }

            $sheet->setCellValue('A' . $row, $numero);
            $sheet->setCellValue('B' . $row, $record['fecha']);
            $sheet->setCellValue('C' . $row, $record['codigo_cliente']);
            $sheet->setCellValue('D' . $row, $record['nombre_cliente']);
            $sheet->setCellValue('E' . $row, $record['codigo_titular']);
            $sheet->setCellValue('F' . $row, $record['nombre_titular']);
            $sheet->setCellValue('G' . $row, $record['tamano_pallet']);
            $sheet->setCellValue('H' . $row, $record['cantidad']);
            $sheet->setCellValue('I' . $row, $record['remision_num']);
            $sheet->setCellValue('J' . $row, $record['conductor']);
            $sheet->setCellValue('K' . $row, $record['placa']);
            $sheet->setCellValue('L' . $row, !empty($record['observacion']) ? $record['observacion'] : '');

            $row++;
            $numero++;
        }

        // Verificar si se agregaron datos
        if ($row === 6) {
            die("No se encontraron registros para exportar.");
        }

        // Autoajustar el ancho de las columnas
        foreach(range('A','M') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Añadir filtros a los títulos
        $sheet->setAutoFilter('A5:L5');

        // Establecer la fila de inicio después del logo y título
        $spreadsheet->getActiveSheet()->freezePane('A6');

        // Configurar la respuesta HTTP para descargar el archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="recibir_mes_'.$month.'_'.$year.'.xlsx"');
        header('Cache-Control: max-age=0');

        // Escribir el archivo Excel
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }

    /**
     * Exporta los datos de clientes a un archivo Excel.
     */
    public function exportarClientesExcel() {
        // Obtener los datos de clientes
        $clientes = $this->model->getClientesParaExcel();

        // Crear una nueva hoja de cálculo
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Añadir el logo de la empresa
        $logoPath = '../../src/LOGO ESQUINA WEB.png'; // Asegúrate de que la ruta es correcta
        if (file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo Empresa');
            $drawing->setDescription('Logo Empresa');
            $drawing->setPath($logoPath);
            $drawing->setHeight(50); // Ajusta la altura según sea necesario
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);
        }

        // Añadir el título después del logo
        $sheet->setCellValue('A3', "Reporte de Clientes - Año " . $this->selectedYear);
        $sheet->mergeCells('A3:D3');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Títulos
        $sheet->setCellValue('A5', 'N°');
        $sheet->setCellValue('B5', 'Código Cliente');
        $sheet->setCellValue('C5', 'Nombre Cliente');
        $sheet->setCellValue('D5', 'Nombre Titular');

        // Aplicar estilos a los títulos
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A5:D5')->applyFromArray($headerStyle);

        // Añadir datos
        $row = 6;
        $numero = 1;
        foreach ($clientes as $cliente) {
            $sheet->setCellValue('A' . $row, $numero);
            $sheet->setCellValue('B' . $row, $cliente['codigo_cliente']);
            $sheet->setCellValue('C' . $row, $cliente['nombre_cliente']);
            $sheet->setCellValue('D' . $row, $cliente['nombre_titular']);
            $row++;
            $numero++;
        }

        // Verificar si se agregaron datos
        if ($row === 6) {
            die("No se encontraron registros de clientes para exportar.");
        }

        // Autoajustar el ancho de las columnas
        foreach(range('A','D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Añadir filtros a los títulos
        $sheet->setAutoFilter('A5:D5');

        // Establecer la fila de inicio después del logo y título
        $spreadsheet->getActiveSheet()->freezePane('A6');

        // Configurar la respuesta HTTP para descargar el archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="clientes.xlsx"');
        header('Cache-Control: max-age=0');

        // Escribir el archivo Excel
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }

    /**
     * Exporta los datos de titulares a un archivo Excel.
     */
    public function exportarTitularesExcel() {
        // Obtener los datos de titulares
        $titulares = $this->model->getTitularesParaExcel();

        // Crear una nueva hoja de cálculo
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Añadir el logo de la empresa
        $logoPath = '../../src/LOGO ESQUINA WEB.png'; // Asegúrate de que la ruta es correcta
        if (file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo Empresa');
            $drawing->setDescription('Logo Empresa');
            $drawing->setPath($logoPath);
            $drawing->setHeight(50); // Ajusta la altura según sea necesario
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);
        }

        // Añadir el título después del logo
        $sheet->setCellValue('A3', "Reporte de Titulares - Año " . $this->selectedYear);
        $sheet->mergeCells('A3:D3');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Títulos
        $sheet->setCellValue('A5', 'N°');
        $sheet->setCellValue('B5', 'Código Titular');
        $sheet->setCellValue('C5', 'Nombre Titular');
        $sheet->setCellValue('D5', 'Nombre Vendedor');

        // Aplicar estilos a los títulos
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A5:D5')->applyFromArray($headerStyle);

        // Añadir datos
        $row = 6;
        $numero = 1;
        foreach ($titulares as $titular) {
            $sheet->setCellValue('A' . $row, $numero);
            $sheet->setCellValue('B' . $row, $titular['codigo_titular']);
            $sheet->setCellValue('C' . $row, $titular['nombre_titular']);
            $sheet->setCellValue('D' . $row, $titular['nombre_vendedor']);
            $row++;
            $numero++;
        }

        // Verificar si se agregaron datos
        if ($row === 6) {
            die("No se encontraron registros de titulares para exportar.");
        }

        // Autoajustar el ancho de las columnas
        foreach(range('A','D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Añadir filtros a los títulos
        $sheet->setAutoFilter('A5:D5');

        // Establecer la fila de inicio después del logo y título
        $spreadsheet->getActiveSheet()->freezePane('A6');

        // Configurar la respuesta HTTP para descargar el archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="titulares.xlsx"');
        header('Cache-Control: max-age=0');

        // Escribir el archivo Excel
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }

    /**
     * Exporta todos los datos (Clientes, Titulares y Recibir por mes) a un archivo Excel con múltiples hojas.
     *
     * @param int $year
     */
    public function exportarTodosExcel($year) {
        // Crear una nueva hoja de cálculo
        $spreadsheet = new Spreadsheet();

        // 1. Añadir hoja de Clientes
        $clientes = $this->model->getClientesParaExcel();
        $sheetClientes = $spreadsheet->getActiveSheet();
        $sheetClientes->setTitle('Clientes');

        // Añadir el logo de la empresa
        $logoPath = '../../src/LOGO ESQUINA WEB.png'; // Asegúrate de que la ruta es correcta
        if (file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo Empresa');
            $drawing->setDescription('Logo Empresa');
            $drawing->setPath($logoPath);
            $drawing->setHeight(50); // Ajusta la altura según sea necesario
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheetClientes);
        }

        // Añadir el título después del logo
        $sheetClientes->setCellValue('A3', "Reporte de Clientes - Año " . $year);
        $sheetClientes->mergeCells('A3:D3');
        $sheetClientes->getStyle('A3')->getFont()->setBold(true)->setSize(14);
        $sheetClientes->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Títulos
        $sheetClientes->setCellValue('A5', 'N°');
        $sheetClientes->setCellValue('B5', 'Código Cliente');
        $sheetClientes->setCellValue('C5', 'Nombre Cliente');
        $sheetClientes->setCellValue('D5', 'Nombre Titular');

        // Aplicar estilos a los títulos
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheetClientes->getStyle('A5:D5')->applyFromArray($headerStyle);

        // Añadir datos
        $row = 6;
        $numero = 1;
        foreach ($clientes as $cliente) {
            $sheetClientes->setCellValue('A' . $row, $numero);
            $sheetClientes->setCellValue('B' . $row, $cliente['codigo_cliente']);
            $sheetClientes->setCellValue('C' . $row, $cliente['nombre_cliente']);
            $sheetClientes->setCellValue('D' . $row, $cliente['nombre_titular']);
            $row++;
            $numero++;
        }

        // Verificar si se agregaron datos
        if ($row === 6) {
            die("No se encontraron registros de clientes para exportar.");
        }

        // Autoajustar el ancho de las columnas
        foreach(range('A','D') as $columnID) {
            $sheetClientes->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Añadir filtros a los títulos
        $sheetClientes->setAutoFilter('A5:D5');

        // Congelar panes
        $spreadsheet->getActiveSheet()->freezePane('A6');

        // 2. Añadir hoja de Titulares
        $titulares = $this->model->getTitularesParaExcel();
        $sheetTitulares = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Titulares');
        $spreadsheet->addSheet($sheetTitulares, 1);

        // Añadir el logo de la empresa
        if (file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo Empresa');
            $drawing->setDescription('Logo Empresa');
            $drawing->setPath($logoPath);
            $drawing->setHeight(50); // Ajusta la altura según sea necesario
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheetTitulares);
        }

        // Añadir el título después del logo
        $sheetTitulares->setCellValue('A3', "Reporte de Titulares - Año " . $year);
        $sheetTitulares->mergeCells('A3:D3');
        $sheetTitulares->getStyle('A3')->getFont()->setBold(true)->setSize(14);
        $sheetTitulares->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Títulos
        $sheetTitulares->setCellValue('A5', 'N°');
        $sheetTitulares->setCellValue('B5', 'Código Titular');
        $sheetTitulares->setCellValue('C5', 'Nombre Titular');
        $sheetTitulares->setCellValue('D5', 'Nombre Vendedor');

        // Aplicar estilos a los títulos
        $sheetTitulares->getStyle('A5:D5')->applyFromArray($headerStyle);

        // Añadir datos
        $row = 6;
        $numero = 1;
        foreach ($titulares as $titular) {
            $sheetTitulares->setCellValue('A' . $row, $numero);
            $sheetTitulares->setCellValue('B' . $row, $titular['codigo_titular']);
            $sheetTitulares->setCellValue('C' . $row, $titular['nombre_titular']);
            $sheetTitulares->setCellValue('D' . $row, $titular['nombre_vendedor']);
            $row++;
            $numero++;
        }

        // Verificar si se agregaron datos
        if ($row === 6) {
            die("No se encontraron registros de titulares para exportar.");
        }

        // Autoajustar el ancho de las columnas
        foreach(range('A','D') as $columnID) {
            $sheetTitulares->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Añadir filtros a los títulos
        $sheetTitulares->setAutoFilter('A5:D5');

        // Congelar panes
        $spreadsheet->getSheetByName('Titulares')->freezePane('A6');

        // 3. Añadir hojas para cada mes
        // Obtener todos los meses del año
        $months = $this->model->getMonths($year);

        foreach ($months as $month) {
            // Obtener el nombre del mes
            $monthName = $this->getMonthName($month);

            // Obtener los datos de recibir para este mes
            $recibir = $this->model->getRecibirPorMes($year, $month);

            // Obtener las cantidades de envios por remision_num
            $enviosPorRemision = $this->model->getEnviosPorRemision();

            // Crear una nueva hoja para el mes
            $sheetMes = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $monthName);
            $spreadsheet->addSheet($sheetMes);

            // Añadir el logo de la empresa
            if (file_exists($logoPath)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo Empresa');
                $drawing->setDescription('Logo Empresa');
                $drawing->setPath($logoPath);
                $drawing->setHeight(50); // Ajusta la altura según sea necesario
                $drawing->setCoordinates('A1');
                $drawing->setWorksheet($sheetMes);
            }

            // Añadir el título después del logo
            $sheetMes->setCellValue('A3', "Reporte de Recibir - $monthName $year");
            $sheetMes->mergeCells('A3:M3');
            $sheetMes->getStyle('A3')->getFont()->setBold(true)->setSize(14);
            $sheetMes->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Títulos
            $sheetMes->setCellValue('A5', 'N°');
            $sheetMes->setCellValue('B5', 'Fecha');
            $sheetMes->setCellValue('C5', 'Código Cliente');
            $sheetMes->setCellValue('D5', 'Nombre Cliente');
            $sheetMes->setCellValue('E5', 'Código Titular');
            $sheetMes->setCellValue('F5', 'Nombre Titular');
            $sheetMes->setCellValue('G5', 'Tamaño del Pallet');
            $sheetMes->setCellValue('H5', 'Cantidad');
            $sheetMes->setCellValue('I5', 'Número de Remisión');
            $sheetMes->setCellValue('J5', 'Conductor');
            $sheetMes->setCellValue('K5', 'Placa');
            $sheetMes->setCellValue('L5', 'Observación');

            // Aplicar estilos a los títulos
            $sheetMes->getStyle('A5:L5')->applyFromArray($headerStyle);

            // Añadir datos
            $row = 6;
            $numero = 1;

            // Preparar arrays para la suma y control de devoluciones
            $sumaRecibirActual = []; // Suma acumulada por remision_num
            $devolucionSet = []; // Controla si ya se ha establecido 'si' para remision_num

            foreach ($recibir as $record) {
                $remision = $record['remision_num'];
                if (!isset($sumaRecibirActual[$remision])) {
                    $sumaRecibirActual[$remision] = 0;
                    $devolucionSet[$remision] = false;
                }
                $sumaRecibirActual[$remision] += $record['cantidad'];

                // Obtener cantidad_envio para esta remision
                $cantidadEnvio = isset($enviosPorRemision[$remision]) ? $enviosPorRemision[$remision] : 0;

                // Determinar 'devolucion' solo si no ha sido seteado antes y la suma alcanza la cantidad_envio
                if (!$devolucionSet[$remision] && $sumaRecibirActual[$remision] == $cantidadEnvio && $cantidadEnvio > 0) {
                    $devolucion = 'si';
                    $devolucionSet[$remision] = true;
                } else {
                    $devolucion = 'no';
                }

                $sheetMes->setCellValue('A' . $row, $numero);
                $sheetMes->setCellValue('B' . $row, $record['fecha']);
                $sheetMes->setCellValue('C' . $row, $record['codigo_cliente']);
                $sheetMes->setCellValue('D' . $row, $record['nombre_cliente']);
                $sheetMes->setCellValue('E' . $row, $record['codigo_titular']);
                $sheetMes->setCellValue('F' . $row, $record['nombre_titular']);
                $sheetMes->setCellValue('G' . $row, $record['tamano_pallet']);
                $sheetMes->setCellValue('H' . $row, $record['cantidad']);
                $sheetMes->setCellValue('I' . $row, $record['remision_num']);
                $sheetMes->setCellValue('J' . $row, $record['conductor']);
                $sheetMes->setCellValue('K' . $row, $record['placa']);
                $sheetMes->setCellValue('L' . $row, !empty($record['observacion']) ? $record['observacion'] : '');

                $row++;
                $numero++;
            }

            // Verificar si se agregaron datos
            if ($row === 6) {
                // Opcional: Puedes optar por dejar la hoja vacía o agregar un mensaje
                $sheetMes->setCellValue('A6', "No se encontraron registros para $monthName $year.");
                $sheetMes->mergeCells('A6:M6');
                $sheetMes->getStyle('A6')->getFont()->setItalic(true);
                $sheetMes->getStyle('A6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            } else {
                // Autoajustar el ancho de las columnas
                foreach(range('A','M') as $columnID) {
                    $sheetMes->getColumnDimension($columnID)->setAutoSize(true);
                }

                // Añadir filtros a los títulos
                $sheetMes->setAutoFilter('A5:L5');

                // Congelar panes
                $sheetMes->freezePane('A6');
            }
        }

        // Eliminar la hoja por defecto si no es necesario
        $defaultSheet = $spreadsheet->getSheetByName('Worksheet');
        if ($defaultSheet) {
            $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($defaultSheet));
        }

        // Configurar la respuesta HTTP para descargar el archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="todos_reporte_'.$year.'.xlsx"');
        header('Cache-Control: max-age=0');

        // Escribir el archivo Excel
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }
}

// Instanciar el controlador y manejar la solicitud
$controller = new FechasController($pdo);
$controller->handleRequest();

// Nota: No se incluye la vista desde el controlador para evitar recursiones
?>