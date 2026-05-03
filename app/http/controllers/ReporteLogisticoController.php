<?php

require_once 'utils/lib/mpdf/vendor/autoload.php';
require_once 'utils/lib/vendor/autoload.php';
require_once "app/models/Venta.php";
require_once "app/models/Cliente.php";
require_once "app/models/DocumentoEmpresa.php";
require_once "app/models/ProductoVenta.php";

class ReporteLogisticoController extends Controller
{
    private $conexion;
    private $mpdf;

    public function __construct()
    {
        $this->mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 0]);
        $this->conexion = (new Conexion())->getConexion();
    }

    public function reporteLogistico()
    {
        $fechaInicio = $_GET['fechaInicio'] ?? '';
        $fechaFin = $_GET['fechaFin'] ?? '';
        $camion = $_GET['camion'] ?? '';
        $medida = $_GET['medida'] ?? '';
        $diasVisita = $_GET['diasVisita'] ?? '';
        $horario = $_GET['horario'] ?? '';

        $queryClientes = " AND DATE(co.fecha) BETWEEN '$fechaInicio' AND '$fechaFin' ";

        if ($horario != "") {
            if ($horario == 'diurno') {
                $queryClientes .= " AND TIME(co.fecha_registro) >= '08:00:00' AND TIME(co.fecha_registro) < '15:00:00' ";
            }
            if ($horario == 'nocturno') {
                $queryClientes .= " AND (TIME(co.fecha_registro) >= '15:00:00' OR TIME(co.fecha_registro) < '08:00:00') ";
            }
            if ($horario == 'todos') {
                $queryClientes .= " AND TIME(co.fecha_registro) >= '00:00:00' AND TIME(co.fecha_registro) <= '23:59:59' ";
            }
            if ($horario == 'primer_corte') {
                // Hora 0 a 37: desde fechaInicio 00:00 hasta 13:00 del día siguiente
                $queryClientes .= " AND co.fecha_registro >= '{$fechaInicio} 00:00:00' AND co.fecha_registro < DATE_ADD('{$fechaInicio} 00:00:00', INTERVAL 37 HOUR) ";
            }
            if ($horario == 'segundo_corte') {
                // Hora 37 a 48: desde las 13:00 del día siguiente hasta las 23:59 del segundo día
                $queryClientes .= " AND co.fecha_registro >= DATE_ADD('{$fechaInicio} 00:00:00', INTERVAL 37 HOUR) AND co.fecha_registro <= DATE_ADD('{$fechaInicio} 00:00:00', INTERVAL 48 HOUR) ";
            }
        }

        if ($camion !== '0' && $camion !== '') {
            $filtros = array();
            switch ($camion) {
                case '1':
                    $filtros = [
                        'lunes' => ['1', '7'],
                        'martes' => ['5', '7'],
                        'miercoles' => ['5'],
                        'jueves' => ['1', '7'],
                        'viernes' => ['6', '7'],
                        'sabado' => ['7', '8'],
                    ];
                    break;
                case '2':
                    $filtros = [
                        'lunes' => ['3', '6'],
                        'martes' => ['1', '3'],
                        'miercoles' => ['1', '3'],
                        'jueves' => ['6', '3'],
                        'viernes' => ['3', '5'],
                        'sabado' => ['3', '6'],
                    ];
                    break;
                case '3':
                    $filtros = [
                        'miercoles' => ['6', '7'],
                        'viernes' => ['8', '2'],
                        'sabado' => ['1', '5'],
                    ];
                    break;
            }

            if ($diasVisita != "" && isset($filtros[$diasVisita])) {
                $filtros = [
                    $diasVisita => $filtros[$diasVisita]
                ];
            }

            $arrQueryClientes = array();
            foreach ($filtros as $key => $filtro) {
                $arrQueryClientes[] = "( c.dias_visitas = '{$key}' AND c.id_ruta IN (" . implode(',', $filtro) . ") )";
            }
            if (sizeof($arrQueryClientes) > 0) {
                $queryClientes .= " AND (" . implode(' OR ', $arrQueryClientes) . ")";
            }
        } elseif ($diasVisita != "") {
            $queryClientes .= " AND c.dias_visitas = '{$diasVisita}' ";
        }

        $sql = "SELECT co.cotizacion_id 
                FROM clientes c 
                INNER JOIN cotizaciones co ON co.id_cliente = c.id_cliente   
                WHERE co.id_empresa='{$_SESSION['id_empresa']}'
                AND co.sucursal='{$_SESSION['sucursal']}'
                AND co.estado!=2 " . $queryClientes;

        // Para consolidado logístico, agrupamos considerando el código de producto y la presentación
        $query_productos = "SELECT p.codigo,
                pc.id_producto, p.descripcion, p.peso_bruto,
                pc.presenta_cnt AS total_medida, pc.medida,
                SUM(pc.cantidad) AS total_cantidad, 
                SUM(pc.cantidad * pc.presenta_cnt) AS total_multiplicado
                FROM productos_cotis pc
                INNER JOIN productos p ON p.id_producto = pc.id_producto
                WHERE pc.id_coti IN ($sql)";

        if (!empty($medida)) {
            $query_productos .= " AND pc.medida = '$medida'";
        }

        $query_productos .= " GROUP BY p.codigo, pc.id_producto, p.descripcion, pc.presenta_cnt, pc.medida ORDER BY p.descripcion ASC";

        $listaProd = $this->conexion->query($query_productos);

        $html = "
        <style>
            body { font-family: Arial, sans-serif; color: #000000; font-weight: bold; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #000; padding: 4px; font-size: 11px; text-align: center; }
            .left { text-align: left; }
            h1 { text-align: center; font-size: 18px; margin-bottom: 5px; }
            p { margin: 2px 0; font-size: 12px; }
        </style>
        ";

        $camionTexto = ($camion === '0' || $camion === '') ? 'Todos' : $camion;
        
        $html .= "<h1>Consolidado Logístico</h1>";
        $html .= "<p><strong>Camión:</strong> $camionTexto</p>";
        $html .= "<p><strong>Periodo:</strong> $fechaInicio al $fechaFin</p>";
        if (!empty($diasVisita)) {
            $html .= "<p><strong>Día de visita:</strong> " . ucfirst($diasVisita) . "</p>";
        }
        if (!empty($horario)) {
            $horarioTexto = [
                'todos'         => 'Todos',
                'diurno'        => 'Diurno (08:00 - 15:00)',
                'nocturno'      => 'Nocturno (15:00 - 07:59)',
                'primer_corte'  => 'Primer Corte (Hora 0 a 37 — Día 1 00:00 a Día 2 13:00)',
                'segundo_corte' => 'Segundo Corte (Hora 37 a 48 — Día 2 13:00 a 23:59)',
            ];
            $html .= "<p><strong>Horario:</strong> " . ($horarioTexto[$horario] ?? ucfirst($horario)) . "</p>";
        }
        if (!empty($medida)) {
            $html .= "<p><strong>Medida:</strong> $medida</p>";
        }

        $html .= "
        <table>
            <thead>
                <tr>
                    <th>item</th>
                    <th>Código</th>
                    <th>M</th>
                    <th>PRODUCTO</th>
                    <th>UNIDAD</th>
                    <th>MEDIDA</th>
                    <th>CANTIDAD</th>
                </tr>
            </thead>
            <tbody>
        ";

        $contador = 1;

        if ($listaProd && $listaProd->num_rows > 0) {
            foreach ($listaProd as $prod) {
                // Formateo de cantidades para evitar decimales innecesarios si son enteros
                $m_multiplicado = number_format($prod['total_multiplicado'], 0);
                $cantidad_real = number_format($prod['total_cantidad'], 0);
                $medida_cnt = floatval($prod['total_medida']); // o number_format si lo prefiere

                $html .= "<tr>
                    <td>{$contador}</td>
                    <td>" . trim($prod['codigo']) . "</td>
                    <td>{$m_multiplicado}</td>
                    <td class='left'>{$prod['descripcion']}</td>
                    <td>{$prod['medida']}</td>
                    <td>{$medida_cnt}</td>
                    <td>{$cantidad_real}</td>
                </tr>";
                $contador++;
            }
        } else {
            $html .= "<tr><td colspan='7'>No hay datos para mostrar</td></tr>";
        }

        $html .= "
            </tbody>
        </table>
        ";

        // Mpdf settings para que reconozca los estilos
        $mpdf = new \Mpdf\Mpdf([
            "format" => "A4",
            "mode" => "utf-8"
        ]);
        
        // Agregar CSS para color negro y negrita
        // Pasando el HTML completo sin forzar HTMLParserMode para que evalúe correctamente las etiquetas <style>
        $mpdf->WriteHTML($html);

        $mpdf->Output("Consolidado_Logistico.pdf", 'I');
    }
}
