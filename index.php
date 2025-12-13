<?php
include "controllers/session.php";

if ($_SESSION["cargo"] === "admin" || $_SESSION["cargo"] === "code") {
    header("Location: venta")
?>
    <a href="controllers/unlog.php">Cerrar</a>
    
<?php
?>

<?php
} else {
    header("Location: login");
}
?>