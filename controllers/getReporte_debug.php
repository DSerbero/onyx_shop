<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "config/db.php";
$conn = connect();

$desde = $_POST["desde"];
$hasta = $_POST["hasta"];

// === VALORES INICIALES ===
$resumen = [
    "efectivo" => 0,
    "transferencia" => 0,
    "credito" => 0,
    "abonos" => 0, // (por ahora no se usa)
    "rentabilidad" => 0
];

// === TRAER VENTAS ===
$stmt = $conn->prepare("
    SELECT id_venta, productos, tipo_pago, fecha_venta
    FROM ventas
    WHERE fecha_venta BETWEEN ? AND ?
");
$stmt->execute([$desde, $hasta]);

$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($ventas as $v) {

    $pago = json_decode($v["tipo_pago"], true);
    $metodos = $pago["metodos"];
    $d = $pago["detalles"];

    if ($d["tipo"] === "efectivo") {
        $resumen["efectivo"] += $d["total"];
    }

    // TRANSFERENCIA PURA
    if ($d["tipo"] === "transferencia") {
        $resumen["transferencia"] += $d["total"];
    }

    // MIXTO (efectivo + transferencia)
    if ($d["tipo"] === "mixto") {
        $resumen["efectivo"] += $d["efectivo"] ?? 0;
        $resumen["transferencia"] += $d["transferencia"] ?? 0;
    }

    // CRÉDITO (con 1 o 2 métodos adicionales)
    if ($d["tipo"] === "credito") {

        // SUMAR CRÉDITO = saldo pendiente
        $resumen["credito"] += $d["saldo"];

        // EFECTIVO + CRÉDITO
        if (in_array("efectivo", $metodos)) {

            // Si viene con monto_efectivo lo uso
            if (isset($d["monto_efectivo"]) && $d["monto_efectivo"] > 0) {
                $resumen["efectivo"] += $d["monto_efectivo"];
            } else {
                // CRÉDITO acompañado solo de efectivo → todo el abono pertenece aquí
                $resumen["efectivo"] += $d["abono"];
            }
        }

        // TRANSFERENCIA + CRÉDITO
        if (in_array("transferencia", $metodos)) {

            if (isset($d["monto_transferencia"]) && $d["monto_transferencia"] > 0) {
                $resumen["transferencia"] += $d["monto_transferencia"];
            } else {
                // CRÉDITO acompañado solo de transferencia → todo el abono pertenece aquí
                $resumen["transferencia"] += $d["abono"];
            }
        }
    }

    // -------------------------
    // 3) RENTABILIDAD
    // -------------------------
    $productos = json_decode($v["productos"], true);

    foreach ($productos as $codigo => $cantidad) {

        $stmtP = $conn->prepare("SELECT costo, venta FROM productos WHERE codigo = ?");
        $stmtP->execute([$codigo]);

        if ($prod = $stmtP->fetch(PDO::FETCH_ASSOC)) {

            $costo_total = $prod["costo"] * $cantidad;
            $venta_total = $prod["venta"] * $cantidad;

            $resumen["rentabilidad"] += ($venta_total - $costo_total);
        }
    }
}

// === GUARDAR EN SESIÓN ===
session_start();
$_SESSION["resumen"] = $resumen;
$_SESSION["desde"] = $desde;
$_SESSION["hasta"] = $hasta;

// === RESPUESTA (si pruebas por API) ===
// echo json_encode($resumen);

// === REDIRIGIR ===
header("Location: prueba1.php?desde=$desde&hasta=$hasta");
exit;
