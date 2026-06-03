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

    public function bajaPublicacion(int $id): void{

    }

    public function modificarPublicacion(): void{

    }

    public function listarPublicaciones(): void{

    }

    public function listadoPublicacionesPropias(int $id): void{

    }

    public function agregarCampoExtra(): void{
        
    }

    public function eliminarCampoExtra(int $id): void{
        
    }

    public function listarPublicacionFiltro(string $filtro): void{
        
    }

}

?>