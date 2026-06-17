<?php
interface IUsuarioController {
    public function altaUsuario(DTUsuario $dtu): void;
    public function bajaUsuario(int $id): void;
    public function modificarUsuario(DTUsuario $dtu, ?string $nuevaPassword): void;
    public function listarUsuarios(): array;
    public function moderarUsuario(): void;
    public function iniciarSesion(DTUsuario $dtu): array;
    public function cerrarSesion(string $token): void;
    public function promoverUsuario(int $id): void;
    public function degradarModerador(int $id): void;
    public function promoverModerador(int $id): void;
    public function degradarAdministrador(int $id): void;
}
?>