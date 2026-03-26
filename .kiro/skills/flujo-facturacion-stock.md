# Flujo de Facturación, Pedidos, Cuentas por Cobrar y Manejo de Stock

## Resumen Ejecutivo

Este documento describe el flujo completo del sistema de ventas, desde la cotización hasta el cobro, incluyendo el manejo de inventario (stock).

## 1. COTIZACIONES (Pedidos)

**Controlador**: `app/http/controllers/CotizacionesController.php`

### Características:
- Las cotizaciones son pedidos que NO afectan el stock
- Solo registran la intención de venta
- Pueden convertirse en ventas posteriormente
- Tienen estado: 0 = Pendiente, 1 = Convertida a venta

### Campos importantes:
- `cotizacion_id`: ID único de la cotización
- `id_cliente`: Cliente asociado
- `total`: Monto total
- `fecha`: Fecha de emisión
- `estado`: 0 (pendiente) o 1 (convertida)
- `cuotas_cotizacion`: Tabla relacionada con cuotas de pago

### Flujo:
1. Se crea cotización con productos y cantidades
2. NO se descuenta stock
3. Queda pendiente hasta que se convierta en venta o se cancele

---

## 2. VENTAS (Facturación)

**Controlador**: `app/http/controllers/VentasController.php`
**Modelo**: `app/models/Venta.php`
**Modelo Detalle**: `app/models/ProductoVenta.php`

### Tipos de Venta:
1. **Venta de Productos** (`tipoventa = 1`): Descuenta stock
2. **Venta de Servicios** (`tipoventa = 2`): NO descuenta stock

### Flujo de Venta de Productos:

#### A. Registro de Venta (`guardarVentas()`)

1. **Validación de Cliente**:
   - Cliente es obligatorio (documento y nombre)
   - Si no existe, se crea automáticamente

2. **Registro de Venta**:
   - Se inserta en tabla `ventas`
   - Se registran métodos de pago en `ventas_pagos`
   - Si viene de cotización, se actualiza estado: `UPDATE cotizaciones SET estado = 1`

3. **Registro de Productos y DESCUENTO DE STOCK**:
   ```php
   // En ProductoVenta::insertar()
   // 1. Insertar detalle de venta
   INSERT INTO productos_ventas VALUES (...)
   
   // 2. DESCUENTO DE STOCK (AQUÍ SE DESCUENTA)
   $cntRestante = $this->cantidad * $this->presenta_cnt;
   UPDATE productos SET cantidad = cantidad - $cntRestante 
   WHERE id_producto = '$this->id_producto'
   ```

4. **Registro de Cuotas** (si es venta a crédito):
   - Se insertan en tabla `dias_ventas`
   - Cada cuota tiene: monto, fecha, estado (0 = pendiente)

5. **Generación de Documentos SUNAT**:
   - Si es Boleta (tipo_doc = 1) o Factura (tipo_doc = 2)
   - Se genera XML y se registra en `ventas_sunat`

#### B. Edición de Venta (`editVentaProducto()`)

**IMPORTANTE**: Al editar una venta, el stock se maneja así:

1. **Devolución de Stock**:
   ```php
   // Se devuelve TODO el stock de la venta original
   foreach ($detalle_venta as $detalle) {
       $cantidad = $detalle['cantidad'] * $detalle['presenta_cnt'];
       UPDATE productos SET cantidad = cantidad + $cantidad
   }
   ```

2. **Eliminación de Detalle**:
   ```php
   DELETE FROM productos_ventas WHERE id_venta = ...
   ```

3. **Nuevo Registro**:
   - Se vuelven a insertar los productos con las nuevas cantidades
   - Al insertar, `ProductoVenta::insertar()` descuenta el stock nuevamente

#### C. Anulación de Venta

**Controlador**: `VentasController::anularVenta()`

- Cambia estado de venta a anulada
- Registra en tabla `ventas_anuladas`
- **NOTA**: El código actual NO devuelve el stock automáticamente al anular

---

## 3. COMPRAS (Incremento de Stock)

