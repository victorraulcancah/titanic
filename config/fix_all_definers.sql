-- Script para listar todas las vistas con DEFINER problemático
-- Ejecuta esto primero para ver qué vistas necesitan arreglarse

SELECT 
    TABLE_NAME,
    DEFINER,
    VIEW_DEFINITION
FROM information_schema.VIEWS
WHERE TABLE_SCHEMA = DATABASE()
AND DEFINER LIKE '%magusqao%';

-- Para arreglar cada vista, necesitarás:
-- 1. Hacer backup: SHOW CREATE VIEW nombre_vista;
-- 2. Eliminar: DROP VIEW nombre_vista;
-- 3. Recrear sin DEFINER usando la definición del backup
