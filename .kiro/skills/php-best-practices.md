# PHP Best Practices Skill

Este skill proporciona guías y mejores prácticas para el desarrollo PHP en este proyecto.

## Estándares de Código

### PSR Standards
- Seguir PSR-12 para estilo de código
- Usar PSR-4 para autoloading de clases
- Implementar PSR-7 para mensajes HTTP cuando sea posible

### Estructura de Clases
```php
<?php

namespace App\Http\Controllers;

class ExampleController extends Controller
{
    // Propiedades privadas primero
    private $property;
    
    // Constructor
    public function __construct()
    {
        // Inicialización
    }
    
    // Métodos públicos
    public function index()
    {
        // Lógica
    }
    
    // Métodos privados al final
    private function helperMethod()
    {
        // Lógica auxiliar
    }
}
```

## Seguridad

### Validación de Datos
- Siempre validar y sanitizar inputs del usuario
- Usar prepared statements para consultas SQL
- Implementar CSRF protection en formularios
- Validar tipos de datos antes de procesarlos

### Ejemplo de Consulta Segura
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);
```

## Manejo de Errores

### Try-Catch Blocks
```php
try {
    // Código que puede fallar
    $result = $this->processData($data);
} catch (Exception $e) {
    // Log del error
    error_log($e->getMessage());
    // Respuesta al usuario
    return ['error' => 'Error procesando datos'];
}
```

## Base de Datos

### Transacciones
```php
try {
    $pdo->beginTransaction();
    // Operaciones
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}
```

## API Responses

### Formato Estándar
```php
// Éxito
return json_encode([
    'success' => true,
    'data' => $result,
    'message' => 'Operación exitosa'
]);

// Error
return json_encode([
    'success' => false,
    'error' => $errorMessage,
    'code' => $errorCode
]);
```

## Documentación

### PHPDoc
```php
/**
 * Procesa una venta y genera el documento
 *
 * @param array $ventaData Datos de la venta
 * @param int $clienteId ID del cliente
 * @return array Resultado de la operación
 * @throws Exception Si hay error en el proceso
 */
public function procesarVenta(array $ventaData, int $clienteId): array
{
    // Implementación
}
```

## Performance

- Usar caché cuando sea apropiado
- Optimizar consultas SQL (evitar N+1)
- Lazy loading para datos grandes
- Implementar paginación en listados

## Testing

- Escribir tests unitarios para lógica de negocio
- Tests de integración para APIs
- Validar edge cases y errores
