pc1 debe tener el index.php.,
 recomiendo puerto 8000 y asignar la ip concedida por la red..debemos dirigir a la ip del server con su puerto;
pc2 debe tener server.php corriendo en xampp recomiendo puerto 8000 y asignar la ip concedida por la red. debemos dirigir a la ip de la bd con la configuracion de red de esta otra. debemos dar la ip a cliente para que se conecte.;
pc3 debe tener la bd corriendo en xampp recomiendo puerto 8000 y asignar la ip concedida por la red. debemos dar la ip a server para que se conecte;


cada xampp se debe configurar con la ip asignada por la red y su puerto. esto en config>http.conf

adicionalmente debemos crear una base de datos en mysql para este soap server:;

CREATE DATABASE person_db;

USE person_db;

CREATE TABLE personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    apellido_paterno VARCHAR(255) NOT NULL,
    apellido_materno VARCHAR(255) NOT NULL,
    numero_carnet VARCHAR(255) NOT NULL UNIQUE,
    fecha_nacimiento DATE NOT NULL,
    sexo CHAR(1) NOT NULL,
    lugar_nacimiento VARCHAR(255) NOT NULL,
    estado_civil CHAR(1) NOT NULL,
    profesion VARCHAR(255) NOT NULL,
    domicilio VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
