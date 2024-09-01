<?php
$message = '';
$message_type = '';

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

            // Registro exitoso
            $message = $response;
            $message_type = 'success';

        } catch (SoapFault $e) {
            // Nivel 1: Error al intentar conectarse al servidor SOAP
            if (strpos($e->getMessage(), 'Could not connect to host') !== false) {
                $message = "Nivel 1: Error - No se pudo conectar al servidor. Por favor, verifique su conexión.";
            } else {
                // Nivel 2: Error en el servidor SOAP (manejo de la solicitud)
                $message = "Nivel 2: Error - No se pudo realizar la tarea en el servidor. Detalle: " . $e->getMessage();
            }
            $message_type = 'error';
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
    <style>
        .message {
            padding: 10px;
            margin-bottom: 15px;
        }
        .error {
            color: red;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .success {
            color: green;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
    </style>
</head>
<body>
    <h1>Registrar Persona</h1>

    <!-- Área de notificaciones para mostrar errores o mensajes de éxito -->
    <?php if ($message): ?>
        <div class="message <?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <label for="name">Nombre:</label>
        <input type="text" id="name" name="name" required><br><br>
        
        <label for="paternal_surname">Apellido Paterno:</label>
        <input type="text" id="paternal_surname" name="paternal_surname" required><br><br>
        
        <label for="maternal_surname">Apellido Materno:</label>
        <input type="text" id="maternal_surname" name="maternal_surname" required><br><br>
        
        <label for="id_number">Número de Carnet:</label>
        <input type="text" id="id_number" name="id_number" pattern="\d+" title="Solo se permiten números" required><br><br>
        
        <label for="birth_date">Fecha de Nacimiento:</label>
        <input type="date" id="birth_date" name="birth_date" required><br><br>
        
        <label for="gender">Sexo:</label>
        <select id="gender" name="gender" required>
            <option value="M">Masculino</option>
            <option value="F">Femenino</option>
        </select><br><br>
        
        <label for="birth_place">Lugar de Nacimiento:</label>
        <input type="text" id="birth_place" name="birth_place" required><br><br>
        
        <label for="marital_status">Estado Civil:</label>
        <select id="marital_status" name="marital_status" required>
            <option value="S">Soltero</option>
            <option value="C">Casado</option>
            <option value="D">Divorciado</option>
            <option value="V">Viudo</option>
        </select><br><br>
        
        <label for="profession">Profesión:</label>
        <input type="text" id="profession" name="profession" required><br><br>
        
        <label for="address">Domicilio:</label>
        <input type="text" id="address" name="address" required><br><br>
        
        <button type="submit">Registrar</button>
    </form>
</body>
</html>
