<?php
include_once __DIR__ . "/../../servicios/Fabrica.php";

class UsuarioEndpoint {
    private IUsuarioController $controlador;

    public function __construct() {
        $this->controlador = Fabrica::getInstance()->getIUsuarioController();
    }

    public function altaUsuario(): void {
        try {
            $datos = $this->leerJson();
            $dtu = $this->crearDTUsuario($datos, true);

            $this->controlador->altaUsuario($dtu);
            $this->responder(["mensaje" => "Usuario creado correctamente"], 201);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 400);
        }
    }

    public function bajaUsuario(): void {
        try {
            $datos = $this->leerJson();
            $this->controlador->bajaUsuario((int)($datos->id ?? 0));
            $this->responder(["mensaje" => "Usuario dado de baja correctamente"]);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 400);
        }
    }

    public function modificarUsuario(): void {
        try {
            $datos = $this->leerJson();
            $dtu = new DTUsuario(
                (int)($datos->id ?? 0),
                $datos->nombre ?? null,
                $datos->apellido ?? null,
                $datos->email ?? null,
                null,
                null,
                null,
                $datos->sexo ?? null,
                $datos->fechaNacimiento ?? null,
                $datos->pais ?? null,
                $datos->bio ?? null,
                $datos->fotoUrl ?? null
            );

            $this->controlador->modificarUsuario($dtu);
            $this->responder(["mensaje" => "Usuario modificado correctamente"]);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 400);
        }
    }

    public function listarUsuarios(): void {
        try {
            $usuarios = array_map(fn(DTUsuario $dtu): array => $this->usuarioAArray($dtu), $this->controlador->listarUsuarios());
            $this->responder($usuarios);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 500);
        }
    }

    public function moderarUsuario(): void {
        $this->responderError("Caso de uso no implementado", 501);
    }

    public function iniciarSesion(): void {
        try {
            $datos = $this->leerJson();
            $dtu = new DTUsuario(
                null,
                null,
                null,
                $datos->email ?? null,
                $datos->password ?? null,
                null,
                null,
                null,
                null,
                null,
                null,
                null
            );

            $resultado = $this->controlador->iniciarSesion($dtu);
            $dts = $resultado["sesion"];
            $dtu = $resultado["usuario"];

            $respuesta = $this->usuarioAArray($dtu);
            $respuesta["token"] = $dts->getToken();
            $respuesta["activa"] = $dts->getActiva();
            $respuesta["idusuario"] = $dtu->getId();
            $respuesta["rol"] = $this->rolDesdeDTO($dtu);

            $this->responder($respuesta);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 401);
        }
    }

    public function cerrarSesion(): void {
        try {
            $datos = $this->leerJson();
            $this->controlador->cerrarSesion((string)($datos->token ?? ""));
            $this->responder(["mensaje" => "Sesion cerrada correctamente"]);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 400);
        }
    }

    public function altaModerador(): void {
        try {
            $datos = $this->leerJson();
            $dtu = $this->crearDTUsuario($datos, true);

            $this->controlador->altaModerador($dtu);
            $this->responder(["mensaje" => "Moderador creado correctamente"], 201);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 400);
        }
    }

    private function leerJson(): object {
        $raw = file_get_contents("php://input");
        $datos = json_decode($raw ?: "{}");

        if (!is_object($datos)) {
            throw new InvalidArgumentException("El cuerpo debe ser JSON valido");
        }

        return $datos;
    }

    private function crearDTUsuario(object $datos, bool $incluyePassword): DTUsuario {
        return new DTUsuario(
            isset($datos->id) ? (int)$datos->id : null,
            $datos->nombre ?? null,
            $datos->apellido ?? null,
            $datos->email ?? null,
            $incluyePassword ? ($datos->password ?? null) : null,
            null,
            null,
            $datos->sexo ?? null,
            $datos->fechaNacimiento ?? null,
            $datos->pais ?? null,
            $datos->bio ?? null,
            $datos->fotoUrl ?? null
        );
    }

    private function usuarioAArray(DTUsuario $dtu): array {
        return [
            "id" => $dtu->getId(),
            "idusuario" => $dtu->getId(),
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

    private function rolDesdeDTO(DTUsuario $dtu): string {
        return match (true) {
            $dtu instanceof DTAdministrador => "ADMINISTRADOR",
            $dtu instanceof DTModerador => "MODERADOR",
            default => "USUARIO"
        };
    }

    private function responder(mixed $datos, int $codigo = 200): void {
        http_response_code($codigo);
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    }

    private function responderError(string $mensaje, int $codigo): void {
        $this->responder(["error" => $mensaje], $codigo);
    }
}
