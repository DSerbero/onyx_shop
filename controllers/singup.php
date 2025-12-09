<?php
include "../config/db.php";
$conn = connect();

try {
    $stmt = $conn->prepare("INSERT INTO usuarios(nombre, correo, contraseña) VALUES (?, ?, ?)");
    $stmt->bindparam(1, $nombre);
    $stmt->bindparam(2, $correo);
    $stmt->bindparam(3, $password);

    $nombre = $_POST["nombre"];
    $correo = $_POST["email"];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $result = $stmt->execute();

    if ($result) {
        header("Location: ../inicio");
    }
} catch (PDOException $e) {
    header("Location: ../login?e=duplicado");
}
