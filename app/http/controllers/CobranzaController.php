<?php

require_once 'app/models/Cobranza.php';
require_once "utils/lib/exel/vendor/autoload.php";

class CobranzaController extends Controller
{
    private $cobranza;

    public function __construct()
    {
        $this->cobranza = new Cobranza();
    }
    public function render()
    {
<<<<<<< HEAD
        try {
            $getAll = $this->cobranza->getAllCobranzas();
            
            if ($getAll === null || $getAll === false) {
                http_response_code(500);
                echo json_encode([
                    "error" => "Error al obtener cobranzas",
                    "message" => "La consulta no retornó datos válidos"
                ]);
                return;
            }
            
            echo json_encode($getAll);
        } catch (Exception $e) {
            http_response_code(500);
            error_log("Error en CobranzaController::render - " . $e->getMessage());
            echo json_encode([
                "error" => "Error al procesar cobranzas",
                "message" => $e->getMessage()
            ]);
        }
=======
        $getAll = $this->cobranza->getAllCobranzas();
        
        // Ordenar por mercado, cliente alfabético, y fecha
        usort($getAll, function ($a, $b) {
            // Ordenamiento: primero mercado (ASC), luego cliente (ASC alfabético), luego fecha (DESC)
            $mercadoA = $a['mercado'] === '' || $a['mercado'] === null ? 999 : (int) $a['mercado'];
            $mercadoB = $b['mercado'] === '' || $b['mercado'] === null ? 999 : (int) $b['mercado'];
            
            if ($mercadoA !== $mercadoB) {
                return $mercadoA - $mercadoB;
            }
            
            // Si mercados son iguales, comparar por nombre de cliente (ASC alfabético)
            $partsA = explode('|', $a['cliente']);
            $partsB = explode('|', $b['cliente']);
            
            $clienteA = isset($partsA[1]) ? trim($partsA[1]) : trim($partsA[0]);
            $clienteB = isset($partsB[1]) ? trim($partsB[1]) : trim($partsB[0]);
            
            // Remover información entre paréntesis para ordenar
            $clienteA = preg_replace('/\s*\([^)]*\)/', '', $clienteA);
            $clienteB = preg_replace('/\s*\([^)]*\)/', '', $clienteB);
            
            $clienteA_lower = strtolower($clienteA);
            $clienteB_lower = strtolower($clienteB);
            
            if ($clienteA_lower !== $clienteB_lower) {
                return strcmp($clienteA_lower, $clienteB_lower);
            }
            
            // Si clientes son iguales, comparar por fecha (DESC)
            return strcmp($b['fecha_emision'], $a['fecha_emision']);
        });
        
        echo json_encode($getAll);
>>>>>>> 9303b6e (combios de kardex motivos)
    }
    public function renderDeudas()
    {
        $getAll = $this->cobranza->getAllDeudas();
        echo json_encode($getAll);
    }
    public function getAllByIdVenta()
    {
        $getAll = $this->cobranza->getAllByIdVenta($_POST['id'],$_POST['tipo']);
        echo json_encode($getAll);
    }
    public function validarLista()
    {
        $listaPagos = json_decode($_POST['dias_lista'], true);
        echo json_encode($listaPagos);
    }
    public function pagarCuota()
    {
        $pagar = $this->cobranza->pagarCuota($_POST['id']);
        echo json_encode($pagar);
    }
    
    public function exportarExcel(){
        // Eliminar cualquier salida previa
        ob_end_clean();
        $data = $this->cobranza->getAllCobranzas();

        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'Reporte de Cobranzas');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $headers = [
            '#', 'Codigo', 'F.Emision', 'F.Vencimiento', 'Vendedor', 'Cliente','Ruta', 'Total', 'Pagado', 'Saldo', 'Situación','Dias Vencidos'
        ];
        
        $row = 2;
        foreach ($headers as $key => $header) {
            $sheet->setCellValueByColumnAndRow($key + 1, 2, $header);
        }
        $sheet->getStyle("A$row:L$row")->getFill()->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF28719B');
        $sheet->getStyle("A$row:L$row")->getFont()->getColor()->setARGB(PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $sheet->getStyle("A$row:L$row")->getFont()->setBold(true);
        $sheet->getStyle("A$row:L$row")->getFont()->setBold(true)->setSize(12); 
        
        $row++;

        foreach ($data as $index => $rowData) {
            // $ruta = $rowData['id_ruta'] ?? '';
            $situacion = "";
            $color = "02a499"; // Color por defecto
            $dias_vence = $rowData['dias_vence'] ?? 0;

            // Validar los datos de total, pagado, y saldo
            $total = isset($rowData['total']) && is_numeric($rowData['total']) ? $rowData['total'] : 0;
            $pagado = isset($rowData['pagado']) && is_numeric($rowData['pagado']) ? $rowData['pagado'] : 0;
            $saldo = isset($rowData['saldo']) && is_numeric($rowData['saldo']) ? $rowData['saldo'] : 0;


            if ($total == $pagado) {
                $dias_vence = 0;
                $situacion = 'Pagado';
            } elseif (($total > $pagado) && $dias_vence <= 0) {
                $situacion = 'Vigente';
                $dias_vence = abs($dias_vence);
                $color = "ec4561";
            } elseif (($total > $pagado) && $dias_vence > 0) {
                $situacion = 'Vencido';
                $color = "ec4561";
            } else {
                $dias_vence = 0;
                $situacion = 'Pagado';
            }

            // Llenar datos por fila
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $rowData['factura'] ?? '');
            $sheet->setCellValue('C' . $row, $rowData['fecha_emision'] ?? '');
            $sheet->setCellValue('D' . $row, $rowData['fecha_vencimiento'] ?? '');
            $sheet->setCellValue('E' . $row, $rowData['vendedor'] ?? '');
            $sheet->setCellValue('F' . $row, $rowData['cliente'] ?? '');
            $sheet->setCellValue('G' . $row, $rowData['id_ruta'] ?? '');
            $sheet->setCellValue('H' . $row, $total); 
            $sheet->setCellValue('I' . $row, $pagado);
            $sheet->setCellValue('J' . $row, $saldo);
            $sheet->setCellValue('K' . $row, $situacion);
            $sheet->setCellValue('L' . $row, $dias_vence);

            $sheet->getStyle("K$row:L$row")->getFont()->getColor()->setARGB($color);

            $row++;
        }
        
        foreach (range('A', 'L') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Nombre del archivo
        $fileName = 'reporte_cobranzas.xlsx';
        // Enviar los encabezados para la descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        // Guardar el archivo y enviarlo al navegador
        $writer->save('php://output');
        exit();
    }
    
}
