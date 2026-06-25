<?php
require 'db.php';

$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$alumno = null;
$error  = '';

// Si viene id, cargamos el alumno para editar
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM alumno WHERE id_alumno = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $alumno = $stmt->get_result()->fetch_assoc();
    if (!$alumno) { header('Location: index.php'); exit; }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre']);
    $apellido  = trim($_POST['apellido']);
    $email     = trim($_POST['email']);
    $tel       = trim($_POST['telefono']);
    $fnac      = $_POST['fecha_nacimiento'];
    $estado    = $_POST['estado'];

    // Armar JSON perfil_fisico
    $perfil = json_encode([
        'peso_kg'             => (float)$_POST['peso_kg'],
        'altura_cm'           => (int)$_POST['altura_cm'],
        'experiencia'         => $_POST['experiencia'],
        'dias_disponibles'    => (int)$_POST['dias_disponibles'],
        'lesiones'            => array_values(array_filter(array_map('trim', explode(',', $_POST['lesiones'])))),
        'objetivo_secundario' => trim($_POST['objetivo_secundario']),
    ]);

    if ($id) {
        // EDITAR
        $stmt = $conn->prepare("UPDATE alumno SET nombre=?, apellido=?, email=?, telefono=?, fecha_nacimiento=?, estado=?, perfil_fisico=? WHERE id_alumno=?");
        $stmt->bind_param('sssssssi', $nombre, $apellido, $email, $tel, $fnac, $estado, $perfil, $id);
    } else {
        // NUEVO
        $hoy = date('Y-m-d');
        $stmt = $conn->prepare("INSERT INTO alumno (nombre, apellido, email, telefono, fecha_nacimiento, estado, perfil_fisico, fecha_inscripcion) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssssssss', $nombre, $apellido, $email, $tel, $fnac, $estado, $perfil, $hoy);
    }

    if ($stmt->execute()) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Error al guardar: ' . $conn->error;
    }
}

// Decodificar perfil si estamos editando
$perfil_data = [];
if ($alumno && $alumno['perfil_fisico']) {
    $perfil_data = json_decode($alumno['perfil_fisico'], true);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Editar alumno' : 'Nuevo alumno' ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f4f4; }
        h2 { color: #333; }
        .card { background: #fff; padding: 24px; border-radius: 8px; max-width: 600px; }
        label { display: block; margin-top: 12px; font-weight: bold; font-size: 14px; }
        input[type=text], input[type=email], input[type=date], input[type=number], select, textarea {
            width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box;
            border: 1px solid #ccc; border-radius: 4px; font-size: 14px;
        }
        .section-title { margin-top: 20px; padding-top: 12px; border-top: 1px solid #eee; color: #555; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-save { background: #28a745; color: #fff; }
        .btn-back { background: #6c757d; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 4px; }
        .error { color: red; margin-bottom: 12px; }
        .actions { margin-top: 20px; display: flex; gap: 10px; }
    </style>
</head>
<body>

<h2><?= $id ? 'Editar alumno' : 'Nuevo alumno' ?></h2>

<?php if ($error): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<div class="card">
<form method="POST">

    <p class="section-title">Datos personales</p>

    <label>Nombre</label>
    <input type="text" name="nombre" required value="<?= htmlspecialchars($alumno['nombre'] ?? '') ?>">

    <label>Apellido</label>
    <input type="text" name="apellido" required value="<?= htmlspecialchars($alumno['apellido'] ?? '') ?>">

    <label>Email</label>
    <input type="email" name="email" required value="<?= htmlspecialchars($alumno['email'] ?? '') ?>">

    <label>Teléfono</label>
    <input type="text" name="telefono" value="<?= htmlspecialchars($alumno['telefono'] ?? '') ?>">

    <label>Fecha de nacimiento</label>
    <input type="date" name="fecha_nacimiento" value="<?= $alumno['fecha_nacimiento'] ?? '' ?>">

    <label>Estado</label>
    <select name="estado">
        <?php foreach (['activo','inactivo','suspendido'] as $op): ?>
        <option value="<?= $op ?>" <?= ($alumno['estado'] ?? 'activo') === $op ? 'selected' : '' ?>><?= ucfirst($op) ?></option>
        <?php endforeach; ?>
    </select>

    <p class="section-title">Perfil físico</p>

    <label>Peso (kg)</label>
    <input type="number" step="0.1" name="peso_kg" value="<?= $perfil_data['peso_kg'] ?? '' ?>">

    <label>Altura (cm)</label>
    <input type="number" name="altura_cm" value="<?= $perfil_data['altura_cm'] ?? '' ?>">

    <label>Experiencia</label>
    <select name="experiencia">
        <?php foreach (['principiante','intermedio','avanzado'] as $op): ?>
        <option value="<?= $op ?>" <?= ($perfil_data['experiencia'] ?? '') === $op ? 'selected' : '' ?>><?= ucfirst($op) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Días disponibles por semana</label>
    <input type="number" name="dias_disponibles" min="1" max="7" value="<?= $perfil_data['dias_disponibles'] ?? '' ?>">

    <label>Lesiones (separadas por coma)</label>
    <input type="text" name="lesiones" placeholder="ej: rodilla derecha, lumbar"
           value="<?= htmlspecialchars(implode(', ', $perfil_data['lesiones'] ?? [])) ?>">

    <label>Objetivo secundario</label>
    <input type="text" name="objetivo_secundario" value="<?= htmlspecialchars($perfil_data['objetivo_secundario'] ?? '') ?>">

    <div class="actions">
        <button type="submit" class="btn btn-save">💾 Guardar</button>
        <a href="index.php" class="btn-back">← Volver</a>
    </div>

</form>
</div>

</body>
</html>
