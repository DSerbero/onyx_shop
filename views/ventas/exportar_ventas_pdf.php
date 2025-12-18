<?php
require_once "../../controllers/session.php";
require_once "../../controllers/ventas.php";
require_once "../../vendor/autoload.php";

use Dompdf\Dompdf;

/*
    En controllers/ventas.php ya existen:
    - $ventas
    - $abonosPorVenta
*/

function obtenerProductosVenta(string $jsonProductos): array
{
    $conn = connect();

    $items = json_decode($jsonProductos, true);
    if (!is_array($items)) return [];

    $resultado = [];

    $stmt = $conn->prepare(
        "SELECT nombre FROM productos WHERE codigo = ?"
    );

    foreach ($items as $item) {
        $stmt->execute([$item["codigo"]]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($p) {
            $resultado[] = $p["nombre"] . " x" . $item["cantidad"];
        }
    }

    return $resultado;
}

function getCliente($id_cliente)
{
    $conn = connect();
    $stmt = $conn->prepare("SELECT nombre FROM clientes WHERE id_cliente = ?");
    $stmt->execute([$id_cliente]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function fechaCortaHora(string $fecha): string
{
    return date("d/m/Y H:i", strtotime($fecha));
}

ob_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background: #f0f0f0;
        }

        .right {
            text-align: right;
        }
    </style>
</head>

<body>

    <h2>Reporte de ventas</h2>

    <table>
        <thead>
            <tr>
                <th># Venta</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Productos</th>
                <th>Método</th>
                <th>Total</th>
                <th>Pendiente</th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($ventas as $v):

                $venta = json_decode($v["tipo_pago"], true) ?? [];
                $abonosVenta = $abonosPorVenta[$v["id_venta"]] ?? [];

                $saldoInicial = (int)($venta["detalles"]["saldo"] ?? 0);
                $totalAbonos = 0;

                foreach ($abonosVenta as $a) {
                    $pago = json_decode($a["tipo_pago"], true);
                    foreach ($pago["detalles"] ?? [] as $monto) {
                        $totalAbonos += (int)$monto;
                    }
                }

                $saldoPendiente = max(0, $saldoInicial - $totalAbonos);
                $productos = obtenerProductosVenta($v["productos"]);
                $productosTexto = !empty($productos)
                    ? implode(", ", $productos)
                    : "—";

            ?>
                <tr>
                    <td><?= $v["id_venta"] ?></td>
                    <td><?= fechaCortaHora($v["fecha_venta"]) ?></td>
                    <td><?= getCliente($v["id_cliente"])["nombre"] ?? "—" ?></td>
                    <td><?= $productosTexto ?></td>
                    <td><?= ucfirst($venta["detalles"]["tipo"] ?? "—") ?></td>
                    <td class="right">$<?= number_format($venta["detalles"]["total"] ?? 0, 0, ',', '.') ?></td>
                    <td class="right">$<?= number_format($saldoPendiente, 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>

</body>

</html>
<?php

$html = ob_get_clean();

$pdf = new Dompdf();
$pdf->loadHtml($html);
$pdf->setPaper("A4", "landscape");
$pdf->render();
$pdf->stream("reporte_ventas.pdf", ["Attachment" => false]);
