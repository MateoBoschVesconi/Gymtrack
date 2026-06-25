<?php
require 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM alumno WHERE id_alumno = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}

header('Location: index.php');
exit;
