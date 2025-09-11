<?php
// config/db.php

$host = "127.0.0.1";   // jangan pakai "localhost", lebih stabil "127.0.0.1"
$port = "3307";        // port MariaDB portable
$user = "root";        // username default
$pass = "";            // password default
$db   = "sistem_kuliah";   // nama database

try {
    // koneksi PDO dengan host + port
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
