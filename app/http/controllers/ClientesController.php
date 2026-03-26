<?php

use Mpdf\Utils\Arrays;

require_once "app/models/Cliente.php";
require_once "utils/lib/exel/vendor/autoload.php";
require_once 'utils/lib/mpdf/vendor/autoload.php';


class ClientesController extends Controller
{

    private $cliente;
    private $conectar;

    public function __construct()
    {
        $this->cliente = new Cliente();
        $this->conectar = (new Conexion())->getConexion();
    }

    public function getUsuarios()
    {
        $sql = "select * from usuarios where id_empresa='{$_SESSION['id_empresa']}'";
        $usuarios = $this->conectar->query($sql)->fetch_all(MYSQLI_ASSOC);
        return json_encode($usuarios);
    }
    //agregando 10/04/2025

    //     public function buscarCobranzas()
    // {
    //     $filtros = [];
    //     $arrQueryClientes = [];

    //     $id_usuario   = isset($_POST['id_usuario']) && $_POST['id_usuario'] !== '' ? $_POST['id_usuario'] : null;
    //     $fecha_inicio = isset($_POST['fecha_inicio']) && $_POST['fecha_inicio'] !== '' ? $_POST['fecha_inicio'] : null;
    //     $fecha_fin    = isset($_POST['fecha_fin']) && $_POST['fecha_fin'] !== '' ? $_POST['fecha_fin'] : null;
    //     $camion       = isset($_POST['camion']) && $_POST['camion'] !== '' ? $_POST['camion'] : null;
    //     $diasVisita   = isset($_POST['diasVisita']) && $_POST['diasVisita'] !== '' ? $_POST['diasVisita'] : null;
    //     $ruta      = isset($_POST['ruta']) && $_POST['ruta'] !== '' ? $_POST['ruta'] : null;

    //     // Condicional para fechas independientes
    //     $whereFechaCoti = '';
    //     if ($fecha_inicio && $fecha_fin) {
    //         $whereFechaCoti = "AND co.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
    //     } elseif ($fecha_inicio) {
    //         $whereFechaCoti = "AND co.fecha >= '$fecha_inicio'";
    //     } elseif ($fecha_fin) {
    //         $whereFechaCoti = "AND co.fecha <= '$fecha_fin'";
    //     }

    //     // Condicional para usuario
    //     $whereUsuarioCoti = '';
    //     if ($id_usuario) {
    //         $whereUsuarioCoti = "AND co.id_usuario = '$id_usuario'";
    //     }

    //     // Filtro por mercado
    //     $whereRuta = '';
    //     if (!empty($ruta)) {
    //         $whereRuta = "AND c.id_ruta = '$ruta'";
    //     }

    //     // Filtros por cami��n
    //     switch ($camion) {
    //         case '1':
    //             $filtros = [
    //                 'lunes'     => ['1', '7'],
    //                 'martes'    => ['5', '7'],
    //                 'miercoles' => ['5'],
    //                 'jueves'    => ['1', '7'],
    //                 'viernes'   => ['6', '7'],
    //                 'sabado'    => ['7', '8'],
    //             ];
    //             break;
    //         case '2':
    //             $filtros = [
    //                 'lunes'     => ['3', '6'],
    //                 'martes'    => ['1', '3'],
    //                 'miercoles' => ['1', '3'],
    //                 'jueves'    => ['6', '3'],
    //                 'viernes'   => ['3', '5'],
    //                 'sabado'    => ['3', '6'],
    //             ];
    //             break;
    //         case '3':
    //             $filtros = [
    //                 'miercoles' => ['6', '7'],
    //                 'viernes'   => ['8', '2'],
    //                 'sabado'    => ['1', '5'],
    //             ];
    //             break;
    //         default:
    //             break;
    //     }

    //     // Si se seleccion�� un d��a espec��fico
    //     if ($diasVisita != "" && isset($filtros[$diasVisita])) {
    //         $filtros = [
    //             $diasVisita => $filtros[$diasVisita]
    //         ];
    //     }
    //     if ($ruta != "") {
    //         foreach ($filtros as $key => $filtro) {
    //             $filtros[$key] = [$ruta];
    //         }
    //     }
    //     foreach ($filtros as $key => $filtro) {
    //         $arrQueryClientes[] = "( LOWER(c.dias_visitas) = LOWER('{$key}') AND c.id_ruta IN (" . implode(',', $filtro) . ") )";
    //     }

