<?php
$host = "localhost";
$user = "root"; // ganti bila bukan root
$pass = "";     // ganti dengan password MySQL Anda
$db   = "inventory"; // ganti dengan nama database Anda

$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_errno) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>