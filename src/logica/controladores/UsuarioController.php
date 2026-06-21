<?php
include_once __DIR__ . "/../../servicios/Interfaces/IUsuarioController.php";
include_once __DIR__ . "/../../logica/manejadores/UsuarioRepositorio.php";
include_once __DIR__ . "/../../logica/manejadores/PublicacionRepositorio.php";
include_once __DIR__ . "/../../logica/modelos/Usuario.php";
include_once __DIR__ . "/../../logica/modelos/Moderador.php";
include_once __DIR__ . "/../../logica/modelos/Administrador.php";
include_once __DIR__ . "/../../logica/modelos/Sesion.php";

include_once __DIR__ . "/../../servicios/DTs/DTUsuario.php";
include_once __DIR__ . "/../../servicios/DTs/DTAdministrador.php";
include_once __DIR__ . "/../../servicios/DTs/DTModerador.php";
include_once __DIR__ . "/../../servicios/DTs/DTSesion.php";
include_once __DIR__ . "/../../servicios/DTs/DTPublicacion.php";

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
        $fechaNac = $dtu->getFechaNacimiento() ? new DateTime($dtu->getFechaNacimiento()) : null;

        $usuario = new Usuario(
            $id,
            $dtu->getNombre(),
            $dtu->getApellido(),
            $dtu->getEmail(),
            $passwordHash,
            true,
            $fechaRegistro,
            $dtu->getSexo(),
            $fechaNac,
            $dtu->getPais(),
            $dtu->getBio(),
            $dtu->getFotoUrl()
        );

        $repositorio->agregarUsuario($usuario);
    }

    public function bajaUsuario(int $id): void{
        $repositorio = UsuarioRepositorio::getInstance();

        $usuario = $repositorio->obtenerUsuarioPorId($id);
        if ($usuario === null) {
            throw new Exception("No existe un usuario con ese id");
        }
        if (!$usuario->getActivo()) {
            throw new Exception("El usuario ya se encuentra dado de baja");
        }

        $repositorio->bajaUsuario($id);
    }

    public function modificarUsuario(DTUsuario $dtu, ?string $nuevaPassword): void{
        $repositorio = UsuarioRepositorio::getInstance();

        $usuario = $repositorio->obtenerUsuarioPorId($dtu->getId());
        if ($usuario === null) {
            throw new Exception("No existe un usuario con ese id");
        }
        if (!$usuario->getActivo()) {
            throw new Exception("El usuario se encuentra dado de baja");
        }
        if (!password_verify($dtu->getPassword(), $usuario->getPasswordHash())) {
            throw new Exception("La contraseña actual introducida es incorrecta.");
        }

        $usuarioConEmail = $repositorio->obtenerUsuarioPorEmail($dtu->getEmail());
        if ($usuarioConEmail !== null && $usuarioConEmail->getId() !== $dtu->getId()) {
            throw new Exception("Ya existe un usuario con ese email");
        }

        if (!empty($nuevaPassword)) {
            $passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
        } else {
            $passwordHash = $usuario->getPasswordHash();
        }

        $fechaNac = $dtu->getFechaNacimiento() ? new DateTime($dtu->getFechaNacimiento()) : null;

        $usuarioModificado = new Usuario(
            $usuario->getId(),
            $dtu->getNombre(),
            $dtu->getApellido(),
            $dtu->getEmail(),
            $passwordHash,
            $usuario->getActivo(),
            $usuario->getFechaRegistro(),
            $dtu->getSexo(),
            $fechaNac,
            $dtu->getPais(),
            $dtu->getBio(),
            $dtu->getFotoUrl()
        );

        $repositorio->modificarUsuario($usuarioModificado);
    }

    public function listarUsuarios(): array {
    $repositorio = UsuarioRepositorio::getInstance();
    $usuarios = $repositorio->listarUsuarios();

    $resultado = [];
    foreach ($usuarios as $usuario) {
        $args = [
            $usuario->getId(),
            $usuario->getNombre(),
            $usuario->getApellido(),
            $usuario->getEmail(),
            null,
            $usuario->getActivo(),
            $usuario->getFechaRegistro() ? $usuario->getFechaRegistro()->format("Y-m-d H:i:s") : null,
            $usuario->getSexo(),
            $usuario->getFechaNacimiento() ? $usuario->getFechaNacimiento()->format("Y-m-d H:i:s") : null,
            $usuario->getPais(),
            $usuario->getBio(),
            $usuario->getFotoUrl()
        ];

        $dtu = match(true) {
            $usuario instanceof Administrador => new DTAdministrador(...$args),
            $usuario instanceof Moderador => new DTModerador(...$args),
            default => new DTUsuario(...$args)
        };

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
        if(!$usuario->getActivo()) {
            throw new Exception("El usuario se encuentra dado de baja");
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
            $usuario->getActivo(),
            $usuario->getFechaRegistro()->format("Y-m-d H:i:s"),
            $usuario->getSexo(),
            $usuario->getFechaNacimiento() ? $usuario->getFechaNacimiento()->format("Y-m-d H:i:s") : null,
            $usuario->getPais(),
            $usuario->getBio(),
            $usuario->getFotoUrl()
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

    public function promoverUsuario(int $id): void {
        $repositorio = UsuarioRepositorio::getInstance();

        $usuario = $repositorio->obtenerUsuarioPorId($id);
        if ($usuario === null) {
            throw new Exception("No existe un usuario con ese id");
        }
        if (!($usuario instanceof Usuario)) {
            throw new Exception("El usuario ya es moderador o administrador");
        }

        $repositorio->promoverAUsuario($id);
    }

    public function degradarModerador(int $id): void {
        $repositorio = UsuarioRepositorio::getInstance();

        $usuario = $repositorio->obtenerUsuarioPorId($id);
        if ($usuario === null) {
            throw new Exception("No existe un usuario con ese id");
        }
        if (!($usuario instanceof Moderador)) {
            throw new Exception("El usuario no es moderador");
        }

        $repositorio->degradarAModerador($id);
    }

    public function promoverModerador(int $id): void {
        $repositorio = UsuarioRepositorio::getInstance();

        $usuario = $repositorio->obtenerUsuarioPorId($id);
        if ($usuario === null) {
            throw new Exception("No existe un usuario con ese id");
        }
        if ($usuario instanceof Administrador) {
            throw new Exception("El usuario ya es administrador");
        }

        $repositorio->promoverAModerador($id);
    }

    public function degradarAdministrador(int $id): void {
        $repositorio = UsuarioRepositorio::getInstance();

        $usuario = $repositorio->obtenerUsuarioPorId($id);
        if ($usuario === null) {
            throw new Exception("No existe un usuario con ese id");
        }
        if (!($usuario instanceof Administrador)) {
            throw new Exception("El usuario no es administrador");
        }

        $repositorio->degradarAAdministrador($id);
    }

    public function agregarFavoritas(int $idUsuario, int $idPublicacion): void {
        $repositorioU = UsuarioRepositorio::getInstance();
        $repositorioP = PublicacionRepositorio::getInstance();

        $usuario = $repositorioU->obtenerUsuarioPorId($idUsuario);
        if ($usuario === null) {
            throw new Exception("No existe un usuario con ese id");
        }

        $publicacion = $repositorioP->obtenerPublicacionId($idPublicacion);
        if ($publicacion === null) {
            throw new Exception("No existe una publicacion con ese id");
        }

        $repositorioU->agregarAFavoritas($idUsuario, $idPublicacion);
    }

    public function eliminarFavorita(int $idUsuario, int $idPublicacion): void {
        $repositorioU = UsuarioRepositorio::getInstance();
        $repositorioP = PublicacionRepositorio::getInstance();

        $usuario = $repositorioU->obtenerUsuarioPorId($idUsuario);
        if ($usuario === null) {
            throw new Exception("No existe un usuario con ese id");
        }

        $publicacion = $repositorioP->obtenerPublicacionId($idPublicacion);
        if ($publicacion === null) {
            throw new Exception("No existe una publicacion con ese id");
        }

        $repositorioU->eliminarDeFavoritas($idUsuario, $idPublicacion);
    }

    public function listarFavoritas(int $idUsuario): array {
        $repositorio = UsuarioRepositorio::getInstance();

        $usuario = $repositorio->obtenerUsuarioPorId($idUsuario);
        if ($usuario === null) {
            throw new Exception("No existe un usuario con ese id");
        }

        $publicaciones = $repositorio->listarLasFavoritas($idUsuario);

        $resultado = [];
        foreach ($publicaciones as $publicacion) {
            $dtp = new DTPublicacion(
                $publicacion->getId(),
                $publicacion->getTitulo(),
                $publicacion->getFoto(),
                $publicacion->getNombreCientifico(),
                $publicacion->getAreasHabitat(),
                $publicacion->getDieta(),
                $publicacion->getHorasActivas(),
                $publicacion->getEstado()->value,
                $publicacion->getFechaCreacion()->format("Y-m-d H-i-s"),
                $publicacion->getFechaUltimaModificacion()->format("Y-m-d H:i:s"),
                $publicacion->getAutor(),
                $publicacion->getCamposExtra(),
                $publicacion->getSeccion()
            );
            $resultado[] = $dtp;
        }

        return $resultado;
    }

    public function obtenerUsuarioId(int $idUsuario): DTUsuario {
        $repositorio = UsuarioRepositorio::getInstance();

        $usuario = $repositorio->obtenerUsuarioPorId($idUsuario);
        if ($usuario === null) {
            throw new Exception("No existe un usuario con ese id");
        }

        $args = [
            $usuario->getId(),
            $usuario->getNombre(),
            $usuario->getApellido(),
            $usuario->getEmail(),
            null,
            $usuario->getActivo(),
            $usuario->getFechaRegistro()->format("Y-m-d H:i:s"),
            $usuario->getSexo(),
            $usuario->getFechaNacimiento() ? $usuario->getFechaNacimiento()->format("Y-m-d H:i:s") : null,
            $usuario->getPais(),
            $usuario->getBio(),
            $usuario->getFotoUrl()
        ];

        $dtUsuario = match(true) {
            $usuario instanceof Administrador => new DTAdministrador(...$args),
            $usuario instanceof Moderador => new DTModerador(...$args),
            $usuario instanceof Usuario => new DTUsuario(...$args)
        };

        return $dtUsuario;

    }




}
?>