    //     $whereClientes = '';
    //     if (!empty($arrQueryClientes)) {
    //         $whereClientes = "AND (" . implode(' OR ', $arrQueryClientes) . ")";
    //     }
    //     $whereDiasVisita = '';
    //     if (!empty($diasVisita)) {
    //         $whereDiasVisita = "AND LOWER(c.dias_visitas) = LOWER('$diasVisita')";
    //     }

    //     try {
    //         // SEGUNDA CONSULTA (cotizaciones)
    //         $sql = "SELECT tb.*, tb.total - tb.pagado AS saldo FROM (
    //             SELECT 
    //                 'c' AS tipo_co,
    //                 co.cotizacion_id AS id_venta,
    //                 CONCAT('#', co.numero) AS factura,
    //                 us.usuario AS vendedor,
    //                 co.fecha AS fecha_emision,
    //                 (SELECT fecha FROM cuotas_cotizacion cc WHERE cc.id_coti = co.cotizacion_id ORDER BY fecha DESC LIMIT 1) AS fecha_vencimiento,
    //                 CONCAT(c.documento, ' | ', c.datos) AS cliente,
    //                 co.total,
    //                 (SELECT IFNULL(SUM(cc.monto), 0) FROM cuotas_cotizacion cc WHERE cc.id_coti = co.cotizacion_id AND cc.estado = 1) AS pagado
    //             FROM cotizaciones co
    //             INNER JOIN clientes AS c ON c.id_cliente = co.id_cliente
    //             JOIN usuarios us ON us.usuario_id = co.id_usuario
    //             WHERE co.id_tipo_pago = 2 
    //                 $whereFechaCoti
    //                 $whereUsuarioCoti
    //                 $whereClientes
    //                 $whereRuta
    //                 $whereDiasVisita
    //         ) tb";

    //         $fila = mysqli_query($this->conectar, $sql);
    //         $lista2 = [];
    //         if ($fila) {
    //             $lista2 = mysqli_fetch_all($fila, MYSQLI_ASSOC);
    //         } else {
    //             echo "Error en la segunda consulta: " . mysqli_error($this->conectar);
    //         }

