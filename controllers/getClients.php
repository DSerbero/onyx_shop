<?php

$conn = connect();
$stmt = $conn->query("SELECT * FROM clientes");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$json_clientes = json_encode($clientes);
