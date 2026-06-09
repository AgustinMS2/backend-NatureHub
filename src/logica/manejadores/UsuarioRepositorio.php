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

            $sql = "INSERT INTO USUARIO (id_usuario, nombre, apellido, email, password_hash, activo, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $consulta = $this->mysql->prepare($sql);

            $id = $usuario->getId();
            $nombre = $usuario->getNombre();
            $apellido = $usuario->getApellido();
            $email = $usuario->getEmail();
            $passwordHash = $usuario->getPasswordHash();
            $activo = $usuario->getActivo();
            $fechaRegistro = $usuario->getFechaRegistro()->format("Y-m-d H:i:s");
    
            $consulta->bind_param("issssss", $id, $nombre, $apellido, $email, $passwordHash, $activo, $fechaRegistro);
            $consulta->execute();
    
            $sqlPerfil = "INSERT INTO PERFIL (id_usuario, bio, foto_url, pais, fecha_nacimiento, sexo) VALUES (?, ?, ?, ?, ?, ?)";
            $consultaPerfil = $this->mysql->prepare($sqlPerfil);

            $bio = $usuario->getBio();
            $fotoUrl = $usuario->getFotoUrl();
            $pais = $usuario->getPais();
            $fechaNacimiento = $usuario->getFechaNacimiento()->format("Y-m-d H:i:s");
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
        try{
            $sql = "UPDATE USUARIO SET activo = false WHERE id_usuario = ?";
            $consulta = $this->mysql->prepare($sql);
            $consulta->bind_param("i", $id);
            $consulta->execute();
        } catch (Exception $e) {
            throw $e;
        }
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

            $sqlPerfil = "UPDATE PERFIL SET bio = ?, foto_url = ?, sexo = ?, fecha_nacimiento = ?, pais = ? WHERE id_usuario = ?";
            $consultaPerfil = $this->mysql->prepare($sqlPerfil);

            $bio = $usuario->getBio(); 
            $fotoUrl = $usuario->getFotoUrl();
            $sexo = $usuario->getSexo();
            $fechaNacimiento = $usuario->getFechaNacimiento()->format("Y-m-d H:i:s");
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
        try{
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
                    [],
                    [],
                    $fila["sexo"],
                    $fila["fecha_nacimiento"],
                    $fila["pais"],
                    $fila["bio"]
                ];

                $usuarios[] = match(true) {
                    !empty($fila["es_admin"]) => new Administrador(...$args),
                    !empty($fila["es_moderador"]) => new Moderador(...$args),
                    default => new Usuario(...$args)
                };
            }
            return $usuarios;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function moderarUsuario(): void {

    }

    public function crearSesion(Sesion $sesion): void {
        try{
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
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function cerrarSesion(Sesion $sesion): void {
        try{
            $sql = "UPDATE SESION SET activa = ?, fecha_fin = ? WHERE token = ?";
            $consulta = $this->mysql->prepare($sql);

            $token = $sesion->getToken();
            $fechaFin = $sesion->getFechaFin()->format("Y-m-d H:i:s");
            $activa= $sesion->getActiva();

            $consulta->bind_param("iss", $activa, $fechaFin, $token);
            $consulta->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function obtenerUsuarioPorId(int $id): ?Usuario {
        try{
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
                [],
                [],
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
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function obtenerUsuarioPorEmail(string $email): ?Usuario {
        try{

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
                [],
                [],
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

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function obtenerSiguienteId(): int {
        try{
            $sql = "SELECT COALESCE(MAX(id_usuario), 0) + 1 AS proximo_id FROM USUARIO";
            $resultado = $this->mysql->query($sql);
            $fila = $resultado->fetch_assoc();
            return $fila["proximo_id"];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function obtenerSiguienteIdSesion(): int {
        try{
            $sql = "SELECT COALESCE(MAX(id_sesion), 0) + 1 AS proximo_id FROM SESION";
            $resultado = $this->mysql->query($sql);
            $fila = $resultado->fetch_assoc();
            return $fila["proximo_id"];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function promoverAModerador(int $idUsuario): void {
        try{
            $sql = "INSERT INTO MODERADOR (id_usuario) VALUES (?)";
            $consulta = $this->mysql->prepare($sql);
            $consulta->bind_param("i", $idUsuario);
            $consulta->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }
 
    public function degradarAUsuario(int $idUsuario): void {
        try{
            $sql = "DELETE FROM MODERADOR WHERE id_usuario = ?";
            $consulta = $this->mysql->prepare($sql);
            $consulta->bind_param("i", $idUsuario);
            $consulta->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }

    
    public function agregarModerador(Moderador $moderador): void {
        $sql = "INSERT INTO USUARIO (id_usuario, nombre, apellido, email, password_hash, activo, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $consulta = $this->mysql->prepare($sql);
 
        $id = $moderador->getId();
        $nombre = $moderador->getNombre();
        $apellido = $moderador->getApellido();
        $email = $moderador->getEmail();
        $passwordHash = $moderador->getPasswordHash();
        $activo = $moderador->getActivo();
        $fechaRegistro = $moderador->getFechaRegistro()->format("Y-m-d H:i:s");
 
        $consulta->bind_param("issssss", $id, $nombre, $apellido, $email, $passwordHash, $activo, $fechaRegistro);
        $consulta->execute();
 
        $sql2 = "INSERT INTO MODERADOR (id_usuario) VALUES (?)";
        $consulta2 = $this->mysql->prepare($sql2);
        $consulta2->bind_param("i", $id);
        $consulta2->execute();
 
        $this->agregarPerfil($id);
    }

    /*
    public function agregarAdministrador(Administrador $administrador): void {
        $sql = "INSERT INTO USUARIO (id_usuario, nombre, apellido, email, password_hash, activo, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $consulta = $this->mysql->prepare($sql);
 
        $id = $administrador->getId();
        $nombre = $administrador->getNombre();
        $apellido = $administrador->getApellido();
        $email = $administrador->getEmail();
        $passwordHash = $administrador->getPasswordHash();
        $activo = $administrador->getActivo();
        $fechaRegistro = $administrador->getFechaRegistro()->format("Y-m-d H:i:s");
 
        $consulta->bind_param("issssss", $id, $nombre, $apellido, $email, $passwordHash, $activo, $fechaRegistro);
        $consulta->execute();

        $sql2 = "INSERT INTO ADMINISTRADOR (id_usuario) VALUES (?)";
        $consulta2 = $this->mysql->prepare($sql2);
        $consulta2->bind_param("i", $id);
        $consulta2->execute();
 
        $this->agregarPerfil($id);
    }
    */

}
?>