<?php

class DTPublicacion {
    protected int $id;
    protected int $seccion;
    protected int $autor;
    protected string $titulo;
    protected string $nombreCientifico;
    protected string $foto;
    protected array $areasHabitat;
    protected string $dieta;
    protected string $horasActivas;
    protected ?string $estado;
    protected ?string $fechaCreacion;
    protected ?string $fechaUltimaModificacion;
    protected array $camposExtra;

    public function __construct(int $id, string $titulo, string $foto, string $nombreCientifico, array $areasHabitat, string $dieta, string $horasActivas, ?string $estado, ?string $fechaCreacion, ?string $fechaUltimaModificacion, int $autor, array $camposExtra, int $seccion) {
        $this->id = $id;
        $this->seccion = $seccion;
        $this->autor = $autor;
        $this->titulo = $titulo;
        $this->nombreCientifico = $nombreCientifico;
        $this->foto = $foto;
        $this->areasHabitat = $areasHabitat;
        $this->dieta = $dieta;
        $this->horasActivas = $horasActivas;
        $this->estado = $estado;
        $this->fechaCreacion = $fechaCreacion;
        $this->fechaUltimaModificacion = $fechaUltimaModificacion;
        $this->camposExtra = $camposExtra;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getSeccion(): int {
        return $this->seccion;
    }

    public function getAutor(): int {
        return $this->autor;
    }

    public function getTitulo(): string {
        return $this->titulo;
    }

    public function getNombreCientifico(): string {
        return $this->nombreCientifico;
    }

    public function getFoto(): string {
        return $this->foto;
    }

    public function getAreasHabitat(): array {
        return $this->areasHabitat;
    }

    public function getDieta(): string {
        return $this->dieta;
    }

    public function getHorasActivas(): string {
        return $this->horasActivas;
    }

    public function getEstado(): ?string {
        return $this->estado;
    }

    public function getFechaCreacion(): ?string {
        return $this->fechaCreacion;
    }

    public function getFechaUltimaModificacion(): ?string {
        return $this->fechaUltimaModificacion;
    }

    public function getCamposExtra(): array {
        return $this->camposExtra;
    }
}
