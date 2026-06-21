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
    public function listarSecciones(): array;
    public function guardarBorrador(int $idAutor, ?int $idSeccion, ?string $titulo, ?string $nombreCientifico, ?string $fotoUrl, ?string $areasHabitat, ?string $dieta, ?string $horasActivas, array $camposExtra): array;
    public function obtenerBorradorPorAutor(int $idAutor): ?array;
    public function eliminarBorradorPorAutor(int $idAutor): void;
    public function listarReportes(): array;
    public function resolverReporte(int $idReporte): void;
    public function obtenerPublicacionPorId(int $id): DTPublicacion;
    public function generarPdfPublicacion(int $id): array;
}
?>