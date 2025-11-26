// cargar_productos.js - actualizado para cliente_info y envío correcto

let clienteSeleccionado = null;

const formatoMoneda = new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0
});

const inputBuscar = document.getElementById("producto");
const listaBusqueda = document.getElementById("lista_busqueda");
const tabla = document.getElementById("tablaProductos");
const totalCompra = document.getElementById("total_compra");
const inputCliente = document.getElementById("buscar_cliente");
const listaClientes = document.getElementById("lista_clientes");
const form = document.querySelector(".form_compra");
const inputProductosEnviados = document.getElementById("productos_enviados");

function actualizarTotal() {
    const filas = tabla.querySelectorAll("tr");
    let total = 0;

    for (let i = 1; i < filas.length; i++) {
        const totalFila = filas[i].querySelector(".total").value.replace(/\D/g, "") || 0;
        total += parseFloat(totalFila);
    }

    totalCompra.textContent = formatoMoneda.format(total);

    // Si existe saldo_credito recalcularlo (para mantener actualizado cuando cambia total)
    const inputSaldo = document.getElementById("saldo_credito");
    const inputAbono = document.getElementById("abono_credito");
    if (inputSaldo && inputAbono) {
        const totalNum = parseFloat(totalCompra.textContent.replace(/\D/g, "")) || 0;
        const abonoNum = parseFloat(inputAbono.value) || 0;
        const saldo = Math.max(0, totalNum - abonoNum);
        inputSaldo.value = formatoMoneda.format(saldo);
    }
}

function agregarProducto(prod) {
    const filas = tabla.querySelectorAll("tr");

    for (let i = 1; i < filas.length; i++) {
        const codigoCelda = filas[i].querySelector("td:nth-child(1) input");
        if (codigoCelda.value === prod.codigo) {
            const inputCantidad = filas[i].querySelector(".cantidad");
            const inputTotal = filas[i].querySelector(".total");
            const nuevaCantidad = parseFloat(inputCantidad.value) + 1;
            inputCantidad.value = nuevaCantidad;
            inputTotal.value = formatoMoneda.format((nuevaCantidad * prod.venta));
            actualizarTotal();
            return;
        }
    }

    const fila = document.createElement("tr");
    fila.innerHTML = `
        <td class="code"><input type="text" value="${prod.codigo}" readonly></td>
        <td><input type="text" value="${prod.nombre}" readonly></td>
        <td><input type="number" min="1" value="1" class="cantidad"></td>
        <td><input type="text" value="${formatoMoneda.format(prod.venta)}" class="total" readonly></td>
        <td><a href="#" class="eliminar"><img src="assets/img/delete.png"></a></td>
    `;
    tabla.appendChild(fila);

    const inputCantidad = fila.querySelector(".cantidad");
    const inputTotal = fila.querySelector(".total");

    inputCantidad.addEventListener("input", () => {
        const cantidad = parseFloat(inputCantidad.value) || 0;
        inputTotal.value = formatoMoneda.format((cantidad * prod.venta));
        actualizarTotal();
    });

    fila.querySelector(".eliminar").addEventListener("click", e => {
        e.preventDefault();
        fila.remove();
        actualizarTotal();
    });

    actualizarTotal();
}

inputBuscar.addEventListener("keyup", () => {
    const texto = inputBuscar.value.toLowerCase().trim();
    listaBusqueda.innerHTML = "";

    if (!texto) return;

    const filtrados = productos.filter(p =>
        p.nombre.toLowerCase().includes(texto)
    );

    filtrados.forEach(prod => {
        const item = document.createElement("div");
        item.textContent = prod.nombre;
        item.classList.add("item_busqueda");

        item.addEventListener("click", () => {
            agregarProducto(prod);
            listaBusqueda.innerHTML = "";
            inputBuscar.value = "";
        });

        listaBusqueda.appendChild(item);
    });
});

/* ---------------- CLIENTES ---------------- */
inputCliente.addEventListener("keyup", () => {
    const texto = inputCliente.value.toLowerCase().trim();
    listaClientes.innerHTML = "";

    if (!texto) return;

    const filtrados = clientes.filter(c =>
        (c.nombre && c.nombre.toLowerCase().includes(texto)) ||
        (c.documento && String(c.documento).toLowerCase().includes(texto))
    );

    filtrados.forEach(cli => {
        const item = document.createElement("div");
        item.classList.add("item_busqueda");
        item.textContent = `${cli.nombre} - ${cli.documento}`;

        item.addEventListener("click", () => {
            clienteSeleccionado = { ...cli }; // guardamos copia editable
            window.clienteSeleccionado = clienteSeleccionado;

            inputCliente.value = clienteSeleccionado.nombre;
            listaClientes.innerHTML = "";

            // Guardar en hidden cliente_info (JSON)
            document.getElementById("cliente_info").value = JSON.stringify(clienteSeleccionado);

            // Mantener id_cliente por compatibilidad (opcional)
            document.getElementById("id_cliente").value = clienteSeleccionado.id_cliente;

            // llenar modal con datos
            document.getElementById("modal_nombre").value = clienteSeleccionado.nombre || "";
            document.getElementById("modal_documento").value = clienteSeleccionado.documento || "";
            document.getElementById("modal_direccion").value = clienteSeleccionado.direccion || "";
            document.getElementById("modal_telefono").value = clienteSeleccionado.telefono || "";
            document.getElementById("modal_correo").value = clienteSeleccionado.correo || "";

            // mostrar modal
            document.getElementById("modal_cliente").style.display = "flex";
        });

        listaClientes.appendChild(item);
    });
});

