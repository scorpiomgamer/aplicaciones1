-- Base de datos para Panthera
-- Sistema de gestión para tienda de accesorios

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS pantera CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pantera;

-- Tabla de tipos de documento
CREATE TABLE tipos_documento (
    id_tipo_documento INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de usuarios/clientes
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    edad INT NOT NULL CHECK (edad >= 5 AND edad <= 100),
    direccion VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20) NOT NULL,
    id_tipo_documento INT NOT NULL,
    numero_documento VARCHAR(50) NOT NULL,
    informacion_adicional TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_tipo_documento) REFERENCES tipos_documento(id_tipo_documento),
    INDEX idx_email (email),
    INDEX idx_documento (numero_documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de categorías de productos
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de productos
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL CHECK (precio >= 0),
    id_categoria INT NOT NULL,
    stock INT DEFAULT 0 CHECK (stock >= 0),
    imagen_url VARCHAR(255),
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria),
    INDEX idx_categoria (id_categoria),
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de pedidos
CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'procesando', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    total DECIMAL(10, 2) NOT NULL CHECK (total >= 0),
    direccion_envio VARCHAR(255) NOT NULL,
    notas_envio TEXT,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    INDEX idx_usuario (id_usuario),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de detalle de pedidos
CREATE TABLE detalle_pedidos (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    precio_unitario DECIMAL(10, 2) NOT NULL CHECK (precio_unitario >= 0),
    subtotal DECIMAL(10, 2) NOT NULL CHECK (subtotal >= 0),
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    INDEX idx_pedido (id_pedido),
    INDEX idx_producto (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de contactos/mensajes
CREATE TABLE contactos (
    id_contacto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    email VARCHAR(100),
    telefono VARCHAR(20),
    mensaje TEXT NOT NULL,
    fecha_contacto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atendido BOOLEAN DEFAULT FALSE,
    INDEX idx_atendido (atendido),
    INDEX idx_fecha (fecha_contacto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar tipos de documento
INSERT INTO tipos_documento (nombre, descripcion) VALUES
('Cedula ciudadana', 'Cédula de ciudadanía colombiana'),
('Cedula extrangera', 'Cédula de extranjería'),
('Targeta de identidad', 'Tarjeta de identidad para menores'),
('Pasaporte', 'Pasaporte internacional');

-- Insertar categorías de productos
INSERT INTO categorias (nombre, descripcion) VALUES
('Bolsos', 'Bolsos de diferentes estilos y tamaños'),
('Tote Bags', 'Bolsas de mano tipo tote'),
('Accesorios', 'Accesorios diversos para dama'),
('Protectores', 'Protectores para dispositivos electrónicos'),
('Medias', 'Medias y calcetines con diseños únicos');

-- Insertar productos de ejemplo
INSERT INTO productos (nombre, descripcion, precio, id_categoria, stock, imagen_url) VALUES
('Protector para audífonos inalámbricos', 'Protector para audífonos inalámbricos con temática de ositos cariñositos', 15000, 4, 50, '../assets/Disponible-09.jpg'),
('Bolso de malla', 'Bolso de mano en forma de malla para llevar con cualquier outfit', 35000, 1, 30, '../assets/Disponible-06.jpg'),
('Par de medias ositos', 'Par de medias con temática de ositos cariñositos', 12000, 5, 100, '../assets/Disponible-08.jpg'),
('Bolso Panthera Rosa', 'Bolso exclusivo Panthera en color rosa', 45000, 1, 25, '../assets/PANTHERA rosapng-07.png');

-- Vista para resumen de pedidos
CREATE VIEW vista_resumen_pedidos AS
SELECT 
    p.id_pedido,
    u.nombres,
    u.apellidos,
    u.email,
    p.fecha_pedido,
    p.estado,
    p.total,
    COUNT(dp.id_detalle) as cantidad_productos
FROM pedidos p
JOIN usuarios u ON p.id_usuario = u.id_usuario
LEFT JOIN detalle_pedidos dp ON p.id_pedido = dp.id_pedido
GROUP BY p.id_pedido, u.nombres, u.apellidos, u.email, p.fecha_pedido, p.estado, p.total;

-- Vista para inventario de productos
CREATE VIEW vista_inventario AS
SELECT 
    p.id_producto,
    p.nombre,
    c.nombre as categoria,
    p.precio,
    p.stock,
    p.activo,
    CASE 
        WHEN p.stock = 0 THEN 'Sin stock'
        WHEN p.stock < 10 THEN 'Stock bajo'
        ELSE 'Stock disponible'
    END as estado_stock
FROM productos p
JOIN categorias c ON p.id_categoria = c.id_categoria;

-- Procedimiento almacenado para crear un pedido
DELIMITER //

CREATE PROCEDURE crear_pedido(
    IN p_id_usuario INT,
    IN p_direccion_envio VARCHAR(255),
    IN p_notas_envio TEXT
)
BEGIN
    INSERT INTO pedidos (id_usuario, direccion_envio, notas_envio, total)
    VALUES (p_id_usuario, p_direccion_envio, p_notas_envio, 0);
    
    SELECT LAST_INSERT_ID() as id_pedido;
END //

-- Procedimiento para agregar producto a pedido
CREATE PROCEDURE agregar_producto_pedido(
    IN p_id_pedido INT,
    IN p_id_producto INT,
    IN p_cantidad INT
)
BEGIN
    DECLARE v_precio DECIMAL(10,2);
    DECLARE v_subtotal DECIMAL(10,2);
    DECLARE v_stock_actual INT;
    
    -- Obtener precio y stock del producto
    SELECT precio, stock INTO v_precio, v_stock_actual
    FROM productos
    WHERE id_producto = p_id_producto;
    
    -- Verificar stock disponible
    IF v_stock_actual >= p_cantidad THEN
        SET v_subtotal = v_precio * p_cantidad;
        
        -- Insertar detalle del pedido
        INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, subtotal)
        VALUES (p_id_pedido, p_id_producto, p_cantidad, v_precio, v_subtotal);
        
        -- Actualizar total del pedido
        UPDATE pedidos 
        SET total = total + v_subtotal
        WHERE id_pedido = p_id_pedido;
        
        -- Actualizar stock del producto
        UPDATE productos 
        SET stock = stock - p_cantidad
        WHERE id_producto = p_id_producto;
        
        SELECT 'Producto agregado exitosamente' as mensaje;
    ELSE
        SELECT 'Stock insuficiente' as mensaje;
    END IF;
END //

DELIMITER ;

-- Trigger para validar email al insertar usuario
DELIMITER //

CREATE TRIGGER validar_email_usuario
BEFORE INSERT ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.email NOT LIKE '%@%.%' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Email inválido';
    END IF;
END //

DELIMITER ;

-- Comentarios sobre el uso de la base de datos
-- Para usar esta base de datos:
-- 1. Ejecutar este script en MySQL
-- 2. Conectar la aplicación web con las credenciales apropiadas
-- 3. Usar los procedimientos almacenados para operaciones complejas
-- 4. Las vistas proporcionan consultas optimizadas para reportes