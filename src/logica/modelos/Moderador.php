<?php

include_once __DIR__ . "/Usuario.php";

class Moderador extends Usuario {

    public function __construct(int $id, string $nombre, string $apellido, string $email, string $passwordHash, bool $activo, DateTime $fechaRegistro, ?string $sexo, ?DateTime $fechaNacimiento, ?string $pais, ?string $bio, ?string $fotoUrl) {
        parent::__construct($id, $nombre, $apellido, $email, $passwordHash, $activo, $fechaRegistro, $sexo, $fechaNacimiento, $pais, $bio, $fotoUrl);
    }

}
?>