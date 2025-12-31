<?php

include "../config/db.php";

$conn = connect();

if (isset($_POST["toggle_id"])) {
    $stmt = $conn->prepare("
        UPDATE usuarios 
        SET estado = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $_POST["estado"],
        $_POST["toggle_id"]
    ]);

    echo json_encode(["status" => "success"]);
    exit;
}

if (isset($_POST["id"])) {
    $stmt = $conn->prepare("
        UPDATE usuarios 
        SET nombre = ?, correo = ?, cargo = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $_POST["nombre"],
        $_POST["correo"],
        $_POST["cargo"],
        $_POST["id"]
    ]);

    echo json_encode(["status" => "success"]);
    exit;
}
