<?php
require_once 'conexion.php';

$periodo = $_GET['periodo'] ?? 'mes';
$productoId = filter_input(INPUT_GET, 'producto_id', FILTER_VALIDATE_INT);
$permitidos = ['dia', 'semana', 'mes'];
if (!in_array($periodo, $permitidos, true)) {
    $periodo = 'mes';
}

if ($periodo === 'dia') {
    $fechaInicio = date('Y-m-d');
} elseif ($periodo === 'semana') {
    $fechaInicio = date('Y-m-d', strtotime('monday this week'));
} else {
    $fechaInicio = date('Y-m-01');
}
$fechaFin = date('Y-m-d');

$where = 'WHERE m.fecha_movimiento >= ? AND m.fecha_movimiento <= ?';
$parametros = [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'];
if ($productoId) {
    $where .= ' AND m.producto_id = ?';
    $parametros[] = $productoId;
}

$sqlResumen = "SELECT
    COALESCE(SUM(CASE WHEN m.tipo_movimiento = 'entrada' THEN m.cantidad ELSE 0 END), 0) AS entradas,
    COALESCE(SUM(CASE WHEN m.tipo_movimiento IN ('venta', 'perdida') THEN m.cantidad ELSE 0 END), 0) AS salidas,
    COALESCE(SUM(CASE WHEN m.tipo_movimiento = 'venta' THEN m.cantidad * m.precio_venta ELSE 0 END), 0) AS ingresos,
    COALESCE(SUM(CASE WHEN m.tipo_movimiento = 'venta' THEN m.cantidad * m.precio_compra ELSE 0 END), 0) AS costo_ventas,
    COALESCE(SUM(CASE WHEN m.tipo_movimiento = 'perdida' THEN m.cantidad * m.precio_compra ELSE 0 END), 0) AS perdidas
    FROM movimientos m $where";
$stmt = $conexion->prepare($sqlResumen);
$stmt->execute($parametros);
$resumen = $stmt->fetch(PDO::FETCH_ASSOC);
$resumen['ganancia'] = (float) $resumen['ingresos'] - (float) $resumen['costo_ventas'];

if ($productoId) {
    $stmtStock = $conexion->prepare('SELECT COALESCE(existencias, 0) AS existencias FROM productos WHERE id = ?');
    $stmtStock->execute([$productoId]);
} else {
    $stmtStock = $conexion->query('SELECT COALESCE(SUM(existencias), 0) AS existencias FROM productos WHERE activo = 1');
}
$stock = $stmtStock->fetch(PDO::FETCH_ASSOC);
$resumen['existencias'] = (int) $stock['existencias'];

$sqlDiario = "SELECT DATE(m.fecha_movimiento) AS fecha, p.codigo, p.nombre,
    COALESCE(SUM(CASE WHEN m.tipo_movimiento = 'entrada' THEN m.cantidad ELSE 0 END), 0) AS entradas,
    COALESCE(SUM(CASE WHEN m.tipo_movimiento IN ('venta', 'perdida') THEN m.cantidad ELSE 0 END), 0) AS salidas,
    COALESCE(SUM(CASE WHEN m.tipo_movimiento = 'venta' THEN m.cantidad ELSE 0 END), 0) AS ventas,
    COALESCE(SUM(CASE WHEN m.tipo_movimiento = 'perdida' THEN m.cantidad ELSE 0 END), 0) AS perdidas
    FROM movimientos m INNER JOIN productos p ON p.id = m.producto_id
    $where GROUP BY DATE(m.fecha_movimiento), m.producto_id, p.codigo, p.nombre
    ORDER BY fecha DESC, p.codigo";
$stmtDiario = $conexion->prepare($sqlDiario);
$stmtDiario->execute($parametros);

$respuesta = [
    'success' => true,
    'periodo' => $periodo,
    'fecha_inicio' => $fechaInicio,
    'fecha_fin' => $fechaFin,
    'resumen' => $resumen,
    'diario' => $stmtDiario->fetchAll(PDO::FETCH_ASSOC)
];

if (!$productoId) {
    $respuesta['resumen']['productos'] = (int) $conexion->query('SELECT COUNT(*) FROM productos WHERE activo = 1')->fetchColumn();
}

responder($respuesta);
