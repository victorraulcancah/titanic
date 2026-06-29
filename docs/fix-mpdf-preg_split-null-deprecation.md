# Fix: Deprecation `preg_split(): Passing null to parameter #2 ($subject)` en mpdf

## Problema

En producción (PHP 8.1+), la página de reporte consolidado de camión mostraba cientos de errores:

```
Deprecated: preg_split(): Passing null to parameter #2 ($subject) of type string is deprecated in
/home/magusqao/titanicsac.com/utils/lib/mpdf/vendor/mpdf/mpdf/src/Otl.php on line 6138
```

Seguido de:

```
ERROR: Data has already been sent to output (...), unable to output PDF file
```

## Causa raíz

El método `_getOTLLangTag()` en `utils/lib/mpdf/vendor/mpdf/mpdf/src/Otl.php:6138` recibe `$ietf = null` y lo pasa directamente a `preg_split()`.

```php
// Antes del fix
if ($available == '') {
    return '';
}
$tags = preg_split('/-/', $ietf);  // $ietf es null → deprecation
```

`$ietf` viene de `$this->mpdf->currentLang`. Cuando esta propiedad no está definida (es `null`), se dispara el warning.

La versión de mpdf instalada (`v7.x`) solo requiere PHP hasta `~7.4.0`. En PHP 8.1+ pasar `null` a funciones internas que esperan `string` es deprecado.

Los warnings se imprimen como HTML antes de que mpdf intente enviar el PDF, causando el error "Data has already been sent to output" que impide la generación del PDF.

## Solución aplicada

Archivo: `utils/lib/mpdf/vendor/mpdf/mpdf/src/Otl.php`

```php
// Después del fix
if ($available == '' || $ietf === null || $ietf === '') {
    return '';
}
$tags = preg_split('/-/', $ietf);
```

## ¿Por qué solo falla en esta página y no en otras?

El "Imprimir Consolidado Logístico" funciona porque `$this->mpdf->currentLang` tiene un valor (ej. `"es-PE"`). La página de reporte consolidado de camión no lo inicializa, por lo que queda como `null`.

## Recomendación a futuro

Actualizar mpdf a `v8.x` que tiene soporte nativo para PHP 8. Alternativamente, inicializar `currentLang` antes de generar cualquier PDF:

```php
$this->mpdf->currentLang = $this->mpdf->currentLang ?? 'en-US';
```
