-- SOLO PARA DEMOSTRACIÓN LOCAL. Cada servicio tiene su propia base de datos.
CREATE DATABASE IF NOT EXISTS pos_ropa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS auth_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS inventario_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS ventas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS reportes_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'laravel_app'@'%' IDENTIFIED BY 'SoloDemoDb_2026!';
CREATE USER IF NOT EXISTS 'auth_app'@'%' IDENTIFIED BY 'SoloDemoDb_2026!';
CREATE USER IF NOT EXISTS 'inventario_app'@'%' IDENTIFIED BY 'SoloDemoDb_2026!';
CREATE USER IF NOT EXISTS 'ventas_app'@'%' IDENTIFIED BY 'SoloDemoDb_2026!';
CREATE USER IF NOT EXISTS 'reportes_app'@'%' IDENTIFIED BY 'SoloDemoDb_2026!';
CREATE USER IF NOT EXISTS 'replicador'@'%' IDENTIFIED BY 'SoloDemoRepl_2026!';
GRANT ALL PRIVILEGES ON pos_ropa.* TO 'laravel_app'@'%';
GRANT ALL PRIVILEGES ON auth_db.* TO 'auth_app'@'%';
GRANT ALL PRIVILEGES ON inventario_db.* TO 'inventario_app'@'%';
GRANT ALL PRIVILEGES ON ventas_db.* TO 'ventas_app'@'%';
GRANT ALL PRIVILEGES ON reportes_db.* TO 'reportes_app'@'%';
GRANT REPLICATION SLAVE ON *.* TO 'replicador'@'%';

CREATE TABLE IF NOT EXISTS auth_db.usuarios (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL,
 correo VARCHAR(160) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 rol ENUM('administrador','cajero','cliente') NOT NULL DEFAULT 'cliente'
);
CREATE TABLE IF NOT EXISTS inventario_db.productos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(140) NOT NULL,
 precio DECIMAL(11,2) NOT NULL,
 activo BOOLEAN NOT NULL DEFAULT TRUE
);
CREATE TABLE IF NOT EXISTS inventario_db.variantes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 producto_id BIGINT UNSIGNED NOT NULL,
 talla VARCHAR(25) NOT NULL,
 color VARCHAR(40) NOT NULL,
 stock INT UNSIGNED NOT NULL DEFAULT 0,
 updated_at DATETIME NULL,
 FOREIGN KEY (producto_id) REFERENCES inventario_db.productos(id)
);
CREATE TABLE IF NOT EXISTS ventas_db.ventas (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 usuario_id BIGINT UNSIGNED NOT NULL,
 variante_id BIGINT UNSIGNED NOT NULL,
 cantidad INT UNSIGNED NOT NULL,
 subtotal DECIMAL(11,2) NOT NULL,
 iva_incluido DECIMAL(11,2) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS reportes_db.consultas (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 usuario_id BIGINT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO auth_db.usuarios (id,nombre,correo,password_hash,rol)
VALUES (1,'Administrador de prueba','admin@demo.local','$2y$12$V9PQjP0EAR7OVcrs5E8EWetHGmENCYiQD83h6OFAYpXnhbeL.pUZ.','administrador'),
       (2,'Cajero de prueba','cajero@demo.local','$2y$12$V9PQjP0EAR7OVcrs5E8EWetHGmENCYiQD83h6OFAYpXnhbeL.pUZ.','cajero');
INSERT IGNORE INTO inventario_db.productos (id,nombre,precio) VALUES
 (1,'Playera deportiva demostración',175.00), (2,'Zapatos casuales demostración',500.00);
INSERT IGNORE INTO inventario_db.variantes (id,producto_id,talla,color,stock) VALUES
 (1,1,'M','Negro',10),(2,1,'S','Rojo',5),(3,2,'9','Negro',10);
