<?php

require_once "app/models/Venta.php";
require_once "app/models/Varios.php";
require_once "app/models/DocumentoSunat.php";
require_once 'utils/lib/mpdf/vendor/autoload.php';



class GeneradoresController extends Controller
{
    private $conexion;

    public function __construct()
    {
        $this->conexion =(new Conexion())->getConexion();
    }

    public function reportePeriodoVenta($periodo){

        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8','format' => 'A4']);



        $empresa = $this->conexion->query("select * from empresas
        where id_empresa = '{$_SESSION['id_empresa']}'")->fetch_assoc();

        $c_venta = new Venta();
        $c_varios = new Varios();
        $c_venta->setIdEmpresa($_SESSION['id_empresa']);
        $a_ventas = $c_venta->verFilasPeriodo($periodo);
        $item = 1;
        $suma_total = 0;
        $rowHmtl ="";
        foreach ($a_ventas  as $fila){
            $total= 0;
            $cliente = "          **** DOCUMENTO ANULADO **** ";
            if ($fila['estado'] == 1) {
                $total = $fila['total'];
                $cliente = $fila['documento'] . " | " . utf8_decode($fila['datos']);
            }
            $suma_total+= $total;
            $codigo = $periodo . $c_varios->zerofill($item, 3);
            $documento_venta = $fila['abreviatura'] . " | " . $fila['serie'] . " - " . $c_varios->zerofill($fila['numero'], 3);

            $subtotal =  number_format($total / 1.18, 2);
            $igv = number_format($total / 1.18 * 0.18, 2);
            $total = number_format($total, 2);
            $cliente = utf8_encode($cliente);
            $rowHmtl .="<tr>
                        <td style='font-size: 9px'>$codigo</td>
                        <td style='font-size: 9px'>{$fila['fecha_emision']}</td>
                        <td style='font-size: 9px'>$documento_venta</td>
                        <td style='font-size: 9px'>$cliente</td>
                        <td style='font-size: 9px'>$subtotal</td>
                        <td style='font-size: 9px'>$igv</td>
                        <td style='font-size: 9px'>$total</td>
                    </tr>";
        }


        $mpdf->WriteHTML("
        table, th, td {
          border: 1px solid black;
          border-collapse: collapse;
        }
        ",\Mpdf\HTMLParserMode::HEADER_CSS);

        $html="
         
        <div style='width: 100%; '>
            <div style='width: 100%; text-align: center;'>
                    <h2 style=''>REPORTE DE VENTAS DEL PERIODO: $periodo</h2>
            </div>
            <div style='width: 100%;'>
                <table style='width: 100%;'>
                    <tr>
                        <td>EMPRESA:</td>
                        <td>{$empresa["ruc"]} | {$empresa['razon_social']}</td>
                    </tr>
                </table>
            </div>
            
            <div style='width: 100%; margin-top:40px;'>
                <table style='width: 100%; text-align: center;' >
                    <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Fecha</th>
                        <th>Documento</th>
                        <th>Cliente</th>
                        <th>SubTotal</th>
                        <th>IGV</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                   <tbody>
                    $rowHmtl
                    </tbody>
                </table>
            </div>
            
        </div>
        ";
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        $mpdf->Output();
    }

    public function generarTextLibroVentas(){
        $anio = $_POST["anio"];
        $mes = $_POST["mes"];

        $cl_venta = new Venta();
        $cl_varios = new Varios();
        //$cl_amarre = new VentaReferencia();
        $cl_notaventa = new Venta();
        $cl_tido = new DocumentoSunat();

        $periodo=$anio.$mes;

        $empresa = $this->conexion->query("select * from empresas 
        where id_empresa = '{$_SESSION['id_empresa']}'")->fetch_assoc();

        $cl_venta->setIdEmpresa($empresa['id_empresa']);
        $a_ventas = $cl_venta->verFilasPeriodo($periodo);
        $contar = 0;

        $file_txt = "LE" . $empresa["ruc"] . $periodo . "00140100001111.txt";

        $contenido='';

        foreach ($a_ventas as $value) {
            $contar++;

            $fecha_doc = $value['fecha_emision'];
            $date = new DateTime($fecha_doc);
            $formato_fecha_doc = $date->format('Ymd');
            // echo "<br>" . $formato_fecha_doc;
            $fecha_periodo = $periodo . "00";
            //echo "<br>" . $fecha_periodo;
            if ($formato_fecha_doc < $fecha_periodo) {
                $estado = 6;
            } else {
                $estado = 1;
            }

            $documento_proveedor = $value['documento'];
            if (strlen($documento_proveedor) == 11) {
                $tipo_doc_proveedor = 6;
            }
            if (strlen($documento_proveedor) == 8) {
                $tipo_doc_proveedor = 1;
            }
            if (strlen($documento_proveedor) < 8) {
                $tipo_doc_proveedor = 0;
            }

            $fecha_amarre = "";
            $doc_amarre = "";
            $serie_amarre = "";
            $numero_amarre = "";

            /*if ($value['id_tido'] == 3 || $value['id_tido']== 4) {
                $cl_amarre->setIdNota($value['id_venta']);
                $cl_amarre->obtenerDatos();

                $cl_notaventa->setIdVenta($cl_amarre->getIdVenta());
                $cl_notaventa->obtenerDatos();

                $fecha_amarre = $cl_varios->fecha_tabla($cl_notaventa->getFecha());
                if ($cl_notaventa->getIdTido() == 1) {
                    $doc_amarre = "03";
                }
                if ($cl_notaventa->getIdTido() == 2) {
                    $doc_amarre = "01";
                }
                $serie_amarre = $cl_notaventa->getSerie();
                $numero_amarre = $cl_notaventa->getNumero();
            }*/

            $moneda = "PEN";

            $cl_tido->setIdTido($value['id_tido']);
            $cl_tido->obtenerDatos();
            $serie_doc = $value['serie'];
            $cod_sunat = $cl_tido->getCodSunat();
            $serie_doc = $cl_varios->zerofill($value['serie'], 4);

            $monto_total_soles = $value['total'] * 1;
            $base = $monto_total_soles / 1.18;
            $igv = $base * 0.18;

            $contenido .= $periodo . "00|" .
                $cl_varios->zerofill($value['id_venta'], 4) . "|" .
                "M" . $cl_varios->zerofill($contar, 3) ."|" .
                $cl_varios->fecha_tabla($value['fecha_emision']) . "|" .
                "|" .
                $cl_varios->zerofill($cod_sunat, 2) . "|" .
                strtoupper($serie_doc) . "|" .
                $cl_varios->zerofill($value['numero'], 4) . "|" .
                "|" .
                $tipo_doc_proveedor . "|" .
                $documento_proveedor . "|" .
                utf8_decode($value['datos']) . "|" .
                "|" .
                number_format($base, 2, ".", "") . "|" .
                "0.00|" .
                number_format($igv, 2, ".", "") . "|" .
                "0.00|" .
                "0.00|" .
                "0.00|" .
                "0.00|" .
                "0.00|" .
                "0.00|" .
                "0.00|" .
                "0.00|" .
                number_format($monto_total_soles, 2, ".", "") . "|" .
                $moneda . "|" .
                "1.000" .
                "|" . $fecha_amarre .
                "|" . $doc_amarre .
                "|" . $serie_amarre .
                "|" . $numero_amarre .
                "|" .
                "|" .
                "|" .
                "|" .
                $estado . "|".PHP_EOL;

        }

        $sql="select ne.*, ds.abreviatura,c.documento, c.datos,nes.nombre_xml
        from notas_electronicas ne 
        join documentos_sunat ds on ne.tido = ds.id_tido
        join clientes c on ne.id_empresa = c.id_empresa
        join notas_electronicas_sunat nes on ne.nota_id = nes.id_notas_electronicas 
        where ne.id_empresa = '{$_SESSION['id_empresa']}' and ne.sucursal='{$_SESSION['sucursal']}' 
        and  concat(year(ne.fecha), LPAD(month(ne.fecha), 2, 0)) = '$periodo' 
        order by ne.fecha asc, ne.numero asc";

        $listaNotas= $this->conexion->query($sql);

        foreach ($listaNotas as $value) {
            $contar++;

            $fecha_doc = $value['fecha'];
            $date = new DateTime($fecha_doc);
            $formato_fecha_doc = $date->format('Ymd');
            // echo "<br>" . $formato_fecha_doc;
            $fecha_periodo = $periodo . "00";
            //echo "<br>" . $fecha_periodo;
            if ($formato_fecha_doc < $fecha_periodo) {
                $estado = 6;
            } else {
                $estado = 1;
            }

            $documento_proveedor = $value['documento'];
            if (strlen($documento_proveedor) == 11) {
                $tipo_doc_proveedor = 6;
            }
            if (strlen($documento_proveedor) == 8) {
                $tipo_doc_proveedor = 1;
            }
            if (strlen($documento_proveedor) < 8) {
                $tipo_doc_proveedor = 0;
            }

            $fecha_amarre = "";
            $doc_amarre = "";
            $serie_amarre = "";
            $numero_amarre = "";

            if ($value['tido'] == 3 || $value['tido']== 4) {

                $sql="select * from ventas where id_venta = '{$value['id_venta']}'";

                $ventaRef = $this->conexion->query($sql)->fetch_assoc();



                $fecha_amarre = $cl_varios->fecha_tabla($ventaRef["fecha_emision"]);
                if ($ventaRef['id_tido'] == 1) {
                    $doc_amarre = "03";
                }
                if ($ventaRef['id_tido'] == 2) {
                    $doc_amarre = "01";
                }
                $serie_amarre =$ventaRef['serie'];
                $numero_amarre = $ventaRef['numero'];
            }

            $moneda = "PEN";

            $cl_tido->setIdTido($value['tido']);
            $cl_tido->obtenerDatos();
            $serie_doc = $value['serie'];
            $cod_sunat = $cl_tido->getCodSunat();
            $serie_doc = $cl_varios->zerofill($value['serie'], 4);

            $monto_total_soles = $value['monto'] * 1;
            $base = $monto_total_soles / 1.18;
            $igv = $base * 0.18;

            $contenido .= $periodo . "00|" .
                $cl_varios->zerofill($value['nota_id'], 4) . "|" .
                "M" . $cl_varios->zerofill($contar, 3) ."|" .
                $cl_varios->fecha_tabla($value['fecha']) . "|" .
                "|" .
                $cl_varios->zerofill($cod_sunat, 2) . "|" .
                strtoupper($serie_doc) . "|" .
                $cl_varios->zerofill($value['numero'], 4) . "|" .
                "|" .
                $tipo_doc_proveedor . "|" .
                $documento_proveedor . "|" .
                utf8_decode($value['datos']) . "|" .
                "|" .
                number_format($base, 2, ".", "") . "|" .
                "0.00|" .
                number_format($igv, 2, ".", "") . "|" .
                "0.00|" .
                "0.00|" .
                "0.00|" .
                "0.00|" .
                "0.00|" .
                "0.00|" .
                "0.00|" .
                "0.00|" .
                number_format($monto_total_soles, 2, ".", "") . "|" .
                $moneda . "|" .
                "1.000" .
                "|" . $fecha_amarre .
                "|" . $doc_amarre .
                "|" . $serie_amarre .
                "|" . $numero_amarre .
                "|" .
                "|" .
                "|" .
                "|" .
                $estado . "|".PHP_EOL;

        }


        $file = fopen("files/temp/".$file_txt, "w");

        fwrite($file, $contenido);

        fclose($file);

        return json_encode(["file"=>$file_txt]);

    }

}