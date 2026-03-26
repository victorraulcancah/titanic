-- Desactivar only_full_group_by en MySQL
-- Ejecuta este comando en tu MySQL local

SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));

-- Para verificar el modo actual:
SELECT @@sql_mode;

-- Para hacer el cambio permanente, edita tu archivo my.ini o my.cnf:
-- [mysqld]
-- sql_mode=STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION
