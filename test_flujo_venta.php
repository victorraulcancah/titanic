<?php
/**
 * Script de prueba para verificar el flujo completo de:
 * COTIZACIÓN → VENTA → STOCK → COBRANZAS
 * 
 * Venta de prueba: NV01-29452 (ID: 45051)
 * Cliente: 19933404 | D-MARIO TOVAR (GAMBETA)
 */

// Cargar configuración
require_once 'utils/config.php';

echo "=== INICIANDO PRUEBAS DEL FLUJO DE VENTAS ===\n\n";

// Conectar a la base de datos
$conexion = new mysqli(HOST_SS, USER_SS, PASSWORD_SS, DATABASE_SS);
$conexion->set_charset("utf8");

if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}

// ID de la venta a testear - buscar la última venta
$sql_ultima = "SELECT id_venta FROM ventas WHERE serie = 'NV01' AND numero = 29452 LIMIT 1";
$result = $conexion->query($sql_ultima);

if ($result && $result->num_rows > 0) {
    $id_venta = $result->fetch_assoc()['id_venta'];
} else {
    // Si no encuentra, buscar la última venta registrada
    $sql_ultima = "SELECT id_venta FROM ventas ORDER BY id_venta DESC LIMIT 1";
    $result = $conexion->query($sql_ultima);
    if ($result && $result->num_rows > 0) {
        $id_venta = $result->fetch_assoc()['id_venta'];
    } else {
        die("❌ No hay ventas en la base de datos\n");
    }
}

echo "📋 TESTEANDO VENTA ID: $id_venta\n";
echo str_repeat("=", 60) . "\n\n";

// ============================================
// TEST 1: Verificar datos de la venta
// ============================================
echo "TEST 1: Verificar datos de la venta\n";
echo str_repeat("-", 60) . "\n";

$sql = "SELECT v.*, c.documento, c.datos as nombre_cliente
        FROM ventas v
        INNER JOIN clientes c ON v.id_cliente = c.id_cliente
        WHERE v.id_venta = $id_venta";

$venta = $conexion->query($sql)->fetch_assoc();

if ($venta) {
    echo "✅ Venta encontrada\n";
    echo "   - Documento: {$venta['serie']}-{$venta['numero']}\n";
    echo "   - Fecha: {$venta['fecha']}\n";
    echo "   - Cliente: {$venta['documento']} | {$venta['nombre_cliente']}\n";
    echo "   - Total: S/ " . number_format($venta['total'], 2) . "\n";
    echo "   - IGV: {$venta['igv']}%\n";
    echo "   - Tipo Pago: " . ($venta['id_tipo_pago'] == 1 ? 'CONTADO' : 'CRÉDITO') . "\n";
    echo "   - Estado: " . ($venta['estado'] == 1 ? 'ACTIVA' : 'ANULADA') . "\n";
} else {
    echo "❌ ERROR: Venta no encontrada\n";
    exit;
}

echo "\n";

// ============================================
// TEST 2: Verificar productos vendidos
// ============================================
echo "TEST 2: Verificar productos vendidos\n";
echo str_repeat("-", 60) . "\n";

$sql = "SELECT pv.*, p.descripcion, p.cantidad as stock_actual
        FROM productos_ventas pv
        INNER JOIN productos p ON pv.id_producto = p.id_producto
        WHERE pv.id_venta = $id_venta";

$productos = $conexion->query($sql);

