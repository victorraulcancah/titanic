<?php
include "BD.php";
$opc = (isset($_POST['opcion'])) ? $_POST['opcion'] : '';
$sistema = "factura_biker";

//"https://magustechnologies.com/kanako/venta/comprobante/pdf/6446/20611556960-01-F001-142";

switch ($opc) {

  case '1': // AGREGAR
    $rucemisor= $_POST['rucemisor'];
    $serie = $_POST['serie'];
    $correlativo = $_POST['correlativo'];
    $consoleInner = $_POST['consoleInner'];
    $sqlcod = "SELECT v.id_venta, d.cod_sunat FROM ventas v, documentos_sunat d WHERE v.serie='$serie' AND v.numero='$correlativo' AND v.id_tido ='$consoleInner'
	AND d.id_tido = v.id_tido";
      $rescod = mysqli_query($con,$sqlcod);
      $arrcod = mysqli_fetch_array($rescod,MYSQLI_ASSOC);
 	
      $codshb = $arrcod['id_venta'];	
      if ($codshb>0) { 
	$buscar = 1;	
        $codsut = $arrcod['cod_sunat'];
      	$ruta = "https://magustechnologies.com/".$sistema."/venta/comprobante/pdf/".$codshb."/".$rucemisor."-".$codsut."-".$serie."-".$correlativo;

      } else { 
	$buscar = 0;
      	$ruta = "";
      } 
    
      $listar = array("buscar" =>$buscar,"ruta" =>$ruta);
      $data[] = $listar;
    break;
  
    


    }         
  print json_encode($data);

 ?>
