<?php

include_once __DIR__ . "/Usuario.php";

class Sesion {
    private ?int $id;
    private ?string $token;
    private ?DateTime $fechaInicio;
    private ?DateTime $fechaFin;
    private ?bool $activa;
    private ?Usuario $usuario;

    public function __construct(?int $id, ?Usuario $usuario, ?string $token, ?DateTime $fechaInicio, ?DateTime $fechaFin,  ?bool $activa) {
        $this->id = $id;
        $this->usuario = $usuario;
        $this->token = $token;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->activa = $activa;
    }

    public function getUsuario(): ?Usuario {
        return $this->usuario;
    }
    public function setUsuario(?Usuario $usuario): void {
        $this->usuario = $usuario;
    }

    public function getToken(): ?string {
        return $this->token;
    }
    public function setToken(?string $token): void {
        $this->token = $token;
    }

    public function getFechaInicio(): ?DateTime {
        return $this->fechaInicio;
    }
    public function setFechaInicio(?DateTime $fechaInicio): void {
        $this->fechaInicio = $fechaInicio;
    }

    public function getFechaFin(): ?DateTime {
        return $this->fechaFin;
    }
    public function setFechaFin(?DateTime $fechaFin): void {
        $this->fechaFin = $fechaFin;
    }

    public function getActiva(): ?bool {
        return $this->activa;
    }
    public function setActiva(?bool $activa): void {
        $this->activa = $activa;
    }

    public function getId(): ?int {
        return $this->id;
    }
    public function setId(?int $id): void {
        $this->id = $id;
    }
}
?>