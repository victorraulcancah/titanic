<?php
/**
 * Test: Filtros de corte del Consolidado Logístico
 * Uso:
 *   ?accion=crear  → inserta datos de prueba en la BD (persiste)
 *   ?accion=limpiar&coti_id=X → elimina los datos de prueba
 *   (sin parámetro) → muestra resultados de la última cotización de prueba
 */

require_once 'utils/config.php';

$db = new mysqli(HOST_SS, USER_SS, PASSWORD_SS, DATABASE_SS);
$db->set_charset('utf8');
if ($db->connect_error) die('<b>Error conexión:</b> ' . $db->connect_error);

// ─── Parámetros ───────────────────────────────────────────────────────────────
$fechaInicio = '2026-05-06';
$fechaFin    = '2026-05-07';
$id_empresa  = 12;
$sucursal    = 1;
$medida_test = 'Kilos';

// Productos de prueba
// [ id_producto, cantidad, precio, presenta_cnt, fecha_registro ]
$productos_test = [
    // BASE — primer_corte (05-06 08:00 → 05-07 08:00)
    [123, 2, 25.00, 5, '2026-05-06 22:00:00'],
    [383, 2, 20.00, 5, '2026-05-06 22:00:00'],
    [342, 2, 52.50, 5, '2026-05-06 22:00:00'],
    // SEGUNDO CORTE — 05-07 08:00 → 15:00
    [123, 3, 25.00, 5, '2026-05-07 09:30:00'],
    [321, 2, 25.00, 5, '2026-05-07 10:00:00'],
    // TERCER CORTE — 05-07 15:00 → 23:59
    [383, 5, 20.00, 5, '2026-05-07 16:00:00'],
    [342, 1, 52.50, 5, '2026-05-07 17:00:00'],
];

