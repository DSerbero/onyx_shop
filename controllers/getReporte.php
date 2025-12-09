<?php
include "session.php";
include "../config/db.php";
$conn = connect();

$desde = $_POST["desde"] ?? "2000-01-01 00:00:00";
$hasta = $_POST["hasta"] ?? date("Y-m-d H:i:s");

$resumen = [
    "efectivo" => 0,
    "transferencia" => 0,
    "credito" => 0,
    "abonos" => 0,
    "rentabilidad" => 0
];

$stmt = $conn->prepare("
    SELECT id_venta, productos, tipo_pago, fecha_venta
    FROM ventas
    WHERE fecha_venta BETWEEN ? AND ?
");
$stmt->execute([$desde, $hasta]);

$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($ventas as $v) {

    $pago = json_decode($v["tipo_pago"], true);
    if (!$pago) continue;

    $metodos  = $pago["metodos"];
    $det      = $pago["detalles"];


    if ($metodos === ["efectivo"]) {
        $resumen["efectivo"] += $det["total"];
    }

    else if ($metodos === ["transferencia"]) {
        $resumen["transferencia"] += $det["total"];
    }

    else if ($metodos === ["efectivo","transferencia"]) {
        $resumen["efectivo"]      += $det["efectivo"];
        $resumen["transferencia"] += $det["transferencia"];
    }



    else if (in_array("credito", $metodos)) {

        $abono = $det["abono"];
        $saldo = $det["saldo"];

        $resumen["credito"] += $saldo;

        if ($metodos === ["efectivo","credito"]) {
            $resumen["efectivo"] += $abono;
        }
        else if ($metodos === ["transferencia","credito"]) {
            $resumen["transferencia"] += $abono;
        }

        else if ($metodos === ["efectivo","transferencia","credito"]) {
            $resumen["efectivo"]      += ($det["monto_efectivo"] ?? 0);
            $resumen["transferencia"] += ($det["monto_transferencia"] ?? 0);
        }
    }

    $productos = json_decode($v["productos"], true);

    foreach ($productos as $p) {

        $codigo = $p["codigo"];
        $cantidad = $p["cantidad"];

        $s2 = $conn->prepare("SELECT costo, venta FROM productos WHERE codigo = ?");
        $s2->execute([$codigo]);

        if ($prod = $s2->fetch(PDO::FETCH_ASSOC)) {
            $c = $prod["costo"];
            $vnta = $prod["venta"];
            $resumen["rentabilidad"] += ($vnta - $c) * $cantidad;
        }
    }
}

$_SESSION["resumen"] = $resumen;
$_SESSION["desde"] = $desde;
$_SESSION["hasta"] = $hasta;
$_SESSION["ventas"] = $ventas; 

header("Location: ../reportes?desde=$desde&hasta=$hasta");

exit;

