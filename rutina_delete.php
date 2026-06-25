<?php
require 'db.php';

$id_alumno = isset($_GET['id_alumno']) ? (int)$_GET['id_alumno'] : 0;
$id_rutina = isset($_GET['id_rutina']) ? (int)$_GET['id_rutina'] : 0;

if ($id_alumno && $id_rutina) {
    // No borrar: actualizar estado a finalizada y registrar fecha_fin
    $hoy  = date('Y-m-d');
    $stmt = $conn->prepare("UPDATE alumno_rutina SET estado='finalizada', fecha_fin=? WHERE id_alumno=? AND id_rutina=? AND estado='activa'");
    $stmt->bind_param('sii', $hoy, $id_alumno, $id_rutina);
    $stmt->execute();
}

header("Location: historial.php?id_alumno=$id_alumno");
exit;
