# Workflow del Proyecto - Sistema de Facturación

Este skill documenta cómo se está trabajando actualmente en el proyecto de facturación electrónica.

## Arquitectura del Proyecto

### Patrón MVC Personalizado

El proyecto usa un patrón MVC sin framework, con estructura manual:

```
app/
├── http/
│   ├── controllers/     # Lógica de negocio y endpoints
│   └── middleware/      # Autenticación y validación
├── models/             # Modelos de datos y consultas SQL
└── clases/             # Clases auxiliares (SUNAT, Email, etc.)
```

### Flujo de Datos Típico

1. **Request** → Middleware (validación) → Controller
2. **Controller** → Instancia modelos → Ejecuta lógica
3. **Modelo** → Ejecuta SQL directo con mysqli
4. **Response** → JSON encode del resultado

## Patrones de Código Actuales

### 1. Estructura de Controladores

```php
class VentasController extends Controller
{
    private $venta;
    private $sunatApi;
    private $conexion;
    
    public function __construct()
    {
        $this->venta = new Venta();
        $this->sunatApi = new SunatApi();
        $this->conexion = (new Conexion())->getConexion();
    }
    
    public function metodoPublico()
    {
        // Lógica del endpoint
        return json_encode($resultado);
    }
}
```

**Características:**
- Propiedades privadas para modelos y conexiones
- Constructor inicializa dependencias
- Métodos públicos son endpoints
- Siempre retornan JSON

### 2. Estructura de Modelos

```php
class Venta
{
    // Propiedades privadas que mapean campos de BD
    private $id_venta;
    private $fecha;
    private $total;
    private $conectar;
    
    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }
    
    // Getters y Setters para cada propiedad
    public function getIdVenta() { return $this->id_venta; }
    public function setIdVenta($id_venta) { $this->id_venta = $id_venta; }
    
    // Método para ejecutar SQL directo
    public function exeSQL($sql)
    {
        return $this->conectar->query($sql);
    }
    
    // Métodos CRUD
    public function insertar() { /* SQL INSERT */ }
    public function editar($id) { /* SQL UPDATE */ }
    public function anular() { /* SQL UPDATE estado */ }
}
```

**Características:**
- Propiedades privadas con getters/setters
- Método `exeSQL()` para consultas directas
- SQL embebido en métodos (no ORM)
- Uso de mysqli directamente

### 3. Manejo de Datos POST

```php
// Recibir datos del formulario
$c_venta->setFecha($_POST['fecha']);
$c_venta->setTotal(filter_input(INPUT_POST, 'total'));

// Arrays JSON desde POST
$array_detalle = json_decode($_POST['listaPro'], true);
$listaPagos = json_decode($_POST['dias_lista'], true);

// Procesar arrays
foreach ($array_detalle as $fila) {
    $c_detalle->setIdProducto($fila['productoid']);
    $c_detalle->insertar();
}
```

### 4. Respuestas JSON Estándar

```php
// Estructura de respuesta exitosa
$resultado = ["res" => true];
$resultado["data"] = $datos;
$resultado["nomFact"] = $archivo;
return json_encode($resultado);

// Estructura de respuesta con error
$resultado = ["res" => false];
$resultado["msg"] = "Mensaje de error";
return json_encode($resultado);
```

### 5. Integración SUNAT

```php
// Preparar datos para SUNAT
$dataSend = [];
$dataSend['cliente'] = json_encode([
    'doc_num' => $c_cliente->getDocumento(),
    'nom_RS' => $c_cliente->getDatos(),
    'direccion' => $direccion
]);
$dataSend['productos'] = json_encode($productos);
$dataSend['total'] = $total;

// Generar XML según tipo de documento
if ($c_venta->getIdTido() == 1) {
    $dataResp = $this->sunatApi->genBoletaXML($dataSend);
} else {
    $dataResp = $this->sunatApi->genFacturaXML($dataSend);
}

// Guardar datos de SUNAT
if ($dataResp["res"]) {
    $c_sunat->setHash($dataResp['data']['hash']);
    $c_sunat->setNombreXml($dataResp['data']['nombre_archivo']);
    $c_sunat->insertar();
}
```

## Convenciones de Código

### Variables y Nomenclatura

- **Modelos:** `$c_venta`, `$c_cliente`, `$c_detalle` (prefijo `c_`)
- **Resultados:** `$resultado`, `$respuesta`, `$lista`
- **SQL:** `$sql` para queries
- **Datos SUNAT:** `$dataSend`, `$dataResp`

### Sesiones