**Controlador**: `app/http/controllers/ComprasController.php`
**Modelo**: `app/models/Compra.php`

### Flujo de Compra:

1. **Registro de Proveedor**:
   - Si no existe, se crea en tabla `proveedores`

2. **Registro de Compra**:
   - Se inserta en tabla `compras`

3. **INCREMENTO DE STOCK**:
   ```php
   // En Compra::updateStock()
   UPDATE productos SET cantidad = cantidad + $cantidad 
   WHERE id_producto = $idProducto
   ```

4. **Registro de Productos**:
   - Se insertan en `productos_compras`

5. **Registro de Cuotas** (si es compra a crédito):
   - Se insertan en `dias_compras`

---

## 4. CUENTAS POR COBRAR (Cobranzas)

**Controlador**: `app/http/controllers/CobranzaController.php`
**Modelo**: `app/models/Cobranza.php`
**Vista**: `resources/views/fragment-views/cliente/cobranzas.php`

### Tablas Relacionadas:
- `dias_ventas`: Cuotas de ventas directas a crédito
- `cuotas_cotizacion`: Cuotas de cotizaciones a crédito
- **IMPORTANTE**: Las ventas que vienen de cotizaciones usan `cuotas_cotizacion`, NO `dias_ventas`

### Flujo:

#### A. Generación de Cuotas

**Caso 1: Cotización a crédito**
```php
// Al crear cotización con id_tipo_pago = 2 (crédito)
INSERT INTO cuotas_cotizacion (id_coti, monto, fecha, estado)
VALUES (46814, 880.00, '2026-03-26', 0)
// estado = 0 (pendiente)
```

**Caso 2: Venta directa a crédito**
```php
// Al crear venta con id_tipo_pago = 2 (crédito)
INSERT INTO dias_ventas (id_venta, monto, fecha, estado)
VALUES (227, 880.00, '2026-03-26', 0)
// estado = 0 (pendiente)
```

#### B. Vista de Cobranzas

La vista `http://well-known.test/cobranzas` muestra un UNION de:

1. **Ventas a crédito**:
```sql
SELECT 
    'v' AS tipo_co,
    v.id_venta,
    CONCAT(v.serie, ' | ', v.numero) AS factura,
    v.fecha_emision,
    v.fecha_vencimiento,
    CONCAT(c.documento, ' | ', c.datos) AS cliente,
    v.total,
    SUM(CASE WHEN dv.estado = '1' THEN dv.monto ELSE 0 END) AS pagado,
    (v.total - SUM(...)) AS saldo
FROM ventas v
INNER JOIN dias_ventas dv ON v.id_venta = dv.id_venta
WHERE v.id_tipo_pago = 2
```

2. **Cotizaciones a crédito**:
```sql
SELECT 
    'c' AS tipo_co,
    co.cotizacion_id AS id_venta,
    CONCAT('#', co.numero) AS factura,
    co.fecha AS fecha_emision,
    co.total,
    (SELECT SUM(monto) FROM cuotas_cotizacion WHERE estado = 1) AS pagado,
    (co.total - pagado) AS saldo
FROM cotizaciones co
WHERE co.id_tipo_pago = 2
```

#### C. Registro de Pago

**Flujo en la interfaz**:
1. Usuario hace clic en botón "Cuotas" (ícono ojo)
2. Se abre modal mostrando cuotas de `cuotas_cotizacion` o `dias_ventas`
3. Usuario selecciona método de pago y hace clic en "Pagar"
4. Se actualiza el estado de la cuota:

```php
// Para cotizaciones
UPDATE cuotas_cotizacion 
SET estado = 1, 
    fecha_pago_real = NOW(),
    id_usuario = {usuario_actual}
WHERE cuota_coti_id = {id}

// Para ventas directas
UPDATE dias_ventas 
SET estado = 1,
    fecha_pago_real = NOW()
WHERE dias_venta_id = {id}
```

#### D. Cálculo de Saldos

El sistema calcula automáticamente:
- **Total**: Monto total de la venta/cotización
- **Pagado**: Suma de cuotas con `estado = 1`
- **Saldo**: Total - Pagado

