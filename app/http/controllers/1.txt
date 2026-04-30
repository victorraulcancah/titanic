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

    public function buscarCobranzas()
    {
        $filtros = [];
        $arrQueryClientes = [];

        $id_usuario = isset($_POST['id_usuario']) && $_POST['id_usuario'] !== '' ? $_POST['id_usuario'] : null;
        $fecha_inicio = isset($_POST['fecha_inicio']) && $_POST['fecha_inicio'] !== '' ? $_POST['fecha_inicio'] : null;
        $fecha_fin = isset($_POST['fecha_fin']) && $_POST['fecha_fin'] !== '' ? $_POST['fecha_fin'] : null;
        $camion = isset($_POST['camion']) && $_POST['camion'] !== '' ? $_POST['camion'] : null;
        $diasVisita = isset($_POST['diasVisita']) && $_POST['diasVisita'] !== '' ? $_POST['diasVisita'] : null;
        $ruta = isset($_POST['ruta']) && $_POST['ruta'] !== '' ? $_POST['ruta'] : null;

        // Condicional para fechas
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

        // Filtro por ruta
        $whereRuta = '';
        if (!empty($ruta)) {
            $whereRuta = "AND c.id_ruta = '$ruta'";
        }

        // Filtros por camión
        switch ($camion) {
            case '1':
                $filtros = ['lunes' => ['1', '7'], 'martes' => ['5', '7'], 'miercoles' => ['5'], 'jueves' => ['1', '7'], 'viernes' => ['6', '7'], 'sabado' => ['7', '8']];
                break;
            case '2':
                $filtros = ['lunes' => ['3', '6'], 'martes' => ['1', '3'], 'miercoles' => ['1', '3'], 'jueves' => ['6', '3'], 'viernes' => ['3', '5'], 'sabado' => ['3', '6']];
                break;
            case '3':
                $filtros = ['miercoles' => ['6', '7'], 'viernes' => ['8', '2'], 'sabado' => ['1', '5']];
                break;
        }

        if ($diasVisita != "" && isset($filtros[$diasVisita])) {
            $filtros = [$diasVisita => $filtros[$diasVisita]];
        }
        if ($ruta != "") {
            foreach ($filtros as $key => $filtro) {
                $filtros[$key] = [$ruta];
            }
        }
        // Función auxiliar para normalizar acentos (insensible a acentos)
        $normalizeAccents = function ($str) {
            return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER($str),'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u')";
        };

        foreach ($filtros as $key => $filtro) {
            // Búsqueda insensible a acentos
            $arrQueryClientes[] = "( " . $normalizeAccents('c.dias_visitas') . " LIKE LOWER('%$key%') AND c.id_ruta IN (" . implode(',', $filtro) . ") )";
        }

        $whereClientes = '';
        if (!empty($arrQueryClientes)) {
            $whereClientes = "AND (" . implode(' OR ', $arrQueryClientes) . ")";
        }

        $whereDiasVisita = '';
        if (!empty($diasVisita)) {
            // Búsqueda insensible a acentos
            $whereDiasVisita = "AND " . $normalizeAccents('c.dias_visitas') . " LIKE LOWER('%$diasVisita%')";
        }

        try {
            // Detectar si están activos los 3 filtros: día de visita, ruta y fecha fin
            $tieneLosTresFiltros = !empty($diasVisita) && !empty($ruta) && !empty($fecha_fin);
            
            if ($tieneLosTresFiltros) {
                // Ordenamiento: primero mercado, luego fecha
                $orderBy = "ORDER BY 
                    CAST(CASE WHEN tb.mercado IS NULL OR tb.mercado = '' THEN 999 ELSE tb.mercado END AS UNSIGNED) ASC,
                    tb.fecha_emision DESC";
            } else {
                // Ordenamiento por defecto: primero fecha, luego mercado
                $orderBy = "ORDER BY 
                    tb.fecha_emision DESC,
                    CAST(CASE WHEN tb.mercado IS NULL OR tb.mercado = '' THEN 999 ELSE tb.mercado END AS UNSIGNED) ASC";
            }

            // Variables condicionales para ventas
            $whereFechaVentas = '';
            if ($fecha_inicio && $fecha_fin) {
                $whereFechaVentas = "AND v.fecha_emision BETWEEN '$fecha_inicio' AND '$fecha_fin'";
            } elseif ($fecha_inicio) {
                $whereFechaVentas = "AND v.fecha_emision >= '$fecha_inicio'";
            } elseif ($fecha_fin) {
                $whereFechaVentas = "AND v.fecha_emision <= '$fecha_fin'";
            }

            $whereUsuarioVentas = '';
            if ($id_usuario) {
                $whereUsuarioVentas = "AND v.id_vendedor = '$id_usuario'";
            }

            // PRIMERA CONSULTA (ventas) - Con filtros unificados y ORDER BY
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
                (v.total - SUM(CASE WHEN dv.estado = '1' THEN dv.monto ELSE 0 END)) AS saldo,
                c.mercado,
                c.dias_visitas,
                c.id_ruta
            FROM ventas AS v
            INNER JOIN dias_ventas AS dv ON v.id_venta = dv.id_venta 
            INNER JOIN clientes AS c ON v.id_cliente = c.id_cliente
            LEFT JOIN usuarios u ON u.usuario_id = v.id_vendedor
            WHERE v.estado = 1 
                AND v.id_empresa = '{$_SESSION['id_empresa']}' 
                " . ($_SESSION["rol"] != 4 ? "AND v.sucursal = '{$_SESSION['sucursal']}'" : "") . "
                $whereUsuarioVentas
                $whereFechaVentas
                $whereRuta
                $whereClientes
                $whereDiasVisita
            GROUP BY v.id_venta
            HAVING v.total > SUM(CASE WHEN dv.estado = '1' THEN dv.monto ELSE 0 END)
            $orderBy";

            $filaVentas = mysqli_query($this->conectar, $sqlVentas);
            $listaVentas = [];
            if ($filaVentas) {
                $listaVentas = mysqli_fetch_all($filaVentas, MYSQLI_ASSOC);
            }

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
                    c.mercado AS mercado,
                    c.dias_visitas,
                    c.id_ruta,
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
            WHERE tb.total > tb.pagado
            $orderBy";

            $fila = mysqli_query($this->conectar, $sql);
            $listaCoti = [];
            if ($fila) {
                $listaCoti = mysqli_fetch_all($fila, MYSQLI_ASSOC);
            } else {
                echo "Error: " . mysqli_error($this->conectar);
            }

            // Unificar resultados y reordenar
            $listaCompleta = array_merge($listaVentas, $listaCoti);

            // Reordenar el array combinado (ordenamiento multinivel)
            usort($listaCompleta, function ($a, $b) use ($tieneLosTresFiltros) {
                if ($tieneLosTresFiltros) {
                    // Ordenamiento: primero mercado (ASC), luego cliente (ASC alfabético), luego fecha (DESC)
                    $mercadoA = $a['mercado'] === '' || $a['mercado'] === null ? 999 : (int) $a['mercado'];
                    $mercadoB = $b['mercado'] === '' || $b['mercado'] === null ? 999 : (int) $b['mercado'];
                    
                    if ($mercadoA !== $mercadoB) {
                        return $mercadoA - $mercadoB;
                    }
                    
                    // Si mercados son iguales, comparar por nombre de cliente (ASC alfabético)
                    $partsA = explode('|', $a['cliente']);
                    $partsB = explode('|', $b['cliente']);
                    
                    $clienteA = isset($partsA[1]) ? trim($partsA[1]) : trim($partsA[0]);
                    $clienteB = isset($partsB[1]) ? trim($partsB[1]) : trim($partsB[0]);
                    
                    // Remover información entre paréntesis para ordenar
                    $clienteA = preg_replace('/\s*\([^)]*\)/', '', $clienteA);
                    $clienteB = preg_replace('/\s*\([^)]*\)/', '', $clienteB);
                    
                    $clienteA_lower = strtolower($clienteA);
                    $clienteB_lower = strtolower($clienteB);
                    
                    if ($clienteA_lower !== $clienteB_lower) {
                        return strcmp($clienteA_lower, $clienteB_lower);
                    }
                    
                    // Si clientes son iguales, comparar por fecha (DESC)
                    return strcmp($b['fecha_emision'], $a['fecha_emision']);
                } else {
                    // Ordenamiento por defecto: primero mercado (ASC), luego cliente (ASC alfabético), luego fecha (DESC)
                    $mercadoA = $a['mercado'] === '' || $a['mercado'] === null ? 999 : (int) $a['mercado'];
                    $mercadoB = $b['mercado'] === '' || $b['mercado'] === null ? 999 : (int) $b['mercado'];
                    
                    if ($mercadoA !== $mercadoB) {
                        return $mercadoA - $mercadoB;
                    }
                    
                    // Si mercados son iguales, comparar por nombre de cliente (ASC alfabético)
                    $partsA = explode('|', $a['cliente']);
                    $partsB = explode('|', $b['cliente']);
                    
                    $clienteA = isset($partsA[1]) ? trim($partsA[1]) : trim($partsA[0]);
                    $clienteB = isset($partsB[1]) ? trim($partsB[1]) : trim($partsB[0]);
                    
                    // Remover información entre paréntesis para ordenar
                    $clienteA = preg_replace('/\s*\([^)]*\)/', '', $clienteA);
                    $clienteB = preg_replace('/\s*\([^)]*\)/', '', $clienteB);
                    
                    $clienteA_lower = strtolower($clienteA);
                    $clienteB_lower = strtolower($clienteB);
                    
                    if ($clienteA_lower !== $clienteB_lower) {
                        return strcmp($clienteA_lower, $clienteB_lower);
                    }
                    
                    // Si clientes son iguales, comparar por fecha (DESC)
                    return strcmp($b['fecha_emision'], $a['fecha_emision']);
                }
            });

            return $listaCompleta;
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
        $id_usuario = isset($data['id_usuario']) && $data['id_usuario'] !== '' ? $data['id_usuario'] : null;
        $fecha_inicio = isset($data['fecha_inicio']) && $data['fecha_inicio'] !== '' ? $data['fecha_inicio'] : null;
        $fecha_fin = isset($data['fecha_fin']) && $data['fecha_fin'] !== '' ? $data['fecha_fin'] : null;
        $camion = isset($data['camion']) && $data['camion'] !== '' ? $data['camion'] : null;
        $diasVisita = isset($data['diasVisita']) && $data['diasVisita'] !== '' ? $data['diasVisita'] : null;
        $ruta = isset($data['ruta']) && $data['ruta'] !== '' ? $data['ruta'] : null;

        $whereFechaCoti = '';
        if ($fecha_inicio && $fecha_fin) {
            $whereFechaCoti = "AND co.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        } elseif ($fecha_inicio) {
            $whereFechaCoti = "AND co.fecha >= '$fecha_inicio'";
        } elseif ($fecha_fin) {
            $whereFechaCoti = "AND co.fecha <= '$fecha_fin'";
        }

        $whereUsuarioCoti = '';
        if ($id_usuario) {
            $whereUsuarioCoti = "AND co.id_usuario = '$id_usuario'";
        }

        $whereRuta = '';
        if (!empty($ruta)) {
            $whereRuta = "AND c.id_ruta = '$ruta'";
        }

        // Filtros por camión
        switch ($camion) {
            case '1':
                $filtros = ['lunes' => ['1', '7'], 'martes' => ['5', '7'], 'miercoles' => ['5'], 'jueves' => ['1', '7'], 'viernes' => ['6', '7'], 'sabado' => ['7', '8']];
                break;
            case '2':
                $filtros = ['lunes' => ['3', '6'], 'martes' => ['1', '3'], 'miercoles' => ['1', '3'], 'jueves' => ['6', '3'], 'viernes' => ['3', '5'], 'sabado' => ['3', '6']];
                break;
            case '3':
                $filtros = ['miercoles' => ['6', '7'], 'viernes' => ['8', '2'], 'sabado' => ['1', '5']];
                break;
        }

        if ($diasVisita != "" && isset($filtros[$diasVisita])) {
            $filtros = [$diasVisita => $filtros[$diasVisita]];
        }
        if ($ruta != "") {
            foreach ($filtros as $key => $filtro) {
                $filtros[$key] = [$ruta];
            }
        }
        // Función auxiliar para normalizar acentos (insensible a acentos)
        $normalizeAccents = function ($str) {
            return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER($str),'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u')";
        };

        foreach ($filtros as $key => $filtro) {
            // Búsqueda insensible a acentos
            $arrQueryClientes[] = "( " . $normalizeAccents('c.dias_visitas') . " LIKE LOWER('%$key%') AND c.id_ruta IN (" . implode(',', $filtro) . ") )";
        }

        $whereClientes = '';
        if (!empty($arrQueryClientes)) {
            $whereClientes = "AND (" . implode(' OR ', $arrQueryClientes) . ")";
        }

        $whereDiasVisita = '';
        if (!empty($diasVisita)) {
            // Búsqueda insensible a acentos
            $whereDiasVisita = "AND " . $normalizeAccents('c.dias_visitas') . " LIKE LOWER('%$diasVisita%')";
        }

        try {
            $orderBy = "ORDER BY 
                tb.fecha_emision DESC,
                CAST(CASE WHEN tb.mercado IS NULL OR tb.mercado = '' THEN 999 ELSE tb.mercado END AS UNSIGNED) ASC";

            // Variables condicionales para ventas (similar a cotizaciones)
            $whereFechaVentas = '';
            if ($fecha_inicio && $fecha_fin) {
                $whereFechaVentas = "AND v.fecha_emision BETWEEN '$fecha_inicio' AND '$fecha_fin'";
            } elseif ($fecha_inicio) {
                $whereFechaVentas = "AND v.fecha_emision >= '$fecha_inicio'";
            } elseif ($fecha_fin) {
                $whereFechaVentas = "AND v.fecha_emision <= '$fecha_fin'";
            }

            $whereUsuarioVentas = '';
            if ($id_usuario) {
                $whereUsuarioVentas = "AND v.id_vendedor = '$id_usuario'";
            }

            // PRIMERA CONSULTA (ventas) - Con filtros unificados y ORDER BY
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
                (v.total - SUM(CASE WHEN dv.estado = '1' THEN dv.monto ELSE 0 END)) AS saldo,
                c.mercado,
                c.dias_visitas,
                c.id_ruta
            FROM ventas AS v
            INNER JOIN dias_ventas AS dv ON v.id_venta = dv.id_venta 
            INNER JOIN clientes AS c ON v.id_cliente = c.id_cliente
            LEFT JOIN usuarios u ON u.usuario_id = v.id_vendedor
            WHERE v.estado = 1 
                AND v.id_empresa = '{$_SESSION['id_empresa']}' 
                " . ($_SESSION["rol"] != 4 ? "AND v.sucursal = '{$_SESSION['sucursal']}'" : "") . "
                $whereUsuarioVentas
                $whereFechaVentas
                $whereRuta
                $whereClientes
                $whereDiasVisita
            GROUP BY v.id_venta
            $orderBy";

            $fila = mysqli_query($this->conectar, $sqlVentas);
            $lista = [];
            if ($fila) {
                $lista = mysqli_fetch_all($fila, MYSQLI_ASSOC);
            }

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
                    c.mercado AS mercado,
                    c.dias_visitas,
                    c.id_ruta,
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
            }

            $datos = array_merge($lista, $lista2);

            // Reordenar el array combinado (ordenamiento multinivel descendente)
            usort($datos, function ($a, $b) {
                // Mapeo de días a números (insensible a acentos)
                $diasOrden = [
                    'lunes' => 1,
                    'martes' => 2,
                    'miercoles' => 3,
                    'miércoles' => 3,
                    'jueves' => 4,
                    'viernes' => 5,
                    'sabado' => 6,
                    'sábado' => 6,
                    'domingo' => 7
                ];

                // Comparar fecha_emision (DESC)
                $fechaComp = strcmp($b['fecha_emision'], $a['fecha_emision']);
                if ($fechaComp !== 0)
                    return $fechaComp;

                // Comparar mercado (ASC)
                $mercadoA = $a['mercado'] === '' || $a['mercado'] === null ? 999 : (int) $a['mercado'];
                $mercadoB = $b['mercado'] === '' || $b['mercado'] === null ? 999 : (int) $b['mercado'];
                return $mercadoA - $mercadoB;
            });

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
            $vendedores = [];
            foreach ($datos as $venta) {
                $vendedor = $venta['vendedor'] !== '' ? $venta['vendedor'] : 'Sin asignar';
                if (!isset($vendedores[$vendedor])) {
                    $vendedores[$vendedor] = [];
                }
                $vendedores[$vendedor][] = $venta;
            }

            $html = '
            <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 10px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                }
                .vendedor-header {
                    background-color: #e0e0e0;
                    font-weight: bold;
                    padding: 10px;
                    margin-top: 20px;
                }
            </style>
            <h2 style="text-align: center;">Reporte de Cobranzas</h2>';

            foreach ($vendedores as $vendedor => $deudas) {
                $html .= '<div class="vendedor-header">Vendedor: ' . $vendedor . '</div>';
                $html .= '
                <table>
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Codigo</th>
                            <th>F. Emisión</th>
                            <th>F. Vencimiento</th>
                            <th>Cliente</th>
                            <th>Mercado</th>
                            <th>Ruta</th>
                            <th>Total</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>';

                foreach ($deudas as $venta) {
                    $html .= '
                        <tr>
                            <td>' . $venta['id_venta'] . '</td>
                            <td>' . $venta['factura'] . '</td>
                            <td>' . $venta['fecha_emision'] . '</td>
                            <td>' . $venta['fecha_vencimiento'] . '</td>
                            <td>' . $venta['cliente'] . '</td>
                            <td>' . $venta['mercado'] . '</td>
                            <td>' . $venta['id_ruta'] . '</td>
                            <td style="text-align: right;">S/. ' . number_format($venta['total'], 2) . '</td>
                            <td style="text-align: right;">S/. ' . number_format($venta['pagado'], 2) . '</td>
                            <td style="text-align: right;">S/. ' . number_format($venta['saldo'], 2) . '</td>
                        </tr>';
                }
                $html .= '
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" style="text-align: right;"><strong>Subtotal (' . $vendedor . '):</strong></td>
                            <td style="text-align: right;"><strong>S/. ' . number_format($resumen[$vendedor]['total'], 2) . '</strong></td>
                            <td style="text-align: right;"><strong>S/. ' . number_format($resumen[$vendedor]['pagado'], 2) . '</strong></td>
                            <td style="text-align: right;"><strong>S/. ' . number_format($resumen[$vendedor]['saldo'], 2) . '</strong></td>
                        </tr>
                    </tfoot>
                </table>';
            }

            $html .= '<div style="margin-top: 20px; font-weight: bold; border-top: 2px solid #000; padding-top: 10px;">
                        Resumen General de Cobranzas:
                      </div>';

            $total_general = 0;
            $pagado_general = 0;
            $saldo_general = 0;
            foreach ($resumen as $res) {
                $total_general += $res['total'];
                $pagado_general += $res['pagado'];
                $saldo_general += $res['saldo'];
            }

            $html .= '
            <table>
                <thead>
                    <tr>
                        <th>Vendedor</th>
                        <th>Total</th>
                        <th>Pagado</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>';
            foreach ($resumen as $vendedor => $res) {
                $html .= '<tr>
                    <td>' . $vendedor . '</td>
                    <td style="text-align: right;">S/. ' . number_format($res['total'], 2) . '</td>
                    <td style="text-align: right;">S/. ' . number_format($res['pagado'], 2) . '</td>
                    <td style="text-align: right;">S/. ' . number_format($res['saldo'], 2) . '</td>
                </tr>';
            }
            $html .= '
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align: right;"><strong>TOTAL GENERAL:</strong></td>
                        <td style="text-align: right;"><strong>S/. ' . number_format($total_general, 2) . '</strong></td>
                        <td style="text-align: right;"><strong>S/. ' . number_format($pagado_general, 2) . '</strong></td>
                        <td style="text-align: right;"><strong>S/. ' . number_format($saldo_general, 2) . '</strong></td>
                    </tr>
                </tfoot>
            </table>';

            $mpdf->WriteHTML($html);
            $mpdf->Output('reporte_cobranzas.pdf', 'I');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function insertarXLista()
    {
        /*   $lista = json_decode($_POST['lista'], true);
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
            $telefonoEditar = trim(filter_var($_POST['telefonoEditar'], F