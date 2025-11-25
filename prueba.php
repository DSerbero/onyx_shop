<?php


if ($_POST["crear_venta"]) {
    if (empty(json_decode($_POST['productos_enviados'], true))) {
        header("Location: venta?e=vacio");
    } else {
        $productos_recibidos = json_decode($_POST['productos_enviados'], true);
        echo "<pre>";
        print_r($productos_recibidos);
        echo "</pre>";
    }

    $pago = json_decode($_POST['pago_info'], true);
    echo "<pre>";
    print_r($pago);
    echo "</pre>";


    $cliente = json_decode($_POST["id_cliente"], true);

    // if (!$cliente || empty($cliente["id_cliente"])) {
    //     header("Location: crear_venta.php?e=cliente");
    //     exit;
    // }

    $cliente = json_decode($_POST["cliente_info"], true);
    print_r($cliente);


    // include "config/db.php";
    // $conn = connect();

    // $sql = "UPDATE clientes 
    //     SET nombre=?, documento=?, direccion=?, telefono=?, correo=?
    //     WHERE id_cliente=?";

    // $stmt = $conn->prepare($sql);
    // $stmt->execute([
    //     $cliente["nombre"],
    //     $cliente["documento"],
    //     $cliente["direccion"],
    //     $cliente["telefono"],
    //     $cliente["correo"],
    //     $cliente["id_cliente"]
    // ]);
}
