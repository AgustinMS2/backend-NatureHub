<?php
include_once __DIR__ . "/../../servicios/Fabrica.php";

class PublicacionEndpoint {
    private IPublicacionController $controlador;

    public function __construct() {
        $this->controlador = Fabrica::getInstance()->getIPublicacionController();
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/altaPublicacion
    public function altaPublicacion(): void{
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isFormData = strpos($contentType, 'multipart/form-data') !== false;

        try{
            if ($isFormData) {
                $datos = (object) $_POST;
                $fotoUrl = $datos->fotoUrl ?? null;

                if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $file = $_FILES['foto'];
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('article_', true) . ($extension ? ".{$extension}" : '');
                    $destination = $uploadDir . $filename;
                    if (!move_uploaded_file($file['tmp_name'], $destination)) {
                        throw new Exception("No se pudo guardar la imagen.");
                    }
                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                    $fotoUrl = sprintf('%s://%s%s/uploads/%s', $scheme, $host, $path, $filename);
                }

                $camposExtra = json_decode($datos->camposExtra ?? '[]');
                $areasHabitat = json_decode($datos->areasHabitat ?? '[]');

                $dtp = new DTPublicacion(
                    0,
                    $datos->titulo,
                    $fotoUrl,
                    $datos->nombreCientifico,
                    $areasHabitat,
                    $datos->dieta,
                    $datos->horasActivas,
                    null, null, null,
                    (int)$datos->autor,
                    $camposExtra,
                    (int)$datos->seccion,
                    [], []
                );
            } else {
                $datos = json_decode(file_get_contents("php://input"));
                $dtp = new DTPublicacion(
                    0,
                    $datos->titulo,
                    $datos->foto,
                    $datos->nombreCientifico,
                    $datos->areasHabitat,
                    $datos->dieta,
                    $datos->horasActivas,
                    null, null, null,
                    $datos->autor,
                    $datos->camposExtra,
                    $datos->seccion,
                    [], []
                );
            }

            $this->controlador->altaPublicacion($dtp);
            http_response_code(201);
            echo json_encode(["mensaje" => "Publicacion creada correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }

    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/bajaPublicacion
    public function bajaPublicacion(): void{
        $dato = json_decode(file_get_contents("php://input"));

        $id = $dato->id;

        try {
            $this->controlador->bajaPublicacion($id);
            http_response_code(201);
            echo json_encode(["mensaje" => "Publicacion eliminada correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
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

        try {
            $this->controlador->modificarPublicacion($dtp);
            http_response_code(201);
            echo json_encode(["mensaje" => "Publicacion modificada correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }

    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/listarPublicaciones
    public function listarPublicaciones(): void{
        try{
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
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }

    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/listarPublicacionesPropias
    public function listarPublicacionesPropias(): void{
        $dato = json_decode(file_get_contents("php://input"));

        $id = $dato->id;

        try{
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
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
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

        try {
            $this->controlador->agregarCampoExtra($dtc);
            http_response_code(201);
            echo json_encode(["mensaje" => "Campo Extra agregado correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/eliminarCampoExtra
    public function eliminarCampoExtra(): void{

        $dato = json_decode(file_get_contents("php://input"));

        $idCampo = $dato->idCampo;

        try {
            $this->controlador->eliminarCampoExtra($idCampo);
            http_response_code(201);
            echo json_encode(["mensaje" => "Campo Extra eliminado correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/modificarCampoExtra
    public function modificarCampoExtra(): void{
        $datos = json_decode(file_get_contents("php://input"));
;
        $dtc = new DTCampoExtra(
            $datos->id,
            $datos->idPublicacion,
            $datos->etiqueta,
            $datos->valor,
            DTTipoCampo::from($datos->tipo)
        );

        try {
            $this->controlador->modificarCampoExtra($dtc);
            http_response_code(201);
            echo json_encode(["mensaje" => "Campo Extra modificado correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/listarPublicacionesPendientes
    public function listarPublicacionesPendientes(): void{
        try{
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
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/listarPublicacionesPorSeccion
    public function listarPublicacionesPorSeccion(): void{

        $dato = json_decode(file_get_contents("php://input"));

        $seccion = $dato->seccion;

        try{
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
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
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

        try {
            $this->controlador->reportePublicacion($dtr);
            http_response_code(201);
            echo json_encode(["mensaje" => "Reporte creado correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/listarPublicacionFiltro
    public function listarPublicacionFiltro(string $filtro): void{
        
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/moderarPublicacion
    public function moderarPublicacion(): void{
        $datos = json_decode(file_get_contents("php://input"));
 
        $dtm = new DTModera(
            null,
            $datos->motivoRechazo ?? null,
            $datos->resultado,
            $datos->idModerador,
            $datos->idPublicacion,
            null
        );

        try {
            $this->controlador->moderarPublicacion($dtm);
            http_response_code(201);
            echo json_encode(["mensaje" => "Publicación moderada correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

}

?>