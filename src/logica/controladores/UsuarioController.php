<?php
include_once __DIR__ . "/../../servicios/Interfaces/IUsuarioController.php";
include_once __DIR__ . "/../../logica/manejadores/UsuarioRepositorio.php";

class UsuarioController implements IUsuarioController {

    public function altaUsuario(DTUsuario $dtu): void {
        $this->validarDatosAlta($dtu);

        $repositorio = UsuarioRepositorio::getInstance();
        if ($repositorio->obtenerUsuarioPorEmail($dtu->getEmail()) !== null) {
            throw new Exception("Ya existe un usuario con ese email");
        }

        $usuario = new Usuario(
            $repositorio->obtenerSiguienteId(),
            $dtu->getNombre(),
            $dtu->getApellido(),
            $dtu->getEmail(),
            password_hash($dtu->getPassword(), PASSWORD_DEFAULT),
            true,
            new DateTime(),
            [],
            [],
            $dtu->getSexo(),
            $this->crearFecha($dtu->getFechaNacimiento()),
            $dtu->getPais(),
            $dtu->getBio(),
            $dtu->getFotoUrl()
        );

        $repositorio->agregarUsuario($usuario);
    }

    public function bajaUsuario(int $id): void {
        if ($id <= 0) {
            throw new Exception("Id de usuario invalido");
        }

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

    public function modificarUsuario(DTUsuario $dtu): void {
        if (($dtu->getId() ?? 0) <= 0) {
            throw new Exception("Id de usuario invalido");
        }
        $this->validarDatosBasicos($dtu);

        $repositorio = UsuarioRepositorio::getInstance();
        $usuario = $repositorio->obtenerUsuarioPorId($dtu->getId());
        if ($usuario === null) {
            throw new Exception("No existe un usuario con ese id");
        }
        if (!$usuario->getActivo()) {
            throw new Exception("El usuario se encuentra dado de baja");
        }

        $usuarioConEmail = $repositorio->obtenerUsuarioPorEmail($dtu->getEmail());
        if ($usuarioConEmail !== null && $usuarioConEmail->getId() !== $dtu->getId()) {
            throw new Exception("Ya existe un usuario con ese email");
        }

        $usuarioModificado = new Usuario(
            $usuario->getId(),
            $dtu->getNombre(),
            $dtu->getApellido(),
            $dtu->getEmail(),
            $usuario->getPasswordHash(),
            $usuario->getActivo(),
            $usuario->getFechaRegistro(),
            [],
            [],
            $dtu->getSexo(),
            $this->crearFecha($dtu->getFechaNacimiento()),
            $dtu->getPais(),
            $dtu->getBio(),
            $dtu->getFotoUrl()
        );

        $repositorio->modificarUsuario($usuarioModificado);
    }

    public function listarUsuarios(): array {
        return array_map(
            fn(Usuario $usuario): DTUsuario => $this->usuarioADTO($usuario),
            UsuarioRepositorio::getInstance()->listarUsuarios()
        );
    }

    public function moderarUsuario(): void {
    }

    public function iniciarSesion(DTUsuario $dtu): array {
        if (!$dtu->getEmail() || !$dtu->getPassword()) {
            throw new Exception("Email y contraseña son obligatorios");
        }

        $repositorio = UsuarioRepositorio::getInstance();
        $usuario = $repositorio->obtenerUsuarioPorEmail($dtu->getEmail());
        if ($usuario === null || !$usuario->getActivo()) {
            throw new Exception("Email invalido");
        }
        if (!password_verify($dtu->getPassword(), $usuario->getPasswordHash())) {
            throw new Exception("Contraseña incorrecta");
        }

        $sesion = new Sesion(
            $repositorio->obtenerSiguienteIdSesion(),
            $usuario,
            bin2hex(random_bytes(32)),
            new DateTime(),
            null,
            true
        );

        $repositorio->crearSesion($sesion);

        $dtSesion = new DTSesion(
            $sesion->getId(),
            $usuario->getId(),
            $sesion->getToken(),
            $this->formatearFecha($sesion->getFechaInicio()),
            null,
            true
        );

        return [
            "sesion" => $dtSesion,
            "usuario" => $this->usuarioADTO($usuario)
        ];
    }

    public function cerrarSesion(string $token): void {
        if (trim($token) === "") {
            throw new Exception("Token obligatorio");
        }

        UsuarioRepositorio::getInstance()->cerrarSesion(new Sesion(
            null,
            null,
            $token,
            null,
            new DateTime(),
            false
        ));
    }

    public function altaModerador(DTUsuario $dtu): void {
        $this->validarDatosAlta($dtu);

        $repositorio = UsuarioRepositorio::getInstance();
        if ($repositorio->obtenerUsuarioPorEmail($dtu->getEmail()) !== null) {
            throw new Exception("Ya existe un usuario con ese email");
        }

        $moderador = new Moderador(
            $repositorio->obtenerSiguienteId(),
            $dtu->getNombre(),
            $dtu->getApellido(),
            $dtu->getEmail(),
            password_hash($dtu->getPassword(), PASSWORD_DEFAULT),
            true,
            new DateTime(),
            [],
            [],
            $dtu->getSexo(),
            $this->crearFecha($dtu->getFechaNacimiento()),
            $dtu->getPais(),
            $dtu->getBio(),
            $dtu->getFotoUrl()
        );

        $repositorio->agregarModerador($moderador);
    }

    private function validarDatosAlta(DTUsuario $dtu): void {
        $this->validarDatosBasicos($dtu);
        if (!$dtu->getPassword() || strlen($dtu->getPassword()) < 6) {
            throw new Exception("La contraseña debe tener al menos 6 caracteres");
        }
    }

    private function validarDatosBasicos(DTUsuario $dtu): void {
        if (!$dtu->getNombre() || !$dtu->getApellido()) {
            throw new Exception("Nombre y apellido son obligatorios");
        }
        if (!$dtu->getEmail() || !filter_var($dtu->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email invalido");
        }
    }

    private function usuarioADTO(Usuario $usuario): DTUsuario {
        $args = [
            $usuario->getId(),
            $usuario->getNombre(),
            $usuario->getApellido(),
            $usuario->getEmail(),
            null,
            $usuario->getActivo(),
            $this->formatearFecha($usuario->getFechaRegistro()),
            $usuario->getSexo(),
            $this->formatearFecha($usuario->getFechaNacimiento(), "Y-m-d"),
            $usuario->getPais(),
            $usuario->getBio(),
            $usuario->getFotoUrl()
        ];

        return match (true) {
            $usuario instanceof Administrador => new DTAdministrador(...$args),
            $usuario instanceof Moderador => new DTModerador(...$args),
            default => new DTUsuario(...$args)
        };
    }

    private function crearFecha(?string $fecha): ?DateTime {
        if ($fecha === null || trim($fecha) === "") {
            return null;
        }

        return new DateTime($fecha);
    }

    private function formatearFecha(?DateTime $fecha, string $formato = "Y-m-d H:i:s"): ?string {
        return $fecha?->format($formato);
    }
}
