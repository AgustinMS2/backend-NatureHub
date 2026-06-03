<?php
include_once __DIR__ . "/../../servicios/Interfaces/IUsuarioController.php";
include_once __DIR__ . "/../../logica/manejadores/UsuarioRepositorio.php";

class UsuarioController implements IUsuarioController {

    public function __construct() {}

    public function altaUsuario(DTUsuario $dtu): void {
    $repositorio = UsuarioRepositorio::getInstance();

    $usuarioExistente = $repositorio->obtenerUsuarioPorEmail($dtu->getEmail());
    if ($usuarioExistente != null) {
        throw new Exception("Ya existe un usuario con ese email");
    }

    $id = $repositorio->obtenerSiguienteId();
    $passwordHash = password_hash($dtu->getPassword(), PASSWORD_DEFAULT);
    $fechaRegistro = new DateTime();

    $usuario = new Usuario(
        $id,
        $dtu->getNombre(),
        $dtu->getApellido(),
        $dtu->getEmail(),
        $passwordHash,
        true,
        $fechaRegistro,
        [],
        []
    );

    $repositorio->agregarUsuario($usuario);
}

    public function bajaUsuario(int $id): void{
        $repositorio = UsuarioRepositorio::getInstance();

    }

    public function modificarUsuario(DTUsuario $dtu): void{
        $repositorio = UsuarioRepositorio::getInstance();

    }

    public function listarUsuarios(): array {
    $repositorio = UsuarioRepositorio::getInstance();

    $usuarios = $repositorio->listarUsuarios();

    $resultado = [];
    foreach ($usuarios as $usuario) {
        $dtu = new DTUsuario(
            $usuario->getId(),
            $usuario->getNombre(),
            $usuario->getApellido(),
            $usuario->getEmail(),
            null,
            $usuario->getActivo(),
            $usuario->getFechaRegistro()->format("Y-m-d H:i:s")
        );
        $resultado[] = $dtu;
    }

    return $resultado;
}

    public function moderarUsuario(): void{
        $repositorio = UsuarioRepositorio::getInstance();

    }

    public function iniciarSesion(DTUsuario $dtu): array {
        $repositorio = UsuarioRepositorio::getInstance();

        $usuario = $repositorio->obtenerUsuarioPorEmail($dtu->getEmail());
        if ($usuario === null) {
            throw new Exception("Email inválido");
        }
        if (!password_verify($dtu->getPassword(), $usuario->getPasswordHash())) {
            throw new Exception("Contraseña incorrecta");
        }

        $idSesion = $repositorio->obtenerSiguienteIdSesion();
        $token = bin2hex(random_bytes(32));
        $fechaInicio = new DateTime();

        $sesion = new Sesion(
            $idSesion, 
            $usuario, 
            $token, 
            $fechaInicio,
            null,
            true
        );

        $repositorio->crearSesion($sesion);

        $dtSesion = new DTSesion(
            null,
            null,
            $token,
            null,
            null,
            true
        );

        $args = [
            $usuario->getId(),
            $usuario->getNombre(),
            $usuario->getApellido(),
            $usuario->getEmail(),
            null,
            null,
            null
        ];

        $dtUsuario = match(true) {
            $usuario instanceof Administrador => new DTAdministrador(...$args),
            $usuario instanceof Moderador => new DTModerador(...$args),
            $usuario instanceof Usuario => new DTUsuario(...$args)
        };

        return ["sesion" => $dtSesion, "usuario" => $dtUsuario];
    }

    public function cerrarSesion(string $token): void {
        $repositorio = UsuarioRepositorio::getInstance();
        
        $fechaFin = new DateTime();
        $activa = false;

        $sesion = new Sesion(
            null, 
            null, 
            $token, 
            null, 
            $fechaFin, 
            $activa
        );

        $repositorio->cerrarSesion($sesion);
    }


}
?>