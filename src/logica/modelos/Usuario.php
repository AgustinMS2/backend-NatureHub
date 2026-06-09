<?php

class Usuario {
    protected int $id;
    protected string $nombre;
    protected string $apellido;
    protected string $email;
    protected string $passwordHash;
    protected bool $activo;
    protected DateTime $fechaRegistro;
    protected ?string $sexo;
    protected ?DateTime $fechaNacimiento;
    protected ?string $pais;
    protected ?string $bio;
    protected ?string $fotoUrl;
    protected Sesion $sesion;
    protected array $reportes;
    protected array $publicaciones;

    public function __construct(int $id, string $nombre, string $apellido, string $email, string $passwordHash, bool $activo, DateTime $fechaRegistro, array $reportes, array $publicaciones, ?string $sexo, ?DateTime $fechaNacimiento, ?string $pais, ?string $bio, ?string $fotoUrl) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->activo = $activo;
        $this->fechaRegistro = $fechaRegistro;
        $this->reportes = $reportes;
        $this->publicaciones = $publicaciones;
        $this->sexo = $sexo;
        $this->fechaNacimiento = $fechaNacimiento;
        $this->pais = $pais;
        $this->bio = $bio;
        $this->fotoUrl = $fotoUrl;
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

    public function getSexo(): ?string {
        return $this->sexo;
    }
    public function setSexo(?string $sexo): void {
        $this->sexo = $sexo;
    }

    public function getFechaNacimiento(): ?DateTime {
        return $this->fechaNacimiento;
    }
    public function setFechaNacimiento(?DateTime $fechaNacimiento): void {
        $this->fechaNacimiento = $fechaNacimiento;
    }

    public function getPais(): ?string {
        return $this->pais;
    }
    public function setPais(?string $pais): void {
        $this->pais = $pais;
    }

    public function getBio(): ?string {
        return $this->bio;
    }
    public function setBio(?string $bio): void {
        $this->bio = $bio;
    }

    public function getFotoUrl(): ?string {
        return $this->fotoUrl;
    }
    public function setFotoUrl(?string $fotoUrl): void {
        $this->fotoUrl = $fotoUrl;
    }

}
?>