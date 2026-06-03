<?php

class DTSesion {
    private ?int $id;
    private ?string $token;
    private ?string $fechaInicio;
    private ?string $fechaFin;
    private ?bool $activa;
    private ?int $usuario;

    public function __construct(?int $id, ?int $usuario, ?string $token, ?bool $activa, ?string $fechaInicio, ?string $fechaFin) {
        $this->id = $id;
        $this->usuario = $usuario;
        $this->token = $token;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->activa = $activa;
    }

    public function getUsuario(): ?int {
        return $this->usuario;
    }
    public function setUsuario(?int $usuario): void {
        $this->usuario = $usuario;
    }

    public function getToken(): ?string {
        return $this->token;
    }
    public function setToken(?string $token): void {
        $this->token = $token;
    }

    public function getFechaInicio(): ?string {
        return $this->fechaInicio;
    }
    public function setFechaInicio(?string $fechaInicio): void {
        $this->fechaInicio = $fechaInicio;
    }

    public function getFechaFin(): ?string {
        return $this->fechaFin;
    }
    public function setFechaFin(?string $fechaFin): void {
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