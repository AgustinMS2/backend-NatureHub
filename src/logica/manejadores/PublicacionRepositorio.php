<?php
include_once __DIR__ . "/../../persistencia/Conectar.php";
include_once __DIR__ . "/../../logica/modelos/Publicacion.php";
include_once __DIR__ . "/../../logica/modelos/EstadoPublicacion.php";
include_once __DIR__ . "/../../logica/modelos/CampoExtra.php";
include_once __DIR__ . "/../../logica/modelos/TipoCampo.php";
include_once __DIR__ . "/../../logica/modelos/Modera.php";
include_once __DIR__ . "/../../logica/modelos/ResultadoRevision.php";
include_once __DIR__ . "/../../logica/modelos/Reporte.php";

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

    public function obtenerPublicacionTitulo(string $titulo): ?Publicacion {
        $sql = "SELECT p.*, c.id_campo, c.etiqueta, c.valor, c.tipo FROM PUBLICACION p LEFT JOIN CAMPO_EXTRA c ON p.id_publicacion = c.id_publicacion WHERE p.activo = true AND p.titulo = ? ORDER BY c.id_campo";

        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("s", $titulo);
        $consulta->execute();

        $resultado = $consulta->get_result();

        $publicacion = null;

        while ($fila = $resultado->fetch_assoc()) {

            if ($publicacion === null) {
                $publicacion = new Publicacion(
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
                $publicacion->setCamposExtra([
                    "id" => $fila["id_campo"],
                    "etiqueta" => $fila["etiqueta"],
                    "valor" => $fila["valor"],
                    "tipo" => $fila["tipo"]
                ]);
            }
        }

        return $publicacion;
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
        $estado = $publicacion->getEstado()->value;
        $fechaUltimaModificacion = $publicacion->getFechaUltimaModificacion()->format("Y-m-d H:i:s");

        $consulta->bind_param("iissssssssi", $seccion, $autor, $titulo, $nombreCientifico, $foto, $areasHabitat, $dieta, $horasActivas, $estado, $fechaUltimaModificacion, $id);
        $consulta->execute();
    }

    public function reemplazarCamposExtra(int $idPublicacion, array $camposExtra): void {
        $sqlDelete = "DELETE FROM CAMPO_EXTRA WHERE id_publicacion = ?";
        $consultaDelete = $this->mysql->prepare($sqlDelete);
        $consultaDelete->bind_param("i", $idPublicacion);
        $consultaDelete->execute();

        if (empty($camposExtra)) {
            return;
        }

        $sqlInsert = "INSERT INTO CAMPO_EXTRA (id_publicacion, etiqueta, valor, tipo) VALUES (?, ?, ?, ?)";
        $consultaInsert = $this->mysql->prepare($sqlInsert);

        foreach ($camposExtra as $campo) {
            $etiqueta = is_object($campo) ? ($campo->etiqueta ?? '') : ($campo['etiqueta'] ?? '');
            $valor = is_object($campo) ? ($campo->valor ?? '') : ($campo['valor'] ?? '');
            $tipo = is_object($campo) ? ($campo->tipo ?? 'texto') : ($campo['tipo'] ?? 'texto');

            if ($etiqueta === '') {
                continue;
            }

            $consultaInsert->bind_param("isss", $idPublicacion, $etiqueta, $valor, $tipo);
            $consultaInsert->execute();
        }
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

    public function reportePublicacion(Reporte $reporte){
        $sql = "INSERT INTO REPORTE (id_publicacion, id_usuario, motivo, fecha, resuelto) VALUES (?, ?, ?, ?, ?)";

        $consulta = $this->mysql->prepare($sql);

        //var_dump($reporte->getIdPublicacion());
        //var_dump($reporte->getIdUsuario());
        //var_dump($reporte->getMotivo());
        //var_dump($reporte->getFecha());
        //var_dump($reporte->getResuelto());
        
        $idPublicacion = $reporte->getIdPublicacion();
        $idUsuario = $reporte->getIdUsuario();
        $motivo = $reporte->getMotivo();
        $fecha = $reporte->getFecha()->format("Y-m-d H:i:s");
        $resuelto = $reporte->getResuelto();

        $consulta->bind_param("iisss", $idPublicacion, $idUsuario, $motivo, $fecha, $resuelto);
        $consulta->execute();

    }

    public function moderarPublicacion(Modera $moderacion): void {
        try {
            $this->mysql->begin_transaction();

            $sqlModera = "INSERT INTO MODERA (id_modera, id_publicacion, id_moderador, resultado, motivo_rechazo, fecha_revision) VALUES (?, ?, ?, ?, ?, ?)";
            $consultaModera = $this->mysql->prepare($sqlModera);

            $idModera = $moderacion->getId();
            $idPublicacion = $moderacion->getPublicacion();
            $idModerador = $moderacion->getModerador();
            $resultadoEnum = $moderacion->getResultado()->value;
            $fechaRevision = $moderacion->getFechaRevision()->format("Y-m-d H:i:s");;
            $motivoRechazo = $moderacion->getMotivoRechazo();

            $consultaModera->bind_param("iiisss", $idModera, $idPublicacion, $idModerador, $resultadoEnum, $motivoRechazo, $fechaRevision);
            $consultaModera->execute();
 
            $sqlUpdate = "UPDATE PUBLICACION SET estado = ?, fecha_modificacion = ? WHERE id_publicacion = ?";
            $consultaUpdate = $this->mysql->prepare($sqlUpdate);
            $consultaUpdate->bind_param("ssi", $resultadoEnum, $fechaRevision, $idPublicacion);
            $consultaUpdate->execute();
 
            $this->mysql->commit();
        } catch (Exception $e) {
            $this->mysql->rollback();
            throw $e;
        }
    }
 
    public function obtenerSiguienteIdModera(): int {
        $sql = "SELECT COALESCE(MAX(id_modera), 0) + 1 AS proximo_id FROM MODERA";
        $resultado = $this->mysql->query($sql);
        $fila = $resultado->fetch_assoc();
        return $fila["proximo_id"];
    }

    public function listarSecciones(): array {
        $sql = "SELECT id_seccion, nombre, descripcion FROM SECCION ORDER BY id_seccion";
        $resultado = $this->mysql->query($sql);
        $secciones = [];
        foreach ($resultado as $fila) {
            $secciones[] = [
                'id_seccion' =>  $fila['id_seccion'],
                'nombre' => $fila['nombre'],
                'descripcion' => $fila['descripcion']
            ];
        }

        
        return $secciones;
    }

    public function obtenerBorradorPorAutor(int $idAutor): ?array {
        $sql = "SELECT * FROM BORRADOR WHERE id_autor = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idAutor);
        $consulta->execute();
        $resultado = $consulta->get_result();
        $fila = $resultado->fetch_assoc();
        return $fila ?: null;
    }

    public function guardarBorrador(
        int $idAutor,
        ?int $idSeccion,
        ?string $titulo,
        ?string $nombreCientifico,
        ?string $fotoUrl,
        ?string $areasHabitat,
        ?string $dieta,
        ?string $horasActivas,
        ?string $camposExtraJson
    ): int {
        $existente = $this->obtenerBorradorPorAutor($idAutor);
        $fechaModificacion = (new DateTime())->format("Y-m-d H:i:s");

        if ($existente !== null) {
            $sql = "UPDATE BORRADOR SET id_seccion = ?, titulo = ?, nombre_cientifico = ?, foto_url = ?, areas_habitat = ?, dieta = ?, horas_activas = ?, campos_extra = ?, fecha_modificacion = ? WHERE id_autor = ?";
            $consulta = $this->mysql->prepare($sql);
            $consulta->bind_param(
                "issssssssi",
                $idSeccion,
                $titulo,
                $nombreCientifico,
                $fotoUrl,
                $areasHabitat,
                $dieta,
                $horasActivas,
                $camposExtraJson,
                $fechaModificacion,
                $idAutor
            );
            $consulta->execute();
            return (int) $existente["id_borrador"];
        }

        $idBorrador = $this->obtenerSiguienteIdBorrador();
        $sql = "INSERT INTO BORRADOR (id_borrador, id_autor, id_seccion, titulo, nombre_cientifico, foto_url, areas_habitat, dieta, horas_activas, campos_extra, fecha_modificacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param(
            "iiissssssss",
            $idBorrador,
            $idAutor,
            $idSeccion,
            $titulo,
            $nombreCientifico,
            $fotoUrl,
            $areasHabitat,
            $dieta,
            $horasActivas,
            $camposExtraJson,
            $fechaModificacion
        );
        $consulta->execute();
        return $idBorrador;
    }

    public function eliminarBorradorPorAutor(int $idAutor): void {
        $sql = "DELETE FROM BORRADOR WHERE id_autor = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idAutor);
        $consulta->execute();
    }

    public function obtenerSiguienteIdBorrador(): int {
        $sql = "SELECT COALESCE(MAX(id_borrador), 0) + 1 AS proximo_id FROM BORRADOR";
        $resultado = $this->mysql->query($sql);
        $fila = $resultado->fetch_assoc();
        return $fila["proximo_id"];
    }

    public function listarReportes(): array {
        $sql = "SELECT id_reporte, id_publicacion, id_usuario, motivo, fecha, resuelto
                FROM REPORTE
                ORDER BY resuelto ASC, fecha DESC";
        $resultado = $this->mysql->query($sql);
        $reportes = [];
        foreach ($resultado as $fila) {
            $reportes[] = [
                'id_reporte' => $fila['id_reporte'],
                'id_publicacion' => $fila['id_publicacion'],
                'id_usuario' => $fila['id_usuario'],
                'motivo' => $fila['motivo'],
                'fecha' => $fila['fecha'],
                'resuelto' => $fila['resuelto'],
            ];
        }
        return $reportes;
    }
 
    public function obtenerReportePorId(int $idReporte): ?array {
        $sql = "SELECT id_reporte, resuelto FROM REPORTE WHERE id_reporte = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idReporte);
        $consulta->execute();
        $resultado = $consulta->get_result();
        $fila = $resultado->fetch_assoc();
        return $fila ?: null;
    }
 
    public function resolverReporte(int $idReporte): void {
        $sql = "UPDATE REPORTE SET resuelto = true WHERE id_reporte = ?";
        $consulta = $this->mysql->prepare($sql);
        $consulta->bind_param("i", $idReporte);
        $consulta->execute();
    }

}
?>