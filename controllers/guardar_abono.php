<?php
include "../config/db.php";

header("Content-Type: application/json");

$idVenta = (int)($_POST["id_venta"] ?? 0);
$metodos = json_decode($_POST["metodos"] ?? "[]", true);

if (!$idVenta || empty($metodos)) {
    echo json_encode(["ok" => false]);
    exit;
}

$detalles = [];

if (in_array("efectivo", $metodos)) {
    $detalles["efectivo"] = (int)$_POST["monto_efectivo"];
}

if (in_array("transferencia", $metodos)) {
    $detalles["transferencia"] = (int)$_POST["monto_transferencia"];
}

$tipoPago = json_encode([
    "metodos" => $metodos,
    "detalles" => $detalles
]);

$conn = connect();

$stmt = $conn->prepare(
    "INSERT INTO abonos (id_venta, tipo_pago)
     VALUES (?, ?)"
);
$stmt->execute([$idVenta, $tipoPago]);

echo json_encode(["ok" => true]);
