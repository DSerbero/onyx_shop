<?php
include "../../config/db.php";


$productos = [];

$conn = connect();
$stmt = $conn->query("SELECT codigo, nombre, cantidad, venta FROM productos");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);



$json_productos = json_encode($productos);
?>
