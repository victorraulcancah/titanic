<?php

/**
 * Kardex de almacén: registro unificado de todos los movimientos de stock.
 * Los flujos del sistema (venta, compra, anulación) registran automáticamente
 * con motivos fijos; los ajustes manuales (ingreso/salida) usan motivos manuales.
 */
class Kardex
{
    public $conectar;

    public function __construct($conexion = null)
    {
        // Permite reutilizar una conexión existente (para no abrir otra en los hooks)
        $this->conectar = $conexion ?: (new Conexion())->getConexion();
    }

    /**
     * Registra un movimiento en el kardex. NO modifica el stock (eso lo hace el
     * flujo que llama). Nunca lanza excepción: un fallo del kardex no debe romper
     * la venta/compra que lo origina.
     *
     * @param int    $idProducto
     * @param string $tipo        'i' ingreso | 'e' salida
     * @param string $motivo      nombre del motivo (ej. 'Venta', 'Compra')
     * @param float  $cantidad    cantidad en unidades (siempre positiva)
     * @param string $referencia  ej. 'venta:123', 'compra:45', 'manual'
     * @param string $observacion
     */
    public function registrar($idProducto, $tipo, $motivo, $cantidad, $referencia = '', $observacion = '')
    {
        try {
            $idProducto = intval($idProducto);
            $cantidad = abs(floatval($cantidad));
            if ($idProducto <= 0 || $cantidad <= 0) {
                return false;
            }
            $tipo = ($tipo === 'i') ? 'i' : 'e';
            $motivoEsc = $this->conectar->real_escape_string($motivo);
            $refEsc = $this->conectar->real_escape_string($referencia);
            $obsEsc = $this->conectar->real_escape_string($observacion);

            // Buscar (o crear) el motivo
            $res = $this->conectar->query("SELECT motivo_id FROM almacen_motivos WHERE nombre='$motivoEsc' AND tipo='$tipo' LIMIT 1");
            if ($res && $res->num_rows > 0) {
                $motivoId = intval($res->fetch_assoc()['motivo_id']);
            } else {
                $this->conectar->query("INSERT INTO almacen_motivos (nombre, tipo, fijo) VALUES ('$motivoEsc','$tipo','0')");
                $motivoId = intval($this->conectar->insert_id);
            }

            // Saldo resultante = stock actual del producto (el flujo ya lo actualizó);
            // saldo anterior = resultante menos/más la cantidad del movimiento.
            $saldo = 'NULL';
            $saldoAnterior = 'NULL';
            $resS = $this->conectar->query("SELECT cantidad FROM productos WHERE id_producto = $idProducto");
            if ($resS && $resS->num_rows > 0) {
                $saldo = floatval($resS->fetch_assoc()['cantidad']);
                $saldoAnterior = ($tipo === 'i') ? $saldo - $cantidad : $saldo + $cantidad;
            }

            $idUsuario = 'NULL';
            if (isset($_SESSION['usuario_fac'])) {
                $idUsuario = intval($_SESSION['usuario_fac']);
            } elseif (isset($_SESSION['usuario_id'])) {
                $idUsuario = intval($_SESSION['usuario_id']);
            }

            $sql = "INSERT INTO almacen_kardex (id_producto, tipo, motivo_id, cantidad, saldo_anterior, saldo_resultante, referencia, observacion, id_usuario)
                    VALUES ($idProducto, '$tipo', $motivoId, $cantidad, $saldoAnterior, $saldo, '$refEsc', '$obsEsc', $idUsuario)";
            return $this->conectar->query($sql);
        } catch (Throwable $e) {
            error_log('KARDEX ERROR: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Marca como ANULADOS (estado='0') los movimientos vigentes de una referencia.
     * El registro NUNCA se elimina: queda visible en el kardex como anulado, y el
     * flujo que anula (ej. anulación de venta) registra su propio movimiento inverso.
     */
    public function anularPorReferencia($referencia, $tipo = 'e')
    {
        try {
            $refEsc = $this->conectar->real_escape_string($referencia);
            $tipo = ($tipo === 'i') ? 'i' : 'e';
            return $this->conectar->query("UPDATE almacen_kardex SET estado='0' WHERE referencia='$refEsc' AND tipo='$tipo' AND estado='1'");
        } catch (Throwable $e) {
            error_log('KARDEX ERROR (anular): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ajuste MANUAL de stock (ingreso o salida de cuadre): SÍ modifica el stock
     * del producto y registra el movimiento.
     */
    public function ajusteManual($idProducto, $tipo, $motivoId, $cantidad, $observacion = '')
    {
        $idProducto = intval($idProducto);
        $motivoId = intval($motivoId);
        $cantidad = abs(floatval($cantidad));
        if ($idProducto <= 0 || $motivoId <= 0 || $cantidad <= 0) {
            return ['res' => false, 'msg' => 'Datos inválidos'];
        }

        // Validar motivo: debe existir, coincidir el tipo y NO ser fijo de sistema
        $res = $this->conectar->query("SELECT nombre, tipo, fijo FROM almacen_motivos WHERE motivo_id = $motivoId");
        if (!$res || $res->num_rows == 0) {
            return ['res' => false, 'msg' => 'Motivo no encontrado'];
        }
        $mot = $res->fetch_assoc();
        if ($mot['tipo'] !== $tipo) {
            return ['res' => false, 'msg' => 'El motivo no corresponde al tipo de movimiento'];
        }
        if ($mot['fijo'] === '1') {
            return ['res' => false, 'msg' => 'Este motivo es de sistema; no se puede usar manualmente'];
        }

        // Actualizar stock
        $signo = ($tipo === 'i') ? '+' : '-';
        $ok = $this->conectar->query("UPDATE productos SET cantidad = cantidad $signo $cantidad WHERE id_producto = $idProducto");
        if (!$ok) {
            return ['res' => false, 'msg' => 'No se pudo actualizar el stock: ' . $this->conectar->error];
        }

        $this->registrar($idProducto, $tipo, $mot['nombre'], $cantidad, 'manual', $observacion);
        return ['res' => true, 'msg' => 'Movimiento registrado'];
    }

    /**
     * ANULA un cuadre manual: revierte el stock, marca el registro como ANULADO
     * (nunca se borra) y registra el contramovimiento 'Anulacion de cuadre'.
     * Solo aplica a movimientos manuales (motivo fijo='0') vigentes.
     */
    public function anularCuadre($kardexId, $observacion = '')
    {
        $kardexId = intval($kardexId);
        if ($kardexId <= 0) {
            return ['res' => false, 'msg' => 'Movimiento inválido'];
        }
        $res = $this->conectar->query(
            "SELECT k.id_producto, k.tipo, k.cantidad, k.estado, m.fijo, m.nombre
             FROM almacen_kardex k JOIN almacen_motivos m ON m.motivo_id = k.motivo_id
             WHERE k.kardex_id = $kardexId"
        );
        if (!$res || $res->num_rows == 0) {
            return ['res' => false, 'msg' => 'Movimiento no encontrado'];
        }
        $mov = $res->fetch_assoc();
        if ($mov['fijo'] === '1') {
            return ['res' => false, 'msg' => 'Solo se pueden anular cuadres manuales; los movimientos de sistema no'];
        }
        if ($mov['estado'] !== '1') {
            return ['res' => false, 'msg' => 'Este cuadre ya está anulado'];
        }

        $cantidad = abs(floatval($mov['cantidad']));
        // Revertir stock: si fue salida, devolver; si fue ingreso, quitar
        $signo = ($mov['tipo'] === 'e') ? '+' : '-';
        $ok = $this->conectar->query("UPDATE productos SET cantidad = cantidad $signo $cantidad WHERE id_producto = '{$mov['id_producto']}'");
        if (!$ok) {
            return ['res' => false, 'msg' => 'No se pudo revertir el stock'];
        }
        $this->conectar->query("UPDATE almacen_kardex SET estado='0' WHERE kardex_id = $kardexId");

        // Contramovimiento (motivo fijo de sistema), referencia al cuadre anulado
        $tipoInverso = ($mov['tipo'] === 'e') ? 'i' : 'e';
        $obs = $observacion !== '' ? $observacion : "Anulación del cuadre #$kardexId ({$mov['nombre']})";
        $this->registrar($mov['id_producto'], $tipoInverso, 'Anulacion de cuadre', $cantidad, 'cuadre:' . $kardexId, $obs);

        return ['res' => true, 'msg' => 'Cuadre anulado y stock revertido', 'movimiento' => $mov];
    }

    /**
     * EDITA la cantidad de un cuadre manual: anula el original (revierte stock)
     * y registra un cuadre nuevo con la cantidad correcta, mismo tipo y motivo.
     */
    public function editarCuadre($kardexId, $nuevaCantidad, $observacion = '')
    {
        $kardexId = intval($kardexId);
        $nuevaCantidad = abs(floatval($nuevaCantidad));
        if ($nuevaCantidad <= 0) {
            return ['res' => false, 'msg' => 'La nueva cantidad debe ser mayor a 0'];
        }
        // Datos del original (para reutilizar tipo y motivo)
        $res = $this->conectar->query(
            "SELECT k.id_producto, k.tipo, k.motivo_id, m.fijo
             FROM almacen_kardex k JOIN almacen_motivos m ON m.motivo_id = k.motivo_id
             WHERE k.kardex_id = $kardexId"
        );
        if (!$res || $res->num_rows == 0) {
            return ['res' => false, 'msg' => 'Movimiento no encontrado'];
        }
        $orig = $res->fetch_assoc();
        if ($orig['fijo'] === '1') {
            return ['res' => false, 'msg' => 'Solo se pueden editar cuadres manuales'];
        }

        $anulado = $this->anularCuadre($kardexId, "Anulado por corrección de cantidad (cuadre #$kardexId)");
        if (!$anulado['res']) {
            return $anulado;
        }

        $obs = $observacion !== '' ? $observacion : "Corrección del cuadre #$kardexId";
        return $this->ajusteManual($orig['id_producto'], $orig['tipo'], $orig['motivo_id'], $nuevaCantidad, $obs);
    }

    /** Motivos disponibles para ajuste manual (excluye los fijos de sistema). */
    public function motivosManuales()
    {
        $res = $this->conectar->query("SELECT motivo_id, nombre, tipo FROM almacen_motivos WHERE fijo='0' AND activo='1' ORDER BY tipo, nombre");
        return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    }

    /** Todos los motivos activos (incluye fijos, para el CRUD — los fijos se muestran bloqueados). */
    public function motivosTodos()
    {
        $res = $this->conectar->query("SELECT motivo_id, nombre, tipo, fijo FROM almacen_motivos WHERE activo='1' ORDER BY fijo DESC, tipo, nombre");
        return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    }

    /** Crea un motivo manual; si existía desactivado con el mismo nombre+tipo, lo reactiva. */
    public function crearMotivo($nombre, $tipo)
    {
        $nombre = trim($nombre);
        $tipo = ($tipo === 'i') ? 'i' : 'e';
        if ($nombre === '' || mb_strlen($nombre) > 100) {
            return ['res' => false, 'msg' => 'Nombre de motivo inválido'];
        }
        $nombreEsc = $this->conectar->real_escape_string($nombre);
        $ok = $this->conectar->query("INSERT INTO almacen_motivos (nombre, tipo, fijo, activo) VALUES ('$nombreEsc','$tipo','0','1')
                                      ON DUPLICATE KEY UPDATE activo='1'");
        if (!$ok) {
            return ['res' => false, 'msg' => 'No se pudo crear: ' . $this->conectar->error];
        }
        return ['res' => true, 'msg' => 'Motivo guardado'];
    }

    /** Edita el nombre de un motivo manual (los fijos de sistema no se tocan). */
    public function editarMotivo($motivoId, $nombre)
    {
        $motivoId = intval($motivoId);
        $nombre = trim($nombre);
        if ($motivoId <= 0 || $nombre === '' || mb_strlen($nombre) > 100) {
            return ['res' => false, 'msg' => 'Datos inválidos'];
        }
        $res = $this->conectar->query("SELECT fijo FROM almacen_motivos WHERE motivo_id = $motivoId");
        if (!$res || $res->num_rows == 0) {
            return ['res' => false, 'msg' => 'Motivo no encontrado'];
        }
        if ($res->fetch_assoc()['fijo'] === '1') {
            return ['res' => false, 'msg' => 'Los motivos de sistema no se pueden editar'];
        }
        $nombreEsc = $this->conectar->real_escape_string($nombre);
        $ok = $this->conectar->query("UPDATE almacen_motivos SET nombre='$nombreEsc' WHERE motivo_id = $motivoId");
        if (!$ok) {
            return ['res' => false, 'msg' => 'No se pudo editar: ' . $this->conectar->error];
        }
        return ['res' => true, 'msg' => 'Motivo actualizado'];
    }

    /** Elimina (desactiva) un motivo manual; el historial del kardex lo conserva. */
    public function eliminarMotivo($motivoId)
    {
        $motivoId = intval($motivoId);
        if ($motivoId <= 0) {
            return ['res' => false, 'msg' => 'Motivo inválido'];
        }
        $res = $this->conectar->query("SELECT fijo FROM almacen_motivos WHERE motivo_id = $motivoId");
        if (!$res || $res->num_rows == 0) {
            return ['res' => false, 'msg' => 'Motivo no encontrado'];
        }
        if ($res->fetch_assoc()['fijo'] === '1') {
            return ['res' => false, 'msg' => 'Los motivos de sistema no se pueden eliminar'];
        }
        $ok = $this->conectar->query("UPDATE almacen_motivos SET activo='0' WHERE motivo_id = $motivoId");
        if (!$ok) {
            return ['res' => false, 'msg' => 'No se pudo eliminar: ' . $this->conectar->error];
        }
        return ['res' => true, 'msg' => 'Motivo eliminado'];
    }

    /** Movimientos del kardex de un producto (con filtro opcional de fechas). */
    public function movimientos($idProducto, $fechaInicio = '', $fechaFin = '')
    {
        $idProducto = intval($idProducto);
        $where = "k.id_producto = $idProducto";
        if ($fechaInicio !== '') {
            $fi = $this->conectar->real_escape_string($fechaInicio);
            $where .= " AND k.fecha >= '$fi 00:00:00'";
        }
        if ($fechaFin !== '') {
            $ff = $this->conectar->real_escape_string($fechaFin);
            $where .= " AND k.fecha <= '$ff 23:59:59'";
        }
        $sql = "SELECT k.kardex_id, k.fecha, p.codigo, p.descripcion, k.tipo,
                       m.nombre AS motivo, k.cantidad, k.saldo_anterior, k.saldo_resultante,
                       k.referencia, k.observacion, k.estado, IFNULL(u.usuario, '-') AS usuario
                FROM almacen_kardex k
                JOIN almacen_motivos m ON m.motivo_id = k.motivo_id
                LEFT JOIN productos p ON p.id_producto = k.id_producto
                LEFT JOIN usuarios u ON u.usuario_id = k.id_usuario
                WHERE $where
                ORDER BY k.fecha DESC, k.kardex_id DESC";
        $res = $this->conectar->query($sql);
        return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    }

    /**
     * Últimos CUADRES de inventario: solo movimientos con motivo manual
     * (ajustes, pérdidas, préstamos...). Las ventas/compras van al Kardex.
     */
    public function ultimosCuadres($limite = 300)
    {
        $limite = intval($limite);
        $sql = "SELECT k.kardex_id, k.fecha, p.codigo, p.descripcion, k.tipo,
                       m.nombre AS motivo, m.fijo, k.cantidad, k.saldo_anterior, k.saldo_resultante,
                       k.referencia, k.observacion, k.estado, IFNULL(u.usuario, '-') AS usuario
                FROM almacen_kardex k
                JOIN almacen_motivos m ON m.motivo_id = k.motivo_id
                LEFT JOIN productos p ON p.id_producto = k.id_producto
                LEFT JOIN usuarios u ON u.usuario_id = k.id_usuario
                WHERE m.fijo = '0'
                ORDER BY k.kardex_id DESC
                LIMIT $limite";
        $res = $this->conectar->query($sql);
        return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    }

    /** Últimos movimientos de todo el almacén (para la vista general). */
    public function ultimosMovimientos($limite = 300)
    {
        $limite = intval($limite);
        $sql = "SELECT k.kardex_id, k.fecha, p.codigo, p.descripcion, k.tipo,
                       m.nombre AS motivo, k.cantidad, k.saldo_anterior, k.saldo_resultante,
                       k.referencia, k.observacion, k.estado, IFNULL(u.usuario, '-') AS usuario
                FROM almacen_kardex k
                JOIN almacen_motivos m ON m.motivo_id = k.motivo_id
                LEFT JOIN productos p ON p.id_producto = k.id_producto
                LEFT JOIN usuarios u ON u.usuario_id = k.id_usuario
                ORDER BY k.kardex_id DESC
                LIMIT $limite";
        $res = $this->conectar->query($sql);
        return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    }
}
