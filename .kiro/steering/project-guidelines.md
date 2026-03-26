---
inclusion: auto
---

# Guías del Proyecto

## Estructura del Proyecto

Este es un proyecto PHP con arquitectura MVC personalizada:

- `app/http/controllers/` - Controladores de la aplicación
- `app/models/` - Modelos de datos
- `app/clases/` - Clases auxiliares (Email, SUNAT API, etc.)
- `app/http/middleware/` - Middleware para autenticación y validación

## Convenciones de Nombres

### Archivos
- Controladores: `NombreController.php`
- Modelos: `NombreModelo.php`
- Clases: `NombreClase.php`

### Clases y Métodos
- Clases: PascalCase (`ClientesController`)
- Métodos: camelCase (`obtenerCliente()`)
- Constantes: UPPER_SNAKE_CASE (`MAX_INTENTOS`)

## Integración SUNAT

El proyecto incluye integración con SUNAT para facturación electrónica:
- `SunatApi.php` y `SunatApi2.php` - APIs de SUNAT
- `DocumentoSunat.php` - Modelo para documentos
- `VentaSunat.php` - Ventas para SUNAT

## Base de Datos

- Usar prepared statements siempre
- Implementar transacciones para operaciones múltiples
- Validar datos antes de insertar/actualizar

## Archivos de Respaldo

Los archivos con sufijo `BK`, `BK2`, etc. son respaldos. No modificar a menos que sea necesario restaurar funcionalidad.

## Módulos Principales

1. **Ventas** - Gestión de ventas y facturación
2. **Compras** - Registro de compras a proveedores
3. **Cobranzas** - Control de cobros y pagos
4. **Inventario** - Control de productos
5. **Reportes** - Generación de reportes diversos
6. **Guías de Remisión** - Documentos de transporte

## Middleware

- `LoginMiddleware` - Validar sesión activa
- `AdminMiddleware` - Validar permisos de administrador
- `ValidarTokenMiddleware` - Validar tokens de API

## Mejores Prácticas

1. Siempre validar y sanitizar inputs
2. Usar try-catch para operaciones críticas
3. Documentar métodos complejos con PHPDoc
4. Mantener controladores delgados, lógica en modelos
5. Retornar respuestas JSON consistentes
