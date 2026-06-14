<?php

class DTCampoExtra {
    private ?int $id;
    private ?int $idPublicacion;
    private string $etiqueta;
    private string $valor;
    private string $tipo;

    public function __construct(?int $id, ?int $idPublicacion, string $etiqueta, string $valor, string $tipo) {
        $this->id = $id;
        $this->idPublicacion = $idPublicacion;
        $this->etiqueta = $etiqueta;
        $this->valor = $valor;
        $this->tipo = $tipo;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getIdPublicacion(): ?int {
        return $this->idPublicacion;
    }

    public function getEtiqueta(): string {
        return $this->etiqueta;
    }

    public function getValor(): string {
        return $this->valor;
    }

    public function getTipo(): string {
        return $this->tipo;
    }
}
