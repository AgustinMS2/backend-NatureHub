<?php
include_once __DIR__ . "/../../persistencia/Conectar.php";

class PublicacionRepositorio {
    private static ?PublicacionRepositorio $instancia = null;
    private mysqli $mysql;

    private function __construct() {
        $this->mysql = new Conectar();
    }

    public static function getInstance(): PublicacionRepositorio {
        if (self::$instancia === null) {
            self::$instancia = new PublicacionRepositorio();
        }
        return self::$instancia;
    }

    public function agregarPublicacion(Publicacion $publicacion): void {
        try {
            $this->mysql->begin_transaction();

            $sql = "INSERT INTO PUBLICACION (id_publicacion, id_seccion, id_autor, titulo, nombre_cientifico, foto_url, areas_habitat, dieta, horas_activas, estado, fecha_creacion, fecha_modificacion)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $consulta = $this->mysql->prepare($sql);

            $id = $publicacion->getId();
            $seccion = $publicacion->getSeccion();
            $autor = $publicacion->getAutor();
            $titulo = $publicacion->getTitulo();
            $nombreCientifico = $publicacion->getNombreCientifico();
            $foto = $publicacion->getFoto();
            $areasHabitat = json_encode($publicacion->getAreasHabitat(), JSON_UNESCAPED_UNICODE);
            $dieta = $publicacion->getDieta();
            $horasActivas = $publicacion->getHorasActivas();
            $estado = $publicacion->getEstado()->value;
            $fechaCreacion = $publicacion->getFechaCreacion()->format("Y-m-d H:i:s");
            $fechaUltimaModificacion = $publicacion->getFechaUltimaModificacion()->format("Y-m-d H:i:s");

            $consulta->bind_param("iiisssssssss", $id, $seccion, $autor, $titulo, $nombreCientifico, $foto, $areasHabitat, $dieta, $horasActivas, $estado, $fechaCreacion, $fechaUltimaModificacion);
            $consulta->execute();

            foreach ($publicacion->getCamposExtra() as $campo) {
                $this->agregarCampoExtraEnTransaccion($id, $campo);
            }

            $this->mysql->commit();
        } catch (Throwable $e) {
            $this->mysql->rollback();
            throw $e;
        }
    }

    public function obtenerPublicacionId(int $id): ?Publicacion {
        $sql = "SELECT * FROM PUBLICACION WHERE id_publicacion = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $id);
        $consulta->execute();

        $fila = $consulta->get_result()->fetch_assoc();
        return $fila ? $this->publicacionDesdeFila($fila) : null;
    }

    public function obtenerPublicacionTitulo(string $titulo): ?Publicacion {
        $sql = "SELECT * FROM PUBLICACION WHERE titulo = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("s", $titulo);
        $consulta->execute();

        $fila = $consulta->get_result()->fetch_assoc();
        return $fila ? $this->publicacionDesdeFila($fila) : null;
    }

    public function modificarPublicacion(Publicacion $publicacion): void {
        try {
            $this->mysql->begin_transaction();

            $sql = "UPDATE PUBLICACION
                    SET id_seccion = ?, id_autor = ?, titulo = ?, nombre_cientifico = ?, foto_url = ?, areas_habitat = ?, dieta = ?, horas_activas = ?, estado = ?, fecha_modificacion = ?
                    WHERE id_publicacion = ?";
            $consulta = $this->mysql->prepare($sql);

            $id = $publicacion->getId();
            $seccion = $publicacion->getSeccion();
            $autor = $publicacion->getAutor();
            $titulo = $publicacion->getTitulo();
            $nombreCientifico = $publicacion->getNombreCientifico();
            $foto = $publicacion->getFoto();
            $areasHabitat = json_encode($publicacion->getAreasHabitat(), JSON_UNESCAPED_UNICODE);
            $dieta = $publicacion->getDieta();
            $horasActivas = $publicacion->getHorasActivas();
            $estado = $publicacion->getEstado()->value;
            $fechaUltimaModificacion = $publicacion->getFechaUltimaModificacion()->format("Y-m-d H:i:s");

            $consulta->bind_param("iissssssssi", $seccion, $autor, $titulo, $nombreCientifico, $foto, $areasHabitat, $dieta, $horasActivas, $estado, $fechaUltimaModificacion, $id);
            $consulta->execute();

            $this->eliminarCamposExtraPorPublicacion($id);
            foreach ($publicacion->getCamposExtra() as $campo) {
                $this->agregarCampoExtraEnTransaccion($id, $campo);
            }

            $this->mysql->commit();
        } catch (Throwable $e) {
            $this->mysql->rollback();
            throw $e;
        }
    }

    public function obtenerSiguienteId(): int {
        $sql = "SELECT COALESCE(MAX(id_publicacion), 0) + 1 AS proximo_id FROM PUBLICACION";
        $fila = $this->mysql->query($sql)->fetch_assoc();
        return (int)$fila["proximo_id"];
    }

