<?php
interface IPublicacionController {
    public function altaPublicacion(DTPublicacion $dtp): void;
    public function bajaPublicacion(int $id): void;
    public function modificarPublicacion(DTPublicacion $dtp): array;
    public function moderarPublicacion(DTModera $dtm): void;
    public function listarPublicaciones(): array;
    public function listarPublicacionesPropias(int $id): array;
    public function listarPublicacionesTitulo(string $titulo): array;
    public function agregarCampoExtra(DTCampoExtra $dtc): void;
    public function eliminarCampoExtra(int $id): void;
    public function modificarCampoExtra(DTCampoExtra $dtc): void;
    public function listarPublicacionesPendientes(): array;
    public function listarPublicacionesPorSeccion(string $seccion): array;
    public function reportePublicacion(DTReporte $dtr): void;
    public function listarPublicacionFiltro(string $filtro): void;
    
}
?>