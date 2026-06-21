<?php
include_once __DIR__ . "/../../servicios/Fabrica.php";

class UsuarioEndpoint {
    private IUsuarioController $controlador;

    public function __construct() {
        $this->controlador = Fabrica::getInstance()->getIUsuarioController();
    }

    // http://localhost/backend-NatureHub/src/index.php/usuarios/altaUsuario
    public function altaUsuario(): void {
        $datos = (object) $_POST;
        $fotoUrl = $datos->fotoUrl ?? null;

        try{
            if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/usuarios/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $file = $_FILES['foto'];
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('profile_', true) . ($extension ? ".{$extension}" : '');
                $destination = $uploadDir . $filename;

                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    throw new Exception("No se pudo guardar la imagen de perfil.");
                }

                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                $fotoUrl = sprintf('%s://%s%s/uploads/usuarios/%s', $scheme, $host, $path, $filename);
            }
            
            $dtu = new DTUsuario(
                null,
                $datos->nombre,
                $datos->apellido,
                $datos->email,
                $datos->password,
                null,
                null,
                $datos->sexo ?? null,
                $datos->fechaNacimiento ?? null,
                $datos->pais ?? null,
                $datos->bio ?? null,
                $fotoUrl
            );

            $this->controlador->altaUsuario($dtu);
            http_response_code(201);
            echo json_encode(["mensaje" => "Usuario creado correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/usuarios/bajaUsuario
    public function bajaUsuario(): void {
        $dato = json_decode(file_get_contents("php://input"));

        $id = $dato->id;

        try {
            $this->controlador->bajaUsuario($id);
            http_response_code(200);
            echo json_encode(["mensaje" => "Usuario dado de baja correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/usuarios/modificarUsuario
    public function modificarUsuario(): void {
        $datos = (object) $_POST;
        $fotoUrl = $datos->fotoUrl ?? null;
        if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $file = $_FILES['foto'];
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('profile_', true) . ($extension ? ".{$extension}" : '');
            $destination = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new Exception("No se pudo guardar la imagen de perfil.");
            }

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            $fotoUrl = sprintf('%s://%s%s/uploads/%s', $scheme, $host, $path, $filename);
        }

        $dtu = new DTUsuario(
            $datos->id,
            $datos->nombre,
            $datos->apellido,
            $datos->email,
            $datos->password,
            null,
            null,
            $datos->sexo ?? null,
            $datos->fechaNacimiento ?? null,
            $datos->pais ?? null,
            $datos->bio ?? null,
            $fotoUrl
        );

        try {
            $nuevaPassword = $datos->nuevaPassword ?? null;
            $this->controlador->modificarUsuario($dtu, $nuevaPassword);
            http_response_code(200);
            echo json_encode(["mensaje" => "Usuario modificado correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/usuarios/listarUsuarios
    public function listarUsuarios(): void {
        try{
            $usuarios = $this->controlador->listarUsuarios();
            
            $resultado = [];
            foreach ($usuarios as $dtu) {
                $rol = match(true) {
                    $dtu instanceof DTAdministrador => "ADMINISTRADOR",
                    $dtu instanceof DTModerador => "MODERADOR",
                    default => "USUARIO"
                };

                $resultado[] = [
                    "id" => $dtu->getId(),
                    "nombre" => $dtu->getNombre(),
                    "apellido" => $dtu->getApellido(),
                    "email" => $dtu->getEmail(),
                    "activo" => $dtu->getActivo(),
                    "fechaRegistro" => $dtu->getFechaRegistro(),
                    "sexo" => $dtu->getSexo(),
                    "fechaNacimiento" => $dtu->getFechaNacimiento(),
                    "pais" => $dtu->getPais(),
                    "bio" => $dtu->getBio(),
                    "fotoUrl" => $dtu->getFotoUrl(),
                    "rol" => $rol
                ];
            }
            
            http_response_code(200);
            echo json_encode($resultado);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/usuarios/moderarUsuario
    public function moderarUsuario(): void {

    }

    // http://localhost/backend-NatureHub/src/index.php/usuarios/iniciarSesion
    public function iniciarSesion(): void {
        $datos = json_decode(file_get_contents("php://input"));

        $dtu1 = new DTUsuario(
            null, 
            null, 
            null,
            $datos->email,
            $datos->password,
            null, 
            null,
            null,
            null,
            null,
            null,
            null,
            null,
        );

        try {
            $resultado  = $this->controlador->iniciarSesion($dtu1);
            $dts = $resultado["sesion"];
            $dtu2 = $resultado["usuario"];

            $rol = match(true) {
                $dtu2 instanceof DTAdministrador => "ADMINISTRADOR",
                $dtu2 instanceof DTModerador => "MODERADOR",
                default => "USUARIO"
            };

            http_response_code(200);
            echo json_encode([
                "token" => $dts->getToken(),
                "activa" => $dts->getActiva(),
                "idusuario" => $dtu2->getId(),
                "nombre" => $dtu2->getNombre(),
                "apellido" => $dtu2->getApellido(),
                "email" => $dtu2->getEmail(),
                "fechaRegistro" => $dtu2->getFechaRegistro(),
                "sexo" => $dtu2->getSexo(),
                "fechaNacimiento" => $dtu2->getFechaNacimiento(),
                "pais" => $dtu2->getPais(),
                "bio" => $dtu2->getBio(),
                "fotoUrl" => $dtu2->getFotoUrl(),
                "rol" => $rol
            ]);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/usuarios/cerrarSesion
    public function cerrarSesion(): void {
        $datos = json_decode(file_get_contents("php://input"));

        try {
            $this->controlador->cerrarSesion($datos->token);
            http_response_code(200);
            echo json_encode(["mensaje" => "Sesión cerrada correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/usuarios/promoverUsuario
    public function promoverUsuario(): void {
        $dato = json_decode(file_get_contents("php://input"));

        try {
            $this->controlador->promoverUsuario($dato->id);
            http_response_code(200);
            echo json_encode(["mensaje" => "Usuario ascendido a moderador correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/usuarios/degradarModerador
    public function degradarModerador(): void {
        $dato = json_decode(file_get_contents("php://input"));

        try {
            $this->controlador->degradarModerador($dato->id);
            http_response_code(200);
            echo json_encode(["mensaje" => "Moderador degradado correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/usuarios/promoverModerador
    public function promoverModerador(): void {
        $dato = json_decode(file_get_contents("php://input"));

        try {
            $this->controlador->promoverModerador($dato->id);
            http_response_code(200);
            echo json_encode(["mensaje" => "Moderador ascendido a administrador correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/usuarios/degradarAdministrador
    public function degradarAdministrador(): void {
        $dato = json_decode(file_get_contents("php://input"));

        try {
            $this->controlador->degradarAdministrador($dato->id);
            http_response_code(200);
            echo json_encode(["mensaje" => "Administrador degradado correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-Naturehub/src/index.php/usuarios/agregarFavoritas
    public function agregarFavoritas(): void {
        $dato = json_decode(file_get_contents("php://input"));

        try{
            $this->controlador->agregarFavoritas($dato->idUsuario, $dato->idPublicacion);
            http_response_code(200);
            echo json_encode(["mensaje" => "Publicacion agregada a favoritas correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-Naturehub/src/index.php/usuarios/eliminarFavorita
    public function eliminarFavorita(): void {
        $dato = json_decode(file_get_contents("php://input"));

        try{
            $this->controlador->eliminarFavorita($dato->idUsuario, $dato->idPublicacion);
            http_response_code(200);
            echo json_encode(["mensaje" => "Publicacion eliminada de favoritas correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-Naturehub/src/index.php/usuarios/listarFavoritas
    public function listarFavoritas(): void {
        $dato = json_decode(file_get_contents("php://input"));

        try{
            $publicaciones = $this->controlador->listarFavoritas($dato->idUsuario, $dato->idPublicacion);

            foreach ($publicaciones as $dpu) {
                $resultado[] = [
                    "id" => $dpu->getId(),
                    "titulo" => $dpu->getTitulo(),
                    "foto" => $dpu->getFoto(),
                    "nombreCientifico" => $dpu->getNombreCientifico(),
                    "areasHabitat" => $dpu->getAreasHabitat(),
                    "dieta" => $dpu->getDieta(),
                    "horasActivas" => $dpu->getHorasActivas(),
                    "estado" => $dpu->getEstado(),
                    "fechaCreacion" => $dpu->getFechaCreacion(),
                    "fechaUltimaModificacion" => $dpu->getFechaUltimaModificacion(),
                    "autor" => $dpu->getAutor(),
                    "seccion" => $dpu->getSeccion()
                ];
            }

            http_response_code(200);
            echo json_encode([$resultado]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-Naturehub/src/index.php/usuarios/obtenerUsuarioId
    public function obtenerUsuarioId(): void {
        $datos = json_decode(file_get_contents("php://input"));
        $id = $datos->id;
        try{
            $dtu = $this->controlador->obtenerUsuarioId($id);

            $rol = match(true) {
                $dtu instanceof DTAdministrador => "ADMINISTRADOR",
                $dtu instanceof DTModerador => "MODERADOR",
                default => "USUARIO"
            };

            $resultado = [
                "id" => $dtu->getId(),
                "nombre" => $dtu->getNombre(),
                "apellido" => $dtu->getApellido(),
                "email" => $dtu->getEmail(),
                "activo" => $dtu->getActivo(),
                "fechaRegistro" => $dtu->getFechaRegistro(),
                "sexo" => $dtu->getSexo(),
                "fechaNacimiento" => $dtu->getFechaNacimiento(),
                "pais" => $dtu->getPais(),
                "bio" => $dtu->getBio(),
                "fotoUrl" => $dtu->getFotoUrl(),
                "rol" => $rol
            ];
            
            http_response_code(200);
            echo json_encode($resultado);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}

?>