**Estados de situación**:
- **Pagado**: `total == pagado`
- **Vencido**: `total > pagado` y `fecha_vencimiento < hoy`
- **Pendiente**: `total > pagado` y `fecha_vencimiento >= hoy`

---

## 5. FLUJO COMPLETO: COTIZACIÓN → VENTA → COBRANZA

### Ejemplo Real (ID Cotización: 46814, ID Venta: 227)

```
PASO 1: COTIZACIÓN A CRÉDITO
├─→ Se crea cotización ID 46814
├─→ Cliente: 19933404 | D-MARIO TOVAR (GAMBETA)
├─→ Total: S/ 880.00
├─→ id_tipo_pago = 2 (crédito)
└─→ Se insertan cuotas en cuotas_cotizacion:
    └─→ INSERT INTO cuotas_cotizacion (id_coti, monto, fecha, estado)
        VALUES (46814, 880.00, '2026-03-26', 0)

PASO 2: CONVERSIÓN A VENTA
├─→ Usuario hace clic en "Vender" en vista de cotizaciones
├─→ Se crea venta ID 227 (NV01-2945)
├─→ Se descuenta stock de productos:
│   ├─→ ARROZ BUEN GALLO: 4 unidades
│   └─→ LINAZA NACIONAL: 10 kg
├─→ Se actualiza cotización:
│   └─→ UPDATE cotizaciones SET estado = 1 WHERE cotizacion_id = 46814
└─→ Las cuotas PERMANECEN en cuotas_cotizacion (NO se copian a dias_ventas)

PASO 3: COBRANZA
├─→ En vista /cobranzas se muestra:
│   ├─→ ID: 46814 (cotización)
│   ├─→ Factura: #46814
│   ├─→ Total: S/ 880.00
│   ├─→ Pagado: S/ 0.00
│   └─→ Saldo: S/ 880.00
├─→ Usuario hace clic en "Cuotas"
├─→ Se muestra modal con cuota pendiente
├─→ Usuario selecciona método de pago y hace clic en "Pagar"
└─→ Se actualiza: UPDATE cuotas_cotizacion SET estado = 1
    └─→ Ahora: Pagado = S/ 880.00, Saldo = S/ 0.00
```

### Punto Crítico

**Las ventas que vienen de cotizaciones a crédito**:
- ✅ Se registran en tabla `ventas`
- ✅ Descuentan stock
- ✅ Las cuotas quedan en `cuotas_cotizacion`
- ❌ NO se crean cuotas en `dias_ventas`
- ✅ En vista de cobranzas se consultan ambas tablas (UNION)

**Las ventas directas a crédito**:
- ✅ Se registran en tabla `ventas`
- ✅ Descuentan stock
- ✅ Se crean cuotas en `dias_ventas`
- ❌ NO usan `cuotas_cotizacion`

---

## 5. MANEJO DE PRESENTACIONES Y UNIDADES

### Concepto:
Los productos pueden tener diferentes presentaciones (ej: caja, unidad, paquete)

### Campos:
- `presenta`: Nombre de la presentación (ej: "CAJA", "UNIDAD")
- `presenta_cnt`: Cantidad de unidades por presentación (ej: 1 caja = 12 unidades)

### Cálculo de Stock:
```php
$cntRestante = $cantidad * $presenta_cnt;
// Ejemplo: 5 cajas * 12 unidades = 60 unidades a descontar
```

---

## 6. INGRESO/EGRESO MANUAL DE ALMACÉN

**Controlador**: `VentasController::ingresoAlmacen()` y `egresoAlmacen()`

### Ingreso Manual:
```php
UPDATE productos SET cantidad = cantidad + '{$_POST['cantidad']}' 
WHERE id_producto = '{$_POST['id_producto']}'
```

### Egreso Manual:
**NOTA**: El código está comentado actualmente
```php
// UPDATE productos SET cantidad = cantidad - '{$_POST['cantidad']}' 
// WHERE id_producto = '{$_POST['id_producto']}'
```

---

## 7. DEVOLUCIONES

**Tabla**: `devoluciones_nv`

