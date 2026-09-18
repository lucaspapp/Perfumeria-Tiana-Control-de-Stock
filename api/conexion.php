<?php
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$baseDatos = 'perfumeria_tiana';
$usuario = 'root';
$contrasena = '';

try {
    $conexion = new PDO(
        "mysql:host=$host;dbname=$baseDatos;charset=utf8mb4",
        $usuario,
        $contrasena,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo conectar con la base de datos.'
    ]);
    exit;
}

function responder($datos, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function leerJson() {
    $contenido = file_get_contents('php://input');
    $datos = json_decode($contenido, true);

    if (!is_array($datos)) {
        responder(['success' => false, 'message' => 'El cuerpo JSON no es válido.'], 400);
    }

    return $datos;
}
