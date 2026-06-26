<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}       

include __DIR__ . "/logica/endpoints/UsuarioEndpoint.php";
include __DIR__ . "/logica/endpoints/PublicacionEndpoint.php";
include __DIR__ . "/logica/Autenticacion.php";

$metodo = $_SERVER['REQUEST_METHOD'];
$ruta = $_SERVER['PATH_INFO'] ?? '';

$rutasProtegidas = [
    'DELETE /usuarios/bajaUsuario' => [],
    'POST /usuarios/modificarUsuario' => [],
    'POST /usuarios/promoverUsuario' => ['ADMINISTRADOR'],
    'POST /usuarios/degradarModerador' => ['ADMINISTRADOR'],
    'POST /usuarios/promoverModerador' => ['ADMINISTRADOR'],
    'POST /usuarios/degradarAdministrador' => ['ADMINISTRADOR'],
    'POST /usuarios/agregarFavoritas' => [],
    'DELETE /usuarios/eliminarFavorita' => [],
    'POST /usuarios/listarFavoritas' => [],
    'POST /usuarios/agregarUsuarioFavorito' => [],
    'DELETE /usuarios/eliminarUsuarioFavorito' => [],
    'POST /usuarios/listarUsuariosFavoritos' => [],

    'POST /publicaciones/altaPublicacion' => [],
    'DELETE /publicaciones/bajaPublicacion' => [],
    'PUT /publicaciones/modificarPublicacion' => [],
    'POST /publicaciones/modificarPublicacion' => [],
    'POST /publicaciones/listarPublicacionesPropias' => [],
    'POST /publicaciones/agregarCampoExtra' => [],
    'DELETE /publicaciones/eliminarCampoExtra' => [],
    'PUT /publicaciones/modificarCampoExtra' => [],
    'GET /publicaciones/listarPublicacionesPendientes'=> ['MODERADOR', 'ADMINISTRADOR'],
    'POST /publicaciones/reportePublicacion' => [],
    'POST /publicaciones/moderarPublicacion' => ['MODERADOR', 'ADMINISTRADOR'],
    'POST /publicaciones/guardarBorrador' => [],
    'GET /publicaciones/obtenerBorrador' => [],
    'DELETE /publicaciones/eliminarBorrador' => [],
    'GET /publicaciones/listarReportes' => ['MODERADOR', 'ADMINISTRADOR'],
    'POST /publicaciones/resolverReporte' => ['MODERADOR', 'ADMINISTRADOR'],
];

