<?php
require_once "../config/db.php";
require_once "session.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "msg" => "Método no permitido"
    ]);
    exit;
}

$id_cliente  = $_POST["id_cliente"] ?? null;
$nombre      = trim($_POST["nombre"] ?? "");
$documento   = trim($_POST["documento"] ?? "");
$direccion   = trim($_POST["direccion"] ?? "");
$correo      = trim($_POST["correo"] ?? "");

if (!$id_cliente || $nombre === "" || $documento === "") {
    echo json_encode([
        "status" => "error",
        "msg" => "Datos obligatorios incompletos"
    ]);
    exit;
}

try {
    $conn = connect();

    $stmt = $conn->prepare("
        UPDATE clientes 
        SET nombre = ?, 
            documento = ?, 
            direccion = ?, 
            correo = ?
        WHERE id_cliente = ?
    ");

    $stmt->execute([
        $nombre,
        $documento,
        $direccion,
        $correo,
        $id_cliente
    ]);

    echo json_encode([
        "status" => "success"
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "msg" => "Error al actualizar cliente"
    ]);
}
