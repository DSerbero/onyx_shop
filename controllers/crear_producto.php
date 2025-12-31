<?php
header("Content-Type: application/json");

include "../config/db.php";
include "session.php";
$conn = connect();

if (!isset($_POST["codigo"])) {
    echo json_encode(["status" => "error", "msg" => "Datos incompletos"]);
    exit;
}

try {
    $stmt = $conn->prepare(
        "INSERT INTO productos
        (codigo, nombre, categoria, tipo_de_producto, costo, venta, cantidad, cantidad_minima)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $_POST["codigo"],
        $_POST["nombre"],
        $_POST["categoria"],
        $_POST["tipo_producto"],
        $_POST["costo"],
        $_POST["venta"],
        $_POST["cantidad"],
        $_POST["min_cant"]
    ]);

    if ($stmt) {
        $stmt_agg = $conn->prepare("SELECT id_producto FROM productos WHERE codigo=?");
        $stmt_agg->execute([$_POST["codigo"]]);
        $res = $stmt_agg->fetch(PDO::FETCH_ASSOC);
        $stmt_com = $conn->prepare("INSERT INTO compras(id_producto, cantidad, ingreso) VALUES(?, ?, ?)");
        if ($stmt_com->execute([$res["id_producto"], $_POST["cantidad"], $_SESSION["nombre"]])) {
            echo json_encode(["status" => "success"]);
        }
        
    } else {
        echo json_encode([
            "status" => "error",
            "msg" => "Error al guardar producto"
        ]);
    }
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode([
            "status" => "error",
            "msg" => "El código del producto ya existe"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "msg" => "Error al guardar producto"
        ]);
    }
}
