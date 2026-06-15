<?php
include_once __DIR__ . "/../../persistencia/Conectar.php";

class PublicacionRepositorio {
    private static ?PublicacionRepositorio $instancia = null;
    private mysqli $mysql;

    private function __construct() {
        $this->mysql = new Conectar();
    }

    public static function getInstance(): PublicacionRepositorio {
        if (self::$instancia === null) {
            self::$instancia = new PublicacionRepositorio();
        }
        return self::$instancia;
    }

    public function agregarPublicacion(Publicacion $publicacion): void {
        $sql = "INSERT INTO PUBLICACION (id_publicacion, id_seccion, id_autor, titulo, nombre_cientifico, foto_url, areas_habitat, dieta, horas_activas, estado, fecha_creacion, fecha_modificacion, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
        $consulta = $this->mysql->prepare($sql);

        $id = $publicacion->getId();
        $seccion = $publicacion->getSeccion();
        $autor = $publicacion->getAutor();
        $titulo = $publicacion->getTitulo();
        $nombreCientifico = $publicacion->getNombreCientifico();
        $foto = $publicacion->getFoto();
        $areasHabitat = json_encode($publicacion->getAreasHabitat());
        $dieta = $publicacion->getDieta();
        $horasActivas = $publicacion->getHorasActivas();
        $estado = 'PENDIENTE_REVISION';
        $fechaCreacion = $publicacion->getFechaCreacion()->format("Y-m-d H:i:s");
        $fechaUltimaModificacion = $publicacion->getFechaUltimaModificacion()->format("Y-m-d H:i:s");
        $activo = true;

        $consulta->bind_param("iiissssssssss", $id, $seccion, $autor, $titulo, $nombreCientifico, $foto, $areasHabitat, $dieta, $horasActivas, $estado, $fechaCreacion, $fechaUltimaModificacion, $activo);
        $consulta->execute();
        
        $camposExtra = $publicacion->getCamposExtra();

        $sqlCampo = "INSERT INTO CAMPO_EXTRA(id_publicacion, etiqueta, valor, tipo) VALUES (?, ?, ?, ?)";

        $consultaCampo = $this->mysql->prepare($sqlCampo);

        foreach ($camposExtra as $campo) {

            $idPublicacion = $publicacion->getId();

            $etiqueta = $campo->etiqueta;
            $valor = $campo->valor;
            $tipo = $campo->tipo;

            $consultaCampo->bind_param("isss", $idPublicacion, $etiqueta, $valor, $tipo);

            $consultaCampo->execute();
        }
    }

    public function obtenerPublicacionId(int $id): ?Publicacion{
        $sql = "SELECT * FROM PUBLICACION WHERE id_publicacion = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $id);
        $consulta->execute();

        $resultado = $consulta->get_result();
        $fila = $resultado->fetch_assoc();

        if ($fila) {
            return new Publicacion(
                $fila["id_publicacion"],
                $fila["titulo"],
                $fila["foto_url"],
                $fila["nombre_cientifico"],
                json_decode($fila["areas_habitat"], true),
                $fila["dieta"],
                $fila["horas_activas"],
                EstadoPublicacion::from($fila["estado"]),
                new DateTime($fila["fecha_creacion"]),
                new DateTime($fila["fecha_modificacion"]),
                $fila["id_autor"],
                [],
                $fila["id_seccion"]
            );
        }

        return null;
    }

    public function obtenerPublicacionTitulo(string $titulo): ?Publicacion{
        $sql = "SELECT * FROM PUBLICACION WHERE titulo = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("s", $titulo);
        $consulta->execute();

        $resultado = $consulta->get_result();
        $fila = $resultado->fetch_assoc();

        if ($fila) {
            return new Publicacion(
                $fila["id_publicacion"],
                $fila["titulo"],
                $fila["foto_url"],
                $fila["nombre_cientifico"],
                json_decode($fila["areas_habitat"], true),
                $fila["dieta"],
                $fila["horas_activas"],
                EstadoPublicacion::from($fila["estado"]),
                new DateTime($fila["fecha_creacion"]),
                new DateTime($fila["fecha_modificacion"]),
                $fila["id_autor"],
                [],
                $fila["id_seccion"]
            );
    }

    return null;
    }

