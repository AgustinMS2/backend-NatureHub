<?php

class Usuario {
    protected int $id;
    protected string $nombre;
    protected string $apellido;
    protected string $email;
    protected string $passwordHash;
    protected bool $activo;
    protected DateTime $fechaRegistro;
    protected Sesion $sesion;
    protected array $reportes;
    protected array $publicaciones;

    public function __construct(int $id, string $nombre, string $apellido, string $email, string $passwordHash, bool $activo, DateTime $fechaRegistro, array $reportes, array $publicaciones) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->activo = $activo;
        $this->fechaRegistro = $fechaRegistro;
        $this->reportes = $reportes;
        $this->publicaciones = $publicaciones;
    }

    public function getId(): int {
        return $this->id;
    }
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getNombre(): string {
        return $this->nombre;
    }
    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }

    public function getApellido(): string {
        return $this->apellido;
    }
    public function setApellido(string $apellido): void {
        $this->apellido = $apellido;
    }

    public function getEmail(): string {
        return $this->email;
    }
    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function getPasswordHash(): string {
        return $this->passwordHash;
    }
    public function setPasswordHash(string $passwordHash): void {
        $this->passwordHash = $passwordHash;
    }

    public function getActivo(): bool {
        return $this->activo;
    }
    public function setActivo(bool $activo): void {
        $this->activo = $activo;
    }

    public function getFechaRegistro(): DateTime {
        return $this->fechaRegistro;
    }
    public function setFechaRegistro(DateTime $fechaRegistro): void {
        $this->fechaRegistro = $fechaRegistro;
    }

    public function getSesion(): Sesion {
        return $this->sesion;
    }
    public function setSesion(Sesion $sesion): void {
        $this->sesion = $sesion;
    }

    public function getReportes(): array {
        return $this->reportes;
    }
    public function setReportes(array $reportes): void {
        $this->reportes = $reportes;
    }

    public function getPublicaciones(): array {
        return $this->publicaciones;
    }
    public function setPublicaciones(array $publicaciones): void {
        $this->publicaciones = $publicaciones;
    }

}  
?>