/* ------------- SUBMIT FORM ------------- */
form.addEventListener("submit", e => {

    // VALIDAR CLIENTE
    const infoCliente = document.getElementById("cliente_info").value;
    if (!infoCliente || infoCliente.trim() === "") {
        e.preventDefault();
        alert("Debe seleccionar un cliente o agregar uno nuevo.");
        return;
    }
    let datosCliente = {};
    try {
        datosCliente = JSON.parse(infoCliente);
    } catch (err) {
        e.preventDefault();
        alert("Información de cliente inválida.");
        return;
    }

    // VALIDAR MÉTODOS DE PAGO
    if (!efectivo.checked && !transferencia.checked && !credito.checked) {
        e.preventDefault();
        alert("Debe seleccionar al menos un método de pago.");
        return;
    }

    // if (credito.checked && !efectivo.checked && !transferencia.checked) {
    //     e.preventDefault();
    //     alert("El crédito debe ir acompañado de efectivo, transferencia o ambos.");
    //     return;
    // }

    // Productos
    const filas = tabla.querySelectorAll("tr");
    const productosArray = [];

    for (let i = 1; i < filas.length; i++) {
        const fila = filas[i];
        const codigo = fila.querySelector(".code input").value;
        const nombre = fila.querySelector("td:nth-child(2) input").value;
        const cantidad = parseFloat(fila.querySelector(".cantidad").value) || 0;
        const totalText = fila.querySelector(".total").value;
        const total = parseFloat(totalText.replace(/\D/g, "")) || 0;

        productosArray.push({ codigo, nombre, cantidad, total });
    }

    inputProductosEnviados.value = JSON.stringify(productosArray);

    const total = parseFloat(totalCompra.textContent.replace(/\D/g, "")) || 0;
    let pago = {};

    // Si crédito (con posibilidad de mixto)
    if (credito.checked) {
        const abono = parseFloat(document.getElementById("abono_credito")?.value || 0);
        const saldo = Math.max(0, total - abono);
        const cuotas = parseInt(document.getElementById("num_cuotas")?.value || 1);

        pago = {
            tipo: "credito",
            total,
            abono,
            saldo,
            cuotas,
            valor_cuota: Math.ceil(saldo / cuotas),
            monto_efectivo: 0,
            monto_transferencia: 0
        };

        if (efectivo.checked && transferencia.checked) {
            pago.monto_efectivo = parseFloat(document.getElementById("pago_efectivo")?.value || 0);
            pago.monto_transferencia = parseFloat(document.getElementById("pago_transferencia")?.value || 0);
        } else if (efectivo.checked) {
            pago.monto_efectivo = abono;
        } else if (transferencia.checked) {
            pago.monto_transferencia = abono;
        }
    }
    // Mixto sin crédito
    else if (efectivo.checked && transferencia.checked) {
        const mixtoBox = document.getElementById("mixto_box");
        pago = {
            tipo: "mixto",
            efectivo: parseFloat(mixtoBox?.querySelector("input[placeholder='Efectivo']")?.value || 0),
            transferencia: parseFloat(mixtoBox?.querySelector("input[placeholder='Transferencia']")?.value || 0),
            total
        };
    }
    // Efectivo solo
    else if (efectivo.checked) {
        pago = {
            tipo: "efectivo",
            total
        };
    }
    // Transferencia solo
    else if (transferencia.checked) {
        pago = {
            tipo: "transferencia",
            total
        };
    }

    document.getElementById("pago_info").value = JSON.stringify(pago);
});

/* ------------- MODAL EDICIÓN CLIENTE EXISTENTE ------------- */
document.getElementById("modal_nombre").addEventListener("input", e => {
    if (window.clienteSeleccionado) {
        window.clienteSeleccionado.nombre = e.target.value;
        inputCliente.value = window.clienteSeleccionado.nombre;
        document.getElementById("cliente_info").value = JSON.stringify(window.clienteSeleccionado);
    }
});
document.getElementById("modal_documento").addEventListener("input", e => {
    if (window.clienteSeleccionado) {
        window.clienteSeleccionado.documento = e.target.value;
        document.getElementById("cliente_info").value = JSON.stringify(window.clienteSeleccionado);
    }
});
document.getElementById("modal_direccion").addEventListener("input", e => {
    if (window.clienteSeleccionado) {
        window.clienteSeleccionado.direccion = e.target.value;
        document.getElementById("cliente_info").value = JSON.stringify(window.clienteSeleccionado);
    }
});
document.getElementById("modal_telefono").addEventListener("input", e => {
    if (window.clienteSeleccionado) {
        window.clienteSeleccionado.telefono = e.target.value;
        document.getElementById("cliente_info").value = JSON.stringify(window.clienteSeleccionado);
    }
});
document.getElementById("modal_correo").addEventListener("input", e => {
    if (window.clienteSeleccionado) {
        window.clienteSeleccionado.correo = e.target.value;
        document.getElementById("cliente_info").value = JSON.stringify(window.clienteSeleccionado);
    }
});
