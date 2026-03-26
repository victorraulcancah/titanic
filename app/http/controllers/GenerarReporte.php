<?php

require_once 'utils/lib/vendor/autoload.php';
require_once 'utils/lib/mpdf/vendor/autoload.php';
require_once 'utils/lib/exel/vendor/autoload.php';



class GenerarReporte extends Controller
{
    private $conexion;
    /*  private $mpdf; */

    public function __construct()
    {
        /*  $this->mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']); */
        $this->conexion = (new Conexion())->getConexion();
    }


    public function ingresosEgresos($id)
    {
        $mpdf  = new \Mpdf\Mpdf([
    'margin_bottom' => 5,
    'margin_top' => 10,
    'margin_left' => 4,
    'margin_right' => 4,
    'mode' => 'utf-8',
]);

$empresa = $this->conexion->query("select * from empresas
        where id_empresa = '{$_SESSION['id_empresa']}'")->fetch_assoc();

$rowHmtl = '';
        $sql = "SELECT ingreso_egreso.*,productos.descripcion,IF(ingreso_egreso.tipo = 'e', 'Egreso', 'Ingreso') AS tipoIntercambio FROM ingreso_egreso JOIN productos ON ingreso_egreso.id_producto=productos.id_producto WHERE intercambio_id = '$id'";
        $result = $this->conexion->query($sql)->fetch_assoc();

        $sql = "SELECT * FROM usuarios WHERE usuario_id = {$result['id_usuario']}";
        $resul2 = $this->conexion->query($sql)->fetch_assoc();
        /*  $sql */
        $dominio = DOMINIO;
       $rowHmtl .= "<tr>
        <td style='border-bottom:1px solid black;font-size: 11px'>{$result['cantidad']}</td>
        <td style='border-bottom:1px solid black;font-size: 11px'>Almacen {$result['almacen_ingreso']}</td>
        <td style='border-bottom:1px solid black;font-size: 11px'>Almacen {$result['almacen_egreso']}</td>
        <td style='border-bottom:1px solid black;font-size: 11px'>{$resul2['nombres']}</td>
    </tr>";

        $html = "
        <div style='width: 100%'>
        <table style='width:100%;margin-bottom: 10px'>
          <tr>
            <td align='center'>
              <img style=' max-width: 85%;' src='" . URL::to('files/logos/' . $empresa['logo']) . "'>
        </td>
        </tr>
        </table>
            <div style='width: 100%;text-align: center'>
                <span style='font-size: 13px;font-weight: bold'>{$empresa["razon_social"]} </span>
            </div>
            <div style='width: 100%;text-align: center'>
                <span style='font-size: 12px'>RUC: {$empresa["ruc"]}</span>
            </div>
            <div style='width: 100%;text-align: center'>
                <span style='font-size: 12px'>{$empresa["direccion"]}</span>
            </div>
            <div style='width: 100%;text-align: center'>
                <span style='font-size: 12px'>{$empresa["telefono"]}</span>
            </div>
            <hr>
            <div style='width: 100%;text-align: center'>
                <table style='width:100%'>
                  <tr>
                    <td style='font-size: 11px;width: 25%'><strong>Codigo:</strong></td>
                    <td style='font-size: 11px;'>{$result['intercambio_id']}</td>
                  </tr>
                  <tr>
                    <td style='font-size: 11px;width: 25%'><strong>Producto:</strong></td>
                    <td style='font-size: 11px;'>{$result['descripcion']}</td>
                  </tr>
                  <tr>
                    <td style='font-size: 11px;width: 25%'><strong>Tipo:</strong></td>
                    <td style='font-size: 11px;'>{$result['tipoIntercambio']}</td>
                  </tr>
                </table>
            </div>
            <div style='width: 100%;text-align: center'>
                <span style='font-size: 13px;'>---------------------- Detalle -----------------------</span>
            </div>
            <div style='width: 100%;'>
                <table style='width: 100%; text-align: center;' >
                    <thead>
                    <tr>
                        <th style='border-bottom:1px solid black;font-size: 11px'>Cantidad</th>
                        <th style='border-bottom:1px solid black;font-size: 11px'>Ingreso</th>
                        <th style='border-bottom:1px solid black;font-size: 11px'>Egreso</th>
                        <th style='border-bottom:1px solid black;font-size: 11px'>Hecho por</th>
                    </tr>
                    </thead>
                   <tbody>
                   $rowHmtl
                    </tbody>
                </table>
            </div>
            <div style='width: 100%;'>
        <span style='font-size: 12px'><b>Observaciones:</b></span>
    </div>
    <br>
     <div style='width: 100%;text-align: center; margin-top:40px'>
        <span style='font-size: 12px'>Representación impresa de la Intercambio de Productos <br>Este documento puede ser validado en $dominio</span>
    </div>
    <div style='width: 100%;text-align: center'>
        <span style='font-size: 12px'>Gracias por su preferencia....</span>
    </div>
        </div>";

$mpdf->AddPageByArray([
    "orientation" => "P",
    "newformat" => [80, 240 - 20]
]);
$mpdf->WriteHTML($html);
$mpdf->Output();

    }
    public function generarExcelProducto(){

        $texto = $_GET['texto'];
        $sql="select descripcion,MIN(codigo) AS codigo,
 MIN(costo) as costo,
       SUM(CASE WHEN almacen = 1 THEN cantidad ELSE 0 END) AS cantidad1, SUM(CASE WHEN almacen = 2 THEN cantidad ELSE 0 END) AS cantidad2 
        from productos where descripcion like '%$texto%' or codigo like '%$texto%' GROUP BY descripcion;";

        $result = $this->conexion->query($sql);

        foreach ($result as $fila) {

            // $tbody .= '
            $tbody = '
            <tr>
                <td>' . $fila['codigo'] . '</td>            
                <td>' . $fila['descripcion'] . '</td>            
                <td>' . $fila['costo'] . '</td>            
                <td>' . $fila['cantidad1'] . '</td>            
                <td>' . $fila['cantidad2'] . '</td>         
                         
            </tr>';
        }

        $tabla = "
        <table>
            <tr>
                    <th style='background-color: #90BFEB;width:10px'>Codigo</th>
                    <th style='background-color: #90BFEB;width:85px'>Descripcion</th>
                    <th style='background-color: #90BFEB;width:7px'>Costo</th>
                    <th style='background-color: #90BFEB;width:7px'>CNT A1</th>
                    <th style='background-color: #90BFEB;width:8px'>CNT A2</th> 
            </tr>
            <tbody>
                " . $tbody . "
            </tbody>
        </table>";




        /*   return ($arrayRes);  */
        $nombre_exel = "reporteproductosstock.xlsx";
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
        $spreadsheet = $reader->loadFromString($tabla);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");


        $writer->save($nombre_exel);
        header('Location: ' . URL::to($nombre_exel));
    }
    public function generarExcel($id)
    {

        $explodeFecha = explode('-', $id);
        $anio =  $explodeFecha[0];
        $mes =  $explodeFecha[1];
        $sql = 'SELECT *,CASE
        WHEN v1.cnt_pv > 0 THEN "VENTA DE MERCADERIA"
        ELSE "VENTA DE SERVICIO"
        END GLOSA
        FROM
        (SELECT v.id_venta,v.id_tido,ds.abreviatura,CONCAT(v.serie , "-" ,LPAD(v.numero,8,0)) AS documento, 
            v.fecha_emision,v.fecha_vencimiento,
            IF(ISNULL(c.documento), "", c.documento) AS codigocliente,IF(ISNULL(c.datos), "", c.datos) AS datos,
            IF(v.enviado_sunat = 1, "Si", "No") AS enviado,v.total,
            IF(ISNULL(gr.serie) ,"",CONCAT(gr.serie , "-" ,LPAD(v.numero,8,0))) AS guia,
            (
        SELECT COUNT(*) FROM productos_ventas pv WHERE pv.id_venta= v.id_venta
        ) cnt_pv,
        (SELECT COUNT(*) FROM ventas_servicios vs WHERE vs.id_venta= v.id_venta) cnt_sv
            FROM ventas AS v 
            JOIN documentos_sunat AS ds
            ON ds.id_tido=v.id_tido
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN guia_remision AS gr ON gr.id_venta=v.id_venta
            WHERE  YEAR(v.fecha_emision) =' . $anio . ' AND MONTH(v.fecha_emision) = ' . $mes . ' AND v.id_empresa=' . $_SESSION['id_empresa'] . ')v1';

        $result = $this->conexion->query($sql);
        $tabla = '';
        $tbody = '';
        foreach ($result as $fila) {
            if ($fila['id_tido']!='1'&&$fila['id_tido']!='2'){
                continue;
            }
            $tbody .= '
            <tr>
                <td>' . $fila['id_venta'] . '</td>            
                <td>' . $fila['abreviatura'] . '</td>            
                <td>' . $fila['documento'] . '</td>            
                <td>' . $fila['fecha_emision'] . '</td>            
                <td>' . $fila['fecha_vencimiento'] . '</td>            
                <td style="text-align:center">' . $fila['codigocliente'] . '</td>            
                <td>' . $fila['datos'] . '</td>            
                <td>' . $fila['enviado'] . '</td>            
                <td>S</td>            
                <td>' . $fila['total'] . '</td>            
                <td>' . $fila['total'] . '</td>            
                <td>' . $fila['GLOSA'] . '</td>            
                      
                <td>' . $fila['total'] . '</td>             
                <td>E</td>             
                <td>' . $fila['guia'] . '</td>             
                <td>Oficina</td>             
                <td>' . $fila['fecha_emision'] . '</td>             
                <td>' . $fila['fecha_emision'] . '</td>             
                <td>admin</td>             
                <td></td>             
                <td></td>             
                <td></td>             
            </tr>';
        }

        $tabla .= "
        <table>
            <tr>
                    <th style='background-color: #90BFEB;width:10px'>Nº Registro</th>
                    <th style='background-color: #90BFEB;width:10px'>Tipo Doc.</th>
                    <th style='background-color: #90BFEB;width:15px'>Documento</th>
                    <th style='background-color: #90BFEB;width:15px'>Fecha Registro</th>
                    <th style='background-color: #90BFEB;width:15px'>F. Vencimiento</th>
                    <th style='background-color: #90BFEB;width:16px;text-align:center'>Codigo Cliente</th>
                    <th style='background-color: #90BFEB;width:85px'>Nombre Cliente</th>
                    <th style='background-color: #90BFEB;width:7px'>Sunat</th>
                    <th style='background-color: #90BFEB;width:7px'>Moneda</th>
                    <th style='background-color: #90BFEB;width:8px'>Total</th>
                    <th style='background-color: #90BFEB;width:8px'>Saldo</th>
                    <th style='background-color: #90BFEB;width:22px'>Glosa</th>
                    <th style='background-color: #90BFEB;width:8px'>Total Convertido</th>
                    <th style='background-color: #90BFEB;width:5px'>E</th>
                    <th style='background-color: #90BFEB;width:14px'>Con Guía</th>
                    <th style='background-color: #90BFEB;width:10px'>Vendedor</th>
                    <th style='background-color: #90BFEB;width:12px'>Orden Compra</th>
                    <th style='background-color: #90BFEB;width:12px'>Fecha Crea.</th>
                    <th style='background-color: #90BFEB;width:10px'>Usuario Crea.</th>
                    <th style='background-color: #90BFEB;width:10px'>Fecha Act.</th>
                    <th style='background-color: #90BFEB;width:10px'>Usuario Act.</th>
                    <th style='background-color: #90BFEB;width:10px'>Historial</th>
            </tr>
            <tbody>
                " . $tbody . "
            </tbody>
        </table>";




        /*   return ($arrayRes);  */
        $nombre_exel = "Venta de " . $anio . "-" . $mes . ".xlsx";
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
        $spreadsheet = $reader->loadFromString($tabla);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");


        $writer->save($nombre_exel);
        header('Location: ' . URL::to($nombre_exel));
    }

    public function generarExcelRVTA($fecha)
    {


        $explodeFecha = explode('-', $fecha);
        $anio =  $explodeFecha[0];
        $mes =  $explodeFecha[1];
        $sql = 'SELECT v.id_tido,v.id_venta,v.fecha_emision,v.fecha_vencimiento,ds.nombre AS tipoDocPago,v.serie,v.numero AS numeroVenta,v.enviado_sunat,v.igv,
       IF(v.enviado_sunat = 0,"No enviado","Enviado") AS enviadoSunat,
        (CASE 
        WHEN LENGTH(c.documento) = 8 THEN "DNI" 
         WHEN LENGTH(c.documento) = 11 THEN "RUC"
        END ) AS tipoDocumento,
        c.documento,c.datos AS cliente,v.total
         FROM ventas v 
        LEFT JOIN documentos_sunat ds ON v.id_tido=ds.id_tido
        LEFT JOIN clientes c ON c.id_cliente=v.id_cliente 
        WHERE  YEAR(v.fecha_emision) =' . $anio . ' AND MONTH(v.fecha_emision) = ' . $mes . ' AND v.id_empresa=' . $_SESSION['id_empresa'];

        /*   var_dump($sql);
        die();
 */
        $result = $this->conexion->query($sql);
        $tabla = '';
        $tbody = '';
        $totalOpgravado = 0;
        /*   $total = 0; */
        foreach ($result as $fila) {
            if ($fila['id_tido']!='1'&&$fila['id_tido']!='2'){
                continue;
            }
            $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
            $igv = $fila['total'] / ($fila['igv'] + 1) * $fila['igv'];
            $totalOpgravado = $fila['total'] - $igv;
            $total = number_format((float)$fila['total'], 2, '.', '');
            $igv = number_format($igv, 2, '.', ',');
            $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
            $style = '';
            if ($fila['enviado_sunat'] == '0') {
                $style = "red";
            } else {
                $style = "green";
            }
            $tbody .= '
               <tr>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['id_venta'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['fecha_emision'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['fecha_vencimiento'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['tipoDocPago'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['serie'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['numeroVenta'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['tipoDocumento'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['documento'] . '</td>
               <td style="font-size: 10px;border:1px solid black;" colspan="2">' . $fila['cliente'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">0</td>
               <td style="font-size: 10px;border:1px solid black;">' .  $totalOpgravado . '</td>
               <td style="font-size: 10px;border:1px solid black;">0</td>
               <td style="font-size: 10px;border:1px solid black;">0</td>
               <td style="font-size: 10px;border:1px solid black;"colspan="2"></td>
               <td style="font-size: 10px;border:1px solid black;" colspan="2">' . ($fila['igv'] * 100) . ' %</td>
               <td style="font-size: 10px;border:1px solid black;" colspan="2">0</td>
               <td style="font-size: 10px;border:1px solid black;" colspan="2">' . $total . '</td>
               <td style="font-size: 10px;border:1px solid black;" colspan="2"></td>
               <td style="font-size: 10px;border:1px solid black;"></td>
               <td style="font-size: 10px;border:1px solid black;"></td>
               <td style="font-size: 10px;border:1px solid black;"></td>
               <td style="font-size: 10px;border:1px solid black;"></td>
               <td style="font-size: 10px;border:1px solid black;width:10px;background-color:' . $style . '">' . $fila['enviadoSunat'] . '</td>
               </tr>
                ';
        }
        $tabla = '  <table style="width:100%">
        <tr>
        </tr>
        <tr>
            <th rowspan="3" colspan="1" style="font-weight:bold;border:1px solid black;text-align: center;font-size: 10px;width:20px;word-wrap: break-word">NUMERO DEL REGISTRO O CODIGO UNICO DE OPERACIÓN</th>
            <th rowspan="2" colspan="5" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;">COMPROBANTE DE PAGO O DOCUMENTO</th>
            <th colspan="4" rowspan="1" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;">INFORMACION DEL CLIENTE</th>
            <th rowspan="3" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:20px;text-align: center;word-wrap: break-word">VALOR FACTURADO DE LA EXPORTACION</th>
            <th rowspan="3" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:20px;text-align: center;word-wrap: break-word">BASE IMPONIBLE DE LA OPERACIÓN GRAVADA</th>
            <th rowspan="2" colspan="2" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:20px;text-align: center;word-wrap: break-word">IMPORTE DE LA OPERACIÓN EXONERADA O INAFECTA</th>
            <th rowspan="3" colspan="2" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;">ISC</th>
            <th rowspan="3" colspan="2" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;">IGV Y/O IPM</th>
            <th rowspan="3" colspan="2" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:20px;text-align: center;word-wrap: break-word">OTROS TRIBUTOS Y CARGOS QUE NO FORMAN PARTE DE LA BASE IMPONIBLE</th>
            <th rowspan="3" colspan="2" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:20px;text-align: center;word-wrap: break-word">IMPORTE TOTAL DEL COMPROBANTE DE PAGO</th>
            <th rowspan="3" colspan="2" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;">TIPO DE CAMBIO</th>
            <th rowspan="2" colspan="4" style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:20px;text-align: center;word-wrap: break-word">REF. COMPROBANTE DE PAGO QUE SE MODIFICA</th>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 10px;font-weight:bold;border:1px solid black;width:20px;text-align: center;word-wrap: break-word">DOC. IDENTIDAD</td>
            <td rowspan="2" colspan="2" style="font-size: 10px;font-weight:bold;border:1px solid black;width:30px;text-align: center;word-wrap: break-word">APELLIDOS Y NOMBRES O RAZON SOCIAL</td>
          
        </tr>
        <tr>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:16px;text-align: center;word-wrap: break-word">FECHA DE EMISION</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:13px;text-align: center;word-wrap: break-word">FECHA DE VCTO</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:16px;text-align: center;word-wrap: break-word">TIPO</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:10px;text-align: center;word-wrap: break-word">SERIE</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:10px;text-align: center;word-wrap: break-word">NUMERO</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;text-align: center;width:8px;">TIPO</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;text-align: center;width:13px;">NUMERO</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;text-align: center;">EXONERADA</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;text-align: center;">INAFECTA</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:15px;text-align: center;">FECHA EMISION</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:12px;text-align: center;">TIPO</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:12px;text-align: center;">SERIE</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:12px;text-align: center;">NUMERO</td>
        </tr>
       ' . $tbody . '
    </table>';

        $nombre_exel = "RVTA " . $anio . "-" . $mes . ".xlsx";
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
        $spreadsheet = $reader->loadFromString($tabla);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save($nombre_exel);
        header('Location: ' . URL::to($nombre_exel));
    }


    public function generarExcelVen222($fecha)
    {


        $explodeFecha = explode('-', $fecha);
        $anio =  $explodeFecha[0];
        $mes =  $explodeFecha[1];
        $sql = '
SELECT 
pro.id_producto,
pro.descripcion,
pro.medida,
pro.cantidad,
SUM(pv.cantidad) venta_mes
FROM productos pro
JOIN productos_ventas pv ON pv.id_producto = pro.id_producto
JOIN ventas v ON v.id_venta = pv.id_venta
WHERE YEAR(v.fecha_emision)= ' . $anio . ' AND MONTH(v.fecha_emision)=' . $mes . '   GROUP BY pro.id_producto;';

        //var_dump($sql);
        //die();

        $result = $this->conexion->query($sql);

        $tabla = '';
        $tbody = '';
        $totalOpgravado = 0;
        /*   $total = 0; */
        foreach ($result as $fila) {

            $tbody .= '
               <tr>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['id_producto'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['descripcion'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['cantidad'] . ' ' . $fila['medida'] . '</td>
               <td style="font-size: 10px;border:1px solid black;">' . $fila['venta_mes'] . ' ' . $fila['medida'] . '</td> 
               </tr>
                ';
        }
        $tabla = '  <table style="width:100%">
        <tr>
        </tr>
        <tr>
            <th style="font-weight:bold;border:1px solid black;text-align: center;font-size: 10px;word-wrap: break-word">#</th>
            <th style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:30px;">Producto</th>
            <th style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:20px;">Stock Actual</th>
            <th style="text-align: center;font-weight:bold;border:1px solid black;font-size: 10px;width:20px;text-align: center;word-wrap: break-word">Cantidad Vendida </th>
        </tr> 
       ' . $tbody . '
    </table>';




        $nombre_exel = "RP " . $anio . "-" . $mes . ".xlsx";
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
        $spreadsheet = $reader->loadFromString($tabla);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save($nombre_exel);
        header('Location: ' . URL::to($nombre_exel));
    }

    public function generarExcelProductoImporte()
    {
        $sql = "SELECT
                    descripcion AS producto,
                    cantidad AS cnt,
                    costo,
                    precio_unidad,
                    precio4,
                    precio,
                    precio2,
                    precio3,
                    codsunat,
                    almacen,
                    codigo 
                FROM
                    productos
                where almacen = '{$_SESSION["sucursal"]}' and estado = 1";

        $result = $this->conexion->query($sql);
        $tabla = '';
        $tbody = '';
        foreach ($result as $fila) {

            $tbody .= '
               <tr>
                    <td style="font-size: 10px;border:1px solid black;">' . $fila['producto'] . '</td>
                    <td style="font-size: 10px;border:1px solid black;">' . $fila['cnt'] . '</td>
                    <td style="font-size: 10px;border:1px solid black;">' . $fila['costo'] . '</td>
                    <td style="font-size: 10px;border:1px solid black;">' . $fila['precio_unidad'] . '</td>
                    <td style="font-size: 10px;border:1px solid black;">' . $fila['precio4'] . '</td>
                    <td style="font-size: 10px;border:1px solid black;">' . $fila['precio'] . '</td>
                    <td style="font-size: 10px;border:1px solid black;">' . $fila['precio2'] . '</td>
                    <td style="font-size: 10px;border:1px solid black;">' . $fila['precio3'] . '</td>
                    <td style="font-size: 10px;border:1px solid black;">' . $fila['codsunat'] . '</td>
                    <td style="font-size: 10px;border:1px solid black;">' . $fila['almacen'] . '</td>
                    <td style="font-size: 10px;border:1px solid black;">' . $fila['codigo'] . '</td>
               </tr>
                ';
        }
        $tabla = '  <table style="width:100%">
        <tr>					
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:16px;text-align: center;word-wrap: break-word">Producto</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:13px;text-align: center;word-wrap: break-word">Cnt</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:16px;text-align: center;word-wrap: break-word">Costo</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:10px;text-align: center;word-wrap: break-word">Precio Unidad</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:10px;text-align: center;word-wrap: break-word">Precio Club</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:15px;text-align: center;">Precio 1</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:12px;text-align: center;">Precio 2</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:12px;text-align: center;">Precio 3</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:12px;text-align: center;">Cod.Sunat</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:12px;text-align: center;">Almacen</td>
            <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:12px;text-align: center;word-wrap: break-word">Codigo</td>
        </tr>
       ' . $tbody . '
        </table>';

        $nombre_exel = "prueba.xlsx";
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
        $spreadsheet = $reader->loadFromString($tabla);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save($nombre_exel);
        header('Location: ' . URL::to($nombre_exel));
    }
    
    public function generarExcelCaja($id)
    {
        $listaTotal = [];
        $sql = "select * from caja_chica where id_caja_empresa ='{$id}'";
        foreach ($this->conexion->query($sql)  as $row) {
            /*   $lista[] = $row; */
            $listaTotal[] = ['detalle' => $row['detalle'], 'salida' => $row['salida'], 'entrada' => $row['entrada'], 'hora' => $row['hora'], 'metodo' => $row['metodo']];
        }

        $dateHoy = date('Y-m-d');

        $sql = "SELECT v.id_venta, v.fecha_emision, CONCAT( ds.abreviatura , ' | ' , v.serie , ' - ', v.numero) AS detalle, 
        v.total AS entrada FROM ventas AS v
                 LEFT JOIN documentos_sunat ds ON v.id_tido = ds.id_tido
                 LEFT JOIN ventas_sunat vs ON v.id_venta = vs.id_venta
             WHERE v.id_empresa = '{$_SESSION['id_empresa']}' AND v.sucursal='{$_SESSION['sucursal']}'  AND v.medoto_pago_id = '10' AND v.fecha_emision ='$dateHoy'
             ORDER BY v.fecha_emision ASC, v.numero ASC";

        /*  $lista2 = []; */
        foreach ($this->conexion->query($sql)  as $row2) {
            $listaTotal[] = ['detalle' => $row2['detalle'], 'salida' => 0, 'entrada' => $row2['entrada'], 'hora' => '-'];
        }
        $tabla = '';
        $tbody = '';
        foreach ($listaTotal as $i => $fila) {
            $index = $i + 1;
            $tipo = "";
            if($fila["metodo"] == 1){
                $tipo = "Efectivo";
            }else if($fila["metodo"] == 2){
                $tipo = "Tarjetas";
            }else{
                $tipo = "Transferencias";
            }
            $tbody .= '
                    <tr>
                            <td style="font-size: 10px;border:1px solid black;">' . $index . '</td>
                            <td style="font-size: 10px;border:1px solid black;">' . $fila['detalle'] . '</td>
                            <td style="font-size: 10px;border:1px solid black;">' . $fila['hora'] . '</td>
                            <td style="font-size: 10px;border:1px solid black;">' . $fila['salida'] . '</td>
                            <td style="font-size: 10px;border:1px solid black;">' . $fila['entrada'] . '</td>
                            <td style="font-size: 10px;border:1px solid black;">' . $tipo . '</td>
                </tr>
                    ';
        }
        $tabla = '  <table style="width:100%">
                    <tr>					
                        <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:16px;text-align: center;word-wrap: break-word">Id</td>
                        <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:13px;text-align: center;word-wrap: break-word">Detalle</td>
                        <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:16px;text-align: center;word-wrap: break-word">Hora</td>
                        <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:10px;text-align: center;word-wrap: break-word">Entrada</td>
                        <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:10px;text-align: center;word-wrap: break-word">Salida</td>
                        <td style="font-size: 10px;font-weight:bold;border:1px solid black;width:15px;text-align: center;">Metodo</td>
                    </tr>
                    ' . $tbody . '
                    </table>';

        $nombre_exel = "cierreDeCaja$dateHoy.xlsx";
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
        $spreadsheet = $reader->loadFromString($tabla);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save($nombre_exel);
        header('Location: ' . URL::to($nombre_exel));
    }
}
