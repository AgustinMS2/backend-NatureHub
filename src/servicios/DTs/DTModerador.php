<?php

include_once __DIR__ . "/DTUsuario.php";

class DTModerador extends DTUsuario {

    public function __construct(?int $id, ?string $nombre, ?string $apellido, ?string $email, ?string $password, ?bool $activo, ?string $fechaRegistro, ?string $sexo, ?string $fechaNacimiento, ?string $pais, ?string $bio, ?string $fotoUrl) {
        parent::__construct($id, $nombre, $apellido, $email, $password, $activo, $fechaRegistro, $sexo, $fechaNacimiento, $pais, $bio, $fotoUrl);
    }

}
?>