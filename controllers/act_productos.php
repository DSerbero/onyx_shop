<?php
include "../config/db.php";

$conn = connect();


if (isset($_POST["act"])) {
    $stmt = $conn->prepare("UPDATE productos SET nombre=?, categoria=?, tipo_de_producto=?, costo=?, venta=?, cantidad_minima=? WHERE id_producto=?");
    $stmt->bindParam(1, $nombre);
    $stmt->bindParam(2, $categoria);
    $stmt->bindParam(3, $tipo_de_producto);
    $stmt->bindParam(4, $costo);
    $stmt->bindParam(5, $venta);
    $stmt->bindParam(6, $cantidadMin);
    $stmt->bindParam(7, $id_producto);

    $nombre = $_POST["nombre"];
    $categoria = $_POST["categoria"];
    $tipo_de_producto = $_POST["tipo"];
    $costo = intval($_POST["costo"]);
    $venta = intval($_POST["venta"]);
    $cantidadMin = intval($_POST['add_min']);
    $id_producto = intval($_POST["edit_id"]);

    $stmt->execute();

    echo json_encode(["status" => "success"]);
    exit;
}

if (isset($_POST['add'])) {

    $id = intval($_POST['add_id']);
    $cantidadAdd = intval($_POST['add_cantidad']);

    $stmt = $conn->prepare("UPDATE productos SET cantidad = cantidad + ? WHERE id_producto = ?");
    $stmt->bindParam(1, $cantidadAdd);
    $stmt->bindParam(2, $id);

    if ($stmt->execute()) {
        $stmt_act = $conn->prepare("INSERT INTO compras (id_producto, cantidad) VALUES (?,?);");
        if ($stmt_act->execute([$id, $cantidadAdd])) {

            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error"]);
        }
    } else {
        echo json_encode(["status" => "error"]);
    }

    exit;
}


if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);

    $stmt = $conn->prepare("DELETE FROM productos WHERE id_producto = ?");

    $stmt->bindParam(1, $id);

    if ($stmt->execute()) {
        $stmt_cam = $conn->prepare("DELETE FROM compras WHERE id_producto = ?");
        if ($stmt_cam->execute([$id])) {
            echo json_encode(['status' => 'success']);
        }
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}
