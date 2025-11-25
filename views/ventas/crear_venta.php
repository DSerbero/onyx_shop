<?php
require "../../controllers/getProducts.php";
require "../../controllers/getClients.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proceso de venta</title>
    <link rel="stylesheet" href="assets/styles/gen_style.css">
    <link rel="stylesheet" href="assets/styles/venta_style.css">
</head>
<body>
    <?php include "../../models/header.php" ?>
    <div class="titulo">
        <h1>Proceso de venta</h1>
    </div>
    <section>
        <form action="prueba.php" class="form_compra" method="post">
            <div class="sec_1">
                <div class="cont_compra">
                    <h2>Lista de productos</h2>
                    <input type="text" id="producto" class="buscar" placeholder="Buscar producto" autocomplete="off">
                    <div id="lista_busqueda" class="lista"></div>

                    <div class="tabla_scroll">
                        <table id="tablaProductos">
                            <tr>
                                <th>Código del producto</th>
                                <th>Nombre</th>
                                <th>Cantidad</th>
                                <th>total</th>
                            </tr>
                        </table>
                    </div>
                    <div id="apartado_total" style="margin-top: 15px; font-weight: bold; font-size: 18px;">
                        Total de la compra: $<span id="total_compra">0</span>
                    </div>
                </div>
            </div>

            <div class="sec_2">
                <div class="cont_cli" id="cont_cli">
                    <h2>Identificación del cliente</h2>
                    <div class="cliente">
                        <input type="text" id="buscar_cliente" placeholder="Buscar cliente" class="buscar" autocomplete="off">
                        <button type="button" id="btn_nuevo_cliente" class="btn_add_cliente">
                            <img src="assets/img/add.png" alt="">
                        </button>
                    </div>
                    <div id="lista_clientes" class="lista"></div>

                    <div class="metodo_pag">
                        <input type="checkbox" name="" id="efectivo" class="btn_m" data-label="Efectivo">
                        <input type="checkbox" name="" id="transferencia" class="btn_m" data-label="Transferencia">
                        <input type="checkbox" name="" id="credito" class="btn_m" data-label="Credito">
                    </div>
                </div>
            </div>

            <div class="sec_3">
                <input type="hidden" name="productos_enviados" id="productos_enviados">
                <input type="hidden" name="pago_info" id="pago_info">
                <input type="hidden" name="id_cliente" id="id_cliente">
                <input type="hidden" name="cliente_info" id="cliente_info">
                <input type="submit" value="Crear venta" name="crear_venta">
            </div>
        </form>

        <!-- Modal editar/mostrar cliente existente -->
        <div id="modal_cliente" class="modal_cliente" style="display:none;">
            <div class="modal_contenido">
                <table>
                    <tr>
                        <th colspan="2" class="title"><h2>Información del cliente</h2></th>
                    </tr>
                    <tr><th><label>Nombre:</label></th><td><input type="text" id="modal_nombre"></td></tr>
                    <tr><th><label>Documento:</label></th><td><input type="text" id="modal_documento"></td></tr>
                    <tr><th><label>Dirección:</label></th><td><input type="text" id="modal_direccion"></td></tr>
                    <tr><th><label>Teléfono:</label></th><td><input type="text" id="modal_telefono"></td></tr>
                    <tr><th><label>Correo:</label></th><td><input type="text" id="modal_correo"></td></tr>
                </table>
                <div style="margin-top:12px; display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" id="cerrar_modal" class="btn_cerrar">Cerrar</button>
                </div>
            </div>
        </div>

        <!-- Modal crear nuevo cliente -->
        <div id="modal_nuevo_cliente" class="modal_cliente" style="display:none;">
            <div class="modal_contenido">
                <h2>Registrar nuevo cliente</h2>
                <div class="form_modal">
                    <label>Nombre:</label><input type="text" id="nuevo_nombre">
                    <label>Documento:</label><input type="text" id="nuevo_documento">
                    <label>Dirección:</label><input type="text" id="nuevo_direccion">
                    <label>Teléfono:</label><input type="text" id="nuevo_telefono">
                    <label>Correo:</label><input type="email" id="nuevo_correo">
                    <label>Referencia personal 1:</label><input type="text" id="nuevo_ref1">
                    <label>Referencia personal 2:</label><input type="text" id="nuevo_ref2">
                </div>
                <div style="margin-top:15px; display:flex; gap:10px; justify-content:flex-end;">
                    <button id="guardar_nuevo_cliente" class="btn_guardar" type="button">Guardar</button>
                    <button type="button" class="cerrar_modal_cliente">Cerrar</button>
                </div>
            </div>
        </div>

    </section>

    <script src="assets/js/tipo_venta.js"></script>
    <script>
        const productos = <?= $json_productos ?>;
        const clientes = <?= $json_clientes ?>;
    </script>
    <script src="assets/js/cargar_productos.js"></script>

    <!-- Cliente modal inline small helpers (keeps handlers with page) -->
    <script>
    // Abrir modal nuevo
    document.getElementById("btn_nuevo_cliente").addEventListener("click", () => {
        document.getElementById("modal_nuevo_cliente").style.display = "flex";
    });

    // Cerrar modal nuevo (delegado por clase)
    document.addEventListener("click", (e) => {
        if (e.target.classList.contains("cerrar_modal_cliente")) {
            document.getElementById("modal_nuevo_cliente").style.display = "none";
        }
    });

    // Guardar nuevo cliente (frontend only -> saved into cliente_info)
    document.getElementById("guardar_nuevo_cliente").addEventListener("click", () => {
        const nombre = document.getElementById("nuevo_nombre").value.trim();
        const documento = document.getElementById("nuevo_documento").value.trim();
        const direccion = document.getElementById("nuevo_direccion").value.trim();
        const telefono = document.getElementById("nuevo_telefono").value.trim();
        const correo = document.getElementById("nuevo_correo").value.trim();
        const ref1 = document.getElementById("nuevo_ref1").value.trim();
        const ref2 = document.getElementById("nuevo_ref2").value.trim();


        const clienteNuevo = {
            nombre,
            documento,
            direccion,
            telefono,
            correo,
            referencia1: ref1 || "",
            referencia2: ref2 || ""
        };

        window.clienteNuevo = clienteNuevo;
        document.getElementById("cliente_info").value = JSON.stringify(clienteNuevo);
        document.getElementById("modal_nuevo_cliente").style.display = "none";
    });

    // Cerrar / sincronizar modal cliente existente
    document.getElementById("cerrar_modal").addEventListener("click", () => {
        if (window.clienteSeleccionado) {
            document.getElementById("cliente_info").value = JSON.stringify(window.clienteSeleccionado);
        }
        document.getElementById("modal_cliente").style.display = "none";
    });
    </script>
</body>
</html>
