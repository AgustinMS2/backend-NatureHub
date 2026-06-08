<?php
interface IUsuarioController {
    public function altaUsuario(DTUsuario $dtu): void;
    public function altaModerador(DTUsuario $dtu): void;
    public function bajaUsuario(int $id): void;
    public function modificarUsuario(DTUsuario $dtu): void;
    public function listarUsuarios(): array;
    public function moderarUsuario(): void;
    public function iniciarSesion(DTUsuario $dtu): array;
    public function cerrarSesion(string $token): void;
}
?>