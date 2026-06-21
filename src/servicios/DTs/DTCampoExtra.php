<?php

class DTCampoExtra {
    private int $id;
    private int $idPublicacion;
    private string $etiqueta;
    private string $valor;
    private DTTipoCampo $tipo;

    public function __construct(int $id, int $idPublicacion, string $etiqueta, string $valor, DTTipoCampo $tipo) {
        $this->id = $id;
        $this->idPublicacion = $idPublicacion;
        $this->etiqueta = $etiqueta;
        $this->tipo = $tipo;
        $this->valor = $valor;
    }

    public function getId(): int {
        return $this->id;
    }
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getIdPublicacion(): int {
        return $this->idPublicacion;
    }
    public function setIdPublicacion(int $idPublicacion): void {
        $this->idPublicacion = $idPublicacion;
    }

    public function getEtiqueta(): string {
        return $this->etiqueta;
    }
    public function setEtiqueta(string $etiqueta): void {
        $this->etiqueta = $etiqueta;
    }

    public function getTipo(): DTTipoCampo {
        return $this->tipo;
    }
    public function setTipo(DTTipoCampo $tipo): void {
        $this->tipo = $tipo;
    }

    public function getValor(): string {
        return $this->valor;
    }
    public function setValor(string $valor): void {
        $this->valor = $valor;
    }
}

?>