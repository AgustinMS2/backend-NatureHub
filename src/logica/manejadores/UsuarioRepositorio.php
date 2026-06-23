<?php
include_once __DIR__ . "/../../persistencia/Conectar.php";
include_once __DIR__ . "/../../logica/modelos/Usuario.php";
include_once __DIR__ . "/../../logica/modelos/Moderador.php";
include_once __DIR__ . "/../../logica/modelos/Administrador.php";
include_once __DIR__ . "/../../logica/modelos/Sesion.php";
include_once __DIR__ . "/../../logica/modelos/Publicacion.php";

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

            $sql = "INSERT INTO USUARIO (nombre, apellido, email, password_hash, activo, fecha_registro) VALUES (?, ?, ?, ?, ?, ?)";
            $consulta = $this->mysql->prepare($sql);

            $nombre = $usuario->getNombre();
            $apellido = $usuario->getApellido();
            $email = $usuario->getEmail();
            $passwordHash = $usuario->getPasswordHash();
            $activo = $usuario->getActivo();
            $fechaRegistro = $usuario->getFechaRegistro()->format("Y-m-d H:i:s");

            $consulta->bind_param("ssssss", $nombre, $apellido, $email, $passwordHash, $activo, $fechaRegistro);
            $consulta->execute();

            $id = $this->mysql->insert_id;

            $sqlPerfil = "INSERT INTO PERFIL (id_usuario, bio, foto_url, pais, fecha_nacimiento, sexo) VALUES (?, ?, ?, ?, ?, ?)";
            $consultaPerfil = $this->mysql->prepare($sqlPerfil);

            $bio = $usuario->getBio();
            $fotoUrl = $usuario->getFotoUrl();
            $pais = $usuario->getPais();
            $fechaNacimiento = $usuario->getFechaNacimiento() ? $usuario->getFechaNacimiento()->format("Y-m-d H:i:s") : null;
            $sexo = $usuario->getSexo();

            $consultaPerfil->bind_param("isssss", $id, $bio, $fotoUrl, $pais, $fechaNacimiento, $sexo);
            $consultaPerfil->execute();

            $this->mysql->commit();
        } catch (Exception $e) {
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

            $sql = "UPDATE USUARIO SET nombre = ?, apellido = ?, email = ?, password_hash = ? WHERE id_usuario = ?";
            $consulta = $this->mysql->prepare($sql);
    
            $nombre = $usuario->getNombre();
            $apellido = $usuario->getApellido();
            $email = $usuario->getEmail();
            $id = $usuario->getId();
            $passwordHash = $usuario->getPasswordHash();
    
            $consulta->bind_param("ssssi", $nombre, $apellido, $email, $passwordHash, $id);
            $consulta->execute();

            $sqlPerfil = "UPDATE PERFIL SET bio = ?, foto_url = ?, sexo = ?, fecha_nacimiento = ?, pais = ? WHERE id_usuario = ?";
            $consultaPerfil = $this->mysql->prepare($sqlPerfil);

            $bio = $usuario->getBio(); 
            $fotoUrl = $usuario->getFotoUrl();
            $sexo = $usuario->getSexo();
            $fechaNacimiento = $usuario->getFechaNacimiento() ? $usuario->getFechaNacimiento()->format("Y-m-d H:i:s") : null;
            $pais = $usuario->getPais();

            $consultaPerfil->bind_param("sssssi", $bio, $fotoUrl, $sexo, $fechaNacimiento, $pais, $id);
            $consultaPerfil->execute();

            $this->mysql->commit();

        } catch (Exception $e) {
            $this->mysql->rollback();
            throw $e;
        }
    }

    public function listarUsuarios(): array {
        $sql = "SELECT u.*, p.bio, p.foto_url, p.sexo, p.fecha_nacimiento, p.pais, m.id_usuario AS es_moderador, a.id_usuario AS es_admin
                FROM USUARIO u
                LEFT JOIN PERFIL p ON p.id_usuario = u.id_usuario
                LEFT JOIN MODERADOR m ON m.id_usuario = u.id_usuario
                LEFT JOIN ADMINISTRADOR a ON a.id_usuario = u.id_usuario";
        $resultado = $this->mysql->query($sql);

        $usuarios = [];
        foreach ($resultado as $fila) {
            $args = [
                $fila["id_usuario"],
                $fila["nombre"],
                $fila["apellido"],
                $fila["email"],
                $fila["password_hash"],
                $fila["activo"],
                new DateTime($fila["fecha_registro"]),
                $fila["sexo"],
                new DateTime($fila["fecha_nacimiento"]),
                $fila["pais"],
                $fila["bio"],
                $fila["foto_url"]
            ];

            $usuarios[] = match(true) {
                !empty($fila["es_admin"]) => new Administrador(...$args),
                !empty($fila["es_moderador"]) => new Moderador(...$args),
                default => new Usuario(...$args)
            };
        }
        return $usuarios;
    }

    public function crearSesion(Sesion $sesion): void {
        $sql = "INSERT INTO SESION (id_usuario, token, fecha_inicio, fecha_fin, activa) VALUES (?, ?, ?, ?, ?)";
        $consulta = $this->mysql->prepare($sql);

        $idUsuario = $sesion->getUsuario()->getId();
        $token = $sesion->getToken();
        $fechaInicio = $sesion->getFechaInicio()->format("Y-m-d H:i:s");
        $fechaFin = $sesion->getFechaFin();
        $activa = $sesion->getActiva();

        $consulta->bind_param("isssi", $idUsuario, $token, $fechaInicio, $fechaFin, $activa);
        $consulta->execute();
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

    public function obtenerSesionPorToken(string $token): ?Sesion {
        $sql = "SELECT * FROM SESION WHERE token = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("s", $token);
        $consulta->execute();

        $resultado = $consulta->get_result();
        $fila = $resultado->fetch_assoc();

        if (!$fila) return null;

        $usuario = $this->obtenerUsuarioPorId($fila["id_usuario"]);

        return new Sesion(
            $fila["id_sesion"],
            $usuario,
            $fila["token"],
            new DateTime($fila["fecha_inicio"]),
            $fila["fecha_fin"] ? new DateTime($fila["fecha_fin"]) : null,
            $fila["activa"]
        );
    }

    public function obtenerUsuarioPorId(int $id): ?Usuario {
        $sql = "SELECT u.*, p.bio, p.foto_url, p.sexo, p.fecha_nacimiento, p.pais, m.id_usuario AS es_moderador, a.id_usuario AS es_admin
            FROM USUARIO u
            LEFT JOIN PERFIL p ON p.id_usuario = u.id_usuario
            LEFT JOIN MODERADOR m ON m.id_usuario = u.id_usuario
            LEFT JOIN ADMINISTRADOR a ON a.id_usuario = u.id_usuario
            WHERE u.id_usuario = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $id);
        $consulta->execute();

        $resultado = $consulta->get_result();
        $fila = $resultado->fetch_assoc();

        if (!$fila) return null;

        $args = [
            $fila["id_usuario"],
            $fila["nombre"],
            $fila["apellido"],
            $fila["email"],
            $fila["password_hash"],
            $fila["activo"],
            new DateTime($fila["fecha_registro"]),
            $fila["sexo"],
            new DateTime($fila["fecha_nacimiento"]),
            $fila["pais"],
            $fila["bio"],
            $fila["foto_url"]
        ];

        return match(true) {
            !empty($fila["es_admin"]) => new Administrador(...$args),
            !empty($fila["es_moderador"]) => new Moderador(...$args),
            default => new Usuario(...$args)
        };
    }

    public function obtenerUsuarioPorEmail(string $email): ?Usuario {
        $sql = "SELECT u.*, p.bio, p.foto_url, p.sexo, p.fecha_nacimiento, p.pais, m.id_usuario AS es_moderador, a.id_usuario AS es_admin
                FROM USUARIO u
                LEFT JOIN PERFIL p ON p.id_usuario = u.id_usuario
                LEFT JOIN MODERADOR m ON m.id_usuario = u.id_usuario
                LEFT JOIN ADMINISTRADOR a ON a.id_usuario = u.id_usuario
                WHERE u.email = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("s", $email);
        $consulta->execute();

        $resultado = $consulta->get_result();
        $fila = $resultado->fetch_assoc();

        if (!$fila) return null;

        $args = [
            $fila["id_usuario"],
            $fila["nombre"],
            $fila["apellido"],
            $fila["email"],
            $fila["password_hash"],
            $fila["activo"],
            new DateTime($fila["fecha_registro"]),
            $fila["sexo"],
            new DateTime($fila["fecha_nacimiento"]),
            $fila["pais"],
            $fila["bio"],
            $fila["foto_url"]
        ];

        return match(true) {
            !empty($fila["es_admin"]) => new Administrador(...$args),
            !empty($fila["es_moderador"]) => new Moderador(...$args),
            default => new Usuario(...$args)
        };
    }

    public function promoverAUsuario(int $idUsuario): void {
        $sql = "INSERT INTO MODERADOR (id_usuario) VALUES (?)";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idUsuario);
        $consulta->execute();
    }

    public function degradarAModerador(int $idUsuario): void {
        $sql = "DELETE FROM MODERADOR WHERE id_usuario = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idUsuario);
        $consulta->execute();
    }

    public function promoverAModerador(int $idUsuario): void {
        $sql = "INSERT INTO ADMINISTRADOR (id_usuario) VALUES (?)";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idUsuario);
        $consulta->execute();
    }

    public function degradarAAdministrador(int $idUsuario): void {
        $sql = "DELETE FROM ADMINISTRADOR WHERE id_usuario = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idUsuario);
        $consulta->execute();
    }

    public function agregarAFavoritas(int $idUsuario, int $idPublicacion): void {
        $sql = "INSERT INTO PUBLICACIONES_FAVORITAS (id_usuario, id_publicacion) VALUES (?, ?)";
        $consulta = $this->mysql->prepare($sql);

        $consulta->bind_param("ii", $idUsuario, $idPublicacion);
        $consulta->execute();
    }

    public function eliminarDeFavoritas(int $idUsuario, int $idPublicacion): void {
        $sql = "DELETE FROM PUBLICACIONES_FAVORITAS WHERE id_usuario = ? AND id_publicacion = ?";
        $consulta = $this->mysql->prepare($sql);

        $consulta->bind_param("ii", $idUsuario, $idPublicacion);
        $consulta->execute();
    }

    public function listarLasFavoritas(int $idUsuario): array {
        $sql = "SELECT pf.id_publicacion, p.*
                FROM PUBLICACIONES_FAVORITAS pf
                LEFT JOIN PUBLICACION p ON pf.id_publicacion = p.id_publicacion
                WHERE pf.id_usuario = ? AND p.activo = true";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idUsuario);
        $consulta->execute();

        $resultado = $consulta->get_result();
        $publicaciones = [];
        foreach ($resultado as $fila) {
            $publicaciones[] = new Publicacion(
                $fila["id_publicacion"],
                $fila["titulo"],
                $fila["foto_url"],
                $fila["nombre_cientifico"],
                json_decode($fila["areas_habitat"], true),
                $fila["dieta"],
                $fila["horas_activas"],
                EstadoPublicacion::from($fila["estado"]),
                new DateTime($fila["fecha_creacion"]),
                new DateTime($fila["fecha_modificacion"]),
                $fila["id_autor"],
                [],
                $fila["id_seccion"]
            );
        }

        return $publicaciones;
    }

    
    public function agregarUsuarioFavorito(int $idUsuario, int $idUsuarioFavorito): void {
        $sql = "INSERT INTO USUARIOS_FAVORITOS (id_usuario, id_usuario_favorito) VALUES (?, ?)";
        $consulta = $this->mysql->prepare($sql);

        $consulta->bind_param("ii", $idUsuario, $idUsuarioFavorito);
        $consulta->execute();
    }

    public function eliminarUsuarioFavorito(int $idUsuario, int $idUsuarioFavorito): void {
        $sql = "DELETE FROM USUARIOS_FAVORITOS WHERE id_usuario = ? AND id_usuario_favorito = ?";
        $consulta = $this->mysql->prepare($sql);
        
        $consulta->bind_param("ii", $idUsuario, $idUsuarioFavorito);
        $consulta->execute();
    }

    public function listarUsuariosFavoritos(int $idUsuario): array {
        $sql = "SELECT u.*, p.bio, p.foto_url, p.sexo, p.fecha_nacimiento, p.pais, 
                    m.id_usuario AS es_moderador, a.id_usuario AS es_admin
                FROM USUARIOS_FAVORITOS uf
                LEFT JOIN USUARIO u ON uf.id_usuario_favorito = u.id_usuario
                LEFT JOIN PERFIL p ON p.id_usuario = u.id_usuario
                LEFT JOIN MODERADOR m ON m.id_usuario = u.id_usuario
                LEFT JOIN ADMINISTRADOR a ON a.id_usuario = u.id_usuario
                WHERE uf.id_usuario = ? AND u.activo = true";
                
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idUsuario);
        $consulta->execute();
        $resultado = $consulta->get_result();

        $usuarios = [];
        foreach ($resultado as $fila) {
            $args = [
                $fila["id_usuario"],
                $fila["nombre"],
                $fila["apellido"],
                $fila["email"],
                $fila["password_hash"],
                $fila["activo"],
                new DateTime($fila["fecha_registro"]),
                $fila["sexo"],
                new DateTime($fila["fecha_nacimiento"]),
                $fila["pais"],
                $fila["bio"],
                $fila["foto_url"]
            ];

            $usuarios[] = match(true) {
                !empty($fila["es_admin"]) => new Administrador(...$args),
                !empty($fila["es_moderador"]) => new Moderador(...$args),
                default => new Usuario(...$args)
            };
        }
        return $usuarios;
    }

}
?>