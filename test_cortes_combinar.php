<?php
/**
 * Test: Cortes en Consolidado Camión — CombinarReporteController
 *
 * Simula el flujo real de CotizacionesController:
 *   CREAR      → productos base con fecha_registro del primer corte
 *   ACTUALIZAR → segundo corte: agrega un PRODUCTO NUEVO + AUMENTO de existente
 *   ACTUALIZAR → tercer corte:  AUMENTO de otro producto existente
 *
 * Acciones:
 *   ?accion=crear               → inserta datos de prueba en BD
 *   ?accion=limpiar&coti_id=X  → elimina los datos de prueba
 *   (sin parámetro)             → muestra resultados
 *
 * Cliente: 583 — A-ALINA ALALUNA(SAN ANTONIO), sabado, ruta=3, camion=2, mercado=2
 * fechaInicio=2026-05-07 (viernes), fechaFin=2026-05-08 (sábado despacho)
 *
 * Flujo de rows que quedan en productos_cotis:
 *   pid=123 qty=2 fecha='2026-05-07 22:00:00'  ← CREAR — base primer corte (viernes noche)
 *   pid=383 qty=2 fecha='2026-05-07 22:00:00'  ← CREAR — base primer corte
 *   pid=342 qty=2 fecha='2026-05-07 22:00:00'  ← CREAR — base primer corte
 *   pid=321 qty=2 fecha='2026-05-08 09:30:00'  ← ACTUALIZAR segundo corte — PRODUCTO NUEVO
 *   pid=123 qty=3 fecha='2026-05-08 09:30:00'  ← ACTUALIZAR segundo corte — AUMENTO existente (2→5, delta=3)
 *   pid=383 qty=5 fecha='2026-05-08 16:00:00'  ← ACTUALIZAR tercer  corte — AUMENTO existente (2→7, delta=5)
 *
 * Resultados esperados:
 *   PRIMER CORTE : 123(2), 383(2), 342(2)   [viernes 08:00 → sábado 08:00]
 *   SEGUNDO CORTE: 321(2) [nuevo],  123(3) [solo delta]   [sábado 08:00 → 15:00]
 *   TERCER CORTE : 383(5) [solo delta]   [sábado 15:00 → 23:59]
 */

require_once 'utils/config.php';

$db = new mysqli(HOST_SS, USER_SS, PASSWORD_SS, DATABASE_SS);
$db->set_charset('utf8');
if ($db->connect_error) die('<b>Error conexión:</b> ' . $db->connect_error);

// ─── Parámetros ───────────────────────────────────────────────────────────────
$fechaInicio = '2026-05-07';   // viernes (día antes del despacho)
$fechaFin    = '2026-05-08';   // sábado  (día de despacho)
$id_empresa  = 12;
$sucursal    = 1;
$id_cliente  = 583;    // A-ALINA ALALUNA(SAN ANTONIO) — sabado, ruta=3, camion=2, mercado=2
$medida_test = 'Kilos';
$camion      = 2;
$diasVisita  = 'sabado';

/*
 * Rows a insertar — replica exactamente lo que CotizacionesController haría:
 *  - insertarProductoCotizacion($cotiId, $prod, $cantidad, $fechaRegistro)
 *  - Cada fila = una llamada a INSERT en productos_cotis
 */
$rows_insertar = [
    // ── CREAR cotización viernes noche: 3 productos base (primer corte) ────
    [123, 2, 25.00, 5, '2026-05-07 22:00:00'],  // base — viernes 22:00 (primer corte)
    [383, 2, 20.00, 5, '2026-05-07 22:00:00'],  // base — viernes 22:00 (primer corte)
    [342, 2, 52.50, 5, '2026-05-07 22:00:00'],  // base — viernes 22:00 (primer corte)
    // ── ACTUALIZAR sábado mañana: segundo corte (08:00–15:00) ──────────────
    [321, 2, 25.00, 5, '2026-05-08 09:30:00'],  // PRODUCTO NUEVO
    [123, 3, 25.00, 5, '2026-05-08 09:30:00'],  // AUMENTO existente (2→5, delta=3)
    // ── ACTUALIZAR sábado tarde: tercer corte (15:00–23:59) ─────────────────
    [383, 5, 20.00, 5, '2026-05-08 16:00:00'],  // AUMENTO existente (2→7, delta=5)
];

