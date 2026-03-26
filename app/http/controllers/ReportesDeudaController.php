<?php

require_once 'utils/lib/mpdf/vendor/autoload.php';
require_once 'utils/lib/vendor/autoload.php';

require_once "app/models/Cobranza.php";
require_once "app/models/ReporteDeudas.php";
// require_once 'utils/lib/mpdf/vendor/setasign/fpdi/src/autoload.php';




use Endroid\QrCode\QrCode;
use Luecano\NumeroALetras\NumeroALetras;



class ReportesDeudaController extends Controller
{
  private $mpdf;
  private $conexion;
  private $reporte;

  public function __construct()
  {
    $this->mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 0]);
    $this->conexion = (new Conexion())->getConexion();
    $this->reporte = new ReporteDeudas();
  }

  public function obtenerFiltros($tipo,$id_cliente,$id_vendedor,$camion,$diasVisita,$ruta)
  {    
    #
    $titulo = "";
    $whereCliente = "";
    $whereVendedor = "";
    $vendedores = null;

    if ($id_cliente != "") {
      $sql = "SELECT
                  *
              FROM
                  clientes c
              where c.id_cliente = {$id_cliente}";
      $fila = mysqli_query($this->conexion, $sql);
      $clientes = mysqli_fetch_all($fila, MYSQLI_ASSOC);
      $titulo = "DEUDA CLIENTE " . strtoupper($clientes[0]['datos']);
      $whereCliente = " AND c.id_cliente={$id_cliente}";
    }
    if ($id_vendedor != "") {
      $sql = "SELECT
                  *
              FROM
                  usuarios u
              where u.usuario_id = {$id_vendedor}";
      $fila = mysqli_query($this->conexion, $sql);
      $vendedores = mysqli_fetch_all($fila, MYSQLI_ASSOC);
      $whereVendedor = " AND cu.id_usuario={$id_vendedor} ";
    }
    #
    $titulo = "";
    switch($tipo){
      case '1': # por vendedor
        $titulo = "Reporte de cobros";
      break;
      case '2': # por vendedor
        $titulo = "Cobranza de Vendedor " . strtoupper($vendedores[0]['nombres']);
      break;
      case '3': # por ruta
        $titulo = "Deudas para Ruta " . $ruta;
      break;
    }
    #
    $filtros = [];
    $arrQueryClientes = [];
    // Filtro por mercado
    $whereRuta = '';
    if (!empty($ruta)) {
      $whereRuta = "AND c.id_ruta = '$ruta'";
    }

    // Filtros por camión
    switch ($camion) {
      case '1':
        $filtros = [
          'lunes'     => ['1', '7'],
          'martes'    => ['5', '7'],
          'miercoles' => ['5'],
          'jueves'    => ['1', '7'],
          'viernes'   => ['6', '7'],
          'sabado'    => ['7', '8'],
        ];
        break;
      case '2':
        $filtros = [
          'lunes'     => ['3', '6'],
          'martes'    => ['1', '3'],
          'miercoles' => ['1', '3'],
          'jueves'    => ['6', '3'],
          'viernes'   => ['3', '5'],
          'sabado'    => ['3', '6'],
        ];
        break;
      case '3':
        $filtros = [
          'miercoles' => ['6', '7'],
          'viernes'   => ['8', '2'],
          'sabado'    => ['1', '5'],
        ];
        break;
      default:
        break;
    }

    // Si se seleccionó un día específico
    if ($diasVisita != "" && isset($filtros[$diasVisita])) {
      $filtros = [
        $diasVisita => $filtros[$diasVisita]
      ];
    }
    if ($ruta != "") {
      foreach ($filtros as $key => $filtro) {
        $filtros[$key] = [$ruta];
      }
    }
    foreach ($filtros as $key => $filtro) {
      $arrQueryClientes[] = "( LOWER(c.dias_visitas) = LOWER('{$key}') AND c.id_ruta IN (" . implode(',', $filtro) . ") )";
    }

    $whereClientes = '';
    if (!empty($arrQueryClientes)) {
      $whereClientes = "AND (" . implode(' OR ', $arrQueryClientes) . ")";
    }
    $whereDiasVisita = '';
    if (!empty($diasVisita)) {
      $whereDiasVisita = "AND LOWER(c.dias_visitas) = LOWER('$diasVisita')";
    }
    #
    return [
      'whereCliente' => $whereCliente,
      'whereVendedor' => $whereVendedor,
      'whereClientes' => $whereClientes,
      'whereDiasVisita' => $whereDiasVisita,
      'whereRuta' => $whereRuta,
      'titulo' => $titulo,
    ];
  }

  public function reporteCobros($id_vendedor){
    $id_cliente = $_GET['id_cliente'];
    $id_vendedor = $_GET['id_vendedor'];
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];
    $camion = $_GET['camion'];
    $diasVisita = $_GET['diasVisita'];
    $ruta = $_GET['ruta'];
    $filtros = $this->obtenerFiltros(1,$id_cliente,$id_vendedor,$camion,$diasVisita,$ruta);

    $whereCliente = $filtros['whereCliente'];
    $whereVendedor = $filtros['whereVendedor'];
    $whereClientes = $filtros['whereClientes'];
    $whereDiasVisita = $filtros['whereDiasVisita'];
    $whereRuta = $filtros['whereRuta'];
    $titulo = $filtros['titulo'];
    $titulo_fecha = '';
    #filtro fechas
    $whereFecha = '';
    if ($fecha_inicio && $fecha_fin) {
        $whereFecha = "AND DATE(co.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $titulo_fecha = 'Desde el día '.$fecha_inicio.' hasta '.$fecha_fin;
      } elseif ($fecha_inicio) {
        $whereFecha = "AND DATE(co.fecha) >= '$fecha_inicio'";
        $titulo_fecha = 'Desde el día '.$fecha_inicio;
      } elseif ($fecha_fin) {
        $whereFecha = "AND DATE(co.fecha) <= '$fecha_fin'";
        $titulo_fecha = 'Hasta el día '.$fecha_fin;
    }

    $listaDeuda = $this->reporte->getAllCobros($whereCliente, $whereVendedor,$whereFecha, $whereClientes, $whereDiasVisita, $whereRuta);
    $rowTable = '';
    $total_pagado = 0;
    $total_saldo = 0;
    $total = 0;
    foreach ($listaDeuda as $key => $deuda) {
      $dias_visitas = strtoupper($deuda['dias_visitas']);
      $pagado = number_format($deuda['pagado'], 2);
      $saldo = number_format(($deuda['total'] - $deuda['pagado']), 2);
      $subtotal = number_format($deuda['total'], 2);
      $total_pagado += $deuda['pagado'];
      $total_saldo += ($deuda['total'] - $deuda['pagado']);
      $total += $deuda['total'];
      $rowTable .= "
          <tr>
          <td style='text-align: left;'>{$deuda['factura']}</td>
          <td style='text-align: left;'>{$deuda['cliente']}</td>
          <td style='text-align: center;'>{$dias_visitas}</td>
          <td style='text-align: center;'>{$deuda['id_ruta']}</td>
          <td style='text-align: center;'>{$deuda['metodo_pago']}</td>
          <td style='text-align: right;'>{$pagado}</td>
            </tr>
          ";
    }

    #
    $total_pagado = number_format($total_pagado, 2);
    $total_saldo = number_format($total_saldo, 2);
    $total = number_format($total, 2);
    $footerTable = "
      <tr>
        <td style='text-align: right;font-weight:bold;' colspan='5'>TOTAL </td>
        <td style='text-align: right;font-weight:bold;'>{$total_pagado}</td>
      <tr/>
    ";

    $html = "
     
    <div style='width: 100%; '>
        <div style='width: 100%; text-align: center;'>
                <h2 style='margin:0px;'>{$titulo}</h2>              
                <h5 style='margin:0px;'>{$titulo_fecha}</h5>              
        </div> 
        
        <div style='width: 100%; margin-top:20px;'>
            <table border='1' style='width: 100%; text-align: center;border-collapse: collapse;' >
                <thead>
                  <tr>                 
                      <th style='padding: 0px 12px;'>Factura</th>
                      <th style='padding: 0px 12px;'>CLIENTE</th>
                      <th style='padding: 0px 12px;'>DIAS VISITA</th>
                      <th style='padding: 0px 12px;'>RUTA</th>
                      <th style='padding: 0px 12px;'>MET.PAGO</th>
                      <th style='padding: 0px 12px;'>PAGO</th>
                  </tr>
                </thead>
                <tbody>
                  $rowTable
                </tbody>
                <tfooter>
                  $footerTable
                </tfooter>
            </table>
        </div>
        
    </div>
    ";
    // exit($html);
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output();
  }

  public function deudaPorVendedor($id_vendedor){
    $id_cliente = $_GET['id_cliente'];
    $id_vendedor = $_GET['id_vendedor'];
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];
    $camion = $_GET['camion'];
    $diasVisita = $_GET['diasVisita'];
    $ruta = $_GET['ruta'];
    $filtros = $this->obtenerFiltros(2,$id_cliente,$id_vendedor,$camion,$diasVisita,$ruta);

    $whereCliente = $filtros['whereCliente'];
    $whereVendedor = $filtros['whereVendedor'];
    $whereClientes = $filtros['whereClientes'];
    $whereDiasVisita = $filtros['whereDiasVisita'];
    $whereRuta = $filtros['whereRuta'];
    $titulo = $filtros['titulo'];
    $titulo_fecha = '';
    #filtro fechas
    $whereFecha = '';
    if ($fecha_inicio && $fecha_fin) {
        $whereFecha = "AND DATE(cu.fecha_pago_real) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $titulo_fecha = 'Desde el día '.$fecha_inicio.' hasta '.$fecha_fin;
      } elseif ($fecha_inicio) {
        $whereFecha = "AND DATE(cu.fecha_pago_real) >= '$fecha_inicio'";
        $titulo_fecha = 'Desde el día '.$fecha_inicio;
      } elseif ($fecha_fin) {
        $whereFecha = "AND DATE(cu.fecha_pago_real) <= '$fecha_fin'";
        $titulo_fecha = 'Hasta el día '.$fecha_fin;
    }

    $listaDeuda = $this->reporte->getAllCobrosByVendedor($whereCliente, $whereVendedor,$whereFecha, $whereClientes, $whereDiasVisita, $whereRuta);

    $rowTableEfectivo = '';
    $rowTableBancos = '';
    $total_pagado = 0;
    $total_saldo = 0;
    $total = 0;
    $footer_efectivo="";
    foreach ($listaDeuda as $key => $deuda) {
      $pagado = number_format($deuda['pagado'], 2);
      $saldo = number_format(($deuda['total'] - $deuda['total_pagado']), 2);
      $subtotal = number_format($deuda['total'], 2);
      $total_saldo += ($deuda['total'] - $deuda['pagado']);
      if(strtolower($deuda['metodo_pago'])=='efectivo'){        
        $total_pagado += $deuda['pagado'];
        $total += $deuda['total'];
        $rowTableEfectivo .= "
            <tr>
              <td style='text-align: left;'>{$deuda['documento']}</td>
              <td style='text-align: left;'>{$deuda['nombre_cliente']}</td>
              <td style='text-align: center;'>{$deuda['factura']}</td>
              <td style='text-align: center;'>{$deuda['fecha_pago']}</td>
              <td style='text-align: center;'>{$deuda['metodo_pago']}</td>
              <td style='text-align: center;'>{$subtotal}</td>
              <td style='text-align: center;'>{$pagado}</td>
              <td style='text-align: center;'>{$saldo}</td>
              </tr>
            ";
      }else{
        $total_pagado_str = number_format($total_pagado, 2);
        $footer_efectivo = "
          <tr>
            <td style='text-align: right;font-weight:bold;' colspan='6'>TOTAL EFECTIVO </td>
            <td style='text-align: right;font-weight:bold;text-align: left;padding-left:20px;' colspan='2'>{$total_pagado_str}</td>
          <tr/>
        ";
        $rowTableBancos .= "
            <tr>
              <td style='text-align: left;'>{$deuda['documento']}</td>
              <td style='text-align: left;'>{$deuda['nombre_cliente']}</td>
              <td style='text-align: center;'>{$deuda['factura']}</td>
              <td style='text-align: center;'>{$deuda['fecha_pago']}</td>
              <td style='text-align: center;'>{$deuda['metodo_pago']}</td>
              <td style='text-align: center;'>{$subtotal}</td>
              <td style='text-align: center;'>{$pagado}</td>
              <td style='text-align: center;'>{$saldo}</td>
            </tr>
            ";        
      }
    }

    #
    // $total_pagado = number_format($total_pagado, 2);
    $total_saldo = number_format($total_saldo, 2);
    $total = number_format($total, 2);
    $footerTable = "";
    /* $footerTable = "
      <tr>
        <td style='text-align: right;font-weight:bold;' colspan='6'>TOTAL </td>
        <td style='text-align: right;font-weight:bold;'>{$total_pagado}</td>
      <tr/>
    "; */

    $html = "
     
    <div style='width: 100%; '>
        <div style='width: 100%; text-align: center;'>
                <h2 style='margin:0px;'>{$titulo}</h2>              
                <h5 style='margin:0px;'>{$titulo_fecha}</h5>              
        </div> 
        
        <div style='width: 100%; margin-top:20px;'>
            <table border='1' style='width: 100%; text-align: center;border-collapse: collapse;' >
                <thead>
                  <tr>                 
                      <th style='padding: 0px 12px;'>DNI</th>
                      <th style='padding: 0px 12px;'>CLIENTE</th>
                      <th style='padding: 0px 12px;'>N°PEDIDO</th>
                      <th style='padding: 0px 12px;'>FECHA PAGO</th>
                      <th style='padding: 0px 12px;'>TIPO PAGO</th>
                      <th style='padding: 0px 12px;'>TOTAL</th>
                      <th style='padding: 0px 12px;'>MONTO</th>
                      <th style='padding: 0px 12px;'>RESTO</th>
                  </tr>
                </thead>
                <tbody>
                  $rowTableEfectivo
                </tbody>
                <tfooter>
                  $footer_efectivo
                </tfooter>
            </table>
        </div>

        <div style='width: 100%; margin-top:20px;'>
            <table border='1' style='width: 100%; text-align: center;border-collapse: collapse;' >
                <thead>
                  <tr>                 
                      <th style='padding: 0px 12px;'>DNI</th>
                      <th style='padding: 0px 12px;'>CLIENTE</th>
                      <th style='padding: 0px 12px;'>N°PEDIDO</th>
                      <th style='padding: 0px 12px;'>FECHA PAGO</th>
                      <th style='padding: 0px 12px;'>TIPO PAGO</th>
                      <th style='padding: 0px 12px;'>TOTAL</th>
                      <th style='padding: 0px 12px;'>MONTO</th>
                      <th style='padding: 0px 12px;'>RESTO</th>
                  </tr>
                </thead>
                <tbody>
                  $rowTableBancos
                </tbody>
            </table>
        </div>
        
    </div>
    ";
    // exit($html);
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output();

  }

  public function deudaPorRuta($id_vendedor){
    $id_cliente = $_GET['id_cliente'];
    $id_vendedor = $_GET['id_vendedor'];
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];
    $camion = $_GET['camion'];
    $diasVisita = $_GET['diasVisita'];
    $ruta = $_GET['ruta'];
    $filtros = $this->obtenerFiltros(3,$id_cliente,$id_vendedor,$camion,$diasVisita,$ruta);

    $whereCliente = $filtros['whereCliente'];
    $whereVendedor = $filtros['whereVendedor'];
    $whereClientes = $filtros['whereClientes'];
    $whereDiasVisita = $filtros['whereDiasVisita'];
    $whereRuta = $filtros['whereRuta'];
    $titulo = $filtros['titulo'];
    $titulo_fecha = '';
    #filtro fechas
    $whereFechaVenta = '';
    $whereFechaCoti = '';
    if ($fecha_inicio && $fecha_fin) {
        $whereFechaVenta = "AND v.fecha_emision BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $whereFechaCoti = "AND co.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $titulo_fecha = 'Desde el día '.$fecha_inicio.' hasta '.$fecha_fin;
    } elseif ($fecha_inicio) {
        $whereFechaVenta = "AND v.fecha_emision >= '$fecha_inicio'";
        $whereFechaCoti = "AND co.fecha >= '$fecha_inicio'";
        $titulo_fecha = 'Desde el día '.$fecha_inicio;
      } elseif ($fecha_fin) {
        $whereFechaVenta = "AND v.fecha_emision <= '$fecha_fin'";
        $whereFechaCoti = "AND co.fecha <= '$fecha_fin'";
        $titulo_fecha = 'Hasta el día '.$fecha_fin;
    }

    $listaDeuda = $this->reporte->getAllDeudaByRuta($whereCliente,$id_vendedor, $whereVendedor,$whereFechaVenta,$whereFechaCoti, $whereClientes, $whereDiasVisita, $whereRuta);
    
    $rowTable = '';
    $total_pagado = 0;
    $total_saldo = 0;
    $total = 0;
    foreach ($listaDeuda as $key => $deuda) {
      if($deuda['saldo']==0) continue;
      $today = strtotime(date('Y-m-d'));
      $f_vencimiento = ($deuda['fecha_vencimiento']!='0000-00-00') ? strtotime($deuda['fecha_vencimiento']) : strtotime($deuda['fecha_emision']);
      $tiempo_vencido = $today-$f_vencimiento;
      $dias = ceil($tiempo_vencido / 86400);
      if($dias<0){
        $dias = '+'.abs($dias);
      }else if($dias>0){
        $dias = '-'.abs($dias);
      }

      $pagado = number_format($deuda['pagado'], 2);
      $saldo = number_format(($deuda['total'] - $deuda['pagado']), 2);
      $subtotal = number_format($deuda['total'], 2);
      $total_pagado += $deuda['pagado'];
      $total_saldo += ($deuda['total'] - $deuda['pagado']);
      $total += $deuda['total'];
      $rowTable .= "
          <tr>
          <td style='text-align: left;'>{$deuda['documento']}</td>
          <td style='text-align: left;'>{$deuda['cliente']}</td>
          <td style='text-align: center;'>{$deuda['factura']}</td>
          <td style='text-align: center;'>{$dias}</td>
          <td style='text-align: center;'>{$subtotal}</td>
          <td style='text-align: center;'>{$saldo}</td>
            </tr>
          ";
    }

    #
    $total_pagado = number_format($total_pagado, 2);
    $total_saldo = number_format($total_saldo, 2);
    $total = number_format($total, 2);
    $footerTable = "
      <tr>
        <td style='text-align: right;font-weight:bold;' colspan='4'>TOTAL </td>
        <td style='text-align: right;font-weight:bold;'>{$total}</td>
        <td style='text-align: right;font-weight:bold;'>{$total_saldo}</td>
      <tr/>
    ";

    $html = "
     
    <div style='width: 100%; '>
        <div style='width: 100%; text-align: center;'>
                <h2 style='margin:0px;'>{$titulo}</h2>
                <h5 style='margin:0px;'>{$titulo_fecha}</h5>
        </div> 
        
        <div style='width: 100%; margin-top:20px;'>
            <table border='1' style='width: 100%; text-align: center;border-collapse: collapse;' >
                <thead>
                  <tr>                 
                      <th style='padding: 0px 12px;'>DNI</th>
                      <th style='padding: 0px 12px;'>CLIENTE</th>
                      <th style='padding: 0px 12px;'>N°PEDIDO</th>
                      <th style='padding: 0px 12px;'>DIAS</th>
                      <th style='padding: 0px 12px;'>MONTO</th>
                      <th style='padding: 0px 12px;'>SALDO</th>
                  </tr>
                </thead>
                <tbody>
                  $rowTable
                </tbody>
                <tfooter>
                  $footerTable
                </tfooter>
            </table>
        </div>
        
    </div>
    ";
    // exit($html);
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output();

  }

  public function deudaPorVendedor2($id_vendedor)
  {
    $id_cliente = $_GET['id_cliente'];
    $id_vendedor = $_GET['id_vendedor'];
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];
    $fecha_fin = $_GET['fecha_fin'];
    $camion = $_GET['camion'];
    $diasVisita = $_GET['diasVisita'];
    $ruta = $_GET['ruta'];
    #
    $whereCliente = "";
    $whereVendedor = "";

    if ($id_cliente != "") {
      $sql = "SELECT
                  *
              FROM
                  clientes c
              where c.id_cliente = {$id_cliente}";
      $fila = mysqli_query($this->conexion, $sql);
      $clientes = mysqli_fetch_all($fila, MYSQLI_ASSOC);
      $titulo = "DEUDA CLIENTE " . strtoupper($clientes[0]['datos']);
      $whereCliente = " AND c.id_cliente={$id_cliente}";
    } else if ($id_vendedor != "") {
      $sql = "SELECT
                  *
              FROM
                  usuarios u
              where u.usuario_id = {$id_vendedor}";
      $fila = mysqli_query($this->conexion, $sql);
      $vendedores = mysqli_fetch_all($fila, MYSQLI_ASSOC);
      $titulo = "Deuda Clientes de Vendedor " . strtoupper($vendedores[0]['nombres']);
      $whereVendedor = " AND us.usuario_id={$id_vendedor} ";
    } else {
      $titulo = "";
    }
    // echo json_encode($respuesta[0]['nombres']);
    // exit();
    #
    $filtros = [];
    $arrQueryClientes = [];
    // Filtro por mercado
    $whereRuta = '';
    if (!empty($ruta)) {
      $whereRuta = "AND c.id_ruta = '$ruta'";
    }

    // Filtros por camión
    switch ($camion) {
      case '1':
        $filtros = [
          'lunes'     => ['1', '7'],
          'martes'    => ['5', '7'],
          'miercoles' => ['5'],
          'jueves'    => ['1', '7'],
          'viernes'   => ['6', '7'],
          'sabado'    => ['7', '8'],
        ];
        break;
      case '2':
        $filtros = [
          'lunes'     => ['3', '6'],
          'martes'    => ['1', '3'],
          'miercoles' => ['1', '3'],
          'jueves'    => ['6', '3'],
          'viernes'   => ['3', '5'],
          'sabado'    => ['3', '6'],
        ];
        break;
      case '3':
        $filtros = [
          'miercoles' => ['6', '7'],
          'viernes'   => ['8', '2'],
          'sabado'    => ['1', '5'],
        ];
        break;
      default:
        break;
    }

    // Si se seleccionó un día específico
    if ($diasVisita != "" && isset($filtros[$diasVisita])) {
      $filtros = [
        $diasVisita => $filtros[$diasVisita]
      ];
    }
    if ($ruta != "") {
      foreach ($filtros as $key => $filtro) {
        $filtros[$key] = [$ruta];
      }
    }
    foreach ($filtros as $key => $filtro) {
      $arrQueryClientes[] = "( LOWER(c.dias_visitas) = LOWER('{$key}') AND c.id_ruta IN (" . implode(',', $filtro) . ") )";
    }

    $whereClientes = '';
    if (!empty($arrQueryClientes)) {
      $whereClientes = "AND (" . implode(' OR ', $arrQueryClientes) . ")";
    }
    $whereDiasVisita = '';
    if (!empty($diasVisita)) {
      $whereDiasVisita = "AND LOWER(c.dias_visitas) = LOWER('$diasVisita')";
    }
    #
    $listaDeuda = $this->reporte->getAllDeudasByVendedor($whereCliente, $whereVendedor, $fecha_inicio, $fecha_fin, $whereClientes, $whereDiasVisita, $whereRuta);
    $rowTable = '';
    $total_pagado = 0;
    $total_saldo = 0;
    $total = 0;
    foreach ($listaDeuda as $key => $deuda) {
      $pagado = number_format($deuda['pagado'], 2);
      $saldo = number_format(($deuda['total'] - $deuda['pagado']), 2);
      $subtotal = number_format($deuda['total'], 2);
      $total_pagado += $deuda['pagado'];
      $total_saldo += ($deuda['total'] - $deuda['pagado']);
      $total += $deuda['total'];
      $rowTable .= "
          <tr>
          <td style='text-align: left;'>{$deuda['factura']}</td>
          <td style='text-align: left;'>{$deuda['cliente']}</td>
          <td style='text-align: center;'>{$deuda['dias_visitas']}</td>
          <td style='text-align: center;'>{$deuda['id_ruta']}</td>
          <td style='text-align: center;'>{$deuda['metodo_pago']}</td>
          <td style='text-align: right;'>{$pagado}</td>
            </tr>
          ";
    }

    #
    $total_pagado = number_format($total_pagado, 2);
    $total_saldo = number_format($total_saldo, 2);
    $total = number_format($total, 2);
    $footerTable = "
      <tr>
        <td style='text-align: right;font-weight:bold;' colspan='5'>TOTAL </td>
        <td style='text-align: right;font-weight:bold;'>{$total_pagado}</td>
      <tr/>
    ";

    $html = "
     
    <div style='width: 100%; '>
        <div style='width: 100%; text-align: center;'>
                <h2 style='margin:0px;'>{$titulo}</h2>              
        </div> 
        
        <div style='width: 100%; margin-top:20px;'>
            <table border='1' style='width: 100%; text-align: center;border-collapse: collapse;' >
                <thead>
                  <tr>                 
                      <th style='padding: 0px 12px;'>Factura</th>
                      <th style='padding: 0px 12px;'>CLIENTE</th>
                      <th style='padding: 0px 12px;'>DIAS VISITA</th>
                      <th style='padding: 0px 12px;'>RUTA</th>
                      <th style='padding: 0px 12px;'>MET.PAGO</th>
                      <th style='padding: 0px 12px;'>PAGO</th>
                  </tr>
                </thead>
                <tbody>
                  $rowTable
                </tbody>
                <tfooter>
                  $footerTable
                </tfooter>
            </table>
        </div>
        
    </div>
    ";
    // exit($html);
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output();
  }

  // SUMATORIOA DE PRODUCTOS
  public function reporteVentaPorProducto2()
  {
    $id_vendedor = $_GET['id_vendedor'];
    $ruta = $_GET['ruta'];
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];
    $titulo = 'Reporte Ventas por Producto<br/> Ruta '.$ruta;
    $whereVendedor = "";
    $whereRuta = "";
    if($id_vendedor!=""){
      $sql = "SELECT
                    *
                FROM
                    usuarios u
              where u.usuario_id = {$id_vendedor}";
      $fila = mysqli_query($this->conexion, $sql);
      $vendedores = mysqli_fetch_all($fila, MYSQLI_ASSOC);
      $titulo = 'Reporte Ventas por Producto<br/>Vendedor '.$vendedores[0]['usuario'];
      $whereVendedor = ' AND u.usuario_id='.$id_vendedor;
    }

    if($ruta!=""){
      $whereRuta = ' AND c.id_ruta='.$ruta;
    }
    #
    $titulo_fecha = '';
    #filtro fechas
    $whereFechaVenta = '';
    if ($fecha_inicio && $fecha_fin) {
        $whereFechaVenta = "AND v.fecha_emision BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $whereFechaCoti = "AND co.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $titulo_fecha = 'Desde el día '.$fecha_inicio.' hasta '.$fecha_fin;
      } elseif ($fecha_inicio) {
        $whereFechaVenta = "AND v.fecha_emision >= '$fecha_inicio'";
        $whereFechaCoti = "AND co.fecha >= '$fecha_inicio'";
        $titulo_fecha = 'Desde el día '.$fecha_inicio;
      } elseif ($fecha_fin) {
        $whereFechaVenta = "AND v.fecha_emision <= '$fecha_fin'";
        $whereFechaCoti = "AND co.fecha <= '$fecha_fin'";
        $titulo_fecha = 'Hasta el día '.$fecha_fin;
    }
    #
    $listaVentas = $this->reporte->getAllVentasProducto1($whereVendedor, $whereFechaVenta,$whereFechaCoti, $whereRuta);

    $rowTable = "";
    $total_venta = 0;
    $total_utilidad = 0;

    foreach ($listaVentas as $key => $venta) {
      // CORREGIDO: Quitar IGV del precio antes de calcular
      // Los precios en la BD incluyen IGV (18%), debemos quitarlo
      $precio_con_igv = $venta['precio']/$venta['productos'];
      $precio_sin_igv = $precio_con_igv / 1.18; // Quitar el 18% de IGV
      $precio_unitario = number_format($precio_sin_igv, 2);
      
      $costo_unitario = number_format($venta['costo']/$venta['productos'],2);
      $utilidad_unitario = $precio_unitario-$costo_unitario;
      $subtotal_utilidad = $utilidad_unitario * $venta['cantidad'];
      $subtotal = $precio_unitario*$venta['cantidad'];
      $total_venta += $subtotal;
      $total_utilidad += $subtotal_utilidad;
      $subtotal = number_format($subtotal,2);
      $subtotal_utilidad = number_format($subtotal_utilidad,2);
      $precio_venta = number_format(floatval($precio_unitario),2);
      $precio_costo = number_format($costo_unitario,2);
      $utilidad_unitario = number_format($utilidad_unitario,2);
      $rowTable .= "
          <tr>
          <td style='text-align: left;'>{$venta['codigo']}</td>
          <td style='text-align: left;'>{$venta['descripcion']}</td>
          <td style='text-align: left;'>{$venta['cantidad']}</td>
          <td style='text-align: left;'>{$precio_venta}</td>
          <td style='text-align: center;'>{$precio_costo}</td>
          <td style='text-align: center;'>{$utilidad_unitario}</td>
          <td style='text-align: center;'>{$subtotal}</td>
          <td style='text-align: center;'>{$subtotal_utilidad}</td>
            </tr>
          ";
    }

    #
    $total_venta = number_format($total_venta, 2);
    $total_utilidad = number_format($total_utilidad, 2);
    $footerTable = "";
    $footerTable = "
      <tr>
        <td style='text-align: right;font-weight:bold;' colspan='6'>TOTAL </td>
        <td style='text-align: right;font-weight:bold;'>{$total_venta}</td>
        <td style='text-align: right;font-weight:bold;'>{$total_utilidad}</td>
      <tr/>
    ";

    $html = "
     
    <div style='width: 100%; '>
        <div style='width: 100%; text-align: center;'>
                <h2 style='margin:0px;'>{$titulo}</h2>              
                <h5 style='margin:0px;'>{$titulo_fecha}</h5>              
        </div> 
        
        <div style='width: 100%; margin-top:20px;'>
            <table border='1' style='width: 100%; text-align: center;border-collapse: collapse;' >
                <thead>
                  <tr>                 
                      <th style='padding: 0px 12px;'>CÓDIGO</th>
                      <th style='padding: 0px 12px;'>DESCRIPCIÓN</th>
                      <th style='padding: 0px 12px;'>CANTIDAD</th>
                      <th style='padding: 0px 12px;'>PRECIO VENTA</th>
                      <th style='padding: 0px 12px;'>PRECIO COSTO</th>
                      <th style='padding: 0px 12px;'>UTILIDAD</th>
                      <th style='padding: 0px 12px;'>TOTAL VENTA</th>
                      <th style='padding: 0px 12px;'>TOTAL UTILIDAD</th>
                  </tr>
                </thead>
                <tbody>
                  $rowTable
                </tbody>
                <tfooter>
                  $footerTable
                </tfooter>
            </table>
        </div>
        
    </div>
    ";
    // exit($html);
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output();
  }
  #2025-10-09
  public function reporteVentaPorProducto()
  {
    $id_cliente = '';#$_GET['id_cliente'];
    $id_vendedor = (isset($_GET['id_vendedor'])) ? $_GET['id_vendedor'] : '';
    $fecha_inicio = (isset($_GET['fecha_inicio'])) ? $_GET['fecha_inicio'] : '';
    $fecha_fin = (isset($_GET['fecha_fin'])) ? $_GET['fecha_fin'] : '';
    $camion = (isset($_GET['camion'])) ? $_GET['camion'] : '';
    $diasVisita = (isset($_GET['diasVisita'])) ? $_GET['diasVisita'] : '';
    $ruta = (isset($_GET['ruta'])) ? $_GET['ruta'] : '';
    
    $filtros = $this->obtenerFiltros(2,$id_cliente,$id_vendedor,$camion,$diasVisita,$ruta);
    
    // Título dinámico según filtros aplicados
    $titulo = 'Reporte Ventas por Producto';
    if($camion!=""){
      $titulo .= '<br/> Camión '.$camion;
    }
    if($ruta!=""){
      $titulo .= ' - Ruta '.$ruta;
    }
    if($id_vendedor!=""){
      $sql = "SELECT
                    *
                FROM
                    usuarios u
              where u.usuario_id = {$id_vendedor}";
      $fila = mysqli_query($this->conexion, $sql);
      $vendedores = mysqli_fetch_all($fila, MYSQLI_ASSOC);
      $titulo .= ' - Vendedor '.$vendedores[0]['usuario'];
    }
    
    $whereCliente = $filtros['whereCliente'];
    $whereVendedor = $filtros['whereVendedor'];
    $whereClientes = $filtros['whereClientes'];
    $whereDiasVisita = $filtros['whereDiasVisita'];
    $whereRuta = $filtros['whereRuta'];

    if($ruta!=""){
      $whereRuta = ' AND c.id_ruta='.$ruta;
    }
    $titulo_fecha = '';
    #filtro fechas
    $whereFechaVenta = '';
    $whereFechaCoti = '';
    if ($fecha_inicio && $fecha_fin) {
        $whereFechaVenta = "AND v.fecha_emision BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $whereFechaCoti = "AND co.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $titulo_fecha = 'Desde el día '.$fecha_inicio.' hasta '.$fecha_fin;
      } elseif ($fecha_inicio) {
        $whereFechaVenta = "AND v.fecha_emision >= '$fecha_inicio'";
        $whereFechaCoti = "AND co.fecha >= '$fecha_inicio'";
        $titulo_fecha = 'Desde el día '.$fecha_inicio;
      } elseif ($fecha_fin) {
        $whereFechaVenta = "AND v.fecha_emision <= '$fecha_fin'";
        $whereFechaCoti = "AND co.fecha <= '$fecha_fin'";
        $titulo_fecha = 'Hasta el día '.$fecha_fin;
    }
    $listaVentas = $this->reporte->getAllVentasProducto($whereVendedor,$whereClientes,$whereDiasVisita,$whereFechaVenta,$whereFechaCoti, $whereRuta);

    $rowTable = "";
    $total_venta = 0;
    $total_utilidad = 0;
    $total_peso_bruto = 0;

    foreach ($listaVentas as $key => $venta) {
      // CORREGIDO: Quitar IGV del precio antes de calcular
      // Los precios en la BD incluyen IGV (18%), debemos quitarlo
      $precio_con_igv = $venta['precio']/$venta['productos'];
      $precio_sin_igv = $precio_con_igv / 1.18; // Quitar el 18% de IGV
      $precio_unitario = number_format($precio_sin_igv, 2);
      
      $costo_unitario = number_format($venta['costo']/$venta['productos'],2);
      $utilidad_unitario = $precio_unitario-$costo_unitario;
      $subtotal_utilidad = $utilidad_unitario * $venta['cantidad'];
      $subtotal = $precio_unitario*$venta['cantidad'];
      
      // CORREGIDO: Calcular peso bruto total correctamente
      // Fórmula: Peso Total (Kg) = Cantidad × Peso Bruto Real del Producto
      
      $peso_bruto_unitario = floatval($venta['peso_bruto'] ?? 0);
      $cantidad = floatval($venta['cantidad']);
      $presenta_cnt = ($venta['presenta_cnt'] <= 0) ? 1 : floatval($venta['presenta_cnt']);
      
      // Cálculo correcto: Cantidad × Peso Bruto (NO presenta_cnt)
      // presenta_cnt es solo el factor de empaque, NO el peso real
      $peso_bruto_total = $cantidad * $peso_bruto_unitario;
      
      $total_peso_bruto += $peso_bruto_total;
      
      $total_venta += $subtotal;
      $total_utilidad += $subtotal_utilidad;
      $subtotal = number_format($subtotal,2);
      $subtotal_utilidad = number_format($subtotal_utilidad,2);
      $precio_venta = number_format(floatval($precio_unitario),2);
      $precio_costo = number_format($costo_unitario,2);
      $utilidad_unitario = number_format($utilidad_unitario,2);
      $peso_bruto_total_fmt = number_format($peso_bruto_total,2);
      
      $rowTable .= "
          <tr>
          <td style='text-align: left;'>{$venta['codigo']}</td>
          <td style='text-align: left;'>{$venta['descripcion']}</td>
          <td style='text-align: center;'>{$venta['peso_bruto']}</td>
          <td style='text-align: center;'>{$venta['cantidad']}</td>
          <td style='text-align: center;'>{$peso_bruto_total_fmt}</td>
          <td style='text-align: left;'>{$precio_venta}</td>
          <td style='text-align: center;'>{$precio_costo}</td>
          <td style='text-align: center;'>{$utilidad_unitario}</td>
          <td style='text-align: center;'>{$subtotal}</td>
          <td style='text-align: center;'>{$subtotal_utilidad}</td>
            </tr>
          ";
    }

    #
    $total_venta = number_format($total_venta, 2);
    $total_utilidad = number_format($total_utilidad, 2);
    $total_peso_bruto = number_format($total_peso_bruto, 2);
    $footerTable = "";
    $footerTable = "
      <tr>
        <td style='text-align: right;font-weight:bold;' colspan='4'>TOTAL </td>
        <td style='text-align: right;font-weight:bold;'>{$total_peso_bruto} Kg</td>
        <td style='text-align: right;font-weight:bold;' colspan='3'></td>
        <td style='text-align: right;font-weight:bold;'>{$total_venta}</td>
        <td style='text-align: right;font-weight:bold;'>{$total_utilidad}</td>
      <tr/>
    ";

    $html = "
     
    <div style='width: 100%; '>
        <div style='width: 100%; text-align: center;'>
                <h2 style='margin:0px;'>{$titulo}</h2>              
                <h5 style='margin:0px;'>{$titulo_fecha}</h5>              
        </div> 
        
        <div style='width: 100%; margin-top:20px;'>
            <table border='1' style='width: 100%; text-align: center;border-collapse: collapse;' >
                <thead>
                  <tr>                 
                      <th style='padding: 0px 12px;'>CÓDIGO</th>
                      <th style='padding: 0px 12px;'>DESCRIPCIÓN</th>
                      <th style='padding: 0px 12px;'>PESO BRUTO</th>
                      <th style='padding: 0px 12px;'>CANTIDAD</th>
                      <th style='padding: 0px 12px;'>PESO TOTAL (Kg)</th>
                      <th style='padding: 0px 12px;'>PRECIO VENTA</th>
                      <th style='padding: 0px 12px;'>PRECIO COSTO</th>
                      <th style='padding: 0px 12px;'>UTILIDAD</th>
                      <th style='padding: 0px 12px;'>TOTAL VENTA</th>
                      <th style='padding: 0px 12px;'>TOTAL UTILIDAD</th>
                  </tr>
                </thead>
                <tbody>
                  $rowTable
                </tbody>
                <tfooter>
                  $footerTable
                </tfooter>
            </table>
        </div>
        
    </div>
    ";
    // exit($html);
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output();
  }
  
  public function reporteVentasVendedor(){
    $id_vendedor = $_GET['id_vendedor'];
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];
    $proveedor = trim($_GET['proveedor']);

    $sql = "SELECT
                  *
              FROM
                  usuarios u
            where u.usuario_id = {$id_vendedor}";
    $fila = mysqli_query($this->conexion, $sql);
    $vendedores = mysqli_fetch_all($fila, MYSQLI_ASSOC);
    $titulo_fecha = '';
    #filtro fechas
    $whereFechaVenta = '';
    $whereFechaCoti = '';
    if ($fecha_inicio && $fecha_fin) {
        $whereFechaVenta = "AND v.fecha_emision BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $whereFechaCoti = "AND co.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        $titulo_fecha = 'Desde el día '.$fecha_inicio.' hasta '.$fecha_fin;
      } elseif ($fecha_inicio) {
        $whereFechaVenta = "AND v.fecha_emision >= '$fecha_inicio'";
        $whereFechaCoti = "AND co.fecha >= '$fecha_inicio'";
        $titulo_fecha = 'Desde el día '.$fecha_inicio;
      } elseif ($fecha_fin) {
        $whereFechaVenta = "AND v.fecha_emision <= '$fecha_fin'";
        $whereFechaCoti = "AND co.fecha <= '$fecha_fin'";
        $titulo_fecha = 'Hasta el día '.$fecha_fin;
    }

    $listaVentas = $this->reporte->getAllVentasVendedorProveedor($id_vendedor, $whereFechaVenta,$whereFechaCoti,$proveedor);
    $total_venta = 0;
    $total_cantidad = 0;
    $rowTable = '';
    foreach ($listaVentas as $key => $venta) {
      // CORREGIDO: Quitar IGV del precio antes de calcular
      // Los precios en la BD incluyen IGV (18%), debemos quitarlo
      $precio_con_igv = $venta['precio']/$venta['productos'];
      $precio_sin_igv = $precio_con_igv / 1.18; // Quitar el 18% de IGV
      $precio_unitario = number_format($precio_sin_igv, 2);
      
      $costo_unitario = $venta['costo']/$venta['productos'];
      $subtotal = $precio_unitario*$venta['cantidad'];
      $total_venta += $subtotal;
      $total_cantidad += $venta['cantidad'];
      $precio_venta = number_format(floatval($precio_unitario),2);
      $subtotal = number_format($subtotal,2);
      $rowTable .= "
          <tr>
          <td style='text-align: left;'>{$venta['codigo']}</td>
          <td style='text-align: left;'>{$venta['descripcion']}</td>
          <td style='text-align: left;'>{$venta['cantidad']}</td>
          <td style='text-align: left;'>{$venta['proveedor']}</td>
          <td style='text-align: center;'>{$precio_venta}</td>
          <td style='text-align: center;'>{$subtotal}</td>
            </tr>
          ";
    }

    #
    $total_venta = number_format($total_venta, 2);
    $total_cantidad = number_format($total_cantidad, 2);
    $footerTable = "";
    $footerTable = "
      <tr>
        <td style='text-align: right;font-weight:bold;' colspan='2'>TOTAL </td>
        <td style='text-align: center;font-weight:bold;'>{$total_cantidad}</td>
        <td style='text-align: right;font-weight:bold;' colspan='2'></td>
        <td style='text-align: right;font-weight:bold;'>{$total_venta}</td>
      <tr/>
    ";

    $html = "
     
    <div style='width: 100%; '>
        <div style='width: 100%; text-align: center;'>
                <h2 style='margin:0px;'>Reporte Ventas por Proveedor {$proveedor} <br/> Vendedor {$vendedores[0]['usuario']}</h2>              
                <h5 style='margin:0px;'>{$titulo_fecha}</h5>              
        </div> 
        
        <div style='width: 100%; margin-top:20px;'>
            <table border='1' style='width: 100%; text-align: center;border-collapse: collapse;' >
                <thead>
                  <tr>                 
                      <th style='padding: 0px 10px;'>CÓDIGO</th>
                      <th style='padding: 0px 10px;'>DESCRIPCIÓN</th>
                      <th style='padding: 0px 10px;'>CANTIDAD</th>
                      <th style='padding: 0px 10px;'>PROVEEDOR</th>
                      <th style='padding: 0px 10px;'>PRECIO VENTA</th>
                      <th style='padding: 0px 10px;'>TOTAL</th>
                  </tr>
                </thead>
                <tbody>
                  $rowTable
                </tbody>
                <tfooter>
                  $footerTable
                </tfooter>
            </table>
        </div>
        
    </div>
    ";
    // exit($html);
    $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $this->mpdf->Output();

  }

}
