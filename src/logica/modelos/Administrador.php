<?php

class Administrador extends Moderador {

    public function __construct(int $id, string $nombre, string $apellido, string $email, string $passwordHash, bool $activo, DateTime $fechaRegistro, array $reportes, array $publicaciones) {
        parent::__construct($id, $nombre, $apellido, $email, $passwordHash, $activo, $fechaRegistro, $reportes, $publicaciones);
    }

}
?>