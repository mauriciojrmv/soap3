<?php
$message = '';
$message_type = '';
$clear_form = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fields = ['name', 'paternal_surname', 'maternal_surname', 'id_number', 'birth_date', 'gender', 'birth_place', 'marital_status', 'profession', 'address'];
    
    foreach ($fields as $field) {
        if (empty($_POST[$field])) {
            $message = "Error: Todos los campos son obligatorios.";
            $message_type = 'error';
            break;
        }
    }

    if (empty($message)) {
        $nombre = htmlspecialchars($_POST['name']);
        $apellido_paterno = htmlspecialchars($_POST['paternal_surname']);
        $apellido_materno = htmlspecialchars($_POST['maternal_surname']);
        $numero_carnet = htmlspecialchars($_POST['id_number']);
        $fecha_nacimiento = htmlspecialchars($_POST['birth_date']);
        $sexo = htmlspecialchars($_POST['gender']);
        $lugar_nacimiento = htmlspecialchars($_POST['birth_place']);
        $estado_civil = htmlspecialchars($_POST['marital_status']);
        $profesion = htmlspecialchars($_POST['profession']);
        $domicilio = htmlspecialchars($_POST['address']);

        $token = md5($nombre . $apellido_paterno . $apellido_materno . $numero_carnet);

        try {
            $client = new SoapClient(null, [
                'location' => "http://localhost:8000/soap3/server.php",
                'uri' => "urn:PersonService",
                'trace' => 1
            ]);

            $response = $client->registerPerson(
                $nombre, 
                $apellido_paterno, 
                $apellido_materno, 
                $numero_carnet, 
                $fecha_nacimiento, 
                $sexo, 
                $lugar_nacimiento, 
                $estado_civil, 
                $profesion, 
                $domicilio, 
                $token
            );

            // Registro exitoso, limpiar formulario
            $message = $response;
            $message_type = 'success';
            $clear_form = true;

        } catch (SoapFault $e) {
            // Nivel 1: Error al intentar conectarse al servidor SOAP
            if (strpos($e->getMessage(), 'Could not connect to host') !== false) {
                $message = "Nivel 1: Error - No se pudo conectar al servidor. Intentando nuevamente en 10 segundos...";
                $message_type = 'error';
                // Ejecutar el script de reintento en caso de fallo de conexión
                echo "<script>
                    setTimeout(function() {
                        document.forms[0].submit();
                    }, 10000);
                </script>";
            } else {
                // Nivel 2: Error en el servidor SOAP (manejo de la solicitud)
                $message = "Nivel 2: Error - No se pudo realizar la tarea en el servidor. Detalle: " . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Persona</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container" id="container">
        <div class="left-side" id="left-side">
            <h2>Bienvenido</h2>
            <p>Registra tus datos para acceder a todos los beneficios.</p>
            <button onclick="toggleAnimation()">Iniciar</button>
        </div>
        <div class="right-side hidden" id="right-side">
            <div class="form-container visible" id="form-container">
                <h1>Registrar Persona</h1>

                <!-- Área de notificaciones para mostrar errores o mensajes de éxito -->
                <?php if ($message): ?>
                    <div class="message <?= $message_type ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form action="index.php" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nombre:</label>
                            <input type="text" id="name" name="name" value="<?= $clear_form ? '' : htmlspecialchars($_POST['name'] ?? '') ?>" required>

                            <label for="paternal_surname">Apellido Paterno:</label>
                            <input type="text" id="paternal_surname" name="paternal_surname" value="<?= $clear_form ? '' : htmlspecialchars($_POST['paternal_surname'] ?? '') ?>" required>

                            <label for="maternal_surname">Apellido Materno:</label>
                            <input type="text" id="maternal_surname" name="maternal_surname" value="<?= $clear_form ? '' : htmlspecialchars($_POST['maternal_surname'] ?? '') ?>" required>

                            <label for="id_number">Número de Carnet:</label>
                            <input type="text" id="id_number" name="id_number" pattern="\d+" title="Solo se permiten números" value="<?= $clear_form ? '' : htmlspecialchars($_POST['id_number'] ?? '') ?>" required>

                            <label for="birth_date">Fecha de Nacimiento:</label>
                            <input type="date" id="birth_date" name="birth_date" value="<?= $clear_form ? '' : htmlspecialchars($_POST['birth_date'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="gender">Sexo:</label>
                            <select id="gender" name="gender" required>
                                <option value="M" <?= isset($_POST['gender']) && $_POST['gender'] === 'M' ? 'selected' : '' ?>>Masculino</option>
                                <option value="F" <?= isset($_POST['gender']) && $_POST['gender'] === 'F' ? 'selected' : '' ?>>Femenino</option>
                            </select>

                            <label for="birth_place">Lugar de Nacimiento:</label>
                            <input type="text" id="birth_place" name="birth_place" value="<?= $clear_form ? '' : htmlspecialchars($_POST['birth_place'] ?? '') ?>" required>

                            <label for="marital_status">Estado Civil:</label>
                            <select id="marital_status" name="marital_status" required>
                                <option value="S" <?= isset($_POST['marital_status']) && $_POST['marital_status'] === 'S' ? 'selected' : '' ?>>Soltero</option>
                                <option value="C" <?= isset($_POST['marital_status']) && $_POST['marital_status'] === 'C' ? 'selected' : '' ?>>Casado</option>
                                <option value="D" <?= isset($_POST['marital_status']) && $_POST['marital_status'] === 'D' ? 'selected' : '' ?>>Divorciado</option>
                                <option value="V" <?= isset($_POST['marital_status']) && $_POST['marital_status'] === 'V' ? 'selected' : '' ?>>Viudo</option>
                            </select>

                            <label for="profession">Profesión:</label>
                            <input type="text" id="profession" name="profession" value="<?= $clear_form ? '' : htmlspecialchars($_POST['profession'] ?? '') ?>" required>

                            <label for="address">Domicilio:</label>
                            <input type="text" id="address" name="address" value="<?= $clear_form ? '' : htmlspecialchars($_POST['address'] ?? '') ?>" required>
                        </div>
                    </div>

                    <button type="submit">Registrar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleAnimation() {
            const leftSide = document.getElementById('left-side');
            const rightSide = document.getElementById('right-side');
            const formContainer = document.getElementById('form-container');
            const container = document.getElementById('container');

            leftSide.classList.toggle('expanded');
            rightSide.classList.toggle('visible');
            formContainer.classList.toggle('hidden');
            container.classList.toggle('moved');
        }
    </script>
</body>

</html>