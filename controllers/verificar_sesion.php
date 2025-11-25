<?php
require "./session.php";

if (isset($_SESSION["cargo"])) {
        echo $_SESSION["cargo"];
        header("Location: ../inicio");
} else {

}

