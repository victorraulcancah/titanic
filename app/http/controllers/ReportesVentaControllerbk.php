<?php

require_once 'utils/lib/mpdf/vendor/autoload.php';
require_once 'utils/lib/vendor/autoload.php';

require_once "app/models/Venta.php";
require_once "app/models/Cliente.php";
require_once "app/models/DocumentoEmpresa.php";
require_once "app/models/ProductoVenta.php";
require_once "app/models/VentaServicio.php";
require_once "app/models/Varios.php";
require_once "app/models/VentaSunat.php";
require_once "app/models/VentaAnulada.php";
require_once "app/clases/SendURL.php";
// require_once 'utils/lib/mpdf/vendor/setasign/fpdi/src/autoload.php';




use Endroid\QrCode\QrCode;
use Luecano\NumeroALetras\NumeroALetras;



class ReportesVentaController extends Controller
{
  private $mpdf;
  private $conexion;
  private $venta;

  public function __construct()
  {
    $this->mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 0]);
    $this->conexion = (new Conexion())->getConexion();
    $this->venta = new Venta();
  }

  private function getNomMedida($nu){
      if($nu==1) return "Unidad";
      if($nu==2) return "Caja";
      if($nu==3) return "Bolsa";
      if($nu==4) return "Saco";
  }

  public function reporteVentaPorProducto()
  {
    $sql = "";

    if (strlen($_GET['fecha2']) == 0) {
      $sql = "select  p.descripcion,v.fecha_emision,ds.nombre nombre_documento,concat(v.serie,'-',v.numero) venta_sn, pv.cantidad,pv.precio,pv.precio_usado ,tp.nombre nom_pago
            from productos_ventas pv
            join productos p on p.id_producto = pv.id_producto
            join ventas v on v.id_venta = pv.id_venta
            join tipo_pago tp on tp.tipo_pago_id = v.id_tipo_pago
            join documentos_sunat ds on v.id_tido = ds.id_tido
            where trim(p.codigo)='{$_GET['codprod']}' and v.fecha_emision >= '{$_GET['fecha1']}'  and v.estado<>'2'
                ";
    } else {
      $sql = "select  p.descripcion,v.fecha_emision,ds.nombre nombre_documento,concat(v.serie,'-',v.numero) venta_sn, pv.cantidad,pv.precio,pv.precio_usado ,tp.nombre nom_pago
            from productos_ventas pv
            join productos p on p.id_producto = pv.id_producto
            join ventas v on v.id_venta = pv.id_venta
            join tipo_pago tp on tp.tipo_pago_id = v.id_tipo_pago
            join documentos_sunat ds on v.id_tido = ds.id_tido
            where trim(p.codigo)='{$_GET['codprod']}' and v.fecha_emision between '{$_GET['fecha1']}' and '{$_GET['fecha2']}' and v.estado<>'2'";
    }

    $rowHmtl = '';
    $rows = $this->conexion->query($sql);

    foreach ($rows as $row) {
      $rowHmtl .= "
          <tr>
          <td>{$row['descripcion']}</td>
          <td>{$row['nom_pago']}</td>
          <td>{$row['fecha_emision']}</td>
          <td>{$row['nombre_documento']}</td>
          <td>{$row['venta_sn']}</td>
          <td>{$row['cantidad']}</td>
          <td>{$row['precio']}</td>
            </tr>
          ";
    }

    $html = "
     
    <div style='width: 100%; '>
        <div style='width: 100%; text-align: center;'>
                <h2 style=''>REPORTE DE PRODUCTOS POR VENTA</h2>
              
        </div> 
        
        <div style='width: 100%; margin-top:40px;'>
            <table border='1' style='width: 100%; text-align: center;' >
                <thead>
                <tr>
                  
                    <th style=''>Producto</th>
                    <th style=''>Pago</th>
                    <th style=''>Fecha</th>
                    <th style=''>Doc.</th>
                    <th style=''>S-N</th>
                    <th style=''>Cantidad</th>
                    <th style=''>Precio</th>
                  
              
                </tr>
                </thead>
               <tbody>
                $rowHmtl
                </tbody>
            </table>
        </div>
        
    </div>
    ";
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output();
  }


  public function reporteCompra($id)
  {



    $sql = "SELECT c.fecha_emision,c.direccion,CONCAT( ds.abreviatura , ' | ' , c.serie , ' - ', c.numero)AS factura,p.razon_social,
    c.total,tp.nombre as tipoPago,c.dias_pagos,c.id_empresa
     FROM compras c
    	LEFT JOIN documentos_sunat ds ON c.id_tido = ds.id_tido
	LEFT JOIN proveedores p ON p.proveedor_id = c.id_proveedor
	LEFT JOIN tipo_pago tp ON tp.tipo_pago_id = c.id_tipo_pago 
    WHERE c.id_compra = $id";
    $result = $this->conexion->query($sql);


    $rowHmtl = "";
    $idEmpresa = "";
    foreach ($result as $fila) {
      $total = number_format($fila['total'], 2, ".", "");
      $idEmpresa = $fila['id_empresa'];
      $rowHmtl .= "<tr>
      <td style='font-size: 9px'>{$fila['fecha_emision']}</td>
      <td style='font-size: 9px'>{$fila['direccion']}</td>
      <td style='font-size: 9px'>{$fila['factura']}</td>
      <td style='font-size: 9px'>{$fila['razon_social']}</td>
      <td style='font-size: 9px'>{$fila['tipoPago']}</td>
      <td style='font-size: 9px'>{$fila['dias_pagos']}</td>
      <td style='font-size: 9px'>{$total}</td>
  </tr>";
    }
    $this->mpdf->WriteHTML("
    table, th, td {
      border: 1px solid black;
      border-collapse: collapse;
    }
    ", \Mpdf\HTMLParserMode::HEADER_CSS);


    $empresa = $this->conexion->query("SELECT * from empresas
    where id_empresa = '{$idEmpresa}'")->fetch_assoc();


    $sql = "SELECT * FROM clientes WHERE id_cliente = $id";
    $result = $this->conexion->query($sql)->fetch_assoc();

    $html = "
     
    <div style='width: 100%; '>
        <div style='width: 100%; text-align: center;'>
                <h2 style=''>REPORTE DE VENTAS POR COMPRAS</h2>
              
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
                  
                    <th style='width: 10%;'>Fecha</th>
                    <th style='width: auto;'>Dirección</th>
                    <th style='width: auto;'>Factura</th>
                    <th style='width: auto;'>Razon Social</th>
                    <th style='width: 10%;'>Tipo Pago</th>
                    <th style='width: 10%;'>Días Pagos</th>
                    <th style='width: 10%;'>Total</th>
                  
              
                </tr>
                </thead>
               <tbody>
                $rowHmtl
                </tbody>
            </table>
        </div>
        
    </div>
    ";
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output();
  }

  public function reporteCompraAll()
  {
    $sql = "SELECT c.fecha_emision,c.direccion,CONCAT( ds.abreviatura , ' | ' , c.serie , ' - ', c.numero)AS factura,p.razon_social,
    c.total,tp.nombre as tipoPago,c.dias_pagos,c.id_empresa
     FROM compras c
     LEFT JOIN documentos_sunat ds ON c.id_tido = ds.id_tido
	    LEFT JOIN proveedores p ON p.proveedor_id = c.id_proveedor
	    LEFT JOIN tipo_pago tp ON tp.tipo_pago_id = c.id_tipo_pago";
    $result = $this->conexion->query($sql);


    $rowHmtl = "";
    $idEmpresa = "";
    foreach ($result as $fila) {
      $total = number_format($fila['total'], 2, ".", "");
      $idEmpresa = $fila['id_empresa'];
      $rowHmtl .= "<tr>
      <td style='font-size: 9px'>{$fila['fecha_emision']}</td>
      <td style='font-size: 9px'>{$fila['direccion']}</td>
      <td style='font-size: 9px'>{$fila['factura']}</td>
      <td style='font-size: 9px'>{$fila['razon_social']}</td>
      <td style='font-size: 9px'>{$fila['tipoPago']}</td>
      <td style='font-size: 9px'>{$fila['dias_pagos']}</td>
      <td style='font-size: 9px'>{$total}</td>
  </tr>";
    }
    $this->mpdf->WriteHTML("
    table, th, td {
      border: 1px solid black;
      border-collapse: collapse;
    }
    ", \Mpdf\HTMLParserMode::HEADER_CSS);


    $empresa = $this->conexion->query("SELECT * from empresas
    where id_empresa = '{$idEmpresa}'")->fetch_assoc();




    $html = "
     
    <div style='width: 100%; '>
        <div style='width: 100%; text-align: center;'>
                <h2 style=''>REPORTE DE VENTAS POR COMPRAS</h2>
              
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
                  
                    <th style='width: 10%;'>Fecha</th>
                    <th style='width: auto;'>Dirección</th>
                    <th style='width: auto;'>Factura</th>
                    <th style='width: auto;'>Razon Social</th>
                    <th style='width: 10%;'>Tipo Pago</th>
                    <th style='width: 10%;'>Días Pagos</th>
                    <th style='width: 10%;'>Total</th>
                  
              
                </tr>
                </thead>
               <tbody>
                $rowHmtl
                </tbody>
            </table>
        </div>
        
    </div>
    ";
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output();
  }

  public function reporteCliente($id)
  {

    $sql = "SELECT *,metodo_pago.nombre AS metodoPago,tipo_pago.nombre AS tipoPago FROM VENTAS 
    LEFT JOIN metodo_pago ON metodo_pago.id_metodo_pago=ventas.medoto_pago_id
    LEFT JOIN tipo_pago ON tipo_pago.tipo_pago_id=ventas.id_tipo_pago WHERE id_cliente = $id";
    $result = $this->conexion->query($sql);

    $rowHmtl = "";
    foreach ($result as $fila) {
      $total = number_format($fila['total'], 2, ".", "");
      $rowHmtl .= "<tr>
      <td style='font-size: 9px'>{$fila['id_venta']}</td>
      <td style='font-size: 9px'>{$fila['fecha_emision']}</td>
      <td style='font-size: 9px'>{$fila['direccion']}</td>
      <td style='font-size: 9px'>{$fila['tipoPago']}</td>
      <td style='font-size: 9px'>{$fila['dias_pagos']}</td>
      <td style='font-size: 9px'>{$total}</td>
      <td style='font-size: 9px'>{$fila['metodoPago']}</td>
  </tr>";
    }
    $this->mpdf->WriteHTML("
    table, th, td {
      border: 1px solid black;
      border-collapse: collapse;
    }
    ", \Mpdf\HTMLParserMode::HEADER_CSS);


    $sql = "SELECT * FROM clientes WHERE id_cliente = $id";
    $result = $this->conexion->query($sql)->fetch_assoc();

    $html = "
     
    <div style='width: 100%; '>
        <div style='width: 100%; text-align: center;'>
                <h2 style=''>REPORTE DE VENTAS POR CLIENTE</h2>
              
        </div>
        <div style='width: 100%;'>
            <table style='width: 100%;'>
                <tr>
                    <td>Documento:</td>
                    <td>{$result['documento']}</td>
                </tr>
                <tr>
                    <td>Cliente:</td>
                    <td>{$result['datos']}</td>
                </tr>
                <tr>
                    <td>Dirección:</td>
                    <td>{$result['direccion']}</td>
                </tr>
                <tr>
                    <td>Dirección:</td>
                    <td>{$result['telefono']}</td>
                </tr>
            </table>
        </div>
        
        <div style='width: 100%; margin-top:40px;'>
            <table style='width: 100%; text-align: center;' >
                <thead>
                <tr>
                    <th style='width: 10%;'>Codigo</th>
                    <th style='width: 10%;'>Fecha</th>
                    <th style='width: auto;'>Dirección</th>
                    <th style='width: 10%;'>Tipo Pago</th>
                    <th style='width: 10%;'>Dias Pagos</th>
                    <th style='width: 10%;'>Total</th>
                    <th style='width:auto;'>Metodo Pago</th>
              
                </tr>
                </thead>
               <tbody>
                $rowHmtl
                </tbody>
            </table>
        </div>
        
    </div>
    ";
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output();
  }

  public function reporteProductos($id)
  {
    $rpart = explode("-", $_GET["fecha"]);
    //var_dump($rpart);
    if ($rpart[1] == 'nn') {
      $sql = "SELECT pv.id_producto,c.datos,c.documento,v.id_venta,v.serie,v.numero,v.fecha_emision,pv.cantidad,pv.precio FROM ventas v 
    JOIN productos_ventas pv ON v.id_venta = pv.id_venta
    LEFT JOIN clientes c ON c.id_cliente= v.id_cliente 
    WHERE pv.id_producto= $id and concat(year(v.fecha_emision),month(v.fecha_emision))='" . $rpart[0] . "'";
    } else {
      $sql = "SELECT pv.id_producto,c.datos,c.documento,v.id_venta,v.serie,v.numero,v.fecha_emision,pv.cantidad,pv.precio FROM ventas v 
    JOIN productos_ventas pv ON v.id_venta = pv.id_venta
    LEFT JOIN clientes c ON c.id_cliente= v.id_cliente 
    WHERE pv.id_producto= $id and concat(year(v.fecha_emision),month(v.fecha_emision), day(v.fecha_emision))='" . $rpart[0] . $rpart[1] . "'";
    }
    //var_dump($sql);
    //die();

    $result = $this->conexion->query($sql);

    $rowHmtl = "";
    $totalSuma = 0;
    foreach ($result as $fila) {
      $cantidad = number_format($fila['cantidad'], 2, ".", "");
      $precio = number_format($fila['precio'], 2, ".", "");
      $total = $cantidad * $precio;
      $total = number_format($total, 2, ".", "");
      $rowHmtl .= "<tr>
      <td style='font-size: 9px'>{$fila['documento']}</td>
      <td style='font-size: 9px'>{$fila['datos']}</td>
      <td style='font-size: 9px'>{$fila['id_venta']}</td>
      <td style='font-size: 9px'>{$fila['serie']}</td>
      <td style='font-size: 9px'>{$fila['numero']}</td>
      <td style='font-size: 9px'>{$fila['fecha_emision']}</td>
      <td style='font-size: 9px'>{$cantidad}</td>
      <td style='font-size: 9px'>{$precio}</td>
      <td style='font-size: 9px'>{$total}</td>
    </tr>";
      $totalSuma += $total;
    }
    $this->mpdf->WriteHTML("
    table, th, td {
      border: 1px solid black;
      border-collapse: collapse;
    }
    ", \Mpdf\HTMLParserMode::HEADER_CSS);


    $sql = "SELECT * FROM productos WHERE id_producto = $id";
    $result = $this->conexion->query($sql)->fetch_assoc();

    $html = "
     
    <div style='width: 100%; '>
        <div style='width: 100%; text-align: center;'>
                <h2 style=''>REPORTE DE VENTAS POR PRODUCTO</h2>
              
        </div>
        <div style='width: 100%;'>
            <table style='width: 100%;'>
                <tr>
                    <td>Producto:</td>
                    <td>{$result['descripcion']}</td>
                </tr>
            </table>
        </div>
        
        <div style='width: 100%; margin-top:40px;'>
            <table style='width: 100%; text-align: center;' >
                <thead>
                <tr>
                    <th style='width: 10%;'>Documento</th>
                    <th style='width: 10%;'>Datos</th>
                    <th style='width: auto;'>Id venta</th>
                    <th style='width: 10%;'>Serie</th>
                    <th style='width: 10%;'>Numero</th>
                    <th style='width: 10%;'>Fecha Emision</th>
                    <th style='width:auto;'>Cantidad</th>
                    <th style='width:auto;'>Precio</th>
                    <th style='width:auto;'>Total</th>
              
                </tr>
                </thead>
               <tbody>
                $rowHmtl
                </tbody>
                <tfoot>
                <tr>
                <td colspan='8' style='text-align: right;font-size: 13px'>Total</td>
                <td  style='font-size: 13px'>$totalSuma</td>
                </tr>
                </tfoot>
            </table>
        </div>
        
    </div>
    ";
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output();
  }
//   public function comprobanteCotizacionA4($coti)
//   {
//     $this->mpdf  = new \Mpdf\Mpdf([
//       "format" => "A4",
//       "mode" => "utf-8",
//     ]);
//     $listaProd1 = $this->conexion->query("SELECT pc.*,p.descripcion,TRIM(p.codigo) codigo  from productos_cotis pc 
//             join productos p on p.id_producto = pc.id_producto where pc.id_coti='$coti' order by codigo ASC");
//     $sql = "select * from cotizaciones where cotizacion_id=" . $coti;
//     $datoVenta = $this->conexion->query($sql)->fetch_assoc();

//     $datoEmpresa = $this->conexion->query("select * from empresas where id_empresa=" . $_SESSION['id_empresa'])->fetch_assoc();

//     $resultVemedor = $this->conexion->query("select * from usuarios where usuario_id = " . $datoVenta['id_usuario'])->fetch_assoc();
//     $resultC = $this->conexion->query("select * from clientes where id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
//     $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : strlen($resultC['documento'] == 11 ? 'RUC' : '');

//     $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha']);

//     $tipo_pagoC = $datoVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
//     $tabla_cuotas = '';

//     $menosRowsNumH = 0;

//     if ($datoVenta["id_tipo_pago"] == '2') {
//       $rowTempCuo = '';
//       $sql = "SELECT * FROM cuotas_cotizacion WHERE id_coti='$coti'";
//       $resulTempCuo = $this->conexion->query($sql);
//       $contadorCuota = 0;
//       $menosRowsNumH = 1;
//       foreach ($resulTempCuo as $cuotTemp) {
//         $menosRowsNumH++;
//         $contadorCuota++;
//         $tempNum = Tools::numeroParaDocumento($contadorCuota, 2);
//         $tempFecha = Tools::formatoFechaVisual($cuotTemp['fecha']);
//         $tempMonto = Tools::money($cuotTemp['monto']);
//         $rowTempCuo .= "
//             <tr>
//                 <td>Cuota $tempNum</td>
//                 <td>$tempFecha </td>
//                 <td>S/ $tempMonto</td>
//             </tr>
//             ";
//       }
//       $tabla_cuotas = '<div style="width: 100%;padding-top: 5px;">
//         <table style="width:50%;margin:auto;display: block;text-align:center;font-size: 10px;">
//                 <thead>
//                 <tr>
//                     <th>CUOTA</th>
//                     <th>FECHA</th>
//                     <th>MONTO</th>
//                 </tr>
//                 </thead>
//                 <tbody>
//                     ' . $rowTempCuo . '
//                 </tbody>
//         </table>
//         </div>';
//     }
//     $formatter = new NumeroALetras;
//     $qrImage = '';
//     $hash_Doc = '';
//     $tipo_documeto_venta = "PEDIDO #: ";
//     $htmlDOM = '';
//     $totalLetras = 'SOLES';
//     $totalOpGratuita = 0;
//     $totalOpExonerada = 0;
//     $totalOpinafec = 0;
//     $totalOpgravado = 0;
//     $totalDescuento = 0;
//     $totalOpinafecta = 0;
//     $SC = 0;
//     $percepcion = 0;
//     $total = 0;
//     $contador = 1;
//     $igv = 0;

//     $rowHTML = '';
//     $rowHTMLTERT = '';

//     foreach ($listaProd1 as $prod) {

//       //$datoVenta['cm_tc']

//       if ($datoVenta['moneda'] == 2) {
//         $prod['precio'] = $prod['precio'] / $datoVenta['cm_tc'];
//       }
//       $precio =  $prod['precio'];
//       $importe = $precio * $prod['cantidad'];
//       //$subtotal = $subtotal + $importe;
//       $total += $importe;
//       $tempDescuento = 0;
//       $importe -= $tempDescuento;
//       $totalDescuento += $tempDescuento;

//       $precio = number_format($precio, 2, '.', ',');
//       $importe = number_format($importe, 2, '.', ',');
//       $tempDescuento = number_format($tempDescuento, 2, '.', ',');
//       $prod['codigo'] = trim($prod['codigo']);
//       $temMedida1 = $this->getNomMedida($prod['presenta']);
//       $prod['cantidad'] =  number_format($prod['cantidad'], 0);
//       $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);

//       $precioDisminu = $precio / $prod['presenta_cnt'];

//       $precioDisminu = number_format($precioDisminu, 2, '.', ',');

//       $rowHTML = $rowHTML . "
//               <tr>
                
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>{$prod['codigo']}</td>
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>$cnt4 </td>
//                 <td class='' style=' font-size: 11px; text-align: left;border-left: 1px solid #363636;'>{$prod['descripcion']}</td>
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>{$prod['presenta_cnt']}  </td>
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>$precioDisminu</td> 
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$importe</td>
//               </tr>
//             ";
//       $contador++;
//     }

//     $cntRowEE = 45;
//     $rowHTMLTERT = "";
//     for ($tert = 0; $tert < ($cntRowEE - $contador) - $menosRowsNumH; $tert++) {
//       $rowHTMLTERT = $rowHTMLTERT . " <tr>
//         <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; color: white'>.</td>

//         <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
//         <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td> 
//         <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
//         <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
        
        
//         <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'> </td>
//       </tr>";
//     }

//     $totalLetras =   $formatter->toInvoice(number_format($total, 2, '.', ''), 2, $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES');

//     $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #ffffff ; float: right;'>

//             <div style='padding: 5px;width: 100%; height: 100px; border: 2px solid #1e1e1e' class=''>
//             <div style='margin-top:10px'></div>
//             <span>RUC: {$datoEmpresa['ruc']}</span><br>
//             <div style='margin-top: 10px'></div>
//             <span><strong>$tipo_documeto_venta {$datoVenta['numero']}</strong></span><br>
//             <div style='margin-top: 10px'></div>
//             <span> </span>
//             </div>
//             </div>
//             </div>";
//     $this->mpdf->WriteFixedPosHTML("<img style='max-width: 300px;max-height: 85px' src='" . URL::to('files/logos/' . $datoEmpresa['logo']) . "'>", 15, 5, 150, 120);
//     $this->mpdf->WriteFixedPosHTML($htmlCuadroHead ?? '', 0, 5, 195, 130);
//     $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Central Telefónica: </strong> {$datoEmpresa['telefono']}</span>", 15, 27, 210, 130);
//     $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Email: </strong> info@titanicsac.com | Web: www.titanicsac.com</span>", 15, 32, 210, 130);
//     $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Dirección:</strong> <span style='font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 37, 120, 130);

//     $totalOpGratuita = number_format($totalOpGratuita, 2, '.', ',');
//     $totalOpExonerada = number_format($totalOpExonerada, 2, '.', ',');
//     $totalOpinafec = number_format($totalOpinafec, 2, '.', ',');
//     $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
//     $totalDescuento = number_format($totalDescuento, 2, '.', ',');
//     $totalOpinafecta = number_format($totalOpinafecta, 2, '.', ',');
//     $SC = number_format($SC, 2, '.', ',');
//     $percepcion = number_format($percepcion, 2, '.', ',');
//     $igv = $total / 1.18 * 0.18;
//     $totalOpgravado = $total - $igv;
//     $total = number_format($total, 2, '.', ',');
//     $igv = number_format($igv, 2, '.', ',');
//     $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
//     $monedaVisual = $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES';
//     $html = "<div style='width: 100%;padding-top: 110px; overflow: hidden;clear: both;'>
//         <div style='width: 100%;border: 1px solid black'>
//         <div style='width: 55%; float: left; '>
        
//         <table style='width:100%'>
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>RUC/DNI:</strong></td>
//             <td style=' font-size: 11px;'>{$resultC['documento']}</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>CLIENTE:</strong></td>
//             <td style=' font-size: 11px;'>{$resultC['datos']}</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>DIRECCIÓN:</strong></td>
//             <td style=' font-size: 11px;'>{$resultC['direccion']}</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>VENDEDOR:</strong></td>
//             <td style=' font-size: 11px;'>{$resultVemedor['nombres']} </td>
//           </tr>
//         </table>
//         </div>
//         <div style='width: 45%; float: left'>
//         <table style='width:100%'>
        
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>FECHA:</strong></td>
//             <td style=' font-size: 11px;'>$fecha_emision</td>
//           </tr>
          
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>MONEDA:</strong></td>
//             <td style=' font-size: 11px;'>$monedaVisual</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>PAGO:</strong></td>
//             <td style=' font-size: 11px;'>Contado</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>CELULAR</strong></td>
//             <td style=' font-size: 11px;'>{$resultC['telefono']}</td>
//           </tr>
//         </table>
//         </div>
//         </div>
        
        
//         </div>
//         <div style='width: 100%; padding-top: 5px;'>
//         <table style='width:100%;border-bottom: 1px solid #363636;border-collapse: collapse;'>
//             <tr style='border-bottom: 1px solid #363636;border-collapse: collapse;'>
//              <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>ITEM</strong></td>
//             <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>Cantidad</strong></td>
           
//             <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>DESCRIPCION</strong></td>
//             <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>MEDIDA</strong></td> 
//             <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>PRECIO</strong></td> 
//             <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>SUB TOTAL</strong></td>
            
//           </tr>
//           $rowHTML
//           $rowHTMLTERT
//               <tr>
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;color: white'>.</td>
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td> 
                
                
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
//               </tr>
         
        
//         </table>
//         </div>
//         <div>
//         <!--<h4>Observacion:</h4> 
//         {$datoVenta['observacion']}-->
//       </div>
        
//         ";
//     $dominio = '';


//     $monedahtmlDol = '';

//     if ($datoVenta['moneda'] == 2) {
//       if ($datoVenta['moneda'] == 2) {
//         $totalDolar = number_format($total * $datoVenta['cm_tc'], 2, '.', ",");
//       } else {
//         $totalDolar = number_format($total / $datoVenta['cm_tc'], 2, '.', ",");
//       }
//       $simbolfff = $datoVenta['moneda'] == 2 ? 'S/' : '$';
//       $monedahtmlDol = "<tr>
//             <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Total a Pagar</td>
//             <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$simbolfff $totalDolar</td>
//           </tr>";
//     }

//     $simbolfff22 = $datoVenta['moneda'] == 1 ? 'S/' : '$';
//     $htmlFooter = "
//     <div style='height: 10px;width: 100%; padding-bottom: 0px;font-size: 10px;border: 1px solid black;'>. SON: | $totalLetras</div>
    
//     <div style='width: 100%; height: 10px;margin-top: 10px;'>
//         <div style='float: left; width: 20%;'>
//             $qrImage
//             <div style='position: absolute; left: 80px; top: 5px;'></div>
//         </div>
//         <div style='width: 65%; padding-bottom: 5px;font-size: 12px; float: left; padding-top: 10px;'>
//             <div style='width: 100%'></div>
//             <div style='width: 95%; padding: 3px; font-size: 10px;height: 90px '>
//                 $hash_Doc
//                 Detalle:<br>
//                 Representación impresa de la $tipo_documeto_venta <br>Este documento puede ser validado en $dominio
//             </div>
//         </div>
//         <div style='width: 35%;'>
//             <table style='width: 100%;border-top: 1px solid #363636;border-bottom: 1px solid #363636;border-right: 1px solid #363636;border-collapse: collapse;'>
//                 <tr>
//                     <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Total Op. Gravado:</td>
//                     <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$totalOpgravado</td>
//                 </tr>
//                 <tr>
//                     <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>IGV:</td>
//                     <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$igv</td>
//                 </tr>
//                 <tr>
//                     <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Total a Pagar</td>
//                     <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$simbolfff22 $total</td>
//                 </tr>
//                 $monedahtmlDol
//             </table>
//         </div>
//     </div> 
//       ";



//     $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #ffffff ; float: right;'>

//                   <div style='padding: 5px;width: 100%; height: 100px; border: 2px solid #1e1e1e' class=''>
//                   <div style='margin-top:10px'></div>
//                   <span>RUC: {$datoEmpresa['ruc']}</span><br>
//                   <div style='margin-top: 10px'></div>
//                   <span><strong>$tipo_documeto_venta {$datoVenta['numero']}</strong></span><br>
//                   <div style='margin-top: 10px'></div>
//                   <span> </span>
//                   </div>
//                   </div>
//                   </div>";


//     $htmlFooter = "
//           <div style='height: 10px;width: 100%; padding-bottom: 0px;font-size: 10px;border: 1px solid black;'>. SON: | $totalLetras</div>
          
//           <div style='width: 100%;margin-top: 10px;'>
//               <div style='float: left; width: 100%;'>
//                   $qrImage
//                   <div style='position: absolute; left: 80px; top: 5px;'></div>
//               </div>
//               <div style='width: 65%; padding-bottom: 5px;font-size: 12px; float: left; padding-top: 10px;'>
//                   <div style='width: 100%'></div>
//                   <div style='width: 95%; padding: 3px; font-size: 10px;height: 90px '>
//                       $hash_Doc
//                       Detalle:<br>
//                       Representación impresa de la $tipo_documeto_venta <br>Este documento puede ser validado en $dominio
//                   </div>
//               </div>
//               <div style='width: 35%;'>
//                   <table style='width: 100%;border-top: 1px solid #363636;border-bottom: 1px solid #363636;border-right: 1px solid #363636;border-collapse: collapse;'>
//                       <tr style=''>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'>Total Op. Gravado:</td>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$totalOpgravado</td>
//                       </tr>
//                       <tr>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'>IGV:</td>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right;' >$igv</td>
//                       </tr>
//                       <tr>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'>Total a Pagar</td>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right;' >$simbolfff22 $total</td>
//                       </tr>
//                       $monedahtmlDol
//                   </table>
//               </div>
//           </div> 
//       ";
//     // $this->mpdf->WriteFixedPosHTML($htmlFooter, 52.5, 240, 142, 130);
//     $this->mpdf->WriteFixedPosHTML($htmlFooter, 15.5, 240, 180, 130);
//     $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
//     $this->mpdf->Output("Cotizacion{$datoVenta['numero']}.pdf", 'I');
//   }

//   public function comprobanteCotizacionA4($coti)
//   {
//     $this->mpdf  = new \Mpdf\Mpdf([
//       "format" => "A4",
//       "mode" => "utf-8",
//     ]);
//     $listaProd1 = $this->conexion->query("SELECT pc.*,p.descripcion,TRIM(p.codigo) codigo  from productos_cotis pc 
//             join productos p on p.id_producto = pc.id_producto where pc.id_coti='$coti' order by codigo ASC");
//     $sql = "select * from cotizaciones where cotizacion_id=" . $coti;
//     $datoVenta = $this->conexion->query($sql)->fetch_assoc();

//     $datoEmpresa = $this->conexion->query("select * from empresas where id_empresa=" . $_SESSION['id_empresa'])->fetch_assoc();

//     $resultVemedor = $this->conexion->query("select * from usuarios where usuario_id = " . $datoVenta['id_usuario'])->fetch_assoc();
//     $resultC = $this->conexion->query("select * from clientes where id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
//     $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : strlen($resultC['documento'] == 11 ? 'RUC' : '');

//     $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha']);

//     $tipo_pagoC = $datoVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
//     $tabla_cuotas = '';

//     $menosRowsNumH = 0;

//     if ($datoVenta["id_tipo_pago"] == '2') {
//       $rowTempCuo = '';
//       $sql = "SELECT * FROM cuotas_cotizacion WHERE id_coti='$coti'";
//       $resulTempCuo = $this->conexion->query($sql);
//       $contadorCuota = 0;
//       $menosRowsNumH = 1;
//       $totalSaldoPagado = 0;

//       foreach ($resulTempCuo as $cuotTemp) {
//         $menosRowsNumH++;
//         $contadorCuota++;
//         $tempNum = Tools::numeroParaDocumento($contadorCuota, 2);
//         $tempFecha = Tools::formatoFechaVisual($cuotTemp['fecha']);
//         $tempMonto = Tools::money($cuotTemp['monto']);
//         $totalSaldoPagado += $cuotTemp['monto'];

//         $rowTempCuo .= "
//             <tr>
//                 <td>Cuota $tempNum</td>
//                 <td>$tempFecha </td>
//                 <td>S/ $tempMonto</td>
//             </tr>
//             ";
//       }
//       $tabla_cuotas = '<div style="width: 100%;padding-top: 5px;">
//         <table style="width:50%;margin:auto;display: block;text-align:center;font-size: 10px;">
//                 <thead>
//                 <tr>
//                     <th>CUOTA</th>
//                     <th>FECHA</th>
//                     <th>MONTO</th>
//                 </tr>
//                 </thead>
//                 <tbody>
//                     ' . $rowTempCuo . '
//                 </tbody>
//         </table>
//         </div>';
//     }
//     $formatter = new NumeroALetras;
//     $qrImage = '';
//     $hash_Doc = '';
//     $tipo_documeto_venta = "PEDIDO #: ";
//     $htmlDOM = '';
//     $totalLetras = 'SOLES';
//     $totalOpGratuita = 0;
//     $totalOpExonerada = 0;
//     $totalOpinafec = 0;
//     $totalOpgravado = 0;
//     $totalDescuento = 0;
//     $totalOpinafecta = 0;
//     $SC = 0;
//     $percepcion = 0;
//     $total = 0;
//     $contador = 1;
//     $igv = 0;

//     $rowHTML = '';
//     $rowHTMLTERT = '';

//     foreach ($listaProd1 as $prod) {
//       //$datoVenta['cm_tc']

//       if ($datoVenta['moneda'] == 2) {
//         $prod['precio'] = $prod['precio'] / $datoVenta['cm_tc'];
//       }
//       $precio =  $prod['precio'];
//       $importe = $precio * $prod['cantidad'];
//       //$subtotal = $subtotal + $importe;
//       $total += $importe;
//       $tempDescuento = 0;
//       $importe -= $tempDescuento;
//       $totalDescuento += $tempDescuento;
//       $observacion = $datoVenta['observacion'];
//       $SaldoPendientePagar = $datoVenta['total'] - $totalSaldoPagado;
//       $precio = number_format($precio, 2, '.', ',');
//       $importe = number_format($importe, 2, '.', ',');
//       $tempDescuento = number_format($tempDescuento, 2, '.', ',');
//       $prod['codigo'] = trim($prod['codigo']);
//       $temMedida1 = $this->getNomMedida($prod['presenta']);
//       $prod['cantidad'] =  number_format($prod['cantidad'], 0);
//       $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);

//       $precioDisminu = $precio / $prod['presenta_cnt'];

//       $precioDisminu = number_format($precioDisminu, 2, '.', ',');

//       $rowHTML = $rowHTML . "
//               <tr>
                
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>$contador</td>
//                 <td class='' style=' font-size: 11px; text-align: left;border-left: 1px solid #363636;'>{$prod['descripcion']}</td>
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>{$prod['cantidad']}</td>
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>{$prod['presenta_cnt']}  </td>
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>$precioDisminu</td> 
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$importe</td>
//               </tr>
//             ";
//       $contador++;
//     }

//     $cntRowEE = 45;
//     $rowHTMLTERT = "";
//     for ($tert = 0; $tert < ($cntRowEE - $contador) - $menosRowsNumH; $tert++) {
//       $rowHTMLTERT = $rowHTMLTERT . " <tr>
//         <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; color: white'>.</td>

//         <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
//         <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td> 
//         <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
//         <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
        
        
//         <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'> </td>
//       </tr>";
//     }

//     $totalLetras =   $formatter->toInvoice(number_format($total, 2, '.', ''), 2, $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES');

//     $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #ffffff ; float: right;'>

//             <div style='padding: 5px;width: 100%; height: 100px; border: 2px solid #1e1e1e' class=''>
//             <div style='margin-top:10px'></div>
//             <span>RUC: {$datoEmpresa['ruc']}</span><br>
//             <div style='margin-top: 10px'></div>
//             <span><strong>$tipo_documeto_venta {$datoVenta['numero']}</strong></span><br>
//             <div style='margin-top: 10px'></div>
//             <span> </span>
//             </div>
//             </div>
//             </div>";
//     $this->mpdf->WriteFixedPosHTML("<img style='max-width: 300px;max-height: 85px' src='" . URL::to('files/logos/' . $datoEmpresa['logo']) . "'>", 15, 5, 150, 120);
//     $this->mpdf->WriteFixedPosHTML($htmlCuadroHead ?? '', 0, 5, 195, 130);
//     $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Central Telefónica: </strong> {$datoEmpresa['telefono']}</span>", 15, 27, 210, 130);
//     $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Email: </strong> info@titanicsac.com | Web: www.titanicsac.com</span>", 15, 32, 210, 130);
//     $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Dirección:</strong> <span style='font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 37, 120, 130);

//     $totalOpGratuita = number_format($totalOpGratuita, 2, '.', ',');
//     $totalOpExonerada = number_format($totalOpExonerada, 2, '.', ',');
//     $totalOpinafec = number_format($totalOpinafec, 2, '.', ',');
//     $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
//     $totalDescuento = number_format($totalDescuento, 2, '.', ',');
//     $totalOpinafecta = number_format($totalOpinafecta, 2, '.', ',');
//     $SC = number_format($SC, 2, '.', ',');
//     $percepcion = number_format($percepcion, 2, '.', ',');
//     $igv = $total / 1.18 * 0.18;
//     $totalOpgravado = $total - $igv;
//     $total = number_format($total, 2, '.', ',');
//     $igv = number_format($igv, 2, '.', ',');
//     $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
//     $monedaVisual = $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES';
//     $html = "<div style='width: 100%;padding-top: 110px; overflow: hidden;clear: both;'>
//         <div style='width: 100%;border: 1px solid black'>
//         <div style='width: 55%; float: left; '>
        
//         <table style='width:100%'>
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>RUC/DNI:</strong></td>
//             <td style=' font-size: 11px;'>{$resultC['documento']}</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>CLIENTE:</strong></td>
//             <td style=' font-size: 11px;'>{$resultC['datos']}</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>DIRECCIÓN:</strong></td>
//             <td style=' font-size: 11px;'>{$resultC['direccion']}</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>VENDEDOR:</strong></td>
//             <td style=' font-size: 11px;'>{$resultVemedor['nombres']} </td>
//           </tr>
//         </table>
//         </div>
//         <div style='width: 45%; float: left'>
//         <table style='width:100%'>
        
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>FECHA:</strong></td>
//             <td style=' font-size: 11px;'>$fecha_emision</td>
//           </tr>
          
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>MONEDA:</strong></td>
//             <td style=' font-size: 11px;'>$monedaVisual</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>PAGO:</strong></td>
//             <td style=' font-size: 11px;'>Contado</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 11px;text-align: left'><strong>CELULAR</strong></td>
//             <td style=' font-size: 11px;'>{$resultC['telefono']}</td>
//           </tr>
//         </table>
//         </div>
//         </div>
        
        
//         </div>
//         <div style='width: 100%; padding-top: 5px;'>
//         <table style='width:100%;border-bottom: 1px solid #363636;border-collapse: collapse;'>
//             <tr style='border-bottom: 1px solid #363636;border-collapse: collapse;'>
//              <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 30px;'><strong>ITEM</strong></td>
//           <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>DESCRIPCION</strong></td>
//           <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 60px;'><strong>Cantidad</strong></td>
//           <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 60px;'><strong>MEDIDA</strong></td> 
//           <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 60px;'><strong>PRECIO</strong></td> 
//           <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 90px;'><strong>SUB TOTAL</strong></td>
            
//           </tr>
//           $rowHTML
//           $rowHTMLTERT
//               <tr>
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;color: white'>.</td>
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td> 
                
                
//                 <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
//               </tr>
         
        
//         </table>
//         </div>
//         <div>
//         <!--<h4>Observacion:</h4> 
//         {$datoVenta['observacion']}-->
//       </div>
        
//         ";
//     $dominio = '';


//     $monedahtmlDol = '';

//     if ($datoVenta['moneda'] == 2) {
//       if ($datoVenta['moneda'] == 2) {
//         $totalDolar = number_format($total * $datoVenta['cm_tc'], 2, '.', ",");
//       } else {
//         $totalDolar = number_format($total / $datoVenta['cm_tc'], 2, '.', ",");
//       }
//       $simbolfff = $datoVenta['moneda'] == 2 ? 'S/' : '$';
//       $monedahtmlDol = "<tr>
//             <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Total a Pagar</td>
//             <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$simbolfff $totalDolar</td>
//           </tr>";
//     }

//     $simbolfff22 = $datoVenta['moneda'] == 1 ? 'S/' : '$';
//     $htmlFooter = "
//     <div style='height: 10px;width: 100%; padding-bottom: 0px;font-size: 10px;border: 1px solid black;'>. SON: | $totalLetras</div>
    
//     <div style='width: 100%; height: 10px;margin-top: 10px;'>
//         <div style='float: left; width: 20%;'>
//             $qrImage
//             <div style='position: absolute; left: 80px; top: 5px;'></div>
//         </div>
//         <div style='width: 65%; padding-bottom: 5px;font-size: 12px; float: left; padding-top: 10px;'>
//             <div style='width: 100%'></div>
//             <div style='width: 95%; padding: 3px; font-size: 10px;height: 90px '>
//                 $hash_Doc
//                 Detalle:<br>
//                 Representación impresa de la $tipo_documeto_venta <br>Este documento puede ser validado en $dominio
//             </div>
//         </div>
//         <div style='width: 35%;'>
//             <table style='width: 100%;border-top: 1px solid #363636;border-bottom: 1px solid #363636;border-right: 1px solid #363636;border-collapse: collapse;'>
//                 <tr>
//                     <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Total Op. Gravado:</td>
//                     <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$totalOpgravado</td>
//                 </tr>
//                 <tr>
//                     <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>IGV:</td>
//                     <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$igv</td>
//                 </tr>
//                 <tr>
//                     <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Total a Pagar</td>
//                     <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$simbolfff22 $total</td>
//                 </tr>
//                 $monedahtmlDol
//             </table>
//         </div>
//     </div> 
//       ";



//     $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #ffffff ; float: right;'>

//                         <div style='padding: 5px;width: 100%; height: 100px; border: 2px solid #1e1e1e' class=''>
//                           <div style='margin-top:10px'></div>
//                             <span>RUC: {$datoEmpresa['ruc']}</span><br>
//                               <div style='margin-top: 10px'></div>
//                               <span><strong>$tipo_documeto_venta {$datoVenta['numero']}</strong></span><br>
//                               <div style='margin-top: 10px'></div>
//                             <span> </span>
//                           </div>
//                         </div>
//                   </div>";


//     $htmlFooter = "
//           <div style='height: 10px;width: 100%; padding-bottom: 0px;font-size: 10px;border: 1px solid black;'>. SON: | $totalLetras</div>
          
//           <div style='width: 100%;margin-top: 10px;'>
//               <div style='float: left; width: 100%;'>
//                   $qrImage
//                   <div style='position: absolute; left: 80px; top: 5px;'></div>
//               </div>
//               <div style='width: 65%; padding-bottom: 5px;font-size: 12px; float: left; padding-top: 10px;'>
//                   <div style='width: 100%'></div>
//                   <div style='width: 95%; padding: 3px; font-size: 10px;height: 90px '>
//                       $hash_Doc
//                       Observaciones:<br>
//                       $observacion <br>
//                       Saldo Pendiente:
//                       $SaldoPendientePagar
//                   </div>
//               </div>
//               <div style='width: 35%;'>
//                   <table style='width: 100%;border-top: 1px solid #363636;border-bottom: 1px solid #363636;border-right: 1px solid #363636;border-collapse: collapse;'>
//                       <tr style=''>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'>Totalp Op. Gravado:</td>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$totalOpgravado</td>
//                       </tr>
//                       <tr>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'>IGV:</td>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right;' >$igv</td>
//                       </tr>
//                       <tr>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'>Total a Pagar</td>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right;' >$simbolfff22 $total</td>
//                       </tr>
//                       $monedahtmlDol
//                   </table>
//               </div>
//           </div> 
//       ";
//     //$this->mpdf->WriteFixedPosHTML($htmlFooter, 52.5, 240, 142, 130);
//     $this->mpdf->WriteFixedPosHTML($htmlFooter, 15.5, 240, 180, 130);
//     $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
//     $this->mpdf->Output("Cotizacion{$datoVenta['numero']}.pdf", 'I');
//   }
  
   public function comprobanteCotizacionA4($coti)
  {
    $this->mpdf  = new \Mpdf\Mpdf([
      "format" => "A4",
      "mode" => "utf-8",
    ]);
    $diaSemana = date('N');
    $dias_acum = 0;

    if ($diaSemana == 2) {
      $dias_acum = 3;
    } else {
      $dias_acum = 2;
    }
    $listaProd1 = $this->conexion->query("SELECT pc.*,p.descripcion,TRIM(p.codigo) codigo  from productos_cotis pc 
            join productos p on p.id_producto = pc.id_producto where pc.id_coti='$coti' order by p.descripcion ASC");
    $sql = "select * from cotizaciones where cotizacion_id=" . $coti;
    $datoVenta = $this->conexion->query($sql)->fetch_assoc();
    // TOTAL PAGADO DE LAS CUOTAS QUE RESTAREMOS CON EL TOTAL DE LA COTIZACIONES
    $totalMontoCuotasCotizacion = $this->conexion->query("
      SELECT IFNULL(SUM(cc.monto), 0) AS total_monto
      FROM `cotizaciones` c
      LEFT JOIN `cuotas_cotizacion` cc ON cc.id_coti = c.cotizacion_id
      WHERE c.id_cliente = " . $datoVenta['id_cliente'] . "
      AND DATE(c.fecha) < DATE_SUB(CURDATE(), INTERVAL $dias_acum DAY) 
    ")->fetch_assoc()['total_monto'];

    $sumaTotalCotizacion = $this->conexion->query("
        SELECT IFNULL(SUM(total), 0) as total 
        FROM `cotizaciones` 
        WHERE `id_cliente` = " . $datoVenta['id_cliente'] . " 
        AND DATE(fecha) < DATE_SUB(CURDATE(), INTERVAL $dias_acum DAY) 
    ")->fetch_assoc()['total'];


    $SaldoPendientePagar = max(0, $sumaTotalCotizacion - $totalMontoCuotasCotizacion);
    $SaldoPendientePagar = number_format($SaldoPendientePagar,2);

    $datoEmpresa = $this->conexion->query("select * from empresas where id_empresa=" . $_SESSION['id_empresa'])->fetch_assoc();

    $resultVemedor = $this->conexion->query("select * from usuarios where usuario_id = " . $datoVenta['id_usuario'])->fetch_assoc();
    $resultC = $this->conexion->query("select * from clientes where id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
    $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : strlen($resultC['documento'] == 11 ? 'RUC' : '');

    $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha']);

    $tipo_pagoC = $datoVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
    $tabla_cuotas = '';

    $menosRowsNumH = 0;

    if ($datoVenta["id_tipo_pago"] == '2') {
      $rowTempCuo = '';
      $sql = "SELECT * FROM cuotas_cotizacion WHERE id_coti='$coti'";
      $resulTempCuo = $this->conexion->query($sql);
      $contadorCuota = 0;
      $menosRowsNumH = 1;
      $totalSaldoPagado = 0;

      foreach ($resulTempCuo as $cuotTemp) {
        $menosRowsNumH++;
        $contadorCuota++;
        $tempNum = Tools::numeroParaDocumento($contadorCuota, 2);
        $tempFecha = Tools::formatoFechaVisual($cuotTemp['fecha']);
        $tempMonto = Tools::money($cuotTemp['monto']);
        $totalSaldoPagado += $cuotTemp['monto'];

        $rowTempCuo .= "
            <tr>
                <td>Cuota $tempNum</td>
                <td>$tempFecha </td>
                <td>S/ $tempMonto</td>
            </tr>
            ";
      }
      $tabla_cuotas = '<div style="width: 100%;padding-top: 5px;">
        <table style="width:50%;margin:auto;display: block;text-align:center;font-size: 10px;">
                <thead>
                <tr>
                    <th>CUOTA</th>
                    <th>FECHA</th>
                    <th>MONTO</th>
                </tr>
                </thead>
                <tbody>
                    ' . $rowTempCuo . '
                </tbody>
        </table>
        </div>';
    }
    $formatter = new NumeroALetras;
    $qrImage = '';
    $hash_Doc = '';
    $tipo_documeto_venta = "PEDIDO #: ";
    $htmlDOM = '';
    $totalLetras = 'SOLES';
    $totalOpGratuita = 0;
    $totalOpExonerada = 0;
    $totalOpinafec = 0;
    $totalOpgravado = 0;
    $totalDescuento = 0;
    $totalOpinafecta = 0;
    $SC = 0;
    $percepcion = 0;
    $total = 0;
    $contador = 1;
    $igv = 0;

    $rowHTML = '';
    $rowHTMLTERT = '';

    foreach ($listaProd1 as $prod) {
      //$datoVenta['cm_tc']

      if ($datoVenta['moneda'] == 2) {
        $prod['precio'] = $prod['precio'] / $datoVenta['cm_tc'];
      }
      $precio =  $prod['precio'];
      $importe = $precio * $prod['cantidad'];
      //$subtotal = $subtotal + $importe;
      $total += $importe;
      $tempDescuento = 0;
      $importe -= $tempDescuento;
      $totalDescuento += $tempDescuento;
      $observacion = $datoVenta['observacion'];
      //$precio = number_format($precio, 2, '.', ',');
      //$importe = number_format($importe, 2, '.', ',');
      $tempDescuento = number_format($tempDescuento, 2, '.', ',');
      $prod['codigo'] = trim($prod['codigo']);
      $temMedida1 = $this->getNomMedida($prod['presenta']);
      $prod['cantidad'] =  number_format($prod['cantidad'], 0);
      $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);

      $precioDisminu = $precio / $prod['presenta_cnt'];

      $precioDisminu = number_format($precioDisminu, 2, '.', ',');
      $multi = $prod['cantidad'] * $prod['presenta_cnt'];

      $rowHTML = $rowHTML . "
              <tr>
                
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>$contador</td>
                <td class='' style='font-size: 10px; text-align: center; border-left: 1px solid #363636;'>$multi</td>
                <td class='' style=' font-size: 11px; text-align: left;border-left: 1px solid #363636;'>{$prod['descripcion']}</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>$precioDisminu</td> 
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$importe</td>
              </tr>
            ";
      $contador++;
    }

    $cntRowEE = 45;
    $rowHTMLTERT = "";
    for ($tert = 0; $tert < ($cntRowEE - $contador) - $menosRowsNumH; $tert++) {
      $rowHTMLTERT = $rowHTMLTERT . " <tr>
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; color: white'>.</td>
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
        
        
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'> </td>
      </tr>";
    }

    $totalLetras =   $formatter->toInvoice(number_format($total, 2, '.', ''), 2, $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES');

    $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #ffffff ; float: right;'>

            <div style='padding: 5px;width: 100%; height: 100px; border: 2px solid #1e1e1e' class=''>
            <div style='margin-top:10px'></div>
            <span>RUC: {$datoEmpresa['ruc']}</span><br>
            <div style='margin-top: 10px'></div>
            <span><strong>$tipo_documeto_venta {$datoVenta['numero']}</strong></span><br>
            <div style='margin-top: 10px'></div>
            <span> </span>
            </div>
            </div>
            </div>";
    $this->mpdf->WriteFixedPosHTML("<img style='max-width: 300px;max-height: 85px' src='" . URL::to('files/logos/' . $datoEmpresa['logo']) . "'>", 15, 5, 150, 120);
    $this->mpdf->WriteFixedPosHTML($htmlCuadroHead ?? '', 0, 5, 195, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Central Telefónica: </strong> {$datoEmpresa['telefono']}</span>", 15, 27, 210, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Email: </strong> info@titanicsac.com | Web: www.titanicsac.com</span>", 15, 32, 210, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Dirección:</strong> <span style='font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 37, 120, 130);

    $totalOpGratuita = number_format($totalOpGratuita, 2, '.', ',');
    $totalOpExonerada = number_format($totalOpExonerada, 2, '.', ',');
    $totalOpinafec = number_format($totalOpinafec, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
    $totalDescuento = number_format($totalDescuento, 2, '.', ',');
    $totalOpinafecta = number_format($totalOpinafecta, 2, '.', ',');
    $SC = number_format($SC, 2, '.', ',');
    $percepcion = number_format($percepcion, 2, '.', ',');
    $igv = $total / 1.18 * 0.18;
    $totalOpgravado = $total - $igv;
    $total = number_format($total, 2, '.', ',');
    $igv = number_format($igv, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
    $monedaVisual = $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES';
    $html = "<div style='width: 100%;padding-top: 110px; overflow: hidden;clear: both;'>
        <div style='width: 100%;border: 1px solid black'>
        <div style='width: 100%; float: left; '>
        <table style='width:100%'>
           <tr>
            <td style='font-size: 11px; padding: 2px;'><strong>CLIENTE:</strong> {$resultC['datos']}</td>
            <td style='font-size: 11px; padding: 2px;'><strong>CELULAR:</strong> {$resultC['telefono']}</td>
        </tr>
        <tr>
            <td style='font-size: 11px; padding: 2px;'><strong>DIRECCIÓN:</strong> {$resultC['direccion']}</td>
            <td style='font-size: 11px; padding: 2px;'><strong>VENDEDOR:</strong> {$resultVemedor['nombres']}</td>
        </tr>
        <tr>
            <td style='font-size: 11px; padding: 2px;'><strong>RUC/DNI:</strong> {$resultC['documento']}</td>
            <td style='font-size: 11px; padding: 2px;'><strong>FECHA:</strong> $fecha_emision</td>
        </tr>
        <tr>
            <td style='font-size: 11px; padding: 2px;'><strong>MONEDA:</strong> $monedaVisual</td>
            <td style='font-size: 11px; padding: 2px;'><strong>PAGO:</strong> Contado</td>
        </tr>
        </table>
        </div>
        </div>
        
        
        </div>
        <div style='width: 100%; padding-top: 5px;'>
        <table style='width:100%;border-bottom: 1px solid #363636;border-collapse: collapse;'>
            <tr style='border-bottom: 1px solid #363636;border-collapse: collapse;'>
             <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 30px;'><strong>ITEM</strong></td>
             <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 60px;'><strong>CANTIDAD</strong></td>
          <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>DESCRIPCION</strong></td>
          <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 60px;'><strong>PRECIO</strong></td> 
          <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 90px;'><strong>SUB TOTAL</strong></td>
            
          </tr>
          $rowHTML
          $rowHTMLTERT
              <tr>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;color: white'>.</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td> 
                
                
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
              </tr>
         
        
        </table>
        </div>
        <div>
        <!--<h4>Observacion:</h4> 
        {$datoVenta['observacion']}-->
      </div>
        
        ";
    $dominio = '';


    $monedahtmlDol = '';

    if ($datoVenta['moneda'] == 2) {
      if ($datoVenta['moneda'] == 2) {
        $totalDolar = number_format($total * $datoVenta['cm_tc'], 2, '.', ",");
      } else {
        $totalDolar = number_format($total / $datoVenta['cm_tc'], 2, '.', ",");
      }
      $simbolfff = $datoVenta['moneda'] == 2 ? 'S/' : '$';
      $monedahtmlDol = "<tr>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Total a Pagar</td>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$simbolfff $totalDolar</td>
          </tr>";
    }

    $simbolfff22 = $datoVenta['moneda'] == 1 ? 'S/' : '$';
    $htmlFooter = "
    <div style='height: 10px;width: 100%; padding-bottom: 0px;font-size: 10px;border: 1px solid black;'>. SON: | $totalLetras</div>
    
    <div style='width: 100%; height: 10px;margin-top: 10px;'>
        <div style='float: left; width: 20%;'>
            $qrImage
            <div style='position: absolute; left: 80px; top: 5px;'></div>
        </div>
        <div style='width: 65%; padding-bottom: 5px;font-size: 12px; float: left; padding-top: 10px;'>
            <div style='width: 100%'></div>
            <div style='width: 95%; padding: 3px; font-size: 10px;height: 90px '>
                $hash_Doc
                Detalle:<br>
                Representación impresa de la $tipo_documeto_venta <br>Este documento puede ser validado en $dominio
            </div>
        </div>
        <div style='width: 35%;'>
            <table style='width: 100%;border-top: 1px solid #363636;border-bottom: 1px solid #363636;border-right: 1px solid #363636;border-collapse: collapse;'>
                <tr>
                    <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Total Op. Gravado:</td>
                    <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$totalOpgravado</td>
                </tr>
                <tr>
                    <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>IGV:</td>
                    <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$igv</td>
                </tr>
                <tr>
                    <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Total a Pagar</td>
                    <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$simbolfff22 $total</td>
                </tr>
                $monedahtmlDol
            </table>
        </div>
    </div> 
      ";



    $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #ffffff ; float: right;'>

                        <div style='padding: 5px;width: 100%; height: 100px; border: 2px solid #1e1e1e' class=''>
                          <div style='margin-top:10px'></div>
                            <span>RUC: {$datoEmpresa['ruc']}</span><br>
                              <div style='margin-top: 10px'></div>
                              <span><strong>$tipo_documeto_venta {$datoVenta['numero']}</strong></span><br>
                              <div style='margin-top: 10px'></div>
                            <span> </span>
                          </div>
                        </div>
                  </div>";


    $htmlFooter = "
          <div style='height: 10px;width: 100%; padding-bottom: 0px;font-size: 10px;border-right: 1px solid;border-bottom: 1px solid;border-left: 1px solid;'>. SON: | $totalLetras</div>
          
          <div style='width: 100%;margin-top: 10px;'>
              <div style='float: left; width: 100%;'>
                  $qrImage
                  <div style='position: absolute; left: 80px; top: 5px;'></div>
              </div>
              <div style='width: 65%; padding-bottom: 5px;font-size: 12px; float: left; padding-top: 10px;'>
                  <div style='width: 100%'></div>
                  <div style='width: 95%; padding: 3px; font-size: 10px;height: 90px '>
                      $hash_Doc
                       Observaciones:<br>
                       $observacion <br>
                       Saldo Pendiente:
                      $SaldoPendientePagar
                  </div>
              </div>
              <div style='width: 35%;'>
                  <table style='width: 100%;border-top: 1px solid #363636;border-bottom: 1px solid #363636;border-right: 1px solid #363636;border-collapse: collapse;'>
                      <tr style=''>
                          <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'>Totalp Op. Gravado:</td>
                          <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$totalOpgravado</td>
                      </tr>
                      <tr>
                          <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'>IGV:</td>
                          <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right;' >$igv</td>
                      </tr>
                      <tr>
                          <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'>Total a Pagar</td>
                          <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right;' >$simbolfff22 $total</td>
                      </tr>
                      $monedahtmlDol
                  </table>
              </div>
          </div> 
      ";
    $html .=$htmlFooter;
    //$this->mpdf->WriteFixedPosHTML($htmlFooter, 52.5, 240, 142, 130);
    // $this->mpdf->WriteFixedPosHTML($htmlFooter, 15.5, 240, 180, 130);
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output("Cotizacion{$datoVenta['numero']}.pdf", 'I');
  }
  
//   public function comprobanteCotizacion($coti)
//   {

//     $this->mpdf = new \Mpdf\Mpdf([
//       "format" => [216, 330],  // Tamaño hoja oficio en milímetros (ancho x alto)
//       "mode" => "utf-8",
//       "margin_left" => 15,     // Margen izquierdo en milímetros
//       "margin_right" => 15,    // Margen derecho en milímetros
//       "margin_top" => 10,      // Margen superior en milímetros
//       "margin_bottom" => 3,   // Margen inferior en milímetros
//     ]);

//     $listaProd1 = $this->conexion->query("SELECT pc.*,p.descripcion,TRIM(p.codigo) codigo  from productos_cotis pc 
//             join productos p on p.id_producto = pc.id_producto where pc.id_coti='$coti' order by codigo ASC");
//     $sql = "select * from cotizaciones where cotizacion_id=" . $coti;
//     $datoVenta = $this->conexion->query($sql)->fetch_assoc();
//     $datoEmpresa = $this->conexion->query("select * from empresas where id_empresa=" . $_SESSION['id_empresa'])->fetch_assoc();
//     $resultVemedor = $this->conexion->query("select * from usuarios where usuario_id = " . $datoVenta['id_usuario'])->fetch_assoc();
//     $resultC = $this->conexion->query("select * from clientes where id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
//     $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : strlen($resultC['documento'] == 11 ? 'RUC' : '');
//     $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha']);
//     $tipo_pagoC = $datoVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
//     $tabla_cuotas = '';
//     $menosRowsNumH = 0;
//     if ($datoVenta["id_tipo_pago"] == '2') {
//       $rowTempCuo = '';
//       $sql = "SELECT * FROM cuotas_cotizacion WHERE id_coti='$coti'";
//       $resulTempCuo = $this->conexion->query($sql);
//       $contadorCuota = 0;
//       $menosRowsNumH = 1;
//       foreach ($resulTempCuo as $cuotTemp) {
//         $menosRowsNumH++;
//         $contadorCuota++;
//         $tempNum = Tools::numeroParaDocumento($contadorCuota, 2);
//         $tempFecha = Tools::formatoFechaVisual($cuotTemp['fecha']);
//         $tempMonto = Tools::money($cuotTemp['monto']);
//         $rowTempCuo .= "
//             <tr>
//                 <td>Cuota $tempNum</td>
//                 <td>$tempFecha </td>
//                 <td>S/ $tempMonto</td>
//             </tr>
//             ";
//       }
//       $tabla_cuotas = '<div style="width: 100%;padding-top: 5px;">
//         <table style="width:50%;margin:auto;display: block;text-align:center;font-size: 10px;">
//                 <thead>
//                 <tr>
//                     <th>CUOTA</th>
//                     <th>FECHA</th>
//                     <th>MONTO</th>
//                 </tr>
//                 </thead>
//                 <tbody>
//                     ' . $rowTempCuo . '
//                 </tbody>
//         </table>
//         </div>';
//     }
//     $formatter = new NumeroALetras;
//     $qrImage = '';
//     $hash_Doc = '';
//     $tipo_documeto_venta = "COTIZACIÓN #: ";
//     $htmlDOM = '';
//     $totalLetras = 'SOLES';
//     $totalOpGratuita = 0;
//     $totalOpExonerada = 0;
//     $totalOpinafec = 0;
//     $totalOpgravado = 0;
//     $totalDescuento = 0;
//     $totalOpinafecta = 0;
//     $SC = 0;
//     $percepcion = 0;
//     $total = 0;
//     $contador = 1;
//     $igv = 0;

//     $rowHTML = '';
//     $rowHTMLTERT = '';

//     foreach ($listaProd1 as $prod) {

//       //$datoVenta['cm_tc']

//       if ($datoVenta['moneda'] == 2) {
//         $prod['precio'] = $prod['precio'] / $datoVenta['cm_tc'];
//       }
//       $precio =  $prod['precio'];
//       $importe = $precio * $prod['cantidad'];
//       //$subtotal = $subtotal + $importe;
//       $total += $importe;
//       $tempDescuento = 0;
//       $importe -= $tempDescuento;
//       $totalDescuento += $tempDescuento;

//       $precio = number_format($precio, 2, '.', ',');
//       $importe = number_format($importe, 2, '.', ',');
//       $tempDescuento = number_format($tempDescuento, 2, '.', ',');
//       $prod['codigo'] = trim($prod['codigo']);
//       $temMedida1 = $this->getNomMedida($prod['presenta']);
//       $prod['cantidad'] =  number_format($prod['cantidad'], 0);
//       $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);

//       $precioDisminu = $precio / $prod['presenta_cnt'];

//       $precioDisminu = number_format($precioDisminu, 2, '.', ',');

//       $rowHTML = $rowHTML . "
//               <tr>
//                 <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636;'>$contador</td>
//                 <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636;'><strong>{$prod['descripcion']}</strong></td>
//                 <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636;'>$cnt4 </td>
//                 <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636;'>{$prod['presenta_cnt']}  </td>
//                 <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636;'>$precioDisminu</td> 
//                 <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$importe</td>
//               </tr>
//             ";
//       $contador++;
//     }
//     $cntRowEE = 28;
//     $rowHTMLTERT = "";
//     for ($tert = 0; $tert < ($cntRowEE - $contador) - $menosRowsNumH; $tert++) {
//       $rowHTMLTERT = $rowHTMLTERT . " <tr>
//         <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; color: white'>.</td>

//         <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td>
//         <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td> 
//         <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td>
//         <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td>
//         <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'> </td>
//       </tr>";
//     }
//     $totalLetras =   $formatter->toInvoice(number_format($total, 2, '.', ''), 2, $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES');

//     $htmlEncabezado = "
//       <table style='width: 100%; border-collapse: collapse;margin-top: 0px;'>
//           <tr>
//               <!-- Columna del Logo y Datos -->
//               <td style='width: 75%; text-align: left; vertical-align: middle;'>
//                   <img style='max-width: 250px; max-height: 48px;' src='" . URL::to('files/logos/' . $datoEmpresa['logo']) . "'><br>
//                   <span style='font-size: 9px;'><strong>Central Telefónica:</strong> {$datoEmpresa['telefono']}</span><br>
//                   <span style='font-size: 9px;'><strong>Email:</strong> info@titanicsac.com | <strong>Web:</strong> www.titanicsac.com</span><br>
//                   <span style='font-size: 9px;'><strong>Dirección:</strong> <span style='font-size: 9px;'>{$datoEmpresa['direccion']}</span></span>
//               </td>
//               <!-- Columna del Cuadro de Pedido -->
//              <td style='width: 25%; text-align: center; vertical-align: middle; border: 1px solid #1e1e1e;'>
//             <span style='font-size: 9px;'>RUC: {$datoEmpresa['ruc']}</span><br><br> <!-- Usamos dos <br> para separar -->
//             <span style='font-size: 9px; font-weight: bold;'>$tipo_documeto_venta {$datoVenta['numero']}</span>
//         </td>
//               </td>
//           </tr>
//       </table>";
//     $totalOpGratuita = number_format($totalOpGratuita, 2, '.', ',');
//     $totalOpExonerada = number_format($totalOpExonerada, 2, '.', ',');
//     $totalOpinafec = number_format($totalOpinafec, 2, '.', ',');
//     $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
//     $totalDescuento = number_format($totalDescuento, 2, '.', ',');
//     $totalOpinafecta = number_format($totalOpinafecta, 2, '.', ',');
//     $SC = number_format($SC, 2, '.', ',');
//     $percepcion = number_format($percepcion, 2, '.', ',');
//     $igv = $total / 1.18 * 0.18;
//     $totalOpgravado = $total - $igv;
//     $total = number_format($total, 2, '.', ',');
//     $igv = number_format($igv, 2, '.', ',');
//     $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
//     $monedaVisual = $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES';
//     $html = "<div style='width: 100%;padding-top: 3px; overflow: hidden;clear: both;'>
//         <div style='width: 100%;border: 1px solid black'>
//         <div style='width: 50%; float: left; '>
//         <table style='width:100%'>
//           <tr>
//             <td style=' font-size: 8px;text-align: left'><strong>RUC/DNI:</strong></td>
//             <td style=' font-size: 8px;'>{$resultC['documento']}</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 8px;text-align: left'><strong>CLIENTE:</strong></td>
//             <td style=' font-size: 8px;'>{$resultC['datos']}</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 8px;text-align: left'><strong>DIRECCIÓN:</strong></td>
//             <td style=' font-size: 8px;'>{$resultC['direccion']}</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 8px;text-align: left'><strong>VENDEDOR:</strong></td>
//             <td style=' font-size: 8px;'>{$resultVemedor['nombres']} </td>
//           </tr>
//         </table>
//         </div>
//         <div style='width: 45%; float: left'>
//         <table style='width:100%'>
//           <tr>
//             <td style=' font-size: 8px;text-align: left'><strong>FECHA:</strong></td>
//             <td style=' font-size: 9px;'>$fecha_emision</td>
//           </tr>
          
//           <tr>
//             <td style=' font-size: 8px;text-align: left'><strong>MONEDA:</strong></td>
//             <td style=' font-size: 8px;'>$monedaVisual</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 8px;text-align: left'><strong>PAGO:</strong></td>
//             <td style=' font-size: 8px;'>Contado</td>
//           </tr>
//           <tr>
//             <td style=' font-size: 8px;text-align: left'><strong>CELULAR</strong></td>
//             <td style=' font-size: 8px;'>{$resultC['telefono']}</td>
//           </tr>
//         </table>
//         </div>
//         </div>
//         </div>
//         <div style='width: 100%; margin-bottom: 3px;margin-top: 3px;'>
//         <table style='width:100%;border-bottom: 1px solid #363636;border-collapse: collapse;'>
//             <tr style='border-bottom: 1px solid #363636;border-collapse: collapse;'>
//              <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>ITEM</strong></td>
            
//             <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>DESCRIPCION</strong></td>
//             <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>Cantidad</strong></td>
//             <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>MEDIDA</strong></td> 
//             <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>PRECIO</strong></td> 
//             <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>SUB TOTAL</strong></td>
//           </tr>
//           $rowHTML
//           $rowHTMLTERT
//         </table>
//         </div>
//         <div>
//         <!--<h4>Observacion:</h4> 
//         {$datoVenta['observacion']}-->
//       </div>
        
//         ";
//     $dominio = '';
//     $monedahtmlDol = '';
//     if ($datoVenta['moneda'] == 2) {
//       if ($datoVenta['moneda'] == 2) {
//         $totalDolar = number_format($total * $datoVenta['cm_tc'], 2, '.', ",");
//       } else {
//         $totalDolar = number_format($total / $datoVenta['cm_tc'], 2, '.', ",");
//       }
//       $simbolfff = $datoVenta['moneda'] == 2 ? 'S/' : '$';
//       $monedahtmlDol = "<tr>
//             <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 9px; text-align: right'>Total a Pagar</td>
//             <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 9px;  text-align: right' >$simbolfff $totalDolar</td>
//           </tr>";
//     }
//     $simbolfff22 = $datoVenta['moneda'] == 1 ? 'S/' : '$';

//     $htmlFooter = "
//           <div style='height: 10px;width: 100%; padding-bottom: 0px;font-size: 9px;border: 1px solid black;font-size: 8px;'>. SON: | $totalLetras</div>
          
//           <div style='width: 100%;margin-top: 3px;'>
//               <div style='float: left; width: 100%;'>
//                   $qrImage
//                   <div style='position: absolute; left: 80px; top: 5px;'></div>
//               </div>
//               <div style='width: 65%; padding-bottom: 3px;font-size: 9.5px; float: left; padding-top: 8px;'>
//                   <div style='width: 100%'></div>
//                   <div style='width: 95%; padding: 3px; font-size: 9.5px;height: 90px '>
//                       $hash_Doc
//                       Detalle:<br>
//                       Representación impresa de la $tipo_documeto_venta <br>Este documento puede ser validado en $dominio
//                   </div>
//               </div>
//               <div style='width: 34.8%;padding-bottom: 0px;margin-bottom: 10px;'>
//                   <table style='width: 100%;border-top: 1px solid #363636;border-bottom: 1px solid #363636;border-right: 1px solid #363636;border-collapse: collapse;'>
//                       <tr style=''>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 9px; text-align: right;padding-right: 3px;'>Total Op. Gravado:</td>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 9px;  text-align: right' >$totalOpgravado</td>
//                       </tr>
//                       <tr>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 9px; text-align: right;padding-right: 3px;'>IGV:</td>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 9px;  text-align: right;' >$igv</td>
//                       </tr>
//                       <tr>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 9px; text-align: right;padding-right: 3px;'>Total a Pagar</td>
//                           <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 9px;  text-align: right;' >$simbolfff22 $total</td>
//                       </tr>
//                       $monedahtmlDol
//                   </table>
//               </div>
//           </div> 
//       ";
//     $html = $htmlEncabezado . $html . $htmlFooter;
//     $html = $html . $html;

//     $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

//     $this->mpdf->Output("Cotizacion{$datoVenta['numero']}.pdf", 'I');
//   }
 /*public function comprobanteCotizacion($coti)
  {

    $this->mpdf = new \Mpdf\Mpdf([
      "format" => "A4-L",         // Formato A4 en orientación landscape
      "mode" => "utf-8",
      "margin_left" => 3,     // Margen izquierdo en milímetros
      "margin_right" => 3,    // Margen derecho en milímetros
      "margin_top" => 5,      // Margen superior en milímetros
      "margin_bottom" => 5,   // Margen inferior en milímetros
    ]);

    $diaSemana = date('N');
    $dias_acum = 0;

    if ($diaSemana == 2) {
      $dias_acum = 3;
    } else {
      $dias_acum = 2;
    }

    $listaProd1 = $this->conexion->query("SELECT pc.*,p.precio as preciop, p.descripcion,TRIM(p.codigo) codigo  from productos_cotis pc 
              join productos p on p.id_producto = pc.id_producto where pc.id_coti='$coti' order by p.descripcion ASC");
    $sql = "select * from cotizaciones where cotizacion_id=" . $coti;
    $datoVenta = $this->conexion->query($sql)->fetch_assoc();
    // TOTAL PAGADO DE LAS CUOTAS QUE RESTAREMOS CON EL TOTAL DE LA COTIZACIONES
    $totalMontoCuotasCotizacion = $this->conexion->query("
      SELECT IFNULL(SUM(cc.monto), 0) AS total_monto
      FROM `cotizaciones` c
      LEFT JOIN `cuotas_cotizacion` cc ON cc.id_coti = c.cotizacion_id
      WHERE c.id_cliente = " . $datoVenta['id_cliente'] . "
      AND DATE(c.fecha) < DATE_SUB(CURDATE(), INTERVAL $dias_acum DAY) 
    ")->fetch_assoc()['total_monto'];

    $sumaTotalCotizacion = $this->conexion->query("
        SELECT IFNULL(SUM(total), 0) as total 
        FROM `cotizaciones` 
        WHERE `id_cliente` = " . $datoVenta['id_cliente'] . " 
        AND DATE(fecha) < DATE_SUB(CURDATE(), INTERVAL $dias_acum DAY) 
    ")->fetch_assoc()['total'];


    $SaldoPendientePagar = max(0, $sumaTotalCotizacion - $totalMontoCuotasCotizacion);
    $datoEmpresa = $this->conexion->query("select * from empresas where id_empresa=" . $_SESSION['id_empresa'])->fetch_assoc();
    $resultVemedor = $this->conexion->query("select * from usuarios where usuario_id = " . $datoVenta['id_usuario'])->fetch_assoc();
    $resultC = $this->conexion->query("select * from clientes where id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
    $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : strlen($resultC['documento'] == 11 ? 'RUC' : '');
    $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha']);
    $tipo_pagoC = $datoVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
    $tabla_cuotas = '';
    $menosRowsNumH = 0;
    if ($datoVenta["id_tipo_pago"] == '2') {
      $rowTempCuo = '';
      $sql = "SELECT * FROM cuotas_cotizacion WHERE id_coti='$coti'";
      $resulTempCuo = $this->conexion->query($sql);
      $contadorCuota = 0;
      $menosRowsNumH = 1;
      $totalSaldoPagado = 0;
      foreach ($resulTempCuo as $cuotTemp) {
        $menosRowsNumH++;
        $contadorCuota++;
        $tempNum = Tools::numeroParaDocumento($contadorCuota, 2);
        $tempFecha = Tools::formatoFechaVisual($cuotTemp['fecha']);
        $tempMonto = Tools::money($cuotTemp['monto']);
        $totalSaldoPagado += $cuotTemp['monto'];
        $rowTempCuo .= "
            <tr>
                <td>Cuota $tempNum</td>
                <td>$tempFecha </td>
                <td>S/ $tempMonto</td>
            </tr>
            ";
      }
      $tabla_cuotas = '<div style="width: 100%;padding-top: 5px;">
        <table style="width:50%;margin:auto;display: block;text-align:center;font-size: 10px;">
                <thead>
                <tr>
                    <th>CUOTA</th>
                    <th>FECHA</th>
                    <th>MONTO</th>
                </tr>
                </thead>
                <tbody>
                    ' . $rowTempCuo . '
                </tbody>
        </table>
        </div>';
    }
    $formatter = new NumeroALetras;
    $qrImage = '';
    $hash_Doc = '';
    $tipo_documeto_venta = "COTIZACIÓN #: ";
    $htmlDOM = '';
    $totalLetras = 'SOLES';
    $totalOpGratuita = 0;
    $totalOpExonerada = 0;
    $totalOpinafec = 0;
    $totalOpgravado = 0;
    $totalDescuento = 0;
    $totalOpinafecta = 0;
    $SC = 0;
    $percepcion = 0;
    $total = 0;
    $contador = 1;
    $igv = 0;

    $rowHTML = '';
    $rowHTMLTERT = '';

    foreach ($listaProd1 as $prod) {

      //$datoVenta['cm_tc']

      if ($datoVenta['moneda'] == 2) {
        $prod['precio'] = $prod['precio'] / $datoVenta['cm_tc'];
      }
      $precio =  $prod['preciop'];
      $importe = $precio * $prod['cantidad'] * $prod['presenta_cnt'];
      //$subtotal = $subtotal + $importe;
      $total += $importe;
      $tempDescuento = 0;
      $importe -= $tempDescuento;
      $totalDescuento += $tempDescuento;
      $observacion = $datoVenta['observacion'];
      //$precio = number_format($precio, 2, '.', ',');
      //$importe = number_format($importe, 2, '.', ',');
      $tempDescuento = number_format($tempDescuento, 2, '.', ',');
      $prod['codigo'] = trim($prod['codigo']);
      $temMedida1 = $this->getNomMedida($prod['presenta']);
      $prod['cantidad'] =  number_format($prod['cantidad'], 0);
      $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);

      //$precioDisminu = $precio / $prod['presenta_cnt'];

      $precioDisminu = number_format($precio, 2, '.', ',');
      $multi = $prod['cantidad'] * $prod['presenta_cnt'];

      $rowHTML = $rowHTML . "
              <tr>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$contador</td>
                <td class='' style='font-size: 10px; text-align: center; border-left: 1px solid #363636;'>$multi</td>
                <td class='' style=' font-size: 10px; text-align: left;border-left: 1px solid #363636;'><strong>{$prod['descripcion']}</strong></td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>{$prod['cantidad']} </td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>{$prod['presenta_cnt']}  </td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$precioDisminu</td> 
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$importe</td>
              </tr>
              
            ";
      $contador++;
    }
    $cntRowEE = 41;
    $rowHTMLTERT = "";
    for ($tert = 0; $tert < ($cntRowEE - $contador) - $menosRowsNumH; $tert++) {
      $rowHTMLTERT = $rowHTMLTERT . " <tr>
        <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; color: white'>.</td>
    <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td>
            <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td>
            <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td> 
            <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td>
            <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td>
            <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'></td>
          </tr>";
    }
    $totalLetras =   $formatter->toInvoice(number_format($total, 2, '.', ''), 2, $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES');
    $htmlEncabezado = "
          <table style='width: 100%; border-collapse: collapse; margin-top: 0px;'>
              <tr>
                  <td style='width: 100%; vertical-align: top;'>
                      <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <!-- Columna del Logo y Datos -->
                            <td style='width: 75%; text-align: left; vertical-align: middle;'>
                                <img style='max-width: 250px; max-height: 48px;' src='" . URL::to('files/logos/' . $datoEmpresa['logo']) . "'><br>
                                <span style='font-size: 9px;'><strong>Central Telefónica:</strong> {$datoEmpresa['telefono']}</span><br>
                                <span style='font-size: 9px;'><strong>Email:</strong> info@titanicsac.com | <strong>Web:</strong> www.titanicsac.com</span><br>
                                <span style='font-size: 9px;'><strong>Dirección:</strong> <span style='font-size: 9px;'>{$datoEmpresa['direccion']}</span></span>
                            </td>
                            <!-- Columna del Cuadro de Pedido -->
                              <td style='width: 25%; text-align: center; vertical-align: middle; border: 1px solid #1e1e1e;'>
                                    <span style='font-size: 9px;'>RUC: {$datoEmpresa['ruc']}</span><br><br> <!-- Usamos dos <br> para separar -->
                                    <span style='font-size: 9px; font-weight: bold;'>$tipo_documeto_venta {$datoVenta['numero']}</span>
                            </td>
                            </td>
                          </tr>
                      </table>
                  </td>
              </tr>
          </table>";

    $totalOpGratuita = number_format($totalOpGratuita, 2, '.', ',');
    $totalOpExonerada = number_format($totalOpExonerada, 2, '.', ',');
    $totalOpinafec = number_format($totalOpinafec, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
    $totalDescuento = number_format($totalDescuento, 2, '.', ',');
    $totalOpinafecta = number_format($totalOpinafecta, 2, '.', ',');
    $SC = number_format($SC, 2, '.', ',');
    $percepcion = number_format($percepcion, 2, '.', ',');
    $igv = $total / 1.18 * 0.18;
    $totalOpgravado = $total - $igv;
    $total = number_format($total, 2, '.', ',');
    $igv = number_format($igv, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
    $monedaVisual = $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES';
    $tabla_datos_cliente = "
    <table style='width: 100%; border: 1px solid #363636; margin-top: 3px; margin-bottom: 3px;'>
        <tr>
            <td style='font-size: 8px; padding: 2px;'><strong>CLIENTE:</strong> {$resultC['datos']}</td>
            <td style='font-size: 8px; padding: 2px;'><strong>CELULAR:</strong> {$resultC['telefono']}</td>
        </tr>
        <tr>
            <td style='font-size: 8px; padding: 2px;'><strong>DIRECCIÓN:</strong> {$resultC['direccion']}</td>
            <td style='font-size: 8px; padding: 2px;'><strong>VENDEDOR:</strong> {$resultVemedor['nombres']}</td>
        </tr>
        <tr>
            <td style='font-size: 8px; padding: 2px;'><strong>RUC/DNI:</strong> {$resultC['documento']}</td>
            <td style='font-size: 8px; padding: 2px;'><strong>FECHA:</strong> $fecha_emision</td>
        </tr>
        <tr>
            <td style='font-size: 8px; padding: 2px;'><strong>MONEDA:</strong> $monedaVisual</td>
            <td style='font-size: 8px; padding: 2px;'><strong>PAGO:</strong> Contado</td>
        </tr>
    </table>";




    $tabla = "
      <table style='width:100%;border-bottom: 1px solid #363636;border-collapse: collapse;'>
        <tr style='border-bottom: 1px solid #363636;border-collapse: collapse;'>
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 5%;'><strong>ITEM</strong></td>
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 5%;'><strong> -- </strong></td>
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 50%;'><strong>DESCRIPCION</strong></td>
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>Cantidad</strong></td>
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>MEDIDA</strong></td> 
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>PRECIO</strong></td> 
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 15%;'><strong>SUB TOTAL</strong></td>
        </tr>
        $rowHTML
        $rowHTMLTERT
      </table>
    ";



    $tableconson = "
    <table style='width: 100%;border: 1px solid #363636;margin-top: 3px;'>
        <tr>
            <td style=style='height: 10px;width: 100%; padding-bottom: 0px;'>
            <span style='font-size: 8px'>SON: | $totalLetras</span>
            </td>
        </tr>
    </table>";
    $dominio = '';
    $monedahtmlDol = '';
    if ($datoVenta['moneda'] == 2) {
      if ($datoVenta['moneda'] == 2) {
        $totalDolar = number_format($total * $datoVenta['cm_tc'], 2, '.', ",");
      } else {
        $totalDolar = number_format($total / $datoVenta['cm_tc'], 2, '.', ",");
      }
      $simbolfff = $datoVenta['moneda'] == 2 ? 'S/' : '$';
      $monedahtmlDol = "<tr>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 9px; text-align: right'>Total a Pagar</td>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 9px;  text-align: right' >$simbolfff $totalDolar</td>
          </tr>";
    }
    $simbolfff22 = $datoVenta['moneda'] == 1 ? 'S/' : '$';

    $tablaFooter = "<table style='width: 100%;'>
                <tr>
                    <td style='width: 50%;'>
                       <div style='width: 95%; padding: 3px; font-size: 9.5px;height: 90px '>
                       $hash_Doc
                       Observaciones:<br>
                       $observacion <br>
                       Saldo Pendiente:
                      $SaldoPendientePagar
                 </div>
                    </td>
                    <td style='width: 50%;text-align: right;'>
                        <table style='width: 50%; border: 1px solid #363636; border-collapse: collapse;margin-right: 0px;'>
                        <tr>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right; padding-right: 3px;'>Total Op. Gravado:</td>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right;'>$totalOpgravado</td>
                        </tr>
                        <tr>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right; padding-right: 3px;'>IGV:</td>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right;'>$igv</td>
                        </tr>
                        <tr>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right; padding-right: 3px;'>Total a Pagar:</td>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right;'>$simbolfff22 $total</td>
                        </tr>
                        $monedahtmlDol
                    </table>
                    </td>
                </tr>
            </table>";

    $html = "

    
      <table style='width: 100%; border-collapse: separate; border-spacing: 40px;'>
          <tr>
              <td style='width: 50%;'>
                  $htmlEncabezado
                  $tabla_datos_cliente
                  $tabla
                  $tableconson
                  $tablaFooter
              </td>
              <td style='width: 50%;'>
              <h3 style='font-size: 8px'>COPIA</h3>
              $htmlEncabezado
                  $tabla_datos_cliente
                  $tabla
                  $tableconson
                  $tablaFooter
              </td>
          </tr>
      </table>

      <!-- <h4>Observación:</h4>
      {$datoVenta['observacion']} -->
      ";
    $html =  $html;

    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

    $this->mpdf->Output("Cotizacion{$datoVenta['numero']}.pdf", 'I');
  }*/
   public function comprobanteCotizacion($coti)
  {

    $this->mpdf = new \Mpdf\Mpdf([
      "format" => "A4-L",         // Formato A4 en orientación landscape
      "mode" => "utf-8",
      "margin_left" => 3,     // Margen izquierdo en milímetros
      "margin_right" => 3,    // Margen derecho en milímetros
      "margin_top" => 5,      // Margen superior en milímetros
      "margin_bottom" => 5,   // Margen inferior en milímetros
    ]);

    $diaSemana = date('N');
    $dias_acum = 0;

    if ($diaSemana == 2) {
      $dias_acum = 3;
    } else {
      $dias_acum = 2;
    }

    $listaProd1 = $this->conexion->query("SELECT pc.*,p.descripcion,TRIM(p.codigo) codigo  from productos_cotis pc 
              join productos p on p.id_producto = pc.id_producto where pc.id_coti='$coti' order by p.descripcion ASC");
    $sql = "select * from cotizaciones where cotizacion_id=" . $coti;
    $datoVenta = $this->conexion->query($sql)->fetch_assoc();
    // TOTAL PAGADO DE LAS CUOTAS QUE RESTAREMOS CON EL TOTAL DE LA COTIZACIONES
    $totalMontoCuotasCotizacion = $this->conexion->query("
      SELECT IFNULL(SUM(cc.monto), 0) AS total_monto
      FROM `cotizaciones` c
      LEFT JOIN `cuotas_cotizacion` cc ON cc.id_coti = c.cotizacion_id
      WHERE c.id_cliente = " . $datoVenta['id_cliente'] . "
      AND c.estado!=2
      AND DATE(c.fecha) < DATE_SUB(CURDATE(), INTERVAL $dias_acum DAY) 
    ")->fetch_assoc()['total_monto'];

    $sumaTotalCotizacion = $this->conexion->query("
        SELECT IFNULL(SUM(total), 0) as total 
        FROM `cotizaciones` 
        WHERE `id_cliente` = " . $datoVenta['id_cliente'] . " 
        AND estado!=2
        AND DATE(fecha) < DATE_SUB(CURDATE(), INTERVAL $dias_acum DAY) 
    ")->fetch_assoc()['total'];


    $SaldoPendientePagar = max(0, $sumaTotalCotizacion - $totalMontoCuotasCotizacion);
    $SaldoPendientePagar = number_format($SaldoPendientePagar,2);
    $datoEmpresa = $this->conexion->query("select * from empresas where id_empresa=" . $_SESSION['id_empresa'])->fetch_assoc();
    $resultVemedor = $this->conexion->query("select * from usuarios where usuario_id = " . $datoVenta['id_usuario'])->fetch_assoc();
    $resultC = $this->conexion->query("select * from clientes where id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
    $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : strlen($resultC['documento'] == 11 ? 'RUC' : '');
    $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha']);
    $tipo_pagoC = $datoVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
    $tabla_cuotas = '';
    $menosRowsNumH = 0;
    if ($datoVenta["id_tipo_pago"] == '2') {
      $rowTempCuo = '';
      $sql = "SELECT * FROM cuotas_cotizacion WHERE id_coti='$coti'";
      $resulTempCuo = $this->conexion->query($sql);
      $contadorCuota = 0;
      $menosRowsNumH = 1;
      $totalSaldoPagado = 0;
      foreach ($resulTempCuo as $cuotTemp) {
        $menosRowsNumH++;
        $contadorCuota++;
        $tempNum = Tools::numeroParaDocumento($contadorCuota, 2);
        $tempFecha = Tools::formatoFechaVisual($cuotTemp['fecha']);
        $tempMonto = Tools::money($cuotTemp['monto']);
        $totalSaldoPagado += $cuotTemp['monto'];
        $rowTempCuo .= "
            <tr>
                <td>Cuota $tempNum</td>
                <td>$tempFecha </td>
                <td>S/ $tempMonto</td>
            </tr>
            ";
      }
      $tabla_cuotas = '<div style="width: 100%;padding-top: 5px;">
        <table style="width:50%;margin:auto;display: block;text-align:center;font-size: 10px;">
                <thead>
                <tr>
                    <th>CUOTA</th>
                    <th>FECHA</th>
                    <th>MONTO</th>
                </tr>
                </thead>
                <tbody>
                    ' . $rowTempCuo . '
                </tbody>
        </table>
        </div>';
    }
    $formatter = new NumeroALetras;
    $qrImage = '';
    $hash_Doc = '';
    $tipo_documeto_venta = "COTIZACIÓN #: ";
    $htmlDOM = '';
    $totalLetras = 'SOLES';
    $totalOpGratuita = 0;
    $totalOpExonerada = 0;
    $totalOpinafec = 0;
    $totalOpgravado = 0;
    $totalDescuento = 0;
    $totalOpinafecta = 0;
    $SC = 0;
    $percepcion = 0;
    $total = 0;
    $contador = 1;
    $igv = 0;

    $rowHTML = '';
    $rowHTMLTERT = '';
    foreach ($listaProd1 as $prod) {

      //$datoVenta['cm_tc']

      if ($datoVenta['moneda'] == 2) {
        $prod['precio'] = $prod['precio'] / $datoVenta['cm_tc'];
      }
      $precio =  $prod['precio'];
      $importe = $precio * $prod['cantidad'];
      //$subtotal = $subtotal + $importe;
      $total += $importe;
      $tempDescuento = 0;
      $importe -= $tempDescuento;
      $totalDescuento += $tempDescuento;
      $observacion = $datoVenta['observacion'];
      //$precio = number_format($precio, 2, '.', ',');
      //$importe = number_format($importe, 2, '.', ',');
      $tempDescuento = number_format($tempDescuento, 2, '.', ',');
      $prod['codigo'] = trim($prod['codigo']);
      $temMedida1 = $this->getNomMedida($prod['presenta']);
      $prod['cantidad'] =  number_format($prod['cantidad'], 0);
      $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);

      $precioDisminu = $precio / $prod['presenta_cnt'];

      $precioDisminu = number_format($precioDisminu, 2, '.', ',');
      $multi = $prod['cantidad'] * $prod['presenta_cnt'];

      $rowHTML = $rowHTML . "
              <tr>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$contador</td>
                <td class='' style='font-size: 10px; text-align: center; border-left: 1px solid #363636;'>$multi</td>
                <td class='' style=' font-size: 10px; text-align: left;border-left: 1px solid #363636;'><strong>{$prod['descripcion']}</strong></td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$precioDisminu</td> 
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$importe</td>
              </tr>
              
            ";
      $contador++;
    }
    $cntRowEE = 41;
    $rowHTMLTERT = "";
    for ($tert = 0; $tert < ($cntRowEE - $contador) - $menosRowsNumH; $tert++) {
      $rowHTMLTERT = $rowHTMLTERT . " <tr>
        <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; color: white'>.</td>
    <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td>
            <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td>
            <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td>
            <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'></td>
          </tr>";
    }
    $totalLetras =   $formatter->toInvoice(number_format($total, 2, '.', ''), 2, $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES');
    $htmlEncabezado = "
          <table style='width: 100%; border-collapse: collapse; margin-top: 0px;'>
              <tr>
                  <td style='width: 100%; vertical-align: top;'>
                      <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <!-- Columna del Logo y Datos -->
                            <td style='width: 75%; text-align: left; vertical-align: middle;'>
                                <img style='max-width: 250px; max-height: 48px;' src='" . URL::to('files/logos/' . $datoEmpresa['logo']) . "'><br>
                                <span style='font-size: 9px;'><strong>Central Telefónica:</strong> {$datoEmpresa['telefono']}</span><br>
                                <span style='font-size: 9px;'><strong>Email:</strong> info@titanicsac.com | <strong>Web:</strong> www.titanicsac.com</span><br>
                                <span style='font-size: 9px;'><strong>Dirección:</strong> <span style='font-size: 9px;'>{$datoEmpresa['direccion']}</span></span>
                            </td>
                            <!-- Columna del Cuadro de Pedido -->
                              <td style='width: 25%; text-align: center; vertical-align: middle; border: 1px solid #1e1e1e;'>
                                    <span style='font-size: 9px;'>RUC: {$datoEmpresa['ruc']}</span><br><br> <!-- Usamos dos <br> para separar -->
                                    <span style='font-size: 9px; font-weight: bold;'>$tipo_documeto_venta {$datoVenta['numero']}</span>
                            </td>
                            </td>
                          </tr>
                      </table>
                  </td>
              </tr>
          </table>";

    $totalOpGratuita = number_format($totalOpGratuita, 2, '.', ',');
    $totalOpExonerada = number_format($totalOpExonerada, 2, '.', ',');
    $totalOpinafec = number_format($totalOpinafec, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
    $totalDescuento = number_format($totalDescuento, 2, '.', ',');
    $totalOpinafecta = number_format($totalOpinafecta, 2, '.', ',');
    $SC = number_format($SC, 2, '.', ',');
    $percepcion = number_format($percepcion, 2, '.', ',');
    $igv = $total / 1.18 * 0.18;
    $totalOpgravado = $total - $igv;
    $total = number_format($total, 2, '.', ',');
    $igv = number_format($igv, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
    $monedaVisual = $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES';
    $tabla_datos_cliente = "
    <table style='width: 100%; border: 1px solid #363636; margin-top: 3px; margin-bottom: 3px;'>
        <tr>
            <td style='font-size: 8px; padding: 2px;'><strong>CLIENTE:</strong> {$resultC['datos']}</td>
            <td style='font-size: 8px; padding: 2px;'><strong>CELULAR:</strong> {$resultC['telefono']}</td>
        </tr>
        <tr>
            <td style='font-size: 8px; padding: 2px;'><strong>DIRECCIÓN:</strong> {$resultC['direccion']}</td>
            <td style='font-size: 8px; padding: 2px;'><strong>VENDEDOR:</strong> {$resultVemedor['nombres']}</td>
        </tr>
        <tr>
            <td style='font-size: 8px; padding: 2px;'><strong>RUC/DNI:</strong> {$resultC['documento']}</td>
            <td style='font-size: 8px; padding: 2px;'><strong>FECHA:</strong> $fecha_emision</td>
        </tr>
        <tr>
            <td style='font-size: 8px; padding: 2px;'><strong>MONEDA:</strong> $monedaVisual</td>
            <td style='font-size: 8px; padding: 2px;'><strong>PAGO:</strong> Contado</td>
        </tr>
    </table>";




    $tabla = "
      <table style='width:100%;border-bottom: 1px solid #363636;border-collapse: collapse;'>
        <tr style='border-bottom: 1px solid #363636;border-collapse: collapse;'>
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 5%;'><strong>ITEM</strong></td>
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 8%;'><strong>CANTIDAD</strong></td>
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 65%;'><strong>DESCRIPCION</strong></td>
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>PRECIO</strong></td> 
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 12%;'><strong>SUB TOTAL</strong></td>
        </tr>
        $rowHTML
        $rowHTMLTERT
      </table>
    ";



    $tableconson = "
    <table style='width: 100%;padding-bottom: 0px;font-size: 10px;border-right: 1px solid;border-bottom: 1px solid;border-left: 1px solid;'>
        <tr>
            <td style=style='height: 10px;width: 100%; padding-bottom: 0px;'>
            <span style='font-size: 8px'>SON: | $totalLetras</span>
            </td>
        </tr>
    </table>";
    $dominio = '';
    $monedahtmlDol = '';
    if ($datoVenta['moneda'] == 2) {
      if ($datoVenta['moneda'] == 2) {
        $totalDolar = number_format($total * $datoVenta['cm_tc'], 2, '.', ",");
      } else {
        $totalDolar = number_format($total / $datoVenta['cm_tc'], 2, '.', ",");
      }
      $simbolfff = $datoVenta['moneda'] == 2 ? 'S/' : '$';
      $monedahtmlDol = "<tr>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 9px; text-align: right'>Total a Pagar</td>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 9px;  text-align: right' >$simbolfff $totalDolar</td>
          </tr>";
    }
    $simbolfff22 = $datoVenta['moneda'] == 1 ? 'S/' : '$';

    $tablaFooter = "<table style='width: 100%;'>
                <tr>
                    <td style='width: 50%;'>
                       <div style='width: 95%; padding: 3px; font-size: 9.5px;height: 90px '>
                       $hash_Doc
                       Observaciones:<br>
                       $observacion <br>
                       Saldo Pendiente:
                      $SaldoPendientePagar
                 </div>
                    </td>
                    <td style='width: 50%;text-align: right;'>
                        <table style='width: 50%; border: 1px solid #363636; border-collapse: collapse;margin-right: 0px;'>
                        <tr>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right; padding-right: 3px;'>Total Op. Gravado:</td>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right;'>$totalOpgravado</td>
                        </tr>
                        <tr>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right; padding-right: 3px;'>IGV:</td>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right;'>$igv</td>
                        </tr>
                        <tr>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right; padding-right: 3px;'>Total a Pagar:</td>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right;'>$simbolfff22 $total</td>
                        </tr>
                        $monedahtmlDol
                    </table>
                    </td>
                </tr>
            </table>";

    $html = "

    
      <table style='width: 100%; border-collapse: separate; border-spacing: 40px;'>
          <tr>
              <td style='width: 50%;'>
                  $htmlEncabezado
                  $tabla_datos_cliente
                  $tabla
                  $tableconson
                  $tablaFooter
              </td>
              <td style='width: 50%;'>
              <h3 style='font-size: 8px'>COPIA</h3>
              $htmlEncabezado
                  $tabla_datos_cliente
                  $tabla
                  $tableconson
                  $tablaFooter
              </td>
          </tr>
      </table>

      <!-- <h4>Observación:</h4>
      {$datoVenta['observacion']} -->
      ";
    $html =  $html;

    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

    $this->mpdf->Output("Cotizacion{$datoVenta['numero']}.pdf", 'I');
  }

  public function comprobantePedidos($coti)
{

    
    $mpdf = new \Mpdf\Mpdf([
      'format' => 'Letter', // Tamaño carta
      'margin_left' => 10,
      'margin_right' => 10,
      'margin_top' => 10,
      'margin_bottom' => 10,
      'margin_header' => 0,
      'margin_footer' => 0,
    ]);
    
    $listaProd1 = $this->conexion->query("SELECT pc.*,p.descripcion,TRIM(p.codigo) codigo  from productos_cotis pc 
            join productos p on p.id_producto = pc.id_producto where pc.id_coti='$coti' order by codigo ASC");

    $sql = "select * from cotizaciones where cotizacion_id=" . $coti;
    $datoVenta = $this->conexion->query($sql)->fetch_assoc();

    $datoEmpresa = $this->conexion->query("select * from empresas where id_empresa=" . $_SESSION['id_empresa'])->fetch_assoc();

    $resultVemedor = $this->conexion->query("select * from usuarios where usuario_id = " . $datoVenta['id_usuario'])->fetch_assoc();
    $resultC = $this->conexion->query("select * from clientes where id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
    $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : (strlen($resultC['documento']) == 11 ? 'RUC' : '');

    $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha']);

    $tipo_pagoC = $datoVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
    $tabla_cuotas = '';

    $menosRowsNumH = 0;

    if ($datoVenta["id_tipo_pago"] == '2') {
        $rowTempCuo = '';
        $sql = "SELECT * FROM cuotas_cotizacion WHERE id_coti='$coti'";
        $resulTempCuo = $this->conexion->query($sql);
        $contadorCuota = 0;
        $menosRowsNumH = 1;
        foreach ($resulTempCuo as $cuotTemp) {
            $menosRowsNumH++;
            $contadorCuota++;
            $tempNum = Tools::numeroParaDocumento($contadorCuota, 2);
            $tempFecha = Tools::formatoFechaVisual($cuotTemp['fecha']);
            $tempMonto = Tools::money($cuotTemp['monto']);
            $rowTempCuo .= "
                <tr>
                    <td>Cuota $tempNum</td>
                    <td>$tempFecha </td>
                    <td>S/ $tempMonto</td>
                </tr>
                ";
        }
        $tabla_cuotas = '<div style="width: 100%;">
            <table style="width:50%;margin:auto;display: block;text-align:center;font-size: 12px;">
                    <thead>
                    <tr>
                        <th>CUOTA</th>
                        <th>FECHA</th>
                        <th>MONTO</th>
                    </tr>
                    </thead>
                    <tbody>
                        ' . $rowTempCuo . '
                    </tbody>
            </table>
            </div>';
    }

    $formatter = new NumeroALetras;

    $qrImage = '';
    $hash_Doc = '';

    $tipo_documeto_venta = "  ";

    $htmlDOM = '';
    $totalLetras = 'SOLES';

    $totalOpGratuita = 0;
    $totalOpExonerada = 0;
    $totalOpinafec = 0;
    $totalOpgravado = 0;
    $totalDescuento = 0;
    $totalOpinafecta = 0;
    $SC = 0;
    $percepcion = 0;
    $total = 0;
    $contador = 1;
    $igv = 0;

    $rowHTML = '';
    $rowHTMLTERT = '';

    foreach ($listaProd1 as $prod) {
        if ($datoVenta['moneda'] == 2) {
            $prod['precio'] = $prod['precio'] / $datoVenta['cm_tc'];
        }
        $precio =  $prod['precio'];
        $importe = $precio * $prod['cantidad'];
        $total += $importe;
        $tempDescuento = 0;
        $importe -= $tempDescuento;
        $totalDescuento += $tempDescuento;

        $precio = number_format($precio, 2, '.', ',');
        $importe = number_format($importe, 2, '.', ',');
        $tempDescuento = number_format($tempDescuento, 2, '.', ',');
        $prod['codigo'] = trim($prod['codigo']);
        
        $temMedida1 = $this->getNomMedida($prod['presenta']);
        $prod['cantidad'] =  number_format($prod['cantidad'], 0);
        $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);

        $precioDisminu = $precio / $prod['presenta_cnt'];
        $precioDisminu = number_format($precioDisminu, 2, '.', ',');

        $rowHTML .= "
            <tr>
                <td class='' style='font-weight: bold;font-family: Arial, sans-serif; font-size: 11px; text-align: center; border-left: 1px solid #fff; padding: 0; width: 45px; white-space: nowrap;'>$contador</td>

                <td class='' style='font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding:0;  width: 55px; white-space: nowrap;'>$cnt4 </td>

                <td class='' style='font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; text-align: left;border-left: 1px solid #fff;  padding:0; '>{$prod['descripcion']}</td>

                <td class='' style='font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff;  padding:0; width: 70px; white-space: nowrap;'>{$prod['presenta_cnt']}  </td>
                
                <td class='' style='font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff;  padding:2px; width: 70px; white-space: nowrap;'>$precioDisminu</td> 
                <td class='' style='font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff;border-right: 1px solid #fff; padding:0; width: 110px; white-space: nowrap;'>$importe</td>
            </tr>
        ";
        $contador++;
    }

    $cntRowEE = 37;
    $rowHTMLTERT = "";
    for ($tert = 0; $tert < ($cntRowEE - $contador) - $menosRowsNumH; $tert++) {
        $rowHTMLTERT .= "<tr>
            <td class='' style='font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff; color: white; padding:0;'>.</td>
            <td class='' style='font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding:0; '> </td>
            <td class='' style='font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff;  padding:0; '> </td> 
            <td class='' style='font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff;  padding:0;'> </td>
            <td class='' style='font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff;  padding:0;'> </td>
            <td class='' style='font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff;border-right: 1px solid #fff;  padding:0;'> </td>
        </tr>";
    }

    $totalLetras =   $formatter->toInvoice(number_format($total, 2, '.', ''), 2, $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES');

    $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #fff ; float: right; margin: left 100px;px;'>
            <div style='padding: 5px;width: 100%; height: 100px; ' class=''>
            <div ></div>
            <span>  </span><br>
            <div style='padding-top:35px;' ></div>
            <span><strong>$tipo_documeto_venta {$datoVenta['numero']}</strong></span><br>
            <div style='margin-top: 10px'></div>
            <span> </span>
            </div>
            </div>
            </div>";
    
    $mpdf->WriteFixedPosHTML($htmlCuadroHead ?? '', 0, 5, 195, 130);

    $totalOpGratuita = number_format($totalOpGratuita, 2, '.', ',');
    $totalOpExonerada = number_format($totalOpExonerada, 2, '.', ',');
    $totalOpinafec = number_format($totalOpinafec, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
    $totalDescuento = number_format($totalDescuento, 2, '.', ',');
    $totalOpinafecta = number_format($totalOpinafecta, 2, '.', ',');
    $SC = number_format($SC, 2, '.', ',');
    $percepcion = number_format($percepcion, 2, '.', ',');
    $igv = $total / 1.18 * 0.18;
    $totalOpgravado = $total - $igv;
    $total = number_format($total, 2, '.', ',');
    $igv = number_format($igv, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');

    $monedaVisual = $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES';
    $html = "<div style='width: 100%; padding-top: 87px; margin:ritgh 5px; overflow: hidden;clear: both;'>
        <div style='width: 567px;border: 1px solid white'>
            <div style='width: 55%; float: left;'>
                <table style='width:100%; font-weight: bold;'>
                    <tr>
                        <td style=' font-size: 9px;text-align: left; color: white;'><strong>CONDICION: </strong></td>
                        <td style=' font-size: 9px;'>Contado</td>
                    </tr>
                    <tr>
                        <td style='font-family: Arial, sans-serif; font-size: 9px;text-align: left '><strong> </strong></td>
                        <td style='font-family: Arial, sans-serif; font-size: 9px;'>{$resultC['datos']}</td>
                    </tr>
                    <tr>
                        <td style='font-family: Arial, sans-serif; font-size: 9px;text-align: left'><strong> </strong></td>
                        <td style='font-family: Arial, sans-serif; font-size: 9px;'>{$resultC['direccion']}</td>
                    </tr>
                </table>
            </div>
            <div style='width: 45%; float: left'>
                <table style='width:100%; font-weight: bold;'>
                    <tr>
                        <td style='width:8px; font-size: 9px;text-align: left; color:white'><strong>fha</strong></td>
                        <td style='font-family: Arial, sans-serif; font-size: 9px; '>$fecha_emision</td>
                    </tr>
                    <tr>
                        <td style='width:8px; font-size: 9px;text-align: left;color:white'><strong>DOR</strong></td>
                        <td style='font-family: Arial, sans-serif; font-size: 9px;'>{$resultVemedor['nombres']} </td>
                    </tr>
                    <tr>
                        <td style='width:8px; font-size: 9px;text-align: left; color:white'><strong>CE</strong></td>
                        <td style='font-family: Arial, sans-serif; font-size: 9px;'>{$resultC['telefono']}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div style='width: 100%; padding-top: 20px; margin-left: 20px'>
        <table style='width:567px; border-bottom: 1px solid #fff;border-collapse: collapse;'>
            <tr style='border-bottom: 1px solid #fff;border-collapse: collapse;'>
                <td style=' font-size: 12px;text-align: center; color: #fff;border: 1px solid #fff; padding:0;'><strong>item</strong></td>
                <td style=' font-size: 12px; color: #fff;border: 1px solid #fff;border-collapse: collapse; padding: 0;'><strong>Cant</strong></td>
                <td style=' font-size: 12px;text-align: center; color: #fff;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>PRODUCTO</strong></td>
                <td style=' font-size: 12px;text-align: center; color: #fff;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>UNIDAD</strong></td> 
                <td style=' font-size: 12px;text-align: center; color: #fff;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>P.U.</strong></td> 
                <td style=' font-size: 12px;text-align: center; color: #fff;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>SUB TOTAL</strong></td>
            </tr>
            $rowHTML
            $rowHTMLTERT
            <tr>
                <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff;color: white; padding:0;'>.</td>
                <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff; padding:0;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-bottom: 1px solid #fff;  padding:0;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-bottom: 1px solid #fff;  padding:0;'> </td> 
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-right: 1px solid #fff;border-bottom: 1px solid #fff;'> </td>
            </tr>
        </table>
    </div>
    <div>
    </div>";

    $dominio = '';
    $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $monedahtmlDol = '';

    if ($datoVenta['moneda'] == 2) {
        if ($datoVenta['moneda'] == 2) {
            $totalDolar = number_format($total * $datoVenta['cm_tc'], 2, '.', ",");
        } else {
            $totalDolar = number_format($total / $datoVenta['cm_tc'], 2, '.', ",");
        }
        $simbol000 = $datoVenta['moneda'] == 2 ? 'S/' : '$';
        $monedahtmlDol = "<tr>
            <td style='border:none; font-size: 12px; text-align: right'>TOTAL S/</td>
            <td style='border-left: 1px solid #fff;border-collapse: collapse; font-size: 12px;  text-align: right'>$simbol000 $totalDolar</td>
        </tr>";
    }

    $simbol00022 = $datoVenta['moneda'] == 1 ? 'S/' : '$';
    $mpdf->SetHTMLFooter("
        <div style='font-weight: bold; font-family: Arial, sans-serif; height: 180px; width: 567px; margin-left: 20px; font-size: 10px; border: 1px solid white;'>
            <div style='float: left; font-weight: bold; font-family: Arial, sans-serif; width: 60%;'>
                <div>SON: | $totalLetras</div>
                <div style='font-weight: bold; font-family: Arial, sans-serif; padding-top: 10px;'>SALDO PENDIENTE: </div>
            </div>
            <div style='float: right; width: 40%;'>
                <table style='width: 100%; border-collapse: collapse; margin: right 55px'>
                    <tr>
                      
                        <td style='font-weight: bold; font-family: Arial, sans-serif; border: 1px solid #fff; font-size: 12px; text-align: right; padding-top: 50px;'>$simbol00022  $total</td>
                    </tr>
                    $monedahtmlDol
                </table>
            </div>
        </div>
        
        <div style='width: 567px; margin-left: 20px; height: 10px; margin-top: 3px;'>
            <div style='float: left; width: 20%; height: 10px '>
                $qrImage
            </div>
            <div style='width: 50%; padding-bottom: 5px; font-size: 12px; float: left; padding-top: 10px;'>
                <div style='width: 100%'></div>
                <div style='width: 95%; padding: 3px; font-size: 10px; height: 140px '>
                    $hash_Doc
                </div>
            </div>
        </div>
    ");
    


    $mpdf->Output("Cotizacion{$datoVenta['numero']}.pdf", 'I');
 
}




// public function getCotizacionesPorRango($numInicio, $numFin, $idCotizacion = null) {
//   $query = "SELECT * FROM view_cotizaciones WHERE 1=1";
//   $params = [];
//   $types = ''; // Inicializar la cadena de tipos

//   if ($numInicio !== null && $numFin !== null) {
//       $query .= " AND numero >= ? AND numero <= ?";
//       $params[] = $numInicio;
//       $params[] = $numFin;
//       $types .= 'ii'; // Suponiendo que son enteros; ajustar según el tipo real
//   }

//   // Filtrar por ID de cotización si está presente
//   if ($idCotizacion !== null) {
//       $query .= " AND id = ?";
//       $params[] = $idCotizacion;
//       $types .= 'i'; // Suponiendo que el ID es un entero
//   }

//   $stmt = $this->conexion->prepare($query);

  
//   if (!$stmt) {
//       die("Error al preparar la consulta: " . $this->conexion->error);
//   }

//   if (!empty($params)) {
//       $stmt->bind_param($types, ...$params); 
//   }

//   if (!$stmt->execute()) {
//       die("Error al ejecutar la consulta: " . $stmt->error);
//   }

//   $result = $stmt->get_result();

//   if ($result === false) {
//       die("Error al obtener el resultado: " . $stmt->error);
//   }

//   return $result->fetch_all(MYSQLI_ASSOC);
// }

// public function generarPDFCombinado($cotizaciones) {
//   // Crear una nueva instancia de Merger
//   $combinador = new Merger();

//   foreach ($cotizaciones as $cotizacion) {
//       $pdfCotizacionUrl = $this->comprobantePedidos($cotizacion['cotizacion_id']); 

//       // Verificar si la URL del PDF existe antes de agregarlo
//       if (file_exists($pdfCotizacionUrl)) {
//           try {
//               // Agregar el PDF a la combinación
//               $combinador->addFromFile($pdfCotizacionUrl);
//           } catch (Exception $e) {
//               // Manejar el error al agregar el PDF
//               error_log("Error al agregar el PDF: " . $e->getMessage());
//           }
//       } else {
//           // Manejar el caso donde el PDF no existe
//           error_log("El PDF no existe: " . $pdfCotizacionUrl);
//       }
//   }

//   // Guardar el archivo combinado
//   $output = 'archivo_combinado.pdf';
//   try {
//       // Combina y guarda el archivo
//       $combinador->merge(); // Asegúrate de no pasar parámetros incorrectos
//       $combinador->save($output); // Guardar el archivo combinado
//   } catch (Exception $e) {
//       // Manejar el error al guardar el archivo combinado
//       error_log("Error al guardar el archivo combinado: " . $e->getMessage());
//       return; // Salir si no se puede guardar
//   }

//   // Mostrar el archivo combinado en el navegador
//   header('Content-Type: application/pdf');
//   header('Content-Disposition: inline; filename="' . $output . '"');
//   readfile($output);
// }



// public function getCotizacionesPorRango($numInicio, $numFin,  $idCotizacion = null) {
//   // Construir la consulta con los parámetros recibidos
//   $query = "SELECT * FROM view_cotizaciones WHERE 1=1";
//   $params = [];
//   $types = ''; // Inicializar la cadena de tipos

//   // Filtrar por número de cotización si los valores están presentes
//   if ($numInicio !== null && $numFin !== null) {
//       $query .= " AND numero >= ? AND numero <= ?";
//       $params[] = $numInicio;
//       $params[] = $numFin;
//       $types .= 'ii'; // Suponiendo que son enteros; ajustar según el tipo real
//   }

 

//   // Filtrar por ID de cotización si está presente
//   if ($idCotizacion !== null) {
//       $query .= " AND id = ?";
//       $params[] = $idCotizacion;
//       $types .= 'i'; // Suponiendo que el ID es un entero
//   }

//   // Preparar la consulta
//   $stmt = $this->conexion->prepare($query);

//   // Comprobar si la consulta fue preparada correctamente
//   if (!$stmt) {
//       die("Error al preparar la consulta: " . $this->conexion->error);
//   }

//   // Unir parámetros y tipos si hay parámetros
//   if (!empty($params)) {
//       $stmt->bind_param($types, ...$params); // Usar la expansión de argumentos
//   }

//   if (!$stmt->execute()) {
//       die("Error al ejecutar la consulta: " . $stmt->error);
//   }

 
//   $result = $stmt->get_result();


//   if ($result === false) {
//       die("Error al obtener el resultado: " . $stmt->error);
//   }


//   return $result->fetch_all(MYSQLI_ASSOC);
// }


// public function generarPDFCombinado($cotizaciones) {
  
//   $mpdf = new \Mpdf\Mpdf();

//   foreach ($cotizaciones as $cotizacion) {
    
//       $pdfCotizacion = $this->comprobantePedidos($cotizacion);

    
//       $pageCount = $mpdf->SetSourceFile($pdfCotizacion);

      
//       for ($i = 1; $i <= $pageCount; $i++) {
//           $tplId = $mpdf->ImportPage($i);
//           $mpdf->AddPage(); 
//           $mpdf->UseTemplate($tplId); 
//       }
//   }

//   // Combinar los PDFs en uno solo y devolverlo
//   $output = 'archivo_combinado.pdf';
//   $mpdf->Output($output, 'I');  // 'I' para mostrar en el navegador, o 'F' para guardar en archivo
// }


  public function comprobanteNotaE($venta, $nombreXML = '')
  {


    $sql = "SELECT ne.*,ds.nombre as 'nota_nombre',v.id_cliente FROM notas_electronicas ne
      join documentos_sunat ds on ne.tido = ds.id_tido
      join ventas v on ne.id_venta = v.id_venta
      where ne.nota_id =" . $venta;
    $datoVenta = $this->conexion->query($sql)->fetch_assoc();
    $datoEmpresa = $this->conexion->query("select * from empresas where id_empresa=" . $_SESSION['id_empresa'])->fetch_assoc();

    $S_N = $datoVenta['serie'] . '-' . Tools::numeroParaDocumento($datoVenta['numero'], 6);
    $tipoDocNom = $datoVenta['nota_nombre'];
    $resultC = $this->conexion->query("select * from clientes where id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
    $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : strlen($resultC['documento'] == 11 ? 'RUC' : '');
    $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha']);

    $formatter = new NumeroALetras;




    $sql = "SELECT * FROM notas_electronicas_sunat where id_notas_electronicas = '$venta' ";
    $qrImage = '';
    $hash_Doc = '';
    if ($rowVS = $this->conexion->query($sql)->fetch_assoc()) {
      $hash_Doc = "HASH: " . $rowVS['hash'] . "<br>";
      $qrCode = new QrCode($rowVS["qr_data"]);
      $qrCode->setSize(150);
      $image = $qrCode->writeString(); //Salida en formato de texto
      $imageData = base64_encode($image);
      $qrImage = '<img style="width: 130px;" src="data:image/png;base64,' . $imageData . '">';
    }

    $tipo_documeto_venta = "";

    if ($datoVenta['tido'] == 3) {
      $tipo_documeto_venta = "NOTA DE CREDITO ELECTRÓNICA";
    } elseif ($datoVenta['tido'] == 4) {
      $tipo_documeto_venta = "NOTA DE DEBITO ELECTRÓNICA";
    }

    $htmlDOM = '';
    $totalLetras = 'SOLES';

    $totalOpGratuita = 0;
    $totalOpExonerada = 0;
    $totalOpinafec = 0;
    $totalOpgravado = 0;
    $totalDescuento = 0;
    $totalOpinafecta = 0;
    $SC = 0;
    $percepcion = 0;
    $total = 0;
    $contador = 1;
    $igv = 0;

    $rowHTML = '';
    $rowHTMLTERT = '';
    $listaProd1 = json_decode($datoVenta['productos'], true);

    foreach ($listaProd1 as $prod) {

      $precio =  $prod['precio'];
      $importe = $precio * $prod['cantidad'];
      //$subtotal = $subtotal + $importe;
      $total += $importe;
      $tempDescuento = 0;
      $importe -= $tempDescuento;
      $totalDescuento += $tempDescuento;

      $precio = number_format($precio, 2, '.', ',');
      $importe = number_format($importe, 2, '.', ',');
      $tempDescuento = number_format($tempDescuento, 2, '.', ',');

      $rowHTML = $rowHTML . "
              <tr>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>$contador</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>{$prod['cantidad']}</td>
                <td class='' style=' font-size: 11px; text-align: left;border-left: 1px solid #363636;'>{$prod['descripcion']}</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>$precio</td>
                 
                
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$importe</td>
              </tr>
            ";
      $contador++;
    }

    $cntRowEE = 40;
    $rowHTMLTERT = "";
    for ($tert = 0; $tert < $cntRowEE - $contador; $tert++) {
      $rowHTMLTERT = $rowHTMLTERT . " <tr>
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; color: white'>.</td>
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td> 
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
        
        
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'> </td>
      </tr>";
    }

    $totalLetras =   $formatter->toInvoice(number_format($total, 2, '.', ''), 2, 'SOLES');

    $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #ffffff ; float: right;'>

            <div style='width: 100%; height: 100px; border: 2px solid #1e1e1e' class=''>
            <div style='margin-top:10px'></div>
            <span>RUC: {$datoEmpresa['ruc']}</span><br>
            <div style='margin-top: 10px'></div>
            <span><strong>$tipoDocNom ELECTRONICA</strong></span><br>
            <div style='margin-top: 10px'></div>
            <span>Nro. $S_N </span>
            </div>
            </div>
            </div>";
    $dominio = DOMINIO;

    $this->mpdf->WriteFixedPosHTML("<img style='max-width: 300px;max-height: 85px' src='" . URL::to('files/logos/' . $datoEmpresa['logo']) . "'>", 15, 5, 150, 120);
    $this->mpdf->WriteFixedPosHTML($htmlCuadroHead, 0, 5, 195, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Central Telefónica: </strong> {$datoEmpresa['telefono']}</span>", 15, 27, 210, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Email: </strong> info@titanicsac.com | Web: www.titanicsac.com</span>", 15, 32, 210, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Dirección:</strong> <span style='font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 37, 120, 130);



    $totalOpGratuita = number_format($totalOpGratuita, 2, '.', ',');
    $totalOpExonerada = number_format($totalOpExonerada, 2, '.', ',');
    $totalOpinafec = number_format($totalOpinafec, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
    $totalDescuento = number_format($totalDescuento, 2, '.', ',');
    $totalOpinafecta = number_format($totalOpinafecta, 2, '.', ',');
    $SC = number_format($SC, 2, '.', ',');
    $percepcion = number_format($percepcion, 2, '.', ',');
    $igv = $total / 1.18 * 0.18;
    $totalOpgravado = $total - $igv;
    $total = number_format($total, 2, '.', ',');
    $igv = number_format($igv, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');


    $html = "<div style='width: 1000%;padding-top: 110px; overflow: hidden;clear: both;'>
        <div style='width: 100%;border: 1px solid black'>
        <div style='width: 55%; float: left; '>
        
        <table style='width:100%'>
          <tr>
            <td style=' font-size: 11px;text-align: left'><strong>DOCUMENTO:</strong></td>
            <td style=' font-size: 11px;'>{$resultC['documento']}</td>
          </tr>
          <tr>
            <td style=' font-size: 11px;text-align: left'><strong>CLIENTE:</strong></td>
            <td style=' font-size: 11px;'>{$resultC['datos']}</td>
          </tr>
          <tr>
            <td style=' font-size: 11px;text-align: left'><strong>DIRECCIÓN:</strong></td>
            <td style=' font-size: 11px;'>{$resultC['direccion']}</td>
          </tr>
        </table>
        </div>
        <div style='width: 45%; float: left'>
        <table style='width:100%'>
          <tr>
            <td style=' font-size: 11px;text-align: left'><strong>FECHA EMISIÓN:</strong></td>
            <td style=' font-size: 11px;'>$fecha_emision</td>
          </tr>
          
          </tr>
           <tr>
            <td style=' font-size: 11px;text-align: left'><strong>MONEDA:</strong></td>
            <td style=' font-size: 11px;'>SOLES</td>
          </tr>
        </table>
        </div>
        </div>
        
        
        </div>
        
        <div style='width: 100%; padding-top: 20px;'>
        <table style='width:100%;border-bottom: 1px solid #363636;border-collapse: collapse;'>
            <tr style='border-bottom: 1px solid #363636;border-collapse: collapse;'>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>ITEM</strong></td>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>CANT</strong></td>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>DESCRIPCION</strong></td>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>PRECIO U.</strong></td> 
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>IMPORTE</strong></td>
            
          </tr>
          $rowHTML
          $rowHTMLTERT
              <tr>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;color: white'>.</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td> 
                
                
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
              </tr>
         
        
        </table>
        </div>
        
        ";
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->SetHTMLFooter("
        
        <div style='height: 10px;width: 100%; padding-bottom: 0px;font-size: 10px;border: 1px solid black;'>. SON: | $totalLetras</div>
        
        
        <div style='width: 100%; height: 10px;margin-top: 3px;'>
        <div style='float: left; width: 20%;height: 10px '>
        $qrImage
        
        <div style='position: absolute; left: 80px; top: 90px;'></div>
        
        </div>
         <div style='width: 50%; padding-bottom: 5px;font-size: 12px; float: left; padding-top: 10px;'>
            <div style='width: 100%'></div>
            <div style='width: 95%; padding: 3px; font-size: 10px;height: 90px '>
            $hash_Doc
            Detalle:<br>
            Representación impresa de la $tipo_documeto_venta <br>Este documento puede ser validado en $dominio
            </div>
         </div>
         <div style='width: 30%;'>
         <table style='width: 100%;border-top: 1px solid #363636;border-bottom: 1px solid #363636;border-right: 1px solid #363636;border-collapse: collapse;'>
          
          
          <tr>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Total Op. Gravado:</td>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$totalOpgravado</td>
          </tr>
          <tr>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>IGV:</td>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$igv</td>
          </tr>
           <tr>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Importe Total:</td>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$total</td>
          </tr>
          
        </table>
            </div>
        </div> 
        ");
    /*$this->mpdf->WriteHTML($htmlDOM,\Mpdf\HTMLParserMode::HTML_BODY);*/
    $this->mpdf->Output($nombreXML . ".pdf", 'I');
  }

  public function guiaRemision($guia, $nombreXML)
  {

    $sql = "select * from guia_remision where id_guia_remision=" . $guia;
    $datosGuia = $this->conexion->query($sql)->fetch_assoc();
    $datoEmpresa = $this->conexion->query("select * from empresas where id_empresa=" . $_SESSION['id_empresa'])->fetch_assoc();

    $sql = "select * from ventas where id_venta=" . $datosGuia['id_venta'];
    $datoVenta = $this->conexion->query($sql)->fetch_assoc();
    $resultC = $this->conexion->query("select * from clientes where id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();

    $listaProductos = $this->conexion->query("select * from guia_detalles where id_guia=" . $guia);


    $numDoc = strlen($resultC["documento"]) > 7 ? $resultC["documento"] : '';

    $fechaEmision = Tools::formatoFechaNumero($datosGuia['fecha_emision']);

    $S_N = $datosGuia['serie'] . '-' . Tools::numeroParaDocumento($datosGuia['numero'], 6);
    $tipoDocNom = 'GUÍA DE REMISIÓN REMITENTE';
    $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #ffffff ; float: right;'>

            <div style='width: 100%; height: 100px; border: 2px solid #1e1e1e' class=''>
            <div style='margin-top:10px'></div>
            <span>RUC: {$datoEmpresa['ruc']}</span><br>
            <div style='margin-top: 10px'></div>
            <span><strong>$tipoDocNom ELECTRONICA</strong></span><br>
            <div style='margin-top: 10px'></div>
            <span>Nro. $S_N </span>
            </div>
            </div>
            </div>";

    $this->mpdf->WriteFixedPosHTML("<img style='max-width: 300px;max-height: 85px' src='" . URL::to('files/logos/' . $datoEmpresa['logo']) . "'>", 15, 5, 150, 120);
    $this->mpdf->WriteFixedPosHTML($htmlCuadroHead, 0, 5, 195, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px;margin: 1pt 2pt 3pt;'><strong>Central Telefónica: </strong>958032985</span>", 15, 27, 210, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px;margin: 1pt 2pt 3pt;'><strong>Email: </strong> info@titanicsac.com | Web: www.titanicsac.com</span>", 15, 32, 210, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px;margin: 1pt 2pt 3pt;'><strong>Dirección:</strong> <span style='font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 37, 120, 130);

    $rowHTML = '';
    $rowHTMLTERT = '';
    $conradorRow = 1;
    foreach ($listaProductos as $itemProd) {
      $rowHTML .= "
            <tr>
                <td  style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>$conradorRow</td>
                <td  style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>{$itemProd['id_producto']}</td>
                <td  style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>{$itemProd['detalles']}</td>
                <td  style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>{$itemProd['unidad']}</td>
                <td  style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>{$itemProd['cantidad']}</td>
            </tr>
            ";
      $conradorRow++;
    }

    $html = "<div style='width: 1000%;padding-top: 110px; overflow: hidden;clear: both;'>
        <div style='width: 100%;border: 1px solid black'>
            <table style='width:100%'>
                <tr style='background-color: #cbcbcb'>
                    <td colspan='2' style='color: black; font-size: 14px; font-weight: bold'>DESTINATARIO</td>
                </tr>
              <tr>
                <td style=' font-size: 11px;text-align: left'><strong>Razón Social: </strong>{$resultC['datos']}</td>
                <td style=' font-size: 11px;'><strong>RUC: </strong>$numDoc</td>
              </tr>
              <tr>
                <td colspan='2' style=' font-size: 11px;text-align: left'><strong>Dirección: </strong> {$datosGuia['dir_llegada']}</td> 
              </tr>
            </table> 
        </div>
        <br>
        <div style='width: 100%;border: 1px solid black'>
            <table style='width:100%'>
                <tr style='background-color: #cbcbcb'>
                    <td colspan='2' style='color: black; font-size: 14px; font-weight: bold'>ENVIO</td>
                </tr>
              <tr>
                <td style=' font-size: 11px;text-align: left'><strong>Fecha Emisión: </strong>$fechaEmision</td>
                <td style=' font-size: 11px;'><strong>Fecha Inicio de Traslado: </strong>$fechaEmision</td>
              </tr>
              <tr>
                <td style=' font-size: 11px;text-align: left'><strong>Motivo Traslado: </strong>VENTAÚX</td>
                <td style=' font-size: 11px;'><strong>Modalidad de Transporte: </strong>{$datosGuia['tipo_transporte']}</td>
              </tr>
              <tr>
                <td style=' font-size: 11px;text-align: left'><strong>Peso Bruto Total (KGM): </strong>{$datosGuia['peso']}</td>
                <td style=' font-size: 11px;'><strong>Número de Bultos: </strong>{$datosGuia['nro_bultos']}</td>
              </tr>
              <tr>
                <td style=' font-size: 11px;text-align: left'><strong>P. Partida: </strong>{$datoEmpresa['direccion']}</td>
                <td style=' font-size: 11px;'><strong>P. Llegada: </strong>{$datosGuia['dir_llegada']}</td>
              </tr>
              
            </table> 
        </div>
        <br>
        <div style='width: 100%;border: 1px solid black'>
            <table style='width:100%'>
                <tr style='background-color: #cbcbcb'>
                    <td colspan='2' style='color: black; font-size: 14px; font-weight: bold'>TRANSPORTE</td>
                </tr>
              <tr>
                <td style=' font-size: 11px;text-align: left'><strong>Razón Social: </strong>{$datosGuia['razon_transporte']}</td>
                <td style=' font-size: 11px;'><strong>RUC: </strong>{$datosGuia['ruc_transporte']}</td>
              </tr>
              <tr>
                <td style=' font-size: 11px;text-align: left'><strong>Vehiculo: </strong>{$datosGuia['vehiculo']}</td>
                <td style=' font-size: 11px;'><strong>Conductor: </strong>{$datosGuia['chofer_brevete']}</td>
              </tr>
            </table> 
        </div>
        
        <div style='width: 100%; padding-top: 20px;'>
        <table style='width:100%;border-bottom: 1px solid #363636;border-collapse: collapse;'>
            <tr style='border-bottom: 1px solid #363636;border-collapse: collapse;'>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>ITEM</strong></td>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>CODIGO</strong></td>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>DESCRIPCION</strong></td>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>UNIDAD</strong></td> 
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>CANTIDAD</strong></td>
            
          </tr>
          $rowHTML
          $rowHTMLTERT
              <tr>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;color: white'>.</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td> 
                
                
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
              </tr>
         
        
        </table>
        </div>
        
        ";

    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output($nombreXML . ".pdf", 'I');
  }

  public function comprobanteVentaMa4($venta, $nombreXML = '-')
  {



    $this->mpdf  = new \Mpdf\Mpdf([
      //"orientation"=>"P",
      //'margin_bottom' => 5,
      //'margin_top' => 2,
      //'margin_left' => 4,
      'format' => [210, 148],
      //'margin_right' => 4,
      'mode' => 'utf-8',
    ]);



    $listaProd1 = $this->conexion->query("SELECT productos_ventas.*,p.descripcion,p.codigo,p.medida FROM productos_ventas join productos p on p.id_producto = productos_ventas.id_producto WHERE id_venta=" . $venta);
    $listaProd2 = $this->conexion->query("SELECT * FROM ventas_servicios WHERE id_venta=" . $venta);
    $ventaSunat = $this->conexion->query("SELECT * FROM ventas_sunat WHERE id_venta=" . $venta)->fetch_assoc();
    $guiaRealionada = '';
    $sql = "SELECT * FROM guia_remision where id_venta = $venta";
    if ($rowGuia = $this->conexion->query($sql)->fetch_assoc()) {
      $guiaRealionada = $rowGuia["serie"] . '-' . Tools::numeroParaDocumento($rowGuia["numero"], 6);
    }

    $sql = "select * from ventas where id_venta=" . $venta;
    $datoVenta = $this->conexion->query($sql)->fetch_assoc();
    $monedaVisual = $datoVenta["moneda"] == "1" ? "SOLES" : 'DOLAR';
    $datoEmpresa = $this->conexion->query("select * from empresas where id_empresa=" . $datoVenta['id_empresa'])->fetch_assoc();


    /*   var_dump("SELECT * FROM sucursales WHERE cod_sucursal ='{$_SESSION['sucursal']}' AND empresa_id=" . $datoVenta['id_empresa']);
    die();  */
    /*   if (is_null($datoSucursal)) {
      var_dump('es nulo');
      die();
    } else {
      var_dump($datoSucursal);
      die();
    } */


    $igv_venta_sel = $datoVenta['igv'];

    $S_N = $datoVenta['serie'] . '-' . Tools::numeroParaDocumento($datoVenta['numero'], 6);
    $tipoDocNom = $datoVenta['id_tido'] == 1 ? 'BOLETA' : 'FACTURA';
    $resultC = $this->conexion->query("select * from clientes where id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
    $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : strlen($resultC['documento'] == 11 ? 'RUC' : '');
    $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha_emision']);
    $fecha_vencimiento = Tools::formatoFechaVisual($datoVenta['fecha_vencimiento']);

    $tipo_pagoC = $datoVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
    $tabla_cuotas = '';

    $menosRowsNumH = 0;

    /* if ($datoVenta["id_tipo_pago"] == '2') {
      $rowTempCuo = '';
      $sql = "SELECT * FROM dias_ventas WHERE id_venta='$venta'";
      $resulTempCuo = $this->conexion->query($sql);
      $contadorCuota = 0;
      $menosRowsNumH = 1;
      foreach ($resulTempCuo as $cuotTemp) {
        $menosRowsNumH++;
        $contadorCuota++;
        $tempNum = Tools::numeroParaDocumento($contadorCuota, 2);
        $tempFecha = Tools::formatoFechaVisual($cuotTemp['fecha']);
        $tempMonto = Tools::money($cuotTemp['monto']);
        $rowTempCuo .= "
            <tr>
                <td>Cuota $tempNum</td>
                <td>$tempFecha </td>
                <td>S/ $tempMonto</td>
            </tr>
            ";
      }
      $tabla_cuotas = '<div style="width: 100%;">
        <table style="width:50%;margin:auto;display: block;text-align:center;font-size: 10px;">
                <thead>
                <tr>
                    <th>CUOTA</th>
                    <th>FECHA</th>
                    <th>MONTO</th>
                </tr>
                </thead>
                <tbody>
                    ' . $rowTempCuo . '
                </tbody>
        </table>
        </div>';
    } */

    $formatter = new NumeroALetras;


    $sql = "SELECT * FROM ventas_sunat where id_venta = '$venta' ";
    $qrImage = '';
    $hash_Doc = '';
    if ($rowVS = $this->conexion->query($sql)->fetch_assoc()) {
      $hash_Doc = "HASH: " . $rowVS['hash'] . "<br>";
      $qrCode = new QrCode($rowVS["qr_data"]);
      $qrCode->setSize(150);
      $image = $qrCode->writeString(); //Salida en formato de texto
      $imageData = base64_encode($image);
      $qrImage = '<img style="width: 100px;" src="data:image/png;base64,' . $imageData . '">';
    }

    $tipo_documeto_venta = "";

    if ($datoVenta['id_tido'] == 1) {
      $tipo_documeto_venta = "BOLETA DE VENTA ELECTRÓNICA";
    } elseif ($datoVenta['id_tido'] == 2) {
      $tipo_documeto_venta = "FACTURA DE VENTA ELECTRÓNICA";
    } elseif ($datoVenta['id_tido'] == 6) {
      $qrImage = '';
      $tipo_documeto_venta = "NOTA DE VENTA  ELECTRÓNICA";
    }

    $htmlDOM = '';
    $totalLetras = 'SOLES';

    $totalOpGratuita = 0;
    $totalOpExonerada = 0;
    $totalOpinafec = 0;
    $totalOpgravado = 0;
    $totalDescuento = 0;
    $totalOpinafecta = 0;
    $SC = 0;
    $percepcion = 0;
    $total = 0;
    $contador = 1;
    $igv = 0;

    $rowHTML = '';
    $rowHTMLTERT = '';

    foreach ($listaProd1 as $prod) {

      $precio =  $prod['precio'];
      $importe = $precio * $prod['cantidad'];
      //$subtotal = $subtotal + $importe;
      $total += $importe;
      $tempDescuento = 0;
      $importe -= $tempDescuento;
      $totalDescuento += $tempDescuento;

      $precio = $precio;
      $importe = number_format($importe, 2, '.', ',');
      $tempDescuento = number_format($tempDescuento, 2, '.', ',');
      $multi = $prod['cantidad'] * $prod['presenta_cnt'];
      $rowHTML = $rowHTML . "
              <tr>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$contador</td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>{$multi}</td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>{$prod['medida']}</td>
                <td class='' style=' font-size: 10px; text-align: left;border-left: 1px solid #363636;'>{$prod['codigo']} | {$prod['descripcion']}</td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$precio</td>
                 
                
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$importe</td>
              </tr>
            ";
      $contador++;
    }
    foreach ($listaProd2 as $prod) {

      $precio =  $prod['monto'];
      $importe = $precio * $prod['cantidad'];
      //$subtotal = $subtotal + $importe;
      $total += $importe;
      $tempDescuento = 0;
      $importe -= $tempDescuento;
      $totalDescuento += $tempDescuento;

      $precio = number_format($precio, 2, '.', ',');
      $importe = number_format($importe, 2, '.', ',');
      $tempDescuento = number_format($tempDescuento, 2, '.', ',');

      $rowHTML = $rowHTML . "
              <tr>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$contador</td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>{$prod['cantidad']}</td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'> </td>
                <td class='' style=' font-size: 10px; text-align: left;border-left: 1px solid #363636;'>{$prod['descripcion']}</td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$precio</td>
                
                
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$importe</td>
              </tr>
            ";
      $contador++;
    }
    $cntRowEE = 9;
    $rowHTMLTERT = "";
    for ($tert = 0; $tert < ($cntRowEE - $contador) - $menosRowsNumH; $tert++) {
      $rowHTMLTERT = $rowHTMLTERT . " <tr>
        <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636; color: white'>.</td>
        <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636; '> </td>
        <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636; '> </td> 
        <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636; '> </td>
        <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636; '> </td>
        
        
        <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'> </td>
      </tr>";
    }




    $totalLetras =   $formatter->toInvoice(number_format($total, 2, '.', ''), 2, $datoVenta["moneda"] == "1" ? "SOLES" : 'DOLARES');

    $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #ffffff ; float: right;'>

            <div style='padding: 5px;width: 100%; height: 70px; border: 2px solid #1e1e1e' class=''>
                <div style='margin-top:5px'></div>
            <span style='font-size: 12px;'>RUC: {$datoEmpresa['ruc']}</span><br>
            <div style='margin-top: 5px'></div>
            <span style='font-size: 12px;'><strong>$tipo_documeto_venta</strong></span><br>
            <div style='margin-top: 5px'></div>
            <span style='font-size: 12px;'>Nro. $S_N </span>
            </div>
            </div>
            </div>";


    $this->mpdf->WriteFixedPosHTML("<div ><img style='height: 95px;width: 360px;' src='" .
      URL::to('files/logos/' . $datoEmpresa['logo']) . "'></div>", 15, 5, 100, 120);

    $this->mpdf->WriteFixedPosHTML($htmlCuadroHead, 0, 5, 195, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Central Telefónica: </strong> {$datoEmpresa['telefono']}</span>", 15, 32, 210, 130);




    $datoSucursal = $this->conexion->query("SELECT * FROM sucursales WHERE cod_sucursal ='{$datoVenta['sucursal']}' AND empresa_id=" . $datoVenta['id_empresa'])->fetch_assoc();
    if ($datoVenta['sucursal'] == '1') {
      $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Dirección:</strong> <span style='font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 36, 120, 130);
    } else {
      if (is_null($datoSucursal)) {
        $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Dirección:</strong> <span style='font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 36, 120, 130);
      } else {
        $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Dirección:</strong> <span style='font-size: 10px'>{$datoSucursal['direccion']}</span></span>", 15, 36, 120, 130);
      }
    }


    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Email: </strong> info@titanicsac.com | Web: www.titanicsac.com</span>", 15, 40, 210, 130);




    $totalOpGratuita = number_format($totalOpGratuita, 2, '.', ',');
    $totalOpExonerada = number_format($totalOpExonerada, 2, '.', ',');
    $totalOpinafec = number_format($totalOpinafec, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
    $totalDescuento = number_format($totalDescuento, 2, '.', ',');
    $totalOpinafecta = number_format($totalOpinafecta, 2, '.', ',');
    $SC = number_format($SC, 2, '.', ',');
    $percepcion = number_format($percepcion, 2, '.', ',');
    $igv = $total / ($igv_venta_sel + 1) * $igv_venta_sel;
    $totalOpgravado = $total - $igv;
    $total = $total;
    $igv = number_format($igv, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');



    //$total = number_format($total, 2, '.', ',');
    /*   $datoSucursal = $this->conexion->query("SELECT * FROM sucursales WHERE cod_sucursal ='{$_SESSION['sucursal']}' AND empresa_id=" . $datoVenta['id_empresa'])->fetch_assoc(); */
    /*  $as = $this->conexion->query("SELECT * FROM sucursales WHERE cod_sucursal ='2' AND empresa_id=" . 28)->fetch_assoc();
    var_dump($as);
    die(); */

    if ($datoVenta['sucursal'] != '1') {
      if (is_null($datoSucursal)) {
        $resultC['direccion'] = $resultC['direccion'];
      } else {
        $resultC['direccion'] = $datoSucursal['direccion'];
      }
    }


    $html = "<div style='width: 100%;padding-top: 120px; overflow: hidden;clear: both;'>
        <div style='width: 100%;border: 1px solid black;'>
        <div style='width: 55%; float: left; '>
        
        <table style='width:100%'>
          <tr>
            <td style=' font-size: 10px;text-align: left'><strong>RUC/DNI:</strong></td>
            <td style=' font-size: 10px;'>{$resultC['documento']}</td>
          </tr>
          <tr>
            <td style=' font-size: 10px;text-align: left'><strong>CLIENTE:</strong></td>
            <td style=' font-size: 10px;'>{$resultC['datos']}</td>
          </tr>
          <tr>
            <td style=' font-size: 10px;text-align: left'><strong>DIRECCIÓN:</strong></td>
            <td style=' font-size: 10px;'>{$resultC['direccion']}</td>
          </tr>
          <tr>
            <td style=' font-size: 10px;text-align: left'><strong>NRO GUÍA:</strong></td>
            <td style=' font-size: 10px;'>$guiaRealionada</td>
          </tr>
        </table>
        </div>
        <div style='width: 45%; float: left'>
        <table style='width:100%'>
        
          <tr>
            <td style=' font-size: 10px;text-align: left'><strong>FECHA EMISIÓN:</strong></td>
            <td style=' font-size: 10px;'>$fecha_emision</td>
          </tr>
          <tr>
            <td style=' font-size: 10px;text-align: left'><strong>FECHA VENCIMIENTO:</strong></td>
            <td style=' font-size: 10px;'>$fecha_vencimiento</td>
          </tr>
          
           <tr>
            <td style=' font-size: 10px;text-align: left'><strong>MONEDA:</strong></td>
            <td style=' font-size: 10px;'>$monedaVisual</td>
          </tr>
          <tr>
            <td style=' font-size: 10px;text-align: left'><strong>PAGO:</strong></td>
            <td style=' font-size: 10px;'>$tipo_pagoC</td>
          </tr>
        </table>
        </div>
        </div>
        
        
        </div>
        $tabla_cuotas
        <div style='width: 100%; padding-top: 5px;'>
        <table style='width:100%;border-bottom: 1px solid #363636;border-collapse: collapse;'>
            <tr style='border-bottom: 1px solid #363636;border-collapse: collapse;'>
            <td style=' font-size: 10px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>ITEM</strong></td>
            <td style=' font-size: 10px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>CANT</strong></td>
            <td style=' font-size: 10px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>Medida</strong></td>
            <td style=' font-size: 10px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>DESCRIPCION</strong></td>
            <td style=' font-size: 10px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>PRECIO U.</strong></td> 
            <td style=' font-size: 10px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>IMPORTE</strong></td>
            
          </tr>
          $rowHTML
          $rowHTMLTERT
             
         
        
        </table>
        </div>
        
        ";
    $dominio = DOMINIO;
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

    /*$this->mpdf->SetHTMLFooter("<div style=' width: 100%;'>
        <div style='height: 10px;width: 100%; padding-bottom: 0px;font-size: 9px;border: 1px solid black;'>. SON: | $totalLetras</div>
        <div style='width: 100%;margin-top: 5px;'>
                <div style='width: 18%;float: left;'>
                    $qrImage
                </div>
                <div style='width: 58%;float: left; font-size: 12px;'>
                     $hash_Doc
                        Detalle:<br>
                        Representación impresa de la $tipo_documeto_venta <br>Este documento puede ser validado en $dominio
                </div>
                <div style='width: 24%;float: left; font-size: 12px;'>
                <table style='width: 100%;border-top: 1px solid #363636;border-bottom: 1px solid #363636;border-right: 1px solid #363636;border-collapse: collapse;'>
                  <tr>
                    <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 10px; text-align: right'>Total Op. Gravado:</td>
                    <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 10px;  text-align: right' >$totalOpgravado</td>
                  </tr>
                  <tr>
                    <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 10px; text-align: right'>IGV:</td>
                    <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 10px;  text-align: right' >$igv</td>
                  </tr>
                  
                  <tr>
                    <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 10px; text-align: right'>Total a Pagar</td>
                    <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 10px;  text-align: right' >$total</td>
                  </tr>
                  
                </table>
                </div>
        </div>
 </div>");*/
    if ($datoVenta['apli_igv'] == '0') {
      $totalOpgravado = $total;
      $igv = '0.00';
    }
    //die();

    $this->mpdf->SetHTMLFooter("
        <div style='height: 3px; width:100%;'></div>
        <div style='height: 10px;width: 100%; padding-bottom: 0px;font-size: 9px;border: 1px solid black;'>. SON: | $totalLetras</div>
        
        
        <div style='width: 100%; height: 10px;  '>
        
        <div style='float: left; width: 20%; '>
        $qrImage
         
        
        </div>
         <div style='width: 50%; padding-bottom:  0px;font-size: 12px; float: left; padding-top: 5px;'>
            <div style='width: 100%'></div>
            <div style='width: 95%; padding: 3px; font-size: 10px;height: 90px '>
            $hash_Doc
            Detalle:<br>
            Representación impresa de la $tipo_documeto_venta <br>Este documento puede ser validado en $dominio
            <br><b>Observaciones:</b>{$datoVenta['observacion']}
            </div>
         </div>
         <div style='width: 30%; padding-top: 5px;'>
         <table style='width: 100%;border-top: 1px solid #363636;border-bottom: 1px solid #363636;border-right: 1px solid #363636;border-collapse: collapse;'>
          
          <tr>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 10px; text-align: right'>Total Op. Gravado:</td>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 10px;  text-align: right' >$totalOpgravado</td>
          </tr>
          <tr>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 10px; text-align: right'>IGV:</td>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 10px;  text-align: right' >$igv</td>
          </tr>
          
          <tr>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 10px; text-align: right'>Total a Pagar</td>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 10px;  text-align: right' >$total</td>
          </tr>
          
        </table>
            </div>
        </div> 
        ");

    $this->mpdf->Output($nombreXML . ".pdf", 'I');
  }


  public function comprobanteVenta($venta, $nombreXML = '-')
  {
    $this->comprobanteVentaGen("I", $venta, $nombreXML ? $nombreXML : '-');
  }

  public function comprobanteVentaBinario($venta, $nombreXML = '-')
  {
    $this->comprobanteVentaGen("F", $venta, $nombreXML ? $nombreXML : '-');
  }

  private  function comprobanteVentaGen($dist, $venta, $nombreXML)
  {


    $guiaRealionada = '';

    $listaProd1 = $this->conexion->query("SELECT productos_ventas.*,p.descripcion,p.codigo FROM productos_ventas 
    join productos p on p.id_producto = productos_ventas.id_producto WHERE id_venta=" . $venta);
    $listaProd2 = $this->conexion->query("SELECT * FROM ventas_servicios WHERE id_venta=" . $venta);
    $ventaSunat = $this->conexion->query("SELECT * FROM ventas_sunat WHERE id_venta=" . $venta)->fetch_assoc();

    $sql = "SELECT * FROM guia_remision where id_venta = $venta";
    if ($rowGuia = $this->conexion->query($sql)->fetch_assoc()) {
      $guiaRealionada = $rowGuia["serie"] . '-' . Tools::numeroParaDocumento($rowGuia["numero"], 6);
    }

    $sql = "select * from ventas where id_venta=" . $venta;
    $datoVenta = $this->conexion->query($sql)->fetch_assoc();
    $datoEmpresa = $this->conexion->query("select * from empresas where id_empresa=" . $datoVenta['id_empresa'])->fetch_assoc();

    $igv_venta_sel = $datoVenta['igv'];

    $isSEgundoPago = false;
    $pagoData = '';
    if ($datoVenta['pagado2']) {
      $isSEgundoPago = true;
      $sql = "select *  from metodo_pago where id_metodo_pago='{$datoVenta['medoto_pago2_id']}'";
      $metodo2 = $this->conexion->query($sql)->fetch_assoc();
      $sql = "select *  from metodo_pago where id_metodo_pago='{$datoVenta['medoto_pago_id']}'";
      $metodo1 = $this->conexion->query($sql)->fetch_assoc();

      $pagoData = "<b>METODO DE PAGO 1 \"{$metodo1['nombre']}\"</b>: S/{$datoVenta['pagado']}, <b>Y METODO DE PAGO 2 \"{$metodo2['nombre']}\"</b>: S/{$datoVenta['pagado2']}";
    } else {
      $sql = "select *  from metodo_pago where id_metodo_pago='{$datoVenta['medoto_pago_id']}'";
      $metodo1 = $this->conexion->query($sql)->fetch_assoc();
      $montoPagadoooo = $datoVenta['pagado'] ? $datoVenta['pagado'] : $datoVenta["total"];
      $pagoData = "";
    //   <b>METODO DE PAGO \"{$metodo1['nombre']}\"</b>: S/$montoPagadoooo
    }


    $S_N = $datoVenta['serie'] . '-' . Tools::numeroParaDocumento($datoVenta['numero'], 6);
    $tipoDocNom = $datoVenta['id_tido'] == 1 ? 'BOLETA' : 'FACTURA';
    $resultC = $this->conexion->query("select * from clientes where id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
    $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : strlen($resultC['documento'] == 11 ? 'RUC' : '');
    $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha_emision']);
    $fecha_vencimiento = Tools::formatoFechaVisual($datoVenta['fecha_vencimiento']);

    $tipo_pagoC = $datoVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
    $tabla_cuotas = '';

    $menosRowsNumH = 0;

    if ($datoVenta["id_tipo_pago"] == '2') {
      $rowTempCuo = '';
      $sql = "SELECT * FROM dias_ventas WHERE id_venta='$venta'";
      $resulTempCuo = $this->conexion->query($sql);
      $contadorCuota = 0;
      $menosRowsNumH = 1;
      foreach ($resulTempCuo as $cuotTemp) {
        $menosRowsNumH++;
        $contadorCuota++;
        $tempNum = Tools::numeroParaDocumento($contadorCuota, 2);
        $tempFecha = Tools::formatoFechaVisual($cuotTemp['fecha']);
        $tempMonto = Tools::money($cuotTemp['monto']);
        $rowTempCuo .= "
            <tr>
                <td>Cuota $tempNum</td>
                <td>$tempFecha </td>
                <td>S/ $tempMonto</td>
            </tr>
            ";
      }
      $tabla_cuotas = '<div style="width: 100%;">
      
        </div>';
    }
//   <table style="width:50%;margin:auto;display: block;text-align:center;font-size: 12px;">
//                 <thead>
//                 <tr>
//                     <th>CUOTA</th>
//                     <th>FECHA</th>
//                     <th>MONTO</th>
//                 </tr>
//                 </thead>
//                 <tbody>
//                     ' . $rowTempCuo . '
//                 </tbody>
//         </table>
    $formatter = new NumeroALetras;


    $sql = "SELECT * FROM ventas_sunat where id_venta = '$venta' ";
    $qrImage = '';
    $hash_Doc = '';
    if ($rowVS = $this->conexion->query($sql)->fetch_assoc()) {
      $hash_Doc = "HASH: " . $rowVS['hash'] . "<br>";
      $qrCode = new QrCode($rowVS["qr_data"]);
      $qrCode->setSize(150);
      $image = $qrCode->writeString(); //Salida en formato de texto
      $imageData = base64_encode($image);
      $qrImage = '<img style="width: 130px;" src="data:image/png;base64,' . $imageData . '">';
    }

    $tipo_documeto_venta = "";

    if ($datoVenta['id_tido'] == 1) {
      $tipo_documeto_venta = "BOLETA DE VENTA ELECTRÓNICA";
    } elseif ($datoVenta['id_tido'] == 2) {
      $tipo_documeto_venta = "FACTURA DE VENTA ELECTRÓNICA";
    } elseif ($datoVenta['id_tido'] == 6) {
      $qrImage = '';
      $tipo_documeto_venta = "NOTA DE VENTA  ELECTRÓNICA";
    }

    $htmlDOM = '';
    $totalLetras = 'SOLES';

    $totalOpGratuita = 0;
    $totalOpExonerada = 0;
    $totalOpinafec = 0;
    $totalOpgravado = 0;
    $totalDescuento = 0;
    $totalOpinafecta = 0;
    $SC = 0;
    $percepcion = 0;
    $total = 0;
    $contador = 1;
    $igv = 0;

    $rowHTML = '';
    $rowHTMLTERT = '';

    foreach ($listaProd1 as $prod) {
      $precio =  $prod['precio'];
      $importe = $precio * $prod['cantidad'];
      //$subtotal = $subtotal + $importe;
      $total += $importe;
      $tempDescuento = 0;
      $importe -= $tempDescuento;
      $totalDescuento += $tempDescuento;

      $precio = $precio;
      $importe = number_format($importe, 2, '.', ',');
      $tempDescuento = number_format($tempDescuento, 2, '.', ',');
        $temMedida1= $this->getNomMedida($prod['presenta']);
      $precioDisminu = $precio / $prod['presenta_cnt'];

      $precioDisminu = number_format($precioDisminu, 2, '.', ',');
      $multi = $prod['cantidad'] * $prod['presenta_cnt'];
      $rowHTML = $rowHTML . "
              <tr>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$contador</td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>{$multi}  </td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'><strong>{$prod['descripcion']}</strong></td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$precioDisminu</td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$importe</td>
              </tr>
            ";
      $contador++;
    }
    foreach ($listaProd2 as $prod) {

      $precio =  $prod['monto'];
      $importe = $precio * $prod['cantidad'];
      //$subtotal = $subtotal + $importe;
      $total += $importe;
      $tempDescuento = 0;
      $importe -= $tempDescuento;
      $totalDescuento += $tempDescuento;

      $precio = number_format($precio, 2, '.', ',');
      $importe = number_format($importe, 2, '.', ',');
      $tempDescuento = number_format($tempDescuento, 2, '.', ',');

      $rowHTML = $rowHTML . "
              <tr>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>$contador</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'> </td>
                <td class='' style=' font-size: 11px; text-align: left;border-left: 1px solid #363636;'>{$prod['descripcion']}</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>{$prod['cantidad']}</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;'>{$prod['precio']}</td>
                
                
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$importe</td>
              </tr>
            ";
      $contador++;
    }
    $cntRowEE = 37;
    $rowHTMLTERT = "";
    for ($tert = 0; $tert < ($cntRowEE - $contador) - $menosRowsNumH; $tert++) {
      $rowHTMLTERT = $rowHTMLTERT . "
        <tr>
            <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; color: white'>.</td>
            <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td>
            <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td>
            <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636; '> </td>
            <td class='' style=' font-size: 9px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'></td>
          </tr>";
      
    }




    $totalLetras =   $formatter->toInvoice(number_format($total, 2, '.', ''), 2, $datoVenta["moneda"] == "1" ? "SOLES" : 'DOLARES');

    $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #ffffff ; float: right;'>

            <div style='padding: 5px;width: 100%; height: 100px; border: 2px solid #1e1e1e' class=''>
            <div style='margin-top:10px'></div>
            <span>RUC: {$datoEmpresa['ruc']}</span><br>
            <div style='margin-top: 10px'></div>
            <span><strong>$tipo_documeto_venta</strong></span><br>
            <div style='margin-top: 10px'></div>
            <span>Nro. $S_N </span>
            </div>
            </div>
            </div>";
    /**/
    $this->mpdf->WriteFixedPosHTML("<div><img style='width: 410px;height: 120px;' src='" .
      URL::to('files/logos/' . $datoEmpresa['logo']) . "'></div>", 15, 5, 110, 120);

    $this->mpdf->WriteFixedPosHTML($htmlCuadroHead, 0, 5, 195, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Central Telefónica: </strong> {$datoEmpresa['telefono']}</span>", 15, 38, 210, 130);

    /* $sql = "select * from ventas where id_venta=" . $venta;
    $datoVenta = $this->conexion->query($sql)->fetch_assoc(); */

    $datoSucursal = $this->conexion->query("SELECT * FROM sucursales WHERE cod_sucursal ='{$datoVenta['sucursal']}' AND empresa_id=" . $datoVenta['id_empresa'])->fetch_assoc();
    if ($datoVenta['sucursal'] == '1') {
      $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Dirección:</strong> <span style='font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 42, 120, 130);
    } else {
      if (is_null($datoSucursal)) {
        $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Dirección:</strong> <span style='font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 42, 120, 130);
      } else {
        $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Dirección:</strong> <span style='font-size: 10px'>{$datoSucursal['direccion']}</span></span>", 15, 42, 120, 130);
      }
    }

    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Email: </strong> info@titanicsac.com | Web: www.titanicsac.com</span>", 15, 46, 210, 130);


    $totalOpGratuita = number_format($totalOpGratuita, 2, '.', ',');
    $totalOpExonerada = number_format($totalOpExonerada, 2, '.', ',');
    $totalOpinafec = number_format($totalOpinafec, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
    $totalDescuento = number_format($totalDescuento, 2, '.', ',');
    $totalOpinafecta = number_format($totalOpinafecta, 2, '.', ',');
    $SC = number_format($SC, 2, '.', ',');
    $percepcion = number_format($percepcion, 2, '.', ',');
    $igv = $total / ($igv_venta_sel + 1) * $igv_venta_sel;
    $totalOpgravado = $total - $igv;
    $total = $total;
    $igv = number_format($igv, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');



    //$total = number_format($total, 2, '.', ',');

    $monedaVisual = $datoVenta["moneda"] == "1" ? "SOLES" : 'DOLAR';

    $html = "<div style='width: 1000%;padding-top: 150px; overflow: hidden;clear: both;'>
        <div style='width: 100%;border: 1px solid black'>
        <div style='width: 55%; float: left; '>
        
        <table style='width:100%'>
          <tr>
            <td style=' font-size: 11px;text-align: left'><strong>RUC/DNI:</strong></td>
            <td style=' font-size: 11px;'>{$resultC['documento']}</td>
          </tr>
          <tr>
            <td style=' font-size: 11px;text-align: left'><strong>CLIENTE:</strong></td>
            <td style=' font-size: 11px;'>{$resultC['datos']}</td>
          </tr>
          <tr>
            <td style=' font-size: 11px;text-align: left'><strong>DIRECCIÓN:</strong></td>
            <td style=' font-size: 11px;'>{$resultC['direccion']}</td>
          </tr>
          <tr>
            <td style=' font-size: 11px;text-align: left'><strong>NRO GUÍA:</strong></td>
            <td style=' font-size: 11px;'>$guiaRealionada</td>
          </tr>
         
        </table>
        </div>
        <div style='width: 45%; float: left'>
        <table style='width:100%'>
        
          <tr>
            <td style=' font-size: 11px;text-align: left'><strong>FECHA EMISIÓN:</strong></td>
            <td style=' font-size: 11px;'>$fecha_emision</td>
          </tr>
          <tr>
            <td style=' font-size: 11px;text-align: left'><strong>FECHA VENCIMIENTO:</strong></td>
            <td style=' font-size: 11px;'>$fecha_vencimiento</td>
          </tr>
          
           <tr>
            <td style=' font-size: 11px;text-align: left'><strong>MONEDA:</strong></td>
            <td style=' font-size: 11px;'>$monedaVisual</td>
          </tr>
          <tr>
            <td style=' font-size: 11px;text-align: left'><strong>PAGO:</strong></td>
            <td style=' font-size: 11px;'>$tipo_pagoC</td>
          </tr>
        </table>
        </div>
       <div style='text-align: center'><span style='font-size: 11px;'> $pagoData</span></div>
        </div>
        
        
        </div>
        $tabla_cuotas
        <div style='width: 100%; padding-top: 20px;'>
        <table style='width:100%;border-bottom: 1px solid #363636;border-collapse: collapse;'>
          <tr style='border-bottom: 1px solid #363636;border-collapse: collapse;'>
            <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 5%;'><strong>ITEM</strong></td>
            <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 8%;'><strong>CANTIDAD</strong></td>
            <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 65%;'><strong>DESCRIPCION</strong></td>
            <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>PRECIO</strong></td> 
            <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 12%;'><strong>SUB TOTAL</strong></td>
          </tr>
          $rowHTML
          $rowHTMLTERT
        </table>
        </div>



        
        ";
    // exit($html);
    if ($datoVenta['apli_igv'] == '0') {
      $igv = '0.00';
      $totalOpgravado = $total;
    }
    $dominio = DOMINIO;
    $footerHtml = "<div style='height: 10px;width: 100%; padding-bottom: 0px;font-size: 10px;border-right: 1px solid;border-bottom: 1px solid;border-left: 1px solid;'>. SON: | $totalLetras</div>
        
        
        <div style='width: 100%; height: 10px;margin-top: 3px;'>
        <div style='float: left; width: 20%;height: 10px '>
        $qrImage
        
        <div style='position: absolute; left: 80px; top: 90px;'></div>
        
        </div>
         <div style='width: 50%; padding-bottom: 5px;font-size: 12px; float: left; padding-top: 10px;'>
            <div style='width: 100%'></div>
            <div style='width: 95%; padding: 3px; font-size: 10px;height: 90px '>
            $hash_Doc
            Detalle:<br>
            Representación impresa de la $tipo_documeto_venta <br>Este documento puede ser validado en $dominio
            <br><br><b>Observaciones:</b> {$datoVenta['observacion']}
            </div> 
         </div>
         <div style='width: 30%;'>
         <table style='width: 100%;border-top: 1px solid #363636;border-bottom: 1px solid #363636;border-right: 1px solid #363636;border-collapse: collapse;'>
          
          <tr>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Total Op. Gravado:</td>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$totalOpgravado</td>
          </tr>
          <tr>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>IGV:</td>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$igv</td>
          </tr>
          
          <tr>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Total a Pagar</td>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$total</td>
          </tr>
          
        </table>
         </div>
         
        </div> ";
    $html .= $footerHtml;
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    
    /*$this->mpdf->WriteHTML($htmlDOM,\Mpdf\HTMLParserMode::HTML_BODY);*/
    if ($dist == 'I') {
      $this->mpdf->Output((is_string($nombreXML) ? $nombreXML : '') . ".pdf", $dist);
    } elseif ($dist == 'F') {
      $this->mpdf->Output(base64_decode((is_string($nombreXML) ? $nombreXML : '')), $dist);
    }
  }

  public function imprimirvoucher5_6cm($id)
  {
    $this->venta->setIdVenta($id);

    /* echo "<pre>"; */
    $this->mpdf  = new \Mpdf\Mpdf([
      'margin_bottom' => 5,
      'margin_top' => 7,
      'margin_left' => 4,
      'margin_right' => 4,
      'mode' => 'utf-8',
    ]);

    $this->venta->setIdVenta($id);
    $sql = "SELECT * FROM ventas where id_venta =$id ";
    $dataVenta = $this->conexion->query($sql)->fetch_assoc();
    $igv_venta_sel = $dataVenta['igv'];
    $sql = "SELECT * FROM empresas where id_empresa = '{$dataVenta['id_empresa']}' ";
    $dataEmpresa = $this->conexion->query($sql)->fetch_assoc();

    $sql = "SELECT * FROM clientes where id_cliente = '{$dataVenta['id_cliente']}' ";
    $dataCliente = $this->conexion->query($sql)->fetch_assoc();

    $sql = "SELECT pv.*,p.descripcion,p.codigo,p.medida FROM productos_ventas pv join productos p on p.id_producto = pv.id_producto where pv.id_venta =$id ";
    $dataProVenta = $this->conexion->query($sql);

    $sql = "SELECT * FROM ventas_servicios where id_venta =$id ";
    $dataServVenta = $this->conexion->query($sql);

    $guiaRealionada = '';
    $sql = "SELECT * FROM guia_remision where id_venta = $id";
    if ($rowGuia = $this->conexion->query($sql)->fetch_assoc()) {
      $guiaRealionada = $rowGuia["serie"] . '-' . Tools::numeroParaDocumento($rowGuia["numero"], 6);
    }

    $clienteDoc = $dataCliente['documento'];

    $rowsHTML = '';
    $contador = 1;

    $tipo_pagoC = $dataVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
    $tabla_cuotas = '';
    $menosRowsNumH = 0;

    $totalImporte = 0;

    /* if ($dataVenta["id_tipo_pago"] == '2') {
      $rowTempCuo = '';
      $sql = "SELECT * FROM dias_ventas WHERE id_venta='$id'";
      $resulTempCuo = $this->conexion->query($sql);
      $contadorCuota = 0;
      $menosRowsNumH = 10;
      foreach ($resulTempCuo as $cuotTemp) {
        $menosRowsNumH += 11;
        $menosRowsNumH++;
        $contadorCuota++;
        $tempNum = Tools::numeroParaDocumento($contadorCuota, 2);
        $tempFecha = Tools::formatoFechaVisual($cuotTemp['fecha']);
        $tempMonto = Tools::money($cuotTemp['monto']);
        $rowTempCuo .= "
            <tr>
                <td>Cuota $tempNum</td>
                <td>$tempFecha </td>
                <td>S/ $tempMonto</td>
            </tr>
            ";
      }
      $tabla_cuotas = '

        <div style="width: 100%; text-align: center;margin-top:3px">
        <strong><span style="font-size:10px">Cuotas de pago</span></strong>
        </div>
        <div style="width: 100%;">
        <table style="width:90%;margin:auto;display: block;text-align:center;font-size: 10px;">
                <thead>
                <tr>
                    <th>CUOTA</th>
                    <th>FECHA</th>
                    <th>MONTO</th>
                </tr>
                </thead>
                <tbody>
                    ' . $rowTempCuo . '
                </tbody>
        </table>
        </div>';
    } */

    $rowTamanioExtra = 0;

    foreach ($dataServVenta as $ser) {
      $totalM = $ser['cantidad'] * $ser['monto'];
      $totalImporte += $totalM;
      $motoFor = number_format($ser['monto'], 2, ".", "");
      $totalM = number_format($totalM, 2, ".", "");
      $cantidadss = number_format($ser['cantidad'], 0, "", "");
      $rowsHTML .= "<tr>
            <td style='font-size: 8px'>$cantidadss</td>
            <td style='font-size: 8px'>{$ser['descripcion']}</td>
            <td style='font-size: 8px'>$motoFor</td>
            <td style='font-size: 8px'>$totalM</td>
            </tr>";
      $contador++;
      $rowTamanioExtra += 23;
    }

    foreach ($dataProVenta as $ser) {
      $totalM = $ser['cantidad'] * $ser['precio'];
      $totalImporte += $totalM;
      $motoFor = number_format($ser['precio'], 2, ".", "");
      $totalM = number_format($totalM, 2, ".", "");
      $cantidadss = number_format($ser['cantidad'], 0, "", "");
      $rowsHTML .= "<tr>
            <td style='font-size: 8px'>$cantidadss  {$ser['medida']}</td>
            <td style='font-size: 8px'>{$ser['codigo']} | {$ser['descripcion']}</td>
            <td style='font-size: 8px'>$motoFor</td>
            <td style='font-size: 8px'>$totalM</td>
            </tr>";
      $contador++;
      $rowTamanioExtra += 23;
    }


    $sql = "SELECT * FROM ventas_sunat where id_venta = '$id' ";
    $qrImage = '';
    if ($rowVS = $this->conexion->query($sql)->fetch_assoc()) {
      $qrCode = new QrCode($rowVS["qr_data"]);
      $qrCode->setSize(150);
      $image = $qrCode->writeString(); //Salida en formato de texto
      $imageData = base64_encode($image);
      $qrImage = '<img style="width: 130px;" src="data:image/png;base64,' . $imageData . '">';
    }

    $data = '';
    $detalles = [];
    $fecha = date('d/m/Y', strtotime($dataVenta['fecha_emision']));
    $fechaVenc = date('d/m/Y', strtotime($dataVenta['fecha_vencimiento']));
    $vendedor = '';
    $cliente = $dataCliente['datos'];
    $telefono_ = '';
    $direccion_ = $dataVenta['direccion'];
    $puesto = '';
    $zona = '';

    $doc_S_N = $dataVenta["serie"] . "-" . Tools::numeroParaDocumento($dataVenta['numero'], 6);
    $formatter = new NumeroALetras;
    $totalLetras =   $formatter->toInvoice(number_format($totalImporte, 2, '.', ''), 2, $dataVenta["moneda"] == "1" ? "SOLES" : 'DOLARES');
    $totalIGVNumeros = number_format($totalImporte / ($igv_venta_sel + 1) * $igv_venta_sel, 2, '.', '');
    $totalNumeros = number_format($totalImporte, 2, '.', '');

    $nom_emp = $dataEmpresa['razon_social'];
    $telefono = $dataEmpresa['telefono'];
    $direccion = $dataEmpresa['direccion'];
    $propaganda = $dataEmpresa['propaganda'];

    $tipo_documeto_venta = "";

    if ($dataVenta['id_tido'] == 1) {
      $tipo_documeto_venta = "BOLETA DE VENTA ELECTRÓNICA";
    } elseif ($dataVenta['id_tido'] == 2) {
      $tipo_documeto_venta = "FACTURA DE VENTA ELECTRÓNICA";
    } elseif ($dataVenta['id_tido'] == 6) {
      $qrImage = '';
      $tipo_documeto_venta = "NOTA DE VENTA  ELECTRÓNICA";
      $rowTamanioExtra -= 40;
    }


    $this->mpdf->AddPageByArray([
      "orientation" => "P",
      "newformat" => [56, 190 + $rowTamanioExtra + $menosRowsNumH]
    ]);
    $dominio = DOMINIO;


    if ($dataVenta['apli_igv'] == '0') {
      $totalIGVNumeros = '0.00';
    }
    /*var_dump($totalIGVNumeros);
      die();*/
    $sql = "select * from ventas where id_venta=" . $id;
    $datoVenta = $this->conexion->query($sql)->fetch_assoc();
    $datoSucursal = $this->conexion->query("SELECT * FROM sucursales WHERE cod_sucursal ='{$datoVenta['sucursal']}' AND empresa_id=" . $datoVenta['id_empresa'])->fetch_assoc();
    if ($datoVenta['sucursal'] != '1') {
      if (!is_null($datoSucursal)) {
        $direccion_ = $datoSucursal['direccion'];
      }
    }


    $html = "
<div style='width: 100%'>
<table style='width:100%;margin-bottom: 10px'>
  <tr>
    <td align='center'>
      <img style=' max-width: 80%;' src='" . URL::to('files/logos/' . $dataEmpresa['logo']) . "'>
</td>
</tr>
</table>
    <div style='width: 100%;text-align: center'>
        <span style='font-size: 10px;font-weight: bold'>{$dataEmpresa["razon_social"]} </span>
    </div>
    <div style='width: 100%;text-align: center'>
        <span style='font-size: 9px'>RUC: {$dataEmpresa["ruc"]}</span>
    </div>
    <div style='width: 100%;text-align: center'>
        <span style='font-size: 9px'>$direccion</span>
    </div>
    <div style='width: 100%;text-align: center'>
        <span style='font-size: 9px'>$telefono</span>
    </div>
    
    <div style='width: 100%;text-align: center;margin-top: 10px;'>
    <span style='font-size: 9px;font-weight: bold'>$propaganda</span><br>
        <span style='font-size: 9px;font-weight: bold'>$tipo_documeto_venta</span><br>
        <span style='font-size: 9px;'>$doc_S_N</span>
        
    </div>
    <hr>
    <div style='width: 100%;text-align: center'>
        <table style='width:100%'>
          <tr>
            <td style='font-size: 8px;width: 25%'><strong>Fecha E:</strong></td>
            <td style='font-size: 8px;'>$fecha</td>
          </tr>
          <tr>
            <td style='font-size: 8px;width: 25%'><strong>Fecha V:</strong></td>
            <td style='font-size: 8px;'>$fechaVenc</td>
          </tr>
          <tr>
            <td style='font-size: 8px;width: 25%'><strong>RUC/DNI:</strong></td>
            <td style='font-size: 8px;'>$clienteDoc</td>
          </tr>
          <tr>
            <td style='font-size: 8px'><strong>Cliente:</strong></td>
            <td style='font-size: 8px'>$cliente</td>
          </tr>
          <tr>
            <td style='font-size: 7.5px'><strong>Dirección:</strong></td>
            <td style='font-size: 7.5px'>$direccion_</td>
          </tr>
           <tr>
            <td style='font-size: 7.5px'><strong>Pago:</strong></td>
            <td style='font-size: 7.5px'>$tipo_pagoC</td>
          </tr>
          <tr>
            <td style='font-size: 8px'><strong>Nro. Guia:</strong></td>
            <td style='font-size: 8px'>$guiaRealionada</td>
          </tr>
        </table>
    </div>
    
     <div style='width: 100%;text-align: center'>
        <span style='font-size: 10px;'>--------------------- Productos --------------------</span>
    </div>
    <div style='width: 100%;text-align: center'>
        <table style='width: 100%'>
            <tr>
                <td style='border-bottom:1px solid black;font-size: 8px'>CNT</td>
                <td style='border-bottom:1px solid black;font-size: 8px'>DESCRIPCION</td>
                <td style='border-bottom:1px solid black;font-size: 8px'>PR.U.</td>
                <td style='border-bottom:1px solid black;font-size: 8px;text-align: center'>IMPR.</td>
            </tr>
            $rowsHTML
            <tr>
                <td style='border-top:1px solid black; font-size: 8px;text-align: right' colspan='3'>IGV</td>
                <td style='border-top:1px solid black;font-size: 8px;text-align: center' >$totalIGVNumeros</td>
            </tr>
            <tr>
                <td style=' font-size: 8px;text-align: right' colspan='3'>Total</td>
                <td style='font-size: 8px;text-align: center' >$totalNumeros</td>
            </tr>
        </table>
    </div>
    <br>
    <div style='width: 100%;'>
        <span style='font-size: 8px'>SON: $totalLetras</span>
    </div>
    $tabla_cuotas
    <div style='width: 100%;'>
        <span style='font-size: 8px'><b>Observaciones:</b> {$dataVenta['observacion']}</span>
    </div>
    <br>
     <div style='width: 100%;text-align: center'>
        <span style='font-size: 8px'>Representación impresa de la $tipo_documeto_venta <br>Este documento puede ser validado en $dominio</span>
    </div>
    <div style='width: 100%;text-align: center'>
        <span style='font-size: 8px'>Gracias por su preferencia....</span>
    </div>
    <div style='width: 100%; '>
        $qrImage
    </div>
    
    
</div>
";
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output();
  }
  public function imprimirvoucher8cm($id)
  {
    $this->venta->setIdVenta($id);

    /* echo "<pre>"; */
    $this->mpdf  = new \Mpdf\Mpdf([
      'margin_bottom' => 5,
      'margin_top' => 10,
      'margin_left' => 4,
      'margin_right' => 4,
      'mode' => 'utf-8',
    ]);

    $this->venta->setIdVenta($id);
    $sql = "SELECT * FROM ventas where id_venta =$id ";
    $dataVenta = $this->conexion->query($sql)->fetch_assoc();

    $sql = "SELECT * FROM usuarios where usuario_id = '{$dataVenta["id_vendedor"]}' ";
    $cajero = $this->conexion->query($sql)->fetch_assoc();

    $sql = "SELECT u.nombres FROM cotizaciones c
    INNER JOIN usuarios u on u.usuario_id =  c.id_usuario
    where c.cotizacion_id = '{$dataVenta["id_coti"]}'";
    $vendor = $this->conexion->query($sql)->fetch_assoc();

    $trCajero = "";
    $trVendor = "";
    if ($cajero["nombres"]) {
      $trCajero = " <tr>
                <td style='font-size: 11px'><strong>Cajero:</strong></td>
                <td style='font-size: 11px'>{$cajero["nombres"]}</td>
              </tr>";
    }

    if ($vendor["nombres"]) {
      $trVendor = " <tr>
                <td style='font-size: 11px'><strong>Vendedor:</strong></td>
                <td style='font-size: 11px'>{$vendor["nombres"]}</td>
              </tr>";
    }

    $igv_venta_sel = $dataVenta['igv'];

    $sql = "SELECT * FROM empresas where id_empresa = '{$dataVenta['id_empresa']}' ";
    $dataEmpresa = $this->conexion->query($sql)->fetch_assoc();

    $sql = "SELECT * FROM clientes where id_cliente = '{$dataVenta['id_cliente']}' ";
    $dataCliente = $this->conexion->query($sql)->fetch_assoc();

    $sql = "SELECT pv.*,p.descripcion,p.codigo,p.medida FROM productos_ventas pv join productos p on p.id_producto = pv.id_producto where pv.id_venta =$id ";
    $dataProVenta = $this->conexion->query($sql);

    $sql = "SELECT * FROM ventas_servicios where id_venta =$id ";
    $dataServVenta = $this->conexion->query($sql);

    $guiaRealionada = '';
    $sql = "SELECT * FROM guia_remision where id_venta = $id";
    if ($rowGuia = $this->conexion->query($sql)->fetch_assoc()) {
      $guiaRealionada = $rowGuia["serie"] . '-' . Tools::numeroParaDocumento($rowGuia["numero"], 6);
    }

    $rowsHTML = '';
    $contador = 1;

    $tipo_pagoC = $dataVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
    $tabla_cuotas = '';
    $menosRowsNumH = 0;

    $totalImporte = 0;

    if ($dataVenta["id_tipo_pago"] == '2') {
      $rowTempCuo = '';
      $sql = "SELECT * FROM dias_ventas WHERE id_venta='$id'";
      $resulTempCuo = $this->conexion->query($sql);
      $contadorCuota = 0;
      $menosRowsNumH = 10;
      foreach ($resulTempCuo as $cuotTemp) {
        $menosRowsNumH += 10;
        $menosRowsNumH++;
        $contadorCuota++;
        $tempNum = Tools::numeroParaDocumento($contadorCuota, 2);
        $tempFecha = Tools::formatoFechaVisual($cuotTemp['fecha']);
        $tempMonto = Tools::money($cuotTemp['monto']);
        $rowTempCuo .= "
            <tr>
                <td>Cuota $tempNum</td>
                <td>$tempFecha </td>
                <td>S/ $tempMonto</td>
            </tr>
            ";
      }
/*       $tabla_cuotas = '

<div style="width: 100%; text-align: center;margin-top:3px;">
<strong><span  >Cuotas de pago</span></strong>
</div>
<div style="width: 100%;">
        <table style="width:90%;margin:auto;display: block;text-align:center;font-size: 10px;">
                <thead>
                <tr>
                    <th>CUOTA</th>
                    <th>FECHA</th>
                    <th>MONTO</th>
                </tr>
                </thead>
                <tbody>
                    ' . $rowTempCuo . '
                </tbody>
        </table>
        </div>'; */
    }

    $rowTamanioExtra = 0;

    foreach ($dataServVenta as $ser) {
      $totalM = $ser['cantidad'] * $ser['monto'];
      $totalImporte += $totalM;
      $motoFor = number_format($ser['monto'], 2, ".", "");
      $totalM = number_format($totalM, 2, ".", "");
      $cantidadss = number_format($ser['cantidad'], 0, "", "");
      $rowsHTML .= "<tr>
            <td style='font-size: 10px'>$cantidadss</td>
            <td style='font-size: 10px'>{$ser['descripcion']}</td>
            <td style='font-size: 10px'>$motoFor</td>
            <td style='font-size: 10px'>$totalM</td>
            </tr>";
      $contador++;
      $rowTamanioExtra += 10;
    }

    foreach ($dataProVenta as $ser) {
      $totalM = $ser['cantidad'] * $ser['precio'];
      $totalImporte += $totalM;
      $motoFor = number_format($ser['precio'], 2, ".", "");
      $totalM = number_format($totalM, 2, ".", "");
      $cantidadss = number_format($ser['cantidad'], 0, "", "");
      $rowsHTML .= "<tr>
            <td style='font-size: 10px'>$cantidadss  {$ser["medida"]}</td>
            <td style='font-size: 10px'>{$ser['codigo']} | {$ser['descripcion']}</td>
            <td style='font-size: 10px'>$motoFor</td>
            <td style='font-size: 10px'>$totalM</td>
            </tr>";
      $contador++;
      $rowTamanioExtra += 10;
    }


    $sql = "SELECT * FROM ventas_sunat where id_venta = '$id' ";
    $qrImage = '';
    if ($rowVS = $this->conexion->query($sql)->fetch_assoc()) {
      $qrCode = new QrCode($rowVS["qr_data"]);
      $qrCode->setSize(150);
      $image = $qrCode->writeString(); //Salida en formato de texto
      $imageData = base64_encode($image);
      $qrImage = '<img style="width: 130px;" src="data:image/png;base64,' . $imageData . '">';
    }

    $data = '';
    $detalles = [];
    $fecha = date('d/m/Y', strtotime($dataVenta['fecha_emision']));
    $fechaVenc = date('d/m/Y', strtotime($dataVenta['fecha_vencimiento']));
    $vendedor = '';
    $cliente = $dataCliente['datos'];

    $clienteDoc = $dataCliente['documento'];

    $telefono_ = '';
    $direccion_ = $dataVenta['direccion'];
    $puesto = '';
    $zona = '';

    $doc_S_N = $dataVenta["serie"] . "-" . Tools::numeroParaDocumento($dataVenta['numero'], 6);
    $formatter = new NumeroALetras;
    $totalLetras =   $formatter->toInvoice(number_format($totalImporte, 2, '.', ''), 2, $dataVenta["moneda"] == "1" ? "SOLES" : 'DOLARES');
    $totalIGVNumeros = number_format($totalImporte / ($igv_venta_sel + 1) * $igv_venta_sel, 2, '.', '');
    $totalNumeros = number_format($totalImporte, 2, '.', '');

    $nom_emp = $dataEmpresa['razon_social'];
    $telefono = $dataEmpresa['telefono'];
    $direccion = $dataEmpresa['direccion'];
    $propaganda = $dataEmpresa['propaganda'];
    $tipo_documeto_venta = "";

    if ($dataVenta['id_tido'] == 1) {
      $tipo_documeto_venta = "BOLETA DE VENTA ELECTRÓNICA";
    } elseif ($dataVenta['id_tido'] == 2) {
      $tipo_documeto_venta = "FACTURA DE VENTA ELECTRÓNICA";
    } elseif ($dataVenta['id_tido'] == 6) {
      $qrImage = '';
      $tipo_documeto_venta = "NOTA DE VENTA  ELECTRÓNICA";
      $rowTamanioExtra -= 30;
    }

    $this->mpdf->AddPageByArray([
      "orientation" => "P",
      "newformat" => [80, 240 + $rowTamanioExtra + $menosRowsNumH]
    ]);
    $dominio = DOMINIO;

    if ($dataVenta['apli_igv'] == '0') {
      $totalIGVNumeros = '0.00';
    }

    $sql = "select * from ventas where id_venta=" . $id;
    $datoVenta = $this->conexion->query($sql)->fetch_assoc();
    $datoSucursal = $this->conexion->query("SELECT * FROM sucursales WHERE cod_sucursal ='{$datoVenta['sucursal']}' AND empresa_id=" . $datoVenta['id_empresa'])->fetch_assoc();
    if ($datoVenta['sucursal'] != '1') {
      if (!is_null($datoSucursal)) {
        $direccion_ = $datoSucursal['direccion'];
      }
    }


    $html = "
<div style='width: 100%'>
<table style='width:100%;margin-bottom: 10px'>
  <tr>
    <td align='center'>
      <img style=' max-width: 85%;' src='" . URL::to('files/logos/' . $dataEmpresa['logo']) . "'>
</td>
</tr>
</table>
    <div style='width: 100%;text-align: center'>
        <span style='font-size: 13px;font-weight: bold'>{$dataEmpresa["razon_social"]} </span>
    </div>
    <div style='width: 100%;text-align: center'>
        <span style='font-size: 12px'>RUC: {$dataEmpresa["ruc"]}</span>
    </div>
    <div style='width: 100%;text-align: center'>
        <span style='font-size: 12px'>$direccion</span>
    </div>
    <div style='width: 100%;text-align: center'>
        <span style='font-size: 12px'>$telefono</span>
    </div>
    
    <div style='width: 100%;text-align: center;margin-top: 10px;'>
        <span style='font-size: 13px;font-weight: bold'>$propaganda</span><br>
        <span style='font-size: 13px;font-weight: bold'>$tipo_documeto_venta</span><br>
        <span style='font-size: 13px;'>$doc_S_N</span>
        
    </div>
    <hr>
    <div style='width: 100%;text-align: center'>
        <table style='width:100%'>
          <tr>
            <td style='font-size: 11px;width: 25%'><strong>Fecha E:</strong></td>
            <td style='font-size: 11px;'>$fecha</td>
          </tr>
          <tr>
            <td style='font-size: 11px;width: 25%'><strong>Fecha V:</strong></td>
            <td style='font-size: 11px;'>$fechaVenc</td>
          </tr>
           <tr>
            <td style='font-size: 11px;width: 25%'><strong>RUC/DNI:</strong></td>
            <td style='font-size: 11px;'>$clienteDoc</td>
          </tr>
          <tr>
            <td style='font-size: 11px'><strong>Cliente:</strong></td>
            <td style='font-size: 11px'>$cliente</td>
          </tr>
          <tr>
            <td style='font-size: 11px'><strong>Dirección:</strong></td>
            <td style='font-size: 11px'>$direccion_</td>
          </tr>
          <tr>
            <td style='font-size: 11px'><strong>Pago:</strong></td>
            <td style='font-size: 11px'>$tipo_pagoC</td>
          </tr>
          <tr>
            <td style='font-size: 11px'><strong>Nro. Guia:</strong></td>
            <td style='font-size: 11px'>$guiaRealionada</td>
          </tr>
          $trCajero
          $trVendor
        </table>
    </div>
    
     <div style='width: 100%;text-align: center'>
        <span style='font-size: 13px;'>---------------------- Productos -----------------------</span>
    </div>
    <div style='width: 100%;text-align: center'>
        <table style='width: 100%'>
            <tr>
                <td style='border-bottom:1px solid black;font-size: 11px'>CNT</td>
                <td style='border-bottom:1px solid black;font-size: 11px'>DESCRIPCION</td>
                <td style='border-bottom:1px solid black;font-size: 11px'>PR.U.</td>
                <td style='border-bottom:1px solid black;font-size: 11px;text-align: center'>IMPR.</td>
            </tr>
            $rowsHTML
            <tr>
                <td style='border-top:1px solid black; font-size: 11px;text-align: right' colspan='3'>IGV</td>
                <td style='border-top:1px solid black;font-size: 11px;text-align: center' >$totalIGVNumeros</td>
            </tr>
            <tr>
                <td style=' font-size: 11px;text-align: right' colspan='3'>Total</td>
                <td style='font-size: 11px;text-align: center' >$totalNumeros</td>
            </tr>
        </table>
    </div>
    <br>
    <div style='width: 100%;'>
        <span style='font-size: 11px'>SON: $totalLetras</span>
    </div>
    $tabla_cuotas
     <div style='width: 100%;'>
        <span style='font-size: 12px'><b>Observaciones:</b> {$dataVenta['observacion']}</span>
    </div>
    <br>
     <div style='width: 100%;text-align: center'>
        <span style='font-size: 12px'>Representación impresa de la $tipo_documeto_venta <br>Este documento puede ser validado en $dominio</span>
    </div>
    <div style='width: 100%;text-align: center'>
        <span style='font-size: 12px'>Gracias por su preferencia....</span>
    </div>
    <div style='width: 100%; '>
        $qrImage
    </div>
    
    
</div>
";
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output();
  }
}
