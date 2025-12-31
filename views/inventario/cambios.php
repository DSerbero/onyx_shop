<?php
include "../../controllers/session.php";
include "../../controllers/getAct.php";

if ($_SESSION["cargo"] === "gerente" || $_SESSION["cargo"] === "admin" || $_SESSION["cargo"] === "code") {
?>

    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Agregar productos</title>
        <link rel="stylesheet" href="assets/styles/gen_style.css">
        <link rel="stylesheet" href="assets/styles/registro.css">
        <link rel="stylesheet" href="assets/styles/header_style.css">
        <link rel="icon" href="assets/img/width_800.ico">
    </head>

    <body>
        <?php
        include "../../models/header.php"
        ?>
        <div class="titulo">
            <h1>Registro de compras</h1>
        </div>
        <section>
            <div class="tab_reportes">
                <input type="text" name="filtro" id="filtro" placeholder="Buscar producto">
                <table id="tabla">
                    <thead>
                        <tr>
                            <th># Ingreso</th>
                            <th>Codigo de producto</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Cantidad</th>
                            <th>Ingreso</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($result as $r) {
                        ?>
                            <tr>

                                <td><?= $r["id_compra"] ?></td>
                                <td><?= $r["codigo"] ?></td>
                                <td><?= date("d/m/Y", strtotime($r["fecha_compra"])) ?></td>
                                <td><?= date("H:i:s", strtotime($r["fecha_compra"])) ?></td>
                                <td><?= $r["cantidad"] ?></td>
                                <td><?= $r["ingreso"] ?></td>
                                <td>
                                    <button class="btn-delete" data-id="<?= $r["id_compra"] ?>" data-code="<?= $r["codigo"] ?>" data-cantidad="<?= $r["cantidad"] ?>"><img src="assets/img/delete.png" alt="Eliminar"></button>
                                </td>
                            </tr>

                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </section>
        <div id="modalConfirm" class="modal">
            <div class="modal-content">
                <h2>¿Deseas eliminar este producto?</h2>

                <div>
                    <button id="btnConfirmYes" style="cursor:pointer;">Eliminar</button>
                    <button id="btnConfirmNo" style="cursor:pointer;">Cancelar</button>
                </div>
            </div>
        </div>
        <div id="toast-container"></div>
        <script src="assets/js/menu.js"></script>
        <script>
            const filtro = document.getElementById("filtro");
            const tabla = document.querySelector("#tabla tbody");

            filtro.addEventListener("keyup", function() {
                const texto = this.value.toLowerCase();

                for (let fila of tabla.rows) {
                    const celdaCodigo = fila.cells[1]; // segunda columna
                    const valor = celdaCodigo.textContent.toLowerCase();

                    fila.style.display = valor.includes(texto) ? "" : "none";
                }
            });
        </script>

        <script>
            function toast(message, type = "info") {
                const container = document.getElementById("toast-container");

                const toast = document.createElement("div");
                toast.className = `toast ${type}`;
                toast.textContent = message;

                container.appendChild(toast);

                setTimeout(() => {
                    toast.remove();
                }, 3500);
            }
        </script>
        <script>
            const modalConfirm = document.getElementById("modalConfirm");
            const btnConfirmYes = document.getElementById("btnConfirmYes");
            const btnConfirmNo = document.getElementById("btnConfirmNo");

            let btnDeleteTemp = null;

            btnConfirmNo.onclick = () => {
                modalConfirm.style.display = "none";
                btnDeleteTemp = null;
            };
            window.onclick = (e) => {
                if (e.target === modalConfirm) modalConfirm.style.display = "none";
            };

            tabla.addEventListener("click", function(e) {
                const btn = e.target.closest("button");
                if (!btn) return;

                if (btn.classList.contains("btn-delete")) {
                    btnDeleteTemp = btn;
                    modalConfirm.style.display = "flex";
                    return;
                }


            });

            btnConfirmYes.onclick = () => {
                if (!btnDeleteTemp) return;

                const data = btnDeleteTemp.dataset;

                const body = new URLSearchParams(data).toString();

                fetch("controllers/delete_reg.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            btnDeleteTemp.closest("tr")?.remove();
                            toast("Producto eliminado correctamente", "success");
                        } else {
                            toast("Error al eliminar producto", "error");
                        }
                        btnDeleteTemp = null;
                    })
                    .catch(() => toast("Error de conexión", "error"));

                modalConfirm.style.display = "none";
            };
        </script>
    </body>

    </html>

<?php

} else {
    header("Location: cerrar");
}

?>