<?php
require 'db.php';

$id_alumno = isset($_GET['id_alumno']) ? (int)$_GET['id_alumno'] : 0;
if (!$id_alumno) { header('Location: index.php'); exit; }

$stmt = $conn->prepare("SELECT * FROM alumno WHERE id_alumno = ?");
$stmt->bind_param('i', $id_alumno);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();
if (!$alumno) { header('Location: index.php'); exit; }

// Traer todas las rutinas asignadas (historial completo)
$stmt = $conn->prepare("
    SELECT ar.*, r.nombre, r.nivel, r.objetivo, r.duracion_min, r.cant_dias
    FROM alumno_rutina ar
    JOIN rutina r ON ar.id_rutina = r.id_rutina
    WHERE ar.id_alumno = ?
    ORDER BY ar.fecha_inicio DESC
");
$stmt->bind_param('i', $id_alumno);
$stmt->execute();
$rutinas = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial — <?= htmlspecialchars($alumno['nombre']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f4f4; }
        h2 { color: #333; }
        .alumno-header { background: #333; color: #fff; padding: 10px 16px; border-radius: 6px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px 14px; border: 1px solid #ddd; text-align: left; }
        th { background: #333; color: #fff; }
        tr:nth-child(even) { background: #f9f9f9; }
        .badge-activa     { color: green;  font-weight: bold; }
        .badge-finalizada { color: gray; }
        .badge-pausada    { color: orange; }
        a { color: #0066cc; text-decoration: none; }
        .btn { display: inline-block; padding: 6px 12px; border-radius: 4px; font-size: 13px; }
        .btn-blue   { background: #007bff; color: #fff; }
        .btn-red    { background: #dc3545; color: #fff; }
        .btn-green  { background: #28a745; color: #fff; }
        .btn-gray   { background: #6c757d; color: #fff; }
        .actions-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    </style>
</head>
<body>

<div class="alumno-header">
    Alumno: <strong><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']) ?></strong>
</div>

<h2>📋 Historial de rutinas</h2>

<div class="actions-top">
    <a href="rutina_form.php?id_alumno=<?= $id_alumno ?>" class="btn btn-green">+ Nueva rutina</a>
    <a href="index.php" class="btn btn-gray">← Volver</a>
</div>

<?php if ($rutinas->num_rows === 0): ?>
    <p>Este alumno no tiene rutinas registradas todavía.</p>
<?php else: ?>
<table>
    <tr>
        <th>Rutina</th>
        <th>Nivel</th>
        <th>Objetivo</th>
        <th>Días</th>
        <th>Duración</th>
        <th>Inicio</th>
        <th>Fin</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr>
    <?php while ($r = $rutinas->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($r['nombre']) ?></td>
        <td><?= ucfirst($r['nivel']) ?></td>
        <td><?= str_replace('_', ' ', ucfirst($r['objetivo'])) ?></td>
        <td><?= $r['cant_dias'] ?> días</td>
        <td><?= $r['duracion_min'] ?> min</td>
        <td><?= $r['fecha_inicio'] ?></td>
        <td><?= $r['fecha_fin'] ?? '—' ?></td>
        <td><span class="badge-<?= $r['estado'] ?>"><?= ucfirst($r['estado']) ?></span></td>
        <td>
            <?php if ($r['estado'] === 'activa'): ?>
                <a href="rutina_form.php?id_alumno=<?= $id_alumno ?>&id_rutina=<?= $r['id_rutina'] ?>" class="btn btn-blue">Editar</a>
                <a href="rutina_pdf.php?id_alumno=<?= $id_alumno ?>&id_rutina=<?= $r['id_rutina'] ?>" class="btn btn-green">PDF</a>
                <a href="rutina_delete.php?id_alumno=<?= $id_alumno ?>&id_rutina=<?= $r['id_rutina'] ?>"
                   class="btn btn-red"
                   onclick="return confirm('¿Finalizar esta rutina y pasarla al historial?')">Finalizar</a>
            <?php else: ?>
                <a href="rutina_pdf.php?id_alumno=<?= $id_alumno ?>&id_rutina=<?= $r['id_rutina'] ?>" class="btn btn-green">PDF</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<?php endif; ?>

</body>
</html>
