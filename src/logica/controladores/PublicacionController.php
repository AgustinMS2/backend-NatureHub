<?php
include_once __DIR__ . "/../../servicios/Interfaces/IPublicacionController.php";
include_once __DIR__ . "/../../logica/manejadores/PublicacionRepositorio.php";
include_once __DIR__ . "/../../logica/modelos/EstadoPublicacion.php";

class PublicacionController implements IPublicacionController {

    public function __construct() {}

    public function altaPublicacion(DTPublicacion $dtp): void {
        $repositorio = PublicacionRepositorio::getInstance();

        $publicacionExistente = $repositorio->obtenerPublicacionTitulo($dtp->getTitulo());
        if ($publicacionExistente != null){
            throw new Exception("Ya existe una publicacion con ese titulo");
        }

        $id = $repositorio->obtenerSiguienteId();
        var_dump($id);
        $fechaCreacion = new DateTime();
        $fechaUltimaModificacion = new DateTime();
        //$d = [$dtp->getAreasHabitat()];

        $publicacion = new Publicacion(
            $id,
            $dtp->getTitulo(),
            $dtp->getFoto(),
            $dtp->getNombreCientifico(),
            $dtp->getAreasHabitat(),
            $dtp->getDieta(),
            $dtp->getHorasActivas(),
            EstadoPublicacion::PENDIENTE_REVISION,
            $fechaCreacion,
            $fechaUltimaModificacion,
            $dtp->getAutor(),
            $dtp->getCamposExtra(),
            $dtp->getSeccion(),
            [],
            []
        );

        $repositorio->agregarPublicacion($publicacion);
        
    }

    public function bajaPublicacion(int $id): void{
        $repositorio = PublicacionRepositorio::getInstance();

        $publicacion = $repositorio->obtenerPublicacionId($id);
        if ($publicacion === null) {
            throw new Exception("No existe una publicación con ese id");
        }

        $repositorio->eliminarPublicacion($id);
    }

    public function modificarPublicacion(DTPublicacion $dtp): void{
        $repositorio = PublicacionRepositorio::getInstance();

        $publicacionExistente = $repositorio->obtenerPublicacionTitulo($dtp->getTitulo());
        if ($publicacionExistente == null){
            throw new Exception("No existe una publicacion con ese Titulo");
        }

        $fechaCreacion = new DateTime();
        $fechaUltimaModificacion = new DateTime();

        $publicacion = new Publicacion(
            $publicacionExistente->getId(),
            $dtp->getTitulo(),
            $dtp->getFoto(),
            $dtp->getNombreCientifico(),
            $dtp->getAreasHabitat(),
            $dtp->getDieta(),
            $dtp->getHorasActivas(),
            EstadoPublicacion::PENDIENTE_REVISION,
            $fechaCreacion,
            $fechaUltimaModificacion,
            $dtp->getAutor(),
            [],
            $dtp->getSeccion()
        );

        $repositorio->modificarPublicacion($publicacion);

    }

    public function listarPublicaciones(): array{
        $repositorio = PublicacionRepositorio::getInstance();

        $publicaciones = $repositorio->listarPublicaciones();

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
                $publicacion->getSeccion(),
                [],
                []
            );
            $resultado[] = $dtp;
        }

        return $resultado;

    }

    public function listarPublicacionesPropias(int $id): array{
        $repositorio = PublicacionRepositorio::getInstance();

        $publicaciones = $repositorio->listarPublicacionesPropias($id);

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
                $publicacion->getSeccion(),
                [],
                []
            );
            $resultado[] = $dtp;
        }

        return $resultado;
    }

    public function agregarCampoExtra(): void{
        $repositorio = PublicacionRepositorio::getInstance();
    }

    public function eliminarCampoExtra(int $id): void{
        $repositorio = PublicacionRepositorio::getInstance();
    }

    public function listarPublicacionFiltro(string $filtro): void{
        $repositorio = PublicacionRepositorio::getInstance();
    }

    public function moderarPublicacion(): void{
        $repositorio = PublicacionRepositorio::getInstance();

    }

    public function reportarPublicacion(DTReporte $dtr): void{
        $repositorio = PublicacionRepositorio::getInstance();

    }


}
?>