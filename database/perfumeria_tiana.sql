CREATE DATABASE IF NOT EXISTS perfumeria_tiana CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE perfumeria_tiana;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    existencias INT NOT NULL DEFAULT 0,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    tipo_movimiento ENUM('entrada', 'venta', 'perdida') NOT NULL,
    cantidad INT NOT NULL,
    precio_compra DECIMAL(10,2) NOT NULL DEFAULT 0,
    precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0,
    observacion VARCHAR(255) NULL,
    fecha_movimiento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_movimientos_producto FOREIGN KEY (producto_id) REFERENCES productos(id),
    INDEX idx_movimientos_fecha (fecha_movimiento),
    INDEX idx_movimientos_producto (producto_id)
);

-- Carga inicial reproducible: 100 productos y movimientos del 18/09/2025 al 18/09/2026.
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE movimientos;
TRUNCATE TABLE productos;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO productos (codigo, nombre) VALUES
('P001','Desodorante Rexona'), ('P002','Perfume Antonio Banderas'), ('P003','Shampoo Sedal'), ('P004','Crema Nivea'), ('P005','Jabon Dove'),
('P006','Desodorante Dove'), ('P007','Perfume Paco Rabanne'), ('P008','Perfume Carolina Herrera'), ('P009','Shampoo Pantene'), ('P010','Acondicionador Pantene'),
('P011','Crema Hinds'), ('P012','Protector solar Nivea'), ('P013','Agua micelar Garnier'), ('P014','Crema Garnier'), ('P015','Gel de ducha Palmolive'),
('P016','Jabon liquido Lux'), ('P017','Desodorante Axe'), ('P018','Perfume Calvin Klein'), ('P019','Perfume Antonio Puig'), ('P020','Shampoo Elvive'),
('P021','Acondicionador Elvive'), ('P022','Crema corporal Dove'), ('P023','Talco Veritas'), ('P024','Colonia Johnson'), ('P025','Aceite corporal Johnson'),
('P026','Perfume Hugo Boss'), ('P027','Perfume Carolina Herrera Good Girl'), ('P028','Perfume Issey Miyake'), ('P029','Shampoo Head and Shoulders'), ('P030','Acondicionador Sedal'),
('P031','Crema facial L Oreal'), ('P032','Agua termal Avene'), ('P033','Protector solar La Roche Posay'), ('P034','Balsamo labial Nivea'), ('P035','Jabon Rexona'),
('P036','Desmaquillante Neutrogena'), ('P037','Crema Neutrogena'), ('P038','Gel limpiador Neutrogena'), ('P039','Perfume Benetton'), ('P040','Perfume Tommy Hilfiger'),
('P041','Shampoo Tresemme'), ('P042','Acondicionador Tresemme'), ('P043','Mascarilla capilar Elvive'), ('P044','Crema para peinar Sedal'), ('P045','Aceite capilar Pantene'),
('P046','Espuma de afeitar Gillette'), ('P047','Maquina de afeitar Gillette'), ('P048','After shave Nivea'), ('P049','Desodorante Gillette'), ('P050','Perfume Dolce Gabbana'),
('P051','Perfume Versace'), ('P052','Perfume Kenzo'), ('P053','Perfume Nina Ricci'), ('P054','Perfume Bvlgari'), ('P055','Perfume Montblanc'),
('P056','Crema de manos Neutrogena'), ('P057','Crema de manos Nivea'), ('P058','Jabon liquido Dove'), ('P059','Jabon liquido Palmolive'), ('P060',CONVERT(0x53616c6573206465206261c3b16f USING utf8mb4)),
('P061','Esponja exfoliante'), ('P062','Algodon desmaquillante'), ('P063','Hisopos'), ('P064','Toallitas humedas'), ('P065','Toallitas desmaquillantes'),
('P066','Perfume Zara Mujer'), ('P067','Perfume Zara Hombre'), ('P068','Perfume Lacoste'), ('P069','Perfume Armani'), ('P070','Perfume Dior'),
('P071','Shampoo Herbal Essences'), ('P072','Acondicionador Herbal Essences'), ('P073','Shampoo Tio Nacho'), ('P074','Tratamiento capilar Kerastase'), ('P075','Spray fijador L Oreal'),
('P076','Crema antiarrugas Nivea'), ('P077','Serum facial Garnier'), ('P078','Contorno de ojos Nivea'), ('P079','Mascarilla facial Garnier'), ('P080','Exfoliante facial Neutrogena'),
('P081','Desodorante Lady Speed Stick'), ('P082','Desodorante Old Spice'), ('P083','Desodorante Rexona Clinical'), ('P084','Perfume Azzaro'), ('P085','Perfume Jean Paul Gaultier'),
('P086','Talco para pies Rexona'), ('P087','Crema para pies Hinds'), ('P088','Gel antibacterial'), ('P089','Alcohol en gel'), ('P090','Jabon intimo Lactacyd'),
('P091','Toallas femeninas Kotex'), ('P092','Protectores diarios Carefree'), ('P093','Algodon en discos'), ('P094','Cepillo de cabello'), ('P095','Peine profesional'),
('P096','Espejo de bolsillo'), ('P097','Set de manicura'), ('P098',CONVERT(0x45736d616c74652064652075c3b1617320526f6a6f USING utf8mb4)), ('P099',CONVERT(0x45736d616c74652064652075c3b16173204e756465 USING utf8mb4)), ('P100','Removedor de esmalte');