    public function eliminarPublicacion(int $id): void {
        try {
            $this->mysql->begin_transaction();
            $this->eliminarCamposExtraPorPublicacion($id);

            $sql = "DELETE FROM PUBLICACION WHERE id_publicacion = ?";
            $consulta = $this->mysql->prepare($sql);
            $consulta->bind_param("i", $id);
            $consulta->execute();

            $this->mysql->commit();
        } catch (Throwable $e) {
            $this->mysql->rollback();
            throw $e;
        }
    }

    public function listarPublicaciones(): array {
        $resultado = $this->mysql->query("SELECT * FROM PUBLICACION ORDER BY fecha_creacion DESC");

        $publicaciones = [];
        while ($fila = $resultado->fetch_assoc()) {
            $publicaciones[] = $this->publicacionDesdeFila($fila);
        }

        return $publicaciones;
    }

    public function listarPublicacionesPropias(int $id): array {
        $sql = "SELECT * FROM PUBLICACION WHERE id_autor = ? ORDER BY fecha_creacion DESC";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $id);
        $consulta->execute();

        $publicaciones = [];
        $resultado = $consulta->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $publicaciones[] = $this->publicacionDesdeFila($fila);
        }

        return $publicaciones;
    }

    public function agregarCampoExtra(int $idPublicacion, CampoExtra $campo): void {
        $this->agregarCampoExtraEnTransaccion($idPublicacion, $campo);
    }

    public function eliminarCampoExtra(int $id): void {
        $sql = "DELETE FROM CAMPO_EXTRA WHERE id_campo = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $id);
        $consulta->execute();
    }

    public function listarPublicacionFiltro(string $filtro): array {
        $like = "%" . $filtro . "%";
        $sql = "SELECT * FROM PUBLICACION
                WHERE titulo LIKE ? OR nombre_cientifico LIKE ? OR areas_habitat LIKE ?
                ORDER BY fecha_creacion DESC";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("sss", $like, $like, $like);
        $consulta->execute();

        $publicaciones = [];
        $resultado = $consulta->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $publicaciones[] = $this->publicacionDesdeFila($fila);
        }

        return $publicaciones;
    }

    private function publicacionDesdeFila(array $fila): Publicacion {
        $id = (int)$fila["id_publicacion"];

        return new Publicacion(
            $id,
            $fila["titulo"],
            $fila["foto_url"] ?? "",
            $fila["nombre_cientifico"] ?? "",
            $this->decodificarAreas($fila["areas_habitat"] ?? ""),
            $fila["dieta"] ?? "",
            $fila["horas_activas"] ?? "",
            EstadoPublicacion::from($fila["estado"]),
            new DateTime($fila["fecha_creacion"]),
            new DateTime($fila["fecha_modificacion"]),
            (int)$fila["id_autor"],
            $this->listarCamposExtra($id),
            (int)$fila["id_seccion"]
        );
    }

    private function decodificarAreas(?string $areas): array {
        if ($areas === null || trim($areas) === "") {
            return [];
        }

        $json = json_decode($areas, true);
        if (is_array($json)) {
            return $json;
        }

        return [$areas];
    }

    private function listarCamposExtra(int $idPublicacion): array {
        $sql = "SELECT * FROM CAMPO_EXTRA WHERE id_publicacion = ? ORDER BY id_campo";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idPublicacion);
        $consulta->execute();

        $campos = [];
        $resultado = $consulta->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $campos[] = new CampoExtra(
                (int)$fila["id_campo"],
                $fila["etiqueta"],
                TipoCampo::from($fila["tipo"]),
                $fila["valor"] ?? ""
            );
        }

        return $campos;
    }

    private function agregarCampoExtraEnTransaccion(int $idPublicacion, CampoExtra $campo): void {
        $sql = "INSERT INTO CAMPO_EXTRA (id_campo, id_publicacion, etiqueta, valor, tipo) VALUES (?, ?, ?, ?, ?)";
        $consulta = $this->mysql->prepare($sql);

        $idCampo = $this->obtenerSiguienteIdCampoExtra();
        $etiqueta = $campo->getEtiqueta();
        $valor = $campo->getValor();
        $tipo = $campo->getTipo()->name;

        $consulta->bind_param("iisss", $idCampo, $idPublicacion, $etiqueta, $valor, $tipo);
        $consulta->execute();
    }

    private function eliminarCamposExtraPorPublicacion(int $idPublicacion): void {
        $sql = "DELETE FROM CAMPO_EXTRA WHERE id_publicacion = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idPublicacion);
        $consulta->execute();
    }

    private function obtenerSiguienteIdCampoExtra(): int {
        $sql = "SELECT COALESCE(MAX(id_campo), 0) + 1 AS proximo_id FROM CAMPO_EXTRA";
        $fila = $this->mysql->query($sql)->fetch_assoc();
        return (int)$fila["proximo_id"];
    }
}