    //         return array_merge($lista2, array());
    //     } catch (Exception $e) {
    //         echo $e->getMessage();
    //     }
    // }
    public function buscarCobranzas()
    {
        $filtros = [];
        $arrQueryClientes = [];
    
        $id_usuario   = isset($_POST['id_usuario']) && $_POST['id_usuario'] !== '' ? $_POST['id_usuario'] : null;
        $fecha_inicio = isset($_POST['fecha_inicio']) && $_POST['fecha_inicio'] !== '' ? $_POST['fecha_inicio'] : null;
        $fecha_fin    = isset($_POST['fecha_fin']) && $_POST['fecha_fin'] !== '' ? $_POST['fecha_fin'] : null;
        $camion       = isset($_POST['camion']) && $_POST['camion'] !== '' ? $_POST['camion'] : null;
        $diasVisita   = isset($_POST['diasVisita']) && $_POST['diasVisita'] !== '' ? $_POST['diasVisita'] : null;
        $ruta      = isset($_POST['ruta']) && $_POST['ruta'] !== '' ? $_POST['ruta'] : null;
    
        // Condicional para fechas independientes
        $whereFechaCoti = '';
        if ($fecha_inicio && $fecha_fin) {
            $whereFechaCoti = "AND co.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        } elseif ($fecha_inicio) {
            $whereFechaCoti = "AND co.fecha >= '$fecha_inicio'";
        } elseif ($fecha_fin) {
            $whereFechaCoti = "AND co.fecha <= '$fecha_fin'";
        }
    
        // Condicional para usuario
        $whereUsuarioCoti = '';
        if ($id_usuario) {
            $whereUsuarioCoti = "AND co.id_usuario = '$id_usuario'";
        }
    
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
    
        try {
            // Determinar ordenamiento dinámico basado en filtros de fecha
            $hayFiltroFecha = !empty($fecha_inicio) || !empty($fecha_fin);
            
            if ($hayFiltroFecha) {
                // Cuando hay filtro de fecha: FECHA primero
                $orderBy = "ORDER BY 
                    tb.fecha_emision DESC,
                    CAST(CASE WHEN tb.mercado IS NULL OR tb.mercado = '' THEN 999 ELSE tb.mercado END AS UNSIGNED) ASC,
                    SUBSTRING_INDEX(tb.cliente, ' | ', -1) ASC";
            } else {
                // Cuando NO hay filtro de fecha: MERCADO primero
                $orderBy = "ORDER BY 
                    CAST(CASE WHEN tb.mercado IS NULL OR tb.mercado = '' THEN 999 ELSE tb.mercado END AS UNSIGNED) ASC,
                    SUBSTRING_INDEX(tb.cliente, ' | ', -1) ASC, 
                    tb.fecha_emision DESC";
            }

            // CONSULTA con ORDER BY dinámico
            $sql = "SELECT tb.*, tb.total - tb.pagado AS saldo FROM (
                SELECT 
                    'c' AS tipo_co,
                    co.cotizacion_id AS id_venta,
                    CONCAT('#', co.numero) AS factura,
                    us.usuario AS vendedor,
                    co.fecha AS fecha_emision,
                    (SELECT fecha FROM cuotas_cotizacion cc WHERE cc.id_coti = co.cotizacion_id ORDER BY fecha DESC LIMIT 1) AS fecha_vencimiento,
                    CONCAT(c.documento, ' | ', c.datos) AS cliente,
                    co.total,
                    c.mercado AS mercado,
                    (SELECT IFNULL(SUM(cc.monto), 0) FROM cuotas_cotizacion cc WHERE cc.id_coti = co.cotizacion_id AND cc.estado = 1) AS pagado
                FROM cotizaciones co
                INNER JOIN clientes AS c ON c.id_cliente = co.id_cliente
                JOIN usuarios us ON us.usuario_id = co.id_usuario
                WHERE co.id_tipo_pago = 2 AND co.estado!=2 
                    $whereFechaCoti
                    $whereUsuarioCoti
                    $whereClientes
                    $whereRuta
                    $whereDiasVisita
            ) tb 
            $orderBy";
    
            $fila = mysqli_query($this->conectar, $sql);
            $lista2 = [];
            if ($fila) {
                $lista2 = mysqli_fetch_all($fila, MYSQLI_ASSOC);
            } else {
                echo "Error en la consulta: " . mysqli_error($this->conectar);
            }
    
            return array_merge($lista2, array());
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    public function pdf()
    {
        $filtros = [];
        $arrQueryClientes = [];
        // Leer los datos JSON del cuerpo de la solicitud
        $data = json_decode(file_get_contents("php://input"), true);
        // Asignar los valores de los datos recibidos a variables
        $id_usuario   = isset($data['id_usuario']) && $data['id_usuario'] !== '' ? $data['id_usuario'] : null;
        $fecha_inicio = isset($data['fecha_inicio']) && $data['fecha_inicio'] !== '' ? $data['fecha_inicio'] : null;
        $fecha_fin    = isset($data['fecha_fin']) && $data['fecha_fin'] !== '' ? $data['fecha_fin'] : null;
        $camion       = isset($data['camion']) && $data['camion'] !== '' ? $data['camion'] : null;
        $diasVisita   = isset($data['diasVisita']) && $data['diasVisita'] !== '' ? $data['diasVisita'] : null;
        $ruta      = isset($data['ruta']) && $data['ruta'] !== '' ? $data['ruta'] : null;

        // Condicional para fechas independientes
        $whereFechaCoti = '';
        if ($fecha_inicio && $fecha_fin) {
            $whereFechaCoti = "AND co.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        } elseif ($fecha_inicio) {
            $whereFechaCoti = "AND co.fecha >= '$fecha_inicio'";
        } elseif ($fecha_fin) {
            $whereFechaCoti = "AND co.fecha <= '$fecha_fin'";
        }

        // Condicional para usuario
        $whereUsuarioCoti = '';
        if ($id_usuario) {
            $whereUsuarioCoti = "AND co.id_usuario = '$id_usuario'";
        }

        // Filtro por mercado
        $whereRuta = '';
        if (!empty($ruta)) {
            $whereRuta = "AND c.id_ruta = '$ruta'";
        }


        // Filtros por cami��n
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

        // Si se seleccion�� un d��a espec��fico
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
            $arrQueryClientes[] = "( c.dias_visitas = '{$key}' AND c.id_ruta IN (" . implode(',', $filtro) . ") )";
        }

        $whereClientes = '';
        if (!empty($arrQueryClientes)) {
            $whereClientes = "AND (" . implode(' OR ', $arrQueryClientes) . ")";
        }

        try {
            // PRIMERA CONSULTA (ventas)
            $sqlVentas = "SELECT 
                'v' as tipo_co, 
                v.id_venta, 
                CONCAT(v.serie, ' | ', v.numero) AS factura, 
                v.fecha_emision, 
                v.fecha_vencimiento,
                CONCAT(c.documento, ' | ', c.datos) AS cliente, 
                v.total, 
                u.usuario AS vendedor,
                SUM(CASE WHEN dv.estado = '1' THEN dv.monto ELSE 0 END) AS pagado,
                (v.total - SUM(CASE WHEN dv.estado = '1' THEN dv.monto ELSE 0 END)) AS saldo
            FROM ventas AS v
            INNER JOIN dias_ventas AS dv ON v.id_venta = dv.id_venta 
            INNER JOIN clientes AS c ON v.id_cliente = c.id_cliente
            LEFT JOIN usuarios u ON u.usuario_id = v.id_vendedor
            WHERE v.estado = 1 
                AND v.id_empresa = '{$_SESSION['id_empresa']}' 
                " . ($_SESSION["rol"] != 4 ? "AND v.sucursal = '{$_SESSION['sucursal']}'" : "") . "
                AND v.id_vendedor = '$id_usuario'
                AND v.fecha_emision BETWEEN '$fecha_inicio' AND '$fecha_fin'
                $whereRuta
            GROUP BY v.id_venta";

            $fila = mysqli_query($this->conectar, $sqlVentas);
            $lista = $fila ? mysqli_fetch_all($fila, MYSQLI_ASSOC) : [];

            // SEGUNDA CONSULTA (cotizaciones)
            $sql = "SELECT tb.*, tb.total - tb.pagado AS saldo FROM (
                SELECT 
                    'c' AS tipo_co,
                    co.cotizacion_id AS id_venta,
                    CONCAT('#', co.numero) AS factura,
                    us.usuario AS vendedor,
                    co.fecha AS fecha_emision,
                    (SELECT fecha FROM cuotas_cotizacion cc WHERE cc.id_coti = co.cotizacion_id ORDER BY fecha DESC LIMIT 1) AS fecha_vencimiento,
                    CONCAT(c.documento, ' | ', c.datos) AS cliente,
                    co.total,
                    (SELECT IFNULL(SUM(cc.monto), 0) FROM cuotas_cotizacion cc WHERE cc.id_coti = co.cotizacion_id AND cc.estado = 1) AS pagado
                FROM cotizaciones co
                INNER JOIN clientes AS c ON c.id_cliente = co.id_cliente
                JOIN usuarios us ON us.usuario_id = co.id_usuario
                WHERE co.id_tipo_pago = 2 AND co.estado!=2 
                    $whereFechaCoti
                    $whereUsuarioCoti
                    $whereClientes
                    $whereRuta
            ) tb";

            $fila = mysqli_query($this->conectar, $sql);
            $lista2 = [];
            if ($fila) {
                $lista2 = mysqli_fetch_all($fila, MYSQLI_ASSOC);
            } else {
                echo "Error en la segunda consulta: " . mysqli_error($this->conectar);
            }

            $datos = array_merge($lista2, $lista);

            // Calcular resumen por vendedor
            $resumen = [];

            foreach ($datos as $venta) {
                $vendedor = $venta['vendedor'] !== '' ? $venta['vendedor'] : 'Sin asignar';

                if (!isset($resumen[$vendedor])) {
                    $resumen[$vendedor] = [
                        'total' => 0,
                        'pagado' => 0,
                        'saldo' => 0
                    ];
                }

                $resumen[$vendedor]['total'] += floatval($venta['total']);
                $resumen[$vendedor]['pagado'] += floatval($venta['pagado']);
                $resumen[$vendedor]['saldo'] += floatval($venta['saldo']);
            }

            $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);

