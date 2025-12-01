<?php

 $host = 'localhost';
 $db_name = 'galery_app'; // Sesuaikan dengan nama database Anda
 $username = 'root';      // Sesuaikan dengan username MySQL Anda
 $password = '';          // Sesuaikan dengan password MySQL Anda

// Membuat koneksi
 $conn = new mysqli($host, $username, $password, $db_name);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Mengatur charset
 $conn->set_charset("utf8mb4");
?>