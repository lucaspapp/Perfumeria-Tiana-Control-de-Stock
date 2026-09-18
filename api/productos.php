<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id) {
        $stmt = $conexion->prepare('SELECT id, codigo, nombre, existencias, fecha_registro, activo FROM productos WHERE id = ?');
        $stmt->execute([$id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            responder(['success' => false, 'message' => 'Producto no encontrado.'], 404);
        }

        responder(['success' => true, 'producto' => $producto]);
    }

    $stmt = $conexion->query('SELECT id, codigo, nombre, existencias, fecha_registro, activo FROM productos WHERE activo = 1 ORDER BY nombre');
    responder(['success' => true, 'productos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = leerJson();
    $codigo = trim($datos['codigo'] ?? '');
    $nombre = trim($datos['nombre'] ?? '');

    if ($codigo === '' || $nombre === '') {
        responder(['success' => false, 'message' => 'El código y el nombre son obligatorios.'], 400);
    }

    if (strlen($codigo) > 20 || strlen($nombre) > 100) {
        responder(['success' => false, 'message' => 'El código o nombre supera el largo permitido.'], 400);
    }

    try {
        $stmt = $conexion->prepare('INSERT INTO productos (codigo, nombre) VALUES (?, ?)');
        $stmt->execute([$codigo, $nombre]);
        responder(['success' => true, 'message' => 'Producto registrado correctamente.', 'id' => $conexion->lastInsertId()], 201);
    } catch (PDOException $error) {
        if ($error->getCode() === '23000') {
            responder(['success' => false, 'message' => 'El código del producto ya existe.'], 409);
        }

        responder(['success' => false, 'message' => 'No se pudo registrar el producto.'], 500);
    }
}

responder(['success' => false, 'message' => 'Método no permitido.'], 405);
