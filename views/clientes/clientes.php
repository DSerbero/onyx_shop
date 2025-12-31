<?php
include "../../config/db.php";
include "../../controllers/session.php";
include "../../controllers/getClients.php";

if ($_SESSION["cargo"] === "gerente" || $_SESSION["cargo"] === "admin" || $_SESSION["cargo"] === "code") {
?>

    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Clientes</title>
        <link rel="stylesheet" href="assets/styles/gen_style.css">
        <link rel="stylesheet" href="assets/styles/header_style.css">
        <link rel="stylesheet" href="assets/styles/clientes.css">
        <link rel="icon" href="assets/img/width_800.ico">
    </head>

    <body>

        <?php include "../../models/header.php"; ?>

        <h1 class="titulo">Clientes</h1>

        <section>
            <div class="tab_productos">
                <input type="text" id="filtro" placeholder="Buscar por nombre o documento">

                <table id="tabla">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Dirección</th>
                            <th>Correo</th>
                            <th>Referencia 1</th>
                            <th>Referencia 2</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <!-- MODAL REFERENCIA -->
        <div id="modalReferencia" class="modal">
            <div class="modal-content">
                <h3>Referencia</h3>
                <ul id="listaReferencia"></ul>
                <button onclick="cerrarModalReferencia()">Cerrar</button>
            </div>
        </div>

        <!-- MODAL EDITAR -->
        <div id="modalEditar" class="modal">
            <div class="modal-content">
                <h3>Editar cliente</h3>

                <form id="formEditarCliente">
                    <input type="hidden" name="id_cliente" id="edit_id">

                    <label>Nombre</label>
                    <input type="text" name="nombre" id="edit_nombre" required>

                    <label>Documento</label>
                    <input type="text" name="documento" id="edit_documento" required>

                    <label>Dirección</label>
                    <input type="text" name="direccion" id="edit_direccion">

                    <label>Correo</label>
                    <input type="email" name="correo" id="edit_correo">

                    <div class="modal-actions">
                        <button type="submit">Guardar</button>
                        <button type="button" onclick="cerrarEditar()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="toast-container"></div>

        <script src="assets/js/menu.js"></script>

        <script>
            const clientes = <?php echo $json_clientes ?? '[]'; ?>;
            const tbody = document.querySelector("#tabla tbody");
            const filtro = document.getElementById("filtro");

            function cargarClientes() {
                tbody.innerHTML = "";

                clientes.forEach(c => {
                    const tr = document.createElement("tr");

                    tr.innerHTML = `
            <td>${c.nombre}</td>
            <td>${c.documento}</td>
            <td>${c.direccion}</td>
            <td>${c.correo}</td>

            <td>
                ${c.referencia1 && c.referencia1.trim() !== "-" 
                    ? `<button onclick="verReferencia('${c.referencia1.replace(/'/g,"\\'")}')">Ver</button>` 
                    : ""}
            </td>

            <td>
                ${c.referencia2 && c.referencia2.trim() !== "-" 
                    ? `<button onclick="verReferencia('${c.referencia2.replace(/'/g,"\\'")}')">Ver</button>` 
                    : ""}
            </td>

            <td>
                <span class="estado ${c.estado == 1 ? 'activo' : 'inactivo'}">
                    ${c.estado == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>

            <td>
                <button onclick="editar(${c.id_cliente})">Editar</button>
                <button onclick="toggleEstado(${c.id_cliente})">
                    ${c.estado == 1 ? 'Desactivar' : 'Activar'}
                </button>
                <button onclick="verHistorial('${encodeURIComponent(c.nombre)}')">
                    Historial
                </button>
            </td>
        `;

                    tbody.appendChild(tr);
                });
            }

            cargarClientes();

            /* FILTRO */
            filtro.addEventListener("keyup", function() {
                const texto = this.value.toLowerCase();
                Array.from(tbody.rows).forEach(row => {
                    row.style.display =
                        row.cells[0].textContent.toLowerCase().includes(texto) ||
                        row.cells[1].textContent.toLowerCase().includes(texto) ?
                        "" : "none";
                });
            });

            /* EDITAR */
            function editar(id) {
                const c = clientes.find(x => x.id_cliente == id);
                if (!c) return;

                edit_id.value = c.id_cliente;
                edit_nombre.value = c.nombre;
                edit_documento.value = c.documento;
                edit_direccion.value = c.direccion;
                edit_correo.value = c.correo;

                modalEditar.style.display = "flex";
            }

            function cerrarEditar() {
                modalEditar.style.display = "none";
            }

            /* SUBMIT EDITAR */
            document.getElementById("formEditarCliente").addEventListener("submit", e => {
                e.preventDefault();
                const formData = new FormData(e.target);

                fetch("controllers/editar_cliente.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === "success") {
                            toast("Cliente actualizado", "success");
                            cerrarEditar();

                            setTimeout(() => {
                                location.reload();
                            }, 1200);
                        } else {
                            toast(res.msg || "Error al actualizar", "error");
                        }
                    })

                    .catch(() => toast("Error de conexión", "error"));
            });

            function toggleEstado(id) {
                if (!confirm("¿Cambiar estado del cliente?")) return;

                fetch("controllers/toggle_estado_cliente.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `id_cliente=${id}`
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === "success") {

                            toast(
                                res.nuevo_estado == 1 ?
                                "Cliente activado correctamente" :
                                "Cliente desactivado correctamente",
                                "info"
                            );

                            setTimeout(() => {
                                location.reload();
                            }, 1200);

                        } else {
                            toast(res.msg || "Error al cambiar estado", "error");
                        }
                    })
                    .catch(() => toast("Error de conexión", "error"));
            }



            /* HISTORIAL */
            function verHistorial(nombre) {
                window.location.href = `historial?cliente=${nombre}`;
            }

            /* REFERENCIAS */
            function verReferencia(texto) {
                const partes = texto.split(" - ");
                const lista = document.getElementById("listaReferencia");
                lista.innerHTML = "";

                ["Nombre", "Teléfono", "Dirección"].forEach((label, i) => {
                    if (partes[i]) {
                        const li = document.createElement("li");
                        li.innerHTML = `<b>${label}:</b> ${i === 2 ? partes.slice(2).join(" - ") : partes[i]}`;
                        lista.appendChild(li);
                    }
                });

                modalReferencia.style.display = "flex";
            }

            function cerrarModalReferencia() {
                modalReferencia.style.display = "none";
            }

            /* TOAST */
            function toast(msg, type = "info") {
                const t = document.createElement("div");
                t.className = `toast ${type}`;
                t.textContent = msg;
                document.getElementById("toast-container").appendChild(t);
                setTimeout(() => t.remove(), 3500);
            }
        </script>

    </body>

    </html>

<?php
} else {
    header("Location: cerrar");
}
?>