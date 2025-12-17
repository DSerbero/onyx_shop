<?php
include "../../controllers/session.php";
include "../../controllers/getProducts.php";

if ($_SESSION["cargo"] === "gerente" || $_SESSION["cargo"] === "admin" || $_SESSION["cargo"] === "code") {

?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Productos</title>
        <link rel="stylesheet" href="assets/styles/gen_style.css">
        <link rel="stylesheet" href="assets/styles/header_style.css">
        <link rel="stylesheet" href="assets/styles/productos.css">
        <style>
        </style>
    </head>

    <body>
        <?php include "../../models/header.php"; ?>
        <h1 class="titulo">Productos</h1>

        <section>
            <div class="tab_productos">
                <input type="text" name="filtro" id="filtro" placeholder="Buscar producto">
                <table id="tabla">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Tipo de producto</th>
                            <th>Costo del producto</th>
                            <th>Precio de venta</th>
                            <th>Cantidad</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?= $producto["codigo"] ?></td>
                                <td><?= $producto["nombre"] ?></td>
                                <td><?= $producto["categoria"] ?></td>
                                <td><?= $producto["tipo_de_producto"] ?></td>
                                <td><?= $producto["costo"] ?></td>
                                <td><?= $producto["venta"] ?></td>
                                <td class="cantidad"><?= $producto["cantidad"] ?></td>
                                <td>
                                    <button class="btn-editar" data-id="<?= $producto['id_producto'] ?>"><img src="assets/img/editar.ico" alt=""></button>
                                    <button class="btn-add" data-id="<?= $producto['id_producto'] ?>" data-min="<?= $producto['cantidad_minima'] ?>"><img src="assets/img/add.png" alt=""></button>
                                    <button class="btn-delete" data-id="<?= $producto['id_producto'] ?>"><img src="assets/img/delete.png" alt="Eliminar"></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div id="modalEditar" class="modal">
            <div class="modal-content">
                <span class="cerrar">&times;</span>
                <form id="formEditar" method="post">
                    <input type="hidden" id="edit_id" name="edit_id">
                    <table>
                        <tr>
                            <th colspan="2">
                                <h2>Editar producto</h2>
                            </th>
                        </tr>
                        <tr>
                            <th><label for="edit_codigo">Código</label></th>
                            <td><input type="text" id="edit_codigo" name="codigo" readonly></td>
                        </tr>
                        <tr>
                            <th><label for="edit_nombre">Nombre del producto</label></th>
                            <td><input type="text" id="edit_nombre" name="nombre" required></td>
                        </tr>
                        <tr>
                            <th><label for="edit_categoria">Categoría</label></th>
                            <td>
                                <select id="edit_categoria" name="categoria" required>
                                    <option value="">Seleccionar</option>
                                    <option value="importada">Importada</option>
                                    <option value="nacional">Nacional</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="edit_tipo">Tipo de producto</label></th>
                            <td>
                                <select id="edit_tipo" name="tipo" required>
                                    <option value="">Seleccionar</option>
                                    <option value="jean">Jean</option>
                                    <option value="camisa">Camisa</option>
                                    <option value="blusa">Blusa</option>
                                    <option value="medias">Medias</option>
                                    <option value="tenis">Tenis</option>
                                    <option value="maquillaje">Maquillaje</option>
                                    <option value="ropa interior">Ropa interior</option>
                                    <option value="accesorio">Accesorio</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="edit_costo">Costo del producto</label></th>
                            <td><input type="number" id="edit_costo" name="costo" required></td>
                        </tr>
                        <tr>
                            <th><label for="edit_venta">Precio de venta</label></th>
                            <td><input type="number" id="edit_venta" name="venta" required></td>
                        </tr>
                    </table>
                    <input type="submit" value="Guardar cambios" name="act">
                </form>
            </div>
        </div>

        <div id="modalAdd" class="modal">
            <div class="modal-content">
                <span class="cerrar-add">&times;</span>
                <form method="post" id="formAdd">
                    <input type="hidden" id="add_id" name="add_id">
                    <table>
                        <tr>
                            <th colspan="2">
                                <h2>Agregar cantidad</h2>
                            </th>
                        </tr>
                        <tr>
                            <th><label for="add_cantidad">Cantidad a agregar</label></th>
                            <td><input type="number" id="add_cantidad" name="add_cantidad" required min="1"></td>
                        </tr>
                        <tr>
                            <th><label for="add_min">Cantidad mínima</label></th>
                            <td><input type="number" id="add_min" name="add_min" min="1"></td>
                        </tr>
                    </table>
                    <input type="submit" value="Agregar" name="add">
                </form>
            </div>
        </div>

        <div id="toast-container"></div>

        <div id="modalConfirm" class="modal">
            <div class="modal-content">
                <h2>¿Deseas eliminar este producto?</h2>

                <div>
                    <button id="btnConfirmYes" style="cursor:pointer;">Eliminar</button>
                    <button id="btnConfirmNo" style="cursor:pointer;">Cancelar</button>
                </div>
            </div>
        </div>



        <script src="assets/js/menu.js"></script>

        <script>
            const filtro = document.getElementById("filtro");
            const tabla = document.querySelector("#tabla tbody");

            filtro.addEventListener("keyup", function() {
                const texto = this.value.toLowerCase();
                for (let fila of tabla.rows) {
                    fila.style.display = fila.innerText.toLowerCase().includes(texto) ? "" : "none";
                }
            });
        </script>

        <script>
            const modalEditar = document.getElementById("modalEditar");
            const modalAdd = document.getElementById("modalAdd");
            const modalConfirm = document.getElementById("modalConfirm");

            const cerrarEditar = modalEditar.querySelector(".cerrar");
            const cerrarAdd = modalAdd.querySelector(".cerrar-add");

            const btnConfirmYes = document.getElementById("btnConfirmYes");
            const btnConfirmNo = document.getElementById("btnConfirmNo");

            let btnDeleteTemp = null;

            cerrarEditar.onclick = () => modalEditar.style.display = "none";
            cerrarAdd.onclick = () => modalAdd.style.display = "none";

            btnConfirmNo.onclick = () => {
                modalConfirm.style.display = "none";
                btnDeleteTemp = null;
            };

            window.onclick = (e) => {
                if (e.target === modalEditar) modalEditar.style.display = "none";
                if (e.target === modalAdd) modalAdd.style.display = "none";
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

                if (btn.classList.contains("btn-editar")) {
                    const fila = btn.closest("tr").children;

                    document.getElementById("edit_id").value = btn.dataset.id;
                    document.getElementById("edit_codigo").value = fila[0].innerText;
                    document.getElementById("edit_nombre").value = fila[1].innerText;
                    document.getElementById("edit_categoria").value = fila[2].innerText.toLowerCase();
                    document.getElementById("edit_tipo").value = fila[3].innerText.toLowerCase();
                    document.getElementById("edit_costo").value = fila[4].innerText;
                    document.getElementById("edit_venta").value = fila[5].innerText;

                    modalEditar.style.display = "flex";
                }

                if (btn.classList.contains("btn-add")) {
                    document.getElementById("add_id").value = btn.dataset.id;
                    document.getElementById("add_min").value = btn.dataset.min;
                    modalAdd.dataset.row = btn.closest("tr").rowIndex;

                    modalAdd.style.display = "flex";
                }
            });

            btnConfirmYes.onclick = () => {
                if (!btnDeleteTemp) return;

                const id = btnDeleteTemp.dataset.id;

                fetch("controllers/act_productos.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `delete_id=${encodeURIComponent(id)}`
                    })
                    .then(async res => {
                        const text = await res.text();
                        console.log("RAW =>", text);

                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            console.error("JSON inválido:", text);
                            toast("Respuesta inválida del servidor", "error");
                            return;
                        }

                        if (data.status === "success") {
                            const row = btnDeleteTemp.closest("tr");
                            if (row) row.remove();
                            toast("Producto eliminado correctamente", "success");
                        } else {
                            toast("Error al eliminar producto", "error");
                        }

                        btnDeleteTemp = null;
                    })
                    .catch(err => {
                        console.error("ERROR FETCH:", err);
                        toast("Error de conexión", "error");
                    });

                modalConfirm.style.display = "none";
            };
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
            const formEditar = document.getElementById("formEditar");

            formEditar.addEventListener("submit", function(e) {
                e.preventDefault();

                const formData = new FormData(formEditar);
                if (!formData.has('act')) formData.append('act', '1');

                const id = formData.get('edit_id');

                fetch("controllers/act_productos.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(async res => {
                        const txt = await res.text();
                        console.log('RAW EDITAR =>', txt);
                        let data;
                        try {
                            data = txt ? JSON.parse(txt) : null;
                        } catch (err) {
                            console.error('JSON parse error (editar):', err, txt);
                            toast('Respuesta inválida del servidor (editar)', 'error');
                            return;
                        }
                        if (!data) {
                            toast('Respuesta vacía del servidor (editar)', 'error');
                            return;
                        }

                        if (data.status === 'success') {
                            toast('Producto actualizado correctamente', 'success');

                            const editarBtn = document.querySelector(`button.btn-editar[data-id="${id}"]`);
                            if (editarBtn) {
                                const fila = editarBtn.closest('tr').children;
                                fila[1].innerText = formData.get('nombre') ?? fila[1].innerText;
                                fila[2].innerText = formData.get('categoria') ?? fila[2].innerText;
                                fila[3].innerText = formData.get('tipo') ?? fila[3].innerText;
                                fila[4].innerText = formData.get('costo') ?? fila[4].innerText;
                                fila[5].innerText = formData.get('venta') ?? fila[5].innerText;
                            } else {
                                console.warn('No se encontró fila para actualizar (editar)', id);
                            }

                            modalEditar.style.display = 'none';
                        } else {
                            toast('Error al actualizar: ' + (data.msg || ''), 'error');
                        }
                    })
                    .catch(err => {
                        console.error('ERROR fetch editar:', err);
                        toast('Error de conexión (editar)', 'error');
                    });
            });





            // --- AGREGAR CANTIDAD SIN RECARGAR ---
            const formAdd = document.getElementById("formAdd");

            formAdd.addEventListener("submit", function(e) {
                e.preventDefault();

                const id = document.getElementById("add_id").value;
                const cantidadAdd = parseInt(document.getElementById("add_cantidad").value);
                const cantidadMin = document.getElementById("add_min").value;

                fetch("controllers/act_productos.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `add=1&add_id=${id}&add_cantidad=${cantidadAdd}&add_min=${cantidadMin}`
                    })
                    .then(async res => {
                        const text = await res.text();
                        console.log("RAW ADD =>", text);

                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            toast("Respuesta inválida del servidor", "error");
                            return;
                        }

                        if (data.status === "success") {

                            // Actualizar cantidad en la tabla sin recargar
                            const fila = document.querySelector(`button.btn-add[data-id='${id}']`).closest("tr");
                            const celdaCantidad = fila.querySelector(".cantidad");
                            const cantidadActual = parseInt(celdaCantidad.innerText);
                            celdaCantidad.innerText = cantidadActual + cantidadAdd;

                            toast("Cantidad agregada correctamente", "success");
                            modalAdd.style.display = "none";

                            formAdd.reset();
                        } else {
                            toast("Error al agregar cantidad", "error");
                        }
                    })
                    .catch(err => {
                        console.error("ERROR ADD:", err);
                        toast("Error de conexión", "error");
                    });
            });
        </script>

    </body>

    </html>

<?php

} else {
    header("Location: login");
}
