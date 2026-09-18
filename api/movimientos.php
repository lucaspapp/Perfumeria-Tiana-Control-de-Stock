<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $productoId = filter_input(INPUT_GET, 'producto_id', FILTER_VALIDATE_INT);
    $tipo = $_GET['tipo'] ?? '';
    $fechaDesde = $_GET['fecha_desde'] ?? '';
    $fechaHasta = $_GET['fecha_hasta'] ?? '';

    $sql = "SELECT m.id, m.producto_id, p.codigo, p.nombre, m.tipo_movimiento, m.cantidad,
                   m.precio_compra, m.precio_venta, m.observacion, m.fecha_movimiento
            FROM movimientos m INNER JOIN productos p ON p.id = m.producto_id WHERE 1=1";
    $parametros = [];

    if ($productoId) {
        $sql .= ' AND m.producto_id = ?';
        $parametros[] = $productoId;
    }
    if (in_array($tipo, ['entrada', 'venta', 'perdida'], true)) {
        $sql .= ' AND m.tipo_movimiento = ?';
        $parametros[] = $tipo;
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde)) {
        $sql .= ' AND m.fecha_movimiento >= ?';
        $parametros[] = $fechaDesde . ' 00:00:00';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) {
        $sql .= ' AND m.fecha_movimiento <= ?';
        $parametros[] = $fechaHasta . ' 23:59:59';
    }

    $sql .= ' ORDER BY m.fecha_movimiento DESC, m.id DESC';
    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);
    responder(['success' => true, 'movimientos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = leerJson();
    $productoId = filter_var($datos['producto_id'] ?? null, FILTER_VALIDATE_INT);
    $tipo = $datos['tipo_movimiento'] ?? '';
    $cantidad = filter_var($datos['cantidad'] ?? null, FILTER_VALIDATE_INT);
    $precioCompra = (float) ($datos['precio_compra'] ?? 0);
    $precioVenta = (float) ($datos['precio_venta'] ?? 0);
    $observacion = trim($datos['observacion'] ?? '');

    if (!$productoId || !in_array($tipo, ['entrada', 'venta', 'perdida'], true) || !$cantidad || $cantidad <= 0) {
        responder(['success' => false, 'message' => 'Producto, tipo y cantidad válida son obligatorios.'], 400);
    }
    if ($precioCompra < 0 || $precioVenta < 0) {
        responder(['success' => false, 'message' => 'Los precios no pueden ser negativos.'], 400);
    }
    if ($tipo === 'entrada' && $precioCompra <= 0) {
        responder(['success' => false, 'message' => 'La entrada requiere un precio de compra mayor que cero.'], 400);
    }
    if ($tipo === 'venta' && $precioVenta <= 0) {
        responder(['success' => false, 'message' => 'La venta requiere un precio de venta mayor que cero.'], 400);
    }
    if (strlen($observacion) > 255) {
        responder(['success' => false, 'message' => 'La observación supera los 255 caracteres.'], 400);
    }

    try {
        $conexion->beginTransaction();
        $stmtProducto = $conexion->prepare('SELECT existencias FROM productos WHERE id = ? AND activo = 1 FOR UPDATE');
        $stmtProducto->execute([$productoId]);
        $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            $conexion->rollBack();
            responder(['success' => false, 'message' => 'El producto no existe.'], 404);
        }

        if ($tipo !== 'entrada' && $producto['existencias'] < $cantidad) {
            $conexion->rollBack();
            responder(['success' => false, 'message' => 'No hay suficiente stock para realizar esta salida.'], 409);
        }

        if ($tipo === 'entrada') {
            $nuevoStock = (int) $producto['existencias'] + $cantidad;
            $precioCompraMovimiento = $precioCompra;
        } else {
            $nuevoStock = (int) $producto['existencias'] - $cantidad;
            $stmtCosto = $conexion->prepare("SELECT precio_compra FROM movimientos WHERE producto_id = ? AND tipo_movimiento = 'entrada' ORDER BY fecha_movimiento DESC, id DESC LIMIT 1");
            $stmtCosto->execute([$productoId]);
            $ultimaEntrada = $stmtCosto->fetch(PDO::FETCH_ASSOC);
            $precioCompraMovimiento = $ultimaEntrada ? (float) $ultimaEntrada['precio_compra'] : 0;
        }

        $stmtMovimiento = $conexion->prepare('INSERT INTO movimientos (producto_id, tipo_movimiento, cantidad, precio_compra, precio_venta, observacion) VALUES (?, ?, ?, ?, ?, ?)');
        $stmtMovimiento->execute([$productoId, $tipo, $cantidad, $precioCompraMovimiento, $tipo === 'venta' ? $precioVenta : 0, $observacion]);
        $stmtStock = $conexion->prepare('UPDATE productos SET existencias = ? WHERE id = ?');
        $stmtStock->execute([$nuevoStock, $productoId]);
        $conexion->commit();

        responder(['success' => true, 'message' => 'Movimiento registrado correctamente.'], 201);
    } catch (PDOException $error) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        responder(['success' => false, 'message' => 'No se pudo registrar el movimiento.'], 500);
    }
}

responder(['success' => false, 'message' => 'Método no permitido.'], 405);
