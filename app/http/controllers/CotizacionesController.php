<?php

class CotizacionesController extends Controller
{
    private $conexion;
    private $conectar;

    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();
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

    private function productoCotizacionKey($idProducto, $medida, $presentaCnt)
    {
        return $idProducto . '|' . trim($medida) . '|' . number_format((float)$presentaCnt, 2, '.', '');
    }

    private function getProductosCotizacionActuales($cotiId)
    {
        $productos = [];
        $sql = "SELECT id_producto, medida, presenta_cnt, SUM(cantidad) AS cantidad
            FROM productos_cotis
            WHERE id_coti = '$cotiId'
            GROUP BY id_producto, medida, presenta_cnt";
        $result = $this->conexion->query($sql);

        if ($result) {
            foreach ($result as $prod) {
                $key = $this->productoCotizacionKey($prod['id_producto'], $prod['medida'], $prod['presenta_cnt']);
                $productos[$key] = [
                    'cantidad' => (float)$prod['cantidad'],
                ];
            }
        }

        return $productos;
    }

    private function actualizarPrecioProductoCotizacion($cotiId, $prod)
    {
        $presentaCntInt = (int)round((float)$prod['presentacionCnt']);
        $medidaEsc = $this->conexion->real_escape_string(trim($prod['medida']));
        $this->conexion->query("UPDATE productos_cotis SET
            precio='{$prod['precioVenta']}',
            presenta='{$prod['presentacion']}',
            costo='{$prod['costo']}'
            WHERE id_coti='$cotiId'
            AND id_producto='{$prod['productoid']}'
            AND medida='$medidaEsc'
            AND presenta_cnt='$presentaCntInt'");
    }

    private function reducirProductoCotizacion($cotiId, $prod, $cantidadReducir)
    {
        $presentaCntInt = (int)round((float)$prod['presentacionCnt']);
        $medidaEsc = $this->conexion->real_escape_string(trim($prod['medida']));

        $sql = "SELECT prod_coti_id, cantidad FROM productos_cotis
            WHERE id_coti='$cotiId'
            AND id_producto='{$prod['productoid']}'
            AND medida='$medidaEsc'
            AND presenta_cnt='$presentaCntInt'
            ORDER BY COALESCE(fecha_registro, '1970-01-01') DESC, prod_coti_id DESC";
        $result = $this->conexion->query($sql);

        if ($result) {
            foreach ($result as $row) {
                if ($cantidadReducir <= 0) break;
                $rowId = $row['prod_coti_id'];
                $rowQty = (float)$row['cantidad'];
                if ($rowQty <= $cantidadReducir) {
                    $this->conexion->query("DELETE FROM productos_cotis WHERE prod_coti_id='$rowId'");
                    $cantidadReducir -= $rowQty;
                } else {
                    $nuevaCantidad = $rowQty - $cantidadReducir;
                    $this->conexion->query("UPDATE productos_cotis SET cantidad='$nuevaCantidad' WHERE prod_coti_id='$rowId'");
                    $cantidadReducir = 0;
                }
            }
        }
    }

    private function normalizarProductosCotizacion($productos)
    {
        $normalizados = [];

        foreach ($productos as $prod) {
            $key = $this->productoCotizacionKey($prod['productoid'], $prod['medida'], $prod['presentacionCnt']);

            if (!isset($normalizados[$key])) {
                $normalizados[$key] = $prod;
                $normalizados[$key]['cantidad'] = 0;
            }

            $normalizados[$key]['cantidad'] += (float)$prod['cantidad'];
        }

        return $normalizados;
    }

    private function insertarProductoCotizacion($cotiId, $prod, $cantidad, $fechaRegistro)
    {
        if ($cantidad <= 0) {
            return;
        }

        $sql = "insert into productos_cotis set id_coti='$cotiId',
              id_producto='{$prod['productoid']}',
              cantidad='$cantidad',
              precio='{$prod['precioVenta']}',
              medida='{$prod['medida']}',
              presenta_cnt='{$prod['presentacionCnt']}',
              presenta='{$prod['presentacion']}',
              costo='{$prod['costo']}',
              fecha_registro='$fechaRegistro'";
        $this->conexion->query($sql);
    }
    public function eliminarCotizacion()
    {
        // Primero verificar que el pedido existe
        $sql = "SELECT cotizacion_id FROM cotizaciones WHERE cotizacion_id = '{$_POST['cod']}'";
        $result = $this->conexion->query($sql);

        if (!$result || $result->num_rows == 0) {
            error_log("ERROR ELIMINAR - Pedido {$_POST['cod']} no existe");
            echo json_encode(["res" => false, "msg" => "El pedido no existe"]);
            return;
        }

        // Eliminar productos relacionados primero
        $sql = "DELETE FROM productos_cotis WHERE id_coti = '{$_POST['cod']}'";
        $this->conexion->query($sql);

        // Eliminar cuotas relacionadas
        $sql = "DELETE FROM cuotas_cotizacion WHERE id_coti = '{$_POST['cod']}'";
        $this->conexion->query($sql);

        // Eliminar la cotización
        $sql = "DELETE FROM cotizaciones WHERE cotizacion_id = '{$_POST['cod']}'";
        $resultado = $this->conexion->query($sql);

        if (!$resultado) {
            error_log("ERROR ELIMINAR - Error SQL: " . $this->conexion->error);
            echo json_encode(["res" => false, "msg" => "Error en la base de datos: " . $this->conexion->error]);
            return;
        }

        if ($this->conexion->affected_rows > 0) {
            error_log("ELIMINAR EXITOSO - Pedido {$_POST['cod']} eliminado físicamente de la BD");
            echo json_encode(["res" => true, "msg" => "Pedido eliminado correctamente"]);
        } else {
            error_log("ERROR ELIMINAR - No se afectaron filas. Pedido: {$_POST['cod']}");
            echo json_encode(["res" => false, "msg" => "No se pudo eliminar el pedido"]);
        }
    }

    public function actualizar()
    {
        $respuesta = ["res" => false];

        // VALIDACIÓN: Cliente obligatorio
        $num_doc = isset($_POST['num_doc']) ? trim($_POST['num_doc']) : '';
        $nom_cli = isset($_POST['nom_cli']) ? trim($_POST['nom_cli']) : '';

        if (empty($num_doc) || empty($nom_cli)) {
            $respuesta['msg'] = 'El cliente es obligatorio. Debe ingresar documento y nombre del cliente.';
            return json_encode($respuesta);
        }

        /*  return json_encode($_POST);
        return; */
        $sql = "SELECT * from  clientes where documento ='{$_POST['num_doc']}'";
        $idCli = '';

        if ($rowCl = $this->conexion->query($sql)->fetch_assoc()) {
            $idCli = $rowCl['id_cliente'];
        } else {
            $sql = "insert into clientes set documento='{$_POST['num_doc']}',
            datos='{$_POST['nom_cli']}',
            direccion='{$_POST['dir_cli']}',
            direccion2='{$_POST['dir2_cli']}',
            id_empresa='{$_SESSION['id_empresa']}'";
            $this->conexion->query($sql);
            $idCli = $this->conexion->insert_id;
        }

        // CALCULAR EL TOTAL EN EL BACKEND PARA VALIDAR
        $productos = json_decode($_POST['listaPro'], true);
        $totalCalculado = 0;
        foreach ($productos as $prod) {
            $totalCalculado += floatval($prod['precioVenta']) * floatval($prod['cantidad']);
        }

        // VALIDAR que el total del frontend coincida con el calculado
        $totalFrontend = floatval($_POST['total']);
        $diferencia = abs($totalCalculado - $totalFrontend);

        // Si la diferencia es mayor a 0.01 (1 centavo), hay un error
        if ($diferencia > 0.01) {
            $respuesta['msg'] = "Error: El total no coincide. Calculado: " . number_format($totalCalculado, 2) . ", Recibido: " . number_format($totalFrontend, 2);
            return json_encode($respuesta);
        }

        // Usar el total calculado en el backend (más confiable)
        $totalFinal = number_format($totalCalculado, 2, '.', '');

        $sql = "update cotizaciones set id_tipo_pago='{$_POST['tipo_pago']}',
                fecha='{$_POST['fecha']}',
                dias_pagos='{$_POST['dias_pago']}',
                direccion='{$_POST['dir_pos']}',
                id_cliente='$idCli',
                observacion='{$_POST['observacion']}',
                usar_precio='{$_POST['usar_precio']}',
                total='$totalFinal' where cotizacion_id='{$_POST['cotiId']}'";

        if ($this->conexion->query($sql)) {
            $respuesta['res'] = true;

            $cuotas = json_decode($_POST['dias_lista'], true);

            // --- CORRECCIÓN: PROTEGER PAGOS AL EDITAR ---
            // Solo eliminamos las cuotas que NO han sido pagadas todavía (estado='0')
            // Esto evita que al editar un pedido se borren los cobros ya realizados.
            // Las cuotas con estado='1' se mantienen con su información de pago intacta.
            $sql = "DELETE FROM cuotas_cotizacion WHERE id_coti='{$_POST['cotiId']}' AND estado='0'";
            $this->conexion->query($sql);

            // Insertar nuevas cuotas (solo las válidas)
            if (!empty($cuotas) && is_array($cuotas)) {
                foreach ($cuotas as $cuota) {
                    $monto = isset($cuota['monto']) ? floatval($cuota['monto']) : 0;
                    $fecha = isset($cuota['fecha']) ? $cuota['fecha'] : '0000-00-00';

                    // Solo insertar cuotas con monto mayor a 0 y fecha válida
                    if ($monto > 0 && $fecha != '0000-00-00' && !empty($fecha)) {
                        $sql = "INSERT INTO cuotas_cotizacion SET 
                                id_coti='{$_POST['cotiId']}',
                                monto='$monto',
                                fecha='$fecha',
                                estado='0'";
                        $this->conexion->query($sql);
                    }
                }
            }

            date_default_timezone_set('America/Lima');
            $fechaMovimiento = date('Y-m-d H:i:s');
            $cotiId = $_POST['cotiId'];
            $productosAnteriores = $this->getProductosCotizacionActuales($cotiId);
            $productosNormalizados = $this->normalizarProductosCotizacion($productos);

            // Eliminar productos que ya no están en el pedido
            foreach ($productosAnteriores as $key => $anterior) {
                if (!isset($productosNormalizados[$key])) {
                    [$idProducto, $medida, $presentaCnt] = explode('|', $key, 3);
                    $presentaCntInt = (int)round((float)$presentaCnt);
                    $medidaEsc = $this->conexion->real_escape_string(trim($medida));
                    $this->conexion->query("DELETE FROM productos_cotis WHERE id_coti='$cotiId' AND id_producto='$idProducto' AND medida='$medidaEsc' AND presenta_cnt='$presentaCntInt'");
                }
            }

            foreach ($productosNormalizados as $key => $prod) {
                $cantidadNueva = (float)$prod['cantidad'];
                $cantidadAnterior = isset($productosAnteriores[$key]) ? (float)$productosAnteriores[$key]['cantidad'] : 0;

                if ($cantidadAnterior == 0) {
                    // Producto nuevo: insertar con timestamp actual
                    $this->insertarProductoCotizacion($cotiId, $prod, $cantidadNueva, $fechaMovimiento);
                } elseif ($cantidadNueva > $cantidadAnterior) {
                    // Aumento: insertar solo el delta con timestamp actual, preservar filas anteriores
                    $this->actualizarPrecioProductoCotizacion($cotiId, $prod);
                    $this->insertarProductoCotizacion($cotiId, $prod, $cantidadNueva - $cantidadAnterior, $fechaMovimiento);
                } elseif ($cantidadNueva < $cantidadAnterior) {
                    // Reducción: eliminar de las filas más nuevas primero
                    $this->reducirProductoCotizacion($cotiId, $prod, $cantidadAnterior - $cantidadNueva);
                    $this->actualizarPrecioProductoCotizacion($cotiId, $prod);
                } else {
                    // Misma cantidad: solo actualizar precio
                    $this->actualizarPrecioProductoCotizacion($cotiId, $prod);
                }
            }
        }

        return json_encode($respuesta);
    }

    public function getInformacion()
    {
        $cotizacion = $_POST['coti'];
        
        // Debug: Verificar qué cotización se está buscando
        error_log("DEBUG: Buscando cotización ID: " . $cotizacion);
        
        $sql = "SELECT * FROM cotizaciones where cotizacion_id='$cotizacion'";
        error_log("DEBUG: SQL sin filtros: " . $sql);
        
        $result = $this->conexion->query($sql);
        
        if (!$result) {
            error_log("DEBUG: Error en la consulta: " . $this->conexion->error);
            return json_encode(["error" => "Error en consulta SQL"]);
        }
        
        // Debug: También buscar por número si no se encuentra por ID
        if ($result->num_rows == 0) {
            error_log("DEBUG: Buscando por número en lugar de ID");
            $sql2 = "SELECT * FROM cotizaciones where numero='$cotizacion'";
            error_log("DEBUG: SQL por número: " . $sql2);
            $result2 = $this->conexion->query($sql2);
            
            if ($result2 && $result2->num_rows > 0) {
                error_log("DEBUG: ¡Encontrada por número!");
                $data = $result2->fetch_assoc();
                error_log("DEBUG: ID real de la cotización: " . $data['cotizacion_id']);
            } else {
                error_log("DEBUG: Tampoco se encontró por número");
                return json_encode([
                    "error" => true,
                    "message" => "Cotización ID/Número $cotizacion no encontrada en la base de datos",
                    "cuotas" => [],
                    "cliente_doc" => null,
                    "cliente_nom" => null,
                    "cliente_dir1" => null,
                    "cliente_dir2" => "",
                    "productos" => []
                ]);
            }
        } else {
            $data = $result->fetch_assoc();
        }
        
        // Debug: Mostrar todos los datos de la cotización
        error_log("DEBUG: Datos de cotización encontrados: " . json_encode($data));
        
        // Verificar específicamente el id_cliente
        if (!isset($data['id_cliente']) || empty($data['id_cliente'])) {
            error_log("DEBUG: id_cliente está vacío o no existe. Valor: " . (isset($data['id_cliente']) ? $data['id_cliente'] : 'NO EXISTE'));
            
            return json_encode([
                "error" => true,
                "message" => "La cotización existe pero no tiene cliente asignado",
                "cotizacion_data" => $data,
                "cuotas" => [],
                "cliente_doc" => null,
                "cliente_nom" => null,
                "cliente_dir1" => null,
                "cliente_dir2" => "",
                "productos" => []
            ]);
        }

        $sql = "select * from cuotas_cotizacion where id_coti = '$cotizacion'";
        $cuotasR = $this->conexion->query($sql);
        $data["cuotas"] = [];
        if ($cuotasR) {
            foreach ($cuotasR as $cuota) {
                $data["cuotas"][] = [
                    'cuotaid' => $cuota['cuota_coti_id'],
                    'fecha' => $cuota['fecha'],
                    'monto' => $cuota['monto'],
                    'estado' => $cuota['estado'],
                    'tipo_pago' => $cuota['tipo_pago']
                ];
            }
        }

        $sql = "select * from clientes where id_cliente = '{$data['id_cliente']}'";
        error_log("DEBUG: Buscando cliente con SQL: " . $sql);
        
        $clienteResult = $this->conexion->query($sql);
        
        if ($clienteResult && $clienteResult->num_rows > 0) {
            $clienteR = $clienteResult->fetch_assoc();
            $data["cliente_doc"] = $clienteR['documento'];
            $data["cliente_nom"] = $clienteR['datos'];
            $data["cliente_dir1"] = $clienteR['direccion'];
            $data["cliente_dir2"] = isset($clienteR['direccion2']) ? $clienteR['direccion2'] : '';
        } else {
            error_log("DEBUG: No se encontró el cliente con ID: " . $data['id_cliente']);
            $data["cliente_doc"] = null;
            $data["cliente_nom"] = null;
            $data["cliente_dir1"] = null;
            $data["cliente_dir2"] = "";
        }

        $data["productos"] = [];
        $sql = "SELECT p.cnt_presenta, pc.medida, pc.presenta, pc.presenta_cnt, p.codigo, pc.id_producto,
            SUM(pc.cantidad) AS cantidad,
            p.descripcion, p.codsunat, p.precio, p.precio2, p.precio3, p.costo,
            MAX(pc.precio) AS precioVenta, p.precio4, p.precio_unidad
        FROM productos_cotis pc
        JOIN productos p ON p.id_producto = pc.id_producto
        WHERE pc.id_coti = '{$data['cotizacion_id']}'
        GROUP BY pc.id_producto, pc.medida, pc.presenta_cnt, pc.presenta,
            p.cnt_presenta, p.codigo, p.descripcion, p.codsunat,
            p.precio, p.precio2, p.precio3, p.costo, p.precio4, p.precio_unidad";

        error_log("DEBUG: SQL productos: " . $sql);
        $productosR = $this->conexion->query($sql);
        
        if (!$productosR) {
            error_log("DEBUG: Error en consulta productos: " . $this->conexion->error);
        } else {
            error_log("DEBUG: Productos encontrados: " . $productosR->num_rows);
        }

        if ($productosR) {
            foreach ($productosR as $pro) {
                // Asegurar que presenta_cnt nunca sea 0 para evitar división por cero
                $presenta_cnt = (float)$pro['presenta_cnt'];
                if ($presenta_cnt <= 0) {
                    $presenta_cnt = 1; // Valor por defecto
                }
                
                $data["productos"][] = [
                    "medida" => $pro['medida'],
                    "presenta" => $pro['presenta'],
                    "presentacion" => $pro['presenta'],
                    "presenta_cnt" => $presenta_cnt,
                    "presentacionCnt" => $presenta_cnt,
                    "codigo_pp" => $pro['codigo'],
                    "codigo" => $pro['codigo'], // Código del producto para mostrar en la tabla
                    "productoid" => $pro['id_producto'],
                    "descripcion" => $pro['descripcion'],
                    "nom_prod" => $pro['descripcion'],
                    "cantidad" => $pro['cantidad'],
                    "stock" => 0,
                    "precioVenta" => number_format((float)$pro['precioVenta'], 2, '.', ''),
                    "precio" => $pro['precio'],
                    "precio2" => $pro['precio2'],
                    "precio3" => $pro['precio3'],
                    "precio4" => $pro['precio4'],
                    "precio_unidad" => $pro['precio_unidad'],
                    "codsunat" => $pro['codsunat'],
                    "costo" => $pro['costo'],
                    "precio_usado" => isset($data['usar_precio']) ? $data['usar_precio'] : 1,
                ];
            }
        }

        return json_encode($data);
    }

    public function agregar()
    {
        $respuesta = ["res" => false];

        // VALIDACIÓN: Cliente obligatorio
        $num_doc = isset($_POST['num_doc']) ? trim($_POST['num_doc']) : '';
        $nom_cli = isset($_POST['nom_cli']) ? trim($_POST['nom_cli']) : '';

        if (empty($num_doc) || empty($nom_cli)) {
            $respuesta['msg'] = 'El cliente es obligatorio. Debe ingresar documento y nombre del cliente.';
            return json_encode($respuesta);
        }

        $sql = "SELECT * from  clientes where documento ='{$_POST['num_doc']}'";
        $idCli = '';

        if ($rowCl = $this->conexion->query($sql)->fetch_assoc()) {
            $idCli = $rowCl['id_cliente'];
        } else {

            $sql = "insert into clientes set documento='{$_POST['num_doc']}',
            datos='{$_POST['nom_cli']}',
            direccion='{$_POST['dir_cli']}',
            id_empresa='{$_SESSION['id_empresa']}'";
            $this->conexion->query($sql);
            $idCli = $this->conexion->insert_id;
        }

        // CALCULAR EL TOTAL EN EL BACKEND PARA VALIDAR
        $listaProd = json_decode($_POST['listaPro'], true);
        $totalCalculado = 0;
        foreach ($listaProd as $prod) {
            $totalCalculado += floatval($prod['precioVenta']) * floatval($prod['cantidad']);
        }

        // VALIDAR que el total del frontend coincida con el calculado
        $totalFrontend = floatval($_POST['total']);
        $diferencia = abs($totalCalculado - $totalFrontend);

        // Si la diferencia es mayor a 0.01 (1 centavo), hay un error
        if ($diferencia > 0.01) {
            $respuesta['msg'] = "Error: El total no coincide. Calculado: " . number_format($totalCalculado, 2) . ", Recibido: " . number_format($totalFrontend, 2);
            return json_encode($respuesta);
        }

        // Usar el total calculado en el backend (más confiable)
        $totalFinal = number_format($totalCalculado, 2, '.', '');

        $sql = "select * from cotizaciones where id_empresa='{$_SESSION['id_empresa']}' order by numero desc limit 1";

        $numCoti = 1;

        if ($roTemp = $this->conexion->query($sql)->fetch_assoc()) {
            $numCoti = $roTemp["numero"] + 1;
        }
        date_default_timezone_set('America/Lima');
        $fecha_registro = date('Y-m-d H:i:s');

        $sql = "insert into cotizaciones set id_tido='{$_POST['tipo_doc']}',
                moneda='{$_POST['moneda']}',
                id_tipo_pago='{$_POST['tipo_pago']}',
                fecha='{$_POST['fecha']}',
                dias_pagos='{$_POST['dias_pago']}',
                direccion='{$_POST['dir_pos']}',
                id_cliente='$idCli',
                total='$totalFinal',
                observacion='{$_POST['observacion']}',
                    numero='$numCoti',
                estado='0',
                usar_precio='{$_POST['usar_precio']}',
                sucursal='{$_SESSION['sucursal']}',
                id_usuario = '{$_SESSION['usuario_fac']}',
                id_empresa='{$_SESSION['id_empresa']}',
                fecha_registro='$fecha_registro'";  // Agregamos la fecha y hora actual


        if ($this->conexion->query($sql)) {
            $idCoti = $this->conexion->insert_id;

            $listaCuotas = json_decode($_POST['dias_lista'], true);

            // CORREGIDO: Crear cuotas con los datos reales de $listaCuotas
            if (!empty($listaCuotas) && is_array($listaCuotas)) {
                foreach ($listaCuotas as $cuota) {
                    $monto = isset($cuota['monto']) ? floatval($cuota['monto']) : 0;
                    $fecha = isset($cuota['fecha']) ? $cuota['fecha'] : '0000-00-00';

                    // Solo insertar cuotas con monto mayor a 0 y fecha válida
                    if ($monto > 0 && $fecha != '0000-00-00' && !empty($fecha)) {
                        $sql = "INSERT INTO cuotas_cotizacion SET 
                                id_coti='$idCoti',
                                monto='$monto',
                                fecha='$fecha',
                                estado='0'";
                        $this->conexion->query($sql);
                    }
                }
            } else {
                // Si no hay cuotas definidas, crear una cuota con el total
                $sql = "INSERT INTO cuotas_cotizacion SET 
                        id_coti='$idCoti',
                        monto='$totalFinal',
                        fecha='{$_POST['fecha']}',
                        estado='0'";
                $this->conexion->query($sql);
            }

            foreach ($this->normalizarProductosCotizacion($listaProd) as $prod) {
                $this->insertarProductoCotizacion($idCoti, $prod, (float)$prod['cantidad'], $fecha_registro);
            }


            $respuesta['res'] = true;
        }
        return json_encode($respuesta);
    }


    public function listar()
    {
        $sql = "select v.cotizacion_id,v.numero, v.fecha,v.moneda,v.cm_tc, 
       v.id_tido, c.documento, c.datos, v.total, v.estado
        from cotizaciones as v
            LEFT JOIN documentos_sunat ds on v.id_tido = ds.id_tido
            LEFT JOIN clientes c on v.id_cliente = c.id_cliente
            LEFT JOIN usuarios u on v.id_usuario = u.usuario_id
        where v.id_empresa = '12'  and v.sucursal ='{$_SESSION['sucursal']}' and v.estado<>'2'";

        // Aplicar filtros si existen
        if (isset($_POST['vendedor']) && !empty($_POST['vendedor'])) {
            $vendedor = $this->conexion->real_escape_string($_POST['vendedor']);
            $sql .= " AND u.usuario = '$vendedor'";
        }

        if (isset($_POST['fecha_inicio']) && !empty($_POST['fecha_inicio'])) {
            $fecha_inicio = $this->conexion->real_escape_string($_POST['fecha_inicio']);
            $sql .= " AND v.fecha >= '$fecha_inicio'";
        }

        if (isset($_POST['fecha_fin']) && !empty($_POST['fecha_fin'])) {
            $fecha_fin = $this->conexion->real_escape_string($_POST['fecha_fin']);
            $sql .= " AND v.fecha <= '$fecha_fin'";
        }

        $sql .= " order by v.fecha asc";

        $rest = $this->conexion->query($sql);
        $lista = [];
        foreach ($rest as $row) {
            $lista[] = $row;
        }
        return json_encode($lista);
    }

    public function getVendedores()
    {
        $sql = "SELECT usuario_id,nombres from usuarios where id_rol =  3";

        $rest = $this->conexion->query($sql);
        $lista = [];
        foreach ($rest as $row) {
            $lista[] = $row;
        }
        return json_encode($lista);
    }
}
