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
        $sql = "INSERT INTO PUBLICACION (id_publicacion, id_seccion, id_autor, titulo, nombre_cientifico, foto_url, areas_habitat, dieta, horas_activas, estado, fecha_creacion, fecha_modificacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
        $consulta = $this->mysql->prepare($sql);

        $id = $publicacion->getId();
        $seccion = $publicacion->getSeccion();
        $autor = $publicacion->getAutor();
        $titulo = $publicacion->getTitulo();
        $nombreCientifico = $publicacion->getNombreCientifico();
        $foto = $publicacion->getFoto();
        $areasHabitat = json_encode($publicacion->getAreasHabitat());
        $dieta = $publicacion->getDieta();
        $horasActivas = $publicacion->getHorasActivas();
        $estado = 'PENDIENTE_REVISION';
        $fechaCreacion = $publicacion->getFechaCreacion()->format("Y-m-d H:i:s");
        $fechaUltimaModificacion = $publicacion->getFechaUltimaModificacion()->format("Y-m-d H:i:s");

        $consulta->bind_param("iiisssssssss", $id, $seccion, $autor, $titulo, $nombreCientifico, $foto, $areasHabitat, $dieta, $horasActivas, $estado, $fechaCreacion, $fechaUltimaModificacion);
        $consulta->execute();
    }

    public function obtenerPublicacionTitulo(string $titulo): ?Publicacion{
        $sql = "SELECT * FROM PUBLICACION WHERE titulo = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("s", $titulo);
        $consulta->execute();

        $resultado = $consulta->get_result();
        $fila = $resultado->fetch_assoc();

        if ($fila) {
            return new Publicacion(
                $fila["id_publicacion"],
                $fila["id_seccion"],
                $fila["id_autor"],
                $fila["titulo"],
                $fila["nombre_cientifico"],
                $fila["foto_url"],
                $fila["areas_habitat"],
                $fila["dieta"],
                $fila["horas_activas"],
                $fila["estado"],
                new DateTime($fila["fecha_creacion"]),
                new DateTime($fila["fecha_modificacion"]),
                [],
                [],
                []
            );
    }

    return null;
    
}

public function obtenerSiguienteId(): int {
        $sql = "SELECT COALESCE(MAX(id_publicacion), 0) + 1 AS proximo_id FROM PUBLICACION";
        $resultado = $this->mysql->query($sql);
        $fila = $resultado->fetch_assoc();
        return $fila["proximo_id"];
    }
}
?>