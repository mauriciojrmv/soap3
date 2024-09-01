<?php
class PersonService {
    private $dbServerUrl;

    public function __construct() {
        $this->dbServerUrl = "http://localhost:8000/soap3/bd.php";
    }

    public function registerPerson($nombre, $apellido_paterno, $apellido_materno, $numero_carnet, $fecha_nacimiento, $sexo, $lugar_nacimiento, $estado_civil, $profesion, $domicilio, $token) {
        try {
            // Verificar si el token ya está registrado (persona con estos mismos datos)
            $tokenExists = $this->remoteCall('checkIfPersonExists', [$token]);

            if ($tokenExists) {
                // Obtener los datos importantes de la persona ya registrada con el mismo token
                $personInfo = $this->remoteCall('getPersonInfo', [$token]);

                throw new SoapFault("Client", "Nivel 2: Error - Este registro ya existe. Nombre: " . $personInfo['nombre'] . ", Apellido Paterno: " . $personInfo['apellido_paterno'] . ", Apellido Materno: " . $personInfo['apellido_materno'] . ", Carnet: " . $personInfo['numero_carnet'] . ".");
            }

            // Si el token no existe, verificar si el carnet ya está registrado con otra persona
            $carnetExists = $this->remoteCall('checkIfCarnetExists', [$numero_carnet]);

            if ($carnetExists) {
                // Si el carnet ya está registrado, lanzar un error de nivel 2 específico
                throw new SoapFault("Client", "Nivel 2: Error - Este carnet ya está registrado con otra persona.");
            }

            // Intentar insertar la persona en la base de datos
            $result = $this->remoteCall('insertPerson', [
                $nombre, $apellido_paterno, $apellido_materno, $numero_carnet, $fecha_nacimiento, $sexo,
                $lugar_nacimiento, $estado_civil, $profesion, $domicilio, $token
            ]);

            if ($result !== true) {
                throw new Exception("Error al insertar la persona en la base de datos.");
            }

            return "Registro exitoso de: $nombre $apellido_paterno $apellido_materno - $numero_carnet";

        } catch (SoapFault $e) {
            if (strpos($e->getMessage(), 'Nivel 2:') !== false || strpos($e->getMessage(), 'Nivel 3:') !== false) {
                throw $e; // No encapsular de nuevo los errores específicos
            } else {
                throw new SoapFault("Server", "Nivel 2: Error - Ocurrió un problema en el servidor al procesar la solicitud.");
            }
        } catch (Exception $e) {
            throw new SoapFault("Server", "Nivel 2: Error - " . $e->getMessage());
        }
    }

    private function remoteCall($method, $params) {
        try {
            $client = new SoapClient(null, [
                'location' => $this->dbServerUrl,
                'uri' => "urn:DatabaseService"
            ]);
            return $client->__soapCall($method, $params);
        } catch (SoapFault $e) {
            throw new SoapFault("Server", "Nivel 3: Error - Problema al acceder a la base de datos.");
        }
    }
}

// Configuración del servidor SOAP
$server = new SoapServer(null, ['uri' => "urn:PersonService"]);
$server->setClass('PersonService');
$server->handle();
?>
