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

    public function agregarCampoExtra(DTCampoExtra $dtc): void{
        $repositorio = PublicacionRepositorio::getInstance();

        $campoExistente = $repositorio->verificarExistenciaCampoExtra($dtc->getIdPublicacion(), $dtc->getEtiqueta());
        if($campoExistente != null){
            throw new Exception("Ya existe este campo en esta publicación");
        }

        //var_dump($dtc->getTipo());
        //var_dump($dtc->getTipo()->value);
        $campoExtra = new CampoExtra(
            $dtc->getIdPublicacion(),
            $dtc->getEtiqueta(),
            $dtc->getValor(),
            TipoCampo::from($dtc->getTipo()->value)
        );

        $repositorio->agregarCampoExtra($campoExtra);
    }

    public function eliminarCampoExtra(int $idCampo): void{
        $repositorio = PublicacionRepositorio::getInstance();

        $campoExistente = $repositorio->verificarExistenciaCampoExtraId($idCampo);
        if($campoExistente == null){
            throw new Exception("No existe un campo con esta id");
        }

        $repositorio->eliminarCampoExtra($idCampo);
    }

    public function modificarCampoExtra(DTCampoExtra $dtc): void{
        $repositorio = PublicacionRepositorio::getInstance();

        $campoExistente = $repositorio->verificarExistenciaCampoExtraId($dtc->getId());
        if($campoExistente == null){
            throw new Exception("No existe este campo en esta publicación");
        }

        $campoExtra = new CampoExtra(
            $dtc->getId(),
            $dtc->getIdPublicacion(),
            $dtc->getEtiqueta(),
            $dtc->getValor(),
            TipoCampo::from($dtc->getTipo()->value)
        );

        $repositorio->modificarCampoExtra($campoExtra);
    }

    public function listarPublicacionesPendientes(): array{
        $repositorio = PublicacionRepositorio::getInstance();

        $publicaciones = $repositorio->listarPublicacionesPendientes();

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

    public function listarPublicacionesPorSeccion(string $seccion): array{
        $repositorio = PublicacionRepositorio::getInstance();

        $publicaciones = $repositorio->listarPublicacionesPorSeccion($seccion);

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

    public function reportePublicacion(DTReporte $dtr): void{
        $repositorio = PublicacionRepositorio::getInstance();

        $reporte = new Reporte(
            0,
            $dtr->getIdPublicacion(),
            $dtr->getIdUsuario(),
            $dtr->getMotivo(),
            $dtr->getFecha(),
            $dtr->getResuelto()
        );

        $repositorio->reportePublicacion($reporte);

    }

    public function listarPublicacionFiltro(string $filtro): void{
        $repositorio = PublicacionRepositorio::getInstance();
    }
    
   public function moderarPublicacion(DTModera $dtm): void {
        $repositorio = PublicacionRepositorio::getInstance();
        $repositorioUsuario = UsuarioRepositorio::getInstance();
 
        $publicacion = $repositorio->obtenerPublicacionId($dtm->getPublicacion());
        if ($publicacion === null) {
            throw new Exception("No existe una publicación con ese id");
        }

        $moderador = $repositorioUsuario->obtenerUsuarioPorId($dtm->getModerador());
        if ($moderador === null || !($moderador instanceof Moderador)) {
            throw new Exception("No existe un moderador o administrador con ese id");
        }
        
        $resultadoEnum = ResultadoRevision::tryFrom($dtm->getResultado());
        if ($resultadoEnum === null) {
            throw new Exception("El resultado debe ser APROBADA o RECHAZADA");
        }

        if ($dtm->getResultado() !== 'APROBADA' && $dtm->getResultado() !== 'RECHAZADA') {
            throw new Exception("El resultado debe ser APROBADA o RECHAZADA");
        }

        $id = $repositorio->obtenerSiguienteIdModera();
        $fecha = new DateTime();

        $moderacion= new Modera(
            $id,
            $dtm->getMotivoRechazo(),
            $resultadoEnum,
            $dtm->getModerador(),
            $dtm->getPublicacion(),
            $fecha
        );
 
        $repositorio->moderarPublicacion($moderacion);
    }


    


}
?>