<?php
include "../../config/db.php";

$conn = connect();

/* =======================
   OBTENER VENTAS
======================= */
$sql = "SELECT * FROM ventas WHERE 1=1";
$params = [];

/* CLIENTE */
if (!empty($_GET["cliente"])) {
    $sql .= " AND id_cliente IN (
        SELECT id_cliente FROM clientes WHERE nombre LIKE ?
    )";
    $params[] = "%" . $_GET["cliente"] . "%";
}

/* ESTADO */
if (!empty($_GET["estado"])) {
    $sql .= " AND estado = ?";
    $params[] = $_GET["estado"];
}

/* MÉTODO DE PAGO */
if (!empty($_GET["metodo"])) {
    $sql .= " AND tipo_pago LIKE ?";
    $params[] = '%"' . $_GET["metodo"] . '"%';
}

/* FECHA */
if (!empty($_GET["desde"]) && !empty($_GET["hasta"])) {
    $sql .= " AND fecha_venta BETWEEN ? AND ?";
    $params[] = $_GET["desde"];
    $params[] = $_GET["hasta"];
}

$sql .= " ORDER BY id_venta DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =======================
   OBTENER ABONOS
======================= */
$stmt = $conn->prepare(
    "SELECT id_abono, id_venta, tipo_pago, fecha_abono
     FROM abonos
     ORDER BY fecha_abono ASC"
);
$stmt->execute();
$abonos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =======================
   AGRUPAR ABONOS POR VENTA
======================= */
$abonosPorVenta = [];

foreach ($abonos as $a) {
    $abonosPorVenta[$a["id_venta"]][] = $a;
}

/* =======================
   ACTUALIZAR ESTADO
======================= */
foreach ($ventas as &$v) {

    $venta = json_decode($v["tipo_pago"], true);

    if (($venta["detalles"]["tipo"] ?? "") !== "credito") {
        continue;
    }

    $idVenta = $v["id_venta"];
    $abonosVenta = $abonosPorVenta[$idVenta] ?? [];

    $saldo = (int)$venta["detalles"]["saldo"];

    foreach ($abonosVenta as $a) {
        $pago = json_decode($a["tipo_pago"], true);

        foreach ($pago["detalles"] as $monto) {
            if (is_numeric($monto)) {
                $saldo -= (int)$monto;
            }
        }
    }

    if ($saldo <= 0 && $v["estado"] === "pendiente") {
        $stmt = $conn->prepare(
            "UPDATE ventas
             SET estado = 'pago'
             WHERE id_venta = ? AND estado = 'pendiente'"
        );
        $stmt->execute([$idVenta]);

        $v["estado"] = "pago";
    }
}

unset($v); // romper referencia
