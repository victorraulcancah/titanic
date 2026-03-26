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


use Endroid\QrCode\QrCode;
use Luecano\NumeroALetras\NumeroALetras;

class ReportesVentaController extends Controller
{
  private $mpdf;
  private $conexion;

  public function __construct()
  {
    $this->mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
    $this->conexion = (new Conexion())->getConexion();
    $this->venta = new Venta();
  }

    public function comprobanteCotizacion($coti)
    {


        $listaProd1 = $this->conexion->query("SELECT pc.*,p.descripcion from productos_cotis pc 
            join productos p on p.id_producto = pc.id_producto where pc.id_coti='$coti'");



        $sql = "select * from cotizaciones where cotizacion_id=" . $coti;
        $datoVenta = $this->conexion->query($sql)->fetch_assoc();

        $datoEmpresa = $this->conexion->query("select * from empresas where id_empresa=" . $_SESSION['id_empresa'])->fetch_assoc();

        $resultC = $this->conexion->query("select * from clientes where id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
        $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : strlen($resultC['documento'] == 11 ? 'RUC' : '');

        $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha']);

        $tipo_pagoC =$datoVenta["id_tipo_pago"]=='1'?'CONTADO':'CREDITO';
        $tabla_cuotas='';

        $menosRowsNumH=0;

        if ($datoVenta["id_tipo_pago"]=='2'){
            $rowTempCuo='';
            $sql="SELECT * FROM cuotas_cotizacion WHERE id_coti='$coti'";
            $resulTempCuo= $this->conexion->query($sql);
            $contadorCuota=0;
            $menosRowsNumH=1;
            foreach ($resulTempCuo as $cuotTemp){
                $menosRowsNumH++;
                $contadorCuota++;
                $tempNum=Tools::numeroParaDocumento($contadorCuota,2);
                $tempFecha =Tools::formatoFechaVisual($cuotTemp['fecha']);
                $tempMonto = Tools::money($cuotTemp['monto']);
                $rowTempCuo.="
            <tr>
                <td>Cuota $tempNum</td>
                <td>$tempFecha </td>
                <td>S/ $tempMonto</td>
            </tr>
            ";
            }
            $tabla_cuotas='<div style="width: 100%;">
        <table style="width:50%;margin:auto;display: block;text-align:center;font-size: 12px;">
                <thead>
                <tr>
                    <th>CUOTA</th>
                    <th>FECHA</th>
                    <th>MONTO</th>
                </tr>
                </thead>
                <tbody>
                    '.$rowTempCuo.'
                </tbody>
        </table>
        </div>';
        }

        $formatter = new NumeroALetras;



        $qrImage ='';
        $hash_Doc='';


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

        $cntRowEE = 37;
        $rowHTMLTERT = "";
        for ($tert = 0; $tert < ($cntRowEE - $contador)-$menosRowsNumH; $tert++) {
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

        $this->mpdf->WriteFixedPosHTML("<img style='max-width: 300px;max-height: 85px' src='" . URL::to('files/logos/'.$datoEmpresa['logo']) . "'>", 15, 5, 150, 120);
        $this->mpdf->WriteFixedPosHTML($htmlCuadroHead, 0, 5, 195, 130);
        $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Central Telefonico: </strong> {$datoEmpresa['telefono']}</span>", 15, 27, 210, 130);
        $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Email: </strong> efacturacion@matrixsistem.com</span>", 15, 32, 210, 130);
        $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Direccion:</strong> <span style='font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 37, 120, 130);



        $totalOpGratuita = number_format($totalOpGratuita, 2, '.', ',');
        $totalOpExonerada = number_format($totalOpExonerada, 2, '.', ',');
        $totalOpinafec = number_format($totalOpinafec, 2, '.', ',');
        $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
        $totalDescuento = number_format($totalDescuento, 2, '.', ',');
        $totalOpinafecta = number_format($totalOpinafecta, 2, '.', ',');
        $SC = number_format($SC, 2, '.', ',');
        $percepcion = number_format($percepcion, 2, '.', ',');
        $igv = $total/ 1.18 * 0.18;
        $totalOpgravado = $total-$igv;
        $total = number_format($total, 2, '.', ',');
        $igv = number_format($igv, 2, '.', ',');
        $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');



        //$total = number_format($total, 2, '.', ',');


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
            <td style=' font-size: 11px;text-align: left'><strong>DIRECCION:</strong></td>
            <td style=' font-size: 11px;'>{$resultC['direccion']}</td>
          </tr>
        </table>
        </div>
        <div style='width: 45%; float: left'>
        <table style='width:100%'>
        
          <tr>
            <td style=' font-size: 11px;text-align: left'><strong>FECHA:</strong></td>
            <td style=' font-size: 11px;'>$fecha_emision</td>
          </tr>
          
           <tr>
            <td style=' font-size: 11px;text-align: left'><strong>MONEDA:</strong></td>
            <td style=' font-size: 11px;'>SOlES</td>
          </tr>
          <tr>
            <td style=' font-size: 11px;text-align: left'><strong>PAGO:</strong></td>
            <td style=' font-size: 11px;'>$tipo_pagoC</td>
          </tr>
        </table>
        </div>
        </div>
        
        
        </div>
        $tabla_cuotas
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
        $dominio ='';
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
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Total a Pagar</td>
            <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$total</td>
          </tr>
          
        </table>
            </div>
        </div> 
        ");
        /*$this->mpdf->WriteHTML($htmlDOM,\Mpdf\HTMLParserMode::HTML_BODY);*/
        $this->mpdf->Output("Cotizacion{$datoVenta['numero']}.pdf", 'I');
    }

  public function comprobanteNotaE($venta,$nombreXML='')
    {

        ///$ventaSunat = $this->conexion->query("SELECT * FROM ventas_sunat WHERE id_venta=" . $venta)->fetch_assoc();

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
        $qrImage ='';
        $hash_Doc='';
        if ($rowVS = $this->conexion->query($sql)->fetch_assoc()){
            $hash_Doc="HASH: ".$rowVS['hash']."<br>";
            $qrCode = new QrCode($rowVS["qr_data"]);
            $qrCode->setSize(150);
            $image = $qrCode->writeString(); //Salida en formato de texto
            $imageData = base64_encode($image);
            $qrImage = '<img style="width: 130px;" src="data:image/png;base64,' . $imageData . '">';
        }

        $tipo_documeto_venta = "";

        if ($datoVenta['tido']==3){
            $tipo_documeto_venta = "NOTA DE CREDITO ELECTRÓNICA";
        }elseif ($datoVenta['tido']==4){
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
        $listaProd1=json_decode($datoVenta['productos'],true);

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
        $dominio =DOMINIO;

        $this->mpdf->WriteFixedPosHTML("<img style='max-width: 300px;max-height: 85px' src='" . URL::to('files/logos/'.$datoEmpresa['logo']) . "'>", 15, 5, 150, 120);
        $this->mpdf->WriteFixedPosHTML($htmlCuadroHead, 0, 5, 195, 130);
        $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Central Telefonico: </strong> {$datoEmpresa['telefono']}</span>", 15, 27, 210, 130);
        $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Email: </strong> efacturacion@matrixsistem.com</span>", 15, 32, 210, 130);
        $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Direccion:</strong> <span style='font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 37, 120, 130);



        $totalOpGratuita = number_format($totalOpGratuita, 2, '.', ',');
        $totalOpExonerada = number_format($totalOpExonerada, 2, '.', ',');
        $totalOpinafec = number_format($totalOpinafec, 2, '.', ',');
        $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
        $totalDescuento = number_format($totalDescuento, 2, '.', ',');
        $totalOpinafecta = number_format($totalOpinafecta, 2, '.', ',');
        $SC = number_format($SC, 2, '.', ',');
        $percepcion = number_format($percepcion, 2, '.', ',');
        $igv = $total/ 1.18 * 0.18;
        $totalOpgravado = $total-$igv;
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
            <td style=' font-size: 11px;text-align: left'><strong>DIRECCION:</strong></td>
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
            <td style=' font-size: 11px;'>SOlES</td>
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
        $this->mpdf->Output($nombreXML.".pdf", 'I');
    }

  public function guiaRemision($guia,$nombreXML)
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

    $this->mpdf->WriteFixedPosHTML("<img style='max-width: 300px;max-height: 85px' src='" . URL::to('files/logos/'.$datoEmpresa['logo']) . "'>", 15, 5, 150, 120);
    $this->mpdf->WriteFixedPosHTML($htmlCuadroHead, 0, 5, 195, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px;margin: 1pt 2pt 3pt;'><strong>Central Telefonico: </strong> 203-1300</span>", 15, 27, 210, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px;margin: 1pt 2pt 3pt;'><strong>Email: </strong> efacturacion@quokka.com</span>", 15, 32, 210, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px;margin: 1pt 2pt 3pt;'><strong>Direccion:</strong> <span style='font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 37, 120, 130);

    $rowHTML = '';
    $rowHTMLTERT = '';
    $conradorRow = 1;
    foreach ($listaProductos as $itemProd) {
      $rowHTML = "
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
      $this->mpdf->Output($nombreXML.".pdf", 'I');
  }

  public function comprobanteVentaMa4($venta,$nombreXML='-')
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

      $guiaRealionada = '';

      $listaProd1 = $this->conexion->query("SELECT productos_ventas.*,p.descripcion FROM productos_ventas join productos p on p.id_producto = productos_ventas.id_producto WHERE id_venta=" . $venta);
      $listaProd2 = $this->conexion->query("SELECT * FROM ventas_servicios WHERE id_venta=" . $venta);
      $ventaSunat = $this->conexion->query("SELECT * FROM ventas_sunat WHERE id_venta=" . $venta)->fetch_assoc();

      $sql = "SELECT * FROM guia_remision where id_venta = $venta";
      if ($rowGuia = $this->conexion->query($sql)->fetch_assoc()){
          $guiaRealionada = $rowGuia["serie"].'-'.Tools::numeroParaDocumento($rowGuia["numero"],6);
      }

      $sql = "select * from ventas where id_venta=" . $venta;
      $datoVenta = $this->conexion->query($sql)->fetch_assoc();
      $datoEmpresa = $this->conexion->query("select * from empresas where id_empresa=" . $datoVenta['id_empresa'])->fetch_assoc();

      $S_N = $datoVenta['serie'] . '-' . Tools::numeroParaDocumento($datoVenta['numero'], 6);
      $tipoDocNom = $datoVenta['id_tido'] == 1 ? 'BOLETA' : 'FACTURA';
      $resultC = $this->conexion->query("select * from clientes where id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
      $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : strlen($resultC['documento'] == 11 ? 'RUC' : '');
      $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha_emision']);
      $fecha_vencimiento = Tools::formatoFechaVisual($datoVenta['fecha_vencimiento']);

      $tipo_pagoC =$datoVenta["id_tipo_pago"]=='1'?'CONTADO':'CREDITO';
      $tabla_cuotas='';

      $menosRowsNumH=0;

      if ($datoVenta["id_tipo_pago"]=='2'){
          $rowTempCuo='';
          $sql="SELECT * FROM dias_ventas WHERE id_venta='$venta'";
          $resulTempCuo= $this->conexion->query($sql);
          $contadorCuota=0;
          $menosRowsNumH=1;
          foreach ($resulTempCuo as $cuotTemp){
              $menosRowsNumH++;
              $contadorCuota++;
              $tempNum=Tools::numeroParaDocumento($contadorCuota,2);
              $tempFecha =Tools::formatoFechaVisual($cuotTemp['fecha']);
              $tempMonto = Tools::money($cuotTemp['monto']);
              $rowTempCuo.="
            <tr>
                <td>Cuota $tempNum</td>
                <td>$tempFecha </td>
                <td>S/ $tempMonto</td>
            </tr>
            ";
          }
          $tabla_cuotas='<div style="width: 100%;">
        <table style="width:50%;margin:auto;display: block;text-align:center;font-size: 10px;">
                <thead>
                <tr>
                    <th>CUOTA</th>
                    <th>FECHA</th>
                    <th>MONTO</th>
                </tr>
                </thead>
                <tbody>
                    '.$rowTempCuo.'
                </tbody>
        </table>
        </div>';
      }

      $formatter = new NumeroALetras;


      $sql = "SELECT * FROM ventas_sunat where id_venta = '$venta' ";
      $qrImage ='';
      $hash_Doc='';
      if ($rowVS = $this->conexion->query($sql)->fetch_assoc()){
          $hash_Doc="HASH: ".$rowVS['hash']."<br>";
          $qrCode = new QrCode($rowVS["qr_data"]);
          $qrCode->setSize(150);
          $image = $qrCode->writeString(); //Salida en formato de texto
          $imageData = base64_encode($image);
          $qrImage = '<img style="width: 100px;" src="data:image/png;base64,' . $imageData . '">';
      }

      $tipo_documeto_venta = "";

      if ($datoVenta['id_tido']==1){
          $tipo_documeto_venta = "BOLETA DE VENTA ELECTRÓNICA";
      }elseif ($datoVenta['id_tido']==2){
          $tipo_documeto_venta = "FACTURA DE VENTA ELECTRÓNICA";
      }elseif ($datoVenta['id_tido']==6){
          $qrImage ='';
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

          $precio = number_format($precio, 2, '.', ',');
          $importe = number_format($importe, 2, '.', ',');
          $tempDescuento = number_format($tempDescuento, 2, '.', ',');

          $rowHTML = $rowHTML . "
              <tr>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$contador</td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>{$prod['cantidad']}</td>
                <td class='' style=' font-size: 10px; text-align: left;border-left: 1px solid #363636;'>{$prod['descripcion']}</td>
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
                <td class='' style=' font-size: 10px; text-align: left;border-left: 1px solid #363636;'>{$prod['descripcion']}</td>
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$precio</td>
                
                
                <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$importe</td>
              </tr>
            ";
          $contador++;
      }
      $cntRowEE = 11;
      $rowHTMLTERT = "";
      for ($tert = 0; $tert < ($cntRowEE - $contador)-$menosRowsNumH; $tert++) {
          $rowHTMLTERT = $rowHTMLTERT . " <tr>
        <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636; color: white'>.</td>
        <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636; '> </td>
        <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636; '> </td> 
        <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636; '> </td>
        
        
        <td class='' style=' font-size: 10px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'> </td>
      </tr>";
      }




      $totalLetras =   $formatter->toInvoice(number_format($total, 2, '.', ''), 2, 'SOLES');

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

      $this->mpdf->WriteFixedPosHTML("<img style='max-width: 300px;max-height: 70px' src='" .
          URL::to('files/logos/'.$datoEmpresa['logo']) . "'>", 15, 5, 150, 120);

      $this->mpdf->WriteFixedPosHTML($htmlCuadroHead, 0, 5, 195, 130);
      $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Central Telefonico: </strong> {$datoEmpresa['telefono']}</span>", 15, 25, 210, 130);
      $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Email: </strong> efacturacion@matrixsistem.com</span>", 15, 30, 210, 130);
      $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Direccion:</strong> <span style='font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 35, 120, 130);



      $totalOpGratuita = number_format($totalOpGratuita, 2, '.', ',');
      $totalOpExonerada = number_format($totalOpExonerada, 2, '.', ',');
      $totalOpinafec = number_format($totalOpinafec, 2, '.', ',');
      $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
      $totalDescuento = number_format($totalDescuento, 2, '.', ',');
      $totalOpinafecta = number_format($totalOpinafecta, 2, '.', ',');
      $SC = number_format($SC, 2, '.', ',');
      $percepcion = number_format($percepcion, 2, '.', ',');
      $igv = $total/ 1.18 * 0.18;
      $totalOpgravado = $total-$igv;
      $total = number_format($total, 2, '.', ',');
      $igv = number_format($igv, 2, '.', ',');
      $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');



      //$total = number_format($total, 2, '.', ',');


      $html = "<div style='width: 100%;padding-top: 90px; overflow: hidden;clear: both;'>
        <div style='width: 100%;border: 1px solid black;'>
        <div style='width: 55%; float: left; '>
        
        <table style='width:100%'>
          <tr>
            <td style=' font-size: 10px;text-align: left'><strong>DOCUMENTO:</strong></td>
            <td style=' font-size: 10px;'>{$resultC['documento']}</td>
          </tr>
          <tr>
            <td style=' font-size: 10px;text-align: left'><strong>CLIENTE:</strong></td>
            <td style=' font-size: 10px;'>{$resultC['datos']}</td>
          </tr>
          <tr>
            <td style=' font-size: 10px;text-align: left'><strong>DIRECCION:</strong></td>
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
            <td style=' font-size: 10px;'>SOlES</td>
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
            <td style=' font-size: 10px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>DESCRIPCION</strong></td>
            <td style=' font-size: 10px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>PRECIO U.</strong></td> 
            <td style=' font-size: 10px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>IMPORTE</strong></td>
            
          </tr>
          $rowHTML
          $rowHTMLTERT
             
         
        
        </table>
        </div>
        
        <div style='border: 1px solid black;'>
         <h3><b>Observaciones</b></h3>
        </div>
        
        ";
      $dominio =DOMINIO;
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

          $this->mpdf->Output($nombreXML.".pdf", 'I');


  }
  public function comprobanteVenta($venta,$nombreXML='-')
  {
      $this->comprobanteVentaGen("I",$venta,$nombreXML?$nombreXML:'-');

  }

  public function comprobanteVentaBinario($venta,$nombreXML='-'){
      $this->comprobanteVentaGen("F",$venta,$nombreXML?$nombreXML:'-');
  }

  private  function comprobanteVentaGen($dist,$venta,$nombreXML )
  {

      $guiaRealionada = '';

    $listaProd1 = $this->conexion->query("SELECT productos_ventas.*,p.descripcion FROM productos_ventas join productos p on p.id_producto = productos_ventas.id_producto WHERE id_venta=" . $venta);
    $listaProd2 = $this->conexion->query("SELECT * FROM ventas_servicios WHERE id_venta=" . $venta);
    $ventaSunat = $this->conexion->query("SELECT * FROM ventas_sunat WHERE id_venta=" . $venta)->fetch_assoc();

    $sql = "SELECT * FROM guia_remision where id_venta = $venta";
    if ($rowGuia = $this->conexion->query($sql)->fetch_assoc()){
        $guiaRealionada = $rowGuia["serie"].'-'.Tools::numeroParaDocumento($rowGuia["numero"],6);
    }

    $sql = "select * from ventas where id_venta=" . $venta;
    $datoVenta = $this->conexion->query($sql)->fetch_assoc();
    $datoEmpresa = $this->conexion->query("select * from empresas where id_empresa=" . $datoVenta['id_empresa'])->fetch_assoc();

    $S_N = $datoVenta['serie'] . '-' . Tools::numeroParaDocumento($datoVenta['numero'], 6);
    $tipoDocNom = $datoVenta['id_tido'] == 1 ? 'BOLETA' : 'FACTURA';
    $resultC = $this->conexion->query("select * from clientes where id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
    $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : strlen($resultC['documento'] == 11 ? 'RUC' : '');
    $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha_emision']);
    $fecha_vencimiento = Tools::formatoFechaVisual($datoVenta['fecha_vencimiento']);

    $tipo_pagoC =$datoVenta["id_tipo_pago"]=='1'?'CONTADO':'CREDITO';
    $tabla_cuotas='';

    $menosRowsNumH=0;

    $colorBorder="#000000";
    if (strlen($datoEmpresa['configuracion'])>0){
        $configuracionDt = json_decode($datoEmpresa['configuracion'],true);
        if (isset($configuracionDt["comprobante"])){
            if (isset($configuracionDt["comprobante"]["bordercolor"])){
                $colorBorder=$configuracionDt["comprobante"]["bordercolor"];
            }
        }
    }


    if ($datoVenta["id_tipo_pago"]=='2'){
        $rowTempCuo='';
        $sql="SELECT * FROM dias_ventas WHERE id_venta='$venta'";
        $resulTempCuo= $this->conexion->query($sql);
        $contadorCuota=0;
        $menosRowsNumH=1;
        foreach ($resulTempCuo as $cuotTemp){
            $menosRowsNumH++;
            $contadorCuota++;
            $tempNum=Tools::numeroParaDocumento($contadorCuota,2);
            $tempFecha =Tools::formatoFechaVisual($cuotTemp['fecha']);
            $tempMonto = Tools::money($cuotTemp['monto']);
            $rowTempCuo.="
            <tr>
                <td>Cuota $tempNum</td>
                <td>$tempFecha </td>
                <td>S/ $tempMonto</td>
            </tr>
            ";
        }
        $tabla_cuotas='<div style="width: 100%;">
        <table style="width:50%;margin:auto;display: block;text-align:center;font-size: 12px;">
                <thead>
                <tr>
                    <th>CUOTA</th>
                    <th>FECHA</th>
                    <th>MONTO</th>
                </tr>
                </thead>
                <tbody>
                    '.$rowTempCuo.'
                </tbody>
        </table>
        </div>';
    }

    $formatter = new NumeroALetras;


      $sql = "SELECT * FROM ventas_sunat where id_venta = '$venta' ";
      $qrImage ='';
      $hash_Doc='';
      if ($rowVS = $this->conexion->query($sql)->fetch_assoc()){
          $hash_Doc="HASH: ".$rowVS['hash']."<br>";
          $qrCode = new QrCode($rowVS["qr_data"]);
          $qrCode->setSize(150);
          $image = $qrCode->writeString(); //Salida en formato de texto
          $imageData = base64_encode($image);
          $qrImage = '<img style="width: 130px;" src="data:image/png;base64,' . $imageData . '">';
      }

      $tipo_documeto_venta = "";

      if ($datoVenta['id_tido']==1){
          $tipo_documeto_venta = "BOLETA DE VENTA ELECTRÓNICA";
      }elseif ($datoVenta['id_tido']==2){
          $tipo_documeto_venta = "FACTURA DE VENTA ELECTRÓNICA";
      }elseif ($datoVenta['id_tido']==6){
          $qrImage ='';
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

      $precio = number_format($precio, 2, '.', ',');
      $importe = number_format($importe, 2, '.', ',');
      $tempDescuento = number_format($tempDescuento, 2, '.', ',');

      $rowHTML = $rowHTML . "
              <tr>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder;'>$contador</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder;'>{$prod['cantidad']}</td>
                <td class='' style=' font-size: 11px; text-align: left;border-left: 1px solid $colorBorder;'>{$prod['descripcion']}</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder;'>$precio</td>
                 
                
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder;border-right: 1px solid $colorBorder;'>$importe</td>
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
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder;'>$contador</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder;'>{$prod['cantidad']}</td>
                <td class='' style=' font-size: 11px; text-align: left;border-left: 1px solid $colorBorder;'>{$prod['descripcion']}</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder;'>$precio</td>
                
                
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder;border-right: 1px solid $colorBorder;'>$importe</td>
              </tr>
            ";
      $contador++;
    }
    $cntRowEE = 37;
    $rowHTMLTERT = "";
    for ($tert = 0; $tert < ($cntRowEE - $contador)-$menosRowsNumH; $tert++) {
      $rowHTMLTERT = $rowHTMLTERT . " <tr>
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder; color: white'>.</td>
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder; '> </td>
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder; '> </td> 
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder; '> </td>
        
        
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder;border-right: 1px solid $colorBorder;'> </td>
      </tr>";
    }




    $totalLetras =   $formatter->toInvoice(number_format($total, 2, '.', ''), 2, 'SOLES');

    $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #ffffff ; float: right;'>

            <div style='padding: 5px;width: 100%; height: 100px; border: 2px solid $colorBorder' class=''>
            <div style='margin-top:10px'></div>$colorBorder
            <span>RUC: {$datoEmpresa['ruc']}</span><br>
            <div style='margin-top: 10px'></div>
            <span><strong>$tipo_documeto_venta</strong></span><br>
            <div style='margin-top: 10px'></div>
            <span>Nro. $S_N </span>
            </div>
            </div>
            </div>";

    $this->mpdf->WriteFixedPosHTML("<img style='max-width: 300px;max-height: 85px' src='" .
        URL::to('files/logos/'.$datoEmpresa['logo']) . "'>", 15, 5, 150, 120);

    $this->mpdf->WriteFixedPosHTML($htmlCuadroHead, 0, 5, 195, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Central Telefonico: </strong> {$datoEmpresa['telefono']}</span>", 15, 27, 210, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Email: </strong> efacturacion@matrixsistem.com</span>", 15, 32, 210, 130);
    $this->mpdf->WriteFixedPosHTML("<span style=' font-size: 12px'><strong>Direccion:</strong> <span style='font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 37, 120, 130);



    $totalOpGratuita = number_format($totalOpGratuita, 2, '.', ',');
    $totalOpExonerada = number_format($totalOpExonerada, 2, '.', ',');
    $totalOpinafec = number_format($totalOpinafec, 2, '.', ',');
    $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');
    $totalDescuento = number_format($totalDescuento, 2, '.', ',');
    $totalOpinafecta = number_format($totalOpinafecta, 2, '.', ',');
    $SC = number_format($SC, 2, '.', ',');
    $percepcion = number_format($percepcion, 2, '.', ',');
      $igv = $total/ 1.18 * 0.18;
      $totalOpgravado = $total-$igv;
      $total = number_format($total, 2, '.', ',');
      $igv = number_format($igv, 2, '.', ',');
      $totalOpgravado = number_format($totalOpgravado, 2, '.', ',');



      //$total = number_format($total, 2, '.', ',');


    $html = "<div style='width: 1000%;padding-top: 110px; overflow: hidden;clear: both;'>
        <div style='width: 100%;border: 1px solid $colorBorder'>
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
            <td style=' font-size: 11px;text-align: left'><strong>DIRECCION:</strong></td>
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
            <td style=' font-size: 11px;'>SOlES</td>
          </tr>
          <tr>
            <td style=' font-size: 11px;text-align: left'><strong>PAGO:</strong></td>
            <td style=' font-size: 11px;'>$tipo_pagoC</td>
          </tr>
        </table>
        </div>
        </div>
        
        
        </div>
        $tabla_cuotas
        <div style='width: 100%; padding-top: 20px;'>
        <table style='width:100%;border-bottom: 1px solid $colorBorder; border-collapse: collapse;'>
            <tr style='border-bottom: 1px solid $colorBorder;border-collapse: collapse;'>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid $colorBorder;border-collapse: collapse;'><strong>ITEM</strong></td>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid $colorBorder;border-collapse: collapse;'><strong>CANT</strong></td>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid $colorBorder;border-collapse: collapse;'><strong>DESCRIPCION</strong></td>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid $colorBorder;border-collapse: collapse;'><strong>PRECIO U.</strong></td> 
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid $colorBorder;border-collapse: collapse;'><strong>IMPORTE</strong></td>
            
          </tr>
          $rowHTML
          $rowHTMLTERT
              <tr>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder;border-bottom: 1px solid $colorBorder;color: white'>.</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder;border-bottom: 1px solid $colorBorder;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder;border-bottom: 1px solid $colorBorder;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder;border-bottom: 1px solid $colorBorder;'> </td> 
                
                
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid $colorBorder;border-right: 1px solid $colorBorder;border-bottom: 1px solid $colorBorder'> </td>
              </tr>
         
        
        </table>
        </div>
        <div style='border: 1px solid $colorBorder; padding: 3px;margin-top:5px;'>
        <b>Observiones:</b> 
         {$datoVenta['observacion']} 
</div>
        ";
      $dominio =DOMINIO;
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->SetHTMLFooter("
        
        <div style='height: 10px;width: 100%; padding-bottom: 0px;font-size: 10px;border: 1px solid $colorBorder;'>. SON: | $totalLetras</div>
        
        
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
         <table style='width: 100%;border-top: 1px solid $colorBorder;
         border-bottom: 1px solid $colorBorder;border-right: 1px solid $colorBorder;border-collapse: collapse;'>
          
          <tr>
            <td style='border-left: 1px solid $colorBorder;border-collapse: collapse; font-size: 12px; text-align: right'>Total Op. Gravado:</td>
            <td style='border-left: 1px solid $colorBorder;border-collapse: collapse; font-size: 12px;  text-align: right' >$totalOpgravado</td>
          </tr>
          <tr>
            <td style='border-left: 1px solid $colorBorder;border-collapse: collapse; font-size: 12px; text-align: right'>IGV:</td>
            <td style='border-left: 1px solid $colorBorder;border-collapse: collapse; font-size: 12px;  text-align: right' >$igv</td>
          </tr>
          
          <tr>
            <td style='border-left: 1px solid $colorBorder;border-collapse: collapse; font-size: 12px; text-align: right'>Total a Pagar</td>
            <td style='border-left: 1px solid $colorBorder;border-collapse: collapse; font-size: 12px;  text-align: right' >$total</td>
          </tr>
          
        </table>
            </div>
        </div> 
        ");
    /*$this->mpdf->WriteHTML($htmlDOM,\Mpdf\HTMLParserMode::HTML_BODY);*/
      if ($dist=='I'){
        $this->mpdf->Output($nombreXML.".pdf", $dist);
      }elseif($dist=='F'){
        $this->mpdf->Output(base64_decode($nombreXML), $dist);
      }

  }

  public function imprimirvoucher5_6cm($id)
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

    $sql = "SELECT * FROM empresas where id_empresa = '{$dataVenta['id_empresa']}' ";
    $dataEmpresa = $this->conexion->query($sql)->fetch_assoc();

    $sql = "SELECT * FROM clientes where id_cliente = '{$dataVenta['id_cliente']}' ";
    $dataCliente = $this->conexion->query($sql)->fetch_assoc();

      $sql = "SELECT pv.*,p.descripcion FROM productos_ventas pv join productos p on p.id_producto = pv.id_producto where pv.id_venta =$id ";
      $dataProVenta = $this->conexion->query($sql);

      $sql = "SELECT * FROM ventas_servicios where id_venta =$id ";
      $dataServVenta = $this->conexion->query($sql);

      $rowsHTML ='';
      $contador = 1;

      $tipo_pagoC =$dataVenta["id_tipo_pago"]=='1'?'CONTADO':'CREDITO';
      $tabla_cuotas='';
      $menosRowsNumH=0;

      if ($dataVenta["id_tipo_pago"]=='2'){
          $rowTempCuo='';
          $sql="SELECT * FROM dias_ventas WHERE id_venta='$id'";
          $resulTempCuo= $this->conexion->query($sql);
          $contadorCuota=0;
          $menosRowsNumH=10;
          foreach ($resulTempCuo as $cuotTemp){
              $menosRowsNumH+=11;
              $menosRowsNumH++;
              $contadorCuota++;
              $tempNum=Tools::numeroParaDocumento($contadorCuota,2);
              $tempFecha =Tools::formatoFechaVisual($cuotTemp['fecha']);
              $tempMonto = Tools::money($cuotTemp['monto']);
              $rowTempCuo.="
            <tr>
                <td>Cuota $tempNum</td>
                <td>$tempFecha </td>
                <td>S/ $tempMonto</td>
            </tr>
            ";
          }
          $tabla_cuotas='

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
                    '.$rowTempCuo.'
                </tbody>
        </table>
        </div>';
      }

      $rowTamanioExtra = 0;

      foreach ($dataServVenta as $ser){
          $totalM = $ser['cantidad']*$ser['monto'];
          $motoFor = number_format($ser['monto'],2,".","");
          $totalM = number_format($totalM,2,".","");
          $cantidadss= number_format($ser['cantidad'],0,"","");
          $rowsHTML .="<tr>
            <td style='font-size: 8px'>$cantidadss</td>
            <td style='font-size: 8px'>{$ser['descripcion']}</td>
            <td style='font-size: 8px'>$motoFor</td>
            <td style='font-size: 8px'>$totalM</td>
            </tr>";
          $contador++;
          $rowTamanioExtra +=23;
      }

      foreach ($dataProVenta as $ser){
          $totalM = $ser['cantidad']*$ser['precio'];
          $motoFor = number_format($ser['precio'],2,".","");
          $totalM = number_format($totalM,2,".","");
          $cantidadss= number_format($ser['cantidad'],0,"","");
          $rowsHTML .="<tr>
            <td style='font-size: 8px'>$cantidadss</td>
            <td style='font-size: 8px'>{$ser['descripcion']}</td>
            <td style='font-size: 8px'>$motoFor</td>
            <td style='font-size: 8px'>$totalM</td>
            </tr>";
          $contador++;
          $rowTamanioExtra +=23;
      }


      $sql = "SELECT * FROM ventas_sunat where id_venta = '$id' ";
      $qrImage ='';
     if ($rowVS = $this->conexion->query($sql)->fetch_assoc()){
         $qrCode = new QrCode($rowVS["qr_data"]);
         $qrCode->setSize(150);
         $image = $qrCode->writeString(); //Salida en formato de texto
         $imageData = base64_encode($image);
         $qrImage = '<img style="width: 130px;" src="data:image/png;base64,' . $imageData . '">';
     }

    $data = '';
    $detalles = [];
      $fecha=date('d/m/Y',strtotime($dataVenta['fecha_emision']));
      $fechaVenc=date('d/m/Y',strtotime($dataVenta['fecha_vencimiento']));
      $vendedor='';
      $cliente=$dataCliente['datos'];
      $telefono_='';
      $direccion_=$dataVenta['direccion'];
      $puesto='';
      $zona='';

      $doc_S_N= $dataVenta["serie"]."-".Tools::numeroParaDocumento($dataVenta['numero'],6);
      $formatter = new NumeroALetras;
      $totalLetras =   $formatter->toInvoice(number_format($dataVenta['total'], 2, '.', ''), 2, 'SOLES');
      $totalIGVNumeros = number_format($dataVenta['total']/ 1.18 * 0.18, 2, '.', '');
      $totalNumeros = number_format($dataVenta['total'], 2, '.', '');

      $nom_emp=$dataEmpresa['razon_social'];
      $telefono=$dataEmpresa['telefono'];
      $direccion=$dataEmpresa['direccion'];

      $tipo_documeto_venta = "";

      if ($dataVenta['id_tido']==1){
          $tipo_documeto_venta = "BOLETA DE VENTA ELECTRÓNICA";
      }elseif ($dataVenta['id_tido']==2){
          $tipo_documeto_venta = "FACTURA DE VENTA ELECTRÓNICA";
      }elseif ($dataVenta['id_tido']==6){
          $qrImage ='';
          $tipo_documeto_venta = "NOTA DE VENTA  ELECTRÓNICA";
          $rowTamanioExtra-=40;
      }


      $this->mpdf->AddPageByArray([
          "orientation"=>"P",
          "newformat"=>[56, 170+$rowTamanioExtra+$menosRowsNumH]
      ]);
      $dominio =DOMINIO;




      $html="
<div style='width: 100%'>
<table style='width:100%;margin-bottom: 10px'>
  <tr>
    <td align='center'>
      <img style=' max-width: 80%;' src='" . URL::to('files/logos/'.$dataEmpresa['logo']) . "'>
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
            <td style='font-size: 8px'><strong>Cliente:</strong></td>
            <td style='font-size: 8px'>$cliente</td>
          </tr>
          <tr>
            <td style='font-size: 7.5px'><strong>Direccion:</strong></td>
            <td style='font-size: 7.5px'>$direccion_</td>
          </tr>
           <tr>
            <td style='font-size: 7.5px'><strong>Pago:</strong></td>
            <td style='font-size: 7.5px'>$tipo_pagoC</td>
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

        $sql = "SELECT * FROM empresas where id_empresa = '{$dataVenta['id_empresa']}' ";
        $dataEmpresa = $this->conexion->query($sql)->fetch_assoc();

        $sql = "SELECT * FROM clientes where id_cliente = '{$dataVenta['id_cliente']}' ";
        $dataCliente = $this->conexion->query($sql)->fetch_assoc();

        $sql = "SELECT pv.*,p.descripcion FROM productos_ventas pv join productos p on p.id_producto = pv.id_producto where pv.id_venta =$id ";
        $dataProVenta = $this->conexion->query($sql);

        $sql = "SELECT * FROM ventas_servicios where id_venta =$id ";
        $dataServVenta = $this->conexion->query($sql);

        $rowsHTML ='';
        $contador = 1;

        $tipo_pagoC =$dataVenta["id_tipo_pago"]=='1'?'CONTADO':'CREDITO';
        $tabla_cuotas='';
        $menosRowsNumH=0;

        if ($dataVenta["id_tipo_pago"]=='2'){
            $rowTempCuo='';
            $sql="SELECT * FROM dias_ventas WHERE id_venta='$id'";
            $resulTempCuo= $this->conexion->query($sql);
            $contadorCuota=0;
            $menosRowsNumH=10;
            foreach ($resulTempCuo as $cuotTemp){
                $menosRowsNumH+=10;
                $menosRowsNumH++;
                $contadorCuota++;
                $tempNum=Tools::numeroParaDocumento($contadorCuota,2);
                $tempFecha =Tools::formatoFechaVisual($cuotTemp['fecha']);
                $tempMonto = Tools::money($cuotTemp['monto']);
                $rowTempCuo.="
            <tr>
                <td>Cuota $tempNum</td>
                <td>$tempFecha </td>
                <td>S/ $tempMonto</td>
            </tr>
            ";
            }
            $tabla_cuotas='

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
                    '.$rowTempCuo.'
                </tbody>
        </table>
        </div>';
        }

        $rowTamanioExtra = 0;

        foreach ($dataServVenta as $ser){
            $totalM = $ser['cantidad']*$ser['monto'];
            $motoFor = number_format($ser['monto'],2,".","");
            $totalM = number_format($totalM,2,".","");
            $cantidadss= number_format($ser['cantidad'],0,"","");
            $rowsHTML .="<tr>
            <td style='font-size: 10px'>$cantidadss</td>
            <td style='font-size: 10px'>{$ser['descripcion']}</td>
            <td style='font-size: 10px'>$motoFor</td>
            <td style='font-size: 10px'>$totalM</td>
            </tr>";
            $contador++;
            $rowTamanioExtra +=10;
        }

        foreach ($dataProVenta as $ser){
            $totalM = $ser['cantidad']*$ser['precio'];
            $motoFor = number_format($ser['precio'],2,".","");
            $totalM = number_format($totalM,2,".","");
            $cantidadss= number_format($ser['cantidad'],0,"","");
            $rowsHTML .="<tr>
            <td style='font-size: 10px'>$cantidadss</td>
            <td style='font-size: 10px'>{$ser['descripcion']}</td>
            <td style='font-size: 10px'>$motoFor</td>
            <td style='font-size: 10px'>$totalM</td>
            </tr>";
            $contador++;
            $rowTamanioExtra +=10;
        }


        $sql = "SELECT * FROM ventas_sunat where id_venta = '$id' ";
        $qrImage ='';
        if ($rowVS = $this->conexion->query($sql)->fetch_assoc()){
            $qrCode = new QrCode($rowVS["qr_data"]);
            $qrCode->setSize(150);
            $image = $qrCode->writeString(); //Salida en formato de texto
            $imageData = base64_encode($image);
            $qrImage = '<img style="width: 130px;" src="data:image/png;base64,' . $imageData . '">';
        }

        $data = '';
        $detalles = [];
        $fecha=date('d/m/Y',strtotime($dataVenta['fecha_emision']));
        $fechaVenc=date('d/m/Y',strtotime($dataVenta['fecha_vencimiento']));
        $vendedor='';
        $cliente=$dataCliente['datos'];
        $telefono_='';
        $direccion_=$dataVenta['direccion'];
        $puesto='';
        $zona='';

        $doc_S_N= $dataVenta["serie"]."-".Tools::numeroParaDocumento($dataVenta['numero'],6);
        $formatter = new NumeroALetras;
        $totalLetras =   $formatter->toInvoice(number_format($dataVenta['total'], 2, '.', ''), 2, 'SOLES');
        $totalIGVNumeros = number_format($dataVenta['total']/ 1.18 * 0.18, 2, '.', '');
        $totalNumeros = number_format($dataVenta['total'], 2, '.', '');

        $nom_emp=$dataEmpresa['razon_social'];
        $telefono=$dataEmpresa['telefono'];
        $direccion=$dataEmpresa['direccion'];

        $tipo_documeto_venta = "";

        if ($dataVenta['id_tido']==1){
            $tipo_documeto_venta = "BOLETA DE VENTA ELECTRÓNICA";
        }elseif ($dataVenta['id_tido']==2){
            $tipo_documeto_venta = "FACTURA DE VENTA ELECTRÓNICA";
        }elseif ($dataVenta['id_tido']==6){
            $qrImage ='';
            $tipo_documeto_venta = "NOTA DE VENTA  ELECTRÓNICA";
            $rowTamanioExtra-=30;
        }


        $this->mpdf->AddPageByArray([
            "orientation"=>"P",
            "newformat"=>[80, 200+$rowTamanioExtra+$menosRowsNumH]
        ]);
        $dominio =DOMINIO;




        $html="
<div style='width: 100%'>
<table style='width:100%;margin-bottom: 10px'>
  <tr>
    <td align='center'>
      <img style=' max-width: 85%;' src='" . URL::to('files/logos/'.$dataEmpresa['logo']) . "'>
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
        <span style='font-size: 13px;font-weight: bold'>$tipo_documeto_venta</span><br>
        <span style='font-size: 13px;'>$doc_S_N</span>
        
    </div>
    <hr>
    <div style='width: 100%;text-align: center'>
        <table style='width:100%'>
          <tr>
            <td style='font-size: 11px;width: 25%'><strong>Fecha E:</strong></td>
            <td style='font-size: 11px;'>$fecha</td>
          </tr><tr>
            <td style='font-size: 11px;width: 25%'><strong>Fecha V:</strong></td>
            <td style='font-size: 11px;'>$fechaVenc</td>
          </tr>
          <tr>
            <td style='font-size: 11px'><strong>Cliente:</strong></td>
            <td style='font-size: 11px'>$cliente</td>
          </tr>
          <tr>
            <td style='font-size: 11px'><strong>Direccion:</strong></td>
            <td style='font-size: 11px'>$direccion_</td>
          </tr>
          <tr>
            <td style='font-size: 11px'><strong>Pago:</strong></td>
            <td style='font-size: 11px'>$tipo_pagoC</td>
          </tr>
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
