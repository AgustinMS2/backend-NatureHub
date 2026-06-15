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


        try {
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
            null,
            null,
            null,
            $datos->sexo ?? null,
            $datos->fechaNacimiento ?? null,
            $datos->pais ?? null,
            $datos->bio ?? null,
            $fotoUrl
        );

        try {
            $this->controlador->modificarUsuario($dtu);
            http_response_code(200);
            echo json_encode(["mensaje" => "Usuario modificado correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/usuarios/listarUsuarios
    public function listarUsuarios(): void {
        $usuarios = $this->controlador->listarUsuarios();
        
        $resultado = [];
        foreach ($usuarios as $dtu) {
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
                "fotoUrl" => $dtu->getFotoUrl()
            ];
        }
        
        http_response_code(200);
        echo json_encode($resultado);
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

    // http://localhost/backend-NatureHub/src/index.php/usuarios/altaModerador
    public function altaModerador(): void {
        $datos = json_decode(file_get_contents("php://input"));

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
            $datos->fotoUrl ?? null
        );

        try {
            $this->controlador->altaModerador($dtu);
            http_response_code(201);
            echo json_encode(["mensaje" => "Moderador creado correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}
?>