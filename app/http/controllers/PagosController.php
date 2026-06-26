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
            $sql = "SELECT com.id_compra, CONCAT(com.serie, ' | ', com.numero) AS factura, com.moneda, com.fecha_emision, com.fecha_vencimiento,
                CONCAT(pro.ruc, ' | ', pro.razon_social) AS cliente,
                com.total,
                IFNULL(SUM(CASE WHEN dc.estado = '1' THEN dc.monto ELSE 0 END), 0) AS pagado,
                (com.total - IFNULL(SUM(CASE WHEN dc.estado = '1' THEN dc.monto ELSE 0 END), 0)) AS saldo
                FROM compras AS com
                INNER JOIN dias_compras AS dc ON com.id_compra = dc.id_compra
                INNER JOIN proveedores AS pro ON com.id_proveedor = pro.proveedor_id
                WHERE com.id_tipo_pago = 2 AND com.id_empresa='{$_SESSION['id_empresa']}'
                AND com.sucursal='{$_SESSION['sucursal']}'
                GROUP BY com.id_compra
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
            $sql = "SELECT dc.*, 'v' as tipo_doc FROM dias_compras dc WHERE dc.id_compra = '{$_POST['id']}'";
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
        $id      = $_POST['id'];
        $es_nuevo = isset($_POST['es_nuevo']) ? intval($_POST['es_nuevo']) : 0;
        $montoPagado = isset($_POST['monto_pagado']) ? floatval($_POST['monto_pagado']) : null;
        $tipo_pago = isset($_POST['tipo_pago']) ? $_POST['tipo_pago'] : 'Efectivo';
        $fecha_pago = isset($_POST['fecha_pago']) ? $_POST['fecha_pago'] : date('Y-m-d');

        date_default_timezone_set('America/Lima');
        $fecha_pago_real = date('Y-m-d H:i:s');

        $id_usuario = null;
        if (isset($_SESSION['usuario_fac']) && !empty($_SESSION['usuario_fac'])) {
            $id_usuario = $_SESSION['usuario_fac'];
        } elseif (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
            $id_usuario = $_SESSION['usuario_id'];
        }

        // Si es una cuota nueva (agregada desde el modal)
        if ($es_nuevo == 1) {
            $idCompra = isset($_POST['id_compra']) ? intval($_POST['id_compra']) : 0;
            if ($idCompra <= 0) {
                echo json_encode(["res" => false, "msg" => "Compra no especificada"]);
                return;
            }
            if ($montoPagado === null || $montoPagado <= 0) {
                echo json_encode(["res" => false, "msg" => "Monto invalido"]);
                return;
            }
            $sql = "INSERT INTO dias_compras (id_compra, monto, fecha, estado, tipo_pago, id_usuario, fecha_pago_real) 
                    VALUES ('$idCompra', '$montoPagado', '$fecha_pago', '1', '$tipo_pago', " . ($id_usuario ? "'$id_usuario'" : "NULL") . ", '$fecha_pago_real')";
            $this->conectar->query($sql);
            echo json_encode(["res" => true, "insert_id" => $this->conectar->insert_id]);
            return;
        }

        $idInt = intval($id);

        // Obtener la cuota actual
        $cuota = $this->conectar->query("SELECT * FROM dias_compras WHERE dias_compra_id = '$idInt'")->fetch_assoc();
        if (!$cuota) {
            echo json_encode(["res" => false, "msg" => "Cuota no encontrada"]);
            return;
        }

        $montoTotal = floatval($cuota['monto']);

        // Si no se envió monto o es igual/mayor al total → pago completo
        if ($montoPagado === null || $montoPagado >= $montoTotal) {
            $sql = "UPDATE dias_compras SET estado = '1', tipo_pago = '$tipo_pago', fecha = '$fecha_pago', id_usuario = " . ($id_usuario ? "'$id_usuario'" : "NULL") . ", fecha_pago_real = '$fecha_pago_real' WHERE dias_compra_id = '$idInt'";
            $this->conectar->query($sql);
            echo json_encode(["res" => true]);
            return;
        }

        // Pago parcial: actualizar cuota actual con el monto pagado y marcarla pagada
        $saldo = round($montoTotal - $montoPagado, 2);
        $this->conectar->query("UPDATE dias_compras SET estado = '1', monto = '$montoPagado', tipo_pago = '$tipo_pago', fecha = '$fecha_pago', id_usuario = " . ($id_usuario ? "'$id_usuario'" : "NULL") . ", fecha_pago_real = '$fecha_pago_real' WHERE dias_compra_id = '$idInt'");

        // Crear nueva cuota con el saldo pendiente
        $idCompra = intval($cuota['id_compra']);
        $fecha    = $this->conectar->real_escape_string($cuota['fecha']);
        $this->conectar->query("INSERT INTO dias_compras (id_compra, monto, fecha, estado) VALUES ('$idCompra', '$saldo', '$fecha', 0)");

        echo json_encode(["res" => true, "parcial" => true, "saldo" => $saldo]);
    }

    public function pagarCuotaVentas()
    {
        // LOG: Registrar inicio
        error_log("=== INICIO pagarCuotaVentas ===");
        error_log("POST recibido: " . json_encode($_POST));
        error_log("SESSION usuario_fac: " . (isset($_SESSION['usuario_fac']) ? $_SESSION['usuario_fac'] : 'NO EXISTE'));
        error_log("SESSION usuario_id: " . (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'NO EXISTE'));
        
        // Validar que existan los par�metros requeridos
        if (!isset($_POST['tipo']) || !isset($_POST['id']) || !isset($_POST['fecha']) || !isset($_POST['monto'])) {
            error_log("ERROR: Faltan parámetros requeridos");
            echo json_encode([
                "res" => false,
                "error" => "Faltan par�metros requeridos",
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

        error_log("ID Usuario determinado: " . ($id_usuario ?? 'NULL'));

        // Validaci�n de seguridad: No permitir pagos sin usuario identificado
        if (empty($id_usuario)) {
            error_log("ERROR: No se pudo identificar al usuario");
            echo json_encode([
                "res" => false,
                "error" => "No se pudo identificar al usuario (Sesi�n expirada). Por favor, cierre sesi�n y vuelva a ingresar."
            ]);
            return;
        }

        $rol_usuario = isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
        $es_nuevo = isset($_POST['es_nuevo']) ? $_POST['es_nuevo'] : 0;

        error_log("es_nuevo: " . $es_nuevo);
        error_log("tipo: " . $_POST["tipo"]);

        // Determinar el m�todo de pago (1=Efectivo, 2=Tarjetas, 3=Transferencias)
        $metodo_pago = 1; // Por defecto Efectivo
        if (in_array($tipo_pago, ['Plin', 'Yape', 'BCP', 'BBVA'])) {
            $metodo_pago = 3; // Transferencias
        }

        if ($_POST["tipo"] == 'c') {
            if ($es_nuevo == 1) {
                // Insertar nueva cuota para cotizaci�n
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
            
            error_log("SQL a ejecutar: " . $sql);
            $result = $this->conectar->query($sql);
            error_log("Resultado query: " . ($result ? 'TRUE' : 'FALSE'));
            
            if (!$result) {
                $error_msg = $this->conectar->error;
                error_log("ERROR SQL: " . $error_msg);
                echo json_encode([
                    "res" => false, 
                    "error" => $error_msg, 
                    "sql" => $sql,
                    "errno" => $this->conectar->errno
                ]);
            } else {
                // COMMIT EXPLÍCITO para asegurar que se guarde
                $commit_result = $this->conectar->commit();
                error_log("COMMIT ejecutado: " . ($commit_result ? 'SUCCESS' : 'FAILED'));
                
                $insert_id = $this->conectar->insert_id;
                $affected_rows = $this->conectar->affected_rows;
                error_log("Insert ID: " . $insert_id);
                error_log("Affected rows: " . $affected_rows);
                
                // Verificar que realmente se insertó
                if ($es_nuevo == 1 && $insert_id > 0) {
                    $verify_sql = "SELECT * FROM cuotas_cotizacion WHERE cuota_coti_id = $insert_id";
                    $verify_result = $this->conectar->query($verify_sql);
                    if ($verify_row = $verify_result->fetch_assoc()) {
                        error_log("VERIFICACIÓN: Registro insertado correctamente: " . json_encode($verify_row));
                    } else {
                        error_log("ADVERTENCIA: No se encontró el registro insertado");
                    }
                }
                
                echo json_encode([
                    "res" => true, 
                    "es_nuevo" => (string)$es_nuevo, 
                    "insert_id" => $insert_id, 
                    "affected_rows" => $affected_rows,
                    "sql_ejecutado" => $sql
                ]);
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
            
            error_log("SQL a ejecutar: " . $sql);
            $result = $this->conectar->query($sql);
            error_log("Resultado query: " . ($result ? 'TRUE' : 'FALSE'));

            if (!$result) {
                $error_msg = $this->conectar->error;
                error_log("ERROR SQL: " . $error_msg);
                echo json_encode([
                    "res" => false, 
                    "error" => $error_msg, 
                    "sql" => $sql,
                    "errno" => $this->conectar->errno
                ]);
            } else {
                // COMMIT EXPLÍCITO para asegurar que se guarde
                $commit_result = $this->conectar->commit();
                error_log("COMMIT ejecutado: " . ($commit_result ? 'SUCCESS' : 'FAILED'));
                
                $insert_id = $this->conectar->insert_id;
                $affected_rows = $this->conectar->affected_rows;
                error_log("Insert ID: " . $insert_id);
                error_log("Affected rows: " . $affected_rows);
                
                echo json_encode([
                    "res" => true, 
                    "es_nuevo" => (string)$es_nuevo, 
                    "insert_id" => $insert_id, 
                    "affected_rows" => $affected_rows,
                    "sql_ejecutado" => $sql
                ]);
            }
        }
        
        error_log("=== FIN pagarCuotaVentas ===");
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

    public function editarCuotaCompras()
    {
        if ($_SESSION['rol'] != 1) {
            echo json_encode(["res" => false, "msg" => "No tienes permisos para editar pagos"]);
            return;
        }

        if (!isset($_POST['id']) || !isset($_POST['monto'])) {
            echo json_encode(["res" => false, "msg" => "Faltan parametros requeridos"]);
            return;
        }

        $id = intval($_POST['id']);
        $monto = floatval($_POST['monto']);
        $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
        $tipo_pago = isset($_POST['tipo_pago']) ? $_POST['tipo_pago'] : 'Efectivo';

        if ($monto <= 0) {
            echo json_encode(["res" => false, "msg" => "El monto debe ser mayor a 0"]);
            return;
        }

        $sql = "UPDATE dias_compras SET monto = '$monto', fecha = '$fecha', tipo_pago = '$tipo_pago' WHERE dias_compra_id = '$id'";
        $result = $this->conectar->query($sql);

        if ($result) {
            echo json_encode(["res" => true, "msg" => "Cuota actualizada correctamente"]);
        } else {
            echo json_encode(["res" => false, "msg" => "Error al actualizar la cuota"]);
        }
    }

    public function eliminarPagoCuotaCompras()
    {
        if ($_SESSION['rol'] == 3) {
            echo json_encode(["res" => false, "msg" => "No tienes permisos para eliminar pagos"]);
            return;
        }

        $id = intval($_POST['id']);
        $sql = "UPDATE dias_compras SET estado = '0', tipo_pago = NULL, id_usuario = NULL, fecha_pago_real = NULL WHERE dias_compra_id = '$id'";
        $result = $this->conectar->query($sql);

        echo json_encode(["res" => $result]);
    }

    public function getAllProductosByIdCompra()
    {
        try {
            $id = $_POST['id'];
            $sql = "SELECT 
                    pc.id_producto,
                    pc.cantidad,
                    p.precio,
                    (pc.cantidad * p.precio) AS total,
                    p.descripcion
                FROM productos_compras pc
                INNER JOIN productos p ON p.id_producto = pc.id_producto
                WHERE pc.id_compra = '$id'";
            $result = $this->conectar->query($sql);
            return json_encode($result->fetch_all(MYSQLI_ASSOC));
        } catch (Exception $e) {
            return json_encode([]);
        }
    }

    public function editarCuotaVentas()
    {
        // Solo administradores pueden editar pagos
        if ($_SESSION['rol'] != 1) {
            echo json_encode(["res" => false, "msg" => "No tienes permisos para editar pagos"]);
            return;
        }

        // Validar par�metros
        if (!isset($_POST['tipo']) || !isset($_POST['id']) || !isset($_POST['monto'])) {
            echo json_encode(["res" => false, "msg" => "Faltan par�metros requeridos"]);
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

        // Obtener el total de la venta/cotizaci�n para validar
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
            echo json_encode(["res" => false, "msg" => "No se encontr� la venta/cotizaci�n"]);
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