// Resultados esperados por corte [pids_esperados, cantidades_esperadas]
$esperado = [
    'primer_corte'  => ['pids' => ['123','383','342'], 'qtys' => ['123'=>2,'383'=>2,'342'=>2]],
    'segundo_corte' => ['pids' => ['321','123'],        'qtys' => ['321'=>2,'123'=>3]],
    'tercer_corte'  => ['pids' => ['383'],              'qtys' => ['383'=>5]],
];

// ─── Filtro de corte (replica de CombinarReporteController::buildFiltroHorarioProductoCorte)
function corte_where($fechaInicio, $fechaFin, $horario) {
    $primerCorteInicio = (date('N', strtotime($fechaInicio)) == 7)
        ? date('Y-m-d', strtotime($fechaInicio . ' -1 day')) : $fechaInicio;
    switch ($horario) {
        case 'primer_corte':
            return "pc.fecha_registro >= '$primerCorteInicio 08:00:00' AND pc.fecha_registro < '$fechaFin 08:00:00'";
        case 'segundo_corte':
            return "pc.fecha_registro >= '$fechaFin 08:00:00' AND pc.fecha_registro < '$fechaFin 15:00:00'";
        case 'tercer_corte':
            return "pc.fecha_registro >= '$fechaFin 15:00:00' AND pc.fecha_registro <= '$fechaFin 23:59:59'";
    }
    return '1=1';
}

