<?php

require_once "utils/lib/exel/vendor/autoload.php";
class DevolucionesController extends Controller
{
    private $conectar;

    public function __construct()
    {
        $this->conectar=(new Conexion())->getConexion();
    }

    public function render(){
        try {
            $sql = "
            SELECT
            dnv.id_devolucion,
            v.id_venta,
            v.fecha_emision,
            CONCAT(v.serie, ' | ', v.numero) AS factura,
            p.id_producto,
            p.descripcion,
            p.codsunat AS 'codigo',
            u.usuario,
            CONCAT(dnv.signo,dnv.cantidad) AS cantidad,
            dnv.fecha
            FROM devoluciones_nv dnv
            INNER JOIN ventas v ON v.id_venta = dnv.id_venta
            INNER JOIN productos p ON p.id_producto = dnv.id_producto
            INNER JOIN usuarios u ON u.usuario_id = dnv.id_usuario  
            ORDER BY dnv.fecha DESC
            ";
            $fila = mysqli_query($this->conectar, $sql);
            return json_encode(mysqli_fetch_all($fila, MYSQLI_ASSOC));
        } catch (Exception $e) {
            return json_encode([]);
        }
    }
    

    public function exportarExcel(){
        // Eliminar cualquier salida previa
        ob_end_clean();
        $sql = "
            SELECT
            dnv.id_devolucion,
            v.id_venta,
            v.fecha_emision,
            CONCAT(v.serie, ' | ', v.numero) AS factura,
            p.id_producto,
            p.descripcion,
            p.codsunat AS 'codigo',
            u.usuario,
            CONCAT(dnv.signo,dnv.cantidad) AS cantidad,
            dnv.fecha
            FROM devoluciones_nv dnv
            INNER JOIN ventas v ON v.id_venta = dnv.id_venta
            INNER JOIN productos p ON p.id_producto = dnv.id_producto
            INNER JOIN usuarios u ON u.usuario_id = dnv.id_usuario  
            ORDER BY dnv.fecha DESC
            ";
        $fila = mysqli_query($this->conectar, $sql);
        $data = mysqli_fetch_all($fila, MYSQLI_ASSOC);

        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'Reporte de Devoluciones');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $headers = [
            '#', 'FACTURA', 'F.EMISIÓN', 'CÓDIGO', 'PRODUCTO', 'CANTIDAD','USUARIO', 'FECHA'
        ];
        
        $row = 2;
        foreach ($headers as $key => $header) {
            $sheet->setCellValueByColumnAndRow($key + 1, 2, $header);
        }
        $sheet->getStyle("A$row:H$row")->getFill()->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF28719B');
        $sheet->getStyle("A$row:H$row")->getFont()->getColor()->setARGB(PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $sheet->getStyle("A$row:H$row")->getFont()->setBold(true);
        $sheet->getStyle("A$row:H$row")->getFont()->setBold(true)->setSize(12); 
        
        $row++;

        foreach ($data as $index => $rowData) {
            // Llenar datos por fila
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $rowData['factura'] ?? '');
            $sheet->setCellValue('C' . $row, $rowData['fecha_emision'] ?? '');
            $sheet->setCellValue('D' . $row, $rowData['codigo'] ?? '');
            $sheet->setCellValue('E' . $row, $rowData['descripcion'] ?? '');
            $sheet->setCellValue('F' . $row, $rowData['cantidad'] ?? '');
            $sheet->setCellValue('G' . $row, $rowData['usuario'] ?? '');
            $sheet->setCellValue('H' . $row, $rowData['fecha'] ?? '');

            $row++;
        }
        
        foreach (range('A', 'L') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Nombre del archivo
        $fileName = 'reporte_devoluciones.xlsx';
        // Enviar los encabezados para la descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        // Guardar el archivo y enviarlo al navegador
        $writer->save('php://output');
        exit();
    }

}