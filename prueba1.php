<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        :root {
            --deg1: #5de0e6;
            --deg2: #004aad;
            --font: 'Helvetica';
            --deg_btn1: #b290f1;
            --deg_btn2: #aedcf3;
            --deg_btn_h1: #b3d6f2;
            --deg_btn_h2: #98bcd9;
            --bgtab: #e5f4ff;
            --th_tab: #99acff;
            --td_tab1: #cdd6ff;
            --td_tab2: #e3e8ff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font);
        }

        body {
            display: grid;
            grid-template:
                "header" 15vh
                "title" 10vh
                "section" 75vh;
        }

        /* HEADER */
        header {
            grid-area: header;
            background: linear-gradient(to right, var(--deg1), var(--deg2));
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            user-select: none;
        }

        .btn_menu {
            font-size: 28px;
            background: none;
            border: none;
            cursor: pointer;
            color: white;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            left: -260px;
            top: 0;
            width: 260px;
            height: 100%;
            background: #1a1a1a;
            padding-top: 60px;
            transition: 0.3s;
            z-index: 999;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            padding: 15px 20px;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
        }

        .sidebar ul li:hover {
            background: #333;
        }

        .sidebar.active {
            left: 0;
        }

        /* OVERLAY */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #00000085;
            display: none;
            z-index: 1;
        }

        .overlay.active {
            display: block;
        }

        /* TARJETA FILTRO FECHAS */
        .filtro-card {
            background: var(--bgtab);
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 4px 10px #00000030;
            width: 420px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* TARJETA RESUMEN */
        .resumen-ventas {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .card-resumen {
            width: 90%;
            max-width: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 10px #00000030;
            padding: 20px;
            border: 1px solid var(--td_tab1);
        }

        .card-resumen h2 {
            text-align: center;
            margin-bottom: 15px;
            color: var(--deg2);
        }

        .card-resumen p {
            font-size: 15px;
            margin: 8px 0;
        }

        .card-resumen span {
            font-weight: bold;
            color: var(--deg2);
        }

        .rent {
            font-size: 17px;
            font-weight: bold;
            color: var(--deg2);
        }
    </style>

</head>

<body>

    <!-- HEADER -->
    <header>
        <button id="btn_menu" class="btn_menu">☰</button>

        <div class="header_s2">
            <img src="assets/img/width_800.ico" alt="" width="90">
        </div>

        <div class="header_s3">
            <p>Perfil</p>
        </div>
    </header>

    <!-- SIDEBAR -->
    <div id="sidebar" class="sidebar">
        <ul>
            <li><a href="../inicio.php">Inicio</a></li>
            <li><a href="../productos/">Productos</a></li>
            <li><a href="../clientes/">Clientes</a></li>
            <li><a href="../ventas/">Ventas</a></li>
            <li><a href="../reportes/">Reportes</a></li>
            <li><a href="../logout.php">Cerrar sesión</a></li>
        </ul>
    </div>

    <div id="overlay" class="overlay"></div>

    <!-- SECTION PRINCIPAL -->
    <section id="filtro_fechas" style="
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 30px;
        position: relative;
        z-index: 5;
    ">

        <!-- TARJETA FILTRO -->
        <form method="POST" class="filtro-card" action="procesar.php">

            <h2 style="color: var(--deg2); text-align: center;">Filtro por Fecha</h2>

            <!-- FECHA DESDE -->
            <div>
                <label for="desde" style="font-weight: bold; font-size: 17px; color: var(--deg2);">Desde:</label>
                <input type="datetime-local" id="desde" name="desde" style="
            margin-top: 5px;
            padding: 10px;
            font-size: 16px;
            border-radius: 10px;
            border: 1px solid var(--td_tab1);
            background: var(--td_tab2);
        " value="<?= isset($_GET['desde']) ? $_GET['desde'] : '' ?>">
            </div>

            <!-- FECHA HASTA -->
            <div>
                <label for="hasta" style="font-weight: bold; font-size: 17px; color: var(--deg2);">Hasta:</label>
                <input type="datetime-local" id="hasta" name="hasta" style="
            margin-top: 5px;
            padding: 10px;
            font-size: 16px;
            border-radius: 10px;
            border: 1px solid var(--td_tab1);
            background: var(--td_tab2);
        " value="<?= isset($_GET['hasta']) ? $_GET['hasta'] : '' ?>">
            </div>

            <p id="error_fecha" style="color: red; text-align: center; font-size: 14px; display: none;">
                La fecha "Hasta" no puede ser menor que "Desde".
            </p>

            <!-- BOTÓN ENVIAR -->
            <button type="submit" style="
        padding: 12px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(to right, var(--deg_btn1), var(--deg_btn2));
        color: white;
        font-size: 18px;
        cursor: pointer;
        margin-top: 10px;
    ">
                Buscar
            </button>

        </form>


        <!-- PRINT_R POST -->
        <pre>
<?php print_r($_POST); ?>
        </pre>

        <!-- TARJETA RESUMEN -->
        <?php
        if (isset($_SESSION["resumen"])) {
            $r = $_SESSION["resumen"];
        ?>
            <section class="resumen-card">
                <h2 style="color: var(--deg2); text-align:center;">Resumen de ventas</h2>

                <p><b>Desde:</b> <?= $_SESSION["desde"] ?></p>
                <p><b>Hasta:</b> <?= $_SESSION["hasta"] ?></p>
                <hr>

                <p>Ventas en transferencia: <b><?= $r["transferencia"] ?></b></p>
                <p>Ventas en efectivo: <b><?= $r["efectivo"] ?></b></p>
                <p>Ventas mixtas: <b><?= $r["mixtas"] ?></b></p>
                <p>Ventas a crédito: <b><?= $r["credito"] ?></b></p>
                <p>Rentabilidad: <b><?= $r["rentabilidad"] ?></b></p>
            </section>
        <?php
            // Limpia después de mostrar
            unset($_SESSION["resumen"]);
            unset($_SESSION["desde"]);
            unset($_SESSION["hasta"]);
        }
        ?>


    </section>

    <!-- VALIDACIÓN FECHAS -->
    <script>
        const desde = document.getElementById("desde");
        const hasta = document.getElementById("hasta");
        const errorFecha = document.getElementById("error_fecha");

        function validarFechas() {
            if (!desde.value || !hasta.value) {
                errorFecha.style.display = "none";
                hasta.style.border = "1px solid var(--td_tab1)";
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

    <!-- MENÚ LATERAL -->
    <script>
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("overlay");
        const btnMenu = document.getElementById("btn_menu");

        btnMenu.addEventListener("click", () => {
            sidebar.classList.add("active");
            overlay.classList.add("active");
        });

        overlay.addEventListener("click", () => {
            sidebar.classList.remove("active");
            overlay.classList.remove("active");
        });
    </script>

</body>

</html>