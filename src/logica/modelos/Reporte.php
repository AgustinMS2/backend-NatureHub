<?php

class Reporte {
    private int $id;
    private int $idPublicacion;
    private int $idUsuario;
    private string $motivo;
    private DateTime $fecha;
    private bool $resuelto;

    public function __construct(int $id, int $idPublicacion, int $idUsuario, string $motivo, DateTime $fecha, bool $resuelto) {
        $this->id = $id;
        $this->idPublicacion = $idPublicacion;
        $this->idUsuario = $idUsuario;
        $this->motivo = $motivo;
        $this->fecha = $fecha;
        $this->resuelto = $resuelto;
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

    public function getIdUsuario(): int {
        return $this->idUsuario;
    }
    public function setIdUsuario(int $idUsuario): void {
        $this->idUsuario = $idUsuario;
    }

    public function getMotivo(): string {
        return $this->motivo;
    }
    public function setMotivo(string $motivo): void {
        $this->motivo = $motivo;
    }

    public function getFecha(): DateTime {
        return $this->fecha;
    }
    public function setFecha(DateTime $fecha): void {
        $this->fecha = $fecha;
    }

    public function getResuelto(): bool {
        return $this->resuelto;
    }
    public function setResuelto(bool $resuelto): void {
        $this->resuelto = $resuelto;
    }
}
?>