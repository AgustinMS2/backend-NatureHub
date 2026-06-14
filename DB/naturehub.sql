CREATE DATABASE IF NOT EXISTS naturehub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE naturehub;

CREATE TABLE SECCION (
    id_seccion INT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT
);

INSERT INTO SECCION (id_seccion, nombre, descripcion) VALUES
(1, 'Mamíferos', 'Vertebrados de sangre caliente con pelo o pelaje y lactancia de sus crías'),
(2, 'Aves', 'Vertebrados con plumas, bípedos, generalmente alados y de sangre caliente'),
(3, 'Reptiles', 'Vertebrados ectotérmicos con escamas o placas óseas en la piel')
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion);

CREATE TABLE USUARIO (
    id_usuario INT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    activo BOOLEAN NOT NULL,
    fecha_registro TIMESTAMP NOT NULL
);

CREATE TABLE PERFIL (
    id_usuario INT PRIMARY KEY,
    bio TEXT,
    foto_url VARCHAR(500),
    sexo VARCHAR(20),
    fecha_nacimiento DATE,
    pais VARCHAR(100),
    FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario)
);

CREATE TABLE MODERADOR (
    id_usuario INT PRIMARY KEY,
    FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario)
);

CREATE TABLE ADMINISTRADOR (
    id_usuario INT PRIMARY KEY,
    FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario)
);

CREATE TABLE SESION (
    id_sesion INT PRIMARY KEY,
    id_usuario INT NOT NULL,
    token VARCHAR(512) NOT NULL UNIQUE,
    fecha_inicio TIMESTAMP NOT NULL,
    fecha_fin TIMESTAMP NULL,
    activa BOOLEAN NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario)
);

CREATE TABLE PUBLICACION (
    id_publicacion INT PRIMARY KEY,
    id_seccion INT NOT NULL,
    id_autor INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    nombre_cientifico VARCHAR(200),
    foto_url VARCHAR(500),
    areas_habitat TEXT,
    dieta VARCHAR(200),
    horas_activas VARCHAR(100),
    estado ENUM('BORRADOR', 'PENDIENTE_REVISION', 'APROBADA', 'RECHAZADA', 'PUBLICADA') NOT NULL,
    fecha_creacion TIMESTAMP NOT NULL,
    fecha_modificacion TIMESTAMP NOT NULL,
    FOREIGN KEY (id_seccion) REFERENCES SECCION(id_seccion),
    FOREIGN KEY (id_autor) REFERENCES USUARIO(id_usuario)
);

CREATE TABLE CAMPO_EXTRA (
    id_campo INT PRIMARY KEY,
    id_publicacion INT NOT NULL,
    etiqueta VARCHAR(100) NOT NULL,
    valor TEXT,
    tipo ENUM('TEXTO', 'BOOLEANO', 'NUMERICO', 'FECHA') NOT NULL,
    FOREIGN KEY (id_publicacion) REFERENCES PUBLICACION(id_publicacion)
);

CREATE TABLE MODERA (
    id_modera INT PRIMARY KEY,
    id_publicacion INT NOT NULL,
    id_moderador INT NOT NULL,
    resultado ENUM('APROBADA', 'RECHAZADA') NOT NULL,
    motivo_rechazo TEXT,
    fecha_revision TIMESTAMP NOT NULL,
    FOREIGN KEY (id_publicacion) REFERENCES PUBLICACION(id_publicacion),
    FOREIGN KEY (id_moderador) REFERENCES MODERADOR(id_usuario)
);

CREATE TABLE REPORTE (
    id_reporte INT PRIMARY KEY,
    id_publicacion INT NOT NULL,
    id_usuario INT NOT NULL,
    motivo TEXT NOT NULL,
    fecha TIMESTAMP NOT NULL,
    resuelto BOOLEAN NOT NULL,
    FOREIGN KEY (id_publicacion) REFERENCES PUBLICACION(id_publicacion),
    FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario)
);