    public function modificarPublicacion(Publicacion $publicacion): void {
        //"UPDATE Proponente pro SET pro.eliminado = true, pro.fechaEliminacion = :fechaEliminacion WHERE pro.nickname = :nick"
        //"INSERT INTO PUBLICACION (id_publicacion, id_seccion, id_autor, titulo, nombre_cientifico, foto_url, areas_habitat, dieta, horas_activas, estado, fecha_creacion, fecha_modificacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
        $sql = "UPDATE PUBLICACION SET id_seccion = ?, id_autor = ?, titulo = ?, nombre_cientifico = ?, foto_url = ?, areas_habitat = ?, dieta = ?, horas_activas = ?, estado = ?, fecha_modificacion = ? WHERE id_publicacion = ?";
        $consulta = $this->mysql->prepare($sql);

        $id = $publicacion->getId();
        $seccion = $publicacion->getSeccion();
        $autor = $publicacion->getAutor();
        $titulo = $publicacion->getTitulo();
        $nombreCientifico = $publicacion->getNombreCientifico();
        $foto = $publicacion->getFoto();
        $areasHabitat = json_encode($publicacion->getAreasHabitat());
        $dieta = $publicacion->getDieta();
        $horasActivas = $publicacion->getHorasActivas();
        //$estado = 'PENDIENTE_REVISION';
        $estado= EstadoPublicacion::from('PENDIENTE_REVISION')->value;
        $fechaUltimaModificacion = $publicacion->getFechaUltimaModificacion()->format("Y-m-d H:i:s");

        $consulta->bind_param("iissssssssi", $seccion, $autor, $titulo, $nombreCientifico, $foto, $areasHabitat, $dieta, $horasActivas, $estado, $fechaUltimaModificacion, $id);
        $consulta->execute();
    }

    public function obtenerSiguienteId(): int {
        $sql = "SELECT COALESCE(MAX(id_publicacion), 0) + 1 AS proximo_id FROM PUBLICACION";
        $resultado = $this->mysql->query($sql);
        $fila = $resultado->fetch_assoc();
        return $fila["proximo_id"];
    }

    public function eliminarPublicacion(int $id): void {
        $sql = "UPDATE PUBLICACION SET activo = false WHERE id_publicacion = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $id);
        $consulta->execute();
    }

    public function listarPublicaciones(): array {
        $sql = "SELECT p.*, c.id_campo, c.etiqueta, c.valor, c.tipo FROM PUBLICACION p LEFT JOIN CAMPO_EXTRA c ON p.id_publicacion = c.id_publicacion WHERE p.activo = true ORDER BY p.id_publicacion";

        $resultado = $this->mysql->query($sql);

        $publicaciones = [];

        foreach ($resultado as $fila) {

            $idPublicacion = $fila["id_publicacion"];

            if (!isset($publicaciones[$idPublicacion])) {

                $publicaciones[$idPublicacion] = new Publicacion(
                    $fila["id_publicacion"],
                    $fila["titulo"],
                    $fila["foto_url"],
                    $fila["nombre_cientifico"],
                    json_decode($fila["areas_habitat"], true),
                    $fila["dieta"],
                    $fila["horas_activas"],
                    EstadoPublicacion::from($fila["estado"]),
                    new DateTime($fila["fecha_creacion"]),
                    new DateTime($fila["fecha_modificacion"]),
                    $fila["id_autor"],
                    [],
                    $fila["id_seccion"]
                );
            }

            if ($fila["id_campo"] !== null) {

                $publicaciones[$idPublicacion]->setCamposExtra([
                    "id" => $fila["id_campo"],
                    "etiqueta" => $fila["etiqueta"],
                    "valor" => $fila["valor"],
                    "tipo" => $fila["tipo"]
                ]);
            }
        }

        return array_values($publicaciones);
    }

