<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include "../config/db.php";
$conn = connect();
function crearArray($data, $clave, $tipo){
    $dato = array();
    foreach($data as $i) {
        $dato[$i[$clave]] = $i[$tipo];
    }
    return $dato;
}


if ($_POST['id_cliente'] != NULL) {
    $stmt = $conn->prepare("INSERT INTO ventas(id_cliente, productos, tipo_pago) VALUES(?,?,?)");
    
    $stmt->bindparam(1, $cliente);
    $stmt->bindparam(2, $productos);
    $stmt->bindparam(3, $pago);

    $info_cliente = json_decode($_POST["id_cliente"], true);
    $cliente = ($info_cliente);
    print_r($info_cliente);
    $productos_recibidos = json_decode($_POST['productos_enviados'], true);
    $productos = json_encode(crearArray($productos_recibidos, "codigo", "cantidad"));
    $info_pago = json_decode($_POST['pago_info'], true);
    $pago = implode(",", $info_pago);


    $result = $stmt->execute();

    if ($result) {
        header("Location: ../venta");
    }
} else {
    $stmtcli = $conn->prepare("INSERT INTO clientes(documento, nombre, direccion, telefono, correo, ref1, ref2) VALUES (?,?,?,?,?,?,?)");

    $stmtcli->bindparam(1, $documento);
    $stmtcli->bindparam(2, $nombre);
    $stmtcli->bindparam(3, $direccion);
    $stmtcli->bindparam(4, $telefono);
    $stmtcli->bindparam(5, $correo);
    $stmtcli->bindparam(6, $ref1);
    $stmtcli->bindparam(7, $ref2);

    $info_cliente = json_decode($_POST["cliente_info"], true);
    print_r($info_cliente);

    $documento = $info_cliente['documento'];
    $nombre = $info_cliente['nombre'];
    $direccion = $info_cliente['direccion'];
    $telefono = $info_cliente['telefono'];
    $correo = $info_cliente['correo'];
    $ref1 = $info_cliente['referencia1'];
    $ref2 = $info_cliente['referencia2'];

    $resultcli = $stmtcli->execute();

    if ($resultcli) {
        $stmt_id_cli = $conn->prepare("SELECT id_cliente FROM clientes WHERE documento=?");
        $stmt_id_cli->bindparam(1, $documento);

        $result_id = $stmt_id_cli->execute();
        $id = $stmt_id_cli->fetch(PDO::FETCH_ASSOC);
        if ($id) {
            $stmt = $conn->prepare("INSERT INTO ventas(id_cliente, productos, tipo_pago) VALUES(?,?,?)");
            
            $stmt->bindparam(1, $cliente);
            $stmt->bindparam(2, $productos);
            $stmt->bindparam(3, $pago);

            $cliente = $id['id_cliente'];
            $productos_recibidos = json_decode($_POST['productos_enviados'], true);
            $productos = json_encode(crearArray($productos_recibidos, "codigo", "cantidad"));
            $info_pago = json_decode($_POST['pago_info'], true);
            $pago = implode(",", $info_pago);


            $result = $stmt->execute();
            if ($result) {
                header("Location: ../venta");
            }
        }
        

    }

}

// try {
    
// } catch (PDOException $e) {
//     header("Location: ../login?e=vacio");
// }



?>