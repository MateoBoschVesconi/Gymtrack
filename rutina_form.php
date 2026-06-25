<?php
require 'db.php';

$id_alumno = isset($_GET['id_alumno']) ? (int) $_GET['id_alumno'] : 0;
$id_rutina = isset($_GET['id_rutina']) ? (int) $_GET['id_rutina'] : 0;
$error = '';

if (!$id_alumno) {
    header('Location: index.php');
    exit;
}

// Cargar alumno
$stmt = $conn->prepare("SELECT * FROM alumno WHERE id_alumno = ?");
$stmt->bind_param('i', $id_alumno);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();
if (!$alumno) {
    header('Location: index.php');
    exit;
}

// Todos los ejercicios disponibles
$ejercicios = $conn->query("SELECT * FROM ejercicio ORDER BY nombre");
$lista_ejercicios = [];
while ($e = $ejercicios->fetch_assoc())
    $lista_ejercicios[] = $e;

// Si hay id_rutina, cargar datos existentes para editar
$rutina = null;
$ejerc_rutina = []; // [dia => [ejercicios]]

if ($id_rutina) {
    $stmt = $conn->prepare("SELECT * FROM rutina WHERE id_rutina = ?");
    $stmt->bind_param('i', $id_rutina);
    $stmt->execute();
    $rutina = $stmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("SELECT re.*, e.nombre as ej_nombre FROM rutina_ejercicio re JOIN ejercicio e ON re.id_ejercicio = e.id_ejercicio WHERE re.id_rutina = ? ORDER BY re.dia, re.orden");
    $stmt->bind_param('i', $id_rutina);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $ejerc_rutina[$row['dia']][] = $row;
    }
}

// PROCESAR FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $nivel = $_POST['nivel'];
    $duracion = (int) $_POST['duracion_min'];
    $objetivo = $_POST['objetivo'];
    $cant_dias = (int) $_POST['cant_dias'];

    if ($id_rutina) {
        // EDITAR rutina existente
        $stmt = $conn->prepare("UPDATE rutina SET nombre=?, descripcion=?, nivel=?, duracion_min=?, objetivo=?, cant_dias=? WHERE id_rutina=?");
        $stmt->bind_param('sssisii', $nombre, $descripcion, $nivel, $duracion, $objetivo, $cant_dias, $id_rutina);
        $stmt->execute();

        // Borrar ejercicios anteriores y reinsertar
        $stmt = $conn->prepare("DELETE FROM rutina_ejercicio WHERE id_rutina=?");
        $stmt->bind_param('i', $id_rutina);
        $stmt->execute();

    } else {
        // CREAR rutina nueva
        $stmt = $conn->prepare("INSERT INTO rutina (nombre, descripcion, nivel, duracion_min, objetivo, cant_dias) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param('sssisi', $nombre, $descripcion, $nivel, $duracion, $objetivo, $cant_dias);
        $stmt->execute();
        $id_rutina = $conn->insert_id;

        // Asignar al alumno
        $hoy = date('Y-m-d');

        // Primero finalizar cualquier rutina activa del alumno
        $stmt = $conn->prepare("UPDATE alumno_rutina SET estado='finalizada', fecha_fin=? WHERE id_alumno=? AND estado='activa'");
        $stmt->bind_param('si', $hoy, $id_alumno);
        $stmt->execute();

        // Insertar nueva asignación
        $stmt = $conn->prepare("INSERT INTO alumno_rutina (id_alumno, id_rutina, fecha_inicio, estado) VALUES (?,?,?,'activa')");
        $stmt->bind_param('iis', $id_alumno, $id_rutina, $hoy);
        $stmt->execute();
    }

    // Insertar ejercicios por día
    for ($d = 1; $d <= $cant_dias; $d++) {
        $dia_key = "Dia $d";
        if (!isset($_POST["ejercicios_dia_$d"]))
            continue;

        $ejercicios_dia = $_POST["ejercicios_dia_$d"];
        $orden = 1;
        foreach ($ejercicios_dia as $idx => $id_ej) {
            $id_ej = (int) $id_ej;
            $series = (int) $_POST["series_{$d}_{$idx}"];
            $reps = (int) $_POST["reps_{$d}_{$idx}"];
            $descanso = (int) $_POST["descanso_{$d}_{$idx}"];

            if ($id_ej > 0 && $series > 0 && $reps > 0) {
                $stmt = $conn->prepare("INSERT INTO rutina_ejercicio (id_rutina, id_ejercicio, dia, series, repeticiones, descanso_seg, orden) VALUES (?,?,?,?,?,?,?)");
                $stmt->bind_param('iisiiii', $id_rutina, $id_ej, $dia_key, $series, $reps, $descanso, $orden);
                $stmt->execute();
                $orden++;
            }
        }
    }

    header("Location: index.php");
    exit;
}

