<?php

class PagosController extends Controller
{
    private $conectar;

    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }

    public function render()
    {
        try {
            $sql = "SELECT com.id_compra,CONCAT(com.serie, ' | ' , com.numero) AS factura,com.moneda ,com.fecha_emision,com.fecha_vencimiento,CONCAT(pro.ruc,' | ' ,pro.razon_social) AS cliente,
            com.total,            
                CASE 
                WHEN dc.estado = '1'  AND dc.id_compra = dc.id_compra THEN SUM(dc.monto)
                WHEN dc.estadO= '0' THEN '0'
                END AS pagado,
               (com.total - SUM(dc.monto) ) AS saldo
                FROM compras AS com
                INNER JOIN dias_compras AS dc ON  com.id_compra=dc.id_compra 
                INNER JOIN proveedores AS pro ON com.id_proveedor=pro.proveedor_id
                WHERE com.id_tipo_pago = 2  and com.id_empresa='{$_SESSION['id_empresa']}'
                and com.sucursal='{$_SESSION['sucursal']}'
                GROUP BY dc.id_compra,dc.estado  
            ";
            $fila = mysqli_query($this->conectar, $sql);
            return json_encode(mysqli_fetch_all($fila, MYSQLI_ASSOC));
        } catch (Exception $e) {
            return json_encode([]);
        }
    }
    public function getAllByIdCompra()
    {


        try {
            $sql = "SELECT * FROM dias_compras WHERE id_compra = '{$_POST['id']}'";
            $fila = mysqli_query($this->conectar, $sql);
            return json_encode(mysqli_fetch_all($fila, MYSQLI_ASSOC));
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function totalCuotaVentas2()
    {
        $totalApagar = "SELECT total FROM cotizaciones WHERE cotizacion_id = '{$_POST['id_venta']}'";
        $result = $this->conectar->query($totalApagar);
        $row = $result->fetch_assoc();
        echo json_encode($row);
    }
    public function totalCuotaVentas()
    {
        if ($_POST['tipo'] == 'c') {
            $totalApagar = "SELECT total FROM cotizaciones WHERE cotizacion_id = '{$_POST['id_venta']}'";
        } else {
            $totalApagar = "SELECT total FROM ventas WHERE id_venta = '{$_POST['id_venta']}'";
        }
        $result = $this->conectar->query($totalApagar);
        $row = $result->fetch_assoc();
        echo json_encode($row);
    }
    public function validarLista()
    {
        $listaPagos = json_decode($_POST['dias_lista'], true);
        echo json_encode($listaPagos);
    }
    public function pagarCuota()
    {
        $sql = "UPDATE dias_compras set estado = '1' where dias_compra_id='{$_POST['id']}'";
        $result = $this->conectar->query($sql);

        echo json_encode($result);
    }

    public function pagarCuotaVentas()
    {
        // Validar que existan los parámetros requeridos
        if (!isset($_POST['tipo']) || !isset($_POST['id']) || !isset($_POST['fecha']) || !isset($_POST['monto'])) {
            echo json_encode([
                "res" => false,
                "error" => "Faltan parámetros requeridos",
                "params_recibidos" => array_keys($_POST)
            ]);
            return;
        }

        // Obtener la fecha y hora actual del sistema
        date_default_timezone_set('America/Lima');
        $fecha_pago_real = date('Y-m-d H:i:s');
        $hora_pago = date('h:i A');
        $fecha_hoy = date('Y-m-d');

        $tipo_pago = (isset($_POST['tipo_pago'])) ? $_POST['tipo_pago'] : 'Efectivo';

        // Obtener id_usuario con fallback a usuario_id si usuario_fac no existe
        $id_usuario = null;
        if (isset($_SESSION['usuario_fac']) && !empty($_SESSION['usuario_fac'])) {
            $id_usuario = $_SESSION['usuario_fac'];
        } elseif (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
            $id_usuario = $_SESSION['usuario_id'];
        }

        // Validación de seguridad: No permitir pagos sin usuario identificado
        if (empty($id_usuario)) {
            echo json_encode([
                "res" => false,
                "error" => "No se pudo identificar al usuario (Sesión expirada). Por favor, cierre sesión y vuelva a ingresar."
            ]);
            return;
        }

        $rol_usuario = isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
        $es_nuevo = isset($_POST['es_nuevo']) ? $_POST['es_nuevo'] : 0;

        // Determinar el método de pago (1=Efectivo, 2=Tarjetas, 3=Transferencias)
        $metodo_pago = 1; // Por defecto Efectivo
        if (in_array($tipo_pago, ['Plin', 'Yape', 'BCP', 'BBVA'])) {
            $metodo_pago = 3; // Transferencias
        }

        if ($_POST["tipo"] == 'c') {
            if ($es_nuevo == 1) {
                // Insertar nueva cuota para cotización
                $sql = "INSERT INTO cuotas_cotizacion (id_coti, estado, fecha, monto, tipo_pago, id_usuario, fecha_pago_real) 
                        VALUES ('{$_POST['id']}', '1', '{$_POST['fecha']}', '{$_POST['monto']}', '{$tipo_pago}', '$id_usuario', '{$fecha_pago_real}')";
            } else {
                // Actualizar cuota existente
                $sql = "UPDATE cuotas_cotizacion SET 
                        estado = '1',
                        fecha = '{$_POST['fecha']}', 
                        monto = '{$_POST['monto']}', 
                        tipo_pago = '{$tipo_pago}', 
                        id_usuario = '$id_usuario',
                        fecha_pago_real = '{$fecha_pago_real}'
                        WHERE cuota_coti_id = '{$_POST['id']}'";
            }
            $result = $this->conectar->query($sql);

            if (!$result) {
                echo json_encode(["res" => false, "error" => $this->conectar->error, "sql" => $sql]);
            } else {
                echo json_encode(["res" => true, "es_nuevo" => (string)$es_nuevo]);
            }
        } else {
            if ($es_nuevo == 1) {
                // Insertar nueva cuota para venta
                $sql = "INSERT INTO dias_ventas (id_venta, estado, fecha, monto, tipo_pago, id_usuario, fecha_pago_real) 
                        VALUES ('{$_POST['id']}', '1', '{$_POST['fecha']}', '{$_POST['monto']}', '{$tipo_pago}', '$id_usuario', '{$fecha_pago_real}')";
            } else {
                // Actualizar cuota existente
                $sql = "UPDATE dias_ventas SET 
                        estado = '1', 
                        fecha = '{$_POST['fecha']}', 
                        monto = '{$_POST['monto']}', 
                        tipo_pago = '{$tipo_pago}', 
                        id_usuario = '$id_usuario',
                        fecha_pago_real = '{$fecha_pago_real}'
                        WHERE dias_venta_id = '{$_POST['id']}'";
            }
            $result = $this->conectar->query($sql);

            if (!$result) {
                echo json_encode(["res" => false, "error" => $this->conectar->error, "sql" => $sql]);
            } else {
                echo json_encode(["res" => true, "es_nuevo" => (string)$es_nuevo]);
            }
        }
    }

    public function eliminarPagoCuotaVentas()
    {
        // Solo administradores pueden eliminar pagos
        if ($_SESSION['rol'] == 3) {
            echo json_encode(["res" => false, "msg" => "No tienes permisos para eliminar pagos"]);
            return;
        }

        if ($_POST["tipo"] == 'c') {
            $sql = "UPDATE cuotas_cotizacion set estado = '0',fecha = 0, monto = '0', tipo_pago=NULL, id_usuario=NULL where cuota_coti_id='{$_POST['id']}'";
            $result = $this->conectar->query($sql);

            echo json_encode($result);
        } else {
            $sql = "UPDATE dias_ventas set estado = '0' where dias_venta_id='{$_POST['id']}'";
            $result = $this->conectar->query($sql);

            echo json_encode($result);
        }
    }

    public function editarCuotaVentas()
    {
        // Solo administradores pueden editar pagos
        if ($_SESSION['rol'] != 1) {
            echo json_encode(["res" => false, "msg" => "No tienes permisos para editar pagos"]);
            return;
        }

        // Validar parámetros
        if (!isset($_POST['tipo']) || !isset($_POST['id']) || !isset($_POST['monto'])) {
            echo json_encode(["res" => false, "msg" => "Faltan parámetros requeridos"]);
            return;
        }

        $id = $_POST['id'];
        $tipo = $_POST['tipo'];
        $monto = floatval($_POST['monto']);
        $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $tipo_pago = isset($_POST['tipo_pago']) ? $_POST['tipo_pago'] : 'Efectivo';

        // Validar que el monto sea positivo
        if ($monto <= 0) {
            echo json_encode(["res" => false, "msg" => "El monto debe ser mayor a 0"]);
            return;
        }

        // Obtener el total de la venta/cotización para validar
        if ($tipo == 'c') {
            $sqlTotal = "SELECT co.total, co.cotizacion_id 
                        FROM cuotas_cotizacion cc
                        INNER JOIN cotizaciones co ON co.cotizacion_id = cc.id_coti
                        WHERE cc.cuota_coti_id = '$id'";
        } else {
            $sqlTotal = "SELECT v.total, v.id_venta 
                        FROM dias_ventas dv
                        INNER JOIN ventas v ON v.id_venta = dv.id_venta
                        WHERE dv.dias_venta_id = '$id'";
        }

        $resultTotal = $this->conectar->query($sqlTotal);
        if (!$resultTotal || $resultTotal->num_rows == 0) {
            echo json_encode(["res" => false, "msg" => "No se encontró la venta/cotización"]);
            return;
        }

        $rowTotal = $resultTotal->fetch_assoc();
        $totalVenta = floatval($rowTotal['total']);
        $id_venta = $tipo == 'c' ? $rowTotal['cotizacion_id'] : $rowTotal['id_venta'];

        // Calcular el total pagado (excluyendo la cuota actual)
        if ($tipo == 'c') {
            $sqlPagado = "SELECT IFNULL(SUM(monto), 0) as total_pagado 
                         FROM cuotas_cotizacion 
                         WHERE id_coti = '$id_venta' 
                         AND estado = 1 
                         AND cuota_coti_id != '$id'";
        } else {
            $sqlPagado = "SELECT IFNULL(SUM(monto), 0) as total_pagado 
                         FROM dias_ventas 
                         WHERE id_venta = '$id_venta' 
                         AND estado = 1 
                         AND dias_venta_id != '$id'";
        }

        $resultPagado = $this->conectar->query($sqlPagado);
        $rowPagado = $resultPagado->fetch_assoc();
        $totalPagado = floatval($rowPagado['total_pagado']);

        // Validar que el nuevo monto no exceda el total
        if (($totalPagado + $monto) > $totalVenta) {
            $disponible = $totalVenta - $totalPagado;
            echo json_encode([
                "res" => false,
                "msg" => "El monto excede el total. Disponible: S/ " . number_format($disponible, 2)
            ]);
            return;
        }

        // Actualizar la cuota
        if ($tipo == 'c') {
            $sql = "UPDATE cuotas_cotizacion 
                   SET monto = '$monto', 
                       fecha = '$fecha', 
                       tipo_pago = '$tipo_pago'
                   WHERE cuota_coti_id = '$id'";
        } else {
            $sql = "UPDATE dias_ventas 
                   SET monto = '$monto', 
                       fecha = '$fecha', 
                       tipo_pago = '$tipo_pago'
                   WHERE dias_venta_id = '$id'";
        }

        $result = $this->conectar->query($sql);

        if ($result) {
            echo json_encode(["res" => true, "msg" => "Cuota actualizada correctamente"]);
        } else {
            echo json_encode(["res" => false, "msg" => "Error al actualizar la cuota", "sql" => $sql]);
        }
    }
}
