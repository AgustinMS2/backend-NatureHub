<?php
include_once __DIR__ . "/../../persistencia/Conectar.php";

class UsuarioRepositorio {
    private static ?UsuarioRepositorio $instancia = null;
    private mysqli $mysql;

    private function __construct() {
        $this->mysql = new Conectar();
    }

    public static function getInstance(): UsuarioRepositorio {
        if (self::$instancia === null) {
            self::$instancia = new UsuarioRepositorio();
        }
        return self::$instancia;
    }

    public function agregarUsuario(Usuario $usuario): void {
        $sql = "INSERT INTO USUARIO (id_usuario, nombre, apellido, email, password_hash, rol, activo, fecha_registro, sexo, fecha_nacimiento, pais, bio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $consulta = $this->mysql->prepare($sql);

        $id = $usuario->getId();
        $nombre = $usuario->getNombre();
        $apellido = $usuario->getApellido();
        $email = $usuario->getEmail();
        $passwordHash = $usuario->getPasswordHash();
        $rol = "Usuario";
        $activo = $usuario->getActivo();
        $fechaRegistro = $usuario->getFechaRegistro()->format("Y-m-d H:i:s");
        $sexo = $usuario->getSexo();
        $fechaNacimiento = $usuario->getFechaNacimiento();
        $pais = $usuario->getPais();
        $bio = $usuario->getBio();

        $consulta->bind_param("isssssssssss", $id, $nombre, $apellido, $email, $passwordHash, $rol, $activo, $fechaRegistro, $sexo, $fechaNacimiento, $pais, $bio);
        $consulta->execute();
    }

    public function obtenerUsuarioPorEmail(string $email): ?Usuario {
        $sql = "SELECT * FROM USUARIO WHERE email = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("s", $email);
        $consulta->execute();

        $resultado = $consulta->get_result();
        $fila = $resultado->fetch_assoc();

        if ($fila) {
            $args = [
                $fila["id_usuario"],
                $fila["nombre"],
                $fila["apellido"],
                $fila["email"],
                $fila["password_hash"],
                $fila["activo"],
                new DateTime($fila["fecha_registro"]),
                [],
                [],
                $fila["sexo"] ?? null,
                $fila["fecha_nacimiento"] ?? null,
                $fila["pais"] ?? null,
                $fila["bio"] ?? null
            ];

            return match($fila["rol"]) {
                "Administrador" => new Administrador(...$args),
                "Moderador" => new Moderador(...$args),
                "Usuario" => new Usuario(...$args)
            };
        }

        return null;
    }

    public function obtenerSiguienteId(): int {
        $sql = "SELECT COALESCE(MAX(id_usuario), 0) + 1 AS proximo_id FROM USUARIO";
        $resultado = $this->mysql->query($sql);
        $fila = $resultado->fetch_assoc();
        return $fila["proximo_id"];
    }

    public function listarUsuarios(): array {
        $sql = "SELECT * FROM USUARIO";
        $resultado = $this->mysql->query($sql);

        $usuarios = [];
        foreach ($resultado as $fila) {
            $usuarios[] = new Usuario(
                $fila["id_usuario"],
                $fila["nombre"],
                $fila["apellido"],
                $fila["email"],
                $fila["password_hash"],
                $fila["activo"],
                new DateTime($fila["fecha_registro"]),
                [],
                []
            );
        }
        return $usuarios;
    }

    public function crearSesion(Sesion $sesion): void {
        $sql = "INSERT INTO SESION (id_sesion, id_usuario, token, fecha_inicio, fecha_fin, activa) VALUES (?, ?, ?, ?, ?, ?)";
        $consulta = $this->mysql->prepare($sql);

        $idSesion = $sesion->getId();
        $idUsuario = $sesion->getUsuario()->getId();
        $token = $sesion->getToken();
        $fechaInicio = $sesion->getFechaInicio()->format("Y-m-d H:i:s");
        $fechaFin = $sesion->getFechaFin();
        $activa = $sesion->getActiva();

        $consulta->bind_param("iisssi", $idSesion, $idUsuario, $token, $fechaInicio, $fechaFin, $activa);
        $consulta->execute();
    }

    public function obtenerSiguienteIdSesion(): int {
        $sql = "SELECT COALESCE(MAX(id_sesion), 0) + 1 AS proximo_id FROM SESION";
        $resultado = $this->mysql->query($sql);
        $fila = $resultado->fetch_assoc();
        return $fila["proximo_id"];
    }

    public function cerrarSesion(Sesion $sesion): void {
        $sql = "UPDATE SESION SET activa = ?, fecha_fin = ? WHERE token = ?";
        $consulta = $this->mysql->prepare($sql);

        $token = $sesion->getToken();
        $fechaFin = $sesion->getFechaFin()->format("Y-m-d H:i:s");
        $activa= $sesion->getActiva();

        $consulta->bind_param("iss", $activa, $fechaFin, $token);
        $consulta->execute();
    }

    public function obtenerUsuarioPorId(int $id): ?Usuario {
        $sql = "SELECT * FROM USUARIO WHERE id_usuario = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $id);
        $consulta->execute();

        $resultado = $consulta->get_result();
        $fila = $resultado->fetch_assoc();

        if ($fila) {
            $args = [
                $fila["id_usuario"],
                $fila["nombre"],
                $fila["apellido"],
                $fila["email"],
                $fila["password_hash"],
                $fila["activo"],
                new DateTime($fila["fecha_registro"]),
                [],
                [],
                $fila["sexo"] ?? null,
                $fila["fecha_nacimiento"] ?? null,
                $fila["pais"] ?? null,
                $fila["bio"] ?? null
            ];

            return match($fila["rol"]) {
                "Administrador" => new Administrador(...$args),
                "Moderador" => new Moderador(...$args),
                "Usuario" => new Usuario(...$args)
            };
        }

        return null;
    }

    public function bajaUsuario(int $id): void {
        $sql = "UPDATE USUARIO SET activo = false WHERE id_usuario = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $id);
        $consulta->execute();
    }

    public function agregarModerador(Usuario $usuario): void {
        $sql = "INSERT INTO USUARIO (id_usuario, nombre, apellido, email, password_hash, rol, activo, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $consulta = $this->mysql->prepare($sql);

        $id = $usuario->getId();
        $nombre = $usuario->getNombre();
        $apellido = $usuario->getApellido();
        $email = $usuario->getEmail();
        $passwordHash = $usuario->getPasswordHash();
        $rol = "Moderador";
        $activo = $usuario->getActivo();
        $fechaRegistro = $usuario->getFechaRegistro()->format("Y-m-d H:i:s");

        $consulta->bind_param("isssssss", $id, $nombre, $apellido, $email, $passwordHash, $rol, $activo, $fechaRegistro);
        $consulta->execute();
    }

    public function modificarUsuario(Usuario $usuario): void {
        $sql = "UPDATE USUARIO SET nombre = ?, apellido = ?, email = ?, sexo = ?, fecha_nacimiento = ?, pais = ?, bio = ? WHERE id_usuario = ?";
        $consulta = $this->mysql->prepare($sql);

        $nombre = $usuario->getNombre();
        $apellido = $usuario->getApellido();
        $email = $usuario->getEmail();
        $sexo = $usuario->getSexo();
        $fechaNacimiento = $usuario->getFechaNacimiento();
        $pais = $usuario->getPais();
        $bio = $usuario->getBio();
        $id = $usuario->getId();

        $consulta->bind_param("ssssssi", $nombre, $apellido, $email, $sexo, $fechaNacimiento, $pais, $bio, $id);
        $consulta->execute();
    }

}
?>