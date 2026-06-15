<?php

class Administrador extends Moderador {

    public function __construct(int $id, string $nombre, string $apellido, string $email, string $passwordHash, bool $activo, DateTime $fechaRegistro, array $reportes, array $publicaciones, ?string $sexo, ?DateTime $fechaNacimiento, ?string $pais, ?string $bio, ?string $fotoUrl) {
        parent::__construct($id, $nombre, $apellido, $email, $passwordHash, $activo, $fechaRegistro, $reportes, $publicaciones, $sexo, $fechaNacimiento, $pais, $bio, $fotoUrl);
    }

}
?>