// ─── CSS base ─────────────────────────────────────────────────────────────────
$html_head = "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'>
<title>Test Cortes CombinarReporte</title>
<style>
  body { font-family:Arial,sans-serif; padding:24px; font-size:14px; color:#222; }
  h1   { color:#1a252f; border-bottom:3px solid #8e44ad; padding-bottom:6px; }
  h2   { color:#2c3e50; margin-top:28px; border-left:4px solid #8e44ad; padding-left:8px; }
  h3   { margin-top:14px; font-size:14px; }
  .box { background:#f5eef8; border:1px solid #c39bd3; padding:10px 16px;
         border-radius:4px; font-size:13px; margin:8px 0 16px; }
  .ok  { color:#27ae60; font-weight:bold; }
  .err { color:#c0392b; font-weight:bold; }
  .btn { display:inline-block; padding:8px 18px; border-radius:4px; font-size:14px;
         font-weight:bold; text-decoration:none; cursor:pointer; border:none; margin:3px; }
  .btn-green { background:#27ae60; color:#fff; }
  .btn-red   { background:#c0392b; color:#fff; }
  .btn-blue  { background:#2980b9; color:#fff; }
  table { border-collapse:collapse; font-size:13px; margin-top:4px; }
  th,td { border:1px solid #ccc; padding:4px 10px; }
  .head { background:#2c3e50; color:#fff; }
</style></head><body>";

$accion  = $_GET['accion']  ?? '';
$coti_id = (int)($_GET['coti_id'] ?? 0);

// ══════════════════════════════════════════════════════════════════════════════
// ACCIÓN: LIMPIAR
// ══════════════════════════════════════════════════════════════════════════════
if ($accion === 'limpiar' && $coti_id > 0) {
    echo $html_head . "<h1>Limpiar datos de prueba</h1>";

    $check = $db->query("SELECT cotizacion_id FROM cotizaciones
                         WHERE cotizacion_id=$coti_id AND observacion='TEST_CORTES_COMBINAR'");
    if (!$check || $check->num_rows == 0) {
        die("<div class='box' style='background:#f8d7da;color:#c0392b'>
            Error: cotización #$coti_id no es de prueba TEST_CORTES_COMBINAR.</div></body></html>");
    }

    $db->query("DELETE FROM productos_cotis WHERE id_coti=$coti_id");
    $db->query("DELETE FROM cuotas_cotizacion WHERE id_coti=$coti_id");
    $db->query("DELETE FROM cotizaciones WHERE cotizacion_id=$coti_id");

    echo "<div class='box' style='background:#d4edda;border-color:#27ae60'>
        ✔ Cotización <b>#$coti_id</b> y sus productos eliminados.</div>";
    echo "<a href='test_cortes_combinar.php?accion=crear' class='btn btn-green'>Crear nuevos datos</a>";
    echo "</body></html>";
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// ACCIÓN: CREAR
// ══════════════════════════════════════════════════════════════════════════════
if ($accion === 'crear') {
    echo $html_head . "<h1>Crear datos de prueba</h1>";

    // Cotización: fecha = sábado (día despacho = $fechaFin), fecha_registro = viernes noche (primer corte)
    if (!$db->query("INSERT INTO cotizaciones SET
            id_tido=6, id_cliente=$id_cliente, id_empresa=$id_empresa, sucursal=$sucursal,
            fecha='$fechaFin', total=1000.00, estado=0,
            id_tipo_pago=1, id_usuario=1,
            observacion='TEST_CORTES_COMBINAR',
            fecha_registro='2026-05-07 22:00:00'")) {
        echo "<div class='box' style='background:#f8d7da'>Error: " . $db->error . "</div></body></html>";
        exit;
    }
    $coti_id = $db->insert_id;

    // Insertar rows exactamente como lo haría CotizacionesController::insertarProductoCotizacion
    foreach ($rows_insertar as [$pid, $qty, $precio, $pcnt, $fecha]) {
        if (!$db->query("INSERT INTO productos_cotis SET
                id_coti=$coti_id, id_producto=$pid, cantidad=$qty, precio=$precio,
                medida='Kilos', presenta_cnt=$pcnt, presenta='1', costo=0,
                fecha_registro='$fecha'")) {
            echo "<div class='box' style='background:#f8d7da'>Error al insertar producto $pid: " . $db->error . "</div>";
        }
    }

    echo "<div class='box' style='background:#d4edda;border-color:#27ae60'>
        ✔ Cotización de prueba creada: ID <b>#$coti_id</b><br>
        Cliente: <b>#$id_cliente A-ALINA ALALUNA (SAN ANTONIO)</b> — sabado, ruta=3, camion=2<br><br>
        <b>Flujo simulado:</b><br>
        — CREAR (viernes 22:00 = primer corte): productos <b>123</b>, <b>383</b>, <b>342</b> qty=2 c/u<br>
        — ACTUALIZAR sábado 09:30 (segundo corte): <b>321 NUEVO</b> qty=2 + <b>aumento 123</b> 2→5 (delta=3)<br>
        — ACTUALIZAR sábado 16:00 (tercer corte): <b>aumento 383</b> 2→7 (delta=5)
    </div>
    <div class='box'>
        <b>Verifica en el reporte real:</b><br>
        <a class='btn btn-blue' style='margin:4px' href='r/pedido/reporte/camion/consolidado-total?camion=2&fechaSeleccionada=$fechaInicio&fechaFinSeleccionada=$fechaFin&diasVisita=sabado&ruta=&mercado=&medida=&tipo=&horario=primer_corte' target='_blank'>Primer Corte (reporte real)</a>
        <a class='btn btn-blue' style='margin:4px' href='r/pedido/reporte/camion/consolidado-total?camion=2&fechaSeleccionada=$fechaInicio&fechaFinSeleccionada=$fechaFin&diasVisita=sabado&ruta=&mercado=&medida=&tipo=&horario=segundo_corte' target='_blank'>Segundo Corte (reporte real)</a>
        <a class='btn btn-blue' style='margin:4px' href='r/pedido/reporte/camion/consolidado-total?camion=2&fechaSeleccionada=$fechaInicio&fechaFinSeleccionada=$fechaFin&diasVisita=sabado&ruta=&mercado=&medida=&tipo=&horario=tercer_corte' target='_blank'>Tercer Corte (reporte real)</a>
    </div>";

    echo "<p>
        <a href='test_cortes_combinar.php?coti_id=$coti_id' class='btn btn-blue'>Ver resultados</a>
        <a href='test_cortes_combinar.php?accion=limpiar&coti_id=$coti_id' class='btn btn-red'
           onclick=\"return confirm('¿Eliminar cotización #$coti_id?')\">Limpiar datos</a>
    </p>";
    echo "</body></html>";
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// VISTA: RESULTADOS
// ══════════════════════════════════════════════════════════════════════════════
echo $html_head;
echo "<h1>Test: Cortes en Consolidado Camión — CombinarReporteController</h1>";

if (!$coti_id) {
    $r = $db->query("SELECT cotizacion_id FROM cotizaciones
                     WHERE observacion='TEST_CORTES_COMBINAR' AND id_empresa=$id_empresa
                     ORDER BY cotizacion_id DESC LIMIT 1");
    if ($r && $row = $r->fetch_assoc()) $coti_id = $row['cotizacion_id'];
}

if (!$coti_id) {
    echo "<div class='box'>No hay datos de prueba en BD todavía.</div>";
    echo "<a href='test_cortes_combinar.php?accion=crear' class='btn btn-green'>Crear datos de prueba</a>";
    echo "</body></html>";
    exit;
}

$check = $db->query("SELECT cotizacion_id FROM cotizaciones
                     WHERE cotizacion_id=$coti_id AND observacion='TEST_CORTES_COMBINAR'");
if (!$check || $check->num_rows == 0) {
    echo "<div class='box' style='background:#f8d7da'>Cotización #$coti_id no es de prueba.</div>
          <a href='test_cortes_combinar.php?accion=crear' class='btn btn-green'>Crear datos</a></body></html>";
    exit;
}

// Info del cliente
$cli_r = $db->query("SELECT datos, dias_visitas, id_ruta FROM clientes WHERE id_cliente=$id_cliente");
$cli   = $cli_r ? $cli_r->fetch_assoc() : [];

echo "<div class='box'>
    <b>Cliente #$id_cliente:</b> " . htmlspecialchars($cli['datos'] ?? '—') . " |
    dias_visitas: <b>" . ($cli['dias_visitas'] ?? '—') . "</b> |
    id_ruta: <b>" . ($cli['id_ruta'] ?? '—') . "</b><br>
    <b>Cotización prueba:</b> #$coti_id |
    fechaInicio: $fechaInicio | fechaFin: $fechaFin
</div>";

echo "<p>
    <a href='test_cortes_combinar.php?accion=limpiar&coti_id=$coti_id' class='btn btn-red'
       onclick=\"return confirm('¿Eliminar cotización #$coti_id?')\">Eliminar datos de prueba</a>
    &nbsp;
    <a href='test_cortes_combinar.php?accion=crear' class='btn btn-green'>Crear nuevos datos</a>
</p>";

// ─── Ventanas de corte ────────────────────────────────────────────────────────
echo "<h2>Ventanas de filtro (pc.fecha_registro)</h2>";
echo "<table>
  <tr class='head'><th>Corte</th><th>Ventana</th><th>Qué contiene</th></tr>
  <tr style='background:#eafaf1'><td><b>PRIMER CORTE</b></td>
      <td>≥ $fechaInicio 08:00  AND  &lt; $fechaFin 08:00</td>
      <td>Pedidos base registrados entre las 08:00 del día inicio y las 08:00 del día de carga</td></tr>
  <tr style='background:#fef9e7'><td><b>SEGUNDO CORTE</b></td>
      <td>≥ $fechaFin 08:00  AND  &lt; $fechaFin 15:00</td>
      <td>Productos NUEVOS + AUMENTOS registrados de 08:00 a 15:00</td></tr>
  <tr style='background:#e8f8f5'><td><b>TERCER CORTE</b></td>
      <td>≥ $fechaFin 15:00  AND  ≤ $fechaFin 23:59</td>
      <td>Productos NUEVOS + AUMENTOS registrados de 15:00 a 23:59</td></tr>
</table>";

// ─── Flujo simulado ──────────────────────────────────────────────────────────
echo "<h2>Flujo simulado de CotizacionesController</h2>";
echo "<table>
  <tr class='head'><th>Evento</th><th>Producto</th><th>Cantidad (fila)</th>
      <th>fecha_registro</th><th>Tipo de operación</th></tr>
  <tr style='background:#eafaf1'>
    <td>CREAR cotización</td><td>123</td><td>2</td>
    <td>2026-05-06 22:00:00</td><td>insertarProductoCotizacion (base)</td></tr>
  <tr style='background:#eafaf1'>
    <td>CREAR cotización</td><td>383</td><td>2</td>
    <td>2026-05-06 22:00:00</td><td>insertarProductoCotizacion (base)</td></tr>
  <tr style='background:#eafaf1'>
    <td>CREAR cotización</td><td>342</td><td>2</td>
    <td>2026-05-06 22:00:00</td><td>insertarProductoCotizacion (base)</td></tr>
  <tr style='background:#fef9e7'>
    <td>ACTUALIZAR — segundo corte</td><td><b>321</b></td><td>2</td>
    <td>2026-05-07 09:30:00</td><td><b>PRODUCTO NUEVO</b> (cantidadAnterior=0 → INSERT completo)</td></tr>
  <tr style='background:#fef9e7'>
    <td>ACTUALIZAR — segundo corte</td><td><b>123</b></td><td>3</td>
    <td>2026-05-07 09:30:00</td><td><b>AUMENTO existente</b> (2→5, delta=3 → INSERT solo delta)</td></tr>
  <tr style='background:#e8f8f5'>
    <td>ACTUALIZAR — tercer corte</td><td><b>383</b></td><td>5</td>
    <td>2026-05-07 16:00:00</td><td><b>AUMENTO existente</b> (2→7, delta=5 → INSERT solo delta)</td></tr>
</table>";

// ─── Filas en BD ─────────────────────────────────────────────────────────────
echo "<h2>Filas en productos_cotis (cotización #$coti_id)</h2>";
$r_all = $db->query("SELECT pc.prod_coti_id, pc.id_producto, p.descripcion,
                            pc.cantidad, pc.medida, pc.presenta_cnt, pc.fecha_registro
                     FROM productos_cotis pc
                     INNER JOIN productos p ON p.id_producto = pc.id_producto
                     WHERE pc.id_coti=$coti_id
                     ORDER BY pc.fecha_registro ASC, pc.prod_coti_id ASC");
if (!$r_all) { echo "<p style='color:red'>Error: " . $db->error . "</p>"; }
else {
    echo "<table>
          <tr class='head'><th>id</th><th>id_prod</th><th>descripcion</th>
              <th>qty</th><th>presenta_cnt</th><th>fecha_registro</th><th>CORTE</th></tr>";
    while ($row = $r_all->fetch_assoc()) {
        $fr = $row['fecha_registro'];
        if ($fr >= "$fechaInicio 08:00:00" && $fr < "$fechaFin 08:00:00")
            [$label,$cbg] = ['PRIMER CORTE',  '#d4edda'];
        elseif ($fr >= "$fechaFin 08:00:00" && $fr < "$fechaFin 15:00:00")
            [$label,$cbg] = ['SEGUNDO CORTE', '#fff3cd'];
        elseif ($fr >= "$fechaFin 15:00:00")
            [$label,$cbg] = ['TERCER CORTE',  '#d1ecf1'];
        else
            [$label,$cbg] = ['fuera de ventana', '#f8d7da'];

        echo "<tr>
            <td>{$row['prod_coti_id']}</td>
            <td>{$row['id_producto']}</td>
            <td>" . htmlspecialchars(trim($row['descripcion'])) . "</td>
            <td style='text-align:center'>{$row['cantidad']}</td>
            <td style='text-align:center'>{$row['presenta_cnt']}</td>
            <td>{$row['fecha_registro']}</td>
            <td style='background:$cbg;font-weight:bold'>$label</td>
        </tr>";
    }
    echo "</table>";
}

// ─── Resultados por corte (query del consolidado) ─────────────────────────────
echo "<h2>Resultados por corte — query de CombinarReporteController</h2>";

$colores_corte = [
    'primer_corte'  => ['PRIMER CORTE — Pedidos base (sin aumentos)',            '#1e6091'],
    'segundo_corte' => ['SEGUNDO CORTE — Producto nuevo (321) + aumento (123)', '#856404'],
    'tercer_corte'  => ['TERCER CORTE — Aumento de existente (383)',             '#0c6674'],
];

$results = [];
foreach ($colores_corte as $corte => [$titulo, $color]) {
    $where = corte_where($fechaInicio, $fechaFin, $corte);

    // Misma query que usa CombinarReporteController::consolidadoPedidosCamion en $query_productos
    $sql = "SELECT pc.id_producto, p.descripcion, pc.medida,
                   pc.presenta_cnt AS total_medida,
                   SUM(pc.cantidad) AS total_cantidad,
                   SUM(pc.cantidad * pc.presenta_cnt) AS total_M
            FROM productos_cotis pc
            INNER JOIN productos p ON p.id_producto = pc.id_producto
            WHERE pc.id_coti = $coti_id
              AND pc.medida = '$medida_test'
              AND $where
            GROUP BY pc.id_producto, p.descripcion, pc.medida, pc.presenta_cnt
            ORDER BY p.descripcion ASC";

    $r = $db->query($sql);
    if (!$r) {
        echo "<p style='color:red'>SQL Error ($corte): " . $db->error . "<br><code>$sql</code></p>";
        $results[$corte] = [];
        continue;
    }
    $rows = [];
    while ($row = $r->fetch_assoc()) $rows[] = $row;
    $results[$corte] = $rows;

    echo "<h3 style='color:$color;margin-top:18px'>$titulo</h3>";
    echo "<div class='box' style='border-color:$color;background:#fafafa'>
            <b>Filtro aplicado:</b> <code>AND $where</code></div>";

    if (empty($rows)) {
        echo "<p style='color:#c0392b;font-style:italic'>— Sin resultados —</p>";
    } else {
        echo "<table>
              <tr class='head'><th>id_producto</th><th>descripcion</th>
                  <th>medida</th><th>presenta_cnt</th>
                  <th>total_cantidad</th><th>total_M (M)</th></tr>";
        foreach ($rows as $row) {
            echo "<tr>
                <td style='text-align:center'>{$row['id_producto']}</td>
                <td>" . htmlspecialchars(trim($row['descripcion'])) . "</td>
                <td>{$row['medida']}</td>
                <td style='text-align:center'>{$row['total_medida']}</td>
                <td style='text-align:center;font-weight:bold'>{$row['total_cantidad']}</td>
                <td style='text-align:center'>{$row['total_M']}</td>
            </tr>";
        }
        echo "</table>";
    }
}

// ─── Resumen total por producto ────────────────────────────────────────────────
echo "<h2>Resumen total por producto</h2>";
$pids_resumen = [123, 383, 342, 321];
echo "<table>
      <tr class='head'>
        <th>id_producto</th><th>Descripción</th>
        <th style='background:#1e6091'>Primer Corte<br>(base)</th>
        <th style='background:#856404'>Segundo Corte<br>(nuevo/aumento)</th>
        <th style='background:#0c6674'>Tercer Corte<br>(aumento)</th>
        <th>TOTAL en BD</th>
      </tr>";
foreach ($pids_resumen as $pid) {
    $r = $db->query("SELECT p.descripcion,
        SUM(IF(pc.fecha_registro >= '$fechaInicio 08:00:00' AND pc.fecha_registro < '$fechaFin 08:00:00', pc.cantidad, 0)) AS base,
        SUM(IF(pc.fecha_registro >= '$fechaFin 08:00:00'   AND pc.fecha_registro < '$fechaFin 15:00:00', pc.cantidad, 0)) AS seg,
        SUM(IF(pc.fecha_registro >= '$fechaFin 15:00:00'   AND pc.fecha_registro <= '$fechaFin 23:59:59', pc.cantidad, 0)) AS ter,
        SUM(pc.cantidad) AS total
      FROM productos_cotis pc
      INNER JOIN productos p ON p.id_producto = pc.id_producto
      WHERE pc.id_coti=$coti_id AND pc.id_producto=$pid
      GROUP BY p.descripcion");
    if ($r && $row = $r->fetch_assoc()) {
        echo "<tr>
          <td style='text-align:center'>$pid</td>
          <td>" . htmlspecialchars(trim($row['descripcion'])) . "</td>
          <td style='text-align:center;background:#eaf4fb'>{$row['base']}</td>
          <td style='text-align:center;background:#fef9e7'>{$row['seg']}</td>
          <td style='text-align:center;background:#e8f8f5'>{$row['ter']}</td>
          <td style='text-align:center;font-weight:bold'>{$row['total']}</td>
        </tr>";
    }
}
echo "</table>";

// ─── Verificaciones ──────────────────────────────────────────────────────────
echo "<h2>Verificaciones</h2><ul style='font-size:13px;line-height:2.2'>";

$all_ok = true;

// V1: PRIMER CORTE — los 3 productos base
$p1_ids = array_column($results['primer_corte'] ?? [], 'id_producto');
$v1 = count(array_intersect(['123','383','342'], $p1_ids)) == 3 && count($p1_ids) == 3;
$all_ok = $all_ok && $v1;
echo "<li>" . ($v1 ? '<span class="ok">✔</span>' : '<span class="err">✘</span>') .
     " PRIMER CORTE tiene los 3 productos base (123, 383, 342). Encontrados: " .
     (empty($p1_ids) ? '—' : implode(', ', $p1_ids)) . "</li>";

// V2: PRIMER CORTE — cantidades correctas (2 cada uno)
$p1_rows = array_column($results['primer_corte'] ?? [], null, 'id_producto');
$v2 = (($p1_rows['123']['total_cantidad'] ?? -1) == 2) &&
      (($p1_rows['383']['total_cantidad'] ?? -1) == 2) &&
      (($p1_rows['342']['total_cantidad'] ?? -1) == 2);
$all_ok = $all_ok && $v2;
echo "<li>" . ($v2 ? '<span class="ok">✔</span>' : '<span class="err">✘</span>') .
     " PRIMER CORTE — cantidades: 123=" . ($p1_rows['123']['total_cantidad'] ?? '?') .
     ", 383=" . ($p1_rows['383']['total_cantidad'] ?? '?') .
     ", 342=" . ($p1_rows['342']['total_cantidad'] ?? '?') . " (esperado: 2, 2, 2)</li>";

// V3: SEGUNDO CORTE — producto NUEVO 321 y aumento de 123
$p2_ids  = array_column($results['segundo_corte'] ?? [], 'id_producto');
$p2_rows = array_column($results['segundo_corte'] ?? [], null, 'id_producto');
$v3 = count($results['segundo_corte'] ?? []) == 2 &&
      in_array('321', $p2_ids) && in_array('123', $p2_ids);
$all_ok = $all_ok && $v3;
echo "<li>" . ($v3 ? '<span class="ok">✔</span>' : '<span class="err">✘</span>') .
     " SEGUNDO CORTE: 2 resultados — producto NUEVO 321 + aumento de 123. Encontrados: " .
     (empty($p2_ids) ? '—' : implode(', ', $p2_ids)) . "</li>";

// V4: SEGUNDO CORTE — producto 321 (nuevo) qty=2
$v4 = ($p2_rows['321']['total_cantidad'] ?? -1) == 2;
$all_ok = $all_ok && $v4;
echo "<li>" . ($v4 ? '<span class="ok">✔</span>' : '<span class="err">✘</span>') .
     " SEGUNDO CORTE — producto 321 (NUEVO): qty=" . ($p2_rows['321']['total_cantidad'] ?? '?') .
     " (esperado=2, cantidad completa del nuevo)</li>";

// V5: SEGUNDO CORTE — producto 123 qty=3 (solo el delta del aumento, no los 5 totales)
$v5 = ($p2_rows['123']['total_cantidad'] ?? -1) == 3;
$all_ok = $all_ok && $v5;
echo "<li>" . ($v5 ? '<span class="ok">✔</span>' : '<span class="err">✘</span>') .
     " SEGUNDO CORTE — producto 123 (AUMENTO): qty=" . ($p2_rows['123']['total_cantidad'] ?? '?') .
     " (esperado=3 — solo el delta, no los 5 totales)</li>";

// V6: TERCER CORTE — solo producto 383 con qty=5 (el delta del aumento)
$p3_ids  = array_column($results['tercer_corte'] ?? [], 'id_producto');
$p3_rows = array_column($results['tercer_corte'] ?? [], null, 'id_producto');
$v6 = count($results['tercer_corte'] ?? []) == 1 &&
      in_array('383', $p3_ids) &&
      ($p3_rows['383']['total_cantidad'] ?? -1) == 5;
$all_ok = $all_ok && $v6;
echo "<li>" . ($v6 ? '<span class="ok">✔</span>' : '<span class="err">✘</span>') .
     " TERCER CORTE: solo producto 383 con qty=" . ($p3_rows['383']['total_cantidad'] ?? '?') .
     " (esperado=5 — solo el delta del aumento). Encontrados: " .
     (empty($p3_ids) ? '—' : implode(', ', $p3_ids)) . "</li>";

// V7: TERCER CORTE no contamina con productos del segundo corte ni base
$v7 = !in_array('321', $p3_ids) && !in_array('123', $p3_ids) && !in_array('342', $p3_ids);
$all_ok = $all_ok && $v7;
echo "<li>" . ($v7 ? '<span class="ok">✔</span>' : '<span class="err">✘</span>') .
     " TERCER CORTE NO incluye productos 321/123 (segundo) ni 342 (solo base)</li>";

echo "</ul>";

$color_final = $all_ok ? '#27ae60' : '#c0392b';
$label_final = $all_ok
    ? '✔ TODOS LOS CHECKS PASAN — el filtro de cortes funciona correctamente'
    : '✘ ALGÚN CHECK FALLÓ — revisar la lógica de filtro en CombinarReporteController';
echo "<div style='background:$color_final;color:#fff;padding:14px 20px;border-radius:6px;
                  font-size:16px;font-weight:bold;margin-top:16px'>
    $label_final
</div>";

echo "<p style='margin-top:24px'>
    <a href='test_cortes_combinar.php?accion=limpiar&coti_id=$coti_id' class='btn btn-red'
       onclick=\"return confirm('¿Eliminar cotización #$coti_id de prueba?')\">
       Limpiar / Eliminar datos de prueba
    </a>
</p>";
echo "</body></html>";
