<?php

class Modera {
    private int $id;
    private ?string $motivoRechazo;
    private ResultadoRevision $resultado;
    private DateTime $fechaRevision;
    private int $moderador;
    private int $publicacion;

    public function __construct(int $id, ?string $motivoRechazo, ResultadoRevision $resultado, int $moderador, int $publicacion, DateTime $fechaRevision) {
        $this->id = $id;
        $this->motivoRechazo = $motivoRechazo;
        $this->resultado = $resultado;
        $this->moderador = $moderador;
        $this->publicacion = $publicacion;
        $this->fechaRevision = $fechaRevision;
    }

    public function getId(): int {
        return $this->id;
    }
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getMotivoRechazo(): ?string {
        return $this->motivoRechazo;
    }
    public function setMotivoRechazo(?string $motivoRechazo): void {
        $this->motivoRechazo = $motivoRechazo;
    }

    public function getPublicacion(): int {
        return $this->publicacion;
    }
    public function setPublicacion(int $publicacion): void {
        $this->publicacion = $publicacion;
    }

    public function getModerador(): int {
        return $this->moderador;
    }
    public function setModerador(int $moderador): void {
        $this->moderador = $moderador;
    }

    public function getResultado(): ResultadoRevision {
        return $this->resultado;
    }
    public function setResultado(ResultadoRevision $resultado): void {
        $this->resultado = $resultado;
    }

    public function getFechaRevision(): DateTime {
        return $this->fechaRevision;
    }
    public function setFechaRevision(DateTime $fechaRevision): void {
        $this->fechaRevision = $fechaRevision;
    }
}
?>