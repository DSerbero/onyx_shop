<?php
include "../config/db.php";
$conn = connect();
function crearArray($data, $clave, $tipo){
    $dato = array();
    foreach($data as $i) {
        $dato[$i[$clave]] = $i[$tipo];
    }
    return $dato;
}


// try {
    $stmt = $conn->prepare("INSERT INTO ventas(id_cliente, productos, tipo_pago) VALUES(?,?,?)");
    
    $stmt->bindparam(1, $cliente);
    $stmt->bindparam(2, $productos);
    $stmt->bindparam(3, $pago);

    $info_cliente = json_decode($_POST["id_cliente"], true);
    $cliente = ($info_cliente["id_cliente"]);
    $productos_recibidos = json_decode($_POST['productos_enviados'], true);
    $productos = crearArray($productos_recibidos, "codigo", "cantidad");
    $info_pago = json_decode($_POST['pago_info'], true);
    $pago = $info_pago;


    $result = $stmt->execute();

    if ($result) {
        header("Location: ../venta");
    }
// } catch (PDOException $e) {
//     header("Location: ../login?e=vacio");
// }



?>