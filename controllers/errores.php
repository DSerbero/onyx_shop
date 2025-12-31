<?php

function error($e) {
    switch ($e) {
        case "duplicado":
            return "El correo ya se encuentra registrado.";
        case "incorrecto":
            return "El correo o contraseña es incorrecto.";
        case "inactivo":
            return "El usuario se enceuntra inactivo.";
        default:
            return "Ni puta idea.";
            

    }
}

?>