# ![Magustechnologies](https://magustechnologies.com/assets/images/magus.png) 

#Manejador de envíos a SUNAT y Generador de XML

### Funciones
- Generación de XML en el estándar UBL 2.0, 2.1
- Generación comprobantes de contingencia.
- Firma digital de XML
- Envío a servicio de SUNAT
- Procesamiento del CDR (Comprobante de Recepción)
- Extracción del Hash o Valor Resumen 

 

**Requerimientos**
- `PHP 7.2` o superior
- Activar extensiones: `openssl`, `soap`

Lista de json que se debe enviar y el sistema lo pueda procesar.

### Envio a Sunat el XML Factura, Boleta, Nota de Creditos y Debido
````text
url: /send_facturacion.php
````
```json
{
    "endpoints":"beta",//production
    "certGlobal":false,
    "lotes": true, // true, por lote maximo 20 entre facturas y boletas, false, solo una se aceptara
    "ruta_xml":'',//http://localhost/files/xml/ <- el "/" al final
    "ruc":"20123456789",
    "clave_sol":"moddatos",
    "usuario_sol":"MODDATOS",
    "lote":[
        {
            "metas":{},
            "nombre_XML":"",
            "contenido_XML":""
        }
    ],//solo si lotes es true
    "documento":{
        "metas":{},
        "nombre_XML":"",
        "contenido_XML":""
    }//solo si lote es false
}
```
Retorna

````json
{
  "res":true,
  "msg": "",
  "resultados": [
    {
      "res":true,
      "metas": {},
      "msg": "",
      "nombre": "",
      "datos": ""
    }
  ]
}
````

### Ejecutar

Abrir la consola y ejecutar el siguiente comando.

```bash
php -S 0.0.0.0:8080
```

Finalmente navegar a http://localhost:8080/
> Los xml, pdf y cdr (archivos zip) seran guardados en la carptea `files`.

