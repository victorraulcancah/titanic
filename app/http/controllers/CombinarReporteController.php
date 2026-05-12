<?php

require_once 'utils/lib/mpdf/vendor/autoload.php';
require_once 'utils/lib/vendor/autoload.php';
// require_once "vendor/autoload.php";
require_once "app/models/Venta.php";
require_once "app/models/Cliente.php";
require_once "app/models/DocumentoEmpresa.php";
require_once "app/models/ProductoVenta.php";
require_once "app/models/VentaServicio.php";
require_once "app/models/Varios.php";
require_once "app/models/VentaSunat.php";
require_once "app/models/VentaAnulada.php";
require_once "app/clases/SendURL.php";
// require_once 'utils/lib/mpdf/vendor/setasign/fpdi/src/autoload.phpp';


use Endroid\QrCode\QrCode;
use Luecano\NumeroALetras\NumeroALetras;




class CombinarReporteController extends Controller
{

    private $conexion;
    private $venta;
    private $mpdf;

    public function __construct()
    {
        $this->mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 0]);
        $this->conexion = (new Conexion())->getConexion();
        $this->venta = new Venta();
        $this->ensureProductosCotisFechaRegistro();
    }

    private function ensureProductosCotisFechaRegistro()
    {
        $columna = $this->conexion->query("SHOW COLUMNS FROM productos_cotis LIKE 'fecha_registro'");
        if ($columna && $columna->num_rows == 0) {
            $this->conexion->query("ALTER TABLE productos_cotis ADD COLUMN fecha_registro DATETIME NULL AFTER costo");
            $this->conexion->query("UPDATE productos_cotis pc
                INNER JOIN cotizaciones co ON co.cotizacion_id = pc.id_coti
                SET pc.fecha_registro = co.fecha_registro
                WHERE pc.fecha_registro IS NULL");
        }
    }


    private function getNomMedida($nu)
    {
        if ($nu == 1)
            return "Unidad";
        if ($nu == 2)
            return "Caja";
        if ($nu == 3)
            return "Bolsa";
        if ($nu == 4)
            return "Saco";
    }


    public function comprobantePedido($numero)
    {

        $this->mpdf = new \Mpdf\Mpdf([
            "format" => [210, 297], // Tamaño A4 en mm (ancho, alto)
            "mode" => "utf-8",

        ]);

        // Establecer color negro por defecto para todo el documento
        $this->mpdf->SetDefaultBodyCSS('color', '#000000');
        $this->mpdf->SetDefaultBodyCSS('font-weight', 'bold');


        $sql = "SELECT cotizacion_id FROM cotizaciones WHERE numero = '$numero'";
        $resultado = $this->conexion->query($sql);
        $cotizacion = $resultado->fetch_assoc();
        $coti = $cotizacion['cotizacion_id'];


        $listaProd1 = $this->conexion->query("SELECT pc.*, p.descripcion, p.peso_bruto, TRIM(p.codigo) codigo 
        FROM productos_cotis pc 
        JOIN productos p ON p.id_producto = pc.id_producto 
        WHERE pc.id_coti = '$coti'
        ORDER BY codigo ASC");

        $sql = "SELECT * FROM cotizaciones WHERE cotizacion_id = '$coti'";
        $datoVenta = $this->conexion->query($sql)->fetch_assoc();
        // TOTAL PAGADO DE LAS CUOTAS QUE RESTAREMOS CON EL TOTAL DE LA COTIZACIONES
        $totalMontoCuotasCotizacion = $this->conexion->query("
    SELECT IFNULL(SUM(cc.monto), 0) AS total_monto
    FROM `cotizaciones` c
    LEFT JOIN `cuotas_cotizacion` cc ON cc.id_coti = c.cotizacion_id
    WHERE c.estado!=2 AND c.id_cliente = " . $datoVenta['id_cliente'] . "
    AND DATE(c.fecha) != CURDATE()
")->fetch_assoc()['total_monto'];

        $sumaTotalCotizacion = $this->conexion->query("
    SELECT IFNULL(SUM(total), 0) as total 
    FROM `cotizaciones` 
    WHERE estado!=2 AND `id_cliente` = " . $datoVenta['id_cliente'] . " 
    AND DATE(fecha) != CURDATE()
")->fetch_assoc()['total'];
        $SaldoPendientePagar = max(0, $sumaTotalCotizacion - $totalMontoCuotasCotizacion);

        $datoEmpresa = $this->conexion->query("SELECT * FROM empresas WHERE id_empresa = " . $_SESSION['id_empresa'])->fetch_assoc();

        $resultVemedor = $this->conexion->query("SELECT * FROM usuarios WHERE usuario_id = " . $datoVenta['id_usuario'])->fetch_assoc();

        $resultC = $this->conexion->query("SELECT * FROM clientes WHERE id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
        $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : (strlen($resultC['documento']) == 11 ? 'RUC' : '');


        $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha']);


        $tipo_pagoC = $datoVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
        $tabla_cuotas = '';

        $menosRowsNumH = 0;


        if ($datoVenta["id_tipo_pago"] == '2') {
            $rowTempCuo = '';
            $sql = "SELECT * FROM cuotas_cotizacion WHERE id_coti = '$coti'";
            $resulTempCuo = $this->conexion->query($sql);
            $contadorCuota = 0;
            $menosRowsNumH = 1;

            // Iterar sobre las cuotas
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

        $tipo_documeto_venta = "COTIZACION #: ";

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
            $precio = $prod['precio'];
            $importe = $precio * $prod['cantidad'];
            $total += $importe;
            $tempDescuento = 0;
            $importe -= $tempDescuento;
            $totalDescuento += $tempDescuento;
            $observacion = $datoVenta['observacion'];

            $precio = number_format($precio, 2, '.', ',');
            $importe = number_format($importe, 2, '.', ',');
            $tempDescuento = number_format($tempDescuento, 2, '.', ',');
            $prod['codigo'] = trim($prod['codigo']);

            $temMedida1 = $this->getNomMedida($prod['presenta']);
            $prod['cantidad'] = number_format($prod['cantidad'], 0);
            $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);

            // Calcular total de paquetes (cantidad × presenta_cnt)
            $total_paquetes = floatval($prod['cantidad']) * floatval($prod['presenta_cnt']);
            $total_paquetes_fmt = number_format($total_paquetes, 0);

            // Calcular peso total (cantidad × peso_bruto)
            $peso_bruto = floatval($prod['peso_bruto'] ?? 0);
            $cantidad_real = floatval(str_replace(',', '', $prod['cantidad']));
            $peso_total = $cantidad_real * $peso_bruto;
            $peso_total_fmt = number_format($peso_total, 2);

            $precioDisminu = $precio / $prod['presenta_cnt'];
            $precioDisminu = number_format($precioDisminu, 2, '.', ',');

            $rowHTML = $rowHTML . "
              <tr>
                
                <td class='' style='font-weight: bold; color: #000000; font-size: 11px; text-align: center;border-left: 1px solid #363636;'>{$prod['codigo']}</td>
                <td class='' style='font-weight: bold; color: #000000; font-size: 11px; text-align: center;border-left: 1px solid #363636;'>$cnt4 </td>
                <td class='' style='font-weight: bold; color: #000000; font-size: 11px; text-align: left;border-left: 1px solid #363636;'>{$prod['descripcion']}</td>
                <td class='' style='font-weight: bold; color: #000000; font-size: 11px; text-align: center;border-left: 1px solid #363636;'>$total_paquetes_fmt</td>
                <td class='' style='font-weight: bold; color: #000000; font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$peso_total_fmt Kg</td>
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
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
        
        
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'> </td>
      </tr>";
        }

        $totalLetras = $formatter->toInvoice(number_format($total, 2, '.', ''), 2, $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES');

        $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #ffffff ; float: right;'>

            <div style='padding: 5px;width: 100%; height: 100px; border: 2px solid #1e1e1e' class=''>
            <div style='margin-top:10px'></div>
            <span style='color: #000000; font-weight: bold;'>RUC: {$datoEmpresa['ruc']}</span><br>
            <div style='margin-top: 10px'></div>
            <span style='color: #000000; font-weight: bold;'><strong>$tipo_documeto_venta {$datoVenta['numero']}</strong></span><br>
            <div style='margin-top: 10px'></div>
            <span> </span>
            </div>
            </div>
            </div>";
        $this->mpdf->WriteFixedPosHTML("<img style='max-width: 300px;max-height: 85px' src='" . URL::to('files/logos/' . $datoEmpresa['logo']) . "'>", 15, 5, 150, 120);
        $this->mpdf->WriteFixedPosHTML($htmlCuadroHead ?? '', 0, 5, 195, 130);
        $this->mpdf->WriteFixedPosHTML("<span style='color: #000000; font-weight: bold; font-size: 12px'><strong style='color: #000000;'>Central Telefónica: </strong> <span style='color: #000000; font-weight: bold;'>{$datoEmpresa['telefono']}</span></span>", 15, 27, 210, 130);
        $this->mpdf->WriteFixedPosHTML("<span style='color: #000000; font-weight: bold; font-size: 12px'><strong style='color: #000000;'>Email: </strong> <span style='color: #000000; font-weight: bold;'>info@titanicsac.com | Web: www.titanicsac.com</span></span>", 15, 32, 210, 130);
        $this->mpdf->WriteFixedPosHTML("<span style='color: #000000; font-weight: bold; font-size: 12px'><strong style='color: #000000;'>Dirección:</strong> <span style='color: #000000; font-weight: bold; font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 37, 120, 130);

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
            <td style='color: #000000; font-size: 11px; padding: 2px;'><strong style='color: #000000;'>RUC/DNI: </strong><span style='color: #000000; font-weight: bold;'>{$resultC['documento']}</span></td>
            <td style='color: #000000; font-size: 11px; padding: 2px;'><strong style='color: #000000;'>CLIENTE:</strong><span style='color: #000000; font-weight: bold;'>{$resultC['datos']}</span></td>
            <td style='color: #000000; font-size: 11px; padding: 2px;'><strong style='color: #000000;'>DIRECCIÓN:</strong> <span style='color: #000000; font-weight: bold;'>{$resultC['direccion']}</span></td>
        </tr>
        <tr>
            <td style='color: #000000; font-size: 11px; padding: 2px;'><strong style='color: #000000;'>VENDEDOR:</strong> <span style='color: #000000; font-weight: bold;'>{$resultVemedor['nombres']}</span></td>
            <td style='color: #000000; font-size: 11px; padding: 2px;'><strong style='color: #000000;'>FECHA:</strong><span style='color: #000000; font-weight: bold;'>$fecha_emision</span></td>
            <td style='color: #000000; font-size: 11px; padding: 2px;'><strong style='color: #000000;'>MONEDA:</strong><span style='color: #000000; font-weight: bold;'>$monedaVisual</span></td>
        </tr>
        <tr>
            <td style='color: #000000; font-size: 11px; padding: 2px;'><strong style='color: #000000;'>PAGO:</strong> <span style='color: #000000; font-weight: bold;'>Contado</span></td>
            <td style='color: #000000; font-size: 11px; padding: 2px;'><strong style='color: #000000;'>CELULAR:</strong><span style='color: #000000; font-weight: bold;'>{$resultC['telefono']}</span></td>
        </tr>
        </table>
        </div>
        </div>
        
        
        </div>
        <div style='width: 100%; padding-top: 5px;'>
        <table style='width:100%;border-bottom: 1px solid #000000ff;border-collapse: collapse;'>
            <tr style='border-bottom: 1px solid #000000ff;border-collapse: collapse;'>
             <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #000000ff;border-collapse: collapse;'><strong>ITEM</strong></td>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #000000ff;border-collapse: collapse;'><strong>Cantidad</strong></td>
           
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #000000ff;border-collapse: collapse;'><strong>DESCRIPCION</strong></td>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #000000ff;border-collapse: collapse;'><strong>PAQUETES</strong></td>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #000000ff;border-collapse: collapse;'><strong>PESO (Kg)</strong></td>
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #000000ff;border-collapse: collapse;'><strong>PRECIO</strong></td> 
            <td style=' font-size: 12px;text-align: center; color: #000000;border: 1px solid #000000ff;border-collapse: collapse;'><strong>SUB TOTAL</strong></td>
            
          </tr>
          $rowHTML
          $rowHTMLTERT
              <tr>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;color: white'>.</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-bottom: 1px solid #363636;'> </td>
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
            <td style='border-left: 1px solid #000000ff;border-collapse: collapse; font-size: 12px; text-align: right'>Total a Pagar</td>
            <td style='border-left: 1px solid #000000ff;border-collapse: collapse; font-size: 12px;  text-align: right' >$simbolfff $totalDolar</td>
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
                        <table style='width: 100%;border-top: 1px solid #000000ff;border-bottom: 1px solid #000000ff;border-right: 1px solid #363636;border-collapse: collapse;'>
                            <!--
                            <tr>
                                <td style='border-left: 1px solid #000000ff;border-collapse: collapse; font-size: 12px; text-align: right'>Total Op. Gravado:</td>
                                <td style='border-left: 1px solid #000000ff;border-collapse: collapse; font-size: 12px;  text-align: right' >$totalOpgravado</td>
                            </tr>
                            <tr>
                                <td style='border-left: 1px solid #000000ff;border-collapse: collapse; font-size: 12px; text-align: right'>IGV:</td>
                                <td style='border-left: 1px solid #000000ff;border-collapse: collapse; font-size: 12px;  text-align: right' >$igv</td>
                            </tr>
                            -->
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
                            <span style='color: #000000; font-weight: bold;'>RUC: {$datoEmpresa['ruc']}</span><br>
                              <div style='margin-top: 10px'></div>
                              <span style='color: #000000; font-weight: bold;'><strong>$tipo_documeto_venta {$datoVenta['numero']}</strong></span><br>
                              <div style='margin-top: 10px'></div>
                            <span> </span>
                          </div>
                        </div>
                  </div>";


        $htmlFooter = "
          <div style='color: #000000; height: 10px;width: 100%; padding-bottom: 0px;font-size: 10px;border: 1px solid black;'><span style='color: #000000;'>. SON: | $totalLetras</span></div>
          
          <div style='color: #000000; width: 100%;margin-top: 5px;'>
              <div style='float: left; width: 100%;'>
                  $qrImage
              </div>
              <div style='color: #000000; width: 65%; padding-bottom: 5px;font-size: 12px; float: left; padding-top: 5px;'>
                  <div style='width: 100%'></div>
                  <div style='color: #000000; width: 95%; padding: 3px; font-size: 10px;'>
                       $hash_Doc
                       <strong style='color: #000000;'>Observaciones:</strong><br>
                       <span style='color: #000000; font-weight: bold;'>$observacion</span> <br>
                       <strong style='color: #000000;'>Saldo Pendiente:</strong>
                      <span style='color: #000000; font-weight: bold;'>$SaldoPendientePagar</span>
                  </div>
              </div>
              <div style='width: 35%;'>
                  <table style='width: 100%;border-top: 1px solid #363636;border-bottom: 1px solid #363636;border-right: 1px solid #363636;border-collapse: collapse;'>
                      <tr style=''>
                          <td style='color: #000000; border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'>Totalp Op. Gravado:</td>
                          <td style='color: #000000; border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' ><span style='color: #000000;'>$totalOpgravado</span></td>
                      </tr>
                      <tr>
                          <td style='color: #000000; border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'>IGV:</td>
                          <td style='color: #000000; border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right;' ><span style='color: #000000;'>$igv</span></td>
                      </tr>
                      <tr>
                          <td style='color: #000000; border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'><strong style='color: #000000;'>Total a Pagar</strong></td>
                          <td style='color: #000000; border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right;' ><span style='color: #000000;'>$simbolfff22 $total</span></td>
                      </tr>
                      $monedahtmlDol
                  </table>
              </div>
          </div> 
      ";
        // $this->mpdf->WriteFixedPosHTML($htmlFooter, 52.5, 240, 142, 130);
        $this->mpdf->WriteFixedPosHTML($htmlFooter, 15.5, 225, 180, 130);
        $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        $this->mpdf->Output("Cotizacion{$datoVenta['numero']}.pdf", 'I');
    }
    public function comprobantePedidoDiasVisita($diasVisita)
    {

        $mpdf = new \Mpdf\Mpdf([
            'format' => 'Letter',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);


        $sql = "SELECT c.id_cliente, co.cotizacion_id 
            FROM clientes c 
            JOIN cotizaciones co ON co.id_cliente = c.id_cliente 
            WHERE co.estado!=2 AND c.dias_visitas = '$diasVisita'";

        $resultado = $this->conexion->query($sql);

        // Manejo de errores
        if (!$resultado) {
            die("Error en la consulta: " . $this->conexion->error);
        }

        $cotizacion = $resultado->fetch_assoc();

        // Verificar si se encontró una cotización
        if (!$cotizacion) {
            die("No se encontraron cotizaciones para los días de visita: " . $diasVisita);
        }

        $coti = $cotizacion['cotizacion_id'];


        $listaProd1 = $this->conexion->query("SELECT pc.*, p.descripcion, p.peso_bruto, TRIM(p.codigo) codigo 
        FROM productos_cotis pc 
        JOIN productos p ON p.id_producto = pc.id_producto 
        WHERE pc.id_coti = '$coti'
        ORDER BY codigo ASC");

        $sql = "SELECT * FROM cotizaciones WHERE cotizacion_id = '$coti'";
        $datoVenta = $this->conexion->query($sql)->fetch_assoc();


        $datoEmpresa = $this->conexion->query("SELECT * FROM empresas WHERE id_empresa = " . $_SESSION['id_empresa'])->fetch_assoc();

        $resultVemedor = $this->conexion->query("SELECT * FROM usuarios WHERE usuario_id = " . $datoVenta['id_usuario'])->fetch_assoc();

        $resultC = $this->conexion->query("SELECT * FROM clientes WHERE id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
        $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : (strlen($resultC['documento']) == 11 ? 'RUC' : '');


        $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha']);


        $tipo_pagoC = $datoVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
        $tabla_cuotas = '';

        $menosRowsNumH = 0;


        if ($datoVenta["id_tipo_pago"] == '2') {
            $rowTempCuo = '';
            $sql = "SELECT * FROM cuotas_cotizacion WHERE id_coti = '$coti'";
            $resulTempCuo = $this->conexion->query($sql);
            $contadorCuota = 0;
            $menosRowsNumH = 1;

            // Iterar sobre las cuotas
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
            // FIX: Usar el precio directo como en el reporte individual
            $precio = $prod['precio'];

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
            $prod['cantidad'] = number_format($prod['cantidad'], 0);
            $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);

            // Calcular total de paquetes (cantidad × presenta_cnt)
            $cantidad_real = floatval(str_replace(',', '', $prod['cantidad']));
            $total_paquetes = $cantidad_real * floatval($prod['presenta_cnt']);
            $total_paquetes_fmt = number_format($total_paquetes, 0);

            // Calcular peso total (cantidad × peso_bruto)
            $peso_bruto = floatval($prod['peso_bruto'] ?? 0);
            $peso_total = $cantidad_real * $peso_bruto;
            $peso_total_fmt = number_format($peso_total, 2);

            $precioDisminu = $precio / $prod['presenta_cnt'];
            $precioDisminu = number_format($precioDisminu, 2, '.', ',');

            $rowHTML .= "
            <tr>
                <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center; border-left: 1px solid #fff; padding: 0; width: 40px; white-space: nowrap;'>$contador</td>

                <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding:0;  width: 60px; white-space: nowrap;'>{$prod['codigo']}</td>

                <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: left;border-left: 1px solid #fff;  padding:0; '>{$prod['descripcion']}</td>

                <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff;  padding:0; width: 70px; white-space: nowrap;'>$total_paquetes_fmt</td>
                
                <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff;  padding:2px; width: 70px; white-space: nowrap;'>$peso_total_fmt Kg</td> 
                <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff;border-right: 1px solid #fff; padding:0; width: 70px; white-space: nowrap;'>$cnt4</td>
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

        $totalLetras = $formatter->toInvoice(number_format($total, 2, '.', ''), 2, $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES');

        $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #fff ; float: right; margin: left 100px;px;'>
            <div style='padding: 5px;width: 100%; height: 100px; ' class=''>
            <div ></div>
            <span style='color: #000000; font-weight: bold;'>  </span><br>
            <div style='padding-top:12px;' ></div>
            <span style='color: #000000; font-weight: bold;'><strong>$tipo_documeto_venta {$datoVenta['numero']}</strong></span><br>
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

        // Agregar CSS global para forzar color negro y fuente en negrita
        $mpdf->WriteHTML('body, td, span, strong, div, p { color: #000000; font-weight: bold; }', \Mpdf\HTMLParserMode::HEADER_CSS);

        $html = "<div style='width: 100%; padding-top: 60px; overflow: hidden;clear: both;'>
        <div style='width: 567px;border: 1px solid white'>
            <div style='width: 55%; float: left;'>
                <table style='width:100%; font-weight: bold;'>
                    <tr>
                        <td style='color: #000; font-size: 9px;text-align: left; color: white;'><strong style='color: #000;'>CONDICION: </strong></td>
                        <td style='color: #000; font-size: 9px;'>Contado</td>
                    </tr>
                    <tr>
                        <td style='color: #000; font-family: Arial, sans-serif; font-size: 9px;text-align: left '><strong style='color: #000;'> </strong></td>
                        <td style='color: #000; font-family: Arial, sans-serif; font-size: 9px;'>{$resultC['datos']}</td>
                    </tr>
                    <tr>
                        <td style='color: #000; font-family: Arial, sans-serif; font-size: 9px;text-align: left'><strong style='color: #000;'> </strong></td>
                        <td style='color: #000; font-family: Arial, sans-serif; font-size: 9px;'>{$resultC['direccion']}</td>
                    </tr>
                </table>
            </div>
            <div style='width: 45%; float: left'>
                <table style='width:100%; font-weight: bold;'>
                    <tr>
                        <td style='width:8px; color: #000; font-size: 9px;text-align: left; color:white'><strong style='color: #000;'>fha</strong></td>
                        <td style='color: #000; font-family: Arial, sans-serif; font-size: 9px; '>$fecha_emision</td>
                    </tr>
                    <tr>
                        <td style='width:8px; color: #000; font-size: 9px;text-align: left;color:white'><strong style='color: #000;'>DOR</strong></td>
                        <td style='color: #000; font-family: Arial, sans-serif; font-size: 9px;'>{$resultVemedor['nombres']} </td>
                    </tr>
                    <tr>
                        <td style='width:8px; color: #000; font-size: 9px;text-align: left; color:white'><strong style='color: #000;'>CE</strong></td>
                        <td style='color: #000; font-family: Arial, sans-serif; font-size: 9px;'>{$resultC['telefono']}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div style='width: 100%; padding-top: 20px; margin-left: 20px'>
        <table style='width:567px; border-bottom: 1px solid #fff;border-collapse: collapse;'>
            <tr style='border-bottom: 1px solid #fff;border-collapse: collapse;'>
                <td style=' font-size: 12px;text-align: center; color: #fff;border: 1px solid #fff; padding:0;'><strong>item</strong></td>
                <td style=' font-size: 12px; color: #fff;border: 1px solid #fff;border-collapse: collapse; padding: 0;'><strong>Código</strong></td>
                <td style=' font-size: 12px;text-align: center; color: #fff;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>PRODUCTO</strong></td>
                <td style=' font-size: 12px;text-align: center; color: #fff;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>PAQUETES</strong></td> 
                <td style=' font-size: 12px;text-align: center; color: #fff;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>PESO (Kg)</strong></td> 
                <td style=' font-size: 12px;text-align: center; color: #fff;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>CANTIDAD</strong></td>
            </tr>
            $rowHTML
            $rowHTMLTERT
            <tr>
                <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff;color: white; padding:0;'>.</td>
                <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff; padding:0;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-bottom: 1px solid #fff;  padding:0;'> </td>
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
                <div style='width: 95%; padding: 3px; font-size: 10px; height: 150px '>
                    $hash_Doc
                </div>
            </div>
        </div>
    ");



        $mpdf->Output("Cotizacion{$datoVenta['numero']}.pdf", 'I');
    }

    public function comprobantePedidoFecha($fecha_inicio, $fecha_fin)
    {
        $this->mpdf = new \Mpdf\Mpdf([
            "format" => [210, 297],
            "mode" => "utf-8",
        ]);

        $sql = "SELECT cotizacion_id FROM cotizaciones WHERE estado!=2 AND fecha >= '$fecha_inicio' AND fecha <= '$fecha_fin'";
        $resultado = $this->conexion->query($sql);
        $cotizaciones = $resultado->fetch_all(MYSQLI_ASSOC);

        // return $cotizacion;
        foreach ($cotizaciones as $cotizacion) {
            $coti = $cotizacion['cotizacion_id'];
            $diaSemana = date('N');
            $dias_acum = 0;

            if ($diaSemana == 2) {
                $dias_acum = 3;
            } else {
                $dias_acum = 2;
            }
            $listaProd1 = $this->conexion->query("SELECT pc.*, p.descripcion, TRIM(p.codigo) codigo 
        FROM productos_cotis pc 
        JOIN productos p ON p.id_producto = pc.id_producto 
        WHERE pc.id_coti = '$coti'
        ORDER BY codigo ASC");

            $sql = "SELECT * FROM cotizaciones WHERE cotizacion_id = '$coti'";
            $datoVenta = $this->conexion->query($sql)->fetch_assoc();
            // TOTAL PAGADO DE LAS CUOTAS QUE RESTAREMOS CON EL TOTAL DE LA COTIZACIONES
            $totalMontoCuotasCotizacion = $this->conexion->query("
                SELECT IFNULL(SUM(cc.monto), 0) AS total_monto
                FROM `cotizaciones` c
                LEFT JOIN `cuotas_cotizacion` cc ON cc.id_coti = c.cotizacion_id
                WHERE c.estado!=2 AND c.id_cliente = " . $datoVenta['id_cliente'] . "
                AND DATE(c.fecha) < DATE_SUB(CURDATE(), INTERVAL $dias_acum DAY) 
                ")->fetch_assoc()['total_monto'];

            $sumaTotalCotizacion = $this->conexion->query("
                    SELECT IFNULL(SUM(total), 0) as total 
                    FROM `cotizaciones` 
                    WHERE estado!=2 AND `id_cliente` = " . $datoVenta['id_cliente'] . " 
                    AND DATE(fecha) < DATE_SUB(CURDATE(), INTERVAL $dias_acum DAY) 
                ")->fetch_assoc()['total'];


            $SaldoPendientePagar = max(0, $sumaTotalCotizacion - $totalMontoCuotasCotizacion);

            $datoEmpresa = $this->conexion->query("SELECT * FROM empresas WHERE id_empresa = " . $_SESSION['id_empresa'])->fetch_assoc();

            $resultVemedor = $this->conexion->query("SELECT * FROM usuarios WHERE usuario_id = " . $datoVenta['id_usuario'])->fetch_assoc();

            $resultC = $this->conexion->query("SELECT * FROM clientes WHERE id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
            $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : (strlen($resultC['documento']) == 11 ? 'RUC' : '');


            $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha']);


            $tipo_pagoC = $datoVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
            $tabla_cuotas = '';

            $menosRowsNumH = 0;


            if ($datoVenta["id_tipo_pago"] == '2') {
                $rowTempCuo = '';
                $sql = "SELECT * FROM cuotas_cotizacion WHERE id_coti = '$coti'";
                $resulTempCuo = $this->conexion->query($sql);
                $contadorCuota = 0;
                $menosRowsNumH = 1;

                // Iterar sobre las cuotas
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

            $tipo_documeto_venta = "COTIZACION #: ";

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
                // FIX: Usar el precio directo como en el reporte individual
                $precio = $prod['precio'];

                $importe = $precio * $prod['cantidad'];
                $total += $importe;
                $tempDescuento = 0;
                $observacion = $datoVenta['observacion'];

                $importe -= $tempDescuento;
                $totalDescuento += $tempDescuento;

                $precio = number_format($precio, 2, '.', ',');
                $importe = number_format($importe, 2, '.', ',');
                $tempDescuento = number_format($tempDescuento, 2, '.', ',');
                $prod['codigo'] = trim($prod['codigo']);

                $temMedida1 = $this->getNomMedida($prod['presenta']);
                $prod['cantidad'] = number_format($prod['cantidad'], 0);
                $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);

                $precioDisminu = $precio / $prod['presenta_cnt'];
                $precioDisminu = number_format($precioDisminu, 2, '.', ',');

                $rowHTML = $rowHTML . "
             <tr>
                <td class='' style='font-weight: bold; color: #000000; font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$contador</td>
                <td class='' style='font-weight: bold; color: #000000; font-size: 10px; text-align: left;border-left: 1px solid #363636;'><strong>{$prod['descripcion']}</strong></td>
                <td class='' style='font-weight: bold; color: #000000; font-size: 10px; text-align: center;border-left: 1px solid #363636;'>{$prod['cantidad']} </td>
                <td class='' style='font-weight: bold; color: #000000; font-size: 10px; text-align: center;border-left: 1px solid #363636;'>{$prod['presenta_cnt']}  </td>
                <td class='' style='font-weight: bold; color: #000000; font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$precioDisminu</td> 
                <td class='' style='font-weight: bold; color: #000000; font-size: 10px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$importe</td>
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
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
        
        
        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'> </td>
      </tr>";
            }

            $totalLetras = $formatter->toInvoice(number_format($total, 2, '.', ''), 2, $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES');

            $htmlCuadroHead = "<div style=' width: 34%;text-align: center; background-color: #ffffff ; float: right;'>

            <div style='padding: 5px;width: 100%; height: 100px; border: 2px solid #1e1e1e' class=''>
            <div style='margin-top:10px'></div>
            <span style='color: #000000; font-weight: bold;'>RUC: {$datoEmpresa['ruc']}</span><br>
            <div style='margin-top: 10px'></div>
            <span style='color: #000000; font-weight: bold;'><strong>$tipo_documeto_venta {$datoVenta['numero']}</strong></span><br>
            <div style='margin-top: 10px'></div>
            <span> </span>
            </div>
            </div>
            </div>";
            $this->mpdf->WriteFixedPosHTML("<img style='max-width: 300px;max-height: 85px' src='" . URL::to('files/logos/' . $datoEmpresa['logo']) . "'>", 15, 5, 150, 120);
            $this->mpdf->WriteFixedPosHTML($htmlCuadroHead ?? '', 0, 5, 195, 130);
            $this->mpdf->WriteFixedPosHTML("<span style='color: #000000 !important; font-size: 12px'><strong style='color: #000000 !important;'>Central Telefónica: </strong> {$datoEmpresa['telefono']}</span>", 15, 27, 210, 130);
            $this->mpdf->WriteFixedPosHTML("<span style='color: #000000 !important; font-size: 12px'><strong style='color: #000000 !important;'>Email: </strong> info@titanicsac.com | Web: www.titanicsac.com</span>", 15, 32, 210, 130);
            $this->mpdf->WriteFixedPosHTML("<span style='color: #000000; font-size: 12px'><strong style='color: #000000;'>Dirección:</strong> <span style='color: #000000; font-size: 10px'>{$datoEmpresa['direccion']}</span></span>", 15, 37, 120, 130);

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

            // Agregar CSS global para forzar color negro y negrita
            $this->mpdf->WriteHTML('body, td, span, strong, div, p { color: #000000; font-weight: bold; }', \Mpdf\HTMLParserMode::HEADER_CSS);

            $html = "<div style='width: 100%;padding-top: 110px; overflow: hidden;clear: both;'>
        <div style='width: 100%;border: 1px solid black'>
        <div style='width: 100%; float: left; '>
        
        <table style='width:100%'>
          <tr>
            <td style='color: #000000 !important; font-size: 8px; padding: 2px;'><strong style='color: #000000 !important;'>RUC/DNI: </strong>{$resultC['documento']}</td>
            <td style='color: #000000 !important; font-size: 8px; padding: 2px;'><strong style='color: #000000 !important;'>CLIENTE:</strong>{$resultC['datos']}</td>
            <td style='color: #000000 !important; font-size: 8px; padding: 2px;'><strong style='color: #000000 !important;'>DIRECCIÓN:</strong> {$resultC['direccion']}</td>
        </tr>
        <tr>
            <td style='color: #000000 !important; font-size: 8px; padding: 2px;'><strong style='color: #000000 !important;'>VENDEDOR:</strong> {$resultVemedor['nombres']}</td>
            <td style='color: #000000 !important; font-size: 8px; padding: 2px;'><strong style='color: #000000 !important;'>FECHA:</strong>$fecha_emision</td>
            <td style='color: #000000 !important; font-size: 8px; padding: 2px;'><strong style='color: #000000 !important;'>MONEDA:</strong>$monedaVisual</td>
        </tr>
        <tr>
            <td style='color: #000000 !important; font-size: 8px; padding: 2px;'><strong style='color: #000000 !important;'>PAGO:</strong> Contado</td>
            <td style='color: #000000 !important; font-size: 8px; padding: 2px;'><strong style='color: #000000 !important;'>CELULAR:</strong>{$resultC['telefono']}</td>
        </tr>
        </table>
        </div>
        </div>
        
        
        </div>
        <div style='width: 100%; padding-top: 5px;'>
      <table style='width:100%;border-bottom: 1px solid #363636;border-collapse: collapse;'>
        <tr style='border-bottom: 1px solid #363636;border-collapse: collapse;'>
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 5%;'><strong>ITEM</strong></td>
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 50%;'><strong>DESCRIPCION</strong></td>
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>Cantidad</strong></td>
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>MEDIDA</strong></td> 
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>PRECIO</strong></td> 
          <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>SUB TOTAL</strong></td>
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
                       $observacion <br>
                       Saldo Pendiente:
                      $SaldoPendientePagar
                        </div>
                    </div>
                    <div style='width: 35%;'>
                        <table style='width: 100%;border-top: 1px solid #363636;border-bottom: 1px solid #363636;border-right: 1px solid #363636;border-collapse: collapse;'>
                            <!--
                            <tr>
                                <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>Total Op. Gravado:</td>
                                <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$totalOpgravado</td>
                            </tr>
                            <tr>
                                <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right'>IGV:</td>
                                <td style='border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$igv</td>
                            </tr>
                            -->
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
                            <span style='color: #000000; font-weight: bold;'>RUC: {$datoEmpresa['ruc']}</span><br>
                              <div style='margin-top: 10px'></div>
                              <span style='color: #000000; font-weight: bold;'><strong>$tipo_documeto_venta {$datoVenta['numero']}</strong></span><br>
                              <div style='margin-top: 10px'></div>
                            <span> </span>
                          </div>
                        </div>
                  </div>";


            $htmlFooter = "
          <div style='color: #000000 !important; height: 10px;width: 100%; padding-bottom: 0px;font-size: 10px;border: 1px solid black;'>. SON: | $totalLetras</div>
          
          <div style='color: #000000 !important; width: 100%;margin-top: 10px;'>
              <div style='float: left; width: 100%;'>
                  $qrImage
                  <div style='position: absolute; left: 80px; top: 5px;'></div>
              </div>
              <div style='color: #000000 !important; width: 65%; padding-bottom: 5px;font-size: 12px; float: left; padding-top: 10px;'>
                  <div style='width: 100%'></div>
                  <div style='color: #000000 !important; width: 95%; padding: 3px; font-size: 10px;height: 90px '>
                      $hash_Doc
                     <strong style='color: #000000 !important;'>Observaciones:</strong><br>
                       $observacion <br>
                       <strong style='color: #000000 !important;'>Saldo Pendiente:</strong>
                      $SaldoPendientePagar
                  </div>
              </div>
              <div style='width: 35%;'>
                  <table style='width: 100%;border-top: 1px solid #363636;border-bottom: 1px solid #363636;border-right: 1px solid #363636;border-collapse: collapse;'>
                      <tr style=''>
                          <td style='color: #000000 !important; border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'>Totalp Op. Gravado:</td>
                          <td style='color: #000000 !important; border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right' >$totalOpgravado</td>
                      </tr>
                      <tr>
                          <td style='color: #000000 !important; border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'>IGV:</td>
                          <td style='color: #000000 !important; border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right;' >$igv</td>
                      </tr>
                      <tr>
                          <td style='color: #000000 !important; border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px; text-align: right;padding-right: 5px;'><strong style='color: #000000 !important;'>Total a Pagar</strong></td>
                          <td style='color: #000000 !important; border-left: 1px solid #363636;border-collapse: collapse; font-size: 12px;  text-align: right;' >$simbolfff22 $total</td>
                      </tr>
                      $monedahtmlDol
                  </table>
              </div>
          </div> 
      ";
            $this->mpdf->WriteFixedPosHTML($htmlFooter, 15.5, 240, 180, 230);
            $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
            if ($listaProd1 !== count($listaProd1) - 1) {
                // Si no es la última iteración, agrega una página
                $this->mpdf->AddPage();
            }
        }

        $this->mpdf->Output("Cotizacion{$datoVenta['numero']}.pdf", 'I');
    }

    public function comprobantePedidoCamion()
    {
        $mpdf = new \Mpdf\Mpdf([
            "format" => "A4-L",         // Formato A4 en orientación landscape
            "mode" => "utf-8",
            "margin_left" => 3,     // Margen izquierdo en milímetros
            "margin_right" => 3,    // Margen derecho en milímetros
            "margin_top" => 5,      // Margen superior en milímetros
            "margin_bottom" => 5,   // Margen inferior en milímetros
        ]);

        $camion = $_GET['camion'] ?? "";
        $fechaSeleccionada = $_GET['fechaSeleccionada'] ?? "";
        $fechaFinSeleccionada = $_GET['fechaFinSeleccionada'] ?? "";
        $diasVisita = $_GET['diasVisita'] ?? "";
        $ruta = $_GET['ruta'] ?? "";
        if ($ruta === 'null')
            $ruta = '';
        $mercado = $_GET['mercado'] ?? "";
        $filtros = array();
        if ($camion == "" || $fechaSeleccionada == "" || $fechaFinSeleccionada == "") {
            die("No se ingresaron todos los campos requiridos");
        }
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
            default:
                break;
        }



        if ($ruta != "") {
            foreach ($filtros as $key => $filtro) {
                $filtros[$key] = [$ruta];
            }
        }

        if ($diasVisita != "" && isset($filtros[$diasVisita])) {
            $filtros = [
                $diasVisita => $filtros[$diasVisita]
            ];
        }
        $queryClientes = "";
        if ($fechaSeleccionada != "") {
            $queryClientes .= " AND co.fecha between '$fechaSeleccionada' AND '$fechaFinSeleccionada' ";
        }
        if ($mercado != "") {
            $queryClientes .= " AND c.mercado= '$mercado'";
        }
        $arrQueryClientes = array();

        foreach ($filtros as $key => $filtro) {
            $arrQueryClientes[] = "( c.dias_visitas = '{$key}' AND c.id_ruta IN (" . implode(',', $filtro) . ") )";
        }

        if (sizeof($arrQueryClientes) > 0) {
            $queryClientes .= " AND (" . implode(' OR ', $arrQueryClientes) . ")";
        }


        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $sql = "SELECT c.id_cliente, co.cotizacion_id 
            FROM clientes c 
            JOIN cotizaciones co ON co.id_cliente = c.id_cliente   
            WHERE 1 
            AND co.id_empresa='{$_SESSION['id_empresa']}'
            AND co.sucursal='{$_SESSION['sucursal']}'
            AND co.estado!=2 " . $queryClientes . " 
            ORDER BY c.mercado ASC, c.datos ASC, co.numero ASC
            ";


        $resultado = $this->conexion->query($sql);
        // Manejo de errores
        if (!$resultado) {
            die("Error en la consulta: " . $this->conexion->error);
        }

        // $cotizacion = $resultado->fetch_assoc();
        $cotizaciones = array();
        while ($row = $resultado->fetch_assoc()) {
            $cotizaciones[] = $row;
        }

        // Verificar si se encontró una cotización
        if (sizeof($cotizaciones) <= 0) {
            die("No se encontraron cotizaciones para camion: " . $camion);
        }

        $item_contador = 1;
        foreach ($cotizaciones as $key => $cotizacion) {
            $coti = $cotizacion['cotizacion_id'];
            $diaSemana = date('N');
            $dias_acum = 0;

            if ($diaSemana == 2) {
                $dias_acum = 3;
            } else {
                $dias_acum = 2;
            }
            $listaProd1 = $this->conexion->query("SELECT pc.*, p.descripcion, TRIM(p.codigo) codigo 
            FROM productos_cotis pc 
            JOIN productos p ON p.id_producto = pc.id_producto 
            WHERE pc.id_coti = '$coti'
            ORDER BY codigo ASC");

            $sql = "SELECT * FROM cotizaciones WHERE cotizacion_id = '$coti'";
            $datoVenta = $this->conexion->query($sql)->fetch_assoc();
            // TOTAL PAGADO DE LAS CUOTAS PAGADAS (estado=1) DE PEDIDOS NO ANULADOS
            $totalMontoCuotasCotizacion = $this->conexion->query("
                SELECT IFNULL(SUM(cc.monto), 0) AS total_monto
                FROM `cotizaciones` c
                INNER JOIN `cuotas_cotizacion` cc ON cc.id_coti = c.cotizacion_id
                WHERE c.estado != 2 
                AND cc.estado = 1 
                AND c.id_cliente = " . $datoVenta['id_cliente'] . "
                AND DATE(c.fecha) < DATE_SUB(CURDATE(), INTERVAL $dias_acum DAY) 
            ")->fetch_assoc()['total_monto'];

            // TOTAL DE DEUDA (SOLO PEDIDOS A CRÉDITO Y NO ANULADOS)
            $sumaTotalCotizacion = $this->conexion->query("
                SELECT IFNULL(SUM(total), 0) as total 
                FROM `cotizaciones` 
                WHERE estado != 2 
                AND id_tipo_pago = 2
                AND `id_cliente` = " . $datoVenta['id_cliente'] . " 
                AND DATE(fecha) < DATE_SUB(CURDATE(), INTERVAL $dias_acum DAY) 
            ")->fetch_assoc()['total'];

            $SaldoPendientePagar = max(0, $sumaTotalCotizacion - $totalMontoCuotasCotizacion);

            $datoEmpresa = $this->conexion->query("SELECT * FROM empresas WHERE id_empresa = " . $_SESSION['id_empresa'])->fetch_assoc();

            $resultVemedor = $this->conexion->query("SELECT * FROM usuarios WHERE usuario_id = " . $datoVenta['id_usuario'])->fetch_assoc();

            $resultC = $this->conexion->query("SELECT * FROM clientes WHERE id_cliente = " . $datoVenta['id_cliente'])->fetch_assoc();
            $dataDocumento = strlen($resultC['documento']) == 8 ? "DNI" : (strlen($resultC['documento']) == 11 ? 'RUC' : '');


            $fecha_emision = Tools::formatoFechaVisual($datoVenta['fecha']);


            $tipo_pagoC = $datoVenta["id_tipo_pago"] == '1' ? 'CONTADO' : 'CREDITO';
            $tabla_cuotas = '';

            $menosRowsNumH = 0;


            if ($datoVenta["id_tipo_pago"] == '2') {
                $rowTempCuo = '';
                $sql = "SELECT * FROM cuotas_cotizacion WHERE id_coti = '$coti'";
                $resulTempCuo = $this->conexion->query($sql);
                $contadorCuota = 0;
                $menosRowsNumH = 1;

                // Iterar sobre las cuotas
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
                // FIX: Usar el precio directo como en el reporte individual
                $precio = $prod['precio'];

                $importe = $precio * $prod['cantidad'];
                $total += $importe;
                $tempDescuento = 0;
                $observacion = $datoVenta['observacion'];

                $importe -= $tempDescuento;
                $totalDescuento += $tempDescuento;

                $precio = number_format($precio, 2, '.', ',');
                $importe = number_format($importe, 2, '.', ',');
                $tempDescuento = number_format($tempDescuento, 2, '.', ',');
                $prod['codigo'] = trim($prod['codigo']);

                $temMedida1 = $this->getNomMedida($prod['presenta']);
                $prod['cantidad'] = number_format($prod['cantidad'], 0);
                $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);

                $descuento = ($prod['presenta_cnt'] && $prod['presenta_cnt'] != 0) ? $prod['presenta_cnt'] : 1;
                $descuento_str = ($prod['presenta_cnt'] && $prod['presenta_cnt'] != 0) ? $prod['presenta_cnt'] : '-';
                $precioDisminu = $precio / $descuento;
                $precioDisminu = number_format($precioDisminu, 2, '.', ',');
                $multi = $prod['cantidad'] * $prod['presenta_cnt'];
                $rowHTML = $rowHTML . "
              <tr>
                <td class='' style='font-weight: bold; color: #000000; font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$contador</td>
                <td class='' style='font-weight: bold; color: #000000; font-size: 10px; text-align: center; border-left: 1px solid #363636;'>$multi</td>
                <td class='' style='font-weight: bold; color: #000000; font-size: 10px; text-align: left;border-left: 1px solid #363636;'><strong>{$prod['descripcion']}</strong></td>
                <td class='' style='font-weight: bold; color: #000000; font-size: 10px; text-align: center;border-left: 1px solid #363636;'>{$prod['cantidad']} </td>
                <td class='' style='font-weight: bold; color: #000000; font-size: 10px; text-align: center;border-left: 1px solid #363636;'>{$prod['presenta_cnt']}  </td>
                <td class='' style='font-weight: bold; color: #000000; font-size: 10px; text-align: center;border-left: 1px solid #363636;'>$precioDisminu</td> 
                <td class='' style='font-weight: bold; color: #000000; font-size: 10px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'>$importe</td>
              </tr>
              
            ";
                $contador++;
            }
            $cntRowEE = 41;
            $rowHTMLTERT = "";
            for ($tert = 0; $tert < ($cntRowEE - $contador) - $menosRowsNumH; $tert++) {
                $rowHTMLTERT .= "<tr>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; color: white'>.</td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td> 
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636; '> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #363636;border-right: 1px solid #363636;'> </td>
            </tr>";
            }

            $totalLetras = $formatter->toInvoice(number_format($total, 2, '.', ''), 2, $datoVenta['moneda'] == 1 ? 'SOLES' : 'DOLARES');

            $htmlEncabezado = "
      <table style='width: 100%; border-collapse: collapse; margin-top: 0px;'>
          <tr>
              <td style='width: 100%; vertical-align: top;'>
                  <table style='width: 100%; border-collapse: collapse;'>
                    <tr>
                        <!-- Columna del Logo y Datos -->
                        <td style='width: 75%; text-align: left; vertical-align: middle;'>
                            <img style='max-width: 250px; max-height: 48px;' src='" . URL::to('files/logos/' . $datoEmpresa['logo']) . "'><br>
                            <span style='font-size: 9px;'><strong>Central Telefónica:</strong> <span style='color: #000000;'>{$datoEmpresa['telefono']}</span></span><br>
                            <span style='font-size: 9px;'><strong>Email:</strong> <span style='color: #000000;'>info@titanicsac.com | Web: www.titanicsac.com</span></span><br>
                            <span style='font-size: 9px;'><strong>Dirección:</strong> <span style='font-size: 9px; color: #000000;'>{$datoEmpresa['direccion']}</span></span>
                        </td>
                        <!-- Columna del Cuadro de Pedido -->
                          <td style='width: 25%; text-align: center; vertical-align: middle; border: 1px solid #1e1e1e;'>
                                <span style='font-size: 9px;'>PEDIDO</span><br><br> <!-- Usamos dos <br> para separar -->
                                <span style='font-size: 9px; font-weight: bold;'>{$datoVenta['numero']}</span><br><br>
                                <span style='font-size: 11px; font-weight: bold;'>ITEM: " . ($item_contador) . "</span>
                        </td>
                      </tr>
                  </table>
              </td>
          </tr>
      </table>";

            // Agregar CSS global para forzar color negro y negrita
            $mpdf->WriteHTML('body, td, span, strong, div, p { color: #000000; font-weight: bold; }', \Mpdf\HTMLParserMode::HEADER_CSS);



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
                        <td style='color: #000000; font-size: 8px; padding: 2px;'><strong style='color: #000000;'>CLIENTE:</strong> <span style='color: #000000;'>{$resultC['datos']}</span></td>
                        <td style='color: #000000; font-size: 8px; padding: 2px;'><strong style='color: #000000;'>CELULAR:</strong> <span style='color: #000000;'>{$resultC['telefono']}</span></td>
                    </tr>
                    <tr>
                        <td style='color: #000000; font-size: 8px; padding: 2px;'><strong style='color: #000000;'>DIRECCIÓN:</strong> <span style='color: #000000;'>{$resultC['direccion']}</span></td>
                        <td style='color: #000000; font-size: 8px; padding: 2px;'><strong style='color: #000000;'>VENDEDOR:</strong> <span style='color: #000000;'>{$resultVemedor['nombres']}</span></td>
                    </tr>
                    <tr>
                        <td style='color: #000000; font-size: 8px; padding: 2px;'><strong style='color: #000000;'>RUC/DNI:</strong> <span style='color: #000000;'>{$resultC['documento']}</span></td>
                        <td style='color: #000000; font-size: 8px; padding: 2px;'><strong style='color: #000000;'>FECHA:</strong> <span style='color: #000000;'>$fecha_emision</span></td>
                    </tr>
                    <tr>
                        <td style='color: #000000; font-size: 8px; padding: 2px;'><strong style='color: #000000;'>MONEDA:</strong> <span style='color: #000000;'>$monedaVisual</span></td>
                        <td style='color: #000000; font-size: 8px; padding: 2px;'><strong style='color: #000000;'>PAGO:</strong> <span style='color: #000000;'>Contado</span></td>
                    </tr>
                </table>";
            $tabla = "
                <table style='width:100%;border-bottom: 1px solid #363636;border-collapse: collapse;'>
                    <tr style='border-bottom: 1px solid #363636;border-collapse: collapse;'>
                    <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 20px;'><strong>ITEM</strong></td>
                    <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 20px;'>--</td>
                    <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;'><strong>DESCRIPCION</strong></td>
                    <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 40px;'><strong>Cantidad</strong></td>
                    <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 40px;'><strong>MEDIDA</strong></td> 
                    <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 40px;'><strong>PRECIO</strong></td> 
                    <td style=' font-size: 8px;text-align: center; color: #000000;border: 1px solid #363636;border-collapse: collapse;width: 70px;'><strong>SUB TOTAL</strong></td>
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
                $simbol000 = $datoVenta['moneda'] == 2 ? 'S/' : '$';
                $monedahtmlDol = "<tr>
                <td style='border:none; font-size: 12px; text-align: right'>TOTAL S/</td>
                <td style='border-left: 1px solid #fff;border-collapse: collapse; font-size: 12px;  text-align: right'>$simbol000 $totalDolar</td>
            </tr>";
            }

            $simbol00022 = $datoVenta['moneda'] == 1 ? 'S/' : '$';
            // $mpdf->SetAutoPageBreak(true, 50);
            $tablaFooter = "<table style='width: 100%;'>
                <tr>
                    <td style='width: 50%;'>
                        <div style='width: 95%; padding: 3px; font-size: 10px;height: 90px; color: #000000;'>
                      $hash_Doc
                      <strong style='color: #000000;'>Observaciones:</strong><br>
                       <span style='color: #000000;'>$observacion</span> <br>
                       <strong style='color: #000000;'>Saldo Pendiente:</strong>
                      <span style='color: #000000;'>$SaldoPendientePagar</span>
                  </div>
                 </div>
                    </td>
                    <td style='width: 50%;text-align: right;'>
                        <table style='width: 50%; border: 1px solid #363636; border-collapse: collapse;margin-right: 0px;'>
                        <!--
                        <tr>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right; padding-right: 3px;'>Total Op. Gravado:</td>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right;'>$totalOpgravado</td>
                        </tr>
                        <tr>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right; padding-right: 3px;'>IGV:</td>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right;'>$igv</td>
                        </tr>
                        -->
                        <tr>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right; padding-right: 3px; color: #000000;'>Total a Pagar:</td>
                            <td style='border-left: 1px solid #363636; font-size: 9px; text-align: right; color: #000000;'>$simbol00022 $total</td>
                        </tr>
                        $monedahtmlDol
                    </table>
                    </td>
                </tr>
            </table>";


            // if (($key + 1) < sizeof($cotizaciones)) $this->mpdf->AddPage();
            $html = "
                    <table style='width: 100%; border-collapse: separate; border-spacing: 40px; position: relative;'>
                    <tr>
                        <td style='width: 50%;'>
                            $htmlEncabezado
                            $tabla_datos_cliente
                            $tabla
                            $tableconson
                            $tablaFooter
                        </td>
                        <td style='width: 50%; position: relative;'>
                            <div style='position: absolute; top: 20px; right: 80px;'>
                                <h3 style='font-size: 8px;'>COPIA&nbsp;</h3>
                            </div>
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
            $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
            $item_contador++;
        }
        $html = $html;

        $mpdf->Output("Cotizacion.pdf", 'I');
    }

    public function consolidadoPedidosCamion()
    {

        $camion = $_GET['camion'] ?? "";
        $fechaSeleccionada = $_GET['fechaSeleccionada'] ?? "";
        $fechaFinSeleccionada = $_GET['fechaFinSeleccionada'] ?? "";
        $diasVisita = $_GET['diasVisita'] ?? "";
        $ruta = $_GET['ruta'] ?? "";
        if ($ruta === 'null')
            $ruta = '';
        $mercado = $_GET['mercado'] ?? "";
        $medida = $_GET['medida'] ?? "";
        $horario = $_GET['horario'] ?? "";
        $filtros = array();
        if ($camion == "" || $fechaSeleccionada == "" || $fechaFinSeleccionada == "") {
            die("No se ingresaron todos los campos requiridos");
        }
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
            default:
                break;
        }
        if ($ruta != "") {
            foreach ($filtros as $key => $filtro) {
                $filtros[$key] = [$ruta];
            }
        }
        if ($diasVisita != "" && isset($filtros[$diasVisita])) {
            $filtros = [
                $diasVisita => $filtros[$diasVisita]
            ];
        }
        $queryClientes = "";
        if ($fechaSeleccionada != "") {
            $queryClientes .= $this->buildFiltroFechaCorte($fechaSeleccionada, $fechaFinSeleccionada, $horario);
        }
        $queryProductosHorario = $this->buildFiltroHorarioProductoCorte($fechaSeleccionada, $fechaFinSeleccionada, $horario);

        if ($mercado != "") {
            //$queryClientes .= " AND c.mercado= '$mercado'";
        }
        $arrQueryClientes = array();

        foreach ($filtros as $key => $filtro) {
            $arrQueryClientes[] = "( c.dias_visitas LIKE '{$key}%' AND c.id_ruta IN (" . implode(',', $filtro) . ") )";
        }

        if (sizeof($arrQueryClientes) > 0) {
            $queryClientes .= " AND (" . implode(' OR ', $arrQueryClientes) . ")";
        }
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        //MODIFICADO
        $mpdf = new \Mpdf\Mpdf([
            'memory_limit' => '512M',
            'format' => 'Letter',
            'margin_left' => 25,
            'margin_right' => 25,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        $sql = "SELECT co.cotizacion_id 
            FROM clientes c 
            INNER JOIN cotizaciones co ON co.id_cliente = c.id_cliente   
            WHERE 1 
            AND co.id_empresa='{$_SESSION['id_empresa']}'
            AND co.sucursal='{$_SESSION['sucursal']}'
            AND co.estado!=2 " . $queryClientes . " 
            /* WHERE 1 and co.cotizacion_id in (1991,1992,1993,1994,1995) */
            ORDER BY c.mercado ASC, c.datos ASC, co.numero ASC
            ";

        // Obtener el tipo ANTES de generar el título
        $tipo = $_GET['tipo'] ?? "";

        $html = " <div style='width: 100%;overflow: hidden;clear: both;'>";

        // Título según si se muestran mercados separados o no
        if ($tipo == "porCamionConsolidado") {
            $html .= "<h1 style='text-align:center;'>Consolidado Camión {$camion}</h1>";
        } else {
            // Consolidado por Mercados (cuando NO es porCamionConsolidado)
            if (!empty($mercado)) {
                $html .= "<h1 style='text-align:center;'>Consolidado Mercado {$mercado}</h1>";
            } else {
                $html .= "<h1 style='text-align:center;'>Consolidado por Mercados</h1>";
            }
        }

        if (!empty($camion)) {
            $html .= "<p style=''>Camion: {$camion}</p>";
        } else {
            $html .= "<h1 style='text-align:center;'>Camión no especificado</h1>";
        }
        if (!empty($fechaSeleccionada) && !empty($fechaFinSeleccionada)) {
            $html .= "<p style=''>Periodo: {$fechaSeleccionada}  al  {$fechaFinSeleccionada}</p>";
        }
        if (!empty($horario)) {
            $fechaInicioPrimerCorte = $this->getFechaInicioPrimerCorte($fechaSeleccionada);
            $horarioLabels = [
                'todos' => 'Todos',
                'primer_corte' => "Primer Corte — Pedidos base (hasta {$fechaFinSeleccionada} 08:00)",
                'segundo_corte' => "Segundo Corte — Aumentos mañana ({$fechaFinSeleccionada} 08:00 - 13:00)",
                'tercer_corte' => "Tercer Corte — Aumentos tarde/noche ({$fechaFinSeleccionada} 15:00 - 23:59)",
            ];
            $html .= "<p style=''>Horario: " . ($horarioLabels[$horario] ?? ucfirst($horario)) . "</p>";
        }

        if (!empty($diasVisita)) {
            $html .= "<p style=''>Días de visita: {$diasVisita}</p>";
        }

        if (!empty($ruta)) {
            $html .= "<p style=''>Ruta: {$ruta}</p>";
        }

        if (!empty($mercado)) {
            //$html .= "<p style=''>Mercado: {$mercado}</p>";
        }


        $html .= "</div>";


        $mercados = array();
        $validacionMercado = false;
        if ($tipo == "porCamionConsolidado") {
            $validacionMercado = true;
            $mercados[] = ['mercado' => ''];
        } else if (!empty($mercado)) {
            $mercados[] = $mercado;
        } else {
            $sqlmercados = "SELECT mercado FROM clientes
            WHERE mercado IS NOT NULL 
            AND mercado!=''
            GROUP BY mercado";
            $mercados = $this->conexion->query($sqlmercados)->fetch_all(MYSQLI_ASSOC);
        }
        $concatmerc = "";
        foreach ($mercados as $merc) {
            //$concatmerc .= "' AND c.mercado= '$merc'";
            $mercadito = $merc['mercado'];
            $concatmerc = "";
            if (!empty($mercadito)) {
                $concatmerc = " AND c.mercado= '$mercadito'";
            }

            //$queryClientes .= $concatmerc;
            $sql = "SELECT co.cotizacion_id 
            FROM clientes c 
            INNER JOIN cotizaciones co ON co.id_cliente = c.id_cliente   
            WHERE 1 
            AND co.id_empresa='{$_SESSION['id_empresa']}'
            AND co.sucursal='{$_SESSION['sucursal']}'
            AND co.estado!=2 " . $queryClientes . $concatmerc . " 
            /* WHERE 1 and co.cotizacion_id in (1991,1992,1993,1994,1995) */
            ORDER BY c.mercado ASC, c.datos ASC, co.numero ASC
            ";

            $query_productos = "SELECT p.codigo,
                pc.id_producto, p.descripcion,
                CAST(pc.presenta_cnt AS DECIMAL(10,2)) AS total_medida, pc.medida,
                SUM(pc.cantidad) AS total_cantidad, SUM(pc.cantidad * CAST(pc.presenta_cnt AS DECIMAL(10,2))) AS total_multiplicado
                    FROM productos_cotis pc
                    INNER JOIN productos p ON p.id_producto = pc.id_producto
                    WHERE pc.id_coti IN ($sql)";

            // Si se proporciona una medida, agregar la condición al SQL
            if (!empty($medida)) {
                $query_productos .= " AND pc.medida = '$medida'";
            }
            $query_productos .= $queryProductosHorario;

            $query_productos .= " GROUP BY p.codigo, pc.id_producto, p.descripcion, pc.medida, CAST(pc.presenta_cnt AS DECIMAL(10,2)) ORDER BY TRIM(p.descripcion) ASC, p.codigo ASC, CAST(pc.presenta_cnt AS DECIMAL(10,2)) ASC";

            // Ejecutar la consulta

            // $html .= "<p style=''>query: {$query_productos}</p>";
            $listaProd1 = $this->conexion->query($query_productos);

            $contador = 1;
            $total_consolidado = 0;

            $rowHTML = '';

            foreach ($listaProd1 as $prod) {
                $total_consolidado += $prod['total_cantidad'];
                $prod['codigo'] = trim($prod['codigo']);

                // M = SUM(cantidad * presenta_cnt) from SQL
                $multi_formateado = number_format($prod['total_multiplicado'], 0);

                // AHORA sí formatear
                $prod['total_cantidad'] = number_format($prod['total_cantidad'], 0);
                $prod['total_multiplicado'] = number_format($prod['total_multiplicado'], 0);
                $prod['total_medida'] = number_format($prod['total_medida'], 0);

                // Generar filas según el tipo
                if ($tipo == 'porCamionConsolidado') {
                    // Consolidado Camión: solo 4 columnas (item, Código, M, PRODUCTO)
                    $rowHTML .= "
                        <tr>
                            <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center; border-left: 1px solid #fff; padding: 2px; width: auto; white-space: nowrap;'>$contador</td>
                            <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff;  padding:2px; width: auto; '>{$prod['codigo']}</td>
                            <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center; border-left: 1px solid #fff; padding: 2px; width: auto; white-space: nowrap;'>{$prod['total_multiplicado']}</td>
                            <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: left;border-left: 1px solid #fff;  padding:2px; width: auto; '>{$prod['descripcion']}</td>
                        </tr>
                    ";
                } else {
                    // Consolidado por Mercados: 6 columnas (item, Código, M, PRODUCTO, MEDIDA, CANTIDAD)
                    $rowHTML .= "
                        <tr>
                            <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center; border-left: 1px solid #fff; padding: 2px; width: auto; white-space: nowrap;'>$contador</td>
                            <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff;  padding:2px; width: auto; '>{$prod['codigo']}</td>
                            <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center; border-left: 1px solid #fff; padding: 2px; width: auto; white-space: nowrap;'>$multi_formateado</td>
                            <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: left;border-left: 1px solid #fff;  padding:2px; width: auto; '>{$prod['descripcion']}</td>
                            <td class='' style='text-align:center; color: #000; font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding:2px;  width:auto; white-space: nowrap;'>{$prod['total_medida']}</td>
                            <td class='' style='text-align: center; color: #000; font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding: 2px; width: auto; white-space: nowrap;'>{$prod['total_cantidad']}</td>
                        </tr>
                    ";
                }
                $contador++;
            }

            if (!empty($mercadito)) {
                $html .= "<p style=''>Mercado: {$mercadito}</p>";
            }
            //$html .= "<p style=''>Mercado: {$sql}</p>";
            //$html .= "<p style=''>Mercado: {$query_productos}</p>";

            if ($tipo == 'porCamionConsolidado') {
                // Consolidado Camión: 4 columnas
                $html .= "<div style='width: 100%; padding-top: 20px;page-break-inside=always;'>
                    <table style='width:567px; border-bottom: 1px solid #fff;border-collapse: collapse;page-break-inside=avoid;'>
                        <tr style='border-bottom: 1px solid #fff;'>
                            <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff; padding:0;'><strong>item</strong></td>
                            <td style=' font-size: 12px; color: #000;border: 1px solid #fff;border-collapse: collapse; padding: 0;'><strong>Código</strong></td>
                            <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff; padding:0;'><strong>M</strong></td>
                            <td style=' font-size: 12px;text-align: left; color: #000;border: 1px solid #fff;'><strong>PRODUCTO</strong></td>
                        </tr>
                        $rowHTML
                        <tr>
                            <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff;color: white; padding:2px;'>.</td>
                            <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff; padding:2px;'> </td>
                            <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-bottom: 1px solid #fff;  padding:2px;'> </td>
                            <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;'> </td>
                        </tr>
                    </table>
                </div>";
            } else {
                // Consolidado por Mercados: 6 columnas
                $html .= "<div style='width: 100%; padding-top: 20px;page-break-inside=always;'>
                    <table style='width:567px; border-bottom: 1px solid #fff;border-collapse: collapse;page-break-inside=avoid;'>
                        <tr style='border-bottom: 1px solid #fff;'>
                            <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff; padding:0;'><strong>item</strong></td>
                            <td style=' font-size: 12px; color: #000;border: 1px solid #fff;border-collapse: collapse; padding: 0;'><strong>Código</strong></td>
                            <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff; padding:0;'><strong>M</strong></td>
                            <td style=' font-size: 12px;text-align: left; color: #000;border: 1px solid #fff;'><strong>PRODUCTO</strong></td>
                            <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff;border-collapse: collapse;  padding:2px;'><strong>MEDIDA</strong></td>
                            <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff;border-collapse: collapse;  padding:2px;'><strong>CANTIDAD</strong></td>
                        </tr>
                        $rowHTML
                        <tr>
                            <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff;color: white; padding:2px;'>.</td>
                            <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff; padding:2px;'> </td>
                            <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-bottom: 1px solid #fff;  padding:2px;'> </td>
                            <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;'> </td>
                            <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-right: 1px solid #fff;border-bottom: 2px solid #000;'> </td>
                            <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-right: 1px solid #fff;border-bottom: 2px solid #000;'> </td>
                        </tr>
                        <tr>
                            <td colspan='4'></td>
                            <td class='' style='text-align:right;font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding:0;  width: 50px; white-space: nowrap;'>Total</td>
                            <td class='' style='text-align:right;font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding:0;  width: 50px; white-space: nowrap;'>{$total_consolidado}</td>
                        </tr>
                    </table>
                </div>";
            }
        }
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        $mpdf->Output("Consolidado_camion_{$camion}.pdf", 'I');
    }

    public function consolidadoTotalPedidosCamion()
    {

        $camion = $_GET['camion'] ?? "";
        $fechaSeleccionada = $_GET['fechaSeleccionada'] ?? "";
        $fechaFinSeleccionada = $_GET['fechaFinSeleccionada'] ?? "";
        $diasVisita = $_GET['diasVisita'] ?? "";
        $ruta = $_GET['ruta'] ?? "";
        if ($ruta === 'null')
            $ruta = '';
        $mercado = $_GET['mercado'] ?? "";
        $medida = $_GET['medida'] ?? "";
        $horario = $_GET['horario'] ?? "";
        $tipo = $_GET['tipo'] ?? "";
        $filtros = array();
        if ($fechaSeleccionada == "" || $fechaFinSeleccionada == "") {
            die("No se ingresaron todos los campos requiridos");
        }
        $filtros = [
            [
                'lunes' => ['1', '7'],
                'martes' => ['5', '7'],
                'miercoles' => ['5'],
                'jueves' => ['1', '7'],
                'viernes' => ['6', '7'],
                'sabado' => ['7', '8'],

            ],
            [
                'lunes' => ['3', '6'],
                'martes' => ['1', '3'],
                'miercoles' => ['1', '3'],
                'jueves' => ['6', '3'],
                'viernes' => ['3', '5'],
                'sabado' => ['3', '6'],

            ],
            [
                'miercoles' => ['6', '7'],
                'viernes' => ['8', '2'],
                'sabado' => ['1', '5'],

            ]
        ];

        $html = " <div style='width: 100%;overflow: hidden;clear: both;'>";
        if (!empty($fechaSeleccionada) && !empty($fechaFinSeleccionada)) {
            $html .= "<p style=''>Periodo: {$fechaSeleccionada}  al  {$fechaFinSeleccionada}</p>";
        }
        if (!empty($horario)) {
            $fechaInicioPrimerCorte = $this->getFechaInicioPrimerCorte($fechaSeleccionada);
            $horarioLabels = [
                'todos' => 'Todos',
                'primer_corte' => "Primer Corte — Pedidos base (hasta {$fechaFinSeleccionada} 08:00)",
                'segundo_corte' => "Segundo Corte — Aumentos mañana ({$fechaFinSeleccionada} 08:00 - 13:00)",
                'tercer_corte' => "Tercer Corte — Aumentos tarde/noche ({$fechaFinSeleccionada} 13:00 - 23:59)",
            ];
            $html .= "<p style=''>Horario: " . ($horarioLabels[$horario] ?? ucfirst($horario)) . "</p>";
        }

        if (!empty($diasVisita)) {
            $html .= "<p style=''>Días de visita: {$diasVisita}</p>";
        }

        if (!empty($ruta)) {
            $html .= "<p style=''>Ruta: {$ruta}</p>";
        }

        if (!empty($mercado)) {
            $html .= "<p style=''>Mercado: {$mercado}</p>";
        }
        $html .= "</div>";
        #

        if ($ruta != "") {
            foreach ($filtros as $key => $filtro_a) {
                foreach ($filtro_a as $clave => $filtro) {
                    $filtros[$key][$clave] = [$ruta];
                }
            }
        }
        $new_filtros = array();
        if ($diasVisita != "") {
            foreach ($filtros as $key => $filtro_a) {
                $new_filtro = array();
                foreach ($filtro_a as $clave => $filtro) {
                    if ($clave == $diasVisita) {
                        $new_filtro = [
                            $diasVisita => $filtro
                        ];
                    }
                }
                if (isset($new_filtro[$diasVisita])) {
                    $filtros[$key] = $new_filtro;
                    $new_filtros[] = $new_filtro;
                }
            }
            // $filtros = [
            //     $diasVisita => $filtros[$diasVisita]
            // ];
        }
        $filtros = $new_filtros;
        #
        $queryClientes = "";
        if ($fechaSeleccionada != "") {
            $queryClientes .= $this->buildFiltroFechaCorte($fechaSeleccionada, $fechaFinSeleccionada, $horario);
        }
        $queryProductosHorario = $this->buildFiltroHorarioProductoCorte($fechaSeleccionada, $fechaFinSeleccionada, $horario);

        if ($mercado != "") {
            $queryClientes .= " AND c.mercado= '$mercado'";
        }
        $arrQueryClientes = array();

        foreach ($filtros as $key => $filtro_a) {
            foreach ($filtro_a as $clave => $filtro) {
                $arrQueryClientes[] = "( c.dias_visitas LIKE '{$clave}%' AND c.id_ruta IN (" . implode(',', $filtro) . ") )";
            }
        }

        if (sizeof($arrQueryClientes) > 0) {
            $queryClientes .= " AND (" . implode(' OR ', $arrQueryClientes) . ")";
        }

        $sql = "SELECT co.cotizacion_id 
        FROM clientes c 
        INNER JOIN cotizaciones co ON co.id_cliente = c.id_cliente   
        WHERE 1 
        AND co.id_empresa='{$_SESSION['id_empresa']}'
        AND co.sucursal='{$_SESSION['sucursal']}'
        AND co.estado!=2 " . $queryClientes . " 
        /* WHERE 1 and co.cotizacion_id in (1991,1992,1993,1994,1995) */
        ORDER BY c.mercado ASC
        ";

        $tipo = "porCamionConsolidado";
        $mercados = array();
        $validacionMercado = false;
        if ($tipo == "porCamionConsolidado") {
            $validacionMercado = true;
            $mercados[] = ['mercado' => ''];
        } else if (!empty($mercado)) {
            $mercados[] = $mercado;
        } else {
            $sqlmercados = "SELECT mercado FROM clientes
            WHERE mercado IS NOT NULL 
            AND mercado!=''
            GROUP BY mercado";
            $mercados = $this->conexion->query($sqlmercados)->fetch_all(MYSQLI_ASSOC);
        }
        $concatmerc = "";
        foreach ($mercados as $merc) {
            //$concatmerc .= "' AND c.mercado= '$merc'";
            $mercadito = $merc['mercado'];
            $concatmerc = "";
            if (!empty($mercadito)) {
                $concatmerc = " AND c.mercado= '$mercadito'";
            }

            //$queryClientes .= $concatmerc;
            $sql_query = "SELECT co.cotizacion_id 
            FROM clientes c 
            INNER JOIN cotizaciones co ON co.id_cliente = c.id_cliente   
            WHERE 1 
            AND co.id_empresa='{$_SESSION['id_empresa']}'
            AND co.sucursal='{$_SESSION['sucursal']}'
            AND co.estado!=2 " . $queryClientes . $concatmerc . " 
            /* WHERE 1 and co.cotizacion_id in (1991,1992,1993,1994,1995) */
            ORDER BY c.mercado ASC, c.datos ASC, co.numero ASC
            ";

            // Obtener los IDs de cotizaciones
            $result_cotis = $this->conexion->query($sql_query);
            $cotizacion_ids = array();
            while ($row = $result_cotis->fetch_assoc()) {
                $cotizacion_ids[] = $row['cotizacion_id'];
            }
            $sql = implode(',', $cotizacion_ids);

            if (empty($sql)) {
                $sql = "0"; // Si no hay cotizaciones, usar 0 para evitar error SQL
            }

            $query_productos = "SELECT p.codigo,
            p.descripcion,
            CAST(pc.presenta_cnt AS DECIMAL(10,2)) AS total_medida,
            pc.medida,
            p.peso_bruto,
            SUM(pc.cantidad) AS total_cantidad, SUM(pc.cantidad * CAST(pc.presenta_cnt AS DECIMAL(10,2))) AS total_multiplicado
            FROM productos_cotis pc
            INNER JOIN productos p ON p.id_producto = pc.id_producto
            WHERE pc.id_coti IN ($sql)";

            // Si se proporciona una medida, agregar la condición al SQL
            if (!empty($medida)) {
                $query_productos .= " AND pc.medida = '$medida'";
            }
            $query_productos .= $queryProductosHorario;

            $query_productos .= " GROUP BY p.codigo, pc.id_producto, p.descripcion, pc.medida, CAST(pc.presenta_cnt AS DECIMAL(10,2)) ORDER BY TRIM(p.descripcion) ASC, p.codigo ASC, CAST(pc.presenta_cnt AS DECIMAL(10,2)) ASC";
            // Ejecutar la consulta

            // $html .= "<p style=''>query: {$query_productos}</p>";
            $listaProd1 = $this->conexion->query($query_productos);

            $contador = 1;
            $total_consolidado = 0;

            $rowHTML = '';

            foreach ($listaProd1 as $prod) {
                $total_consolidado += $prod['total_cantidad'];
                $prod['codigo'] = trim($prod['codigo']);

                // M = suma de (presenta_cnt × cantidad) de todas las presentaciones agrupadas
                $multi_formateado = number_format($prod['total_multiplicado'], 0);

                // Formatear para mostrar
                $prod['total_cantidad'] = number_format($prod['total_cantidad'], 0);

                $rowHTML .= "
                <tr>
                    <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center; border-left: 1px solid #fff; padding: 2px; width:30px; white-space: nowrap;'>$contador</td>
                    <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff; padding:2px; width:65px; white-space: nowrap;'>{$prod['codigo']}</td>
                    <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center; border-left: 1px solid #fff; padding: 2px; width:35px; white-space: nowrap;'>$multi_formateado</td>
                    <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: left;border-left: 1px solid #fff; padding:2px;'>{$prod['descripcion']}</td>
                    <!-- <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff;  padding:2px; white-space: nowrap;'>{$prod['medida']}</td> -->
                    <!-- <td class='' style='text-align: center; color: #000; font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding: 2px; white-space: nowrap;'>{$prod['total_cantidad']}</td> -->
                </tr>
            ";
                $contador++;
            }
            //$html .= "<p style=''>Mercado: {$sql}</p>";
            //$html .= "<p style=''>Mercado: {$query_productos}</p>";

            $html .= "<h2 style='text-align:center'>Consolidado Total Camiones</h2>";
            $html .= "<div style='width: 100%; padding-top: 20px;page-break-inside=always;'>
            <table style='width:100%; border-bottom: 1px solid #fff;border-collapse: collapse;page-break-inside=avoid;'>
                <tr style='border-bottom: 1px solid #fff;'>
                    <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff; padding:0; width:30px; white-space: nowrap;'><strong>item</strong></td>
                    <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff; padding:0; width:65px; white-space: nowrap;'><strong>Código</strong></td>
                    <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff; padding:0; width:35px; white-space: nowrap;'><strong>M</strong></td>
                    <td style=' font-size: 12px;text-align: left; color: #000;border: 1px solid #fff;'><strong>PRODUCTO</strong></td>
                    <!-- <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff;border-collapse: collapse; padding:2px; white-space: nowrap;'><strong>UNIDAD MEDIDA</strong></td> -->
                    <!-- <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff;border-collapse: collapse; padding:2px; white-space: nowrap;'><strong>CANTIDAD</strong></td> -->
                </tr>
                $rowHTML
                <tr>
                    <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 2px solid #000;color: white; padding:2px;'>.</td>
                    <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 2px solid #000; padding:2px;'> </td>
                    <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-bottom: 2px solid #000; padding:2px;'> </td>
                    <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-bottom: 2px solid #000;'> </td>
                </tr>
                <tr>
                    <td colspan='3'></td>
                    <td class='' style='text-align:right;font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding:0; white-space: nowrap;'>Total: {$total_consolidado}</td>
                </tr>
            </table>
        </div>
        <div>
        </div>";
        }

        // exit($html);
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        //MODIFICADO
        $mpdf = new \Mpdf\Mpdf([
            'memory_limit' => '512M',
            'format' => 'Letter',
            'margin_left' => 25,
            'margin_right' => 25,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        $mpdf->Output("Consolidado_camion_{$camion}.pdf", 'I');
    }

    public function consolidadoTotalPedidosCamion2()
    {

        $camion = $_GET['camion'] ?? "";
        $fechaSeleccionada = $_GET['fechaSeleccionada'] ?? "";
        $fechaFinSeleccionada = $_GET['fechaFinSeleccionada'] ?? "";
        $diasVisita = $_GET['diasVisita'] ?? "";
        $ruta = $_GET['ruta'] ?? "";
        if ($ruta === 'null')
            $ruta = '';
        $mercado = $_GET['mercado'] ?? "";
        $medida = $_GET['medida'] ?? "";
        $horario = $_GET['horario'] ?? "";
        $tipo = $_GET['tipo'] ?? "";
        $filtros = array();
        if ($fechaSeleccionada == "" || $fechaFinSeleccionada == "") {
            die("No se ingresaron todos los campos requiridos");
        }


        $html = " <div style='width: 100%;overflow: hidden;clear: both;'>";
        if (!empty($fechaSeleccionada) && !empty($fechaFinSeleccionada)) {
            $html .= "<p style=''>Periodo: {$fechaSeleccionada}  al  {$fechaFinSeleccionada}</p>";
        }
        if (!empty($horario)) {
            $fechaInicioPrimerCorte = $this->getFechaInicioPrimerCorte($fechaSeleccionada);
            $horarioLabels = [
                'todos' => 'Todos',
                'primer_corte' => "Primer Corte — Pedidos base (hasta {$fechaFinSeleccionada} 08:00)",
                'segundo_corte' => "Segundo Corte — Aumentos mañana ({$fechaFinSeleccionada} 08:00 - 13:00)",
                'tercer_corte' => "Tercer Corte — Aumentos tarde/noche ({$fechaFinSeleccionada} 13:00 - 23:59)",
            ];
            $html .= "<p style=''>Horario: " . ($horarioLabels[$horario] ?? ucfirst($horario)) . "</p>";
        }

        if (!empty($diasVisita)) {
            $html .= "<p style=''>Días de visita: {$diasVisita}</p>";
        }

        if (!empty($ruta)) {
            $html .= "<p style=''>Ruta: {$ruta}</p>";
        }

        if (!empty($mercado)) {
            $html .= "<p style=''>Mercado: {$mercado}</p>";
        }
        $html .= "</div>";
        #
        for ($camion = 1; $camion <= 3; $camion++) {
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
                default:
                    break;
            }
            if ($ruta != "") {
                foreach ($filtros as $key => $filtro) {
                    $filtros[$key] = [$ruta];
                }
            }
            if ($diasVisita != "" && isset($filtros[$diasVisita])) {
                $filtros = [
                    $diasVisita => $filtros[$diasVisita]
                ];
            }
            $queryClientes = "";
            if ($fechaSeleccionada != "") {
                $queryClientes .= $this->buildFiltroFechaCorte($fechaSeleccionada, $fechaFinSeleccionada, $horario);
            }
            $queryProductosHorario = $this->buildFiltroHorarioProductoCorte($fechaSeleccionada, $fechaFinSeleccionada, $horario);

            if ($mercado != "") {
                //$queryClientes .= " AND c.mercado= '$mercado'";
            }
            $arrQueryClientes = array();

            foreach ($filtros as $key => $filtro) {
                $arrQueryClientes[] = "( c.dias_visitas LIKE '{$key}%' AND c.id_ruta IN (" . implode(',', $filtro) . ") )";
            }

            if (sizeof($arrQueryClientes) > 0) {
                $queryClientes .= " AND (" . implode(' OR ', $arrQueryClientes) . ")";
            }

            $sql = "SELECT co.cotizacion_id 
        FROM clientes c 
        INNER JOIN cotizaciones co ON co.id_cliente = c.id_cliente   
        WHERE 1 
        AND co.id_empresa='{$_SESSION['id_empresa']}'
        AND co.sucursal='{$_SESSION['sucursal']}'
        AND co.estado!=2 " . $queryClientes . " 
        /* WHERE 1 and co.cotizacion_id in (1991,1992,1993,1994,1995) */
        ORDER BY c.mercado ASC
        ";

            $mercados = array();
            $validacionMercado = false;
            if ($tipo == "porCamionConsolidado") {
                $validacionMercado = true;
                $mercados[] = ['mercado' => ''];
            } else if (!empty($mercado)) {
                $mercados[] = $mercado;
            } else {
                $sqlmercados = "SELECT mercado FROM clientes
        WHERE mercado IS NOT NULL 
        AND mercado!=''
        GROUP BY mercado";
                $mercados = $this->conexion->query($sqlmercados)->fetch_all(MYSQLI_ASSOC);
            }
            $concatmerc = "";
            foreach ($mercados as $merc) {
                //$concatmerc .= "' AND c.mercado= '$merc'";
                $mercadito = $merc['mercado'];
                $concatmerc = "";
                if (!empty($mercadito)) {
                    $concatmerc = " AND c.mercado= '$mercadito'";
                }

                //$queryClientes .= $concatmerc;
                $sql = "SELECT co.cotizacion_id 
        FROM clientes c 
        INNER JOIN cotizaciones co ON co.id_cliente = c.id_cliente   
        WHERE 1 
        AND co.id_empresa='{$_SESSION['id_empresa']}'
        AND co.sucursal='{$_SESSION['sucursal']}'
        AND co.estado!=2 " . $queryClientes . $concatmerc . " 
        /* WHERE 1 and co.cotizacion_id in (1991,1992,1993,1994,1995) */
        ORDER BY c.mercado ASC
        ";

                $query_productos = "SELECT p.codigo,
            pc.id_producto, p.descripcion,
                CAST(pc.presenta_cnt AS DECIMAL(10,2)) AS total_medida, pc.medida,
                SUM(pc.cantidad) AS total_cantidad, SUM(pc.cantidad * CAST(pc.presenta_cnt AS DECIMAL(10,2))) AS total_multiplicado
                FROM productos_cotis pc
                INNER JOIN productos p ON p.id_producto = pc.id_producto
                WHERE pc.id_coti IN ($sql)";

                // Si se proporciona una medida, agregar la condición al SQL
                if (!empty($medida)) {
                    $query_productos .= " AND pc.medida = '$medida'";
                }
                $query_productos .= $queryProductosHorario;

                $query_productos .= " GROUP BY p.codigo, pc.id_producto, p.descripcion, pc.medida, CAST(pc.presenta_cnt AS DECIMAL(10,2)) ORDER BY TRIM(p.descripcion) ASC, p.codigo ASC, CAST(pc.presenta_cnt AS DECIMAL(10,2)) ASC";
                // Ejecutar la consulta

                // $html .= "<p style=''>query: {$query_productos}</p>";
                $listaProd1 = $this->conexion->query($query_productos);

                $contador = 1;
                $total_consolidado = 0;

                $rowHTML = '';

                foreach ($listaProd1 as $prod) {
                    $total_consolidado += $prod['total_cantidad'];
                    $prod['codigo'] = trim($prod['codigo']);
                    $prod['total_cantidad'] = number_format($prod['total_cantidad'], 0);
                    $prod['total_multiplicado'] = number_format($prod['total_multiplicado'], 0);
                    $multi = $prod['total_cantidad'] * $prod['total_medida'];

                    // $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);
                    $rowHTML .= "
                    <tr>
                        <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center; border-left: 1px solid #fff; padding: 2px; width: auto; white-space: nowrap;'>$contador</td>
                        <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff;  padding:2px; width: auto; '>{$prod['codigo']}</td>
                        <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center; border-left: 1px solid #fff; padding: 2px; width: auto; white-space: nowrap;'>{$prod['total_multiplicado']}</td>
                        <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: left;border-left: 1px solid #fff;  padding:2px; width: auto; '>{$prod['descripcion']}</td>
                    </tr>
                "; // CAMBIO: M y CANTIDAD muestran el mismo valor
                    $contador++;
                }
                //$html .= "<p style=''>Mercado: {$sql}</p>";
                //$html .= "<p style=''>Mercado: {$query_productos}</p>";

                $html .= "<h2 style='text-align:center'>Consolidado Camion {$camion}</h2>";
                $html .= "<div style='width: 100%; padding-top: 20px;page-break-inside=always;'>
                <table style='width:567px; border-bottom: 1px solid #fff;border-collapse: collapse;page-break-inside=avoid;'>
                    <tr style='border-bottom: 1px solid #fff;'>
                        <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff; padding:0;'><strong>item</strong></td>
                        <td style=' font-size: 12px; color: #000;border: 1px solid #fff;border-collapse: collapse; padding: 0;'><strong>Código</strong></td>
                        <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff; padding:0;'><strong>M</strong></td>
                        <td style=' font-size: 12px;text-align: left; color: #000;border: 1px solid #fff;'><strong>PRODUCTO</strong></td>
                    </tr>
                    $rowHTML
                    <tr>
                        <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff;color: white; padding:2px;'>.</td>
                        <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff; padding:2px;'> </td>
                        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-bottom: 1px solid #fff;  padding:2px;'> </td>
                        <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;'> </td>
                    </tr>
                </table>
            </div>
            <div>
            </div>";
            }
        }
        // exit($html);
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        //MODIFICADO
        $mpdf = new \Mpdf\Mpdf([
            'memory_limit' => '512M',
            'format' => 'Letter',
            'margin_left' => 25,
            'margin_right' => 25,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        $mpdf->Output("Consolidado_camion_{$camion}.pdf", 'I');
    }
    private function getFechaInicioPrimerCorte($fechaInicio)
    {
        if (date('N', strtotime($fechaInicio)) == 7) {
            return date('Y-m-d', strtotime($fechaInicio . ' -1 day'));
        }
        return $fechaInicio;
    }

    private function buildFiltroFechaCorte($fechaInicio, $fechaFin, $horario = "")
    {
        $fechaInicioFiltro = $fechaInicio;
        if ($horario == 'primer_corte') {
            $fechaInicioFiltro = $this->getFechaInicioPrimerCorte($fechaInicio);
        }
        return " AND DATE(co.fecha) BETWEEN '$fechaInicioFiltro' AND '$fechaFin' ";
    }

    private function buildFiltroHorarioCorte($fechaInicio, $fechaFin, $horario)
    {
        if ($horario == "" || $horario == "todos") {
            return "";
        }
        $fechaInicioPrimerCorte = $this->getFechaInicioPrimerCorte($fechaInicio);
        if ($horario == 'primer_corte') {
            return " AND co.fecha_registro < '{$fechaFin} 08:00:00' ";
        }
        if ($horario == 'segundo_corte') {
            return " AND co.fecha_registro >= '{$fechaFin} 08:00:00' AND co.fecha_registro < '{$fechaFin} 13:00:00' ";
        }
        if ($horario == 'tercer_corte') {
            return " AND co.fecha_registro >= '{$fechaFin} 13:00:00' AND co.fecha_registro <= '{$fechaFin} 23:59:59' ";
        }
        return "";
    }

    private function buildFiltroHorarioProductoCorte($fechaInicio, $fechaFin, $horario)
    {
        if ($horario == 'primer_corte') {
            return " AND (pc.fecha_registro IS NULL OR pc.fecha_registro < '{$fechaFin} 08:00:00') ";
        }
        return str_replace('co.fecha_registro', 'pc.fecha_registro', $this->buildFiltroHorarioCorte($fechaInicio, $fechaFin, $horario));
    }

    public function comprobantePedidoPorClientes()
    {
        $camion = $_GET['camion'] ?? "";
        $fechaSeleccionada = $_GET['fechaSeleccionada'] ?? "";
        $fechaFinSeleccionada = $_GET['fechaFinSeleccionada'] ?? "";
        $diasVisita = $_GET['diasVisita'] ?? "";
        $ruta = $_GET['ruta'] ?? "";
        if ($ruta === 'null')
            $ruta = '';
        $mercado = $_GET['mercado'] ?? "";
        $filtros = array();
        if ($camion == "" || $fechaSeleccionada == "" || $fechaFinSeleccionada == "") {
            die("No se ingresaron todos los campos requiridos");
        }
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
            default:
                break;
        }



        if ($ruta != "") {
            foreach ($filtros as $key => $filtro) {
                $filtros[$key] = [$ruta];
            }
        }

        if ($diasVisita != "" && isset($filtros[$diasVisita])) {
            $filtros = [
                $diasVisita => $filtros[$diasVisita]
            ];
        }
        $queryClientes = "";
        if ($fechaSeleccionada != "") {
            $queryClientes .= " AND co.fecha between '$fechaSeleccionada' AND '$fechaFinSeleccionada' ";
        }
        if ($mercado != "") {
            $queryClientes .= " AND c.mercado= '$mercado'";
        }
        $arrQueryClientes = array();

        foreach ($filtros as $key => $filtro) {
            $arrQueryClientes[] = "( c.dias_visitas = '{$key}' AND c.id_ruta IN (" . implode(',', $filtro) . ") )";
        }

        if (sizeof($arrQueryClientes) > 0) {
            $queryClientes .= " AND (" . implode(' OR ', $arrQueryClientes) . ")";
        }


        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $mpdf = new \Mpdf\Mpdf([
            'memory_limit' => '512M',
            'format' => 'Letter',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 0,
            'margin_bottom' => 10,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        $sql = "SELECT
                `co`.`cotizacion_id` AS `cotizacion_id`,
                `co`.`numero` AS `numero`,
                `co`.`fecha` AS `fecha`,
                `co`.`moneda` AS `moneda`,
                `co`.`cm_tc` AS `cm_tc`,
                `co`.`id_tido` AS `id_tido`,
                `c`.`documento`,
                `c`.`datos`,
                `c`.`mercado`,
                `co`.`total` AS `total`,
                `co`.`estado` AS `estado`,
                `co`.`id_usuario` AS `usuario`
            FROM
                `cotizaciones` `co`
                LEFT JOIN `documentos_sunat` `ds` ON `co`.`id_tido` = `ds`.`id_tido`
                LEFT JOIN `clientes` `c` ON `co`.`id_cliente` = `c`.`id_cliente`
            WHERE 1 
            AND co.id_empresa='{$_SESSION['id_empresa']}'
            AND co.sucursal='{$_SESSION['sucursal']}'
            AND co.estado!=2 " . $queryClientes . " 
            ORDER BY c.mercado ASC, c.datos ASC, co.numero ASC
            ";
        /* WHERE co.cotizacion_id=1849"; */

        $resultado = $this->conexion->query($sql);
        // Manejo de errores
        if (!$resultado) {
            die("Error en la consulta: " . $this->conexion->error);
        }

        // $cotizacion = $resultado->fetch_assoc();
        $cotizaciones = array();
        while ($row = $resultado->fetch_assoc()) {
            $cotizaciones[] = $row;
        }

        // Verificar si se encontró una cotización
        if (sizeof($cotizaciones) <= 0) {
            die("No se encontraron cotizaciones para camion: " . $camion);
        }


        $contador = 1;

        $rowHTML = '';
        $fecha_ini = explode('-', $fechaSeleccionada);
        $fecha_fin = explode('-', $fechaFinSeleccionada);
        $time = date('d/m/Y h:i:s A');
        foreach ($cotizaciones as $cotizacion) {
            // echo '<pre>';
            // print_r($cotizacion);
            // echo '</pre>';
            // exit();
            // $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);


            $rowHTML .= "
            <tr>
                <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center; border-left: 1px solid #fff; padding: 0; white-space: nowrap;width: 5%;'>{$contador}</td>
                <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center; border-left: 1px solid #fff; padding: 0; white-space: nowrap;width: 15%;'>{$cotizacion['documento']}</td>

                <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: left;border-left: 1px solid #fff;  padding:0;width: 45%;'>{$cotizacion['datos']}</td>
                
                <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff;  padding:0;width: 10%; '>{$cotizacion['mercado']}</td>
                
                <td class='' style='font-weight: bold; color: #000; font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-left: 1px solid #fff;  padding:0; white-space: nowrap;width: 15%;'>{$cotizacion['numero']}</td>
                
                <td class='' style='text-align:right;font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-left: 1px solid #fff; padding:0; white-space: nowrap;width: 10%;'>{$cotizacion['total']}</td>
            </tr>
        ";
            $contador++;
        }

        $html = "
    <div style='width: 100%; padding-top: 60px; overflow: hidden;clear: both;'>
        <p>{$time}</p>
        <h1 style='text-align:center;'>Detalle por Cliente</h1>
        <p style='text-align:center;'> <strong>DEL</strong> {$fecha_ini[2]}/{$fecha_ini[1]}/{$fecha_ini[0]} <strong>AL</strong> {$fecha_fin[2]}/{$fecha_fin[1]}/{$fecha_fin[0]}</p>
    </div>
    <div style='width: 100%; padding-top: 20px; margin-left: 20px'>
        <table style='width:567px; border-bottom: 1px solid #fff;border-collapse: collapse;'>
            <tr style='border-bottom: 1px solid #fff;border-collapse: collapse;'>
                <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff; padding:0;'><strong>ITEM</strong></td>
                <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff; padding:0;'><strong>DOC</strong></td>
                <td style=' font-size: 12px; color: #000;border: 1px solid #fff;border-collapse: collapse; padding: 0;'><strong>Cliente</strong></td>
                <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>MERCADO</strong></td> 
                <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>DOC.PEDIDO</strong></td>
                <td style=' font-size: 12px;text-align: center; color: #000;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>TOTAL</strong></td> 
            </tr>
            $rowHTML
            <tr>
                <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff;color: white; padding:0;'>.</td>
                <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff; padding:0;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-bottom: 1px solid #fff;  padding:0;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-bottom: 1px solid #fff;  padding:0;'> </td> 
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-right: 1px solid #fff;border-bottom: 1px solid #fff;'> </td>
                <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-right: 1px solid #fff;border-bottom: 1px solid #fff;'> </td>
            </tr>
        </table>
    </div>
    <div>
    </div>";
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);








        $mpdf->Output("Consolidado_camion_{$camion}.pdf", 'I');
    }
}
