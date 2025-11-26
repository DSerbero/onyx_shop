// tipo_venta.js - manejo de métodos de pago y crédito
const efectivo = document.getElementById("efectivo");
const transferencia = document.getElementById("transferencia");
const credito = document.getElementById("credito");
const cont = document.getElementById("cont_cli");

function remove(id) {
    const e = document.getElementById(id);
    if (e) e.remove();
}

/* ============================================================
   1) PAGO MIXTO NORMAL (EFECTIVO + TRANSFERENCIA sin crédito)
   ============================================================ */
function renderPagoMixtoNormal() {

    // Se muestra SOLO si NO hay crédito
    if (credito.checked) {
        remove("mixto_box");
        return;
    }

    // Se crea SOLO si ambos están marcados
    if (efectivo.checked && transferencia.checked) {

        remove("mixto_box");

        const base = parseFloat(totalCompra.textContent.replace(/\D/g, "")) || 0;

        const box = document.createElement("div");
        box.id = "mixto_box";

        const inpEf = document.createElement("input");
        inpEf.type = "number";
        inpEf.placeholder = "Efectivo";
        inpEf.min = 0;
        inpEf.id = "mixto_input_ef";

        const inpTr = document.createElement("input");
        inpTr.type = "number";
        inpTr.placeholder = "Transferencia";
        inpTr.min = 0;
        inpTr.id = "mixto_input_tr";

        box.appendChild(inpEf);
        box.appendChild(inpTr);

        cont.appendChild(box);

        function update() {
            const ef = parseFloat(inpEf.value) || 0;
            const tr = parseFloat(inpTr.value) || 0;

            if (document.activeElement === inpEf) {
                inpTr.value = Math.max(0, base - ef);
            }
            if (document.activeElement === inpTr) {
                inpEf.value = Math.max(0, base - tr);
            }
        }

        inpEf.addEventListener("input", update);
        inpTr.addEventListener("input", update);

    } else {
        remove("mixto_box");
    }
}

/* ============================================================
   2) PAGO CON CRÉDITO (ORDEN ESPECÍFICO)
   ============================================================ */
function renderCredito() {

    remove("credito_box");

    if (!credito.checked) return;

    // // Debe tener al menos un método adicional
    // if (!efectivo.checked && !transferencia.checked) {
    //     alert("El crédito debe ir acompañado de efectivo, transferencia o ambos.");
    //     credito.checked = false;
    //     return;
    // }

    const total = parseFloat(totalCompra.textContent.replace(/\D/g, "")) || 0;

    const box = document.createElement("div");
    box.id = "credito_box";

    // === ABONO ===
    const lblAb = document.createElement("p");
    lblAb.textContent = "Abono inicial:";
    const inpAbono = document.createElement("input");
    inpAbono.type = "number";
    inpAbono.min = 0;
    inpAbono.id = "abono_credito";

    box.appendChild(lblAb);
    box.appendChild(inpAbono);

    // Saber cuántos métodos acompañan el crédito
    const metodos = (efectivo.checked ? 1 : 0) + (transferencia.checked ? 1 : 0);

    // === EFECTIVO (solo si hay DOS métodos) ===
    let inpEf = null;
    if (efectivo.checked && metodos === 2) {
        const lblEf = document.createElement("p");
        lblEf.textContent = "Efectivo:";
        inpEf = document.createElement("input");
        inpEf.type = "number";
        inpEf.min = 0;
        inpEf.id = "pago_efectivo";

        box.appendChild(lblEf);
        box.appendChild(inpEf);
    }

    // === TRANSFERENCIA (solo si hay DOS métodos) ===
    let inpTr = null;
    if (transferencia.checked && metodos === 2) {
        const lblTr = document.createElement("p");
        lblTr.textContent = "Transferencia:";
        inpTr = document.createElement("input");
        inpTr.type = "number";
        inpTr.min = 0;
        inpTr.id = "pago_transferencia";

        box.appendChild(lblTr);
        box.appendChild(inpTr);
    }

    // === SALDO ===
    const lblSd = document.createElement("p");
    lblSd.textContent = "Saldo pendiente:";
    const inpSaldo = document.createElement("input");
    inpSaldo.type = "text";
    inpSaldo.readOnly = true;
    inpSaldo.id = "saldo_credito";

    box.appendChild(lblSd);
    box.appendChild(inpSaldo);

    // === CUOTAS ===
    const lblCt = document.createElement("p");
    lblCt.textContent = "Número de cuotas:";
    const selCt = document.createElement("select");
    selCt.id = "num_cuotas";

    for (let i = 2; i <= 12; i++) {
        const o = document.createElement("option");
        o.value = i;
        o.textContent = `${i} cuotas`;
        selCt.appendChild(o);
    }

    box.appendChild(lblCt);
    box.appendChild(selCt);

    cont.appendChild(box);

    // === LÓGICA ABONO ===
    function actualizarSaldo() {
        const ab = parseFloat(inpAbono.value) || 0;
        const saldo = Math.max(0, total - ab);
        inpSaldo.value = formatoMoneda.format(saldo);
    }

    inpAbono.addEventListener("input", actualizarSaldo);

    // === LÓGICA DE DIVISIÓN ENTRE EFECTIVO Y TRANSFERENCIA ===
    function actualizarMixto() {
        const ab = parseFloat(inpAbono.value) || 0;

        if (inpEf && document.activeElement === inpEf) {
            if (inpTr) inpTr.value = Math.max(0, ab - (parseFloat(inpEf.value) || 0));
        }

        if (inpTr && document.activeElement === inpTr) {
            if (inpEf) inpEf.value = Math.max(0, ab - (parseFloat(inpTr.value) || 0));
        }
    }

    if (inpEf) inpEf.addEventListener("input", actualizarMixto);
    if (inpTr) inpTr.addEventListener("input", actualizarMixto);
}

/* ============================================================
   EVENTOS
   ============================================================ */
efectivo.addEventListener("change", () => {
    renderPagoMixtoNormal();
    renderCredito();
});

transferencia.addEventListener("change", () => {
    renderPagoMixtoNormal();
    renderCredito();
});

credito.addEventListener("change", () => {
    renderPagoMixtoNormal();
    renderCredito();
});
