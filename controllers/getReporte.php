<?php
include "session.php";
include "../config/db.php";
$conn = connect();

function fixDate($d, $isEnd = false)
{
    if (!$d) return null;

    $d = str_replace("T", " ", $d);

    // si es "hasta", poner segundos 59
    return $isEnd ? ($d . ":59") : ($d . ":00");
}

$desde = fixDate($_POST["desde"] ?? null);
$hasta = fixDate($_POST["hasta"] ?? null, true);


$resumen = [
    "ventas" => 0,
    "efectivo" => 0,
    "transferencia" => 0,
    "credito" => 0,
    "abonos" => [
        "efectivo" => 0,
        "transferencia" => 0
    ],
    "rentabilidad" => 0
];

$stmt = $conn->prepare("
    SELECT id_venta, productos, tipo_pago, fecha_venta
    FROM ventas
    WHERE fecha_venta BETWEEN ? AND ?
");
$stmt->execute([$desde, $hasta]);

$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$resumen["ventas"] = $stmt->rowCount();

foreach ($ventas as $v) {

    $pago = json_decode($v["tipo_pago"], true);
    if (!$pago) continue;

    $metodos  = $pago["metodos"];
    $det      = $pago["detalles"];


    if ($metodos === ["efectivo"]) {
        $resumen["efectivo"] += $det["total"];
    } else if ($metodos === ["transferencia"]) {
        $resumen["transferencia"] += $det["total"];
    } else if ($metodos === ["efectivo", "transferencia"]) {
        $resumen["efectivo"]      += $det["efectivo"];
        $resumen["transferencia"] += $det["transferencia"];
    } else if (in_array("credito", $metodos)) {

        $abono = $det["abono"];
        $saldo = $det["saldo"];

        $resumen["credito"] += $saldo;

        if ($metodos === ["efectivo", "credito"]) {
            $resumen["efectivo"] += $abono;
        } else if ($metodos === ["transferencia", "credito"]) {
            $resumen["transferencia"] += $abono;
        } else if ($metodos === ["efectivo", "transferencia", "credito"]) {
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

$stmt_abo = $conn->prepare("
    SELECT id_abono, id_venta, tipo_pago, fecha_abono
    FROM abonos
    WHERE fecha_abono BETWEEN ? AND ?
");
$stmt_abo->execute([$desde, $hasta]);

$abonos = $stmt_abo->fetchAll(PDO::FETCH_ASSOC);

foreach ($abonos as $ab) {
    $pago = json_decode($ab["tipo_pago"], true);
    if (!$pago) continue;

    $metodos  = $pago["metodos"];
    $det      = $pago["detalles"];

    if ($metodos === ["efectivo"]) {
        $resumen["abonos"]['efectivo'] += $det["efectivo"];
    } else if ($metodos === ["transferencia"]) {
        $resumen['abonos']["transferencia"] += $det["transferencia"];
    } else if ($metodos === ["efectivo", "transferencia"]) {
        $resumen['abonos']["efectivo"]      += $det["efectivo"];
        $resumen['abonos']["transferencia"] += $det["transferencia"];
    }
}


$_SESSION["resumen"] = $resumen;
$_SESSION["desde"] = $desde;
$_SESSION["hasta"] = $hasta;
$_SESSION["ventas"] = $ventas;

header("Location: ../reportes?desde=$desde&hasta=$hasta");

exit;