            // Detalle por cliente
            // Agrupar los datos por vendedor
            $ventasPorVendedor = [];
            foreach ($datos as $row) {
                $vendedor = $row['vendedor'] !== '' ? $row['vendedor'] : 'Sin asignar';
                $ventasPorVendedor[$vendedor][] = $row;
            }

            $html = '
     <style>
         body {
             font-size: 10px;
             font-family: sans-serif;
         }
         table {
             width: 100%;
             border-collapse: collapse;
             margin-bottom: 15px;
         }
         th, td {
             border: 1px solid #ccc;
             padding: 4px;
             text-align: left;
         }
         h1 {
             font-size: 14px;
         }
         h2 {
             font-size: 12px;
         }
     </style>
     
     <h1>Reporte de Cobranzas</h1>
     ';

            foreach ($ventasPorVendedor as $vendedor => $ventas) {
                $html .= "<h2>Vendedor: $vendedor</h2>";

                $html .= '
         <table>
             <thead>
                 <tr>
                     <th>Factura</th>
                     <th>Cliente</th>
                     <th>F. Emisi��n</th>
                     <th>F. Vencimiento</th>
                     <th>Total (S/.)</th>
                     <th>Pagado (S/.)</th>
                     <th>Saldo (S/.)</th>
                 </tr>
             </thead>
             <tbody>
         ';

                foreach ($ventas as $venta) {
                    $html .= '<tr>';
                    $html .= "<td>{$venta['factura']}</td>";
                    $html .= "<td>{$venta['cliente']}</td>";
                    $html .= "<td>{$venta['fecha_emision']}</td>";
                    $html .= "<td>{$venta['fecha_vencimiento']}</td>";
                    $html .= "<td>" . number_format($venta['total'], 2) . "</td>";
                    $html .= "<td>" . number_format($venta['pagado'], 2) . "</td>";
                    $html .= "<td>" . number_format($venta['saldo'], 2) . "</td>";
                    $html .= '</tr>';
                }

                $html .= '</tbody></table>';

                // Resumen por vendedor alineado a la derecha, con "Resumen de..." ocupando 3 columnas
                $suma = $resumen[$vendedor];
                $html .= '
        <table style="width: 100%; margin-top: 10px; font-size: 10px;">
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Resumen de ' . $vendedor . ':</strong></td>
                <td style="text-align: right;">Total: S/. ' . number_format($suma['total'], 2) . '</td>
                <td style="text-align: right;">Pagado: S/. ' . number_format($suma['pagado'], 2) . '</td>
                <td style="text-align: right;">Saldo: S/. ' . number_format($suma['saldo'], 2) . '</td>
            </tr>
        </table><hr>';
            }


