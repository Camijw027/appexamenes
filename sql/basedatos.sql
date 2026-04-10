CREATE DATABASE IF NOT EXISTS base_datos;
USE base_datos;
CREATE TABLE alumnos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    direccion VARCHAR(50),
    genero CHAR(1),
    fecha_nacimiento DATE NOT NULL
);

CREATE TABLE examenes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(50) NOT NULL,
    fecha_creacion DATE NOT NULL,
    materia VARCHAR(50) NOT NULL
);

CREATE TABLE preguntas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    examen_id INT NOT NULL,
    pregunta VARCHAR(100) NOT NULL,
    opcion_a VARCHAR(100) NOT NULL,
    opcion_b VARCHAR(100) NOT NULL,
    opcion_c VARCHAR(100) NOT NULL,
    respuesta_correcta VARCHAR(1) NOT NULL,
    FOREIGN KEY (examen_id) REFERENCES examenes(id) ON DELETE CASCADE
);