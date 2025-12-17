<?php
include "controllers/session.php";

if (!in_array($_SESSION["cargo"], ["gerente", "admin", "code"])) {
    header("Location: venta")
?>

    
<?php
?>

<?php
} else {
    header("Location: login");
}
?>