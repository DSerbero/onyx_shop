<?php
include "../config/db.php";
$conn = connect();

if ($_POST["agregar"]) {
    try {
        $stmt = $conn->prepare("INSERT INTO productos(codigo, nombre, categoria, tipo_de_producto, costo, venta, cantidad, cantidad_minima) VALUES(?,?,?,?,?,?,?,?)");
        $stmt->bindparam(1, $codigo);
        $stmt->bindparam(2, $nombre);
        $stmt->bindparam(3, $categoria);
        $stmt->bindparam(4, $tipo_de_producto);
        $stmt->bindparam(5, $costo);
        $stmt->bindparam(6, $venta);
        $stmt->bindparam(7, $cantidad);
        $stmt->bindparam(8, $cantidad_minima);

        $codigo = $_POST["codigo"];
        $nombre = $_POST["nombre"];
        $categoria = $_POST["categoria"];
        $tipo_de_producto = $_POST["tipo_producto"];
        $costo = $_POST["costo"];
        $venta = $_POST["venta"];
        $cantidad = $_POST["cantidad"];
        $cantidad_minima = $_POST["min_cant"];

        $result = $stmt->execute();

        if ($result) {
            header("Location: ../agregar");
        }
    } catch (PDOException $e) {
        header("Location: ../agregar?e=pro_dup");
    }
}
