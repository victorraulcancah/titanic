<?php

require_once "app/models/Venta.php";
require_once "app/models/Cliente.php";
require_once "app/models/DocumentoEmpresa.php";
require_once "app/models/ProductoVenta.php";
require_once "app/models/VentaServicio.php";
require_once "app/models/Varios.php";
require_once "app/models/VentaSunat.php";
require_once "app/models/VentaAnulada.php";
require_once "app/models/GuiaRemision.php";
require_once "app/clases/SendURL.php";
require_once "app/clases/SunatApi.php";


class VentasController extends Controller
{
    private $venta;
    private $sunatApi;
    private $conexion;
    private $guia;
    public function __construct()
    {
        $this->venta = new Venta();
        $this->sunatApi = new SunatApi();
        $this->guia = new GuiaRemision();
        $this->conexion = (new Conexion())->getConexion();
    }


    public function ingresosEgresosRender()
    {
        $lista = [];
        $sql = "SELECT
                	ingreso_egreso.*,
                	productos.descripcion,
                	productos.codigo,
                	usuario 
                FROM
                	ingreso_egreso
                	JOIN productos ON ingreso_egreso.id_producto = productos.id_producto
                	INNER JOIN usuarios on usuarios.usuario_id = ingreso_egreso.id_usuario
                ORDER BY
                	intercambio_id ASC";
        $result = $this->conexion->query($sql);
        /*  foreach ($result as $res) {
            $lista[] = $res;
        }
 */
        return $result;
    }

