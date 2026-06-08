<?php

class DTUsuario {
    private ?int $id;
    private ?string $nombre;
    private ?string $apellido;
    private ?string $email;
    private ?string $password;
    private ?bool $activo;
    private ?string $fechaRegistro;
    private ?string $sexo;
    private ?string $fechaNacimiento;
    private ?string $pais;
    private ?string $bio;

    public function __construct(?int $id, ?string $nombre, ?string $apellido, ?string $email, ?string $password, ?bool $activo, ?string $fechaRegistro, ?string $sexo = null, ?string $fechaNacimiento = null, ?string $pais = null, ?string $bio = null) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->email = $email;
        $this->password = $password;
        $this->activo = $activo;
        $this->fechaRegistro = $fechaRegistro;
        $this->sexo = $sexo;
        $this->fechaNacimiento = $fechaNacimiento;
        $this->pais = $pais;
        $this->bio = $bio;
    }

    public function getId(): ?int {
        return $this->id;
    }
    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getNombre(): ?string {
        return $this->nombre;
    }
    public function setNombre(?string $nombre): void {
        $this->nombre = $nombre;
    }

    public function getApellido(): ?string {
        return $this->apellido;
    }
    public function setApellido(?string $apellido): void {
        $this->apellido = $apellido;
    }

    public function getEmail(): ?string {
        return $this->email;
    }
    public function setEmail(?string $email): void {
        $this->email = $email;
    }

    public function getPassword(): ?string {
        return $this->password;
    }
    public function setPassword(?string $password): void {
        $this->password = $password;
    }

    public function getActivo(): ?bool {
        return $this->activo;
    }
    public function setActivo(?bool $activo): void {
        $this->activo = $activo;
    }

    public function getFechaRegistro(): ?string {
        return $this->fechaRegistro;
    }
    public function setFechaRegistro(?string $fechaRegistro): void {
        $this->fechaRegistro = $fechaRegistro;
    }

    public function getSexo(): ?string {
        return $this->sexo;
    }
    public function setSexo(?string $sexo): void {
        $this->sexo = $sexo;
    }

    public function getFechaNacimiento(): ?string {
        return $this->fechaNacimiento;
    }
    public function setFechaNacimiento(?string $fechaNacimiento): void {
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

}
?>