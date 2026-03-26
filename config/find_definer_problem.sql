-- Buscar todos los objetos con DEFINER problemático

-- 1. Buscar VISTAS con definer magusqao
SELECT 
    'VIEW' AS tipo,
    TABLE_NAME AS nombre,
    DEFINER
FROM information_schema.VIEWS
WHERE TABLE_SCHEMA = DATABASE()
AND DEFINER LIKE '%magusqao%';

-- 2. Buscar TRIGGERS con definer magusqao
SELECT 
    'TRIGGER' AS tipo,
    TRIGGER_NAME AS nombre,
    EVENT_OBJECT_TABLE AS tabla,
    DEFINER
FROM information_schema.TRIGGERS
WHERE TRIGGER_SCHEMA = DATABASE()
AND DEFINER LIKE '%magusqao%';

-- 3. Buscar STORED PROCEDURES con definer magusqao
SELECT 
    'PROCEDURE' AS tipo,
    ROUTINE_NAME AS nombre,
    DEFINER
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE()
AND DEFINER LIKE '%magusqao%';

-- 4. Buscar FUNCIONES con definer magusqao
SELECT 
    'FUNCTION' AS tipo,
    ROUTINE_NAME AS nombre,
    DEFINER
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE()
AND ROUTINE_TYPE = 'FUNCTION'
AND DEFINER LIKE '%magusqao%';
