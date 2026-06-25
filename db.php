<?php
$host = 'localhost';
$db   = 'gymtrackpro';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