    public function listarPublicacionesPropias(int $id): array {
        $sql = "SELECT p.*, c.id_campo, c.etiqueta, c.valor, c.tipo FROM PUBLICACION p LEFT JOIN CAMPO_EXTRA c ON p.id_publicacion = c.id_publicacion WHERE p.activo = true AND id_autor = ? ORDER BY p.id_publicacion";
        //$sql = "SELECT * FROM PUBLICACION WHERE id_autor = ?";
        $consulta = $this->mysql->prepare($sql);

        $consulta->bind_param("i", $id);
        $consulta->execute();

        $resultado = $consulta->get_result();

        $publicaciones = [];

        foreach ($resultado as $fila) {

            $idPublicacion = $fila["id_publicacion"];

            if (!isset($publicaciones[$idPublicacion])) {

                $publicaciones[$idPublicacion] = new Publicacion(
                    $fila["id_publicacion"],
                    $fila["titulo"],
                    $fila["foto_url"],
                    $fila["nombre_cientifico"],
                    json_decode($fila["areas_habitat"], true),
                    $fila["dieta"],
                    $fila["horas_activas"],
                    EstadoPublicacion::from($fila["estado"]),
                    new DateTime($fila["fecha_creacion"]),
                    new DateTime($fila["fecha_modificacion"]),
                    $fila["id_autor"],
                    [],
                    $fila["id_seccion"]
                );
            }

            if ($fila["id_campo"] !== null) {

                $publicaciones[$idPublicacion]->setCamposExtra([
                    "id" => $fila["id_campo"],
                    "etiqueta" => $fila["etiqueta"],
                    "valor" => $fila["valor"],
                    "tipo" => $fila["tipo"]
                ]);
            }
        }

        return array_values($publicaciones);
    }

    public function verificarExistenciaCampoExtra(int $idPublicacion, string $etiqueta): ?CampoExtra{
        $sql = "SELECT * FROM CAMPO_EXTRA WHERE id_publicacion = ? AND etiqueta = ?";

        $consulta = $this->mysql->prepare($sql);

        $consulta->bind_param("is", $idPublicacion, $etiqueta);
        $consulta->execute();

        $resultado = $consulta->get_result();
        $fila = $resultado->fetch_assoc();

        if($fila){
            return new CampoExtra(
                $fila["id_campo"],
                $fila["id_publicacion"],
                $fila["etiqueta"],
                $fila["valor"],
                TipoCampo::from($fila["tipo"])
            );
        }

        return null;
    }

    public function verificarExistenciaCampoExtraId(int $idCampo): ?CampoExtra{
        $sql = "SELECT * FROM CAMPO_EXTRA WHERE id_campo = ?";

        $consulta = $this->mysql->prepare($sql);

        $consulta->bind_param("i", $idCampo);
        $consulta->execute();

        $resultado = $consulta->get_result();
        $fila = $resultado->fetch_assoc();

        if($fila){
            return new CampoExtra(
                $fila["id_campo"],
                $fila["id_publicacion"],
                $fila["etiqueta"],
                $fila["valor"],
                TipoCampo::from($fila["tipo"])
            );
        }

        return null;
    }

    public function agregarCampoExtra(CampoExtra $campoExtra): void{
        $sql = "INSERT INTO CAMPO_EXTRA (id_publicacion, etiqueta, valor, tipo) VALUES (?, ?, ?, ?)";

        $consulta = $this->mysql->prepare($sql);

        $idPublicacion = $campoExtra->getIdPublicacion();
        $etiqueta = $campoExtra->getEtiqueta();
        $valor = $campoExtra->getValor();
        $tipo = $campoExtra->getTipo()->value;

        $consulta->bind_param("isss", $idPublicacion, $etiqueta, $valor, $tipo);
        $consulta->execute();
    }

    public function eliminarCampoExtra(int $idCampo): void{
        $sql = "DELETE FROM CAMPO_EXTRA WHERE id_campo = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idCampo);
        $consulta->execute();
    }

