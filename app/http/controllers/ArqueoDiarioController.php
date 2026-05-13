<?php

class ArqueoDiarioController extends Controller
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();
    }

    // Obtener cobros del día agrupados por vendedor
    public function obtenerCobrosDia()
    {
        $fecha = $_POST['fecha'];
        $resumen = [];
        
        // Obtener todos los cobros del día de dias_ventas
        $sql_dv = "SELECT 
            IFNULL(dv.id_usuario, v.id_vendedor) as id_usuario,
            u.usuario,
            dv.tipo_pago,
            SUM(dv.monto) as total
        FROM dias_ventas dv
        INNER JOIN ventas v ON v.id_venta = dv.id_venta
        LEFT JOIN usuarios u ON u.usuario_id = IFNULL(dv.id_usuario, v.id_vendedor)
        WHERE DATE(IFNULL(dv.fecha_pago_real, dv.fecha)) = '$fecha'
        AND dv.estado = '1'
        AND v.id_empresa = '{$_SESSION['id_empresa']}'
        AND v.sucursal = '{$_SESSION['sucursal']}'
        GROUP BY IFNULL(dv.id_usuario, v.id_vendedor), u.usuario, dv.tipo_pago";
        
        $result_dv = $this->conexion->query($sql_dv);
        
        if (!$result_dv) {
            echo json_encode(['error' => 'Error en consulta dias_ventas: ' . $this->conexion->error]);
            return;
        }
        
        // Obtener cobros de cuotas_cotizacion
        $sql_cc = "SELECT 
            IFNULL(cc.id_usuario, co.id_usuario) as id_usuario,
            u.usuario,
            cc.tipo_pago,
            SUM(cc.monto) as total
        FROM cuotas_cotizacion cc
        INNER JOIN cotizaciones co ON co.cotizacion_id = cc.id_coti
        LEFT JOIN usuarios u ON u.usuario_id = IFNULL(cc.id_usuario, co.id_usuario)
        WHERE DATE(IFNULL(cc.fecha_pago_real, cc.fecha)) = '$fecha'
        AND cc.estado = '1'
        AND co.id_empresa = '{$_SESSION['id_empresa']}'
        AND co.sucursal = '{$_SESSION['sucursal']}'
        GROUP BY IFNULL(cc.id_usuario, co.id_usuario), u.usuario, cc.tipo_pago";
        
        $result_cc = $this->conexion->query($sql_cc);
        
        if (!$result_cc) {
            echo json_encode(['error' => 'Error en consulta cuotas_cotizacion: ' . $this->conexion->error]);
            return;
        }
        
        // Procesar resultados
        $vendedores = [];
        
        while ($row = $result_dv->fetch_assoc()) {
            $usuario_id = $row['id_usuario'];
            $usuario = $row['usuario'] ?? 'Sin nombre';
            $tipo_pago = $row['tipo_pago'] ?? 'Efectivo';
            
            if (!isset($vendedores[$usuario_id])) {
                $vendedores[$usuario_id] = [
                    'usuario_id' => $usuario_id,
                    'usuario' => $usuario, 
                    'efectivo' => 0, 
                    'bancos' => 0, 
                    'total' => 0,
                    'pagos_digitales_sistema' => []
                ];
            }
            
            if ($tipo_pago == 'Efectivo' || $tipo_pago == NULL) {
                $vendedores[$usuario_id]['efectivo'] += floatval($row['total']);
            } else {
                $vendedores[$usuario_id]['bancos'] += floatval($row['total']);
            }
        }
        
        while ($row = $result_cc->fetch_assoc()) {
            $usuario_id = $row['id_usuario'];
            $usuario = $row['usuario'] ?? 'Sin nombre';
            $tipo_pago = $row['tipo_pago'] ?? 'Efectivo';
            
            if (!isset($vendedores[$usuario_id])) {
                $vendedores[$usuario_id] = [
                    'usuario_id' => $usuario_id,
                    'usuario' => $usuario, 
                    'efectivo' => 0, 
                    'bancos' => 0, 
                    'total' => 0,
                    'pagos_digitales_sistema' => []
                ];
            }
            
            if ($tipo_pago == 'Efectivo' || $tipo_pago == NULL) {
                $vendedores[$usuario_id]['efectivo'] += floatval($row['total']);
            } else {
                $vendedores[$usuario_id]['bancos'] += floatval($row['total']);
            }
        }
        
        // Obtener detalles de pagos digitales para pre-llenar el modal
        $sql_detalles = "SELECT 
            id_usuario_pago, id_vendedor, tipo_pago, monto, cliente_nombre
        FROM (
            SELECT 
                dv.id_usuario as id_usuario_pago,
                v.id_vendedor,
                dv.tipo_pago,
                dv.monto,
                IFNULL(c.datos, 'SIN CLIENTE') as cliente_nombre
            FROM dias_ventas dv
            INNER JOIN ventas v ON v.id_venta = dv.id_venta
            LEFT JOIN clientes c ON c.id_cliente = v.id_cliente
            WHERE (DATE(dv.fecha_pago_real) = '$fecha' OR (dv.fecha_pago_real IS NULL AND dv.fecha = '$fecha'))
            AND dv.estado = '1'
            AND TRIM(LOWER(IFNULL(dv.tipo_pago, ''))) NOT IN ('efectivo', '')
            AND v.id_empresa = '{$_SESSION['id_empresa']}'
            AND v.sucursal = '{$_SESSION['sucursal']}'
            
            UNION ALL
            
            SELECT 
                cc.id_usuario as id_usuario_pago,
                co.id_usuario as id_vendedor,
                cc.tipo_pago,
                cc.monto,
                IFNULL(c.datos, 'SIN CLIENTE') as cliente_nombre
            FROM cuotas_cotizacion cc
            INNER JOIN cotizaciones co ON co.cotizacion_id = cc.id_coti
            LEFT JOIN clientes c ON c.id_cliente = co.id_cliente
            WHERE (DATE(cc.fecha_pago_real) = '$fecha' OR (cc.fecha_pago_real IS NULL AND cc.fecha = '$fecha'))
            AND cc.estado = '1'
            AND TRIM(LOWER(IFNULL(cc.tipo_pago, ''))) NOT IN ('efectivo', '')
            AND co.id_empresa = '{$_SESSION['id_empresa']}'
            AND co.sucursal = '{$_SESSION['sucursal']}'
        ) as t";
        
        $result_detalles = $this->conexion->query($sql_detalles);
        if ($result_detalles) {
            while ($row = $result_detalles->fetch_assoc()) {
                // El cobro pertenece al usuario que lo registró, o al vendedor de la venta si no hay usuario registrado
                $uid = !empty($row['id_usuario_pago']) ? $row['id_usuario_pago'] : $row['id_vendedor'];
                
                if (empty($uid)) continue;
                
                // Aseguramos que el usuario exista en el resumen
                if (!isset($vendedores[$uid])) {
                    $vendedores[$uid] = [
                        'usuario_id' => $uid,
                        'usuario' => 'Usuario ' . $uid,
                        'efectivo' => 0,
                        'bancos' => 0,
                        'total' => 0,
                        'pagos_digitales_sistema' => []
                    ];
                }
                
                $vendedores[$uid]['pagos_digitales_sistema'][] = [
                    'cliente_nombre' => $row['cliente_nombre'],
                    'tipo_pago' => $row['tipo_pago'],
                    'monto' => floatval($row['monto'])
                ];
            }
        }

        // Calcular totales
        foreach ($vendedores as &$v) {
            $v['total'] = $v['efectivo'] + $v['bancos'];
        }
        
        return json_encode(array_values($vendedores));
    }
    
    // Guardar arqueo diario
    public function guardarArqueo()
    {
        $respuesta = ["res" => false];
        
        $fecha = $_POST['fecha'];
        $vendedor = $_POST['vendedor'] ?? '';
        $vendedor_id = $_POST['vendedor_id'] ?? 0;
        $cobros_efectivo = $_POST['cobros_efectivo'] ?? 0;
        $cobros_bancos = $_POST['cobros_bancos'] ?? 0;
        $ingresos_efectivo = $_POST['ingresos_efectivo'] ?? 0;
        $ingresos_bancos = $_POST['ingresos_bancos'] ?? 0;
        $egresos_efectivo = $_POST['egresos_efectivo'] ?? 0;
        $egresos_bancos = $_POST['egresos_bancos'] ?? 0;
        $diferencia_efectivo = $_POST['diferencia_efectivo'] ?? 0;
        $diferencia_bancos = $_POST['diferencia_bancos'] ?? 0;
        $cuadra_efectivo = isset($_POST['cuadra_efectivo']) && $_POST['cuadra_efectivo'] ? 1 : 0;
        $cuadra_bancos = isset($_POST['cuadra_bancos']) && $_POST['cuadra_bancos'] ? 1 : 0;
        
        // Nuevos datos de detalle de efectivo
        $detalle_efectivo = isset($_POST['detalle_efectivo']) ? json_decode($_POST['detalle_efectivo'], true) : null;
        
        // Nuevos datos de pagos digitales
        $pagos_digitales = isset($_POST['pagos_digitales']) ? json_decode($_POST['pagos_digitales'], true) : [];
        
        $usuario_actual = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : $_SESSION['usuario_fac'];
        
        // Iniciar transacción
        $this->conexion->begin_transaction();
        
        try {
            // Insertar arqueo principal
            $sql = "INSERT INTO arqueos_diarios SET
                    id_empresa = '{$_SESSION['id_empresa']}',
                    sucursal = '{$_SESSION['sucursal']}',
                    fecha_arqueo = '$fecha',
                    vendedor = '$vendedor',
                    vendedor_id = '$vendedor_id',
                    cobros_efectivo = '$cobros_efectivo',
                    cobros_bancos = '$cobros_bancos',
                    ingresos_efectivo = '$ingresos_efectivo',
                    ingresos_bancos = '$ingresos_bancos',
                    egresos_efectivo = '$egresos_efectivo',
                    egresos_bancos = '$egresos_bancos',
                    diferencia_efectivo = '$diferencia_efectivo',
                    diferencia_bancos = '$diferencia_bancos',
                    cuadra_efectivo = '$cuadra_efectivo',
                    cuadra_bancos = '$cuadra_bancos',
                    usuario_registro = '$usuario_actual'";
            
            if (!$this->conexion->query($sql)) {
                throw new Exception("Error al guardar arqueo: " . $this->conexion->error);
            }
            
            $arqueo_id = $this->conexion->insert_id;
            
            // Guardar detalle de efectivo si existe
            if ($detalle_efectivo) {
                $billetes = floatval($detalle_efectivo['billetes'] ?? 0);
                $monedas = floatval($detalle_efectivo['monedas'] ?? 0);
                $pasaje = floatval($detalle_efectivo['pasaje'] ?? 0);
                $combustible = floatval($detalle_efectivo['combustible'] ?? 0);
                $gastos = floatval($detalle_efectivo['gastos'] ?? 0);
                $menu = floatval($detalle_efectivo['menu'] ?? 0);
                $otro = floatval($detalle_efectivo['otro'] ?? 0);
                $otro_desc = $this->conexion->real_escape_string($detalle_efectivo['otro_descripcion'] ?? '');
                
                $total_ingresos = $billetes + $monedas;
                $total_gastos = $pasaje + $combustible + $gastos + $menu + $otro;
                $total_efectivo_real = $total_ingresos + $total_gastos;
                
                $sql_detalle = "INSERT INTO arqueo_efectivo_detalle SET
                                arqueo_id = '$arqueo_id',
                                billetes = '$billetes',
                                monedas = '$monedas',
                                pasaje = '$pasaje',
                                combustible = '$combustible',
                                gastos = '$gastos',
                                menu = '$menu',
                                otro = '$otro',
                                otro_descripcion = '$otro_desc',
                                total_ingresos = '$total_ingresos',
                                total_gastos = '$total_gastos',
                                total_efectivo_real = '$total_efectivo_real'";
                
                if (!$this->conexion->query($sql_detalle)) {
                    throw new Exception("Error al guardar detalle de efectivo: " . $this->conexion->error);
                }
            }
            
            // Guardar pagos digitales
            if (!empty($pagos_digitales)) {
                foreach ($pagos_digitales as $pago) {
                    $cliente = $this->conexion->real_escape_string($pago['cliente_nombre']);
                    $tipo = $this->conexion->real_escape_string($pago['tipo_pago']);
                    $operacion = $this->conexion->real_escape_string($pago['numero_operacion']);
                    $monto = floatval($pago['monto']);
                    
                    $sql_pago = "INSERT INTO arqueo_pagos_digitales SET
                                arqueo_id = '$arqueo_id',
                                cliente_nombre = '$cliente',
                                tipo_pago = '$tipo',
                                numero_operacion = '$operacion',
                                monto = '$monto'";
                    
                    if (!$this->conexion->query($sql_pago)) {
                        throw new Exception("Error al guardar pago digital: " . $this->conexion->error);
                    }
                }
            }
            
            // Confirmar transacción
            $this->conexion->commit();
            
            $respuesta["res"] = true;
            $respuesta["arqueo_id"] = $arqueo_id;
            $respuesta["mensaje"] = "Arqueo de $vendedor del día $fecha guardado correctamente";
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $this->conexion->rollback();
            $respuesta["mensaje"] = $e->getMessage();
        }
        
        return json_encode($respuesta);
    }
    
    // Obtener arqueos guardados
    public function obtenerArqueosGuardados()
    {
        $sql = "SELECT * FROM arqueos_diarios 
                WHERE id_empresa = '{$_SESSION['id_empresa']}' 
                AND sucursal = '{$_SESSION['sucursal']}'
                ORDER BY fecha_arqueo DESC, arqueo_id DESC";
        
        $result = $this->conexion->query($sql);
        $arqueos = [];
        
        while ($row = $result->fetch_assoc()) {
            $arqueos[] = $row;
        }
        
        return json_encode($arqueos);
    }
    
    // Obtener un arqueo específico por ID
    public function obtenerArqueoPorId()
    {
        $arqueo_id = $_POST['arqueo_id'];
        
        $sql = "SELECT * FROM arqueos_diarios WHERE arqueo_id = '$arqueo_id'";
        $result = $this->conexion->query($sql);
        
        if ($row = $result->fetch_assoc()) {
            // Obtener detalle de efectivo
            $sql_detalle = "SELECT * FROM arqueo_efectivo_detalle WHERE arqueo_id = '$arqueo_id'";
            $result_detalle = $this->conexion->query($sql_detalle);
            $row['detalle_efectivo'] = $result_detalle->fetch_assoc();
            
            // Obtener pagos digitales
            $sql_pagos = "SELECT * FROM arqueo_pagos_digitales WHERE arqueo_id = '$arqueo_id' ORDER BY pago_digital_id";
            $result_pagos = $this->conexion->query($sql_pagos);
            $pagos = [];
            while ($pago = $result_pagos->fetch_assoc()) {
                $pagos[] = $pago;
            }
            $row['pagos_digitales'] = $pagos;
            
            return json_encode($row);
        }
        
        return json_encode(["error" => "Arqueo no encontrado"]);
    }
    
    // Actualizar arqueo existente
    public function actualizarArqueo()
    {
        $respuesta = ["res" => false];
        
        $arqueo_id = $_POST['arqueo_id'];
        $ingresos_efectivo = $_POST['ingresos_efectivo'] ?? 0;
        $ingresos_bancos = $_POST['ingresos_bancos'] ?? 0;
        $egresos_efectivo = $_POST['egresos_efectivo'] ?? 0;
        $egresos_bancos = $_POST['egresos_bancos'] ?? 0;
        $diferencia_efectivo = $_POST['diferencia_efectivo'] ?? 0;
        $diferencia_bancos = $_POST['diferencia_bancos'] ?? 0;
        $cuadra_efectivo = isset($_POST['cuadra_efectivo']) && $_POST['cuadra_efectivo'] ? 1 : 0;
        $cuadra_bancos = isset($_POST['cuadra_bancos']) && $_POST['cuadra_bancos'] ? 1 : 0;
        
        // Nuevos datos de detalle de efectivo
        $detalle_efectivo = isset($_POST['detalle_efectivo']) ? json_decode($_POST['detalle_efectivo'], true) : null;
        
        // Nuevos datos de pagos digitales
        $pagos_digitales = isset($_POST['pagos_digitales']) ? json_decode($_POST['pagos_digitales'], true) : [];
        
        // Iniciar transacción
        $this->conexion->begin_transaction();
        
        try {
            // Actualizar arqueo principal
            $sql = "UPDATE arqueos_diarios SET
                    ingresos_efectivo = '$ingresos_efectivo',
                    ingresos_bancos = '$ingresos_bancos',
                    egresos_efectivo = '$egresos_efectivo',
                    egresos_bancos = '$egresos_bancos',
                    diferencia_efectivo = '$diferencia_efectivo',
                    diferencia_bancos = '$diferencia_bancos',
                    cuadra_efectivo = '$cuadra_efectivo',
                    cuadra_bancos = '$cuadra_bancos'
                    WHERE arqueo_id = '$arqueo_id'";
            
            if (!$this->conexion->query($sql)) {
                throw new Exception("Error al actualizar arqueo: " . $this->conexion->error);
            }
            
            // Actualizar o insertar detalle de efectivo
            if ($detalle_efectivo) {
                $billetes = floatval($detalle_efectivo['billetes'] ?? 0);
                $monedas = floatval($detalle_efectivo['monedas'] ?? 0);
                $pasaje = floatval($detalle_efectivo['pasaje'] ?? 0);
                $combustible = floatval($detalle_efectivo['combustible'] ?? 0);
                $gastos = floatval($detalle_efectivo['gastos'] ?? 0);
                $menu = floatval($detalle_efectivo['menu'] ?? 0);
                $otro = floatval($detalle_efectivo['otro'] ?? 0);
                $otro_desc = $this->conexion->real_escape_string($detalle_efectivo['otro_descripcion'] ?? '');
                
                $total_ingresos = $billetes + $monedas;
                $total_gastos = $pasaje + $combustible + $gastos + $menu + $otro;
                $total_efectivo_real = $total_ingresos + $total_gastos;
                
                // Verificar si ya existe detalle
                $check = $this->conexion->query("SELECT detalle_id FROM arqueo_efectivo_detalle WHERE arqueo_id = '$arqueo_id'");
                
                if ($check->num_rows > 0) {
                    // Actualizar
                    $sql_detalle = "UPDATE arqueo_efectivo_detalle SET
                                    billetes = '$billetes',
                                    monedas = '$monedas',
                                    pasaje = '$pasaje',
                                    combustible = '$combustible',
                                    gastos = '$gastos',
                                    menu = '$menu',
                                    otro = '$otro',
                                    otro_descripcion = '$otro_desc',
                                    total_ingresos = '$total_ingresos',
                                    total_gastos = '$total_gastos',
                                    total_efectivo_real = '$total_efectivo_real'
                                    WHERE arqueo_id = '$arqueo_id'";
                } else {
                    // Insertar
                    $sql_detalle = "INSERT INTO arqueo_efectivo_detalle SET
                                    arqueo_id = '$arqueo_id',
                                    billetes = '$billetes',
                                    monedas = '$monedas',
                                    pasaje = '$pasaje',
                                    combustible = '$combustible',
                                    gastos = '$gastos',
                                    menu = '$menu',
                                    otro = '$otro',
                                    otro_descripcion = '$otro_desc',
                                    total_ingresos = '$total_ingresos',
                                    total_gastos = '$total_gastos',
                                    total_efectivo_real = '$total_efectivo_real'";
                }
                
                if (!$this->conexion->query($sql_detalle)) {
                    throw new Exception("Error al actualizar detalle de efectivo: " . $this->conexion->error);
                }
            }
            
            // Eliminar pagos digitales anteriores y agregar los nuevos
            $this->conexion->query("DELETE FROM arqueo_pagos_digitales WHERE arqueo_id = '$arqueo_id'");
            
            if (!empty($pagos_digitales)) {
                foreach ($pagos_digitales as $pago) {
                    $cliente = $this->conexion->real_escape_string($pago['cliente_nombre']);
                    $tipo = $this->conexion->real_escape_string($pago['tipo_pago']);
                    $operacion = $this->conexion->real_escape_string($pago['numero_operacion']);
                    $monto = floatval($pago['monto']);
                    
                    $sql_pago = "INSERT INTO arqueo_pagos_digitales SET
                                arqueo_id = '$arqueo_id',
                                cliente_nombre = '$cliente',
                                tipo_pago = '$tipo',
                                numero_operacion = '$operacion',
                                monto = '$monto'";
                    
                    if (!$this->conexion->query($sql_pago)) {
                        throw new Exception("Error al guardar pago digital: " . $this->conexion->error);
                    }
                }
            }
            
            // Confirmar transacción
            $this->conexion->commit();
            
            $respuesta["res"] = true;
            $respuesta["mensaje"] = "Arqueo actualizado correctamente";
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $this->conexion->rollback();
            $respuesta["mensaje"] = $e->getMessage();
        }
        
        return json_encode($respuesta);
    }
    
    // Buscar clientes para autocomplete
    public function buscarClientes()
    {
        $termino = $_POST['termino'] ?? '';
        $termino = $this->conexion->real_escape_string($termino);
        
        $sql = "SELECT id_cliente, datos, documento 
                FROM clientes 
                WHERE id_empresa = '{$_SESSION['id_empresa']}'
                AND (datos LIKE '%$termino%' OR documento LIKE '%$termino%')
                ORDER BY datos
                LIMIT 20";
        
        $result = $this->conexion->query($sql);
        $clientes = [];
        
        while ($row = $result->fetch_assoc()) {
            $clientes[] = [
                'id' => $row['id_cliente'],
                'nombre' => $row['datos'],
                'documento' => $row['documento']
            ];
        }
        
        return json_encode($clientes);
    }
}