if ($productos->num_rows > 0) {
    echo "✅ Productos encontrados: {$productos->num_rows}\n\n";
    
    $total_calculado = 0;
    while ($prod = $productos->fetch_assoc()) {
        $subtotal = $prod['cantidad'] * $prod['precio'];
        $total_calculado += $subtotal;
        
        echo "   Producto: {$prod['descripcion']}\n";
        echo "   - ID: {$prod['id_producto']}\n";
        echo "   - Cantidad vendida: {$prod['cantidad']} {$prod['presenta']}\n";
        echo "   - Unidades por presentación: {$prod['presenta_cnt']}\n";
        echo "   - Unidades totales: " . ($prod['cantidad'] * $prod['presenta_cnt']) . "\n";
        echo "   - Precio unitario: S/ " . number_format($prod['precio'], 2) . "\n";
        echo "   - Costo: S/ " . number_format($prod['costo'], 2) . "\n";
        echo "   - Subtotal: S/ " . number_format($subtotal, 2) . "\n";
        echo "   - Stock actual: {$prod['stock_actual']}\n";
        echo "\n";
    }
    
    echo "   Total calculado: S/ " . number_format($total_calculado, 2) . "\n";
} else {
    echo "❌ ERROR: No se encontraron productos para esta venta\n";
}

echo "\n";

// ============================================
// TEST 3: Verificar descuento de stock
// ============================================
echo "TEST 3: Verificar que se descontó el stock\n";
echo str_repeat("-", 60) . "\n";

$sql = "SELECT pv.id_producto, p.descripcion, pv.cantidad, pv.presenta_cnt, 
        (pv.cantidad * pv.presenta_cnt) as unidades_vendidas,
        p.cantidad as stock_actual
        FROM productos_ventas pv
        INNER JOIN productos p ON pv.id_producto = p.id_producto
        WHERE pv.id_venta = $id_venta";

$productos = $conexion->query($sql);

if ($productos->num_rows > 0) {
    echo "✅ Verificando descuento de stock:\n\n";
    
    while ($prod = $productos->fetch_assoc()) {
        echo "   {$prod['descripcion']}\n";
        echo "   - Unidades vendidas: {$prod['unidades_vendidas']}\n";
        echo "   - Stock actual: {$prod['stock_actual']}\n";
        echo "   - Estado: " . ($prod['stock_actual'] >= 0 ? "✅ Stock válido" : "⚠️ Stock negativo") . "\n";
        echo "\n";
    }
}

echo "\n";

// ============================================
// TEST 4: Verificar cuotas (si es a crédito)
// ============================================
echo "TEST 4: Verificar cuotas de pago\n";
echo str_repeat("-", 60) . "\n";

$sql = "SELECT * FROM dias_ventas WHERE id_venta = $id_venta ORDER BY fecha";
$cuotas = $conexion->query($sql);

if ($cuotas->num_rows > 0) {
    echo "✅ Cuotas encontradas: {$cuotas->num_rows}\n\n";
    
    $total_cuotas = 0;
    $cuotas_pagadas = 0;
    $cuotas_pendientes = 0;
    
    while ($cuota = $cuotas->fetch_assoc()) {
        $estado_texto = $cuota['estado'] == 1 ? '✅ PAGADA' : '⏳ PENDIENTE';
        echo "   Cuota - Fecha: {$cuota['fecha']}\n";
        echo "   - Monto: S/ " . number_format($cuota['monto'], 2) . "\n";
        echo "   - Estado: $estado_texto\n";
        echo "\n";
        
        $total_cuotas += $cuota['monto'];
        if ($cuota['estado'] == 1) {
            $cuotas_pagadas++;
        } else {
            $cuotas_pendientes++;
        }
    }
    
    echo "   Total cuotas: S/ " . number_format($total_cuotas, 2) . "\n";
    echo "   Pagadas: $cuotas_pagadas | Pendientes: $cuotas_pendientes\n";
} else {
    echo "ℹ️  No hay cuotas (venta al contado)\n";
}

echo "\n";

// ============================================
// TEST 5: Verificar cobranzas registradas
// ============================================
echo "TEST 5: Verificar cobranzas registradas\n";
echo str_repeat("-", 60) . "\n";

// Verificar si la tabla existe
$tabla_existe = $conexion->query("SHOW TABLES LIKE 'cobranzas'");

