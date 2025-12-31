<?php
include "../config/db.php";
include "session.php";

header("Content-Type: application/json");

if (!isset($_POST["id_cliente"])) {
    echo json_encode([
        "status" => "error",
        "msg" => "ID no recibido"
    ]);
    exit;
}

$id = intval($_POST["id_cliente"]);

try {
    $conn = connect();

    $stmt = $conn->prepare("SELECT estado FROM clientes WHERE id_cliente = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        echo json_encode([
            "status" => "error",
            "msg" => "Cliente no encontrado"
        ]);
        exit;
    }

    $nuevoEstado = $cliente["estado"] == 1 ? 0 : 1;

    $update = $conn->prepare("UPDATE clientes SET estado = ? WHERE id_cliente = ?");
    $update->execute([$nuevoEstado, $id]);

    echo json_encode([
        "status" => "success",
        "nuevo_estado" => $nuevoEstado
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "msg" => "Error interno"
    ]);
}
