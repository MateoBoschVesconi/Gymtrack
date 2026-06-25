<?php
require 'db.php';

$alumnos = $conn->query("SELECT * FROM alumno ORDER BY apellido, nombre");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>GymTrack Pro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f4f4; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px 14px; border: 1px solid #ddd; text-align: left; }
        th { background: #333; color: #fff; }
        tr:nth-child(even) { background: #f9f9f9; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .btn { display: inline-block; padding: 6px 12px; border-radius: 4px; font-size: 13px; }
        .btn-green  { background: #28a745; color: #fff; }
        .btn-blue   { background: #007bff; color: #fff; }
        .btn-orange { background: #fd7e14; color: #fff; }
        .btn-red    { background: #dc3545; color: #fff; }
        .btn-gray   { background: #6c757d; color: #fff; }
        .badge-activo   { color: green; font-weight: bold; }
        .badge-inactivo { color: gray; }
        .badge-suspendido { color: red; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    </style>
</head>
<body>

<h1>🏋️ GymTrack Pro</h1>

<div class="top-bar">
    <span><?= $alumnos->num_rows ?> alumno(s) registrado(s)</span>
    <a href="alumno_form.php" class="btn btn-green">+ Nuevo alumno</a>
</div>

<table>
    <tr>
        <th>Nombre</th>
        <th>Email</th>
        <th>Teléfono</th>
        <th>Inscripción</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr>
    <?php while ($a = $alumnos->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($a['nombre'] . ' ' . $a['apellido']) ?></td>
        <td><?= htmlspecialchars($a['email']) ?></td>
        <td><?= htmlspecialchars($a['telefono'] ?? '-') ?></td>
        <td><?= $a['fecha_inscripcion'] ?></td>
        <td><span class="badge-<?= $a['estado'] ?>"><?= ucfirst($a['estado']) ?></span></td>
        <td>
            <a href="alumno_form.php?id=<?= $a['id_alumno'] ?>" class="btn btn-blue">Editar</a>
            <a href="rutina_form.php?id_alumno=<?= $a['id_alumno'] ?>" class="btn btn-orange">Rutina</a>
            <a href="historial.php?id_alumno=<?= $a['id_alumno'] ?>" class="btn btn-gray">Historial</a>
            <a href="alumno_delete.php?id=<?= $a['id_alumno'] ?>"
               class="btn btn-red"
               onclick="return confirm('¿Eliminar a <?= htmlspecialchars($a['nombre']) ?>?')">Eliminar</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
