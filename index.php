<?php
// --- Parámetros de conexión (XAMPP defaults) ---
$host = 'localhost';
$db = 'gymtrackpro';
$user = 'root';
$pass = '';

// --- Conexión MySQLi orientada a objetos ---
$conn = new mysqli($host, $user, $pass, $db);

// --- Manejo de error de conexión ---
if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}

// --- Charset: importante para tildes y caracteres especiales ---
$conn->set_charset('utf8mb4');

// La conexión quedará abierta para usarla más abajo
?>
<?php
// Paso 1: formulario puro, sin lógica de procesamiento todavía
// En este paso no hay PHP útil: solo estructuramos el HTML
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Paso 1 — Formulario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        input[type=text] {
            width: 500px;
            padding: 8px;
            font-size: 14px;
        }

        button {
            padding: 8px 20px;
            font-size: 14px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <h2>Consulta SQL</h2>

    <!-- El formulario envía a este mismo archivo (action vacío) -->
    <!-- method POST: los datos van en el cuerpo, no en la URL   -->
    <form action="" method="POST">

        <label for="consulta">Escribí tu consulta SQL:</label><br><br>

        <!-- name="consulta": con este nombre lo recuperamos en PHP -->
        <input type="text" id="consulta" name="consulta" placeholder="SELECT * FROM productos">

        &nbsp;
        <button type="submit">Ejecutar</button>

    </form>

    <hr style="margin-top: 30px; margin-bottom: 30px;">

    <?php
    // Paso 2: Procesar la consulta cuando el usuario hace clic en "Ejecutar"
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['consulta'])) {
        $consulta = $_POST['consulta']; // Obtenemos lo que escribiste en el input
        
        echo "<h3>Resultados para: <code style='background: #eee; padding: 4px; border-radius: 4px;'>" . htmlspecialchars($consulta) . "</code></h3>";

        // Ejecutar la consulta contra la base de datos
        $resultado = $conn->query($consulta);

        if ($resultado === TRUE) {
            // Si es un INSERT, UPDATE o DELETE exitoso (no devuelve filas, solo TRUE)
            echo "<p style='color: green;'>✅ Consulta ejecutada correctamente. Filas afectadas: " . $conn->affected_rows . "</p>";
        } elseif ($resultado === FALSE) {
            // Si hubo un error en la consulta (mal escrita, tabla no existe, etc.)
            echo "<p style='color: red;'>❌ Error en la consulta: " . $conn->error . "</p>";
        } else {
            // Si es un SELECT (devuelve un conjunto de resultados)
            if ($resultado->num_rows > 0) {
                // Dibujamos una tabla HTML para mostrar los datos
                echo "<table border='1' cellpadding='8' style='border-collapse: collapse; min-width: 600px; text-align: left;'>";
                
                // 1. Mostrar los nombres de las columnas
                $columnas = $resultado->fetch_fields();
                echo "<tr style='background-color: #f0f0f0;'>";
                foreach ($columnas as $col) {
                    echo "<th>" . htmlspecialchars($col->name) . "</th>";
                }
                echo "</tr>";

                // 2. Recorrer y mostrar cada fila de la base de datos
                while ($fila = $resultado->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($fila as $valor) {
                        echo "<td>" . htmlspecialchars((string)$valor) . "</td>";
                    }
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p>No se encontraron registros para esta consulta.</p>";
            }
        }
    }

    // Cerramos la conexión al final de todo
    $conn->close();
    ?>
</body>

</html>