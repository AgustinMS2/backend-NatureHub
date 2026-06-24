<?php
include_once __DIR__ . "/../../servicios/Fabrica.php";
include_once __DIR__ . "/../UrlUtil.php";

class PublicacionEndpoint {
    private IPublicacionController $controlador;
    private ?DTUsuario $usuarioAutenticado;

    public function __construct(?DTUsuario $usuarioAutenticado = null) {
        $this->controlador = Fabrica::getInstance()->getIPublicacionController();
        $this->usuarioAutenticado = $usuarioAutenticado;
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/altaPublicacion
    public function altaPublicacion(): void{
        $datos = (object) $_POST;
        $fotoUrl = $datos->fotoUrl ?? null;

        try{
            if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/publicaciones/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $file = $_FILES['foto'];
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('profile_', true) . ($extension ? ".{$extension}" : '');
                $destination = $uploadDir . $filename;

                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    throw new Exception("No se pudo guardar la imagen de perfil.");
                }

                $fotoUrl = obtenerUrlBase() . "/uploads/publicaciones/{$filename}";
            }
            
            $camposExtra = json_decode($datos->camposExtra ?? '[]');
            $areasHabitat = json_decode($datos->areasHabitat ?? '[]');

            $idAutor = $this->usuarioAutenticado->getId();

            $dtp = new DTPublicacion(
                0,
                $datos->titulo,
                $fotoUrl,
                $datos->nombreCientifico,
                $areasHabitat,
                $datos->dieta,
                $datos->horasActivas,
                null, 
                null, 
                null,
                $idAutor,
                $camposExtra,
                $datos->seccion          
            );
            

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

        if (!($this->usuarioAutenticado instanceof DTAdministrador) && $publicacion->getAutor()!==$this->usuarioAutenticado->getId()) {
            http_response_code(403);
            echo json_encode(["error" => "No tienes permiso para eliminar esta publicación"]);
            return;
        }

        try {
            $this->controlador->bajaPublicacion($id);
            http_response_code(200);
            echo json_encode(["mensaje" => "Publicacion eliminada correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/modificarPublicacion
    public function modificarPublicacion(): void{
        $datos = (object) $_POST;
        $fotoUrl = $datos->fotoUrl ?? null;

        try {
            if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/publicaciones/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $file = $_FILES['foto'];
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('profile_', true) . ($extension ? ".{$extension}" : '');
                $destination = $uploadDir . $filename;

                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    throw new Exception("No se pudo guardar la imagen.");
                }

                $fotoUrl = obtenerUrlBase() . "/uploads/publicaciones/{$filename}";
            }

            $camposExtra = json_decode($datos->camposExtra ?? '[]');
            $areasHabitat = json_decode($datos->areasHabitat ?? '[]');

            $idAutor = $this->usuarioAutenticado->getId();

            $dtp = new DTPublicacion(
                (int) $datos->id,
                $datos->titulo,
                $fotoUrl,
                $datos->nombreCientifico,
                $areasHabitat,
                $datos->dieta,
                $datos->horasActivas,
                null,
                null,
                null,
                $idAutor,
                $camposExtra,
                (int) $datos->seccion
            );

            $resultado = $this->controlador->modificarPublicacion($dtp);
            http_response_code(200);
            echo json_encode([
                "mensaje" => "Publicacion modificada correctamente",
                "estado" => $resultado["estado"]
            ]);
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
        $id = $this->usuarioAutenticado->getId();

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

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/listarPublicacionesTitulo
    public function listarPublicacionesTitulo(): void{
        $dato = json_decode(file_get_contents("php://input"));

        $titulo = $dato->titulo;

        try{
            $publicaciones = $this->controlador->listarPublicacionesTitulo($titulo);

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
        $idUsuario = $this->usuarioAutenticado->getId();
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

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/moderarPublicacion
    public function moderarPublicacion(): void{
        $datos = json_decode(file_get_contents("php://input"));
 
        $idModerador = $this->usuarioAutenticado->getId();

        $dtm = new DTModera(
            null,
            $datos->motivoRechazo ?? null,
            $datos->resultado,
            $idModerador,
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
    
    public function listarSecciones(): void {
        try {
            $secciones = $this->controlador->listarSecciones();
            http_response_code(200);
            echo json_encode($secciones);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/guardarBorrador
    public function guardarBorrador(): void {
        $datos = (object) $_POST;
        $fotoUrl = $datos->fotoUrl ?? $datos->foto ?? null;

        try {
            if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/publicaciones/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $file = $_FILES['foto'];
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('draft_', true) . ($extension ? ".{$extension}" : '');
                $destination = $uploadDir . $filename;

                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                    throw new Exception("No se pudo guardar la imagen del borrador.");
                }

                $fotoUrl = obtenerUrlBase() . "/uploads/publicaciones/{$filename}";
            }

            $camposExtra = json_decode($datos->camposExtra ?? '[]', true) ?: [];
            $areasHabitat = $datos->areasHabitat ?? '';
            if (is_string($areasHabitat) && str_starts_with($areasHabitat, '[')) {
                $decoded = json_decode($areasHabitat, true);
                if (is_array($decoded)) {
                    $areasHabitat = implode(', ', $decoded);
                }
            }

            $idSeccion = isset($datos->seccion) && $datos->seccion !== '' ? (int) $datos->seccion : null;

            $idAutor = $this->usuarioAutenticado->getId();

            $resultado = $this->controlador->guardarBorrador(
                $idAutor,
                $idSeccion,
                $datos->titulo ?? null,
                $datos->nombreCientifico ?? null,
                $fotoUrl,
                $areasHabitat ?: null,
                $datos->dieta ?? null,
                $datos->horasActivas ?? null,
                $camposExtra
            );

            http_response_code(200);
            echo json_encode($resultado);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/obtenerBorrador?idAutor=1
    public function obtenerBorrador(): void {
        $idAutor = $this->usuarioAutenticado->getId();

        try {
            $borrador = $this->controlador->obtenerBorradorPorAutor($idAutor);

            if ($borrador === null) {
                http_response_code(200);
                echo json_encode(null);
                return;
            }

            http_response_code(200);
            echo json_encode($borrador);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/eliminarBorrador
    public function eliminarBorrador(): void {
        $idAutor = $this->usuarioAutenticado->getId();

        try {
            $this->controlador->eliminarBorradorPorAutor($idAutor);
            http_response_code(200);
            echo json_encode(["mensaje" => "Borrador eliminado correctamente"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/listarReportes
    public function listarReportes(): void {
        try {
            $reportes = $this->controlador->listarReportes();
            http_response_code(200);
            echo json_encode($reportes);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
 
    // http://localhost/backend-NatureHub/src/index.php/publicaciones/resolverReporte
    public function resolverReporte(): void {
        $datos = json_decode(file_get_contents("php://input"));
        $idReporte = $datos->idReporte;
 
        try {
            $this->controlador->resolverReporte($idReporte);
            http_response_code(200);
            echo json_encode(["mensaje" => "Reporte marcado como resuelto"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-NatureHub/src/index.php/publicaciones/generarPdfPublicacion?id=1
    public function generarPdfPublicacion(): void {
        $id = (int) ($_GET['id'] ?? 0);

        try {
            if ($id <= 0) {
                throw new Exception("Debe indicar el id de la publicación");
            }

            $resultado = $this->controlador->generarPdfPublicacion($id);

            while (ob_get_level()) {
                ob_end_clean();
            }

            header_remove('Content-Type');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $resultado['filename'] . '"');
            header('Content-Length: ' . strlen($resultado['content']));
            header('Cache-Control: private, max-age=0, must-revalidate');
            http_response_code(200);
            echo $resultado['content'];
        } catch (Exception $e) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // http://localhost/backend-Naturehub/src/index.php/publicaciones/obtenerPublicacionPorId
    public function obtenerPublicacionPorId(): void {
        $datos = json_decode(file_get_contents("php://input"));
        $id = $datos->id;

        try{
            $dpu = $this->controlador->obtenerPublicacionPorId($id);

            $resultado = [
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
            
            http_response_code(200);
            echo json_encode($resultado);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }


}
?>