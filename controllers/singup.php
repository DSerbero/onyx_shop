<?php
include "../config/db.php";
$conn = connect();

try {
    $stmt = $conn->prepare("INSERT INTO usuarios(correo, contraseña) VALUES ( ?, ?)");
    $stmt->bindparam(1, $correo);
    $stmt->bindparam(2, $password);

    $correo = $_POST["email"];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $result = $stmt->execute();

    if ($result) {
        header("Location: ../inicio");
    }
} catch (PDOException $e) {
    header("Location: ../login?e=duplicado");
}