DELIMITER //
CREATE PROCEDURE cargar_movimientos_iniciales()
BEGIN
    DECLARE productoActual INT DEFAULT 1;
    DECLARE fechaActual DATE;
    DECLARE precioCompra DECIMAL(10,2);
    DECLARE precioVenta DECIMAL(10,2);
    DECLARE cantidadEntrada INT;
    DECLARE cantidadVenta INT;

    WHILE productoActual <= 100 DO
        SET precioCompra = 1800 + (productoActual * 35);
        SET precioVenta = ROUND(precioCompra * 1.60, 2);
        SET fechaActual = '2025-09-18';
        SET cantidadEntrada = 30 + MOD(productoActual, 11);

        INSERT INTO movimientos (producto_id, tipo_movimiento, cantidad, precio_compra, precio_venta, observacion, fecha_movimiento)
        VALUES (productoActual, 'entrada', cantidadEntrada, precioCompra, 0, 'Compra inicial de prueba', '2025-09-18 09:00:00');
        UPDATE productos SET existencias = existencias + cantidadEntrada WHERE id = productoActual;
        SET fechaActual = DATE_ADD(fechaActual, INTERVAL 15 DAY);

        WHILE fechaActual <= '2026-09-18' DO
            SET cantidadEntrada = 8 + MOD(productoActual, 6);
            SET cantidadVenta = 2 + MOD(productoActual, 3);

            INSERT INTO movimientos (producto_id, tipo_movimiento, cantidad, precio_compra, precio_venta, observacion, fecha_movimiento)
            VALUES (productoActual, 'entrada', cantidadEntrada, precioCompra, 0, 'Reposicion de proveedor', CONCAT(fechaActual, ' 09:00:00'));
            UPDATE productos SET existencias = existencias + cantidadEntrada WHERE id = productoActual;

            INSERT INTO movimientos (producto_id, tipo_movimiento, cantidad, precio_compra, precio_venta, observacion, fecha_movimiento)
            VALUES (productoActual, 'venta', cantidadVenta, precioCompra, precioVenta, 'Venta de mostrador', CONCAT(fechaActual, ' 15:00:00'));
            UPDATE productos SET existencias = existencias - cantidadVenta WHERE id = productoActual;

            IF MOD(DATEDIFF(fechaActual, '2025-09-18'), 60) = 0 THEN
                INSERT INTO movimientos (producto_id, tipo_movimiento, cantidad, precio_compra, precio_venta, observacion, fecha_movimiento)
                VALUES (productoActual, 'perdida', 1, precioCompra, 0, 'Producto danado', CONCAT(fechaActual, ' 17:00:00'));
                UPDATE productos SET existencias = existencias - 1 WHERE id = productoActual;
            END IF;

            SET fechaActual = DATE_ADD(fechaActual, INTERVAL 15 DAY);
        END WHILE;

        INSERT INTO movimientos (producto_id, tipo_movimiento, cantidad, precio_compra, precio_venta, observacion, fecha_movimiento)
        VALUES (productoActual, 'venta', 1, precioCompra, precioVenta, 'Venta de cierre del periodo', '2026-09-18 12:00:00');
        UPDATE productos SET existencias = existencias - 1 WHERE id = productoActual;

        SET productoActual = productoActual + 1;
    END WHILE;
END//
DELIMITER ;

CALL cargar_movimientos_iniciales();
DROP PROCEDURE cargar_movimientos_iniciales;
