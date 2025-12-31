<?php
include "../config/db.php";
$conn = connect();

$id       = $_POST['id'] ?? null;
$codigo   = $_POST['code'] ?? null;
$cantidad = $_POST['cantidad'] ?? null;
$fecha    = $_POST['fecha'] ?? null;

if (!$id || !$codigo) {
    echo json_encode(["status" => "error"]);
    exit;
}
$stmt_act = $conn->prepare("UPDATE productos SET cantidad=cantidad-? WHERE codigo=?");
if ($stmt_act->execute([$cantidad, $codigo])) {
    $stmt = $conn->prepare("DELETE FROM compras WHERE id_compra = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(["status" => "success"]);
        exit;
    } else {
        echo json_encode(["status" => "error"]);
    }
} else {
    echo json_encode(["status" => "error"]);
}