            $mpdf->WriteHTML($html);
            $mpdf->Output('reporte_cobranzas.pdf', 'I'); // Mostrar en navegador

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function insertarXLista()
    {
        /*   $lista = json_decode($_POST['lista'], true);
        echo json_encode($lista);
        die(); */
        $lista = json_decode($_POST['lista'], true);
        //var_dump($lista);
        $respuesta = ["res" => false];

        foreach ($lista as $item) {
            $datos = $item['datos'];
            $direccion = $item['direccion'];
            $distrito = $item['distrito'];
            $sql = "INSERT into clientes set datos=?,
            documento='{$item['documento']}',
            direccion=?,
            distrito=?,
            id_empresa='{$_SESSION['id_empresa']}',
            telefono='{$item['telefono']}',
            dias_visitas='{$item['visita']}',
            id_ruta='{$item['ruta']}',
            mercado='{$item['mercado']}';";

            $stmt = $this->conectar->prepare($sql);
            $stmt->bind_param('sss', $datos, $direccion, $distrito);
            if ($stmt->execute()) {
                $respuesta["res"] = true;
            }
        }
        return json_encode($respuesta);
    }
    public function insertar()
    {
        if (!empty($_POST)) {
            $doc = trim(filter_var($_POST['documentoAgregar'], FILTER_SANITIZE_NUMBER_INT));
            $datosAgregar = trim(filter_var($_POST['datosAgregar'], FILTER_SANITIZE_STRING));
            $direccionAgregar = trim(filter_var($_POST['direccionAgregar'], FILTER_SANITIZE_STRING));
            $distrito = trim(filter_var($_POST['distrito'], FILTER_SANITIZE_STRING));
            $telefonoAgregar = trim(filter_var($_POST['telefonoAgregar'], FILTER_SANITIZE_NUMBER_INT));
            $visita = trim(filter_var($_POST['visita'], FILTER_SANITIZE_STRING));
            $telefonoIntVal = intval($telefonoAgregar);
            $docIntVal = intval($doc);
            $id_ruta = trim($_POST['ruta']);
            $mercado = trim($_POST['mercado']);
            if ($doc !== "" && $datosAgregar !== "") {
                $telefonoTrueInt = filter_var($telefonoIntVal, FILTER_VALIDATE_INT);
                $doctTrueInt = filter_var($docIntVal, FILTER_VALIDATE_INT);
                if ($doctTrueInt == true) {
                    $this->cliente->setDocumento($doc);
                    $this->cliente->setDatos($datosAgregar);
                    $this->cliente->setDireccion($direccionAgregar);
                    $this->cliente->setDistrito($distrito);
                    $this->cliente->setTelefono($telefonoAgregar);
                    $this->cliente->setDiasVisitas($visita);
                    $this->cliente->setEmail('');
                    $this->cliente->setIdRuta($id_ruta);
                    $this->cliente->setMercado($mercado);
                    $save = $this->cliente->insertar();
                    if ($save == true) {
                        echo json_encode($this->cliente->idLast());
                    } else {
                        echo json_encode("Ocurrio un Error");
                    }
                } else {
                    echo json_encode('Llene el formulario correctamente 39');
                }
            } else {
                echo json_encode('Llene el formulario correctamente 42');
            }
        } else {
            echo json_encode('Error');
        }
    }
    public function render()
    {
        $getAll = $this->cliente->getAllData();
        echo json_encode($getAll);
    }
    public function getOne()
    {
        /* $presupuesto = new PresupuestosModel(); */
        $data = $_POST;
        $id = $data['id'];
        $getOne = $this->cliente->getOne($id);
        echo json_encode($getOne);
    }
    public function cuentasCobrar()
    {
        /* $presupuesto = new PresupuestosModel(); */

        $getAll = $this->cliente->cuentasCobrar();
        echo json_encode($getAll);
    }
    public function cuentasCobrarEstado()
    {
        $getAll = $this->cliente->cuentasCobrarEstado($_POST['id']);
        echo json_encode($getAll);
    }
    public function editar()
    {
        if (!empty($_POST)) {
            $doc = trim(filter_var($_POST['documentoEditar'], FILTER_SANITIZE_STRING));
            $datosEditar = trim(filter_var($_POST['datosEditar'], FILTER_SANITIZE_STRING));
            $direccionEditar = trim(filter_var($_POST['direccionEditar'], FILTER_SANITIZE_STRING));
            $distrito = trim(filter_var($_POST['distritoEditar'], FILTER_SANITIZE_STRING));
            $telefonoEditar = trim(filter_var($_POST['telefonoEditar'], FILTER_SANITIZE_STRING));
            $visita = trim(filter_var($_POST['visitasEditar'], FILTER_SANITIZE_STRING));
            $emailEditar = '';
            $telefonoIntVal = intval($telefonoEditar);
            $docIntVal = intval($doc);
            $id_ruta = trim($_POST['rutaEditar']);
            $mercado = trim($_POST['mercadoEditar']);
            $id = $_POST['idCliente'];
            if ($doc !== "" && $datosEditar !== "") {

                $telefonoTrueInt = filter_var($telefonoIntVal, FILTER_VALIDATE_INT);
                $doctTrueInt = filter_var($docIntVal, FILTER_VALIDATE_INT);

                if (ctype_digit($doc) && (strlen($doc) == 8 || strlen($doc) == 11)) {
                    $this->cliente->setDocumento($doc);
                    $this->cliente->setDatos($datosEditar);
                    $this->cliente->setDireccion($direccionEditar);
                    $this->cliente->setDistrito($distrito);
                    $this->cliente->setTelefono($telefonoEditar);
                    $this->cliente->setDiasVisitas($visita);
                    $this->cliente->setEmail('');
                    $this->cliente->setIdRuta($id_ruta);
                    $this->cliente->setMercado($mercado);
                    $save = $this->cliente->editar($_POST['idCliente']);
                    if ($save == true) {
                        echo json_encode($this->cliente->getOne($id));
                    } else {
                        echo json_encode("Ocurrio un Error");
                    }
                } else {
                    echo json_encode('Vacio el documento y datos');
                }
            } else {
                echo json_encode('Llene el formulario correctamente');
            }
        } else {
            echo json_encode('Error');
        }
    }
    public function borrar()
    {
        $dataId = $_POST["value"];
        $save = $this->cliente->delete($dataId);
        if ($save) {
            echo json_encode("nice");
        } else {
            echo json_encode("error");
        }
    }