    public function modificarCampoExtra(CampoExtra $campoExtra): void{
        $sql = "UPDATE CAMPO_EXTRA SET etiqueta = ?, valor = ?, tipo = ? WHERE id_campo = ?";

        $consulta = $this->mysql->prepare($sql);

        $etiqueta = $campoExtra->getEtiqueta();
        $valor = $campoExtra->getValor();
        $tipo = $campoExtra->getTipo()->value;
        $idCampo = $campoExtra->getId();

        $consulta->bind_param("sssi", $etiqueta, $valor, $tipo, $idCampo);
        $consulta->execute();

    }

    public function listarPublicacionesPendientes(): array {
        $sql = "SELECT p.*, c.id_campo, c.etiqueta, c.valor, c.tipo FROM PUBLICACION p LEFT JOIN CAMPO_EXTRA c ON p.id_publicacion = c.id_publicacion WHERE p.activo = true AND estado = 'PENDIENTE_REVISION' ORDER BY p.id_publicacion";

        $resultado = $this->mysql->query($sql);

        $publicaciones = [];

        foreach ($resultado as $fila) {

            $idPublicacion = $fila["id_publicacion"];

            if (!isset($publicaciones[$idPublicacion])) {

                $publicaciones[$idPublicacion] = new Publicacion(
                    $fila["id_publicacion"],
                    $fila["titulo"],
                    $fila["foto_url"],
                    $fila["nombre_cientifico"],
                    json_decode($fila["areas_habitat"], true),
                    $fila["dieta"],
                    $fila["horas_activas"],
                    EstadoPublicacion::from($fila["estado"]),
                    new DateTime($fila["fecha_creacion"]),
                    new DateTime($fila["fecha_modificacion"]),
                    $fila["id_autor"],
                    [],
                    $fila["id_seccion"]
                );
            }

            if ($fila["id_campo"] !== null) {

                $publicaciones[$idPublicacion]->setCamposExtra([
                    "id" => $fila["id_campo"],
                    "etiqueta" => $fila["etiqueta"],
                    "valor" => $fila["valor"],
                    "tipo" => $fila["tipo"]
                ]);
            }
        }

        return array_values($publicaciones);
    }

    public function listarPublicacionesPorSeccion(string $seccion): array {
        $sql = "SELECT p.*, c.id_campo, c.etiqueta, c.valor, c.tipo FROM PUBLICACION p LEFT JOIN CAMPO_EXTRA c ON p.id_publicacion = c.id_publicacion LEFT JOIN SECCION s ON p.id_seccion = s.id_seccion WHERE p.activo = true AND s.nombre = ? ORDER BY p.id_publicacion";

        $consulta = $this->mysql->prepare($sql);

        $consulta->bind_param("s", $seccion);
        $consulta->execute();

        $resultado = $consulta->get_result();

        $publicaciones = [];

        foreach ($resultado as $fila) {

            $idPublicacion = $fila["id_publicacion"];

            if (!isset($publicaciones[$idPublicacion])) {

                $publicaciones[$idPublicacion] = new Publicacion(
                    $fila["id_publicacion"],
                    $fila["titulo"],
                    $fila["foto_url"],
                    $fila["nombre_cientifico"],
                    json_decode($fila["areas_habitat"], true),
                    $fila["dieta"],
                    $fila["horas_activas"],
                    EstadoPublicacion::from($fila["estado"]),
                    new DateTime($fila["fecha_creacion"]),
                    new DateTime($fila["fecha_modificacion"]),
                    $fila["id_autor"],
                    [],
                    $fila["id_seccion"]
                );
            }

            if ($fila["id_campo"] !== null) {

                $publicaciones[$idPublicacion]->setCamposExtra([
                    "id" => $fila["id_campo"],
                    "etiqueta" => $fila["etiqueta"],
                    "valor" => $fila["valor"],
                    "tipo" => $fila["tipo"]
                ]);
            }
        }

        return array_values($publicaciones);
    }

}
?>