<?php
require 'db.php';

$id_alumno = isset($_GET['id_alumno']) ? (int)$_GET['id_alumno'] : 0;
$id_rutina = isset($_GET['id_rutina']) ? (int)$_GET['id_rutina'] : 0;

if (!$id_alumno || !$id_rutina) { header('Location: index.php'); exit; }

// Cargar alumno
$stmt = $conn->prepare("SELECT * FROM alumno WHERE id_alumno = ?");
$stmt->bind_param('i', $id_alumno);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();

// Cargar rutina
$stmt = $conn->prepare("SELECT * FROM rutina WHERE id_rutina = ?");
$stmt->bind_param('i', $id_rutina);
$stmt->execute();
$rutina = $stmt->get_result()->fetch_assoc();

// Cargar ejercicios agrupados por día
$stmt = $conn->prepare("
    SELECT re.*, e.nombre AS ej_nombre, e.grupo_muscular, e.tipo
    FROM rutina_ejercicio re
    JOIN ejercicio e ON re.id_ejercicio = e.id_ejercicio
    WHERE re.id_rutina = ?
    ORDER BY re.dia, re.orden
");
$stmt->bind_param('i', $id_rutina);
$stmt->execute();
$res = $stmt->get_result();

$dias = [];
while ($row = $res->fetch_assoc()) {
    $dias[$row['dia']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Rutina — <?= htmlspecialchars($alumno['nombre']) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 30px; color: #222; background: #fff; }

        .header { border-bottom: 3px solid #222; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { margin: 0 0 4px 0; font-size: 22px; }
        .header .sub { font-size: 13px; color: #555; }

        .info-grid { display: flex; gap: 30px; margin-bottom: 20px; flex-wrap: wrap; }
        .info-item { font-size: 13px; }
        .info-item strong { display: block; font-size: 11px; text-transform: uppercase; color: #888; }

        .dia-titulo {
            background: #222; color: #fff;
            padding: 6px 12px; font-size: 14px; font-weight: bold;
            margin-top: 20px; margin-bottom: 0;
            border-radius: 4px 4px 0 0;
        }

        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #f0f0f0; padding: 8px 10px; text-align: left; border: 1px solid #ddd; }
        td { padding: 8px 10px; border: 1px solid #ddd; }
        tr:nth-child(even) { background: #fafafa; }

        .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 11px; color: #aaa; text-align: right; }

        /* Botones — solo en pantalla, no en impresión */
        .no-print { margin-bottom: 20px; }
        .btn { display: inline-block; padding: 8px 16px; border-radius: 4px; font-size: 13px; cursor: pointer; text-decoration: none; border: none; }
        .btn-print { background: #28a745; color: #fff; }
        .btn-back  { background: #6c757d; color: #fff; }

        @media print {
            .no-print { display: none; }
            body { padding: 15px; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn btn-print">🖨️ Imprimir / Guardar PDF</button>
    <a href="historial.php?id_alumno=<?= $id_alumno ?>" class="btn btn-back">← Volver</a>
</div>

<div class="header">
    <h1>🏋️ GymTrack Pro — Rutina de Entrenamiento</h1>
    <div class="sub">Generado el <?= date('d/m/Y') ?></div>
</div>

<div class="info-grid">
    <div class="info-item">
        <strong>Alumno</strong>
        <?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']) ?>
    </div>
    <div class="info-item">
        <strong>Rutina</strong>
        <?= htmlspecialchars($rutina['nombre']) ?>
    </div>
    <div class="info-item">
        <strong>Nivel</strong>
        <?= ucfirst($rutina['nivel']) ?>
    </div>
    <div class="info-item">
        <strong>Objetivo</strong>
        <?= str_replace('_', ' ', ucfirst($rutina['objetivo'])) ?>
    </div>
    <div class="info-item">
        <strong>Duración</strong>
        <?= $rutina['duracion_min'] ?> min
    </div>
    <div class="info-item">
        <strong>Días por semana</strong>
        <?= $rutina['cant_dias'] ?>
    </div>
</div>

<?php if ($rutina['descripcion']): ?>
<p style="font-size:13px; color:#555; margin-bottom:20px;">
    <?= htmlspecialchars($rutina['descripcion']) ?>
</p>
<?php endif; ?>

<?php if (empty($dias)): ?>
    <p>Esta rutina no tiene ejercicios cargados.</p>
<?php else: ?>
    <?php foreach ($dias as $dia => $ejercicios): ?>
        <div class="dia-titulo"><?= htmlspecialchars($dia) ?></div>
        <table>
            <tr>
                <th>#</th>
                <th>Ejercicio</th>
                <th>Grupo muscular</th>
                <th>Tipo</th>
                <th>Series</th>
                <th>Repeticiones</th>
                <th>Descanso</th>
            </tr>
            <?php foreach ($ejercicios as $i => $ej): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($ej['ej_nombre']) ?></td>
                <td><?= htmlspecialchars($ej['grupo_muscular']) ?></td>
                <td><?= ucfirst($ej['tipo']) ?></td>
                <td><?= $ej['series'] ?></td>
                <td><?= $ej['repeticiones'] ?></td>
                <td><?= $ej['descanso_seg'] ?> seg</td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endforeach; ?>
<?php endif; ?>

<div class="footer">
    GymTrack Pro — <?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']) ?> — <?= date('d/m/Y') ?>
</div>

</body>
</html>
