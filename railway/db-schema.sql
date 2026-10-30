-- Esquema separado por servicio; sin credenciales ni datos demo.
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

-- Datos neutros para demostrar el flujo REST distribuido (BD separada del POS real).
INSERT IGNORE INTO inventario_db.productos (id,nombre,precio) VALUES
 (1,'Playera deportiva - servicio REST',175.00),(2,'Zapatos casuales - servicio REST',500.00);
INSERT IGNORE INTO inventario_db.variantes (id,producto_id,talla,color,stock) VALUES
 (1,1,'M','Negro',10),(2,1,'S','Rojo',5),(3,2,'9','Negro',10);
