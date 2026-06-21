<?php
include_once __DIR__ . "/../../servicios/Interfaces/IPublicacionController.php";
include_once __DIR__ . "/../../logica/manejadores/PublicacionRepositorio.php";
include_once __DIR__ . "/../../logica/manejadores/UsuarioRepositorio.php";
include_once __DIR__ . "/../../logica/modelos/EstadoPublicacion.php";
include_once __DIR__ . "/../../logica/modelos/Publicacion.php";
include_once __DIR__ . "/../../logica/modelos/CampoExtra.php";
include_once __DIR__ . "/../../logica/modelos/TipoCampo.php";
include_once __DIR__ . "/../../logica/modelos/Reporte.php";
include_once __DIR__ . "/../../logica/modelos/Modera.php";
include_once __DIR__ . "/../../logica/modelos/ResultadoRevision.php";
include_once __DIR__ . "/../../logica/modelos/Moderador.php";

include_once __DIR__ . "/../../servicios/DTs/DTPublicacion.php";
include_once __DIR__ . "/../../servicios/DTs/DTCampoExtra.php";
include_once __DIR__ . "/../../servicios/DTs/DTReporte.php";
include_once __DIR__ . "/../../servicios/DTs/DTModera.php";

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
            $dtp->getSeccion()
        );

        $repositorio->agregarPublicacion($publicacion);
        $repositorio->eliminarBorradorPorAutor($dtp->getAutor());
        
    }

    public function bajaPublicacion(int $id): void{
        $repositorio = PublicacionRepositorio::getInstance();

        $publicacion = $repositorio->obtenerPublicacionId($id);
        if ($publicacion === null) {
            throw new Exception("No existe una publicación con ese id");
        }

        $repositorio->eliminarPublicacion($id);
    }

    public function modificarPublicacion(DTPublicacion $dtp): array{
        $repositorio = PublicacionRepositorio::getInstance();

        $publicacionExistente = $repositorio->obtenerPublicacionId($dtp->getId());
        if ($publicacionExistente === null){
            throw new Exception("No existe una publicacion con ese id");
        }

        if ($publicacionExistente->getAutor() !== $dtp->getAutor()) {
            throw new Exception("No tenés permiso para modificar esta publicación");
        }

        if ($dtp->getTitulo() !== $publicacionExistente->getTitulo()) {
            $otraConMismoTitulo = $repositorio->obtenerPublicacionTitulo($dtp->getTitulo());
            if ($otraConMismoTitulo !== null) {
                throw new Exception("Ya existe una publicacion con ese titulo");
            }
        }

        $nuevoEstado = $publicacionExistente->getEstado();
        if ($publicacionExistente->getEstado() === EstadoPublicacion::APROBADA) {
            $nuevoEstado = EstadoPublicacion::PENDIENTE_REVISION;
        }

        $foto = $dtp->getFoto() ?: $publicacionExistente->getFoto();
        $fechaUltimaModificacion = new DateTime();

        $publicacion = new Publicacion(
            $publicacionExistente->getId(),
            $dtp->getTitulo(),
            $foto,
            $dtp->getNombreCientifico(),
            $dtp->getAreasHabitat(),
            $dtp->getDieta(),
            $dtp->getHorasActivas(),
            $nuevoEstado,
            $publicacionExistente->getFechaCreacion(),
            $fechaUltimaModificacion,
            $dtp->getAutor(),
            [],
            $dtp->getSeccion()
        );

        $repositorio->modificarPublicacion($publicacion);
        $repositorio->reemplazarCamposExtra($publicacion->getId(), $dtp->getCamposExtra());

        return ["estado" => $nuevoEstado->value];
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
                $publicacion->getSeccion()
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
                $publicacion->getSeccion()
            );
            $resultado[] = $dtp;
        }

        return $resultado;
    }

    public function listarPublicacionesTitulo(string $titulo): array{
        $repositorio = PublicacionRepositorio::getInstance();

        $publicacion = $repositorio->obtenerPublicacionTitulo($titulo);
        $resultado = [];
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
                $publicacion->getSeccion()
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
                $publicacion->getSeccion()
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

    public function listarSecciones(): array {
        $repositorio = PublicacionRepositorio::getInstance();
        $secciones = $repositorio->listarSecciones();
        return $secciones;
    }

    public function guardarBorrador(int $idAutor, ?int $idSeccion, ?string $titulo, ?string $nombreCientifico, ?string $fotoUrl, ?string $areasHabitat, ?string $dieta, ?string $horasActivas, array $camposExtra): array {
        $repositorio = PublicacionRepositorio::getInstance();
        $camposExtraJson = json_encode($camposExtra);
        $idBorrador = $repositorio->guardarBorrador(
            $idAutor,
            $idSeccion,
            $titulo,
            $nombreCientifico,
            $fotoUrl,
            $areasHabitat,
            $dieta,
            $horasActivas,
            $camposExtraJson
        );

        return ["id_borrador" => $idBorrador, "mensaje" => "Borrador guardado correctamente"];
    }

    public function obtenerBorradorPorAutor(int $idAutor): ?array {
        $repositorio = PublicacionRepositorio::getInstance();
        $borrador = $repositorio->obtenerBorradorPorAutor($idAutor);

        if ($borrador === null) {
            return null;
        }

        $camposExtra = json_decode($borrador["campos_extra"] ?? '[]', true) ?: [];

        return [
            "id_borrador" => (int) $borrador["id_borrador"],
            "autor" => (int) $borrador["id_autor"],
            "seccion" => $borrador["id_seccion"] !== null ? (int) $borrador["id_seccion"] : null,
            "titulo" => $borrador["titulo"] ?? '',
            "nombreCientifico" => $borrador["nombre_cientifico"] ?? '',
            "foto" => $borrador["foto_url"] ?? '',
            "areasHabitat" => $borrador["areas_habitat"] ?? '',
            "dieta" => $borrador["dieta"] ?? '',
            "horasActivas" => $borrador["horas_activas"] ?? '',
            "camposExtra" => $camposExtra,
            "fechaModificacion" => $borrador["fecha_modificacion"],
            "estado" => "BORRADOR",
        ];
    }

    public function eliminarBorradorPorAutor(int $idAutor): void {
        $repositorio = PublicacionRepositorio::getInstance();
        $repositorio->eliminarBorradorPorAutor($idAutor);
    }

    public function listarReportes(): array {
        $repositorio = PublicacionRepositorio::getInstance();
        return $repositorio->listarReportes();
    }
 
    public function resolverReporte(int $idReporte): void {
        $repositorio = PublicacionRepositorio::getInstance();
 
        $reporte = $repositorio->obtenerReportePorId($idReporte);
        if ($reporte === null) {
            throw new Exception("No existe un reporte con ese id");
        }
        if ($reporte['resuelto']) {
            throw new Exception("El reporte ya estaba marcado como resuelto");
        }
 
        $repositorio->resolverReporte($idReporte);
    }


    


}
?>