```php
$_SESSION['usuario_fac']    // ID del usuario actual
$_SESSION['id_empresa']     // ID de la empresa
$_SESSION['sucursal']       // ID de la sucursal
$_SESSION['rol']            // Rol del usuario (1=admin)
```

### Consultas SQL

```php
// Patrón común: SQL directo con interpolación
$sql = "SELECT * FROM ventas 
        WHERE id_empresa = '$this->id_empresa' 
        AND sucursal = '{$_SESSION['sucursal']}'";

// Ejecutar y procesar
$result = $this->conectar->query($sql);
foreach ($result as $row) {
    $lista[] = $row;
}
```

⚠️ **Nota de Seguridad:** El código actual usa interpolación directa. Para nuevas funcionalidades, usar prepared statements.

## Flujos de Trabajo Comunes

### Crear una Venta

1. Validar cliente (crear si no existe)
2. Obtener datos de empresa y configuración
3. Crear registro de venta
4. Insertar detalles (productos o servicios)
5. Insertar cuotas de pago si aplica
6. Generar XML para SUNAT (si es factura/boleta)
7. Guardar datos de SUNAT
8. Retornar URLs de PDF

### Editar una Venta

1. Cargar venta existente
2. Actualizar datos del cliente
3. Calcular diferencias en productos (stock)
4. Eliminar detalles antiguos
5. Insertar nuevos detalles
6. Actualizar registro de venta
7. Retornar confirmación

### Anular una Venta

1. Cambiar estado a '2' (anulado)
2. Registrar en `ventas_anuladas`
3. Opcionalmente devolver stock
4. Generar comunicación de baja a SUNAT

## Archivos de Respaldo

El proyecto mantiene archivos con sufijos `BK`, `BK2`, etc.:

```
VentasController.php       # Versión actual
VentasControllerBK.php     # Respaldo anterior
```

**Regla:** No modificar archivos BK a menos que sea para restaurar funcionalidad.

## Logging

```php
// Crear log de operaciones
$dataSaveLog = "Venta: {$id}, fecha: " . date("Y-m-d") . "\n";
$dataSaveLog .= "Detalle: " . $info . "\n";

file_put_contents(
    "files/log/ventas/Venta_{$id}.txt", 
    $dataSaveLog
);
```

## Validaciones Comunes

```php
// Validar cliente obligatorio
if (empty($num_doc) || empty($nom_cli)) {
    echo json_encode([
        'res' => false,
        'msj' => 'El cliente es obligatorio'
    ]);
    return;
}

// Validar documento existente
if (!$c_cliente->verificarDocumento()) {
    $c_cliente->insertar();
}

// Manejar valores vacíos
if (trim($direccion) == "") {
    $direccion = '-';
}
```

## Manejo de Monedas

```php
// Conversión según moneda
$precio = $_POST['moneda'] == 1 
    ? $fila['precioVenta']              // PEN
    : $fila['precioVenta'] / $_POST['tc']; // USD con tipo de cambio
```

## Presentaciones de Productos

```php
// Manejar presentaciones (cajas, paquetes, etc.)
$presenta = isset($fila['presenta']) 
    ? $fila['presenta'] 
    : $fila['presentacion'];

$presenta_cnt = isset($fila['presenta_cnt']) 
    ? $fila['presenta_cnt'] 
    : $fila['presentacionCnt'];

// Calcular cantidad real
$presenta_cnt = ($presenta_cnt == 0) ? 1 : $presenta_cnt;
$cantidad_real = $fila['cantidad'] * $presenta_cnt;
```

## Debugging

```php
// Guardar datos para debug
file_put_contents("debug.json", json_encode($dataSend));

// Imprimir y detener
echo '<pre>';
print_r($array);
echo '</pre>';
exit();
```

## Mejores Prácticas del Proyecto

1. **Siempre retornar JSON** desde controladores
2. **Usar transacciones** para operaciones múltiples (aunque no siempre se hace)
3. **Validar datos** antes de insertar
4. **Mantener logs** de operaciones críticas
5. **Verificar sesiones** en cada endpoint
6. **Manejar errores** con try-catch cuando sea posible
7. **Documentar cambios** en archivos de respaldo

## Tipos de Documentos (id_tido)

- `1` = Boleta
- `2` = Factura
- `3` = Nota de Crédito
- `4` = Nota de Débito
- `6` = Nota de Venta (sin SUNAT)

## Estados

- `1` = Activo/Vigente
- `2` = Anulado
- `0` = Pendiente (en algunos casos)
