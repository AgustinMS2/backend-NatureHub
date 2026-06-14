<?php
include_once __DIR__ . "/../../servicios/Interfaces/IPublicacionController.php";
include_once __DIR__ . "/../../logica/manejadores/PublicacionRepositorio.php";

class PublicacionController implements IPublicacionController {

    public function altaPublicacion(DTPublicacion $dtp): void {
        $this->validarPublicacion($dtp);

        $repositorio = PublicacionRepositorio::getInstance();
        if ($repositorio->obtenerPublicacionTitulo($dtp->getTitulo()) !== null) {
            throw new Exception("Ya existe una publicacion con ese titulo");
        }

        $publicacion = new Publicacion(
            $repositorio->obtenerSiguienteId(),
            $dtp->getTitulo(),
            $dtp->getFoto(),
            $dtp->getNombreCientifico(),
            $dtp->getAreasHabitat(),
            $dtp->getDieta(),
            $dtp->getHorasActivas(),
            EstadoPublicacion::PENDIENTE_REVISION,
            new DateTime(),
            new DateTime(),
            $dtp->getAutor(),
            $this->crearCamposExtra($dtp->getCamposExtra()),
            $dtp->getSeccion()
        );

        $repositorio->agregarPublicacion($publicacion);
    }

    public function bajaPublicacion(int $id): void {
        if ($id <= 0) {
            throw new Exception("Id de publicacion invalido");
        }

        $repositorio = PublicacionRepositorio::getInstance();
        if ($repositorio->obtenerPublicacionId($id) === null) {
            throw new Exception("No existe una publicacion con ese id");
        }

        $repositorio->eliminarPublicacion($id);
    }

    public function modificarPublicacion(DTPublicacion $dtp): void {
        if ($dtp->getId() <= 0) {
            throw new Exception("Id de publicacion invalido");
        }
        $this->validarPublicacion($dtp);

        $repositorio = PublicacionRepositorio::getInstance();
        if ($repositorio->obtenerPublicacionId($dtp->getId()) === null) {
            throw new Exception("No existe una publicacion con ese id");
        }

        $publicacion = new Publicacion(
            $dtp->getId(),
            $dtp->getTitulo(),
            $dtp->getFoto(),
            $dtp->getNombreCientifico(),
            $dtp->getAreasHabitat(),
            $dtp->getDieta(),
            $dtp->getHorasActivas(),
            EstadoPublicacion::PENDIENTE_REVISION,
            new DateTime(),
            new DateTime(),
            $dtp->getAutor(),
            $this->crearCamposExtra($dtp->getCamposExtra()),
            $dtp->getSeccion()
        );

        $repositorio->modificarPublicacion($publicacion);
    }

    public function listarPublicaciones(): array {
        return array_map(
            fn(Publicacion $publicacion): DTPublicacion => $this->publicacionADTO($publicacion),
            PublicacionRepositorio::getInstance()->listarPublicaciones()
        );
    }

    public function listarPublicacionesPropias(int $id): array {
        if ($id <= 0) {
            throw new Exception("Id de usuario invalido");
        }

        return array_map(
            fn(Publicacion $publicacion): DTPublicacion => $this->publicacionADTO($publicacion),
            PublicacionRepositorio::getInstance()->listarPublicacionesPropias($id)
        );
    }

    public function agregarCampoExtra(DTCampoExtra $dtc): void {
        if (($dtc->getIdPublicacion() ?? 0) <= 0) {
            throw new Exception("Id de publicacion obligatorio");
        }
        if (trim($dtc->getEtiqueta()) === "") {
            throw new Exception("La etiqueta del campo extra es obligatoria");
        }

        PublicacionRepositorio::getInstance()->agregarCampoExtra(
            $dtc->getIdPublicacion(),
            new CampoExtra(0, $dtc->getEtiqueta(), $this->tipoCampoDesdeString($dtc->getTipo()), $dtc->getValor())
        );
    }

    public function eliminarCampoExtra(int $id): void {
        if ($id <= 0) {
            throw new Exception("Id de campo extra invalido");
        }

        PublicacionRepositorio::getInstance()->eliminarCampoExtra($id);
    }

    public function listarPublicacionFiltro(string $filtro): array {
        return array_map(
            fn(Publicacion $publicacion): DTPublicacion => $this->publicacionADTO($publicacion),
            PublicacionRepositorio::getInstance()->listarPublicacionFiltro($filtro)
        );
    }

    public function moderarPublicacion(): void {
    }

    public function reportarPublicacion(DTReporte $dtr): void {
    }

    private function validarPublicacion(DTPublicacion $dtp): void {
        if (trim($dtp->getTitulo()) === "") {
            throw new Exception("El titulo es obligatorio");
        }
        if (trim($dtp->getNombreCientifico()) === "") {
            throw new Exception("El nombre cientifico es obligatorio");
        }
        if ($dtp->getAutor() <= 0) {
            throw new Exception("El autor es obligatorio");
        }
        if ($dtp->getSeccion() <= 0) {
            throw new Exception("La seccion es obligatoria");
        }
    }

    private function crearCamposExtra(array $dtCampos): array {
        $campos = [];

        foreach ($dtCampos as $dtCampo) {
            if (!$dtCampo instanceof DTCampoExtra || trim($dtCampo->getEtiqueta()) === "") {
                continue;
            }

            $campos[] = new CampoExtra(
                0,
                $dtCampo->getEtiqueta(),
                $this->tipoCampoDesdeString($dtCampo->getTipo()),
                $dtCampo->getValor()
            );
        }

        return $campos;
    }

    private function publicacionADTO(Publicacion $publicacion): DTPublicacion {
        return new DTPublicacion(
            $publicacion->getId(),
            $publicacion->getTitulo(),
            $publicacion->getFoto(),
            $publicacion->getNombreCientifico(),
            $publicacion->getAreasHabitat(),
            $publicacion->getDieta(),
            $publicacion->getHorasActivas(),
            $publicacion->getEstado()->value,
            $publicacion->getFechaCreacion()->format("Y-m-d H:i:s"),
            $publicacion->getFechaUltimaModificacion()->format("Y-m-d H:i:s"),
            $publicacion->getAutor(),
            array_map(fn(CampoExtra $campo): DTCampoExtra => new DTCampoExtra(
                $campo->getId(),
                $publicacion->getId(),
                $campo->getEtiqueta(),
                $campo->getValor(),
                $campo->getTipo()->name
            ), $publicacion->getCamposExtra()),
            $publicacion->getSeccion()
        );
    }

    private function tipoCampoDesdeString(string $tipo): TipoCampo {
        return match (strtoupper($tipo)) {
            "BOOLEANO" => TipoCampo::BOOLEANO,
            "NUMERICO", "NUMÉRICO" => TipoCampo::NUMERICO,
            "FECHA" => TipoCampo::FECHA,
            default => TipoCampo::TEXTO
        };
    }
}
