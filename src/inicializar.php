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
        [1, 'Carlos', 'Mendoza', 'usuario1@naturehub.com'],
        [2, 'Laura', 'Fernandez', 'moderador@naturehub.com'],
        [3, 'Gualberto', 'Brito', 'administrador@naturehub.com'],
        [4, 'Mariana', 'Silva', 'usuario2@naturehub.com'],
        [5, 'Esteban', 'Quito', 'usuario3@naturehub.com'],
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
        [4, 'Fotógrafa de naturaleza y entusiasta del ecoturismo.', 'https://picsum.photos/seed/mariana-nh/200/200', 'Femenino', '1995-07-19', 'Uruguay'],
        [5, 'Estudiante de ciencias ambientales.', 'https://picsum.photos/seed/esteban-nh/200/200', 'Masculino', '2001-02-02', 'Argentina'],
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
        [7, 1, 4, 'Carpincho', 'Hydrochoerus hydrochaeris', 'https://picsum.photos/seed/carpincho-pub/600/400', '["Humedales","Orillas de ríos"]', 'Herbívoro', 'Crepuscular', 'APROBADA'],
        [8, 2, 5, 'Hornero', 'Furnarius rufus', 'https://picsum.photos/seed/hornero-pub/600/400', '["Campos abiertos","Zonas urbanas"]', 'Insectívoro', 'Diurno', 'APROBADA'],
        [9, 7, 4, 'Araña Lobo', 'Lycosa erythrognatha', 'https://picsum.photos/seed/spider-wolf/600/400', '["Jardines","Praderas"]', 'Insectívoro', 'Nocturno', 'APROBADA'],
        [10, 8, 5, 'Cangrejo de Río', 'Aegla uruguayana', 'https://picsum.photos/seed/crab-river/600/400', '["Arroyos con fondo de piedra","Ríos"]', 'Detritívoro', 'Nocturno', 'APROBADA'],
        [11, 9, 1, 'Ceibo', 'Erythrina crista-galli', 'https://picsum.photos/seed/ceibo-tree/600/400', '["Zonas inundables","Riberas"]', 'Autótrofo', 'Diurno', 'APROBADA'],
        [12, 1, 4, 'Mano Pelada', 'Procyon cancrivorus', 'https://picsum.photos/seed/raccoon-crab/600/400', '["Monte nativo","Riberas","Arroyos"]', 'Omnívoro', 'Nocturno', 'APROBADA'],
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
        [5, 5, 2, 'APROBADA', null],
        [6, 6, 2, 'APROBADA', null],
        [7, 7, 2, 'APROBADA', null],
        [8, 8, 2, 'APROBADA', null],
        [9, 9, 2, 'APROBADA', null],
        [10, 10, 2, 'APROBADA', null],
        [11, 11, 2, 'APROBADA', null],
        [12, 12, 2, 'APROBADA', null],
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
        [11, 'Flor Nacional', 'Uruguay y Argentina', 'TEXTO'],
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

    $favoritas = [
        [1, 3], 
        [1, 7], 
        [1, 11],
        [4, 1], 
        [4, 9], 
        [4, 12],
        [5, 3], 
        [5, 8], 
        [5, 10],
        [2, 4], 
        [2, 11],
    ];

    foreach ($favoritas as $fav) {
        $stmt = $conexion->prepare("INSERT IGNORE INTO PUBLICACIONES_FAVORITAS (id_usuario, id_publicacion) VALUES (?, ?)");
        $stmt->bind_param("ii", $fav[0], $fav[1]);
        $stmt->execute();
        $stmt->close();
    }

    $usuariosFavoritos = [
        [1, 2],
        [1, 4],
        [4, 1],
        [4, 2],
        [5, 3],
        [2, 3],
    ];

    foreach ($usuariosFavoritos as $ufav) {
        $stmt = $conexion->prepare("INSERT IGNORE INTO USUARIOS_FAVORITOS (id_usuario, id_usuario_favorito) VALUES (?, ?)");
        $stmt->bind_param("ii", $ufav[0], $ufav[1]);
        $stmt->execute();
        $stmt->close();
    }

    $reportes = [
        [7, 1, 'El contenido contiene información incorrecta sobre la dieta del animal.', false],
        [1, 4, 'La publicación contiene contenido inapropiado o spam.', false],
        [11, 5, 'La imagen no corresponde a la especie descrita.', true],
        [3, 2, 'Información duplicada con otra publicación existente.', true],
        [9, 5, 'El nombre científico parece estar mal escrito.', false],
    ];

    foreach ($reportes as $rep) {
        $check = $conexion->prepare("SELECT 1 FROM REPORTE WHERE id_publicacion = ? AND id_usuario = ? AND motivo = ? LIMIT 1");
        $check->bind_param("iis", $rep[0], $rep[1], $rep[2]);
        $check->execute();
        $existe = $check->get_result()->fetch_assoc();
        $check->close();

        if ($existe) {
            continue;
        }

        $stmt = $conexion->prepare("INSERT INTO REPORTE (id_publicacion, id_usuario, motivo, fecha, resuelto) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->bind_param("iisi", $rep[0], $rep[1], $rep[2], $rep[3]);
        $stmt->execute();
        $stmt->close();
    }

    $conexion->close();

} catch (Exception $e) {

}
?>