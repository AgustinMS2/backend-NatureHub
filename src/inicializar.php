<?php
// http://localhost/backend-NatureHub/src/inicializar.php
include_once __DIR__ . "/persistencia/Conectar.php";

try {
    $conexion = new Conectar();
    $passwordHash = password_hash('123456', PASSWORD_DEFAULT);

    $secciones = [
        [1, 'Mamíferos', 'Vertebrados de sangre caliente con pelo o pelaje y lactancia de sus crías'],
        [2, 'Aves', 'Vertebrados con plumas, bípedos, generalmente alados y de sangre caliente'],
        [3, 'Reptiles', 'Vertebrados ectotérmicos con escamas o placas óseas en la piel'],
        [4, 'Anfibios', 'Vertebrados de vida acuática y terrestre, piel húmeda y metamorfosis'],
        [5, 'Peces', 'Vertebrados acuáticos con branquias, aletas y cuerpo cubierto de escamas'],
        [6, 'Insectos', 'Artrópodos de seis patas, cuerpo segmentado y alto grado de diversidad'],
        [7, 'Arácnidos', 'Artrópodos de ocho patas, sin antenas, como arañas, escorpiones y ácaros.'],
        [8, 'Invertebrados Acuáticos', 'Moluscos, crustáceos y otros organismos sin columna vertebral que habitan ambientes acuáticos.'],
        [9, 'Flora Nativa', 'Plantas, árboles y arbustos autóctonos que forman la base de los ecosistemas.'],
        [10, 'Hongos', 'Organismos del reino Fungi, incluyendo setas y líquenes, esenciales para el ecosistema.']
    ];

    foreach ($secciones as $seccion) {
        $stmt = $conexion->prepare("INSERT IGNORE INTO SECCION (id_seccion, nombre, descripcion) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $seccion[0], $seccion[1], $seccion[2]);
        $stmt->execute();
        $stmt->close();
    }

    $usuarios = [
        [1, 'Carlos', 'Mendoza', 'usuario@naturehub.com'],
        [2, 'Laura', 'Fernandez', 'moderador@naturehub.com'],
        [3, 'Gualberto', 'Brito', 'administrador@naturehub.com'],
    ];

    foreach ($usuarios as $usuario) {
        $stmt = $conexion->prepare(
            "INSERT IGNORE INTO USUARIO (id_usuario, nombre, apellido, email, password_hash, activo, fecha_registro)
             VALUES (?, ?, ?, ?, ?, 1, NOW())"
        );
        $stmt->bind_param("issss", $usuario[0], $usuario[1], $usuario[2], $usuario[3], $passwordHash);
        $stmt->execute();
        $stmt->close();
    }

    $perfiles = [
        [1, 'Naturalista aficionado.', 'https://picsum.photos/seed/carlos-nh/200/200', 'Masculino', '1998-04-12', 'Colombia'],
        [2, 'Bióloga y moderadora de contenido en NatureHub.', 'https://picsum.photos/seed/laura-nh/200/200', 'Femenino', '1992-09-03', 'Argentina'],
        [3, 'Administrador del sistema.', 'https://picsum.photos/seed/admin-nh/200/200', 'Masculino', '1988-11-25', 'Uruguay'],
    ];

    foreach ($perfiles as $perfil) {
        $stmt = $conexion->prepare(
            "INSERT IGNORE INTO PERFIL (id_usuario, bio, foto_url, sexo, fecha_nacimiento, pais) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("isssss", $perfil[0], $perfil[1], $perfil[2], $perfil[3], $perfil[4], $perfil[5]);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conexion->prepare("INSERT IGNORE INTO MODERADOR (id_usuario) VALUES (?)");
    foreach ([2, 3] as $idModerador) {
        $stmt->bind_param("i", $idModerador);
        $stmt->execute();
    }
    $stmt->close();

    $stmt = $conexion->prepare("INSERT IGNORE INTO ADMINISTRADOR (id_usuario) VALUES (?)");
    $idAdmin = 3;
    $stmt->bind_param("i", $idAdmin);
    $stmt->execute();
    $stmt->close();

    $publicaciones = [
        [1, 1, 1, 'Zorro Pampeano', 'Lycalopex gymnocercus', 'https://picsum.photos/seed/fox-pampa/600/400', '["Pampas","Pastizales del sur de Sudamérica"]', 'Omnívoro', 'Crepuscular y nocturno', 'PENDIENTE_REVISION'],
        [2, 2, 1, 'Chingolo', 'Zonotrichia capensis', 'https://picsum.photos/seed/chingolo/600/400', '["Parques urbanos","Bosques templados"]', 'Omnívoro', 'Diurno', 'PENDIENTE_REVISION'],
        [3, 3, 1, 'Lagarto Overo', 'Salvator merianae', 'https://picsum.photos/seed/lizard-tegu/600/400', '["Bosques","Márgenes de ríos"]', 'Omnívoro', 'Diurno', 'APROBADA'],
        [4, 4, 1, 'Sapito Común', 'Physalaemus biligonigerus', 'https://picsum.photos/seed/sapito/600/400', '["Humedales","Pastizales"]', 'Insectívoro', 'Nocturno', 'APROBADA'],
        [5, 5, 1, 'Pejerrey', 'Odontesthes bonariensis', 'https://picsum.photos/seed/pejerrey/600/400', '["Ríos","Lagunas"]', 'Carnívoro', 'Diurno', 'RECHAZADA'],
        [6, 6, 1, 'Mariposa Monarca', 'Danaus plexippus', 'https://picsum.photos/seed/monarca/600/400', '["Campos con flores","Jardines"]', 'Herbívoro (larva)', 'Diurno', 'RECHAZADA'],
    ];

    foreach ($publicaciones as $pub) {
        $stmt = $conexion->prepare(
            "INSERT IGNORE INTO PUBLICACION
             (id_publicacion, id_seccion, id_autor, titulo, nombre_cientifico, foto_url, areas_habitat, dieta, horas_activas, estado, fecha_creacion, fecha_modificacion, activo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 1)"
        );
        $stmt->bind_param("iiisssssss", $pub[0], $pub[1], $pub[2], $pub[3], $pub[4], $pub[5], $pub[6], $pub[7], $pub[8], $pub[9]);
        $stmt->execute();
        $stmt->close();
    }

    $moderaciones = [
        [1, 3, 2, 'APROBADA', null],
        [2, 4, 2, 'APROBADA', null],
        [3, 5, 2, 'RECHAZADA', 'La información sobre hábitat es insuficiente y carece de fuentes verificables.'],
        [4, 6, 2, 'RECHAZADA', 'El nombre científico no coincide con la descripción proporcionada.'],
    ];

    foreach ($moderaciones as $mod) {
        $stmt = $conexion->prepare(
            "INSERT IGNORE INTO MODERA (id_modera, id_publicacion, id_moderador, resultado, motivo_rechazo, fecha_revision)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );
        $stmt->bind_param("iiiss", $mod[0], $mod[1], $mod[2], $mod[3], $mod[4]);
        $stmt->execute();
        $stmt->close();
    }

    $camposExtra = [
        [3, 'Longitud máxima', '1.5 metros', 'TEXTO'],
        [4, 'Estado de conservación', 'Preocupación menor', 'TEXTO'],
    ];

    foreach ($camposExtra as $campo) {
        $check = $conexion->prepare("SELECT 1 FROM CAMPO_EXTRA WHERE id_publicacion = ? AND etiqueta = ? LIMIT 1");
        $check->bind_param("is", $campo[0], $campo[1]);
        $check->execute();
        $existe = $check->get_result()->fetch_assoc();
        $check->close();

        if ($existe) {
            continue;
        }

        $stmt = $conexion->prepare(
            "INSERT INTO CAMPO_EXTRA (id_publicacion, etiqueta, valor, tipo) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("isss", $campo[0], $campo[1], $campo[2], $campo[3]);
        $stmt->execute();
        $stmt->close();
    }

    $borradores = [
        [1, 1, 1, 'Carpincho', 'Hydrochoerus hydrochaeris', 'https://picsum.photos/seed/carpincho/600/400', 'Bañados y orillas de ríos', 'Herbívoro', 'Crepuscular', '[{"etiqueta":"Peso promedio","valor":"50 kg","tipo":"TEXTO"}]'],
        [2, 3, 2, 'Hornero', 'Furnarius rufus', 'https://picsum.photos/seed/hornero/600/400', 'Campos abiertos y zonas urbanas', 'Omnívoro', 'Diurno', '[{"etiqueta":"Ave nacional","valor":"Argentina y Uruguay","tipo":"TEXTO"}]'],
    ];

    foreach ($borradores as $borrador) {
        $stmt = $conexion->prepare(
            "INSERT IGNORE INTO BORRADOR
             (id_borrador, id_autor, id_seccion, titulo, nombre_cientifico, foto_url, areas_habitat, dieta, horas_activas, campos_extra, fecha_modificacion)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->bind_param("iiisssssss", $borrador[0], $borrador[1], $borrador[2], $borrador[3], $borrador[4], $borrador[5], $borrador[6], $borrador[7], $borrador[8], $borrador[9]);
        $stmt->execute();
        $stmt->close();
    }

    $conexion->close();

} catch (Exception $e) {

}
?>
