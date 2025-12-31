<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../config/db.php";
$conn = connect();

function filtrarProductos($data)
{
    $clean = [];
    foreach ($data as $p) {
        $clean[] = [
            "codigo" => $p["codigo"],
            "cantidad" => $p["cantidad"]
        ];
    }
    return $clean;
}

if (!empty($_POST['id_cliente'])) {

    $info_cliente = json_decode($_POST["cliente_info"], true);

    $stmt_cli = $conn->prepare("UPDATE clientes SET documento=?, nombre=?, direccion=?,
        telefono=?, correo=?, referencia1=?, referencia2=? WHERE id_cliente=?");

    $stmt_cli->bindParam(1, $documento);
    $stmt_cli->bindParam(2, $nombre);
    $stmt_cli->bindParam(3, $direccion);
    $stmt_cli->bindParam(4, $telefono);
    $stmt_cli->bindParam(5, $correo);
    $stmt_cli->bindParam(6, $referencia1);
    $stmt_cli->bindParam(7, $referencia2);
    $stmt_cli->bindParam(8, $id_cliente);

    $documento   = $info_cliente["documento"];
    $nombre      = $info_cliente["nombre"];
    $direccion   = $info_cliente["direccion"];
    $telefono    = $info_cliente["telefono"];
    $correo      = $info_cliente["correo"];
    $referencia1 = $info_cliente["referencia1"];
    $referencia2 = $info_cliente["referencia2"];
    $id_cliente  = $info_cliente["id_cliente"];

    if ($stmt_cli->execute()) {

        $stmt = $conn->prepare("INSERT INTO ventas(id_cliente, productos, tipo_pago, estado) VALUES(?,?,?,?)");

        $stmt->bindParam(1, $cliente);
        $stmt->bindParam(2, $productos);
        $stmt->bindParam(3, $pago);
        $stmt->bindParam(4, $estado);

        $cliente = $id_cliente;

        $productos_recibidos = json_decode($_POST['productos_enviados'], true);
        $productos = json_encode(filtrarProductos($productos_recibidos), JSON_UNESCAPED_UNICODE);

        $info_pago = json_decode($_POST['pago_info'], true);
        $pago = json_encode($info_pago, JSON_UNESCAPED_UNICODE);
        $estado = (in_array("credito", $info_pago["metodos"])) ? "pendiente" : "pago";
        if ($stmt->execute()) {

            foreach ($productos_recibidos as $i) {

                $stmt_pro = $conn->prepare("UPDATE productos 
                                            SET cantidad = cantidad - ? 
                                            WHERE codigo = ?");

                $stmt_pro->bindParam(1, $cantidad_pro);
                $stmt_pro->bindParam(2, $codigo);

                $codigo = $i["codigo"];
                $cantidad_pro = $i["cantidad"];

                $stmt_pro->execute();
            }

            header("Location: ../venta");
            exit;
        }
    }
} else {
    try {
        $stmtcli = $conn->prepare("INSERT INTO clientes(documento, nombre, direccion, telefono, correo, referencia1, referencia2)
                                VALUES (?,?,?,?,?,?,?)");

        $stmtcli->bindParam(1, $documento);
        $stmtcli->bindParam(2, $nombre);
        $stmtcli->bindParam(3, $direccion);
        $stmtcli->bindParam(4, $telefono);
        $stmtcli->bindParam(5, $correo);
        $stmtcli->bindParam(6, $ref1);
        $stmtcli->bindParam(7, $ref2);

        $info_cliente = json_decode($_POST["cliente_info"], true);

        $documento = $info_cliente['documento'];
        $nombre = $info_cliente['nombre'];
        $direccion = $info_cliente['direccion'];
        $telefono = $info_cliente['telefono'];
        $correo = $info_cliente['correo'];
        $ref1 = $info_cliente['referencia1'];
        $ref2 = $info_cliente['referencia2'];

        if ($stmtcli->execute()) {

            $stmt_id_cli = $conn->prepare("SELECT id_cliente FROM clientes WHERE documento=?");
            $stmt_id_cli->bindParam(1, $documento);
            $stmt_id_cli->execute();

            if ($id = $stmt_id_cli->fetch(PDO::FETCH_ASSOC)) {

                $stmt = $conn->prepare("INSERT INTO ventas(id_cliente, productos, tipo_pago, estado) VALUES(?,?,?,?)");

                $stmt->bindParam(1, $cliente);
                $stmt->bindParam(2, $productos);
                $stmt->bindParam(3, $pago);
                $stmt->bindParam(4, $estado);


                $cliente = $id['id_cliente'];
                $productos_recibidos = json_decode($_POST['productos_enviados'], true);
                $productos = json_encode(filtrarProductos($productos_recibidos), JSON_UNESCAPED_UNICODE);

                $info_pago = json_decode($_POST['pago_info'], true);
                $pago = json_encode($info_pago, JSON_UNESCAPED_UNICODE);
                $estado = (in_array("credito", $info_pago["metodos"])) ? "pendiente" : "pago";

                if ($stmt->execute()) {

                    foreach ($productos_recibidos as $i) {

                        $stmt_pro = $conn->prepare("UPDATE productos 
                                                    SET cantidad = cantidad - ? 
                                                    WHERE codigo = ?");

                        $stmt_pro->bindParam(1, $cantidad_pro);
                        $stmt_pro->bindParam(2, $codigo);

                        $codigo = $i["codigo"];
                        $cantidad_pro = $i["cantidad"];

                        $stmt_pro->execute();
                    }

                    header("Location: ../venta");
                    exit;
                }
            }
        }
    } catch (PDOException $e) {
        session_start();

        if ($e->getCode() == 23000) {
            $_SESSION["venta_error"] = "reg"; // registro duplicado
            header("Location: ../venta");
            exit;
        } else {
            $_SESSION["venta_error"] = "err"; // error general
            header("Location: ../venta");
            exit;
        }
    }
}
