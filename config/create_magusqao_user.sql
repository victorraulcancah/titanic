-- Crear el usuario magusqao en tu base de datos local
-- Esto resolverá el error de DEFINER

CREATE USER IF NOT EXISTS 'magusqao'@'localhost' IDENTIFIED BY 'local123';
GRANT ALL PRIVILEGES ON *.* TO 'magusqao'@'localhost';
FLUSH PRIVILEGES;

-- Verificar que se creó correctamente
SELECT User, Host FROM mysql.user WHERE User = 'magusqao';
