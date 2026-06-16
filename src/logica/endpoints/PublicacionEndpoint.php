<?php
include_once __DIR__ . "/../../servicios/Fabrica.php";

class PublicacionEndpoint {
    private IPublicacionController $controlador;

    public function __construct() {
        $this->controlador = Fabrica::getInstance()->getIPublicacionController();
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/altaPublicacion
    public function altaPublicacion(): void{
        $datos = json_decode(file_get_contents("php://input"));

        //$d = [$datos->areasHabitat];

        //var_dump($datos);
        //die();

        $dtp = new DTPublicacion(
            0,
            $datos->titulo,
            $datos->foto,
            $datos->nombreCientifico,
            $datos->areasHabitat,
            $datos->dieta,
            $datos->horasActivas,
            null,
            null,
            null,
            $datos->autor,
            $datos->camposExtra, //CamposExtra
            $datos->seccion,
            [], //Moderaciones
            [] //Reportes
        );

        $this->controlador->altaPublicacion($dtp);

        http_response_code(201);
        echo json_encode(["mensaje" => "Publicacion creada correctamente"]);
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/bajaPublicacion
    public function bajaPublicacion(): void{
        $dato = json_decode(file_get_contents("php://input"));

        $id = $dato->id;

        $this->controlador->bajaPublicacion($id);

        http_response_code(201);
        echo json_encode(["mensaje" => "Publicacion eliminada correctamente"]);
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/modificarPublicacion
    public function modificarPublicacion(): void{
        $datos = json_decode(file_get_contents("php://input"));

        $dtp = new DTPublicacion(
            0,
            $datos->titulo,
            $datos->foto,
            $datos->nombreCientifico,
            $datos->areasHabitat,
            $datos->dieta,
            $datos->horasActivas,
            null,
            null,
            null,
            $datos->autor,
            [],
            $datos->seccion
        );

        $this->controlador->modificarPublicacion($dtp);

        http_response_code(201);
        echo json_encode(["mensaje" => "Publicacion modificada correctamente"]);

    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/listarPublicaciones
    public function listarPublicaciones(): void{
        $publicaciones = $this->controlador->listarPublicaciones();

        $resultado = [];
        foreach ($publicaciones as $dpu) {
            $resultado[] = [
                "id" => $dpu->getId(),
                "titulo" => $dpu->getTitulo(),
                "foto" => $dpu->getFoto(),
                "nombreCientifico" => $dpu->getNombreCientifico(),
                "areasHabitat" => $dpu->getAreasHabitat(),
                "dieta" => $dpu->getDieta(),
                "horasActivas" => $dpu->getHorasActivas(),
                "estado" => $dpu->getEstado(),
                "fechaCreacion" => $dpu->getFechaCreacion(),
                "fechaUltimaModificacion" => $dpu->getFechaUltimaModificacion(),
                "autor" => $dpu->getAutor(),
                "camposExtra" => $dpu->getCamposExtra(),
                "seccion" => $dpu->getSeccion()
            ];
        }
        
        http_response_code(200);
        echo json_encode($resultado);

    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/listarPublicacionesPropias
    public function listarPublicacionesPropias(): void{
        $dato = json_decode(file_get_contents("php://input"));

        $id = $dato->id;

        $publicaciones = $this->controlador->listarPublicacionesPropias($id);

        $resultado = [];
        foreach ($publicaciones as $dpu) {
            $resultado[] = [
                "id" => $dpu->getId(),
                "titulo" => $dpu->getTitulo(),
                "foto" => $dpu->getFoto(),
                "nombreCientifico" => $dpu->getNombreCientifico(),
                "areasHabitat" => $dpu->getAreasHabitat(),
                "dieta" => $dpu->getDieta(),
                "horasActivas" => $dpu->getHorasActivas(),
                "estado" => $dpu->getEstado(),
                "fechaCreacion" => $dpu->getFechaCreacion(),
                "fechaUltimaModificacion" => $dpu->getFechaUltimaModificacion(),
                "autor" => $dpu->getAutor(),
                "camposExtra" => $dpu->getCamposExtra(),
                "seccion" => $dpu->getSeccion()
            ];
        }
        
        http_response_code(200);
        echo json_encode($resultado);
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/agregarCampoExtra
    public function agregarCampoExtra(): void{
        $datos = json_decode(file_get_contents("php://input"));

        $dtc = new DTCampoExtra(
            0,
            $datos->idPublicacion,
            $datos->etiqueta,
            $datos->valor,
            DTTipoCampo::from($datos->tipo)
        );

        $this->controlador->agregarCampoExtra($dtc);

         http_response_code(201);
        echo json_encode(["mensaje" => "Campo Extra agregado correctamente"]);
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/eliminarCampoExtra
    public function eliminarCampoExtra(): void{

        $dato = json_decode(file_get_contents("php://input"));

        $idCampo = $dato->idCampo;

        $this->controlador->eliminarCampoExtra($idCampo);
        
        http_response_code(201);
        echo json_encode(["mensaje" => "Campo Extra eliminado correctamente"]);
        
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/modificarCampoExtra
    public function modificarCampoExtra(): void{
        $datos = json_decode(file_get_contents("php://input"));

        var_dump($datos->id);
        $dtc = new DTCampoExtra(
            $datos->id,
            $datos->idPublicacion,
            $datos->etiqueta,
            $datos->valor,
            DTTipoCampo::from($datos->tipo)
        );

        $this->controlador->modificarCampoExtra($dtc);

         http_response_code(201);
        echo json_encode(["mensaje" => "Campo Extra modificado correctamente"]);
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/listarPublicacionesPendientes
    public function listarPublicacionesPendientes(): void{
        $publicaciones = $this->controlador->listarPublicacionesPendientes();

        $resultado = [];
        foreach ($publicaciones as $dpu) {
            $resultado[] = [
                "id" => $dpu->getId(),
                "titulo" => $dpu->getTitulo(),
                "foto" => $dpu->getFoto(),
                "nombreCientifico" => $dpu->getNombreCientifico(),
                "areasHabitat" => $dpu->getAreasHabitat(),
                "dieta" => $dpu->getDieta(),
                "horasActivas" => $dpu->getHorasActivas(),
                "estado" => $dpu->getEstado(),
                "fechaCreacion" => $dpu->getFechaCreacion(),
                "fechaUltimaModificacion" => $dpu->getFechaUltimaModificacion(),
                "autor" => $dpu->getAutor(),
                "camposExtra" => $dpu->getCamposExtra(),
                "seccion" => $dpu->getSeccion()
            ];
        }
        
        http_response_code(200);
        echo json_encode($resultado);
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/listarPublicacionesPorSeccion
    public function listarPublicacionesPorSeccion(): void{

        $dato = json_decode(file_get_contents("php://input"));

        $seccion = $dato->seccion;

        $publicaciones = $this->controlador->listarPublicacionesPorSeccion($seccion);

        $resultado = [];
        foreach ($publicaciones as $dpu) {
            $resultado[] = [
                "id" => $dpu->getId(),
                "titulo" => $dpu->getTitulo(),
                "foto" => $dpu->getFoto(),
                "nombreCientifico" => $dpu->getNombreCientifico(),
                "areasHabitat" => $dpu->getAreasHabitat(),
                "dieta" => $dpu->getDieta(),
                "horasActivas" => $dpu->getHorasActivas(),
                "estado" => $dpu->getEstado(),
                "fechaCreacion" => $dpu->getFechaCreacion(),
                "fechaUltimaModificacion" => $dpu->getFechaUltimaModificacion(),
                "autor" => $dpu->getAutor(),
                "camposExtra" => $dpu->getCamposExtra(),
                "seccion" => $dpu->getSeccion()
            ];
        }
        
        http_response_code(200);
        echo json_encode($resultado);
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/reportePublicacion
    public function reportePublicacion(): void{

        $datos = json_decode(file_get_contents("php://input"));

        $idPublicacion = $datos->idPublicacion;
        $idUsuario = $datos->idUsuario;
        $motivo = $datos->motivo;
        $fecha = new DateTime();
        $resuelto = false;

        $dtr = new DTReporte(
            0,
            $idPublicacion,
            $idUsuario,
            $motivo,
            $fecha,
            $resuelto
        );

        $this->controlador->reportePublicacion($dtr);

        http_response_code(201);
        echo json_encode(["mensaje" => "Reporte creado correctamente"]);

    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/listarPublicacionFiltro
    public function listarPublicacionFiltro(string $filtro): void{
        
    }

}

?>