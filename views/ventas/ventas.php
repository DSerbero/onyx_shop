<?php
include "../../controllers/session.php";
include "../../controllers/ventas.php";

if (!in_array($_SESSION["cargo"], ["gerente", "admin", "code"])) {
    header("Location: login");
    exit;
}

/* =======================
   FUNCIONES AUXILIARES
======================= */

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

/* MÉTODO DE PAGO (fila 1) */
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

/* DETALLE (fila 2) */
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

            /* ===== ABONO INICIAL ===== */
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

            /* ===== SALDO A CRÉDITO ===== */
            if (!empty($d["saldo"])) {
                $html[] = "Crédito: $" .
                    number_format($d["saldo"], 0, ',', '.');
            }

            /* ===== SALDO PENDIENTE ===== */
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



/* TOTAL (fila 3) */
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
</head>

<body>

    <?php include "../../models/header.php"; ?>

    <div class="titulo">
        <h1>Historial de ventas</h1>
    </div>

    <section>

        <div class="filtros-barra">
            <button id="btnFiltros">Seleccionar filtro</button>
            <div id="filtrosActivos"></div>
            <button id="filtar">Filtrar</button>
        </div>

        <div class="tabla-wrapper">
            <table class="tabla-ventas" border="1">
                <thead>
                    <tr>
                        <th># - Venta</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Método</th>
                        <th>Detalle</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Abonos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $v):
                        $venta = json_decode($v["tipo_pago"], true) ?? [];
                        $idVenta = $v["id_venta"];
                        $abonosVenta = $abonosPorVenta[$idVenta] ?? [];
                    ?>
                        <tr>
                            <td><?= $v["id_venta"] ?></td>
                            <td><?= fechaCortaHora($v["fecha_venta"]) ?></td>

                            <td><?= getCliente($v["id_cliente"])["nombre"] ?? "—" ?></td>

                            <td><?= obtenerMetodoPago($venta) ?></td>

                            <td><?= obtenerDetallePago($venta, $abonosVenta) ?></td>


                            <td><?= obtenerTotalVenta($venta) ?></td>

                            <td><?= ucfirst($v["estado"]) ?></td>

                            <td>
                                <?php if (!empty($abonosVenta)): ?>
                                    <details>
                                        <summary>Ver</summary>
                                        <ul>
                                            <?php foreach ($abonosVenta as $a):
                                                $pagoAbono = json_decode($a["tipo_pago"], true)["detalles"] ?? [];
                                            ?>
                                                <li>
                                                    <?= date("d/m/Y", strtotime($a["fecha_abono"])) ?>
                                                    <ul>
                                                        <?php foreach ($pagoAbono as $metodo => $monto): ?>
                                                            <li>
                                                                <?= ucfirst($metodo) ?>:
                                                                <b>$<?= number_format($monto, 0, ',', '.') ?></b>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </details>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $tieneCredito = ($venta["detalles"]["tipo"] ?? "") === "credito";

                                // calcular saldo pendiente
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
                                        class="btn-abono"
                                        data-venta="<?= $idVenta ?>"
                                        data-saldo="<?= $saldoPendiente ?>">
                                        Crear abono
                                    </button>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="acciones-tabla">
                <form method="POST"
                    action="exportar_ventas_pdf.php"
                    target="_blank">

                    <input type="hidden" name="filtros" id="filtrosPDF">

                    <button type="submit" class="btn-pdf">
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

                <label>
                    <input type="checkbox" id="chkEfectivo" value="efectivo">
                    Efectivo
                </label>

                <label>
                    <input type="checkbox" id="chkTransferencia" value="transferencia">
                    Transferencia
                </label>

                <div id="inputsMontos"></div>

                <button type="submit">Guardar</button>
            </form>
        </div>
    </div>
    <div id="modalFiltros" class="modal hidden">
        <div class="modal-content">
            <h2>Seleccionar filtros</h2>

            <form id="formFiltros">

                <label>
                    <input type="checkbox" id="filtroFecha">
                    Fecha
                </label>

                <label>
                    <input type="checkbox" id="filtroCliente">
                    Cliente
                </label>

                <label>
                    <input type="checkbox" id="filtroEstado">
                    Estado
                </label>

                <label>
                    <input type="checkbox" id="filtroMetodo">
                    Método de pago
                </label>

                <button type="submit">Aplicar</button>
            </form>
        </div>
    </div>
    <div id="modalFecha" class="modal hidden">
        <div class="modal-content">
            <h2>Seleccionar filtros</h2>

            <form id="formFiltros">

                <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Architecto cupiditate explicabo ullam voluptatibus. Numquam autem sed perferendis incidunt quod necessitatibus quam et vel eveniet accusantium harum officiis beatae, veritatis unde.</p>
            </form>
        </div>
    </div>
    <div id="toast-container"></div>
    <script src="assets/js/menu.js"></script>
    <script>
        const modal = document.getElementById("modalAbono");
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
        form.addEventListener("submit", async e => {
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

            data.append("metodos", JSON.stringify(metodos));

            const res = await fetch("controllers/guardar_abono.php", {
                method: "POST",
                body: data
            });

            const json = await res.json();

            if (json.ok) {
                toast("Abono registrado correctamente");
                setTimeout(() => location.reload(), 1200);
            } else {
                toast(json.error || "Error", "error");
            }

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
                    const val = f.querySelector("select")?.value;
                    if (val) params.set("metodo", val);
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
            document.getElementById("filtrosPDF").value =
                JSON.stringify(Object.fromEntries(params));

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
            <span>Fecha</span>
            <button type="button" id="select-fecha">Seleccionar rango</button>
            <button type="button" class="btn-remove">✕</button>
        </div>
    `;
            }

            if (chkCliente.checked) {
                filtrosActivos.innerHTML += `
        <div class="filtro-activo" data-filtro="cliente">
            <span>Cliente</span>
            <input type="text" name="cliente">
            <button type="button" class="btn-remove">✕</button>
        </div>
    `;
            }

            if (chkEstado.checked) {
                filtrosActivos.innerHTML += `
        <div class="filtro-activo" data-filtro="estado">
            <span>Estado</span>
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
            <span>Método</span>
            <button type="button">Seleccionar método</button>
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

            /* CLIENTE */
            if (filtrosPersistidos.cliente) {
                contenedor.innerHTML += `
            <div class="filtro-activo" data-filtro="cliente">
                <span>Cliente</span>
                <input type="text" value="${filtrosPersistidos.cliente}">
                <button type="button" class="btn-remove">✕</button>
            </div>
        `;
                chkCliente.checked = true;
            }

            /* ESTADO */
            if (filtrosPersistidos.estado) {
                contenedor.innerHTML += `
            <div class="filtro-activo" data-filtro="estado">
                <span>Estado</span>
                <select>
                    <option value="pago" ${filtrosPersistidos.estado === "pago" ? "selected" : ""}>Pago</option>
                    <option value="pendiente" ${filtrosPersistidos.estado === "pendiente" ? "selected" : ""}>Pendiente</option>
                </select>
                <button type="button" class="btn-remove">✕</button>
            </div>
        `;
                chkEstado.checked = true;
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
        const modalFecha = document.getElementById("modalFecha");
        let btnSelFe = document.getElementById("select-fecha");
        btnSelFe.addEventListener('click', () => {
            modalFecha.classList.remove("hidden");

        });
    </script>
</body>

</html>