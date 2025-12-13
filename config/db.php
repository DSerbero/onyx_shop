<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

function connect()
{
    $hostname = $_ENV["DB_HOST"];
    $userdb   = $_ENV["DB_USER"];
    $password = $_ENV["DB_PASS"];
    $dbname   = $_ENV["DB_NAME"];

    try {
        $conn = new PDO("mysql:host=$hostname;dbname=$dbname;charset=utf8mb4", $userdb, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        date_default_timezone_set('America/Bogota');
        $conn->exec("SET time_zone = '-05:00'");
        return $conn;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
