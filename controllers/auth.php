<?php
include "../config/db.php";
include "session.php";
$conn = connect();

try {;
    $stmt = $conn->prepare("SELECT cargo, correo, contraseña FROM usuarios WHERE correo=?");
    $stmt->bindparam(1, $correo);

    $correo = $_POST["email"];

    $result = $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($_POST['password'], $user['contraseña'])) {
        $_SESSION["cargo"] = $user["cargo"];
        $_SESSION["user"] = $user["correo"];

        header("Location: verificar_sesion.php");
    } else {
        header("Location: ../login?e=incorrecto");

    }
} catch (PDOException $e) {
}
