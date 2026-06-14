<?php
include_once __DIR__ . "/../../servicios/Fabrica.php";

class PublicacionEndpoint {
    private IPublicacionController $controlador;

    public function __construct() {
        $this->controlador = Fabrica::getInstance()->getIPublicacionController();
    }

    public function altaPublicacion(): void {
        try {
            $datos = $this->leerJson();
            $dtp = $this->crearDTPublicacion($datos);

            $this->controlador->altaPublicacion($dtp);
            $this->responder(["mensaje" => "Publicacion creada correctamente"], 201);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 400);
        }
    }

    public function bajaPublicacion(): void {
        try {
            $datos = $this->leerJson();
            $this->controlador->bajaPublicacion((int)($datos->id ?? 0));
            $this->responder(["mensaje" => "Publicacion eliminada correctamente"]);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 400);
        }
    }

    public function modificarPublicacion(): void {
        try {
            $datos = $this->leerJson();
            $dtp = $this->crearDTPublicacion($datos, (int)($datos->id ?? 0));

            $this->controlador->modificarPublicacion($dtp);
            $this->responder(["mensaje" => "Publicacion modificada correctamente"]);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 400);
        }
    }

    public function listarPublicaciones(): void {
        try {
            $publicaciones = array_map(fn(DTPublicacion $dtp): array => $this->publicacionAArray($dtp), $this->controlador->listarPublicaciones());
            $this->responder($publicaciones);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 500);
        }
    }

    public function listarPublicacionesPropias(): void {
        try {
            $id = (int)($_GET["id"] ?? 0);
            $publicaciones = array_map(fn(DTPublicacion $dtp): array => $this->publicacionAArray($dtp), $this->controlador->listarPublicacionesPropias($id));
            $this->responder($publicaciones);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 400);
        }
    }

    public function agregarCampoExtra(): void {
        try {
            $datos = $this->leerJson();
            $campo = new DTCampoExtra(
                null,
                isset($datos->idPublicacion) ? (int)$datos->idPublicacion : (isset($datos->id_publicacion) ? (int)$datos->id_publicacion : null),
                trim((string)($datos->etiqueta ?? "")),
                (string)($datos->valor ?? ""),
                (string)($datos->tipo ?? "TEXTO")
            );

            $this->controlador->agregarCampoExtra($campo);
            $this->responder(["mensaje" => "Campo extra agregado correctamente"], 201);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 400);
        }
    }

    public function eliminarCampoExtra(int $id): void {
        try {
            $this->controlador->eliminarCampoExtra($id);
            $this->responder(["mensaje" => "Campo extra eliminado correctamente"]);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 400);
        }
    }

    public function listarPublicacionFiltro(string $filtro): void {
        try {
            $publicaciones = array_map(fn(DTPublicacion $dtp): array => $this->publicacionAArray($dtp), $this->controlador->listarPublicacionFiltro($filtro));
            $this->responder($publicaciones);
        } catch (Throwable $e) {
            $this->responderError($e->getMessage(), 400);
        }
    }

    private function leerJson(): object {
        $raw = file_get_contents("php://input");
        $datos = json_decode($raw ?: "{}");

        if (!is_object($datos)) {
            throw new InvalidArgumentException("El cuerpo debe ser JSON valido");
        }

        return $datos;
    }

    private function crearDTPublicacion(object $datos, int $id = 0): DTPublicacion {
        return new DTPublicacion(
            $id,
            trim((string)($datos->titulo ?? "")),
            (string)($datos->foto ?? $datos->fotoUrl ?? ""),
            trim((string)($datos->nombreCientifico ?? "")),
            $this->normalizarAreas($datos->areasHabitat ?? []),
            trim((string)($datos->dieta ?? "")),
            trim((string)($datos->horasActivas ?? "")),
            null,
            null,
            null,
            (int)($datos->autor ?? 0),
            $this->normalizarCamposExtra($datos->camposExtra ?? []),
            (int)($datos->seccion ?? 0)
        );
    }

    private function normalizarAreas(mixed $areas): array {
        if (is_array($areas)) {
            return array_values(array_filter(array_map(fn($area) => trim((string)$area), $areas)));
        }

        if (is_string($areas)) {
            return array_values(array_filter(array_map("trim", explode(",", $areas))));
        }

        return [];
    }

    private function normalizarCamposExtra(mixed $campos): array {
        if (!is_array($campos)) {
            return [];
        }

        $resultado = [];
        foreach ($campos as $campo) {
            if (!is_object($campo)) {
                continue;
            }

            $etiqueta = trim((string)($campo->etiqueta ?? ""));
            if ($etiqueta === "") {
                continue;
            }

            $resultado[] = new DTCampoExtra(
                isset($campo->id) ? (int)$campo->id : null,
                isset($campo->idPublicacion) ? (int)$campo->idPublicacion : null,
                $etiqueta,
                (string)($campo->valor ?? ""),
                (string)($campo->tipo ?? "TEXTO")
            );
        }

        return $resultado;
    }

    private function publicacionAArray(DTPublicacion $dtp): array {
        return [
            "id" => $dtp->getId(),
            "titulo" => $dtp->getTitulo(),
            "foto" => $dtp->getFoto(),
            "nombreCientifico" => $dtp->getNombreCientifico(),
            "areasHabitat" => $dtp->getAreasHabitat(),
            "dieta" => $dtp->getDieta(),
            "horasActivas" => $dtp->getHorasActivas(),
            "estado" => $dtp->getEstado(),
            "fechaCreacion" => $dtp->getFechaCreacion(),
            "fechaUltimaModificacion" => $dtp->getFechaUltimaModificacion(),
            "autor" => $dtp->getAutor(),
            "seccion" => $dtp->getSeccion(),
            "camposExtra" => array_map(fn(DTCampoExtra $campo): array => [
                "id" => $campo->getId(),
                "idPublicacion" => $campo->getIdPublicacion(),
                "etiqueta" => $campo->getEtiqueta(),
                "valor" => $campo->getValor(),
                "tipo" => $campo->getTipo()
            ], $dtp->getCamposExtra())
        ];
    }

    private function responder(mixed $datos, int $codigo = 200): void {
        http_response_code($codigo);
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    }

    private function responderError(string $mensaje, int $codigo): void {
        $this->responder(["error" => $mensaje], $codigo);
    }
}
