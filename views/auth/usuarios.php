<?php
include "../../controllers/session.php";
include "../../config/db.php";

if ($_SESSION["cargo"] !== "code") {
    header("Location: cerrar");
    exit;
}

$conn = connect();
$stmt = $conn->prepare("SELECT id, cargo, nombre, correo, estado FROM usuarios");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Config Usuarios</title>

    <link rel="stylesheet" href="assets/styles/gen_style.css">
    <link rel="stylesheet" href="assets/styles/header_style.css">
    <link rel="stylesheet" href="assets/styles/productos.css">
    <link rel="icon" href="assets/img/width_800.ico">

    <style>
        .inactivo {
            opacity: 0.45;
            background-color: #f3f3f3;
        }
    </style>
</head>

<body>

    <?php include "../../models/header.php"; ?>

    <h1 class="titulo">Usuarios</h1>

    <section>
        <div class="tab_productos">
            <input type="text" id="filtro" placeholder="Buscar usuario">

            <table id="tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cargo</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr class="<?= !$u["estado"] ? 'inactivo' : '' ?>">
                            <td><?= $u["id"] ?></td>
                            <td><?= $u["cargo"] ?></td>
                            <td><?= $u["nombre"] ?></td>
                            <td style="text-transform: lowercase;"><?= $u["correo"] ?></td>
                            <td>
                                <button class="btn-edit"
                                    data-id="<?= $u["id"] ?>"
                                    data-cargo="<?= $u["cargo"] ?>"
                                    data-nombre="<?= $u["nombre"] ?>"
                                    data-correo="<?= $u["correo"] ?>">
                                    ✏️
                                </button>

                                <button class="btn-toggle"
                                    data-id="<?= $u["id"] ?>"
                                    data-estado="<?= $u["estado"] ?>">
                                    <?= $u["estado"] ? "🚫" : "✅" ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- MODAL CONFIRMAR -->
    <div id="modalConfirm" class="modal">
        <div class="modal-content">
            <h2 id="confirmText"></h2>
            <div>
                <button id="btnConfirmYes">Aceptar</button>
                <button id="btnConfirmNo">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <h2>Editar usuario</h2>

            <form id="formEditar">
                <input type="hidden" id="edit_id" name="id">

                <label>Nombre</label>
                <input type="text" id="edit_nombre" name="nombre" required>

                <label>Correo</label>
                <input type="email" id="edit_correo" name="correo" required>

                <label>Cargo</label>
                <select id="edit_cargo" name="cargo" required>
                    <option value="admin">Admin</option>
                    <option value="gerente">Gerente</option>
                    <option value="code">Code</option>
                </select>

                <div style="margin-top:15px; display:flex; gap:10px;">
                    <button type="submit">Guardar</button>
                    <button type="button" id="btnCerrarEditar">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast-container"></div>
    <script src="assets/js/menu.js"></script>

    <script>
        /* FILTRO */
        const filtro = document.getElementById("filtro");
        const tbody = document.querySelector("#tabla tbody");

        filtro.addEventListener("keyup", () => {
            const texto = filtro.value.toLowerCase();
            [...tbody.rows].forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(texto) ? "" : "none";
            });
        });
    </script>

    <script>
        /* CONFIRMAR ACTIVAR / DESACTIVAR */
        const modalConfirm = document.getElementById("modalConfirm");
        const btnYes = document.getElementById("btnConfirmYes");
        const btnNo = document.getElementById("btnConfirmNo");
        const confirmText = document.getElementById("confirmText");

        let btnToggleTemp = null;

        tbody.addEventListener("click", e => {
            const btn = e.target.closest(".btn-toggle");
            if (!btn) return;

            btnToggleTemp = btn;
            confirmText.innerText = btn.dataset.estado == 1 ?
                "¿Desactivar este usuario?" :
                "¿Activar este usuario?";

            modalConfirm.style.display = "flex";
        });

        btnNo.onclick = () => {
            modalConfirm.style.display = "none";
            btnToggleTemp = null;
        };

        btnYes.onclick = () => {
            if (!btnToggleTemp) return;

            const id = btnToggleTemp.dataset.id;
            const nuevoEstado = btnToggleTemp.dataset.estado == 1 ? 0 : 1;

            fetch("controllers/act_usuarios.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `toggle_id=${id}&estado=${nuevoEstado}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {

                        btnToggleTemp.dataset.estado = nuevoEstado;
                        btnToggleTemp.innerText = nuevoEstado ? "🚫" : "✅";

                        btnToggleTemp.closest("tr")
                            .classList.toggle("inactivo", !nuevoEstado);

                        toast(nuevoEstado ? "Usuario activado" : "Usuario desactivado", "success");
                    } else {
                        toast("Error al cambiar estado", "error");
                    }
                })
                .catch(() => toast("Error de conexión", "error"));

            modalConfirm.style.display = "none";
        };
    </script>

    <script>
        /* TOAST */
        function toast(msg, type = "info") {
            const c = document.getElementById("toast-container");
            const t = document.createElement("div");
            t.className = `toast ${type}`;
            t.textContent = msg;
            c.appendChild(t);
            setTimeout(() => t.remove(), 3000);
        }
    </script>

    <script>
        /* EDITAR USUARIO */
        const modalEditar = document.getElementById("modalEditar");
        const formEditar = document.getElementById("formEditar");
        const btnCerrarEditar = document.getElementById("btnCerrarEditar");

        tbody.addEventListener("click", e => {
            const btn = e.target.closest(".btn-edit");
            if (!btn) return;

            edit_id.value = btn.dataset.id;
            edit_nombre.value = btn.dataset.nombre;
            edit_correo.value = btn.dataset.correo;
            edit_cargo.value = btn.dataset.cargo;

            modalEditar.style.display = "flex";
        });

        btnCerrarEditar.onclick = () => modalEditar.style.display = "none";

        formEditar.addEventListener("submit", e => {
            e.preventDefault();

            const data = new FormData(formEditar);

            fetch("controllers/act_usuarios.php", {
                    method: "POST",
                    body: data
                })
                .then(res => res.json())
                .then(resp => {
                    if (resp.status === "success") {

                        const btn = document.querySelector(`.btn-edit[data-id="${data.get("id")}"]`);
                        const fila = btn.closest("tr").children;

                        fila[1].innerText = data.get("cargo");
                        fila[2].innerText = data.get("nombre");
                        fila[3].innerText = data.get("correo");

                        btn.dataset.cargo = data.get("cargo");
                        btn.dataset.nombre = data.get("nombre");
                        btn.dataset.correo = data.get("correo");

                        toast("Usuario actualizado", "success");
                        modalEditar.style.display = "none";
                    } else {
                        toast("Error al actualizar", "error");
                    }
                })
                .catch(() => toast("Error de conexión", "error"));
        });
    </script>

</body>

</html>