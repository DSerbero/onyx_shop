<?php
// procesar.php

if (!isset($_POST['desde']) || !isset($_POST['hasta'])) {
    die("No llegaron las fechas.");
}

$desde = $_POST['desde'];
$hasta = $_POST['hasta'];

// 👉 AQUÍ haces tus consultas reales a la BD
// Ejemplo de datos simulados:
$resumen = [
    "transferencia" => 120000,
    "efectivo" => 85000,
    "mixtas" => 45000,
    "credito" => 60000,
    "rentabilidad" => 55000
];

// Devolver al archivo principal (ej: ventas.php)
session_start();
$_SESSION["resumen"] = $resumen;
$_SESSION["desde"] = $desde;
$_SESSION["hasta"] = $hasta;

header("Location: prueba1.php?desde=$desde&hasta=$hasta");
exit;
