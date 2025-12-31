<?php
require "../../controllers/getProducts.php";
require "../../controllers/getClients.php";
include "../../controllers/session.php";

if ($_SESSION["cargo"] === "gerente" || $_SESSION["cargo"] === "admin" || $_SESSION["cargo"] === "code") {

?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Proceso de venta</title>
        <link rel="stylesheet" href="assets/styles/gen_style.css">
        <link rel="stylesheet" href="assets/styles/venta_style.css">
        <link rel="stylesheet" href="assets/styles/header_style.css">
        <link rel="icon" href="assets/img/width_800.ico">
    </head>

    <body>
        <?php include "../../models/header.php" ?>
        <div class="titulo">
            <h1>Proceso de venta</h1>
        </div>
        <section>
            <form action="controllers/crearVenta.php" class="form_compra" method="post">
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

            <div id="modal_cliente" class="modal_cliente" style="display:none;">
                <div class="modal_contenido">
                    <table>
                        <tr>
                            <th colspan="2" class="title">
                                <h2>Información del cliente</h2>
                            </th>
                        </tr>
                        <tr>
                            <th><label>Nombre:</label></th>
                            <td><input type="text" id="modal_nombre"></td>
                        </tr>
                        <tr>
                            <th><label>Documento:</label></th>
                            <td><input type="text" id="modal_documento"></td>
                        </tr>
                        <tr>
                            <th><label>Dirección:</label></th>
                            <td><input type="text" id="modal_direccion"></td>
                        </tr>
                        <tr>
                            <th><label>Teléfono:</label></th>
                            <td><input type="text" id="modal_telefono"></td>
                        </tr>
                        <tr>
                            <th><label>Correo:</label></th>
                            <td><input type="text" id="modal_correo"></td>
                        </tr>
                        <tr>
                            <th><label>Referencia 1:</label></th>
                            <td><textarea id="modal_ref1" rows="3"></textarea></td>
                        </tr>
                        <tr>
                            <th><label>Referencia 2:</label></th>
                            <td><textarea id="modal_ref2" rows="3"></textarea></td>
                        </tr>

                    </table>
                    <div style="margin-top:12px; display:flex; gap:10px; justify-content:flex-end;">
                        <button type="button" id="cerrar_modal" class="btn_cerrar">Cerrar</button>
                    </div>
                </div>
            </div>


            <div id="modal_nuevo_cliente" class="modal_cliente" style="display:none;">
                <div class="modal_contenido">
                    <div class="tablas">
                        <table>
                            <tr>
                                <th colspan="2" class="title">
                                    <h2>Registrar nuevo cliente</h2>
                                </th>
                            </tr>
                            <tr>
                                <th><label>Nombre:</label></th>
                                <td><input type="text" id="nuevo_nombre"></td>
                            </tr>
                            <tr>
                                <th><label>Documento:</label></th>
                                <td><input type="text" id="nuevo_documento"></td>
                            </tr>
                            <tr>
                                <th><label>Dirección:</label></th>
                                <td><input type="text" id="nuevo_direccion"></td>
                            </tr>
                            <tr>
                                <th><label>Teléfono:</label></th>
                                <td><input type="text" id="nuevo_telefono"></td>
                            </tr>
                            <tr>
                                <th><label>Correo:</label></th>
                                <td><input type="email" id="nuevo_correo"></td>
                            </tr>

                        </table>
                        <table>
                            <tr>
                                <th colspan="2">
                                    <h2>Referencias personales</h2>
                                </th>
                            </tr>
                            <tr>
                                <th><label>Nombre:</label></th>
                                <td><input type="text" id="nom_nuevo_ref1"></td>
                            </tr>
                            <tr>
                                <th><label>Teléfono:</label></th>
                                <td><input type="text" id="tel_nuevo_ref1"></td>
                            </tr>
                            <tr>
                                <th><label>Dirección:</label></th>
                                <td><input type="text" id="dir_nuevo_ref1"></td>
                            </tr>
                            <tr>
                                <th><label>Nombre:</label></th>
                                <td><input type="text" id="nom_nuevo_ref2"></td>
                            </tr>
                            <tr>
                                <th><label>Teléfono:</label></th>
                                <td><input type="text" id="tel_nuevo_ref2"></td>
                            </tr>
                            <tr>
                                <th><label>Dirección:</label></th>
                                <td><input type="text" id="dir_nuevo_ref2"></td>
                            </tr>
                        </table>
                    </div>
                    <div style="margin-top:15px; display:flex; gap:10px; justify-content:flex-end;">
                        <button id="guardar_nuevo_cliente" class="btn_guardar" type="button">Guardar</button>
                        <button type="button" class="cerrar_modal_cliente">Cerrar</button>
                    </div>
                </div>
            </div>

        </section>
        <div id="modal_confirm" class="modal_cliente" style="display:none;">
            <div class="modal_contenido">
                <p id="modal_confirm_text"></p>

                <div style="margin-top:12px; display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" id="confirm_cancelar" class="btn_mo">Cancelar</button>
                    <button type="button" id="confirm_aceptar" class="btn_mo">Aceptar</button>
                </div>
            </div>
        </div>
        <div id="modal_alert" class="modal_cliente" style="display:none;">
            <div class="modal_contenido">
                <p id="modal_alert_text"></p>

                <div style="margin-top:12px; display:flex; justify-content:flex-end;">
                    <button type="button" id="alert_aceptar" class="btn_mo">Aceptar</button>
                </div>
            </div>
        </div>

        <script src="assets/js/tipo_venta.js"></script>
        <script>
            const productos = <?= $json_productos ?>;
            const clientes = <?= $json_clientes ?>;
        </script>
        <script src="assets/js/cargar_productos.js"></script>
        <script src="assets/js/menu.js"></script>
        <script>
            document.getElementById("btn_nuevo_cliente").addEventListener("click", () => {
                document.getElementById("modal_nuevo_cliente").style.display = "flex";
            });

            document.addEventListener("click", (e) => {
                if (e.target.classList.contains("cerrar_modal_cliente")) {
                    document.getElementById("modal_nuevo_cliente").style.display = "none";
                }
            });

            document.getElementById("guardar_nuevo_cliente").addEventListener("click", () => {
                const nombre = document.getElementById("nuevo_nombre").value.trim();
                const documento = document.getElementById("nuevo_documento").value.trim();
                const direccion = document.getElementById("nuevo_direccion").value.trim();
                const telefono = document.getElementById("nuevo_telefono").value.trim();
                const correo = document.getElementById("nuevo_correo").value.trim();
                const ref1Nombre = document.getElementById("nom_nuevo_ref1").value.trim();
                const ref1Telefono = document.getElementById("tel_nuevo_ref1").value.trim();
                const ref1Direccion = document.getElementById("dir_nuevo_ref1").value.trim();
                const ref2Nombre = document.getElementById("nom_nuevo_ref2").value.trim();
                const ref2Telefono = document.getElementById("tel_nuevo_ref2").value.trim();
                const ref2Direccion = document.getElementById("dir_nuevo_ref2").value.trim();

                const referencia1 = `${ref1Nombre} - ${ref1Telefono} - ${ref1Direccion}`.trim();
                const referencia2 = `${ref2Nombre} - ${ref2Telefono} - ${ref2Direccion}`.trim();


                const clienteNuevo = {
                    nombre,
                    documento,
                    direccion,
                    telefono,
                    correo,
                    referencia1: referencia1 || "",
                    referencia2: referencia2 || ""
                };

                window.clienteNuevo = clienteNuevo;
                document.getElementById("cliente_info").value = JSON.stringify(clienteNuevo);
                document.getElementById("modal_nuevo_cliente").style.display = "none";
            });


            document.getElementById("cerrar_modal").addEventListener("click", async () => {
                const guardar = await modalConfirm("¿Deseas guardar los cambios?");

                if (guardar) {
                    window.clienteSeleccionado = {
                        ...window.clienteEditado
                    };
                    inputCliente.value = window.clienteSeleccionado.nombre;
                    document.getElementById("cliente_info").value =
                        JSON.stringify(window.clienteSeleccionado);
                }

                document.getElementById("modal_cliente").style.display = "none";
            });;
        </script>
        <script>
            function modalConfirm(mensaje) {
                return new Promise(resolve => {
                    const modal = document.getElementById("modal_confirm");
                    const texto = document.getElementById("modal_confirm_text");
                    const btnOk = document.getElementById("confirm_aceptar");
                    const btnCancel = document.getElementById("confirm_cancelar");

                    texto.textContent = mensaje;
                    modal.style.display = "flex";

                    const limpiar = () => {
                        modal.style.display = "none";
                        btnOk.onclick = null;
                        btnCancel.onclick = null;
                    };

                    btnOk.onclick = () => {
                        limpiar();
                        resolve(true);
                    };

                    btnCancel.onclick = () => {
                        limpiar();
                        resolve(false);
                    };
                });
            }
        </script>
        <script>
            function modalAlert(mensaje) {
                return new Promise(resolve => {
                    const modal = document.getElementById("modal_alert");
                    const texto = document.getElementById("modal_alert_text");
                    const btn = document.getElementById("alert_aceptar");

                    texto.textContent = mensaje;
                    modal.style.display = "flex";

                    btn.onclick = () => {
                        modal.style.display = "none";
                        btn.onclick = null;
                        resolve();
                    };
                });
            }
        </script>
        <?php
        if (isset($_SESSION["venta_error"])) {
            if ($_SESSION["venta_error"] === "reg") {
        ?>
                <script>
                    modalAlert("El cliente ya se encuentra registrado o se encuentra inactivo.");
                </script>
            <?php
            } else {
            ?>
                <script>
                    modalAlert("Error al realizar la compra.");
                </script>
        <?php
            }
            unset($_SESSION["venta_error"]);
        }
        ?>

    </body>

    </html>
<?php
} else {
    header("Location: cerrar");
}
