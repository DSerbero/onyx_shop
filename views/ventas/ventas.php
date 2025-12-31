<?php
include "../../controllers/session.php";
include "../../controllers/ventas.php";

if (!in_array($_SESSION["cargo"], ["gerente", "admin", "code"])) {
    header("Location: cerrar");
    exit;
}


function fechaCortaHora(string $fecha): string
{
    return date("d/m/Y H:i", strtotime($fecha));
}

function getCliente($id_cliente)
{
    $conn = connect();
    $stmt = $conn->prepare("SELECT nombre FROM clientes WHERE id_cliente = ?");
    $stmt->execute([$id_cliente]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function obtenerProductosVenta($productosJson)
{
    if (empty($productosJson)) {
        return [];
    }

    $productos = json_decode($productosJson, true);
    if (!is_array($productos)) {
        return [];
    }

    $conn = connect();

    $resultado = [];

    foreach ($productos as $p) {
        if (!isset($p["codigo"], $p["cantidad"])) continue;

        $stmt = $conn->prepare("SELECT nombre FROM productos WHERE codigo = ?");
        $stmt->execute([$p["codigo"]]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $resultado[] = [
            "nombre" => $row["nombre"] ?? "Producto no encontrado",
            "cantidad" => (int)$p["cantidad"]
        ];
    }

    return $resultado;
}


function obtenerMetodoPago(array $venta): string
{
    $d = $venta["detalles"] ?? [];

    if (!isset($d["tipo"])) {
        return "N/A";
    }

    if ($d["tipo"] === "mixto") {
        return "Mixto";
    }

    return ucfirst($d["tipo"]);
}

function obtenerDetallePago(array $venta, array $abonosVenta = []): string
{
    $d = $venta["detalles"] ?? [];

    if (empty($d) || empty($d["tipo"])) {
        return "—";
    }

    $html = [];

    switch ($d["tipo"]) {

        case "efectivo":
        case "transferencia":
            if (isset($d["total"])) {
                $html[] = "$" . number_format($d["total"], 0, ',', '.');
            }
            break;

        case "mixto":
            if (!empty($d["efectivo"])) {
                $html[] = "Efectivo: $" . number_format($d["efectivo"], 0, ',', '.');
            }
            if (!empty($d["transferencia"])) {
                $html[] = "Transferencia: $" . number_format($d["transferencia"], 0, ',', '.');
            }
            break;

        case "credito":

            if (!empty($d["abono"])) {

                $abonoEfectivo = (int)($d["monto_efectivo"] ?? 0);
                $abonoTransferencia = (int)($d["monto_transferencia"] ?? 0);

                if ($abonoEfectivo > 0 && $abonoTransferencia > 0) {

                    $html[] = "Abono inicial:";
                    $html[] = "&nbsp;&nbsp;• Efectivo: $" . number_format($abonoEfectivo, 0, ',', '.');
                    $html[] = "&nbsp;&nbsp;• Transferencia: $" . number_format($abonoTransferencia, 0, ',', '.');
                } elseif ($abonoEfectivo > 0) {

                    $html[] = "Abono inicial (Efectivo): $" .
                        number_format($abonoEfectivo, 0, ',', '.');
                } elseif ($abonoTransferencia > 0) {

                    $html[] = "Abono inicial (Transferencia): $" .
                        number_format($abonoTransferencia, 0, ',', '.');
                }
            }

            if (!empty($d["saldo"])) {
                $html[] = "Crédito: $" .
                    number_format($d["saldo"], 0, ',', '.');
            }

            $totalAbonos = 0;

            foreach ($abonosVenta as $a) {

                $pago = json_decode($a["tipo_pago"], true);

                if (!isset($pago["detalles"]) || !is_array($pago["detalles"])) {
                    continue;
                }

                foreach ($pago["detalles"] as $monto) {
                    $totalAbonos += (int)$monto;
                }
            }


            $saldoPendiente = max(
                0,
                (int)($d["saldo"] ?? 0) - $totalAbonos
            );

            $html[] = "Pendiente: $" .
                number_format($saldoPendiente, 0, ',', '.');

            break;
    }

    return implode("<br>", $html);
}



function obtenerTotalVenta(array $venta): string
{
    $d = $venta["detalles"] ?? [];

    if (!isset($d["total"])) {
        return "$0";
    }

    return "$" . number_format((int)$d["total"], 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Historial de ventas</title>
    <link rel="stylesheet" href="assets/styles/gen_style.css">
    <link rel="stylesheet" href="assets/styles/header_style.css">
    <link rel="stylesheet" href="assets/styles/ventas.css">
    <link rel="icon" href="assets/img/width_800.ico">
</head>

<body>

    <?php include "../../models/header.php"; ?>

    <div class="titulo">
        <h1>Historial de ventas</h1>
    </div>

    <section>

        <div class="filtros-barra">
            <button id="btnFiltros" class="btn">Seleccionar filtro</button>
            <div id="filtrosActivos"></div>
            <button id="filtar" class="btn">Filtrar</button>
        </div>

        <div class="tabla-wrapper">
            <table class="tabla-ventas" border="1">
                <thead>
                    <tr>
                        <th># - Venta</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Método</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $v):
                        $venta = json_decode($v["tipo_pago"], true) ?? [];
                        $idVenta = $v["id_venta"];
                        $abonosVenta = $abonosPorVenta[$idVenta] ?? [];
                        $productosVenta = obtenerProductosVenta($v["productos"]);
                    ?>
                        <tr>
                            <td><?= $v["id_venta"] ?></td>
                            <td><?= fechaCortaHora($v["fecha_venta"]) ?></td>
                            <td><?= getCliente($v["id_cliente"])["nombre"] ?? "—" ?></td>

                            <td <?php if (obtenerMetodoPago($venta) === "Credito") {
                                    echo 'style="background: #ff000055;"';
                                } ?>><?= obtenerMetodoPago($venta) ?></td>
                            <td><?= obtenerTotalVenta($venta) ?></td>
                            <td><?= ucfirst($v["estado"]) ?></td>
                            <td>
                                <?php
                                $tieneCredito = ($venta["detalles"]["tipo"] ?? "") === "credito";

                                $saldoInicial = (int)($venta["detalles"]["saldo"] ?? 0);

                                $totalAbonos = 0;
                                foreach ($abonosVenta as $a) {
                                    $pago = json_decode($a["tipo_pago"], true);
                                    foreach ($pago["detalles"] ?? [] as $monto) {
                                        $totalAbonos += (int)$monto;
                                    }
                                }

                                $saldoPendiente = max(0, $saldoInicial - $totalAbonos);
                                ?>

                                <?php if ($tieneCredito && $saldoPendiente > 0): ?>
                                    <button
                                        class="btn-abono btn"
                                        data-venta="<?= $idVenta ?>"
                                        data-saldo="<?= $saldoPendiente ?>">
                                        Crear abono
                                    </button>
                                <?php endif; ?>
                                <button
                                    class="btn btn-detalles"
                                    data-id="<?= $v['id_venta'] ?>"
                                    data-fecha="<?= fechaCortaHora($v["fecha_venta"]) ?>"
                                    data-cliente="<?= getCliente($v["id_cliente"])["nombre"] ?? "—" ?>"
                                    data-productos='<?= json_encode($productosVenta) ?>'
                                    data-detalle='<?= htmlspecialchars(obtenerDetallePago($venta, $abonosVenta)) ?>'
                                    data-abonos='<?= json_encode($abonosVenta) ?>'>
                                    Ver detalles
                                </button>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="acciones-tabla">
                <form method="GET"
                    action="views/ventas/exportar_ventas_pdf.php"
                    target="_blank">

                    <input type="hidden" name="cliente" value="<?= $_GET["cliente"] ?? "" ?>">
                    <input type="hidden" name="estado" value="<?= $_GET["estado"] ?? "" ?>">
                    <?php if (!empty($_GET["metodo"]) && is_array($_GET["metodo"])): ?>
                        <?php foreach ($_GET["metodo"] as $m): ?>
                            <input type="hidden" name="metodo[]" value="<?= htmlspecialchars($m) ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <input type="hidden" name="desde" value="<?= $_GET["desde"] ?? "" ?>">
                    <input type="hidden" name="hasta" value="<?= $_GET["hasta"] ?? "" ?>">

                    <button type="submit" class="btn-pdf btn">
                        Exportar a PDF
                    </button>
                </form>
            </div>


        </div>

    </section>
    <div id="modalAbono" class="modal hidden">
        <div class="modal-content">
            <h2>Registrar abono</h2>

            <form id="formAbono">
                <input type="hidden" name="id_venta" id="abono_id_venta">
                <input type="hidden" id="abono_saldo">

                <div class="metodos">
                    <label>
                        <input type="checkbox" id="chkEfectivo" value="efectivo" class="btn_f" data-label="Efectivo">
                    </label>

                    <label>
                        <input type="checkbox" id="chkTransferencia" value="transferencia" class="btn_f" data-label="Transferencia">
                    </label>
                </div>

                <div id="inputsMontos"></div>

                <button type="submit" class="btn" style="margin-top: 10px;">Guardar</button>
            </form>
        </div>
    </div>
    <div id="modalFiltros" class="modal hidden">
        <div class="modal-content">
            <h2>Seleccionar filtros</h2>

            <form id="formFiltros">

                <label>
                    <input type="checkbox" id="filtroFecha" class="btn_f" data-label="fecha">
                </label>

                <label>
                    <input type="checkbox" id="filtroCliente" class="btn_f" data-label="Cliente">
                </label>

                <label>
                    <input type="checkbox" id="filtroEstado" class="btn_f" data-label="Estado">
                </label>

                <label>
                    <input type="checkbox" id="filtroMetodo" class="btn_f" data-label="Metodo de pago">
                </label>

                <button type="submit" class="btn">Aplicar</button>
            </form>
        </div>
    </div>
    <div id="modalFecha" class="modal hidden">
        <div class="modal-content">
            <h2>Filtrar por fecha</h2>
            <div class="presets-fecha">
                <button type="button" data-preset="hoy" class="btn">Hoy</button>
                <button type="button" data-preset="ayer" class="btn">Ayer</button>
                <button type="button" data-preset="semana" class="btn">Esta semana</button>
                <button type="button" data-preset="mes" class="btn">Este mes</button>
                <button type="button" data-preset="7dias" class="btn">Últimos 7 días</button>
                <button type="button" data-preset="30dias" class="btn">Últimos 30 días</button>
            </div>

            <form id="formFecha">
                <label>
                    Desde
                    <input type="datetime-local" name="desde" required>
                </label>

                <label>
                    Hasta
                    <input type="datetime-local" name="hasta" required>
                </label>

                <div style="margin-top:10px">
                    <button type="submit">Aplicar</button>
                    <button type="button" id="cancelarFecha">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    <div id="modalDetalles" class="modal hidden">
        <div class="modal-content" style="max-width:600px">
            <h2>Detalle de la venta</h2>

            <p><b>Venta #:</b> <span id="detalleVentaId"></span></p>

            <h3>Fecha de venta</h3>
            <div id="detalleFecha"></div>

            <h3>Cliente</h3>
            <div id="detalleCliente"></div>

            <h3>Productos</h3>
            <div id="detalleProductos"></div>

            <h3>Detalle de pago</h3>
            <div id="detallePago"></div>

            <div id="bloqueAbonos">
                <h3>Abonos</h3>
                <div id="detalleAbonos"></div>
            </div>


            <div style="margin-top:15px;text-align:right">
                <button id="cerrarDetalles" class="btn">Cerrar</button>
            </div>
        </div>
    </div>
    <div id="modalMetodo" class="modal hidden">
        <div class="modal-content">
            <h2>Método de pago</h2>

            <form id="formMetodo">
                <div class="metodos_sel">
                    <label>
                        <input type="checkbox" name="metodo[]" value="efectivo" data-label="efectivo" class="btn_f">
                    </label>

                    <label>
                        <input type="checkbox" name="metodo[]" value="transferencia" data-label="transferencia" class="btn_f">
                    </label>

                    <label>
                        <input type="checkbox" name="metodo[]" value="credito" data-label="credito" class="btn_f">
                    </label>
                </div>

                <div style="margin-top:10px; display: flex; flex-direction: row; justify-content:space-around;">
                    <button type="submit" class="btn">Aplicar</button>
                    <button type="button" id="cancelarMetodo" class="btn">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalConfirmGuardar" class="modal hidden">
        <div class="modal-content" style="max-width:400px">
            <h3>Confirmar registro</h3>
            <p>¿Desea guardar este abono?</p>

            <div style="margin-top:15px; text-align:right">
                <button id="btnConfirmGuardar" class="btn">Sí, guardar</button>
                <button id="btnCancelGuardar" class="btn">Cancelar</button>
            </div>
        </div>
    </div>


    <div id="toast-container"></div>
    <script src="assets/js/menu.js"></script>
    <script>
        const modal = document.getElementById("modalAbono");
        const modalConfirmGuardar = document.getElementById("modalConfirmGuardar");
        const form = document.getElementById("formAbono");
        const inputsMontos = document.getElementById("inputsMontos");


        const chkEfectivo = document.getElementById("chkEfectivo");
        const chkTransferencia = document.getElementById("chkTransferencia");

        document.querySelectorAll(".btn-abono").forEach(btn => {
            btn.addEventListener("click", () => {
                document.getElementById("abono_id_venta").value = btn.dataset.venta;
                document.getElementById("abono_saldo").value = btn.dataset.saldo;

                chkEfectivo.checked = false;
                chkTransferencia.checked = false;
                inputsMontos.innerHTML = "";

                modal.classList.remove("hidden");
            });
        });

        modal.addEventListener("click", e => {
            if (e.target === modal) modal.classList.add("hidden");
        });


        function renderInputs() {
            inputsMontos.innerHTML = "";

            const saldo = parseInt(document.getElementById("abono_saldo").value);

            if (chkEfectivo.checked && chkTransferencia.checked) {

                inputsMontos.innerHTML = `
            <label>
                Efectivo
                <input type="number" name="monto_efectivo" min="1" max="${saldo}" required>
            </label>

            <label>
                Transferencia
                <input type="number" name="monto_transferencia" min="1" max="${saldo}" required>
            </label>
        `;

            } else if (chkEfectivo.checked) {

                inputsMontos.innerHTML = `
            <label>
                <input type="number" name="monto_efectivo" min="1" max="${saldo}" required>
            </label>
        `;

            } else if (chkTransferencia.checked) {

                inputsMontos.innerHTML = `
            <label>
                <input type="number" name="monto_transferencia" min="1" max="${saldo}" required>
            </label>
        `;
            }
        }

        chkEfectivo.addEventListener("change", renderInputs);
        chkTransferencia.addEventListener("change", renderInputs);
    </script>
    <script>
        let abonoDataTemp = null;

        form.addEventListener("submit", e => {
            e.preventDefault();

            const saldo = parseInt(document.getElementById("abono_saldo").value);
            const data = new FormData(form);

            let total = 0;
            let metodos = [];

            if (chkEfectivo.checked) {
                metodos.push("efectivo");
                total += parseInt(data.get("monto_efectivo") || 0);
            }

            if (chkTransferencia.checked) {
                metodos.push("transferencia");
                total += parseInt(data.get("monto_transferencia") || 0);
            }

            if (metodos.length === 0) {
                toast("Seleccione al menos un método", "error");
                return;
            }

            if (total > saldo) {
                toast("El abono no puede superar el saldo pendiente", "error");
                return;
            }

            const copia = new FormData();
            for (const [k, v] of data.entries()) {
                copia.append(k, v);
            }

            copia.append("metodos", JSON.stringify(metodos));
            abonoDataTemp = copia;

            modalConfirmGuardar.classList.remove("hidden");
        });
    </script>
    <script>
        function toast(mensaje, tipo = "success", tiempo = 3000) {
            const container = document.getElementById("toast-container");

            if (!container) return;

            const toast = document.createElement("div");
            toast.className = `toast ${tipo}`;
            toast.textContent = mensaje;

            container.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, tiempo + 400);
        }
    </script>
    <script>
        function aplicarFiltros() {
            const params = new URLSearchParams();

            document.querySelectorAll(".filtro-activo").forEach(f => {
                const tipo = f.dataset.filtro;

                if (tipo === "cliente") {
                    const val = f.querySelector("input")?.value.trim();
                    if (val) params.set("cliente", val);
                }

                if (tipo === "estado") {
                    const val = f.querySelector("select")?.value;
                    if (val) params.set("estado", val);
                }

                if (tipo === "metodo") {
                    f.querySelectorAll("input[name='metodo[]']").forEach(chk => {
                        params.append("metodo[]", chk.value);
                    });
                }


                if (tipo === "fecha") {
                    const desde = f.querySelector("[name='desde']")?.value;
                    const hasta = f.querySelector("[name='hasta']")?.value;

                    if (desde && hasta) {
                        params.set("desde", desde);
                        params.set("hasta", hasta);
                    }
                }
            });

            window.location.search = params.toString();

        }
    </script>

    <script>
        const modalFiltros = document.getElementById("modalFiltros");
        const btnFiltros = document.getElementById("btnFiltros");

        const chkFecha = document.getElementById("filtroFecha");
        const chkCliente = document.getElementById("filtroCliente");
        const chkEstado = document.getElementById("filtroEstado");
        const chkMetodo = document.getElementById("filtroMetodo");

        btnFiltros.addEventListener("click", () => {
            modalFiltros.classList.remove("hidden");
        });

        modalFiltros.addEventListener("click", e => {
            if (e.target === modalFiltros) modalFiltros.classList.add("hidden");
        });
    </script>
    <script>
        document.getElementById("formFiltros").addEventListener("submit", e => {
            e.preventDefault();

            filtrosActivos.innerHTML = "";

            if (chkFecha.checked) {
                filtrosActivos.innerHTML += `
        <div class="filtro-activo" data-filtro="fecha">
            <button type="button" id="select-fecha" class="btn">Seleccionar fecha</button>
            <button type="button" class="btn-remove">✕</button>
        </div>
    `;
            }

            if (chkCliente.checked) {
                filtrosActivos.innerHTML += `
        <div class="filtro-activo" data-filtro="cliente">
            <input type="text" name="cliente" placeholder="Cliente">
            <button type="button" class="btn-remove">✕</button>
        </div>
    `;
            }

            if (chkEstado.checked) {
                filtrosActivos.innerHTML += `
        <div class="filtro-activo" data-filtro="estado">
            <select name="estado">
                <option value="">Todos</option>
                <option value="pago">Pago</option>
                <option value="pendiente">Pendiente</option>
            </select>
            <button type="button" class="btn-remove">✕</button>
        </div>
    `;
            }

            if (chkMetodo.checked) {
                filtrosActivos.innerHTML += `
        <div class="filtro-activo" data-filtro="metodo">
            <button type="button" id="select-metodo" class="btn">
                Seleccionar método
            </button>
            <button type="button" class="btn-remove">✕</button>
        </div>
    `;
            }



            modalFiltros.classList.add("hidden");
        });
    </script>
    <script>
        window.addEventListener("DOMContentLoaded", () => {

            const contenedor = document.getElementById("filtrosActivos");

            if (filtrosPersistidos.cliente) {
                contenedor.innerHTML += `
            <div class="filtro-activo" data-filtro="cliente">
                <input type="text" value="${filtrosPersistidos.cliente}">
                <button type="button" class="btn-remove">✕</button>
            </div>
        `;
                chkCliente.checked = true;
            }

            if (filtrosPersistidos.estado) {
                contenedor.innerHTML += `
            <div class="filtro-activo" data-filtro="estado">
                <select>
                    <option value="pago" ${filtrosPersistidos.estado === "pago" ? "selected" : ""}>Pago</option>
                    <option value="pendiente" ${filtrosPersistidos.estado === "pendiente" ? "selected" : ""}>Pendiente</option>
                </select>
                <button type="button" class="btn-remove">✕</button>
            </div>
        `;
                chkEstado.checked = true;
            }
            if (filtrosPersistidos.desde && filtrosPersistidos.hasta) {
                filtrosActivos.innerHTML += `
        <div class="filtro-activo" data-filtro="fecha">
            <input type="hidden" name="desde" value="${filtrosPersistidos.desde}">
            <input type="hidden" name="hasta" value="${filtrosPersistidos.hasta}">

            <button type="button" id="select-fecha" class="btn">Cambiar</button>
            <button type="button" class="btn-remove">✕</button>
        </div>
    `;
                chkFecha.checked = true;
            }

            if (filtrosPersistidos.metodo) {

                const metodos = Array.isArray(filtrosPersistidos.metodo) ?
                    filtrosPersistidos.metodo : [filtrosPersistidos.metodo];

                filtrosActivos.innerHTML += `
        <div class="filtro-activo" data-filtro="metodo">

            ${metodos.map(m =>
                `<input type="hidden" name="metodo[]" value="${m}">`
            ).join("")}

            <button type="button" id="select-metodo" class="btn">
                ${metodos.join(", ")}
            </button>

            <button type="button" class="btn-remove">✕</button>
        </div>
    `;

                chkMetodo.checked = true;
            }


        });
    </script>

    <script>
        document.getElementById("filtrosActivos").addEventListener("click", e => {
            if (!e.target.classList.contains("btn-remove")) return;

            const filtro = e.target.closest(".filtro-activo");
            const tipo = filtro.dataset.filtro;

            filtro.remove();

            if (tipo === "fecha") chkFecha.checked = false;
            if (tipo === "cliente") chkCliente.checked = false;
            if (tipo === "estado") chkEstado.checked = false;
            if (tipo === "metodo") chkMetodo.checked = false;

            aplicarFiltros();
        });
    </script>
    <script>
        const envio_btn = document.getElementById("filtar");

        envio_btn.addEventListener("click", () => {
            aplicarFiltros();
        });
    </script>

    <script>
        const filtrosPersistidos = <?= json_encode($_GET) ?>;
    </script>
    <script>
        document.addEventListener("click", e => {
            if (e.target.id === "select-fecha") {
                modalFecha.classList.remove("hidden");
            }
        });
    </script>
    <script>
        document.getElementById("cancelarFecha").addEventListener("click", () => {
            modalFecha.classList.add("hidden");
        });

        modalFecha.addEventListener("click", e => {
            if (e.target === modalFecha) {
                modalFecha.classList.add("hidden");
            }
        });
    </script>
    <script>
        document.getElementById("formFecha").addEventListener("submit", e => {
            e.preventDefault();

            const desde = e.target.desde.value;
            const hasta = e.target.hasta.value;

            if (!desde || !hasta) return;

            const filtroFecha = document.querySelector(".filtro-activo[data-filtro='fecha']");

            filtroFecha.innerHTML = `

        <input type="hidden" name="desde" value="${desde}">
        <input type="hidden" name="hasta" value="${hasta}">


        <button type="button" id="select-fecha" class="btn">Cambiar</button>
        <button type="button" class="btn-remove">✕</button>
    `;

            modalFecha.classList.add("hidden");
        });
    </script>
    <script>
        function formatoDatetimeLocal(date) {
            const pad = n => n.toString().padStart(2, "0");
            return `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        }

        document.querySelectorAll("[data-preset]").forEach(btn => {
            btn.addEventListener("click", () => {

                const preset = btn.dataset.preset;
                const ahora = new Date();

                let desde, hasta;

                switch (preset) {

                    case "hoy":
                        desde = new Date();
                        desde.setHours(0, 0, 0, 0);
                        hasta = new Date();
                        hasta.setHours(23, 59, 59, 999);
                        break;

                    case "ayer":
                        desde = new Date();
                        desde.setDate(desde.getDate() - 1);
                        desde.setHours(0, 0, 0, 0);

                        hasta = new Date(desde);
                        hasta.setHours(23, 59, 59, 999);
                        break;

                    case "semana":
                        desde = new Date();
                        desde.setDate(desde.getDate() - desde.getDay());
                        desde.setHours(0, 0, 0, 0);

                        hasta = new Date();
                        hasta.setHours(23, 59, 59, 999);
                        break;

                    case "mes":
                        desde = new Date(ahora.getFullYear(), ahora.getMonth(), 1);
                        desde.setHours(0, 0, 0, 0);

                        hasta = new Date();
                        hasta.setHours(23, 59, 59, 999);
                        break;

                    case "7dias":
                        desde = new Date();
                        desde.setDate(desde.getDate() - 7);
                        desde.setHours(0, 0, 0, 0);

                        hasta = new Date();
                        break;

                    case "30dias":
                        desde = new Date();
                        desde.setDate(desde.getDate() - 30);
                        desde.setHours(0, 0, 0, 0);

                        hasta = new Date();
                        break;
                }

                document.querySelector("#formFecha [name='desde']").value = formatoDatetimeLocal(desde);
                document.querySelector("#formFecha [name='hasta']").value = formatoDatetimeLocal(hasta);
            });
        });
    </script>
    <script>
        const modalDetalles = document.getElementById("modalDetalles");
        const detalleProductos = document.getElementById("detalleProductos");
        const detallePago = document.getElementById("detallePago");
        const detalleAbonos = document.getElementById("detalleAbonos");


        document.querySelectorAll(".btn-detalles").forEach(btn => {
            btn.addEventListener("click", () => {
                const ventaId = btn.dataset.id;
                document.getElementById("detalleVentaId").textContent = ventaId;

                const ventaDate = btn.dataset.fecha;
                document.getElementById("detalleFecha").textContent = ventaDate;

                const ventaCliente = btn.dataset.cliente;
                document.getElementById("detalleCliente").textContent = ventaCliente;

                const productos = JSON.parse(btn.dataset.productos || "[]");
                detalleProductos.innerHTML = productos.length ?
                    productos.map(p => `• ${p.nombre} x${p.cantidad}`).join("<br>") :
                    "—";

                detallePago.innerHTML = btn.dataset.detalle || "—";

                const bloqueAbonos = document.getElementById("bloqueAbonos");

                const abonos = JSON.parse(btn.dataset.abonos || "[]");

                if (!abonos.length) {
                    bloqueAbonos.style.display = "none";
                } else {
                    bloqueAbonos.style.display = "block";

                    detalleAbonos.innerHTML = abonos.map(a => {
                        const pagos = JSON.parse(a.tipo_pago).detalles || {};
                        let html = `<b>${a.fecha_abono}</b><ul>`;

                        for (const metodo in pagos) {
                            html += `<li>${metodo}: $${Number(pagos[metodo]).toLocaleString()}</li>`;
                        }

                        html += "</ul>";
                        return html;
                    }).join("");
                }


                modalDetalles.classList.remove("hidden");
            });
        });

        document.getElementById("cerrarDetalles").addEventListener("click", () => {
            modalDetalles.classList.add("hidden");
        });

        modalDetalles.addEventListener("click", e => {
            if (e.target === modalDetalles) {
                modalDetalles.classList.add("hidden");
            }
        });
    </script>
    <script>
        const modalMetodo = document.getElementById("modalMetodo");

        document.addEventListener("click", e => {
            if (e.target.id === "select-metodo") {
                modalMetodo.classList.remove("hidden");
            }
        });

        document.getElementById("cancelarMetodo").addEventListener("click", () => {
            modalMetodo.classList.add("hidden");
        });

        modalMetodo.addEventListener("click", e => {
            if (e.target === modalMetodo) {
                modalMetodo.classList.add("hidden");
            }
        });
    </script>
    <script>
        document.getElementById("formMetodo").addEventListener("submit", e => {
            e.preventDefault();

            const checks = e.target.querySelectorAll(
                "input[name='metodo[]']:checked"
            );

            if (!checks.length) return;

            const valores = [...checks].map(c => c.value);

            const filtroMetodo = document.querySelector(
                ".filtro-activo[data-filtro='metodo']"
            );

            filtroMetodo.innerHTML = `

            ${valores.map(v =>
                `<input type="hidden" name="metodo[]" value="${v}">`
            ).join("")}

            <button type="button" id="select-metodo" class="btn">
                ${valores.join(", ")}
            </button>

            <button type="button" class="btn-remove">✕</button>
        `;

            modalMetodo.classList.add("hidden");
        });
    </script>
    <script>
        document.getElementById("btnCancelGuardar").addEventListener("click", () => {
            modalConfirmGuardar.classList.add("hidden");
        });

        modalConfirmGuardar.addEventListener("click", e => {
            if (e.target === modalConfirmGuardar) {
                modalConfirmGuardar.classList.add("hidden");
            }
        });

        document.getElementById("btnConfirmGuardar").addEventListener("click", async () => {
            if (!abonoDataTemp) return;

            const res = await fetch("controllers/guardar_abono.php", {
                method: "POST",
                body: abonoDataTemp
            });

            const json = await res.json();

            modalConfirmGuardar.classList.add("hidden");
            modal.classList.add("hidden");

            if (json.ok) {
                toast("Abono registrado correctamente");
                setTimeout(() => location.reload(), 1200);
            } else {
                toast(json.error || "Error", "error");
            }

            abonoDataTemp = null;
        });
    </script>

</body>

</html>