<?php
function connect(){
    $hostname = "localhost";
    $userdb = "root";
    $password = "";
    $dbname = "onyx_shop";

    try {
        $conn = new PDO("mysql:host=".$hostname.";dbname=".$dbname, $userdb, $password );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        echo "Error ". $e->getMessage();
    }
}
?>