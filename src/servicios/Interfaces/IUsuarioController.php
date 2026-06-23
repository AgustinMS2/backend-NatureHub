<?php
interface IUsuarioController {
    public function altaUsuario(DTUsuario $dtu): void;
    public function bajaUsuario(int $id): void;
    public function modificarUsuario(DTUsuario $dtu, ?string $nuevaPassword): void;
    public function listarUsuarios(): array;
    public function iniciarSesion(DTUsuario $dtu): array;
    public function cerrarSesion(string $token): void;
    public function validarToken(string $token): DTUsuario;
    public function promoverUsuario(int $id): void;
    public function degradarModerador(int $id): void;
    public function promoverModerador(int $id): void;
    public function degradarAdministrador(int $id): void;
    public function agregarFavoritas(int $idUsuario, int $idPublicacion): void;
    public function eliminarFavorita(int $idUsuario, int $idPublicacion): void;
    public function listarFavoritas(int $idUsuario): array;
    public function obtenerUsuarioId(int $idUsuario): DTUsuario;
    public function agregarUsuarioFavorito(int $idUsuario, int $idUsuarioFavorito): void;
    public function eliminarUsuarioFavorito(int $idUsuario, int $idUsuarioFavorito): void;
    public function listarUsuariosFavoritos(int $idUsuario): array;

}
?>