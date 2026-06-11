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
            [],
            "seccion" => $dpu->getSeccion()
        ];
    }
    
    http_response_code(200);
    echo json_encode($resultado);

    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/listarPublicacionesPropias?id=X
    public function listarPublicacionesPropias(): void{
        $id = (int)($_GET['id'] ?? 0);

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
            [],
            "seccion" => $dpu->getSeccion()
            ];
        }
        
        http_response_code(200);
        echo json_encode($resultado);
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/agregarCampoExtra
    public function agregarCampoExtra(): void{
        
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/eliminarCampoExtra
    public function eliminarCampoExtra(int $id): void{
        
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/listarPublicacionFiltro
    public function listarPublicacionFiltro(string $filtro): void{
        
    }

}

?>