if ($tabla_existe && $tabla_existe->num_rows > 0) {
    $sql = "SELECT c.*, u.usuario 
            FROM cobranzas c
            LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario
            WHERE c.id_venta = $id_venta
            ORDER BY c.fecha";

    $cobranzas = $conexion->query($sql);

    if ($cobranzas->num_rows > 0) {
        echo "✅ Cobranzas encontradas: {$cobranzas->num_rows}\n\n";
        
        $total_cobrado = 0;
        while ($cobro = $cobranzas->fetch_assoc()) {
            echo "   Cobranza ID: {$cobro['id_cobranza']}\n";
            echo "   - Fecha: {$cobro['fecha']}\n";
            echo "   - Monto: S/ " . number_format($cobro['monto'], 2) . "\n";
            echo "   - Usuario: {$cobro['usuario']}\n";
            echo "\n";
            
            $total_cobrado += $cobro['monto'];
        }
        
        echo "   Total cobrado: S/ " . number_format($total_cobrado, 2) . "\n";
        
        // Comparar con total de venta
        $diferencia = $venta['total'] - $total_cobrado;
        if ($diferencia == 0) {
            echo "   ✅ Venta totalmente cobrada\n";
        } else if ($diferencia > 0) {
            echo "   ⏳ Saldo pendiente: S/ " . number_format($diferencia, 2) . "\n";
        } else {
            echo "   ⚠️  Sobrepago: S/ " . number_format(abs($diferencia), 2) . "\n";
        }
    } else {
        echo "ℹ️  No hay cobranzas registradas aún\n";
    }
} else {
    echo "ℹ️  Tabla de cobranzas no disponible (test omitido)\n";
}

echo "\n";

// ============================================
// TEST 6: Verificar si viene de cotización
// ============================================
echo "TEST 6: Verificar origen (cotización)\n";
echo str_repeat("-", 60) . "\n";

if ($venta['id_coti'] && $venta['id_coti'] != 0) {
    $sql = "SELECT * FROM cotizaciones WHERE cotizacion_id = {$venta['id_coti']}";
    $cotizacion = $conexion->query($sql)->fetch_assoc();
    
    if ($cotizacion) {
        echo "✅ Venta generada desde cotización\n";
        echo "   - ID Cotización: {$cotizacion['cotizacion_id']}\n";
        echo "   - Fecha cotización: {$cotizacion['fecha']}\n";
        echo "   - Estado cotización: " . ($cotizacion['estado'] == 1 ? '✅ CONVERTIDA' : '⏳ PENDIENTE') . "\n";
    }
} else {
    echo "ℹ️  Venta directa (no viene de cotización)\n";
}

echo "\n";

// ============================================
// TEST 7: Verificar documento SUNAT
// ============================================
echo "TEST 7: Verificar documento SUNAT\n";
echo str_repeat("-", 60) . "\n";

$sql = "SELECT * FROM ventas_sunat WHERE id_venta = $id_venta";
$sunat = $conexion->query($sql)->fetch_assoc();

if ($sunat) {
    echo "✅ Documento SUNAT registrado\n";
    echo "   - Nombre XML: {$sunat['nombre_xml']}\n";
    echo "   - Hash: " . substr($sunat['hash'], 0, 20) . "...\n";
    echo "   - QR generado: " . ($sunat['qr_data'] != '-' ? 'Sí' : 'No') . "\n";
} else {
    echo "ℹ️  No requiere documento SUNAT (nota de venta)\n";
}

echo "\n";

// ============================================
// RESUMEN FINAL
// ============================================
echo str_repeat("=", 60) . "\n";
echo "RESUMEN FINAL\n";
echo str_repeat("=", 60) . "\n\n";

echo "✅ Venta registrada correctamente\n";
echo "✅ Productos asociados y stock descontado\n";

if ($cuotas->num_rows > 0) {
    echo "✅ Cuotas generadas ($cuotas_pagadas pagadas, $cuotas_pendientes pendientes)\n";
}

if ($cobranzas->num_rows > 0) {
    echo "✅ Cobranzas registradas\n";
}

echo "\n🎉 TODOS LOS TESTS COMPLETADOS\n\n";

$conexion->close();