### Flujo:
- Al editar una venta con tipo_doc = 6 (Nota de Crédito)
- Se registran los cambios de cantidad en `devoluciones_nv`
- Incluye: id_venta, id_producto, cantidad, signo (+/-)

---

## 8. RESUMEN DEL FLUJO COMPLETO

```
COTIZACIÓN A CRÉDITO (Pedido)
    ↓ (NO afecta stock)
    ├─→ Se crean cuotas en cuotas_cotizacion (estado=0)
    ↓
    ↓ [Botón "Vender"]
    ↓
VENTA (Facturación)
    ↓
    ├─→ Venta de PRODUCTOS → DESCUENTA STOCK (ProductoVenta::insertar)
    │                         └─→ UPDATE productos SET cantidad = cantidad - X
    │
    ├─→ Venta de SERVICIOS → NO afecta stock
    │
    └─→ Si viene de cotización:
        ├─→ UPDATE cotizaciones SET estado = 1
        └─→ Las cuotas quedan en cuotas_cotizacion (NO se copian)
    ↓
COBRANZAS (Vista /cobranzas)
    ↓
    ├─→ Muestra UNION de:
    │   ├─→ Ventas directas a crédito (dias_ventas)
    │   └─→ Cotizaciones a crédito (cuotas_cotizacion)
    ↓
    ├─→ Usuario hace clic en "Cuotas"
    ├─→ Se muestra modal con cuotas pendientes
    ├─→ Usuario selecciona método de pago
    └─→ Se actualiza estado de cuota a 1 (pagada)

COMPRAS
    └─→ INCREMENTA STOCK (Compra::updateStock)
        └─→ UPDATE productos SET cantidad = cantidad + X
```

---

## 9. PUNTOS CRÍTICOS Y CONSIDERACIONES

### ✅ Stock se descuenta en:
1. **Venta de productos**: Al insertar en `productos_ventas` (método `ProductoVenta::insertar()`)
2. **Edición de venta**: Se devuelve stock original y se descuenta el nuevo

### ✅ Stock se incrementa en:
1. **Compras**: Al registrar compra (método `Compra::updateStock()`)
2. **Ingreso manual**: Método `ingresoAlmacen()` (activo)

### ⚠️ Stock NO se devuelve en:
1. **Anulación de venta**: El código actual NO devuelve stock automáticamente
2. **Egreso manual**: El código está comentado

### 📋 Validaciones importantes:
- Cliente es obligatorio en ventas
- Las presentaciones multiplican la cantidad (`cantidad * presenta_cnt`)
- Las cotizaciones NO afectan stock hasta convertirse en venta
- Los servicios NO afectan stock

---

## 10. TABLAS DE BASE DE DATOS PRINCIPALES

| Tabla | Descripción |
|-------|-------------|
| `productos` | Inventario con campo `cantidad` (stock) |
| `cotizaciones` | Pedidos sin afectar stock |
| `ventas` | Registro de ventas |
| `productos_ventas` | Detalle de productos vendidos |
| `ventas_servicios` | Detalle de servicios vendidos |
| `dias_ventas` | Cuotas de ventas a crédito |
| `cobranzas` | Registro de pagos recibidos |
| `compras` | Registro de compras |
| `productos_compras` | Detalle de productos comprados |
| `dias_compras` | Cuotas de compras a crédito |
| `ventas_sunat` | Datos de facturación electrónica |
| `devoluciones_nv` | Registro de devoluciones |

---

## 11. TIPOS DE DOCUMENTOS

| ID | Tipo | Afecta Stock |
|----|------|--------------|
| 1 | Boleta | Sí (si es venta de productos) |
| 2 | Factura | Sí (si es venta de productos) |
| 6 | Nota de Crédito | Sí (devuelve stock) |
| Otros | Varios | Depende del tipo |

---

## 12. MÉTODOS DE PAGO

| ID | Tipo | Genera Cuotas |
|----|------|---------------|
| 1 | Contado | No |
| 2 | Crédito | Sí (tabla dias_ventas) |

---

## Última actualización
Marzo 2026