    // agregando 10/04/2025
    public function getAllByProductosIdVenta($request)
    {
        try {
            $id = $request->id;

            $sql = "SELECT 
                    pc.id_producto, 
                    pc.cantidad, 
                    pc.presenta_cnt,
                    p.precio, 
                    (pc.cantidad * pc.presenta_cnt * p.precio) AS total, 
                    p.descripcion 
                FROM productos_cotis pc
                INNER JOIN productos p ON p.id_producto = pc.id_producto
                WHERE id_coti = '$id'";

            $result = $this->conexion->query($sql);
            return json_encode($result->fetch_all(MYSQLI_ASSOC));
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }


    /*  public function  */
    public function ingresoAlmacen()
    {
        $respuesta['res'] = false;
        $sql = "INSERT INTO ingreso_egreso set id_producto = '{$_POST['productoid']}', tipo = '{$_POST['tipo']}',cantidad = '{$_POST['cantidad']}', id_usuario = '{$_SESSION['usuario_fac']}', almacen_ingreso = '{$_POST['almacen']}'";
        if ($this->conexion->query($sql)) {
            $sql = "update productos set cantidad=cantidad+'{$_POST['cantidad']}' where id_producto= '{$_POST['productoid']}'";
            $this->conexion->query($sql);
            $respuesta['res'] = true;
        }
        echo json_encode($respuesta);
    }
    public function egresoAlmacen()
    {
        $respuesta['res'] = false;
        $sql = "INSERT INTO ingreso_egreso set id_producto = '{$_POST['productoid']}', tipo = '{$_POST['tipo']}',cantidad = '{$_POST['cantidad']}', id_usuario = '{$_SESSION['usuario_fac']}', almacen_ingreso = '{$_POST['alAlmacen']}', almacen_egreso = '{$_POST['almacen']}', estado = 0";
        if ($this->conexion->query($sql)) {
            //$sql="select  * from productos where id_producto= '{$_POST['productoid']}'";
            //$result =  $this->conexion->query($sql)->fetch_assoc();
            //
            //$sql="update productos set cantidad=cantidad-'{$_POST['cantidad']}' where id_producto= '{$_POST['productoid']}'";
            //$this->conexion->query($sql);
            //$sql="update productos set cantidad=cantidad+'{$_POST['cantidad']}' where codigo= '{$result['codigo']}' and almacen='{$_POST['alAlmacen']}'";
            //$this->conexion->query($sql);
            $respuesta['res'] = true;
        }
        echo json_encode($respuesta);
    }
    public function envioComunicacionBajaPorEmpresa()
    {
        $listaBoletas = [];
        foreach (json_decode($_POST['boletas'], true) as  $bol) {
            $listaBoletas[] = "v.id_venta='$bol'";
        }

        $sql = "select v.id_venta, v.enviado_sunat,vs.nombre_xml from ventas v
        join ventas_sunat vs on v.id_venta = vs.id_venta
        where " . implode(" OR ", $listaBoletas);

        $listaPorEnviar = $this->venta->exeSQL($sql);

        foreach ($listaPorEnviar as $vpr) {
            if ($vpr['enviado_sunat'] == '0') {
                if ($this->sunatApi->envioIndividualDocumentoVPorEmpresa($vpr['nombre_xml'], $_POST['empresa'])) {
                    $sql = "update ventas set enviado_sunat='1' where id_venta='{$vpr['id_venta']}'";
                    $this->venta->exeSQL($sql);
                }
                sleep(2);
            }
        }
        $respuesta = [];
        $respuesta['msg_resumen'] = $this->sunatApi->comunicacionBajaPorEmpresa(
            $listaBoletas,
            $_POST['empresa'],
            $_POST['fecharesumen'],
            $_POST["fechagen"],
            $_POST['correlativo1']
        );

        return json_encode($respuesta);
    }

    public function envioResumenDiarioPorEmpresa()
    {
        $listaBoletas = [];
        foreach (json_decode($_POST['boletas'], true) as  $bol) {
            $listaBoletas[] = "v.id_venta='$bol'";
        }
        return json_encode([
            $this->sunatApi->resumenDiarioPorEmpresa(
                $listaBoletas,
                $_POST['empresa'],
                $_POST['fechagen'],
                $_POST['fecharesumen'],
                $_POST['correlativo1']
            ),
            $this->sunatApi->resumenDiarioBajaPorEmpresa(
                $listaBoletas,
                $_POST['empresa'],
                $_POST['fechagen'],
                $_POST['fecharesumen'],
                $_POST['correlativo2']
            )
        ]);
    }

    public function enviarDocumentoSunatPorEmpresa()
    {
        $sql = "select vs.*,v.id_empresa from ventas_sunat vs
        join ventas v on v.id_venta = vs.id_venta
        where vs.id_venta = '{$_POST["cod"]}'";
        $resultado = ["res" => false];
        if ($row = $this->venta->exeSQL($sql)->fetch_assoc()) {
            if ($this->sunatApi->envioIndividualDocumentoVPorEmpresa($row["nombre_xml"], $row['id_empresa'])) {
                $sql = "update ventas set  enviado_sunat='1'
                where id_venta = '{$_POST["cod"]}'";
                $this->venta->exeSQL($sql);
                $resultado['res'] = true;
            } else {
                $resultado['msg'] = $this->sunatApi->getMensaje();
            }
        }
        return json_encode($resultado);
    }

    public function regenerarXML()
    {
        $venta = $_POST["venta"];

        $sql = "SELECT * from ventas where id_venta='$venta'";
        $ventaData = $this->venta->exeSQL($sql)->fetch_assoc();
        $empresa = $this->venta->exeSQL("select * from empresas where id_empresa='{$ventaData['id_empresa']}'")->fetch_assoc();
        $cliente = $this->venta->exeSQL("select * from clientes where id_cliente='{$ventaData['id_cliente']}'")->fetch_assoc();


        $dataSend = [];
        $dataSend["certGlobal"] = false;

        $direccionselk = $cliente["direccion"];



        if (strlen(trim($direccionselk)) == "") {
            $direccionselk = '-';
        }
        if (trim($cliente["datos"]) == "") {
            $cliente["datos"] = '-';
        }

        $dataSend['cliente'] = json_encode([
            'doc_num' => $cliente["documento"],
            'nom_RS' => $cliente["datos"],
            'direccion' => $direccionselk
        ]);
        $dataSend['productos'] = [];
        $dataSend['apli_igv'] = $ventaData['apli_igv'] == 1;
        $dataSend['total'] = $ventaData["total"];
        $dataSend['serie'] = $ventaData["serie"];
        $dataSend['numero'] = $ventaData["numero"];
        $dataSend['fechaE'] = $ventaData["fecha_emision"];
        $dataSend['fechaV'] = $ventaData["fecha_vencimiento"];
        $dataSend['tipo_pago'] = $ventaData["id_tipo_pago"];
        $dataSend['igv_venta'] = $ventaData["igv"];
        $dataSend['dias_pagos'] = [];
        $dataSend['moneda'] = "PEN";

        $sql = "select * from dias_ventas where id_venta='$venta'";
        $cuotasVentas = $this->venta->exeSQL($sql);

        foreach ($cuotasVentas as $cuotas) {
            $dataSend['dias_pagos'][] = [
                "monto" => $cuotas['monto'],
                "fecha" => $cuotas['fecha']
            ];
        }

        $sql = "select pv.*,p.descripcion from productos_ventas pv
        join productos p on p.id_producto = pv.id_producto
        where pv.id_venta='$venta'";
        $listaProductos = $this->venta->exeSQL($sql);
        foreach ($listaProductos as $prod) {
            $dataSend['productos'][] = [
                "precio" => number_format($prod['precio'], 2, ".", ""),
                "cantidad" => number_format($prod['cantidad'], 0),
                "cod_pro" => $prod['id_producto'],
                "cod_sunat" => "",
                "descripcion" => $prod['descripcion']
            ];
        }

        $sql = "select * from ventas_servicios where  id_venta='$venta'";
        $listaProductos = $this->venta->exeSQL($sql);
        foreach ($listaProductos as $prod) {
            $dataSend['productos'][] = [
                "precio" => number_format($prod['monto'], 2, ".", ""),
                "cantidad" => number_format($prod['cantidad'], 0),
                "cod_pro" => $prod['id_item'],
                "cod_sunat" => $prod['codsunat'],
                "descripcion" => $prod['descripcion']
            ];
        }

        $dataSend["endpoints"] = $empresa['modo'];

        $dataSend['empresa'] = json_encode([
            'ruc' => $empresa['ruc'],
            'razon_social' => $empresa['razon_social'],
            'direccion' => $empresa['direccion'],
            'ubigeo' => $empresa['ubigeo'],
            'distrito' => $empresa['distrito'],
            'provincia' => $empresa['provincia'],
            'departamento' => $empresa['departamento'],
            'clave_sol' => $empresa['clave_sol'],
            'usuario_sol' => $empresa['user_sol']
        ]);
        $respuesta = ["res" => false];

        if ($ventaData['id_tido'] == 1 || $ventaData['id_tido'] == 2) {
            $dataSend['dias_pagos'] = json_encode($dataSend['dias_pagos']);

            $dataSend['productos'] = json_encode($dataSend['productos']);
            file_put_contents("Dataaaaaaaaaaaaaaaaaaaa.json", json_encode($dataSend));
            if ($ventaData['id_tido'] == 1) {
                $dataResp = $this->sunatApi->genBoletaXML($dataSend);
            } else {
                $dataResp = $this->sunatApi->genFacturaXML($dataSend);
            }
            if ($dataResp["res"]) {
                $respuesta["res"] = true;
                $sql = "select * from ventas_sunat where id_venta = '$venta'";
                if ($rrroooo = $this->venta->exeSQL($sql)->fetch_assoc()) {
                    $sql = "update ventas_sunat set hash='{$dataResp['data']['hash']}',
                      nombre_xml='{$dataResp['data']['nombre_archivo']}',
                      qr_data='{$dataResp['data']['qr']}' where id_venta = '$venta' ";
                    $this->venta->exeSQL($sql);
                } else {
                    $sql = "insert into ventas_sunat set hash='{$dataResp['data']['hash']}',
                      nombre_xml='{$dataResp['data']['nombre_archivo']}',
                      qr_data='{$dataResp['data']['qr']}',  id_venta = '$venta' ";
                    $this->venta->exeSQL($sql);
                }
            }
        }

        return json_encode($respuesta);
    }

    public function listaVentasPorEmpresa()
    {
        return json_encode($this->venta->verFilasPorEmpresas($_POST["empresa"], $_POST["sucursal"]));
    }


    public function enviarDocumentoSunat()
    {
        $sql = "select * from ventas_sunat where id_venta = '{$_POST["cod"]}'";
        $resultado = ["res" => false];
        if ($row = $this->venta->exeSQL($sql)->fetch_assoc()) {
            if ($this->sunatApi->envioIndividualDocumentoV($row["nombre_xml"])) {
                $sql = "update ventas set  enviado_sunat='1' where id_venta = '{$_POST["cod"]}'";
                $this->venta->exeSQL($sql);
                $resultado['res'] = true;
            } else {
                $resultado['msg'] = $this->sunatApi->getMensaje();
            }
        }
        return json_encode($resultado);
    }

    public function anularVenta()
    {
        $this->venta->setIdVenta($_POST['iventa']);
        $c_anulada = new VentaAnulada();
        $c_producto = new ProductoVenta();

        /*$c_producto->setIdVenta($this->venta->getIdVenta());
        $c_producto->eliminar();*/

        $c_anulada->setIdVenta($this->venta->getIdVenta());
        $c_anulada->setFecha(date("Y-m-d"));
        $c_anulada->setMotivo("-");
        $resultado = ["res" => false];
        if ($this->venta->anular()) {
            $resultado['res'] = true;
            $c_anulada->insertar();
        }
        return json_encode($resultado);
    }

    public function listarVentas()
    {
        require_once "app/clases/serverside.php";
        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        $table_data = new TableData();
        $where = ($_SESSION['rol'] == 1) ? "" : "where sucursal = {$_SESSION["sucursal"]} ";
        $table_data->get("view_ventas", "id_venta", [
            "cod_v",
            "sn_v",
            "fecha_emision",
            "datos_cl",
            "subtotal",
            "igv_v",
            "total",
            "doc_ventae",
            "estado",
            "id_venta",
        ], $where);

        //$this->venta->setIdEmpresa($_SESSION['id_empresa']);
        //$lista = $this->venta->verFilas("202202");
        //return json_encode($lista);
    }
    public function detalleVenta()
    {
        //echo $_POST['iventa'];
        $this->venta->setIdVenta($_POST['iventa']);
        return $this->venta->verDetalle();
    }
    public function tipoVenta()
    {
        //echo $_POST['iventa'];
        $idVenta = $_POST['iventa'];
        $sqlProducto = "SELECT * FROM productos_ventas WHERE id_venta = $idVenta";
        $sqlServicio = "SELECT * FROM ventas_servicios WHERE id_venta = $idVenta";
        $returnFetch = $this->venta->exeSQL($sqlProducto)->fetch_assoc();
        $respuesta['tipo'] = '';
        $respuesta['res'] = false;
        if (empty($returnFetch)) {
            $returnFetchServicios = $this->venta->exeSQL($sqlServicio)->fetch_assoc();
            $respuesta['tipo'] = 'servicio';
            $respuesta['data'] = $returnFetchServicios;
            $respuesta['res'] = true;
            return json_encode($respuesta);
        } else {
            $respuesta['tipo'] = 'productos';
            $respuesta['data'] = $returnFetch;
            $respuesta['res'] = true;
            return json_encode($respuesta);
        }
    }


    public function detalleVenta2()
    {
        //echo $_POST['iventa'];
        $this->venta->setIdVenta($_POST['iventa']);
        return $this->venta->verDetalle2();
    }

    public function editVentaServicio()
    {
        $resultado = ["res" => false];



        $dataSend = [];
        $dataSend["certGlobal"] = false;


        $c_cliente = new Cliente();
        $c_venta = new Venta();
        $c_tido = new DocumentoEmpresa();
        $c_detalle = new ProductoVenta();
        $c_servicio = new VentaServicio();
        // $c_curl = new SendCurlVenta();
        $c_sunat = new VentaSunat();
        $c_varios = new Varios();

        $id_empresa = $_SESSION['id_empresa'];

        $sql = "SELECT * from empresas where id_empresa = " . $id_empresa;

        $respEmpre = $c_venta->exeSQL($sql)->fetch_assoc();

        $igv_empr_sel = $respEmpre['igv'];


        $c_cliente->setIdEmpresa($id_empresa);
        $c_cliente->setDocumento(filter_input(INPUT_POST, 'num_doc'));
        $c_cliente->setDatos(filter_input(INPUT_POST, 'nom_cli'));
        $c_cliente->setDireccion(filter_input(INPUT_POST, 'dir_cli'));
        //$c_cliente->setDireccion2(filter_input(INPUT_POST, 'dir2_cli'));

        if ($c_cliente->getDocumento() == "") {
            $numDoc = $_POST['num_doc'] == '' ? '' : $_POST['num_doc'];
            $nombre = $_POST['nom_cli'] == '' ? '' : $_POST['nom_cli'];
            $c_cliente->modificar("SD" . $c_varios->generarCodigo(5), $nombre, $_POST['id_cliente']);
            /*             $c_cliente->setDocumento("SD" . $c_varios->generarCodigo(5));
            $c_cliente->insertar(); */
        } else {
            $numDoc = $_POST['num_doc'] == '' ? '' : $_POST['num_doc'];
            $nombre = $_POST['nom_cli'] == '' ? '' : $_POST['nom_cli'];
            $c_cliente->modificar($numDoc, $nombre, $_POST['id_cliente']);
            /*  $numDoc = $_POST['num_doc'] == '' ? '' : $_POST['num_doc'];
            $nombre = $_POST['nom_cli'] == '' ? '' : $_POST['nom_cli'];
            $c_cliente->modificar($numDoc, $nombre, $_POST['id_cliente']); */
            /*  if (!$c_cliente->verificarDocumento()) {
                $c_cliente->insertar();
            } else {
                $numDoc = $_POST['num_doc'] == '' ? '' : $_POST['num_doc'];
                $nombre = $_POST['nom_cli'] == '' ? '' : $_POST['nom_cli'];
                $c_cliente->modificar($numDoc, $nombre, $_POST['id_cliente']);
            } */
        }
        /*  $numDoc = $_POST['num_doc'] == '' ? '' : $_POST['num_doc'];
        $nombre = $_POST['nom_cli'] == '' ? '' : $_POST['nom_cli'];
        $c_cliente->modificar($numDoc, $nombre, $_POST['id_cliente']); */


        $resultado["email"] = $c_cliente->getEmail() ? $c_cliente->getEmail() : '';
        $resultado["cel"] = $c_cliente->getTelefono() ? $c_cliente->getTelefono() : '';

        $direccionselk = '';
        if ($_POST['dir_pos'] == 1) {
            $direccionselk = $_POST['dir_cli'];
        } elseif ($_POST['dir_pos'] == 2) {
            $direccionselk = $_POST['dir2_cli'];
        }

        if (trim($c_cliente->getDocumento()) == "") {
            $c_cliente->setDocumento('');
        }
        if (strlen(trim($direccionselk)) == "") {
            $direccionselk = '-';
        }
        if (trim($c_cliente->getDatos()) == "") {
            $c_cliente->setDatos('-');
        }

        $dataSend['cliente'] = json_encode([
            'doc_num' => $c_cliente->getDocumento(),
            'nom_RS' => $c_cliente->getDatos(),
            'direccion' => $direccionselk
        ]);
        $c_venta->setDireccion($direccionselk);
        /*   $dataSend['productos'] = []; */

        $c_venta->setApliIgv($_POST['apli_igv']);
        $c_venta->setIdEmpresa($id_empresa);
        $c_venta->setFecha($_POST['fecha']);
        $c_venta->setFechaVenc($_POST['tipo_pago'] == '1' ? $_POST['fecha'] : $_POST['fechaVen']);
        $c_venta->setDiasPagos($_POST['dias_pago']);
        $c_venta->setIdTipoPago($_POST['tipo_pago']);
        $c_venta->setObserva($_POST['observ']);

        $c_venta->setIdCliente($_POST['id_cliente']);
        $c_venta->setIgv($igv_empr_sel);
        $c_venta->setTotal(filter_input(INPUT_POST, 'total'));
        /*     $c_venta->setIdVenta(); */
        $tipoventa = filter_input(INPUT_POST, 'tipoventa');
        /* 

        $dataSend['apli_igv'] = $_POST['apli_igv'] == 1;
        $dataSend['total'] = $c_venta->getTotal();
        $dataSend['serie'] = $c_tido->getSerie();
        $dataSend['numero'] = $c_tido->getNumero();
        $dataSend['fechaE'] = $c_venta->getFecha();
        $dataSend['fechaV'] = $c_venta->getFechaVenc();
        $dataSend['tipo_pago'] = $c_venta->getIdTipoPago();
        $dataSend['igv_venta'] = $igv_empr_sel;
        $dataSend['dias_pagos'] = [];
        $dataSend['moneda'] = "PEN"; */

        $listaPagos = json_decode($_POST['dias_lista'], true);

        if ($c_venta->editar($_POST['idVenta'])) {

            $resultado["res"] = true;
            $array_detalle = json_decode($_POST['listaPro'], true);
            foreach ($listaPagos as $diaP) {
                $sql = "insert into dias_ventas set id_venta='{$c_venta->getIdVenta()}',
                    monto='{$diaP['monto']}',fecha='{$diaP['fecha']}',estado='0'";
                $c_venta->exeSQL($sql);
                /*  $dataSend['dias_pagos'][] = [
                    "monto" => $diaP['monto'],
                    "fecha" => $diaP['fecha']
                ]; */
            }
            /*    $dataSend['dias_pagos'] = json_encode($dataSend['dias_pagos']); */

            $nroitem = 1;


            /*  $c_servicio->setIdventa(); */
            $c_servicio->eliminar($_POST['idVenta']);

            foreach ($array_detalle as $fila) {
                $c_servicio->setDescripcion($fila['descripcion']);
                $c_servicio->setCantidad($fila['cantidad']);
                $c_servicio->setMonto($fila['precioVenta']);
                $c_servicio->setCodsunat(isset($fila['codsunat']) ? $fila['codsunat'] : '');
                $c_servicio->setIditem($nroitem);
                /*  $c_servicio->setIdventa($_POST['idVenta']); */
                $c_servicio->editar($_POST['idVenta']);
                $nroitem++;
                /*     $dataSend['productos'][] = [
                    "precio" => $fila['precio'],
                    "cantidad" => $fila['cantidad'],
                    "cod_pro" => $nroitem,
                    "cod_sunat" => isset($fila['codsunat']) ? $fila['codsunat'] : '',
                    "descripcion" => $fila['descripcion']
                ]; */
            }

            //definir url segun el tipo de documento sunat
            if ($c_venta->getIdTido() == 1) {
                $archivo = "boleta";
            }
            if ($c_venta->getIdTido() == 2) {
                $archivo = "factura";
            }

            /*   if ($c_venta->getIdTido() == 1 || $c_venta->getIdTido() == 2) { */

            /* 
                $dataSend["endpoints"] = $respEmpre['modo'];

                $dataSend['empresa'] = json_encode([
                    'ruc' => $respEmpre['ruc'],
                    'razon_social' => $respEmpre['razon_social'],
                    'direccion' => $respEmpre['direccion'],
                    'ubigeo' => $respEmpre['ubigeo'],
                    'distrito' => $respEmpre['distrito'],
                    'provincia' => $respEmpre['provincia'],
                    'departamento' => $respEmpre['departamento'],
                    'clave_sol' => $respEmpre['clave_sol'],
                    'usuario_sol' => $respEmpre['user_sol']
                ]);



                $dataSend['productos'] = json_encode($dataSend['productos']); */
            /* 
                if ($c_venta->getIdTido() == 1) {
                    $dataResp = $this->sunatApi->genBoletaXML($dataSend);
                } else {
                    $dataResp = $this->sunatApi->genFacturaXML($dataSend);
                }



                if ($dataResp["res"]) {
                    $c_sunat->setIdVenta($c_venta->getIdVenta());
                    $c_sunat->setHash($dataResp['data']['hash']);
                    $c_sunat->setNombreXml($dataResp['data']['nombre_archivo']);
                    $c_sunat->setQrData($dataResp['data']['qr']);
                    $c_sunat->insertar();
                } else {
                } */
            /* } */ /* else {
                $c_sunat->setIdVenta($c_venta->getIdVenta());
                $c_sunat->setHash("-");
                $c_sunat->setNombreXml("-");
                $c_sunat->setQrData('-');
                $c_sunat->insertar();

                $resultado["valor"] = $c_venta->getIdVenta();
            } */
            /*    $resultado["nomFact"] = $c_sunat->getNombreXml() . ".pdf";
            $resultado["urlFact"] = URL::to('/venta/comprobante/pdf/' . $c_sunat->getIdVenta() . '/' . $c_sunat->getNombreXml());
            $resultado["urlFactd"] = URL::to('/venta/comprobante/pdfd/' . $c_sunat->getIdVenta() . '/' . $c_sunat->getNombreXml());
        } */
        }
        /*  $_REQUEST */
        $resultado["nomFact"] =  '2020' . ".pdf";
        $resultado["urlFact"] = URL::to('/venta/comprobante/pdf/' . $_POST['idVenta'] . '/' . '2020');
        $resultado["urlFactd"] = URL::to('/venta/comprobante/pdfd/' . $_POST['idVenta'] . '/2020');

        return json_encode($resultado);
    }
    public function editVentaProducto()
    {
        /* echo '<pre>';
        print_r(json_decode($_POST['listaPro'], true));
        echo '</pre>';
        exit(); */
        $resultado = ["res" => false];



        $dataSend = [];
        $dataSend["certGlobal"] = false;


        $c_cliente = new Cliente();
        $c_venta = new Venta();
        $c_tido = new DocumentoEmpresa();
        $c_detalle = new ProductoVenta();
        /*  $c_servicio = new VentaServicio(); */
        // $c_curl = new SendCurlVenta();
        $c_sunat = new VentaSunat();
        $c_varios = new Varios();

        $id_empresa = $_SESSION['id_empresa'];

        $sql = "SELECT * from empresas where id_empresa = " . $id_empresa;

        $respEmpre = $c_venta->exeSQL($sql)->fetch_assoc();

        $igv_empr_sel = $respEmpre['igv'];


        $c_cliente->setIdEmpresa($id_empresa);
        $c_cliente->setDocumento(filter_input(INPUT_POST, 'num_doc'));
        $c_cliente->setDatos(filter_input(INPUT_POST, 'nom_cli'));
        $c_cliente->setDireccion(filter_input(INPUT_POST, 'dir_cli'));
        //$c_cliente->setDireccion2(filter_input(INPUT_POST, 'dir2_cli'));


        if ($c_cliente->getDocumento() == "") {
            $numDoc = $_POST['num_doc'] == '' ? '' : $_POST['num_doc'];
            $nombre = $_POST['nom_cli'] == '' ? '' : $_POST['nom_cli'];
            $c_cliente->modificar("SD" . $c_varios->generarCodigo(5), $nombre, $_POST['id_cliente']);
            /*             $c_cliente->setDocumento("SD" . $c_varios->generarCodigo(5));
            $c_cliente->insertar(); */
        } else {
            $numDoc = $_POST['num_doc'] == '' ? '' : $_POST['num_doc'];
            $nombre = $_POST['nom_cli'] == '' ? '' : $_POST['nom_cli'];
            $c_cliente->modificar($numDoc, $nombre, $_POST['id_cliente']);
            /*  $numDoc = $_POST['num_doc'] == '' ? '' : $_POST['num_doc'];
            $nombre = $_POST['nom_cli'] == '' ? '' : $_POST['nom_cli'];
            $c_cliente->modificar($numDoc, $nombre, $_POST['id_cliente']); */
            /*  if (!$c_cliente->verificarDocumento()) {
                $c_cliente->insertar();
            } else {
                $numDoc = $_POST['num_doc'] == '' ? '' : $_POST['num_doc'];
                $nombre = $_POST['nom_cli'] == '' ? '' : $_POST['nom_cli'];
                $c_cliente->modificar($numDoc, $nombre, $_POST['id_cliente']);
            } */
        }

        $resultado["email"] = $c_cliente->getEmail() ? $c_cliente->getEmail() : '';
        $resultado["cel"] = $c_cliente->getTelefono() ? $c_cliente->getTelefono() : '';

        $direccionselk = '';
        if ($_POST['dir_pos'] == 1) {
            $direccionselk = $_POST['dir_cli'];
        } elseif ($_POST['dir_pos'] == 2) {
            $direccionselk = $_POST['dir2_cli'];
        }

        if (trim($c_cliente->getDocumento()) == "") {
            $c_cliente->setDocumento('');
        }
        if (strlen(trim($direccionselk)) == "") {
            $direccionselk = '-';
        }
        if (trim($c_cliente->getDatos()) == "") {
            $c_cliente->setDatos('-');
        }

        /*  $dataSend['cliente'] = json_encode([
            'doc_num' => $c_cliente->getDocumento(),
            'nom_RS' => $c_cliente->getDatos(),
            'direccion' => $direccionselk
        ]); */
        $c_venta->setDireccion($direccionselk);
        $c_tido->setIdEmpresa($id_empresa);
        $c_tido->setIdTido(filter_input(INPUT_POST, 'tipo_doc'));
        $c_tido->obtenerDatos();
        $c_venta->setApliIgv($_POST['apli_igv']);
        $c_venta->setIdEmpresa($id_empresa);
        $c_venta->setFecha($_POST['fecha']);
        $c_venta->setFechaVenc($_POST['tipo_pago'] == '1' ? $_POST['fecha'] : $_POST['fechaVen']);
        $c_venta->setDiasPagos($_POST['dias_pago']);
        $c_venta->setIdTipoPago($_POST['tipo_pago']);
        $c_venta->setObserva($_POST['observ']);
        $c_venta->setIdTido($c_tido->getIdTido());
        $c_venta->setSerie($c_tido->getSerie());
        $c_venta->setNumero($c_tido->getNumero());
        $c_venta->setIdCliente($_POST['id_cliente']);
        $c_venta->setIgv($igv_empr_sel);
        $c_venta->setTotal(filter_input(INPUT_POST, 'total'));


        /*      $dataSend['apli_igv'] = $_POST['apli_igv'] == 1;
        $dataSend['total'] = $c_venta->getTotal();
        $dataSend['serie'] = $c_tido->getSerie();
        $dataSend['numero'] = $c_tido->getNumero();
        $dataSend['fechaE'] = $c_venta->getFecha();
        $dataSend['fechaV'] = $c_venta->getFechaVenc();
        $dataSend['tipo_pago'] = $c_venta->getIdTipoPago();
        $dataSend['igv_venta'] = $igv_empr_sel;
        $dataSend['dias_pagos'] = [];
        $dataSend['moneda'] = "PEN"; */

        $listaPagos = json_decode($_POST['dias_lista'], true);

        if ($c_venta->editar($_POST['idVenta'])) {

            $resultado["res"] = true;
            $array_detalle = json_decode($_POST['listaPro'], true);
            foreach ($listaPagos as $diaP) {
                $sql = "insert into dias_ventas set id_venta='{$_POST['idVenta']}',
                    monto='{$diaP['monto']}',fecha='{$diaP['fecha']}',estado='0'";
                $c_venta->exeSQL($sql);
                /*  $dataSend['dias_pagos'][] = [
                    "monto" => $diaP['monto'],
                    "fecha" => $diaP['fecha']
                ]; */
            }
            /*  $dataSend['dias_pago'] = json_encode($dataSend['dias_pagos']); */
            #verificar los cambios de cantidades
            //    $sql_detalle = "SELECT * FROM productos_ventas WHERE id_producto=92 and id_venta='{$_POST['idVenta']}';";
            $sql_detalle = "SELECT * FROM productos_ventas WHERE id_venta='{$_POST['idVenta']}'";
            $detalle_venta = $this->conexion->query($sql_detalle)->fetch_all(MYSQLI_ASSOC);
            $cambios_cantidad = [];
            $i = 0;
            $arr_product_id_cambios = [];
            #retornamos el stock al inventario
            foreach ($detalle_venta as $detalle) {
                $presenta_cnt = $detalle['presenta_cnt'];
                $presenta_cnt = ($presenta_cnt == 0) ? 1 : $presenta_cnt;
                $cantidad = $detalle['cantidad'] * $presenta_cnt;
                #
                $sql = "update productos set  cantidad= cantidad+{$cantidad} where id_producto='{$detalle['id_producto']}' ";
                //echo $sql;
                $this->conexion->query($sql);
            }
            # primero recorremos los nuevos productos
            foreach ($array_detalle as $key => $a_detalle) {
                $existe = -1;
                foreach ($detalle_venta as $detalle) {
                    if ($detalle['id_producto'] == $a_detalle['productoid']) {
                        $cantidad_db = $detalle['cantidad'] * $detalle['presenta_cnt'];
                        $presenta_cnt = (isset($a_detalle['presenta_cnt'])) ? $a_detalle['presenta_cnt'] : $a_detalle['presentacionCnt'];
                        $cantidad_ingreso = $a_detalle['cantidad'] * $presenta_cnt;
                        $cantidad = $cantidad_db - $cantidad_ingreso;
                        if ($cantidad > 0) {
                            $arr_product_id_cambios[] = $detalle['id_producto'];
                            $cambios_cantidad[$i] = $detalle;
                            $cambios_cantidad[$i]['cantidad'] = $cantidad;
                            $cambios_cantidad[$i]['signo'] = '+';
                            $i++;
                        }
                        /* else if ($cantidad < 0) {
                            $arr_product_id_cambios[] = $detalle['id_producto'];
                            $cambios_cantidad[$i] = $detalle;
                            $cambios_cantidad[$i]['cantidad'] = abs($cantidad);
                            $cambios_cantidad[$i]['signo'] = '-';
                            $i++;
                        } */
                        $existe = 1;
                        break;
                    }
                }
                if ($existe === -1) {
                    $arr_product_id_cambios[] = $a_detalle['productoid'];
                    $cambios_cantidad[$i] = $a_detalle;
                    $cambios_cantidad[$i]['signo'] = '+';
                    $i++;
                }
            }
            #luego verificamos en los productos que existian
            foreach ($detalle_venta as $detalle) {
                if (in_array($detalle['id_producto'], $arr_product_id_cambios)) continue;
                $existe = -1;
                foreach ($array_detalle as $a_detalle) {
                    if ($detalle['id_producto'] == $a_detalle['productoid']) {
                        $cantidad_db = $detalle['cantidad'] * $detalle['presenta_cnt'];
                        $presenta_cnt = (isset($a_detalle['presenta_cnt'])) ? $a_detalle['presenta_cnt'] : $a_detalle['presentacionCnt'];
                        $cantidad_ingreso = $a_detalle['cantidad'] * $presenta_cnt;
                        $cantidad = $cantidad_db - $cantidad_ingreso;
                        if ($cantidad > 0) {
                            $arr_product_id_cambios[] = $detalle['id_producto'];
                            $cambios_cantidad[$i] = $detalle;
                            $cambios_cantidad[$i]['cantidad'] = $cantidad;
                            $cambios_cantidad[$i]['signo'] = '+';
                        }
                        /* else if ($cantidad < 0) {
                            $arr_product_id_cambios[] = $detalle['id_producto'];
                            $cambios_cantidad[$i] = $detalle;
                            $cambios_cantidad[$i]['cantidad'] = abs($cantidad);
                            $cambios_cantidad[$i]['signo'] = '-';
                        } */
                        $existe = 1;
                        break;
                    }
                }
                if ($existe === -1) {
                    $arr_product_id_cambios[] = $detalle['id_producto'];
                    $cambios_cantidad[$i] = $detalle;
                    $cambios_cantidad[$i]['signo'] = '+';
                    $cambios_cantidad[$i]['algo'] = 'asasd';
                    $cambios_cantidad[$i]['algo1'] = $detalle['id_producto'];
                }
                $i++;
            }
            #
            foreach ($cambios_cantidad as $item) {
                $presenta = (isset($item['presenta'])) ? $item['presenta'] : $item['presentacion'];
                $presenta_cnt = (isset($item['presenta_cnt'])) ? $item['presenta_cnt'] : $item['presentacionCnt'];
                $presenta_cnt = ($presenta_cnt == 0) ? 1 : $presenta_cnt;
                #
                if ($c_venta->getIdTido() == 6) {
                    $sql_1 = "
                   INSERT INTO devoluciones_nv (id_venta,id_producto,id_usuario,cantidad,presenta,presenta_cnt,signo)
                   VALUES ('{$item['id_venta']}','{$item['id_producto']}','{$_SESSION['usuario_fac']}','{$item['cantidad']}','{$presenta}','{$presenta_cnt}','{$item['signo']}')
                   ";
                    //echo $sql_1;
                    $this->conexion->query($sql_1);
                }
            }

            /* $c_detalle->setIdVenta($c_venta->getIdVenta()); */
            $c_detalle->eliminar($_POST['idVenta']);

            /*  $c_servicio->eliminar($_POST['idVenta']);   */
            $c_detalle->setIdVenta($_POST['idVenta']);
            foreach ($array_detalle as $fila) {
                $presenta = (isset($fila['presenta'])) ? $fila['presenta'] : $fila['presentacion'];
                $presenta_cnt = (isset($fila['presenta_cnt'])) ? $fila['presenta_cnt'] : $fila['presentacionCnt'];
                $presenta_cnt = ($presenta_cnt == 0) ? 1 : $presenta_cnt;
                $c_detalle->setIdProducto($fila['productoid']);
                $c_detalle->setCantidad($fila['cantidad']);
                $c_detalle->setCosto($fila['costo']);
                $c_detalle->setMedida($fila['medida']);
                $c_detalle->setPresenta($presenta);
                $c_detalle->setPresentaCnt($presenta_cnt);
                $c_detalle->setPrecio($_POST['moneda'] == 1 ? $fila['precioVenta'] : $fila['precioVenta'] / $_POST['tc']);
                $c_detalle->setPrecioUsado($_POST['moneda'] == 1 ? $fila['precio_usado'] : $fila['precio_usado'] / $_POST['tc']);
                $c_detalle->insertar();
                /*   $dataSend['productos'][] = [
                    "precio" => $fila['precio'],
                    "cantidad" => $fila['cantidad'],
                    "cod_pro" => $fila['productoid'],
                    "cod_sunat" => "",
                    "descripcion" => $fila['descripcion']
                ]; */
            }

            //definir url segun el tipo de documento sunat
            /*   if ($c_venta->getIdTido() == 1) {
                $archivo = "boleta";
            }
            if ($c_venta->getIdTido() == 2) {
                $archivo = "factura";
            }

            if ($c_venta->getIdTido() == 1 || $c_venta->getIdTido() == 2) {


                $dataSend["endpoints"] = $respEmpre['modo'];

                $dataSend['empresa'] = json_encode([
                    'ruc' => $respEmpre['ruc'],
                    'razon_social' => $respEmpre['razon_social'],
                    'direccion' => $respEmpre['direccion'],
                    'ubigeo' => $respEmpre['ubigeo'],
                    'distrito' => $respEmpre['distrito'],
                    'provincia' => $respEmpre['provincia'],
                    'departamento' => $respEmpre['departamento'],
                    'clave_sol' => $respEmpre['clave_sol'],
                    'usuario_sol' => $respEmpre['user_sol']
                ]);



                $dataSend['productos'] = json_encode($dataSend['productos']);

                if ($c_venta->getIdTido() == 1) {
                    $dataResp = $this->sunatApi->genBoletaXML($dataSend);
                } else {
                    $dataResp = $this->sunatApi->genFacturaXML($dataSend);
                }



                if ($dataResp["res"]) {
                    $c_sunat->setIdVenta($c_venta->getIdVenta());
                    $c_sunat->setHash($dataResp['data']['hash']);
                    $c_sunat->setNombreXml($dataResp['data']['nombre_archivo']);
                    $c_sunat->setQrData($dataResp['data']['qr']);
                    $c_sunat->insertar();
                } else {
                }
            } else {
                $c_sunat->setIdVenta($c_venta->getIdVenta());
                $c_sunat->setHash("-");
                $c_sunat->setNombreXml("-");
                $c_sunat->setQrData('-');
                $c_sunat->insertar();

                $resultado["valor"] = $c_venta->getIdVenta();
            } */
            $resultado["nomFact"] =  '2020' . ".pdf";
            $resultado["urlFact"] = URL::to('/venta/comprobante/pdf/' . $_POST['idVenta'] . '/' . '2020');
            $resultado["urlFactd"] = URL::to('/venta/comprobante/pdfd/' . $_POST['idVenta'] . '/2020');
        }

        return json_encode($resultado);
    }
    public function guardarVentas()
    {

        //echo "aaa";

        /*    return json_encode("SELECT * FROM sucursales WHERE cod_sucursal ='{$_SESSION['sucursal']}' AND empresa_id=" . $_SESSION['id_empresa']);
        die(); */
        /*      echo $_SESSION['sucursal'];
        var_dump($_SESSION['sucursal'] !== '1'); */
        /*  if (!$_SESSION['sucursal'] == '1') {
           
        } */
        /* die(); */
        $resultado = ["res" => false];



        $dataSend = [];
        $dataSend["certGlobal"] = false;


        $c_cliente = new Cliente();
        $c_venta = new Venta();
        $c_tido = new DocumentoEmpresa();
        $c_detalle = new ProductoVenta();
        $c_servicio = new VentaServicio();
        // $c_curl = new SendCurlVenta();
        $c_sunat = new VentaSunat();
        $c_varios = new Varios();
        $c_guia = new GuiaRemision();

        $id_empresa = $_SESSION['id_empresa'];

        $sql = "SELECT * from empresas where id_empresa = " . $id_empresa;

        $respEmpre = $c_venta->exeSQL($sql)->fetch_assoc();

        $igv_empr_sel = $respEmpre['igv'];


        $c_cliente->setIdEmpresa($id_empresa);
        $c_cliente->setDocumento(filter_input(INPUT_POST, 'num_doc'));
        $c_cliente->setDatos(filter_input(INPUT_POST, 'nom_cli'));
        $c_cliente->setDireccion(filter_input(INPUT_POST, 'dir_cli'));
        //$c_cliente->setDireccion2(filter_input(INPUT_POST, 'dir2_cli'));


        if ($c_cliente->getDocumento() == "") {
            $c_cliente->setDocumento("SD" . $c_varios->generarCodigo(5));
            $c_cliente->insertar();
        } else {
            if (!$c_cliente->verificarDocumento()) {
                $c_cliente->insertar();
            }
        }

        $resultado["email"] = $c_cliente->getEmail() ? $c_cliente->getEmail() : '';
        $resultado["cel"] = $c_cliente->getTelefono() ? $c_cliente->getTelefono() : '';

        $direccionselk = '';
        if ($_POST['dir_pos'] == 1) {
            $direccionselk = $_POST['dir_cli'];
        } elseif ($_POST['dir_pos'] == 2) {
            $direccionselk = $_POST['dir2_cli'];
        }

        if (trim($c_cliente->getDocumento()) == "") {
            $c_cliente->setDocumento('');
        }
        if (strlen(trim($direccionselk)) == "") {
            $direccionselk = '-';
        }
        if (trim($c_cliente->getDatos()) == "") {
            $c_cliente->setDatos('-');
        }

        $dataSend['cliente'] = json_encode([
            'doc_num' => $c_cliente->getDocumento(),
            'nom_RS' => $c_cliente->getDatos(),
            'direccion' => $direccionselk
        ]);
        $c_venta->setDireccion($direccionselk);
        $dataSend['productos'] = [];
        $c_tido->setIdEmpresa($id_empresa);
        $c_tido->setIdTido(filter_input(INPUT_POST, 'tipo_doc'));
        $c_tido->obtenerDatos();
        $c_venta->setApliIgv($_POST['apli_igv']);
        $c_venta->setIdEmpresa($id_empresa);
        $c_venta->setFecha($_POST['fecha']);
        $c_venta->setFechaVenc($_POST['tipo_pago'] == '1' ? $_POST['fecha'] : $_POST['fechaVen']);
        $c_venta->setDiasPagos($_POST['dias_pago']);
        $c_venta->setIdTipoPago($_POST['tipo_pago']);
        $metodo = intval($_POST['metodo']);
        $c_venta->setMetodo($metodo);
        $c_venta->setObserva($_POST['observ']);
        $c_venta->setIdTido($c_tido->getIdTido());
        $c_venta->setSerie($c_tido->getSerie());
        $c_venta->setNumero($c_tido->getNumero());
        $c_venta->setIdCliente($c_cliente->getIdCliente());
        $c_venta->setIgv($igv_empr_sel);
        $c_venta->setTotal(filter_input(INPUT_POST, 'total'));
        $c_venta->setIdCoti($_POST['idCoti']);
        $tipoventa = filter_input(INPUT_POST, 'tipoventa');


        $dataSend['apli_igv'] = $_POST['apli_igv'] == 1;
        $dataSend['total'] = number_format($c_venta->getTotal(), 2, '.', '');
        $dataSend['serie'] = $c_tido->getSerie();
        $dataSend['numero'] = $c_tido->getNumero();
        $dataSend['fechaE'] = $c_venta->getFecha();
        $dataSend['fechaV'] = $c_venta->getFechaVenc();
        $dataSend['tipo_pago'] = $c_venta->getIdTipoPago();
        $dataSend['igv_venta'] = $igv_empr_sel;
        $dataSend['dias_pagos'] = [];
        $dataSend['moneda'] = $_POST['moneda'] == 1 ? "PEN" : "USD";
        $dataSend['tc'] = $_POST['tc'];


        $datosGuiaRemosion = json_decode($_POST['datosGuiaRemosion'], true);
        $datosTransporteGuiaRemosion = json_decode($_POST['datosTransporteGuiaRemosion'], true);

        /* echo json_encode($datosGuiaRemosion);

        return; */
        /*   $datosUbigeoGuiaRemosion = json_decode($_POST['datosUbigeoGuiaRemosion'], true); */
        /*   echo json_encode($datosGuiaRemosion['fecha_emision']);
        echo json_encode($datosGuiaRemosion['dir_cli']);
        echo json_encode($_POST['datosUbigeoGuiaRemosion']);
        echo json_encode($datosTransporteGuiaRemosion); */
        /*  echo json_encode($datosGuiaRemosion['fecha_emision']);
        echo json_encode($datosGuiaRemosion['fecha_emision']); */
        /*  return */
        $listaPagos = json_decode($_POST['dias_lista'], true);

        if ($c_venta->insertar()) {
            $pagos = $_POST["pagos"];
            foreach ($pagos as $i => $pago) {
                $npago = $i + 1;
                if ($pago["metodoPago"] !== "" && $pago['montoPago'] !== "") {
                    $sql = "insert into ventas_pagos set id_venta='{$c_venta->getIdVenta()}',
                    metodo_pago='{$pago['metodoPago']}',monto='{$pago['montoPago']}',npago='{$npago}'";
                    $c_venta->exeSQL($sql);
                }
            }
            if (isset($_POST['cotiId'])) {
                $sql = "UPDATE cotizaciones set estado = 1 WHERE cotizacion_id = '{$_POST['cotiId']}'";
                $this->conexion->query($sql);
            }


            $resultado["res"] = true;
            $array_detalle = json_decode($_POST['listaPro'], true);
            foreach ($listaPagos as $diaP) {
                $sql = "insert into dias_ventas set id_venta='{$c_venta->getIdVenta()}',
                    monto='{$diaP['monto']}',fecha='{$diaP['fecha']}',estado='0'";
                $c_venta->exeSQL($sql);
                $dataSend['dias_pagos'][] = [
                    "monto" => $diaP['monto'],
                    "fecha" => $diaP['fecha']
                ];
            }
            $dataSend['dias_pagos'] = json_encode($dataSend['dias_pagos']);

            $dataSaveLog = "Venta: {$c_venta->getIdVenta()}, fecha: " . date("Y-m-d") . "\n\n";

            if ($tipoventa == 1) {
                $c_detalle->setIdVenta($c_venta->getIdVenta());
                foreach ($array_detalle as $fila) {
                    $presenta = (isset($fila['presenta'])) ? $fila['presenta'] : $fila['presentacion'];
                    $presenta_cnt = (isset($fila['presenta_cnt'])) ? $fila['presenta_cnt'] : $fila['presentacionCnt'];
                    $c_detalle->setIdProducto($fila['productoid']);
                    $c_detalle->setCantidad($fila['cantidad']);
                    $c_detalle->setCosto($fila['costo']);
                    $c_detalle->setMedida($fila['medida']);
                    $c_detalle->setPresenta($presenta);
                    $c_detalle->setPresentaCnt($presenta_cnt);
                    $c_detalle->setPrecio($_POST['moneda'] == 1 ? $fila['precioVenta'] : $fila['precioVenta'] / $_POST['tc']);
                    $c_detalle->setPrecioUsado($_POST['moneda'] == 1 ? $fila['precio_usado'] : $fila['precio_usado'] / $_POST['tc']);
                    if ($c_detalle->insertar()) {
                        $dataSaveLog .= "Prod: " . $c_detalle->getSql() . " - true";
                    } else {
                        $dataSaveLog .= "Prod: " . $c_detalle->getSql() . " - false \n";
                        $dataSaveLog .= $c_detalle->getSqlError() . "\n\n\n";
                    }

                    $dataSend['productos'][] = [
                        "precio" => $_POST['moneda'] == 1 ? $fila['precioVenta'] : number_format($fila['precioVenta'] / $_POST['tc'], 2, '.', ''),
                        "cantidad" => $fila['cantidad'],
                        "cod_pro" => $fila['productoid'],
                        "cod_sunat" => "",
                        "descripcion" => $fila['descripcion']
                    ];

                    //$sql="update productos set  cantidad= cantidad-'{$fila['cantidad']}' where id_producto='{$fila['productoid']}' ";
                    //$c_venta->exeSQL($sql);
                }
            }
            file_put_contents("files/log/ventas/Venta_" . $c_venta->getIdVenta() . "_" . $dataSend['serie'] . '-' . $dataSend['numero'] . '.txt', $dataSaveLog);

            if ($tipoventa == 2) {
                $nroitem = 1;
                $c_servicio->setIdventa($c_venta->getIdVenta());
                foreach ($array_detalle as $fila) {
                    $c_servicio->setDescripcion($fila['descripcion']);
                    $c_servicio->setCantidad($fila['cantidad']);
                    $c_servicio->setMonto($fila['precioVenta']);
                    $c_servicio->setCodsunat(isset($fila['codsunat']) ? $fila['codsunat'] : '');
                    $c_servicio->setIditem($nroitem);
                    $c_servicio->insertar();
                    $nroitem++;
                    $dataSend['productos'][] = [
                        "precio" => $fila['precioVenta'],
                        "cantidad" => $fila['cantidad'],
                        "cod_pro" => $nroitem,
                        "cod_sunat" => isset($fila['codsunat']) ? $fila['codsunat'] : '',
                        "descripcion" => $fila['descripcion']
                    ];
                }
            }


            //definir url segun el tipo de documento sunat
            if ($c_venta->getIdTido() == 1) {
                $archivo = "boleta";
            }
            if ($c_venta->getIdTido() == 2) {
                $archivo = "factura";
            }

            $nom_xmlFac = '-';

            if ($c_venta->getIdTido() == 1 || $c_venta->getIdTido() == 2) {


                $dataSend["endpoints"] = $respEmpre['modo'];
                //


                if ($_SESSION['sucursal'] != '1') {
                    $datoSucursal = $this->conexion->query("SELECT * FROM sucursales WHERE cod_sucursal ='{$_SESSION['sucursal']}' AND empresa_id=" . $_SESSION['id_empresa'])->fetch_assoc();
                    $dataSend['empresa'] = json_encode([
                        'ruc' => $respEmpre['ruc'],
                        'razon_social' => $respEmpre['razon_social'],
                        'direccion' => $datoSucursal['direccion'],
                        'ubigeo' => $datoSucursal['ubigeo'],
                        'distrito' => $datoSucursal['distrito'],
                        'provincia' => $datoSucursal['provincia'],
                        'departamento' => $datoSucursal['departamento'],
                        'clave_sol' => $respEmpre['clave_sol'],
                        'usuario_sol' => $respEmpre['user_sol']
                    ]);
                } else {
                    $dataSend['empresa'] = json_encode([
                        'ruc' => $respEmpre['ruc'],
                        'razon_social' => $respEmpre['razon_social'],
                        'direccion' => $respEmpre['direccion'],
                        'ubigeo' => $respEmpre['ubigeo'],
                        'distrito' => $respEmpre['distrito'],
                        'provincia' => $respEmpre['provincia'],
                        'departamento' => $respEmpre['departamento'],
                        'clave_sol' => $respEmpre['clave_sol'],
                        'usuario_sol' => $respEmpre['user_sol']
                    ]);
                }

                /*  if() */


                $dataSend['productos'] = json_encode($dataSend['productos']);

                file_put_contents("aaaaaaaaaaas.json", json_encode($dataSend));
                if ($c_venta->getIdTido() == 1) {
                    $dataResp = $this->sunatApi->genBoletaXML($dataSend);
                } else {
                    $dataResp = $this->sunatApi->genFacturaXML($dataSend);
                }



                if ($dataResp["res"]) {
                    $c_sunat->setIdVenta($c_venta->getIdVenta());
                    $c_sunat->setHash($dataResp['data']['hash']);
                    $c_sunat->setNombreXml($dataResp['data']['nombre_archivo']);
                    $c_sunat->setQrData($dataResp['data']['qr']);
                    $c_sunat->insertar();

                    $nom_xmlFac = $dataResp['data']['nombre_archivo'];
                } else {
                }
            } else {
                $c_sunat->setIdVenta($c_venta->getIdVenta());
                $c_sunat->setHash("-");
                $c_sunat->setNombreXml("-");
                $c_sunat->setQrData('-');
                $c_sunat->insertar();

                $resultado["valor"] = $c_venta->getIdVenta();
            }
            $resultado["nomxml"] = $nom_xmlFac;
            $resultado["venta"] = $c_venta->getIdVenta();
            $resultado["nomFact"] = $c_sunat->getNombreXml() . ".pdf";
            $resultado["urlFact"] = URL::to('/venta/comprobante/pdf/' . $c_sunat->getIdVenta() . '/' . $c_sunat->getNombreXml());
            $resultado["urlFactd"] = URL::to('/venta/comprobante/pdfd/' . $c_sunat->getIdVenta() . '/' . $c_sunat->getNombreXml());
        }
        return json_encode($resultado);
    }
}
