<?php
include "../../config/db.php";


$conn = connect();


$stmt = $conn->prepare("SELECT c.id_compra, p.codigo, c.fecha_compra, c.cantidad, c.ingreso FROM compras c JOIN productos p WHERE c.id_producto = p.id_producto ORDER BY c.id_compra DESC");
$stmt->execute();

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>