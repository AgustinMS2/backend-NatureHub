USE naturehub;

CREATE TABLE IF NOT EXISTS BORRADOR (
    id_borrador INT PRIMARY KEY,
    id_autor INT NOT NULL UNIQUE,
    id_seccion INT NULL,
    titulo VARCHAR(200) NULL,
    nombre_cientifico VARCHAR(200) NULL,
    foto_url VARCHAR(500) NULL,
    areas_habitat TEXT NULL,
    dieta VARCHAR(200) NULL,
    horas_activas VARCHAR(100) NULL,
    campos_extra TEXT NULL,
    fecha_modificacion TIMESTAMP NOT NULL,
    FOREIGN KEY (id_autor) REFERENCES USUARIO(id_usuario),
    FOREIGN KEY (id_seccion) REFERENCES SECCION(id_seccion)
);
