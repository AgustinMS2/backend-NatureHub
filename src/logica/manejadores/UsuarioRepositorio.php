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
        try {
            $this->mysql->begin_transaction();
            $this->insertarUsuarioBase($usuario);
            $this->guardarPerfil($usuario);
            $this->mysql->commit();
        } catch (Throwable $e) {
            $this->mysql->rollback();
            throw $e;
        }
    }

    public function agregarModerador(Moderador $moderador): void {
        try {
            $this->mysql->begin_transaction();
            $this->insertarUsuarioBase($moderador);

            $sql = "INSERT INTO MODERADOR (id_usuario) VALUES (?)";
            $consulta = $this->mysql->prepare($sql);
            $id = $moderador->getId();
            $consulta->bind_param("i", $id);
            $consulta->execute();

            $this->guardarPerfil($moderador);
            $this->mysql->commit();
        } catch (Throwable $e) {
            $this->mysql->rollback();
            throw $e;
        }
    }

    public function bajaUsuario(int $id): void {
        $sql = "UPDATE USUARIO SET activo = false WHERE id_usuario = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $id);
        $consulta->execute();
    }

    public function modificarUsuario(Usuario $usuario): void {
        try {
            $this->mysql->begin_transaction();

            $sql = "UPDATE USUARIO SET nombre = ?, apellido = ?, email = ? WHERE id_usuario = ?";
            $consulta = $this->mysql->prepare($sql);

            $nombre = $usuario->getNombre();
            $apellido = $usuario->getApellido();
            $email = $usuario->getEmail();
            $id = $usuario->getId();

            $consulta->bind_param("sssi", $nombre, $apellido, $email, $id);
            $consulta->execute();

            $this->guardarPerfil($usuario);
            $this->mysql->commit();
        } catch (Throwable $e) {
            $this->mysql->rollback();
            throw $e;
        }
    }

    public function listarUsuarios(): array {
        $sql = "SELECT u.*, p.bio, p.foto_url, p.sexo, p.fecha_nacimiento, p.pais,
                       m.id_usuario AS es_moderador, a.id_usuario AS es_admin
                FROM USUARIO u
                LEFT JOIN PERFIL p ON p.id_usuario = u.id_usuario
                LEFT JOIN MODERADOR m ON m.id_usuario = u.id_usuario
                LEFT JOIN ADMINISTRADOR a ON a.id_usuario = u.id_usuario";
        $resultado = $this->mysql->query($sql);

        $usuarios = [];
        while ($fila = $resultado->fetch_assoc()) {
            $usuarios[] = $this->usuarioDesdeFila($fila);
        }

        return $usuarios;
    }

    public function moderarUsuario(): void {
    }

    public function crearSesion(Sesion $sesion): void {
        $sql = "INSERT INTO SESION (id_sesion, id_usuario, token, fecha_inicio, fecha_fin, activa) VALUES (?, ?, ?, ?, ?, ?)";
        $consulta = $this->mysql->prepare($sql);

        $idSesion = $sesion->getId();
        $idUsuario = $sesion->getUsuario()->getId();
        $token = $sesion->getToken();
        $fechaInicio = $sesion->getFechaInicio()->format("Y-m-d H:i:s");
        $fechaFin = $sesion->getFechaFin()?->format("Y-m-d H:i:s");
        $activa = $sesion->getActiva() ? 1 : 0;

        $consulta->bind_param("iisssi", $idSesion, $idUsuario, $token, $fechaInicio, $fechaFin, $activa);
        $consulta->execute();
    }

    public function cerrarSesion(Sesion $sesion): void {
        $sql = "UPDATE SESION SET activa = ?, fecha_fin = ? WHERE token = ?";
        $consulta = $this->mysql->prepare($sql);

        $activa = $sesion->getActiva() ? 1 : 0;
        $fechaFin = $sesion->getFechaFin()->format("Y-m-d H:i:s");
        $token = $sesion->getToken();

        $consulta->bind_param("iss", $activa, $fechaFin, $token);
        $consulta->execute();
    }

    public function obtenerUsuarioPorId(int $id): ?Usuario {
        $sql = "SELECT u.*, p.bio, p.foto_url, p.sexo, p.fecha_nacimiento, p.pais,
                       m.id_usuario AS es_moderador, a.id_usuario AS es_admin
                FROM USUARIO u
                LEFT JOIN PERFIL p ON p.id_usuario = u.id_usuario
                LEFT JOIN MODERADOR m ON m.id_usuario = u.id_usuario
                LEFT JOIN ADMINISTRADOR a ON a.id_usuario = u.id_usuario
                WHERE u.id_usuario = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $id);
        $consulta->execute();

        $fila = $consulta->get_result()->fetch_assoc();
        return $fila ? $this->usuarioDesdeFila($fila) : null;
    }

    public function obtenerUsuarioPorEmail(string $email): ?Usuario {
        $sql = "SELECT u.*, p.bio, p.foto_url, p.sexo, p.fecha_nacimiento, p.pais,
                       m.id_usuario AS es_moderador, a.id_usuario AS es_admin
                FROM USUARIO u
                LEFT JOIN PERFIL p ON p.id_usuario = u.id_usuario
                LEFT JOIN MODERADOR m ON m.id_usuario = u.id_usuario
                LEFT JOIN ADMINISTRADOR a ON a.id_usuario = u.id_usuario
                WHERE u.email = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("s", $email);
        $consulta->execute();

        $fila = $consulta->get_result()->fetch_assoc();
        return $fila ? $this->usuarioDesdeFila($fila) : null;
    }

    public function obtenerSiguienteId(): int {
        $sql = "SELECT COALESCE(MAX(id_usuario), 0) + 1 AS proximo_id FROM USUARIO";
        $fila = $this->mysql->query($sql)->fetch_assoc();
        return (int)$fila["proximo_id"];
    }

    public function obtenerSiguienteIdSesion(): int {
        $sql = "SELECT COALESCE(MAX(id_sesion), 0) + 1 AS proximo_id FROM SESION";
        $fila = $this->mysql->query($sql)->fetch_assoc();
        return (int)$fila["proximo_id"];
    }

    public function promoverAModerador(int $idUsuario): void {
        $sql = "INSERT INTO MODERADOR (id_usuario) VALUES (?)";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idUsuario);
        $consulta->execute();
    }

    public function degradarAUsuario(int $idUsuario): void {
        $sql = "DELETE FROM MODERADOR WHERE id_usuario = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idUsuario);
        $consulta->execute();
    }

    private function insertarUsuarioBase(Usuario $usuario): void {
        $sql = "INSERT INTO USUARIO (id_usuario, nombre, apellido, email, password_hash, activo, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $consulta = $this->mysql->prepare($sql);

        $id = $usuario->getId();
        $nombre = $usuario->getNombre();
        $apellido = $usuario->getApellido();
        $email = $usuario->getEmail();
        $passwordHash = $usuario->getPasswordHash();
        $activo = $usuario->getActivo() ? 1 : 0;
        $fechaRegistro = $usuario->getFechaRegistro()->format("Y-m-d H:i:s");

        $consulta->bind_param("issssis", $id, $nombre, $apellido, $email, $passwordHash, $activo, $fechaRegistro);
        $consulta->execute();
    }

    private function guardarPerfil(Usuario $usuario): void {
        $sql = "INSERT INTO PERFIL (id_usuario, bio, foto_url, pais, fecha_nacimiento, sexo)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    bio = VALUES(bio),
                    foto_url = VALUES(foto_url),
                    pais = VALUES(pais),
                    fecha_nacimiento = VALUES(fecha_nacimiento),
                    sexo = VALUES(sexo)";
        $consulta = $this->mysql->prepare($sql);

        $id = $usuario->getId();
        $bio = $usuario->getBio();
        $fotoUrl = $usuario->getFotoUrl();
        $pais = $usuario->getPais();
        $fechaNacimiento = $usuario->getFechaNacimiento()?->format("Y-m-d");
        $sexo = $usuario->getSexo();

        $consulta->bind_param("isssss", $id, $bio, $fotoUrl, $pais, $fechaNacimiento, $sexo);
        $consulta->execute();
    }

    private function usuarioDesdeFila(array $fila): Usuario {
        $args = [
            (int)$fila["id_usuario"],
            $fila["nombre"],
            $fila["apellido"],
            $fila["email"],
            $fila["password_hash"],
            (bool)$fila["activo"],
            new DateTime($fila["fecha_registro"]),
            [],
            [],
            $fila["sexo"] ?? null,
            $this->fechaONull($fila["fecha_nacimiento"] ?? null),
            $fila["pais"] ?? null,
            $fila["bio"] ?? null,
            $fila["foto_url"] ?? null
        ];

        return match (true) {
            !empty($fila["es_admin"]) => new Administrador(...$args),
            !empty($fila["es_moderador"]) => new Moderador(...$args),
            default => new Usuario(...$args)
        };
    }

    private function fechaONull(?string $fecha): ?DateTime {
        if ($fecha === null || $fecha === "" || $fecha === "0000-00-00") {
            return null;
        }

        return new DateTime($fecha);
    }
}
