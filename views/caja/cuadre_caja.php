<?php
include "../../controllers/session.php";

if ($_SESSION["cargo"] === "gerente" || $_SESSION["cargo"] === "admin" || $_SESSION["cargo"] === "code") {

?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cuadre de caja</title>
        <link rel="stylesheet" href="assets/styles/gen_style.css">
        <link rel="stylesheet" href="assets/styles/header_style.css">
        <link rel="stylesheet" href="assets/styles/caja.css">
        <link rel="icon" href="assets/img/width_800.ico">
    </head>

    <body>
        <?php include "../../models/header.php" ?>
        <h1 class="titulo">Cuadre de caja</h1>
        <section id="filtro_fechas">

            <form method="POST" class="filtro-card" action="controllers/getReporte.php">

                <h2>Filtro por Fecha</h2>

                <div>
                    <label for="desde">Desde:</label>
                    <input type="datetime-local" id="desde" name="desde" value="<?= isset($_GET['desde']) ? $_GET['desde'] : '' ?>" required>
                </div>

                <div>
                    <label for="hasta">Hasta:</label>
                    <input type="datetime-local" id="hasta" name="hasta" value="<?= isset($_GET['hasta']) ? $_GET['hasta'] : '' ?>" required>
                </div>

                <p id="error_fecha" style="color: red; text-align: center; font-size: 14px; display: none;">
                    La fecha "Hasta" no puede ser menor que "Desde".
                </p>

                <button type="submit">Buscar</button>

            </form>

            <?php
            if (isset($_SESSION["resumen"])) {
                $r = $_SESSION["resumen"];
                $efectivo = isset($r["efectivo"]) ? $r["efectivo"] : 0;
                $transferencia = isset($r["transferencia"]) ? $r["transferencia"] : 0;
                $credito = isset($r["credito"]) ? $r["credito"] : 0;
                $abonos_ef = isset($r["abonos"]["efectivo"]) ? $r["abonos"]["efectivo"] : 0;
                $abonos_tr = isset($r["abonos"]["transferencia"]) ? $r["abonos"]["transferencia"] : 0;
                $rentabilidad = isset($r["rentabilidad"]) ? $r["rentabilidad"] : 0;
            ?>
                <div class="resumen-ventas">
                    <div class="card-resumen">
                        <h2>Resumen de ventas</h2>
                        <hr>
                        <p>Ventas en efectivo: <b>$<?= number_format($efectivo, 0, ',', '.') ?></b></p>
                        <p>Ventas en transferencia: <b>$<?= number_format($transferencia, 0, ',', '.') ?></b></p>
                        <p>Ventas a crédito (saldo pendiente): <b>$<?= number_format($credito, 0, ',', '.') ?></b></p>
                        <p>Total: <b>$<?= number_format($credito + $transferencia + $efectivo, 0, ',', '.') ?></b></p>

                        <hr style="margin-top:12px; margin-bottom:6px;">
                        <h3 style="text-align:left; margin-bottom:8px; color:var(--deg2);">Abonos</h3>
                        <p>- Abonos en efectivo: <b>$<?= number_format($abonos_ef, 0, ',', '.') ?></b></p>
                        <p>- Abonos por transferencia: <b>$<?= number_format($abonos_tr, 0, ',', '.') ?></b></p>
                        <p>- Total abonos: <b>$<?= number_format($abonos_tr + $abonos_ef, 0, ',', '.') ?></b></p>


                        <hr style="margin-top:12px; margin-bottom:6px;">
                        <p>Total: <b>$<?= number_format($abonos_tr + $abonos_ef + $credito + $transferencia + $efectivo, 0, ',', '.') ?></b></p>
                        <p>Rentabilidad: <b>$<?= number_format($rentabilidad, 0, ',', '.') ?></b></p>
                        <p>Ventas: <b><?= $r["ventas"] ?></b></p>

                    </div>
                </div>
            <?php
                unset($_SESSION["resumen"]);
                unset($_SESSION["desde"]);
                unset($_SESSION["hasta"]);
            }
            ?>


        </section>
        <script>
            const form = document.querySelector('.filtro-card');
            const desde = document.getElementById("desde");
            const hasta = document.getElementById("hasta");
            const errorFecha = document.getElementById("error_fecha");

            function validarFechas() {
                const vDesde = (desde.value || "").trim();
                const vHasta = (hasta.value || "").trim();

                if (!vDesde || !vHasta) {
                    errorFecha.style.display = "none";
                    desde.style.border = "1px solid var(--td_tab1)";
                    hasta.style.border = "1px solid var(--td_tab1)";
                    return true;
                }

                if (vHasta < vDesde) {
                    errorFecha.style.display = "block";
                    hasta.style.border = "2px solid red";
                    return false;
                }

                errorFecha.style.display = "none";
                desde.style.border = "1px solid var(--td_tab1)";
                hasta.style.border = "1px solid var(--td_tab1)";
                return true;
            }

            desde.addEventListener("input", validarFechas);
            hasta.addEventListener("input", validarFechas);
            desde.addEventListener("change", validarFechas);
            hasta.addEventListener("change", validarFechas);

            form.addEventListener("submit", function(e) {
                if (!validarFechas()) {
                    e.preventDefault();
                    hasta.focus();
                    hasta.animate([{
                        transform: "translateX(-6px)"
                    }, {
                        transform: "translateX(6px)"
                    }, {
                        transform: "translateX(0)"
                    }], {
                        duration: 200
                    });
                    return false;
                }
            });
        </script>

        <script src="assets/js/menu.js"></script>
    </body>

    </html>

<?php

} else {
    header("Location: cerrar");
}
