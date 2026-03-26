-- Script para recrear la vista view_ventas sin DEFINER problemático
-- Ejecutar en tu base de datos local

-- Primero eliminar la vista existente
DROP VIEW IF EXISTS `view_ventas`;

-- Recrear la vista sin DEFINER (usará el usuario actual)
CREATE VIEW `view_ventas` AS
SELECT 
    v.id_venta,
    CONCAT(de.abreviatura, '-', v.serie, '-', LPAD(v.numero, 8, '0')) AS cod_v,
    CONCAT(v.serie, '-', LPAD(v.numero, 8, '0')) AS sn_v,
    v.fecha AS fecha_emision,
    CONCAT(c.documento, ' | ', c.datos) AS datos_cl,
    (v.total / (1 + (v.igv / 100))) AS subtotal,
    (v.total - (v.total / (1 + (v.igv / 100)))) AS igv_v,
    v.total,
    de.descripcion AS doc_ventae,
    v.estado,
    v.id_empresa,
    v.sucursal
FROM ventas v
INNER JOIN clientes c ON v.id_cliente = c.id_cliente
INNER JOIN documentos_empresas de ON v.id_tido = de.id_tido AND v.id_empresa = de.id_empresa;
