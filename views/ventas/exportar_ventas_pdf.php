<?php
require_once "../../vendor/autoload.php";
require_once "../../controllers/ventas.php";

use Dompdf\Dompdf;

$filtros = json_decode($_POST["filtros"] ?? "{}", true);

/* reutiliza tu controlador */
$ventas = obtenerVentas($filtros);
$abonosPorVenta = obtenerAbonosAgrupados();

function fechaCortaHora($f)
{
    return date("d/m/Y H:i", strtotime($f));
}

ob_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial;
            font-size: 11px;
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
            background: #eee;
        }
    </style>
</head>

<body>

    <h2 style="text-align:center">Reporte de Ventas</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Método</th>
                <th>Total</th>
                <th>Pendiente</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ventas as $v):

                $venta = json_decode($v["tipo_pago"], true) ?? [];
                $abonos = $abonosPorVenta[$v["id_venta"]] ?? [];

                $saldoInicial = (int)($venta["detalles"]["saldo"] ?? 0);
                $totalAbonos = 0;

                foreach ($abonos as $a) {
                    foreach (json_decode($a["tipo_pago"], true)["detalles"] ?? [] as $m) {
                        $totalAbonos += (int)$m;
                    }
                }

                $pendiente = max(0, $saldoInicial - $totalAbonos);
            ?>
                <tr>
                    <td><?= $v["id_venta"] ?></td>
                    <td><?= fechaCortaHora($v["fecha_venta"]) ?></td>
                    <td><?= $v["cliente"] ?? "—" ?></td>
                    <td><?= ucfirst($venta["detalles"]["tipo"] ?? "—") ?></td>
                    <td>$<?= number_format($venta["detalles"]["total"] ?? 0, 0, ',', '.') ?></td>
                    <td>$<?= number_format($pendiente, 0, ',', '.') ?></td>
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
$pdf->stream("ventas.pdf", ["Attachment" => false]);
