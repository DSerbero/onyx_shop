<?php
include "../config/db.php";
include "session.php";

$conn = connect();

try {

    $correo = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';

    $stmt = $conn->prepare("
        SELECT nombre, cargo, correo, contraseña, estado 
        FROM usuarios 
        WHERE correo = ?
        LIMIT 1
    ");

    $stmt->bindParam(1, $correo);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: ../login?e=incorrecto");
        exit;
    }

    if ($user["estado"] == 0) {
        header("Location: ../login?e=inactivo");
        exit;
    }

    if (!password_verify($password, $user["contraseña"])) {
        header("Location: ../login?e=incorrecto");
        exit;
    }

    $_SESSION["nombre"] = $user["nombre"];
    $_SESSION["cargo"]  = $user["cargo"];
    $_SESSION["user"]   = $user["correo"];

    header("Location: verificar_sesion.php");
    exit;
} catch (PDOException $e) {
    header("Location: ../login?e=error");
    exit;
}
