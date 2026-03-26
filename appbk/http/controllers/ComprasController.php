<?php

require_once "app/models/Compra.php";
require_once "app/models/DocumentoEmpresa.php";

class ComprasController extends Controller
{
    private $conectar;

    public function __construct()
    {
        $this->conectar=(new Conexion())->getConexion();
    }

    public function guardarCompras()
    {
        /* $this->sunatApi = new SunatApi(); */
        $insert = false;

        $c_compra = new Compra();
        $c_tido = new DocumentoEmpresa();


        $idProveedor = '';
        if ($_POST['num_doc'] !== '') {
            $empresaExistente = $c_tido->consultarProveedor($_POST['num_doc']);
            if (!empty($empresaExistente)) {
                $idProveedor = $empresaExistente[0]['proveedor_id'];
            } else {
                $insert = $c_tido->insertarProveedor($_POST['num_doc'], $_POST['nom_cli']);
                $idProveedor = $insert;
            }
        }

        $id_tido = $_POST['tipo_doc'] !== '' ? $_POST['tipo_doc'] : 2;
        $tipo_pago = $_POST['tipo_pago'] !== '' ? $_POST['tipo_pago'] : '';
        $fecha = isset($_POST['fecha'])  ? $_POST['fecha'] : '';
        $fechaVen = isset($_POST['fechaVen'])  ? $_POST['fechaVen'] : '';
        $dir_cli = $_POST['dir_cli'] !== '' ? $_POST['dir_cli'] : '-';
        $serie = $_POST['serie'] !== '' ? $_POST['serie'] : '';
        $numero = $_POST['numero'] !== '' ? $_POST['numero'] : '';
        $total = $_POST['total'] !== 0 ? intval($_POST['total']) : 0;
        $moneda = $_POST['moneda'] !== '' ? $_POST['moneda'] : '';
        $tipoventa = $_POST['tipoventa'] !== '' ? $_POST['tipoventa'] : '';



        if ($id_tido !== '' && $tipo_pago !== ''  && $fecha !== '' && $fechaVen !== '' && $dir_cli !== '' && $serie !== '' && $numero !== '' && $total > 0 && $moneda !== '' && $idProveedor !== '') {
            $array_detalle = json_decode($_POST['listaPro'], true);
            $listaPagos = json_decode($_POST['dias_lista'], true);
            $insertarCompra =  $c_compra->insertarCompra($id_tido, $tipo_pago, $idProveedor, $fecha, $fechaVen, $dir_cli, $serie, $numero, $total, $_SESSION['id_empresa'], $moneda);

            if (is_int($insertarCompra)) {
                $updateStock = false;
                foreach ($array_detalle as $row) {
                    $updateStock  = $c_compra->updateStock($row['cantidad'], $row['productoid']);
                }
                if ($updateStock) {
                    if ($tipo_pago == 1) {
                        $insertCompra = false;
                        foreach ($array_detalle as $fila) {
                            $insertCompra = $c_compra->insertProductosCompras($fila['productoid'], $insertarCompra, $fila['cantidad'], $fila['precio']);
                        }
                        if ($insertCompra) {
                            echo json_encode(array('resp' => true, 'msj' => 'Registro exitoso 135'));
                        } else {
                            echo json_encode(array('resp' => false, 'msj' => 'Ocurrio un Error 137'));
                        }
                    } elseif ($tipo_pago == 2) {
                        $array_detalle = json_decode($_POST['listaPro'], true);
                        $insertDiasCompra = false;
                        foreach ($listaPagos as $fila) {
                            $insertDiasCompra = $c_compra->insertDiasCompras($insertarCompra, $fila['monto'], $fila['fecha']);
                        }
                        if ($insertDiasCompra) {
                            echo json_encode(array('resp' => true, 'msj' => 'Registro exitoso'));
                        } else {
                            echo json_encode(array('resp' => false, 'msj' => 'Ocurrio un Error'));
                        }
                    }
                } else {
                    echo json_encode(array('resp' => false, 'msj' => 'Ocurrio un Error'));
                }
            } else {
                echo json_encode(array('resp' => false, 'msj' => 'Ocurrio un Error'));
            }
        } else {
            echo json_encode(array('resp' => false, 'msj' => 'Llene todos los campos'));
        }



        /*   */
        /*   echo json_encode(array($_POST['num_doc'])); */
        /*    $id_empresa = $_SESSION['id_empresa'];
        $c_cliente->setIdEmpresa($id_empresa);
        $c_cliente->setDocumento(filter_input(INPUT_POST, 'num_doc'));
        $c_cliente->setDatos(filter_input(INPUT_POST, 'nom_cli'));
        $c_cliente->setDireccion(filter_input(INPUT_POST, 'dir_cli'));
        $c_cliente->setDireccion2(filter_input(INPUT_POST, 'dir2_cli'));

        return json_encode(array($c_cliente->setDocumento(filter_input(INPUT_POST, 'num_doc'))));
        if ($c_cliente->getDocumento() == "") {
            $c_cliente->setDocumento("SD" . $c_varios->generarCodigo(5));
            $c_cliente->insertar();
        } else {
            if (!$c_cliente->verificarDocumento()) {
                $c_cliente->insertar();
            }
        }

        $direccionselk = $_POST['dir_cli'] !== null ? $_POST['dir_cli'] : '-'; */
    }

    public function getAll()
    {
        $sql = "SELECT c.id_compra,c.fecha_emision,c.fecha_vencimiento,c.serie,c.numero,p.razon_social FROM compras AS c LEFT JOIN proveedores AS p ON
        c.id_proveedor=p.proveedor_id where c.id_empresa='{$_SESSION['id_empresa']}' and c.sucursal='{$_SESSION['sucursal']}'";
        return $this->conectar->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getDetalle()
    {
        $sql = "SELECT pc.id_producto_venta,p.descripcion,pc.cantidad,pc.precio FROM productos_compras AS pc LEFT JOIN productos AS p ON
        pc.id_producto=p.id_producto LEFT JOIN compras AS c ON
        pc.id_compra=c.id_compra WHERE c.id_compra = '{$_POST['id']}'";
        return json_encode($this->conectar->query($sql)->fetch_all(MYSQLI_ASSOC));

    }

    public function buscarProducto()
    {
        $dataProducto = $_POST['producto'];
        if ($dataProducto !== '') {
            $sql = "SELECT * from productos 
        where descripcion LIKE '%$dataProducto%' ";

            $getAll = $this->conectar->query($sql)->fetch_all(MYSQLI_ASSOC);
            if (!empty($getAll)) {
                $res = array("res" => true, "data" => $getAll);
                echo json_encode($res);
            } else {
                $res = array("res" => false, "msj" => 'No se encontró ningun producto');
                echo json_encode($res);
            }
        }
    }



}