    public function importarExcel()
    {
        $respuesta = ["res" => false];
        $filename = $_FILES['file']['name'];

        $path_parts = pathinfo($filename, PATHINFO_EXTENSION);
        $newName = Tools::getToken(80);
        /* Location */
        $loc_ruta = "files/temp";
        if (!file_exists($loc_ruta)) {
            mkdir($loc_ruta, 0777, true);
        }
        $location = $loc_ruta . "/" . $newName . '.' . $path_parts;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
            $nombre_logo = $newName . "." . $path_parts;

            $respuesta["res"] = true;
            $type = $path_parts;

            if ($type == "xlsx") {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            } elseif ($type == "xls") {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } elseif ($type == "csv") {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            }

            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load("files/temp/" . $nombre_logo);

            $schdeules = $spreadsheet->getActiveSheet()->toArray();
            // array_shift($schdeules);
            $respuesta["data"] = $schdeules;

            unlink($location);
            //return $schdeules;
            /*   $last = $this->cliente->idLast();
            $arr = array($respuesta, $last); */
        }

        return json_encode($respuesta);
    }
    /*   public function importAdd(){
        echo json_encode($_POST);
    } */

    public function exportarExcel()
    {
        $data = $this->cliente->getAllData();

        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'Reporte de Clientes');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);
        $sheet->getStyle("A1")->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $headers = [
            '#',
            'Documento',
            'Nombre',
            'Direccion',
            'Distrito',
            'Telefono',
            'Dias Visita',
            'Rutas',
            'Mercado'
        ];

        $row = 2;
        foreach ($headers as $key => $header) {
            $sheet->setCellValueByColumnAndRow($key + 1, 2, $header);
        }
        $sheet->getStyle("A$row:I$row")->getFill()->setFillType(PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF28719B');
        $sheet->getStyle("A$row:I$row")->getFont()->getColor()->setARGB(PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        $sheet->getStyle("A$row:I$row")->getFont()->setBold(true);
        $sheet->getStyle("A$row:I$row")->getFont()->setBold(true)->setSize(12);

        $row++;

        foreach ($data as $index => $rowData) {
            $sheet->setCellValue('A' . $row, $index + 1); // Columna # (Índice)
            $sheet->setCellValue('B' . $row, $rowData['documento']);
            $sheet->setCellValue('C' . $row, $rowData['datos']);
            $sheet->setCellValue('D' . $row, $rowData['direccion']);
            $sheet->setCellValue('E' . $row, $rowData['distrito']);
            $sheet->setCellValue('F' . $row, $rowData['telefono']);
            $sheet->setCellValue('G' . $row, $rowData['dias_visitas']);
            $sheet->setCellValue('H' . $row, $rowData['id_ruta']);
            $sheet->setCellValue('I' . $row, $rowData['mercado']);
            $row++;
        }

        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Nombre del archivo
        $fileName = 'reporte_clientes.xlsx';

        // Enviar los encabezados para la descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        // Guardar el archivo y enviarlo al navegador
        $writer->save('php://output');
        exit();
    }

    public function exportarClientesVisitaPdf()
    {
        $id_ruta = $_GET['id_ruta'] ?? "";
        $dia_visita = $_GET['dia_visita'] ?? "";
        if ($id_ruta == "" || $dia_visita == "") {
            die("No se ingresaron todos los campos requiridos");
        }

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $mpdf = new \Mpdf\Mpdf([
            'memory_limit' => '512M',
            'format' => 'Letter',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        $sql = "SELECT c.* 
            FROM clientes c    
            WHERE c.id_empresa='{$_SESSION['id_empresa']}' AND c.id_ruta='$id_ruta'
            AND c.dias_visitas = '$dia_visita'
            ORDER BY c.datos ASC ";

        // $vendedor = $this->conectar->query("SELECT usuario_id,usuario,nombres FROM usuarios WHERE usuario_id='$id_ruta' ")->fetch_assoc();


        $resultado = $this->conectar->query($sql);
        // Manejo de errores
        if (!$resultado) {
            die("Error en la consulta: " . $this->conectar->error);
        }

        // $cotizacion = $resultado->fetch_assoc();
        $clientes = array();
        while ($row = $resultado->fetch_assoc()) {
            $clientes[] = $row;
        }

        // Verificar si se encontró una cotización
        if (sizeof($clientes) <= 0) {
            die("No se encontraron clientes para ruta: " . $id_ruta);
        }

        $contador = 1;
        $total_consolidado = 0;

        $rowHTML = '';

        foreach ($clientes as $cliente) {
            // $cnt4 = Tools::numeroParaDocumento($prod['cantidad'], 3);


            $rowHTML .= "
                <tr>
                    <td class='' style='font-weight: bold;font-family: Arial, sans-serif; font-size: 11px; text-align: center; border-bottom: 1px solid #000;white-space: nowrap;'>$contador</td>
                    <td class='' style='font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; text-align: left;border-bottom: 1px solid #000; white-space: nowrap; '>{$cliente['datos']}</td>                    
                    <td class='' style='font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; text-align: center;border-bottom: 1px solid #000; '>{$cliente['documento']}</td>                    
                    <td class='' style='font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; text-align: left;border-bottom: 1px solid #000; white-space: nowrap;'>{$cliente['direccion']}  </td>                    
                    <td class='' style='text-align:center;font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-bottom: 1px solid #000;'>{$cliente['telefono']} </td>
                    <td class='' style='text-align:left;font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-bottom: 1px solid #000;white-space: nowrap;'>{$cliente['distrito']} </td>
                    <td class='' style='text-align:center;font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; border-bottom: 1px solid #000;white-space: nowrap;'>{$cliente['mercado']} </td>
                </tr>
            ";
            $contador++;
        }

        $html = "
        <div style='width: 100%; padding-top: 0px; overflow: hidden;clear: both;'>
            <h1 style='text-align:center;'>Clientes para ruta {$id_ruta}</h1>
        </div>
        <div style='width: 100%; padding-top: 20px; margin-left: 20px'>
            <table style='width:567px; border-bottom: 1px solid #fff;border-collapse: collapse;'>
                <tr style='border-bottom: 1px solid #fff;border-collapse: collapse;'>
                    <td style=' font-size: 16px;text-align: center; color: #f00;border: 1px solid #fff; padding:0;'><strong>#</strong></td>
                    <td style=' font-size: 16px;text-align: center; color: #f00;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>Nombre</strong></td>
                    <td style=' font-size: 16px; color: #f00;border: 1px solid #fff;border-collapse: collapse; padding: 0;'><strong>Documento</strong></td>
                    <td style=' font-size: 16px;text-align: center; color: #f00;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>Direccion</strong></td> 
                    <td style=' font-size: 16px;text-align: center; color: #f00;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>Telefono</strong></td> 
                    <td style=' font-size: 16px;text-align: center; color: #f00;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>Distrito</strong></td>
                    <td style=' font-size: 16px;text-align: center; color: #f00;border: 1px solid #fff;border-collapse: collapse;  padding:0;'><strong>Mercado</strong></td>
                </tr>
                $rowHTML
                <tr>
                    <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff;color: white; padding:0;'>.</td>
                    <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;border-bottom: 1px solid #fff;  padding:0;'> </td>
                    <td class='' style=' font-size: 11px; border-left: 1px solid #fff;border-bottom: 1px solid #fff; padding:0;'> </td>
                    <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;'> </td>
                    <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;'> </td>
                    <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;'> </td>
                    <td class='' style=' font-size: 11px; text-align: center;border-left: 1px solid #fff;'> </td>
                </tr>
            </table>
        </div>
        <div>
        </div>";
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

        $mpdf->Output("clientes_día_{$dia_visita}.pdf", 'I');
    }
}
