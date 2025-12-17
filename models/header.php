<?php

include "../../controllers/getSession.php";

?>

<header>
    <div class="header_s1">
        <button id="btn_menu" class="btn_menu">
            <img src="assets/img/menu.ico" alt="">
        </button>
        <div id="sidebar" class="sidebar">
            <ul>
                <li><a href="venta">Crear venta</a></li>
                <li><a href="productos">Ver productos</a></li>
                <li><a href="agregar">Ingresar productos</a></li>
                <li><a href="clientes">Ver clientes</a></li>
                <li><a href="reportes">Cuadre de caja</a></li>
                <li><a href="historial">Historial de ventas</a></li>
                <li><a href="cerrar">Cerrar Sesion</a></li>

            </ul>
        </div>
        <div id="overlay" class="overlay"></div>
    </div>
    <div class="header_s2">
        <img src="assets/img/width_800.ico" alt="">
    </div>
    <div class="header_s3">
        <img src="assets/img/perfil.ico" alt="">
        <span><?php echo $_SESSION["nombre"];?></span>
        <span><?php echo getSession();?></span>
    </div>
</header>
