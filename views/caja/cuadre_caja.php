<?php
include "../../controllers/session.php";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuadre de caja</title>
    <link rel="stylesheet" href="assets/styles/gen_style.css">
    <link rel="stylesheet" href="assets/styles/header_style.css">
</head>

<body>
    <?php include "../../models/header.php" ?>
    <section id="filtro_fechas">

        <!-- TARJETA FILTRO -->
        <form method="POST" class="filtro-card" action="controllers/getReporte.php">

            <h2>Filtro por Fecha</h2>

            <!-- FECHA DESDE -->
            <div>
                <label for="desde">Desde:</label>
                <input type="datetime-local" id="desde" name="desde" value="<?= isset($_GET['desde']) ? $_GET['desde'] : '' ?>" required>
            </div>

            <!-- FECHA HASTA -->
            <div>
                <label for="hasta">Hasta:</label>
                <input type="datetime-local" id="hasta" name="hasta" value="<?= isset($_GET['hasta']) ? $_GET['hasta'] : '' ?>" required>
            </div>

            <p id="error_fecha" style="color: red; text-align: center; font-size: 14px; display: none;">
                La fecha "Hasta" no puede ser menor que "Desde".
            </p>

            <!-- BOTÓN ENVIAR -->
            <button type="submit">Buscar</button>

        </form>

        <!-- TARJETA RESUMEN -->
        <?php
        if (isset($_SESSION["resumen"])) {
            $r = $_SESSION["resumen"];
            // manejar índices faltantes por seguridad
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

                    <hr style="margin-top:12px; margin-bottom:6px;">
                    <h3 style="text-align:left; margin-bottom:8px; color:var(--deg2);">Abonos</h3>
                    <p>- Abonos en efectivo: <b>$<?= number_format($abonos_ef, 0, ',', '.') ?></b></p>
                    <p>- Abonos por transferencia: <b>$<?= number_format($abonos_tr, 0, ',', '.') ?></b></p>

                    <hr style="margin-top:12px; margin-bottom:6px;">
                    <p>Rentabilidad: <b>$<?= number_format($rentabilidad, 0, ',', '.') ?></b></p>
                </div>
            </div>
        <?php
            // limpiar sesión
            unset($_SESSION["resumen"]);
            unset($_SESSION["desde"]);
            unset($_SESSION["hasta"]);
        }
        ?>


    </section>
    <script>
        const desde = document.getElementById("desde");
        const hasta = document.getElementById("hasta");
        const errorFecha = document.getElementById("error_fecha");

        function validarFechas() {
            if (!desde.value || !hasta.value) {
                errorFecha.style.display = "none";
                return;
            }

            let d1 = new Date(desde.value);
            let d2 = new Date(hasta.value);

            if (d2 < d1) {
                errorFecha.style.display = "block";
                hasta.style.border = "2px solid red";
            } else {
                errorFecha.style.display = "none";
                hasta.style.border = "1px solid var(--td_tab1)";
            }
        }

        desde.addEventListener("change", validarFechas);
        hasta.addEventListener("change", validarFechas);
    </script>
    <script src="assets/js/menu.js"></script>
</body>

</html>