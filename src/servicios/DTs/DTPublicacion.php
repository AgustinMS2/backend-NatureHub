<?php

 class DTPublicacion{
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
    }

    public function getId(): int {
        return $this->id;
    }
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getSeccion(): int {
        return $this->seccion;
    }
    public function setSeccion(int $seccion): void {
        $this->seccion = $seccion;
    }

    public function getAutor(): int {
        return $this->autor;
    }
    public function setAutor(int $autor): void {
        $this->autor = $autor;
    }

    public function getTitulo(): string {
        return $this->titulo;
    }
    public function setTitulo(string $titulo): void {
        $this->titulo = $titulo;
    }

    public function getNombreCientifico(): string {
        return $this->nombreCientifico;
    }
    public function setNombreCientifico(string $nombreCientifico): void {
        $this->nombreCientifico = $nombreCientifico;
    }

    public function getFoto(): string {
        return $this->foto;
    }
    public function setFoto(string $foto): void {
        $this->foto = $foto;
    }

    public function getAreasHabitat(): array {
        return $this->areasHabitat;
    }
    public function setAreasHabitat(array $areasHabitat): void {
        $this->areasHabitat = [$areasHabitat];
    }

    public function getDieta(): string {
        return $this->dieta;
    }
    public function setDieta(string $dieta): void {
        $this->dieta = $dieta;
    }

    public function getHorasActivas(): string {
        return $this->horasActivas;
    }
    public function setHorasActivas(string $horasActivas): void {
        $this->horasActivas = $horasActivas;
    }

    public function getEstado(): ?string {
        return $this->estado;
    }
    public function setEstado(?string $estado): void {
        $this->estado = $estado;
    }

    public function getFechaCreacion(): ?string {
        return $this->fechaCreacion;
    }
    public function setFechaCreacion(?string $fechaCreacion): void {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function getFechaUltimaModificacion(): ?string {
        return $this->fechaUltimaModificacion;
    }
    public function setFechaUltimaModificacion(?    string $fechaUltimaModificacion): void {
        $this->fechaUltimaModificacion = $fechaUltimaModificacion;
    }
    
 }
?>