try {
    $claveRuta = "$metodo $ruta";
    $usuarioAutenticado = null;
    if (array_key_exists($claveRuta, $rutasProtegidas)) {
        $usuarioAutenticado = Autenticacion::autenticar($rutasProtegidas[$claveRuta]);
    }

    $usuarioEndpoint = new UsuarioEndpoint($usuarioAutenticado);
    $publicacionEndpoint = new PublicacionEndpoint($usuarioAutenticado);

    match([$metodo, $ruta]) {
        ['POST', '/usuarios/altaUsuario'] => $usuarioEndpoint->altaUsuario(),
        ['DELETE','/usuarios/bajaUsuario'] => $usuarioEndpoint->bajaUsuario(),
        ['POST',  '/usuarios/modificarUsuario'] => $usuarioEndpoint->modificarUsuario(),
        ['GET',  '/usuarios/listarUsuarios'] => $usuarioEndpoint->listarUsuarios(),
        ['POST', '/usuarios/iniciarSesion'] => $usuarioEndpoint->iniciarSesion(),
        ['POST', '/usuarios/cerrarSesion'] => $usuarioEndpoint->cerrarSesion(),
        ['POST', '/usuarios/promoverUsuario'] => $usuarioEndpoint->promoverUsuario(),
        ['POST', '/usuarios/degradarModerador'] => $usuarioEndpoint->degradarModerador(),
        ['POST', '/usuarios/promoverModerador'] => $usuarioEndpoint->promoverModerador(),
        ['POST', '/usuarios/degradarAdministrador'] => $usuarioEndpoint->degradarAdministrador(),
        ['POST', '/usuarios/agregarFavoritas'] => $usuarioEndpoint->agregarFavoritas(),
        ['DELETE', '/usuarios/eliminarFavorita'] => $usuarioEndpoint->eliminarFavorita(),
        ['POST', '/usuarios/listarFavoritas'] => $usuarioEndpoint->listarFavoritas(),
        ['POST', '/usuarios/obtenerUsuarioId'] => $usuarioEndpoint->obtenerUsuarioId(),
        ['POST', '/usuarios/agregarUsuarioFavorito'] => $usuarioEndpoint->agregarUsuarioFavorito(),
        ['DELETE', '/usuarios/eliminarUsuarioFavorito'] => $usuarioEndpoint->eliminarUsuarioFavorito(),
        ['POST', '/usuarios/listarUsuariosFavoritos'] => $usuarioEndpoint->listarUsuariosFavoritos(),

        ['POST', '/publicaciones/altaPublicacion'] => $publicacionEndpoint->altaPublicacion(),
        ['DELETE', '/publicaciones/bajaPublicacion'] => $publicacionEndpoint->bajaPublicacion(),
        ['POST', '/publicaciones/modificarPublicacion'] => $publicacionEndpoint->modificarPublicacion(),
        ['GET', '/publicaciones/listarPublicaciones'] => $publicacionEndpoint->listarPublicaciones(),
        ['POST', '/publicaciones/listarPublicacionesPropias'] => $publicacionEndpoint->listarPublicacionesPropias(),
        ['GET', '/publicaciones/listarPublicacionesTitulo'] => $publicacionEndpoint->listarPublicacionesTitulo(),
        ['POST', '/publicaciones/agregarCampoExtra'] => $publicacionEndpoint->agregarCampoExtra(),
        ['DELETE', '/publicaciones/eliminarCampoExtra'] => $publicacionEndpoint->eliminarCampoExtra(),
        ['PUT', '/publicaciones/modificarCampoExtra'] => $publicacionEndpoint->modificarCampoExtra(),
        ['GET', '/publicaciones/listarPublicacionesPendientes'] => $publicacionEndpoint->listarPublicacionesPendientes(),
        ['GET', '/publicaciones/listarPublicacionesPorSeccion'] => $publicacionEndpoint->listarPublicacionesPorSeccion(),
        ['POST', '/publicaciones/reportePublicacion'] => $publicacionEndpoint->reportePublicacion(),
        ['GET', '/publicaciones/listarPublicacionFiltro'] => $publicacionEndpoint->listarPublicacionFiltro(),
        ['POST', '/publicaciones/moderarPublicacion'] => $publicacionEndpoint->moderarPublicacion(),
        ['GET', '/publicaciones/listarSecciones'] => $publicacionEndpoint->listarSecciones(),
        ['POST', '/publicaciones/guardarBorrador'] => $publicacionEndpoint->guardarBorrador(),
        ['GET', '/publicaciones/obtenerBorrador'] => $publicacionEndpoint->obtenerBorrador(),
        ['DELETE', '/publicaciones/eliminarBorrador'] => $publicacionEndpoint->eliminarBorrador(),
        ['GET',  '/publicaciones/listarReportes']   => $publicacionEndpoint->listarReportes(),
        ['POST', '/publicaciones/resolverReporte']  => $publicacionEndpoint->resolverReporte(),
        ['POST', '/publicaciones/obtenerPublicacionPorId']  => $publicacionEndpoint->obtenerPublicacionPorId(),
        ['GET', '/publicaciones/generarPdfPublicacion']  => $publicacionEndpoint->generarPdfPublicacion(),

        default => http_response_code(404)
    };
} catch (Exception $e) {
    http_response_code(500);
    file_put_contents(__DIR__ . '/error.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL, FILE_APPEND);
    echo json_encode(['error' => $e->getMessage()]);
}
?>