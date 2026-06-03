<?php
include_once __DIR__ . "/../../servicios/Fabrica.php";

class UsuarioEndpoint {
    private IUsuarioController $controlador;

    public function __construct() {
        $this->controlador = Fabrica::getInstance()->getIUsuarioController();
    }

    // http://localhost/backend-NatureHub/src/index.php/usuarios/altaUsuario
    public function altaUsuario(): void {
    $datos = json_decode(file_get_contents("php://input"));
    
    $dtu = new DTUsuario(
        null,
        $datos->nombre,
        $datos->apellido,
        $datos->email,
        $datos->password,
        null,
        null
    );

    $this->controlador->altaUsuario($dtu);
    
    http_response_code(201);
    echo json_encode(["mensaje" => "Usuario creado correctamente"]);
}

    public function bajaUsuario(int $id): void {

    }

    public function modificarUsuario(): void {

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
            "fechaRegistro" => $dtu->getFechaRegistro()
        ];
    }
    
    http_response_code(200);
    echo json_encode($resultado);
    }

    public function moderarUsuario(): void {

    }

    // http://localhost/backend-NatureHub/src/index.php/usuarios/iniciarSesion
    public function iniciarSesion(): void {
        $datos = json_decode(file_get_contents("php://input"));

        $dtu = new DTUsuario(
            null, 
            null, 
            null,
            $datos->email,
            $datos->password,
            null, 
            null
        );

        try {
            $resultado  = $this->controlador->iniciarSesion($dtu);
            $dts = $resultado["sesion"];
            $dtu = $resultado["usuario"];

            http_response_code(200);
            echo json_encode([
                "token" => $dts->getToken(),
                "activa" => $dts->getActiva(),
                "idusuario" => $dtu->getId(),
                "nombre" => $dtu->getNombre(),
                "apellido" => $dtu->getApellido(),
                "email" => $dtu->getEmail(),
                "rol" => $dtu instanceof Administrador ? "ADMINISTRADOR" : ($dtu instanceof Moderador ? "MODERADOR" : "USUARIO")
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
}
?>