<?php
// Archivo para inicializar datos básicos en la base de datos

include_once __DIR__ . "/persistencia/Conectar.php";

try {
    $conexion = new Conectar();
    
    // Insertar secciones si no existen
    $secciones = [
        [1, 'Mamíferos', 'Vertebrados de sangre caliente con pelo o pelaje y lactancia de sus crías'],
        [2, 'Aves', 'Vertebrados con plumas, bípedos, generalmente alados y de sangre caliente'],
        [3, 'Reptiles', 'Vertebrados ectotérmicos con escamas o placas óseas en la piel']
    ];
    
    foreach ($secciones as $seccion) {
        $stmt = $conexion->prepare("INSERT IGNORE INTO SECCION (id_seccion, nombre, descripcion) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $seccion[0], $seccion[1], $seccion[2]);
        $stmt->execute();
        $stmt->close();
    }
    
    // Insertar usuarios de prueba si no existen
    $usuarios = [
        [1, 'Juan', 'Pérez', 'juan@example.com', 'password_hash'],
        [2, 'María', 'García', 'maria@example.com', 'password_hash']
    ];
    
    foreach ($usuarios as $usuario) {
        $stmt = $conexion->prepare("INSERT IGNORE INTO USUARIO (id_usuario, nombre, apellido, email, password_hash, activo, fecha_registro) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $stmt->bind_param("issss", $usuario[0], $usuario[1], $usuario[2], $usuario[3], $usuario[4]);
        $stmt->execute();
        $stmt->close();
    }

    $conexion->query("CREATE TABLE IF NOT EXISTS BORRADOR (
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
    )");
    
    $conexion->close();
    
} catch (Exception $e) {
    // Silenciar errores en inicialización
}
?>
