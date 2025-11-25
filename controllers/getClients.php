<?php

$conn = connect();
$stmt = $conn->query("SELECT id_cliente, documento, nombre, direccion, telefono, correo FROM clientes");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$json_clientes = json_encode($clientes);