$cant_dias_default = $rutina['cant_dias'] ?? 3;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= $id_rutina ? 'Editar rutina' : 'Nueva rutina' ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f4f4f4;
        }

        h2,
        h3 {
            color: #333;
        }

        .card {
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            max-width: 800px;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
            font-size: 14px;
        }

        input[type=text],
        input[type=number],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .dia-bloque {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 16px;
            margin-top: 16px;
            background: #fafafa;
        }

        .dia-titulo {
            font-weight: bold;
            font-size: 15px;
            color: #333;
            margin-bottom: 12px;
        }

        .ejercicio-row {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }

        .ejercicio-row select {
            flex: 2;
            min-width: 180px;
        }

        .ejercicio-row input {
            flex: 1;
            min-width: 60px;
        }

        .ejercicio-row label {
            display: none;
        }

        .mini-label {
            font-size: 11px;
            color: #888;
            display: block;
            text-align: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-save {
            background: #28a745;
            color: #fff;
        }

        .btn-add {
            background: #007bff;
            color: #fff;
            font-size: 12px;
            padding: 6px 12px;
            margin-top: 8px;
        }

        .btn-back {
            background: #6c757d;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
        }

        .btn-remove {
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 6px 10px;
            cursor: pointer;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .alumno-header {
            background: #333;
            color: #fff;
            padding: 10px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .cols-header {
            display: flex;
            gap: 8px;
            font-size: 11px;
            color: #888;
            margin-bottom: 4px;
            flex-wrap: wrap;
        }

        .cols-header span:first-child {
            flex: 2;
            min-width: 180px;
        }

        .cols-header span {
            flex: 1;
            min-width: 60px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="alumno-header">
        Alumno: <strong><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']) ?></strong>
    </div>

    <h2><?= $id_rutina ? 'Editar rutina' : 'Nueva rutina' ?></h2>

    <div class="card">
        <form method="POST" id="formRutina">

            <label>Nombre de la rutina</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($rutina['nombre'] ?? '') ?>">

            <label>Descripción</label>
            <textarea name="descripcion" rows="2"><?= htmlspecialchars($rutina['descripcion'] ?? '') ?></textarea>

            <label>Nivel</label>
            <select name="nivel">
                <?php foreach (['principiante', 'intermedio', 'avanzado'] as $op): ?>
                    <option value="<?= $op ?>" <?= ($rutina['nivel'] ?? '') === $op ? 'selected' : '' ?>><?= ucfirst($op) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Duración estimada (minutos)</label>
            <input type="number" name="duracion_min" min="1" value="<?= $rutina['duracion_min'] ?? 45 ?>">

            <label>Objetivo</label>
            <select name="objetivo">
                <?php foreach (['perdida_peso', 'hipertrofia', 'resistencia', 'movilidad'] as $op): ?>
                    <option value="<?= $op ?>" <?= ($rutina['objetivo'] ?? '') === $op ? 'selected' : '' ?>>
                        <?= str_replace('_', ' ', ucfirst($op)) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Cantidad de días</label>
            <select name="cant_dias" id="cant_dias" onchange="renderDias()">
                <?php for ($i = 1; $i <= 7; $i++): ?>
                    <option value="<?= $i ?>" <?= $cant_dias_default == $i ? 'selected' : '' ?>><?= $i ?>
                        día<?= $i > 1 ? 's' : '' ?></option>
                <?php endfor; ?>
            </select>

            <div id="dias-container"></div>

            <div class="actions">
                <button type="submit" class="btn btn-save">💾 Guardar rutina</button>
                <a href="index.php" class="btn-back">← Volver</a>
            </div>

        </form>
    </div>

    <script>
        // Datos de ejercicios ya cargados en PHP para usarlos en JS
        const ejerciciosDisponibles = <?= json_encode($lista_ejercicios) ?>;
        const ejerciciosPorDia = <?= json_encode($ejerc_rutina) ?>;

        function renderDias() {
            const cant = parseInt(document.getElementById('cant_dias').value);
            const container = document.getElementById('dias-container');
            container.innerHTML = '';

            for (let d = 1; d <= cant; d++) {
                const diaKey = `Dia ${d}`;
                const ejercsCargados = ejerciciosPorDia[diaKey] || [];

                const bloque = document.createElement('div');
                bloque.className = 'dia-bloque';
                bloque.id = `bloque_dia_${d}`;

                bloque.innerHTML = `
            <div class="dia-titulo">📅 Día ${d}</div>
            <div class="cols-header">
                <span>Ejercicio</span>
                <span>Series</span>
                <span>Reps</span>
                <span>Descanso (seg)</span>
                <span></span>
            </div>
            <div id="ejercicios_dia_${d}"></div>
            <button type="button" class="btn btn-add" onclick="agregarEjercicio(${d})">+ Agregar ejercicio</button>
        `;
                container.appendChild(bloque);

                // Si hay ejercicios ya cargados, renderizarlos
                if (ejercsCargados.length > 0) {
                    ejercsCargados.forEach(ej => {
                        agregarEjercicio(d, ej.id_ejercicio, ej.series, ej.repeticiones, ej.descanso_seg);
                    });
                } else {
                    agregarEjercicio(d); // una fila vacía por defecto
                }
            }
        }

        function agregarEjercicio(dia, id_ej = 0, series = 3, reps = 10, descanso = 60) {
            const container = document.getElementById(`ejercicios_dia_${dia}`);
            const idx = container.children.length;

            const opciones = ejerciciosDisponibles.map(e =>
                `<option value="${e.id_ejercicio}" ${e.id_ejercicio == id_ej ? 'selected' : ''}>${e.nombre} (${e.tipo})</option>`
            ).join('');

            const row = document.createElement('div');
            row.className = 'ejercicio-row';
            row.innerHTML = `
        <select name="ejercicios_dia_${dia}[]">
            <option value="">-- Elegir ejercicio --</option>
            ${opciones}
        </select>
        <div>
            <span class="mini-label">Series</span>
            <input type="number" name="series_${dia}_${idx}" value="${series}" min="1" max="10">
        </div>
        <div>
            <span class="mini-label">Reps</span>
            <input type="number" name="reps_${dia}_${idx}" value="${reps}" min="1" max="100">
        </div>
        <div>
            <span class="mini-label">Descanso</span>
            <input type="number" name="descanso_${dia}_${idx}" value="${descanso}" min="0" max="600">
        </div>
        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✕</button>
    `;
            container.appendChild(row);
        }

        // Renderizar al cargar la página
        renderDias();
    </script>

</body>

</html>