// ─── Filtros de corte (replica del controller) ────────────────────────────────
function corte_filtro($fechaInicio, $fechaFin, $horario) {
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

function query_corte($db, $coti_id, $fechaInicio, $fechaFin, $horario, $medida) {
    $where = corte_filtro($fechaInicio, $fechaFin, $horario);
    $sql = "SELECT pc.id_producto, p.descripcion, pc.medida, pc.presenta_cnt,
                   pc.fecha_registro,
                   SUM(pc.cantidad) AS total_cantidad,
                   SUM(pc.cantidad * pc.presenta_cnt) AS total_M
            FROM productos_cotis pc
            INNER JOIN productos p ON p.id_producto = pc.id_producto
            WHERE pc.id_coti = $coti_id AND pc.medida = '$medida' AND $where
            GROUP BY pc.id_producto, p.descripcion, pc.medida, pc.presenta_cnt, pc.fecha_registro
            ORDER BY p.descripcion ASC, pc.fecha_registro ASC";
    $r = $db->query($sql);
    if (!$r) { echo "<pre>SQL Error: " . $db->error . "\n$sql</pre>"; return []; }
    $rows = [];
    while ($row = $r->fetch_assoc()) $rows[] = $row;
    return $rows;
}

function tabla($rows, $titulo, $color) {
    echo "<h3 style='color:$color;margin-top:18px'>$titulo</h3>";
    if (empty($rows)) { echo "<p style='color:#c0392b'>— Sin resultados —</p>"; return; }
    echo "<table border='1' cellpadding='4' cellspacing='0'
               style='border-collapse:collapse;font-size:13px;min-width:600px'>";
    echo "<tr style='background:$color;color:#fff'>";
    foreach (array_keys($rows[0]) as $col) echo "<th style='padding:4px 10px'>$col</th>";
    echo "</tr>";
    foreach ($rows as $i => $row) {
        $bg = $i % 2 ? '#fff' : '#f5f5f5';
        echo "<tr style='background:$bg'>";
        foreach ($row as $v) echo "<td style='padding:4px 10px'>" . htmlspecialchars((string)$v) . "</td>";
        echo "</tr>";
    }
    $total = array_sum(array_column($rows, 'total_cantidad'));
    echo "<tr style='background:#e8f4f8;font-weight:bold'>
            <td colspan='" . (count(array_keys($rows[0])) - 2) . "' style='padding:4px 10px;text-align:right'>TOTAL unidades:</td>
            <td style='padding:4px 10px'>$total</td><td></td></tr>";
    echo "</table>";
}

// ─── CSS base ─────────────────────────────────────────────────────────────────
$html_head = "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'>
<title>Test Cortes Logístico</title>
<style>
  body { font-family:Arial,sans-serif; padding:24px; font-size:14px; color:#222; }
  h1   { color:#1a252f; border-bottom:3px solid #2980b9; padding-bottom:6px; }
  h2   { color:#2c3e50; margin-top:28px; border-left:4px solid #2980b9; padding-left:8px; }
  .box { background:#eaf4fb; border:1px solid #aed6f1; padding:10px 16px;
         border-radius:4px; font-size:13px; margin:8px 0 16px; }
  .ok  { color:#27ae60; font-weight:bold; }
  .err { color:#c0392b; font-weight:bold; }
  .btn { display:inline-block; padding:8px 18px; border-radius:4px; font-size:14px;
         font-weight:bold; text-decoration:none; cursor:pointer; border:none; }
  .btn-green  { background:#27ae60; color:#fff; }
  .btn-red    { background:#c0392b; color:#fff; }
  .btn-blue   { background:#2980b9; color:#fff; }
</style></head><body>";

$accion  = $_GET['accion']  ?? '';
$coti_id = (int)($_GET['coti_id'] ?? 0);

// ══════════════════════════════════════════════════════════════════════════════
// ACCIÓN: LIMPIAR
// ══════════════════════════════════════════════════════════════════════════════
if ($accion === 'limpiar' && $coti_id > 0) {
    echo $html_head;
    echo "<h1>Limpiar datos de prueba</h1>";

    // Verificar que sea realmente una cotización de prueba antes de eliminar
    $check = $db->query("SELECT cotizacion_id FROM cotizaciones WHERE cotizacion_id=$coti_id AND observacion='TEST_CORTES_LOGISTICO'");
    if (!$check || $check->num_rows == 0) {
        die("<div class='box' style='background:#f8d7da;color:#c0392b;font-weight:bold'>Error de seguridad: El ID #$coti_id no es una cotización de prueba válida (falta marca TEST_CORTES_LOGISTICO). Protegiendo la base de datos...</div></body></html>");
    }

    $db->query("DELETE FROM productos_cotis WHERE id_coti=$coti_id");
    $db->query("DELETE FROM cuotas_cotizacion WHERE id_coti=$coti_id");
    $db->query("DELETE FROM cotizaciones WHERE cotizacion_id=$coti_id");

    echo "<div class='box' style='background:#d4edda;border-color:#27ae60'>
        ✔ Cotización <b>#$coti_id</b> y sus productos eliminados correctamente.
    </div>";
    echo "<a href='test_cortes_logistico.php?accion=crear' class='btn btn-green'>Crear nuevos datos de prueba</a>";
    echo "</body></html>";
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// ACCIÓN: CREAR
// ══════════════════════════════════════════════════════════════════════════════
if ($accion === 'crear') {
    echo $html_head;
    echo "<h1>Crear datos de prueba</h1>";

    if (!$db->query("INSERT INTO cotizaciones SET
            id_tido=6, id_cliente=1763, id_empresa=$id_empresa, sucursal=$sucursal,
            fecha='$fechaInicio', total=1000.00, estado=0,
            id_tipo_pago=1, id_usuario=1,
            observacion='TEST_CORTES_LOGISTICO',
            fecha_registro='2026-05-06 22:00:00'")) {
        echo "<div class='box' style='background:#f8d7da;border-color:#c0392b'>
            Error: " . $db->error . "</div>";
        exit;
    }

    $coti_id = $db->insert_id;

    foreach ($productos_test as [$pid, $qty, $precio, $pcnt, $fecha]) {
        $db->query("INSERT INTO productos_cotis SET
            id_coti=$coti_id, id_producto=$pid, cantidad=$qty, precio=$precio,
            medida='Kilos', presenta_cnt=$pcnt, presenta='1', costo=0,
            fecha_registro='$fecha'");
    }

    echo "<div class='box' style='background:#d4edda;border-color:#27ae60'>
        ✔ Cotización de prueba creada con ID <b>#$coti_id</b> — los datos YA están en la BD.
    </div>";

    echo "<div class='box'>
        <b>Prueba el reporte ahora:</b><br><br>
        <a class='btn btn-blue' style='margin:4px' href='?horario=primer_corte&fechaInicio=$fechaInicio&fechaFin=$fechaFin&camion=2&medida=Kilos&diasVisita=viernes&action=reporteLogistico' target='_blank'>
            Ver Primer Corte (no aplica — solo ver datos)
        </a><br><br>
        <b>O copia estos parámetros en el modal del reporte:</b><br>
        fechaInicio: <b>$fechaInicio</b> | fechaFin: <b>$fechaFin</b> | camion: <b>2</b> | medida: <b>Kilos</b> | diasVisita: <b>viernes</b>
    </div>";

    echo "<p><a href='test_cortes_logistico.php?coti_id=$coti_id' class='btn btn-blue'>Ver resultados del test con esta cotización</a></p>";
    echo "<p><a href='test_cortes_logistico.php?accion=limpiar&coti_id=$coti_id' class='btn btn-red'
             onclick=\"return confirm('¿Eliminar cotización #$coti_id de prueba?')\">
             Limpiar / Eliminar datos de prueba</a></p>";
    echo "</body></html>";
    exit;
}

// ══════════════════════════════════════════════════════════════════════════════
// VISTA: MOSTRAR RESULTADOS (con coti_id existente o pantalla de inicio)
// ══════════════════════════════════════════════════════════════════════════════
echo $html_head;
echo "<h1>Test: Filtros de Corte — Consolidado Logístico</h1>";

if (!$coti_id) {
    // Buscar si ya existe una cotización de prueba reciente (marcada con TEST_CORTES_LOGISTICO)
    $r = $db->query("SELECT cotizacion_id FROM cotizaciones
                     WHERE observacion='TEST_CORTES_LOGISTICO' AND id_empresa=$id_empresa
                     ORDER BY cotizacion_id DESC LIMIT 1");
    if ($r && $row = $r->fetch_assoc()) {
        $coti_id = $row['cotizacion_id'];
    }
}

if (!$coti_id) {
    echo "<div class='box'>No hay datos de prueba en la BD todavía.</div>";
    echo "<a href='test_cortes_logistico.php?accion=crear' class='btn btn-green'>Crear datos de prueba en la BD</a>";
    echo "</body></html>";
    exit;
}

// Verificar que la cotización existe y es de prueba
$r = $db->query("SELECT cotizacion_id FROM cotizaciones WHERE cotizacion_id=$coti_id AND observacion='TEST_CORTES_LOGISTICO'");
if (!$r || $r->num_rows == 0) {
    echo "<div class='box' style='background:#f8d7da'>Cotización #$coti_id no encontrada o NO es una cotización de prueba válida.</div>";
    echo "<a href='test_cortes_logistico.php?accion=crear' class='btn btn-green'>Crear datos de prueba</a>";
    echo "</body></html>";
    exit;
}

echo "<div class='box'>
    <b>Parámetros:</b> fechaInicio=$fechaInicio | fechaFin=$fechaFin | camion=2 | medida=Kilos | diasVisita=viernes<br>
    <b>Cotización de prueba:</b> ID=<b>$coti_id</b> (datos REALES en BD — no se revierten)
</div>";

echo "<p>
    <a href='test_cortes_logistico.php?accion=limpiar&coti_id=$coti_id' class='btn btn-red'
       onclick=\"return confirm('¿Eliminar cotización #$coti_id de prueba?')\">
       Eliminar datos de prueba (ID #$coti_id)
    </a>
    &nbsp;
    <a href='test_cortes_logistico.php?accion=crear' class='btn btn-green'>Crear nuevos datos</a>
</p>";

// ─── Ventanas ─────────────────────────────────────────────────────────────────
echo "<h2>Ventanas de filtro</h2>";
echo "<table border='1' cellpadding='4' cellspacing='0' style='border-collapse:collapse;font-size:13px'>
  <tr style='background:#2c3e50;color:#fff'>
    <th style='padding:4px 12px'>Corte</th>
    <th style='padding:4px 12px'>Ventana pc.fecha_registro</th>
  </tr>
  <tr><td style='padding:4px 12px;font-weight:bold'>PRIMER CORTE</td>
      <td style='padding:4px 12px'>≥ $fechaInicio 08:00  AND  &lt; $fechaFin 08:00</td></tr>
  <tr style='background:#f9f9f9'>
      <td style='padding:4px 12px;font-weight:bold'>SEGUNDO CORTE</td>
      <td style='padding:4px 12px'>≥ $fechaFin 08:00  AND  &lt; $fechaFin 15:00</td></tr>
  <tr><td style='padding:4px 12px;font-weight:bold'>TERCER CORTE</td>
      <td style='padding:4px 12px'>≥ $fechaFin 15:00  AND  ≤ $fechaFin 23:59</td></tr>
</table>";

// ─── Datos insertados ──────────────────────────────────────────────────────────
echo "<h2>Productos en BD (cotización #$coti_id)</h2>";
$r_all = $db->query("SELECT pc.prod_coti_id, pc.id_producto, p.descripcion,
                            pc.cantidad, pc.medida, pc.presenta_cnt, pc.fecha_registro
                     FROM productos_cotis pc
                     INNER JOIN productos p ON p.id_producto = pc.id_producto
                     WHERE pc.id_coti=$coti_id ORDER BY pc.fecha_registro, pc.prod_coti_id");
echo "<table border='1' cellpadding='4' cellspacing='0' style='border-collapse:collapse;font-size:13px'>
      <tr style='background:#2c3e50;color:#fff'>
        <th>id</th><th>producto</th><th>descripcion</th><th>qty</th>
        <th>medida</th><th>presenta_cnt</th><th>fecha_registro</th><th>CORTE</th>
      </tr>";
while ($row = $r_all->fetch_assoc()) {
    $fr = $row['fecha_registro'];
    if ($fr >= "$fechaInicio 08:00:00" && $fr < "$fechaFin 08:00:00")
        [$label,$cbg] = ['PRIMER CORTE (base)', '#d4edda'];
    elseif ($fr >= "$fechaFin 08:00:00" && $fr < "$fechaFin 15:00:00")
        [$label,$cbg] = ['SEGUNDO CORTE', '#fff3cd'];
    elseif ($fr >= "$fechaFin 15:00:00")
        [$label,$cbg] = ['TERCER CORTE', '#d1ecf1'];
    else
        [$label,$cbg] = ['fuera de ventana', '#f8d7da'];

    echo "<tr>
      <td style='padding:3px 8px'>{$row['prod_coti_id']}</td>
      <td style='padding:3px 8px'>{$row['id_producto']}</td>
      <td style='padding:3px 8px'>" . htmlspecialchars(trim($row['descripcion'])) . "</td>
      <td style='padding:3px 8px;text-align:center'>{$row['cantidad']}</td>
      <td style='padding:3px 8px'>{$row['medida']}</td>
      <td style='padding:3px 8px;text-align:center'>{$row['presenta_cnt']}</td>
      <td style='padding:3px 8px'>{$row['fecha_registro']}</td>
      <td style='padding:3px 8px;background:$cbg;font-weight:bold'>$label</td>
    </tr>";
}
echo "</table>";

// ─── Resultados por corte ──────────────────────────────────────────────────────
echo "<h2>Resultados por corte</h2>";
$config_cortes = [
    'primer_corte'  => ['PRIMER CORTE — Pedidos base',            '#1e6091'],
    'segundo_corte' => ['SEGUNDO CORTE — Solo aumentos mañana',   '#856404'],
    'tercer_corte'  => ['TERCER CORTE — Solo aumentos tarde/noche','#0c6674'],
];
$results = [];
foreach ($config_cortes as $corte => [$titulo, $color]) {
    $filtro = corte_filtro($fechaInicio, $fechaFin, $corte);
    echo "<div class='box' style='border-color:$color;background:#fafafa'>
            <b>Filtro:</b> <code>$filtro</code></div>";
    $rows = query_corte($db, $coti_id, $fechaInicio, $fechaFin, $corte, $medida_test);
    $results[$corte] = $rows;
    tabla($rows, $titulo, $color);
}

// ─── Resumen ──────────────────────────────────────────────────────────────────
echo "<h2>Resumen por producto</h2>";
$pids = [123, 383, 342, 321];
echo "<table border='1' cellpadding='4' cellspacing='0' style='border-collapse:collapse;font-size:13px'>
      <tr style='background:#2c3e50;color:#fff'>
        <th>id_producto</th><th>Descripción</th>
        <th style='background:#1e6091'>Base (1er)</th>
        <th style='background:#856404'>+Mañana (2do)</th>
        <th style='background:#0c6674'>+Tarde (3er)</th>
        <th>TOTAL</th>
      </tr>";
foreach ($pids as $pid) {
    $r = $db->query("SELECT p.descripcion,
        SUM(IF(pc.fecha_registro >= '$fechaInicio 08:00:00' AND pc.fecha_registro < '$fechaFin 08:00:00', pc.cantidad, 0)) AS base,
        SUM(IF(pc.fecha_registro >= '$fechaFin 08:00:00'   AND pc.fecha_registro < '$fechaFin 15:00:00', pc.cantidad, 0)) AS seg,
        SUM(IF(pc.fecha_registro >= '$fechaFin 15:00:00'   AND pc.fecha_registro <= '$fechaFin 23:59:59', pc.cantidad, 0)) AS ter,
        SUM(pc.cantidad) AS total
      FROM productos_cotis pc INNER JOIN productos p ON p.id_producto=pc.id_producto
      WHERE pc.id_coti=$coti_id AND pc.id_producto=$pid GROUP BY p.descripcion");
    if ($r && $row = $r->fetch_assoc()) {
        echo "<tr>
          <td style='padding:4px 10px;text-align:center'>$pid</td>
          <td style='padding:4px 10px'>" . htmlspecialchars(trim($row['descripcion'])) . "</td>
          <td style='padding:4px 10px;text-align:center;background:#eaf4fb'>{$row['base']}</td>
          <td style='padding:4px 10px;text-align:center;background:#fef9e7'>{$row['seg']}</td>
          <td style='padding:4px 10px;text-align:center;background:#e8f8f5'>{$row['ter']}</td>
          <td style='padding:4px 10px;text-align:center;font-weight:bold'>{$row['total']}</td>
        </tr>";
    }
}
echo "</table>";

// ─── Verificaciones ────────────────────────────────────────────────────────────
echo "<h2>Verificaciones</h2><ul style='font-size:13px;line-height:1.8'>";
$primer_ids = array_column($results['primer_corte'], 'id_producto');
$v1 = count(array_intersect(['123','383','342'], $primer_ids)) == 3;
echo "<li>" . ($v1?'<span class="ok">✔</span>':'<span class="err">✘</span>') .
     " PRIMER CORTE tiene los 3 productos base: " . implode(', ', $primer_ids) . "</li>";

$seg_ids = array_column($results['segundo_corte'], 'id_producto');
$seg_ok  = count($results['segundo_corte']) == 2 && in_array('123',$seg_ids) && in_array('321',$seg_ids);
echo "<li>" . ($seg_ok?'<span class="ok">✔</span>':'<span class="err">✘</span>') .
     " SEGUNDO CORTE muestra " . count($results['segundo_corte']) . " fila(s): " . implode(', ', $seg_ids ?: ['-']) . "</li>";

$ter_ids = array_column($results['tercer_corte'], 'id_producto');
$ter_ok  = count($results['tercer_corte']) == 2 && in_array('383',$ter_ids) && in_array('342',$ter_ids);
echo "<li>" . ($ter_ok?'<span class="ok">✔</span>':'<span class="err">✘</span>') .
     " TERCER CORTE muestra " . count($results['tercer_corte']) . " fila(s): " . implode(', ', $ter_ids ?: ['-']) . "</li>";

$seg_en_ter = in_array('321', $ter_ids);
echo "<li>" . (!$seg_en_ter?'<span class="ok">✔</span>':'<span class="err">✘</span>') .
     " TERCER CORTE NO incluye producto 321 (exclusivo del 2do corte).</li>";
echo "</ul>";

echo "<p style='margin-top:24px'>
    <a href='test_cortes_logistico.php?accion=limpiar&coti_id=$coti_id' class='btn btn-red'
       onclick=\"return confirm('¿Eliminar cotización #$coti_id de prueba?')\">
       Limpiar / Eliminar datos de prueba
    </a>
</p>";
echo "</body></html>";
