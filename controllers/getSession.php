<?php

function getSession(){
    if ($_SESSION["cargo"] == "gerente") {
        return "Gerente";
    }
    if ($_SESSION["cargo"] == "admin") {
        return "Administrador";
    }
    if ($_SESSION["cargo"] == "vendedor") {
        return "Vendedor";
    }
    if ($_SESSION["cargo"] == "code") {
        return "Programador";
    }
}


?>