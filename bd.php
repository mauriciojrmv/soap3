<?php
class DatabaseService {
    private $pdo;

    public function __construct() {
        try {
            $dsn = 'mysql:host=localhost;dbname=person_db';
            $username = 'root';
            $password = '';
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new SoapFault("Server", "Nivel 3: Error - No se pudo conectar a la base de datos: " . $e->getMessage());
        }
    }

    public function checkIfCarnetExists($numero_carnet) {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM personas WHERE numero_carnet = :numero_carnet");
            $stmt->execute(['numero_carnet' => $numero_carnet]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new SoapFault("Server", "Nivel 3: Error - Problema al verificar la existencia del carnet: " . $e->getMessage());
        }
    }

    public function checkIfPersonExists($token) {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM personas WHERE token = :token");
            $stmt->execute(['token' => $token]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new SoapFault("Server", "Nivel 3: Error - Problema al verificar la existencia de la persona: " . $e->getMessage());
        }
    }

    public function getPersonInfo($token) {
        try {
            $stmt = $this->pdo->prepare("SELECT nombre, apellido_paterno, apellido_materno, numero_carnet FROM personas WHERE token = :token");
            $stmt->execute(['token' => $token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new SoapFault("Server", "Nivel 3: Error - Problema al obtener la información de la persona: " . $e->getMessage());
        }
    }

    public function insertPerson($nombre, $apellido_paterno, $apellido_materno, $numero_carnet, $fecha_nacimiento, $sexo, $lugar_nacimiento, $estado_civil, $profesion, $domicilio, $token) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO personas 
                (nombre, apellido_paterno, apellido_materno, numero_carnet, fecha_nacimiento, sexo, lugar_nacimiento, estado_civil, profesion, domicilio, token) 
                VALUES 
                (:nombre, :apellido_paterno, :apellido_materno, :numero_carnet, :fecha_nacimiento, :sexo, :lugar_nacimiento, :estado_civil, :profesion, :domicilio, :token)
            ");
            $result = $stmt->execute([
                'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'numero_carnet' => $numero_carnet,
                'fecha_nacimiento' => $fecha_nacimiento,
                'sexo' => $sexo,
                'lugar_nacimiento' => $lugar_nacimiento,
                'estado_civil' => $estado_civil,
                'profesion' => $profesion,
                'domicilio' => $domicilio,
                'token' => $token
            ]);
            return $result;
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // Código para violaciones de integridad
                throw new SoapFault("Client", "Nivel 2: Error - Este registro ya existe en el sistema.");
            } else {
                throw new SoapFault("Server", "Nivel 3: Error - Problema al insertar la persona en la base de datos: " . $e->getMessage());
            }
        }
    }
}

// Configuración del servidor SOAP en PC3
$server = new SoapServer(null, ['uri' => "urn:DatabaseService"]);
$server->setClass('DatabaseService');
$server->handle();
?>
