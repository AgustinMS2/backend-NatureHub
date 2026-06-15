<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Inicializar datos básicos
include_once __DIR__ . "/inicializar.php";

include __DIR__ . "/logica/endpoints/UsuarioEndpoint.php";
include __DIR__ . "/logica/endpoints/PublicacionEndpoint.php";

include __DIR__ . "/logica/modelos/Usuario.php";
include __DIR__ . "/logica/modelos/Sesion.php";
include __DIR__ . "/logica/modelos/Publicacion.php";
include __DIR__ . "/logica/modelos/CampoExtra.php";
include __DIR__ . "/logica/modelos/TipoCampo.php";

include __DIR__ . "/servicios/DTs/DTSesion.php";
include __DIR__ . "/servicios/DTs/DTUsuario.php";
include __DIR__ . "/servicios/DTs/DTPublicacion.php";
include __DIR__ . "/servicios/DTs/DTCampoExtra.php";
include __DIR__ . "/servicios/DTs/DTTipoCampo.php";

$metodo = $_SERVER['REQUEST_METHOD'];
$ruta = $_SERVER['PATH_INFO'] ?? '';

try {
    $usuarioEndpoint = new UsuarioEndpoint();
    $publicacionEndpoint = new PublicacionEndpoint();

match([$metodo, $ruta]) {
    ['POST', '/usuarios/altaUsuario'] => $usuarioEndpoint->altaUsuario(),
    ['POST', '/usuarios/altaModerador'] => $usuarioEndpoint->altaModerador(),
    ['DELETE','/usuarios/bajaUsuario'] => $usuarioEndpoint->bajaUsuario(),
    ['POST',  '/usuarios/modificarUsuario'] => $usuarioEndpoint->modificarUsuario(),
    ['GET',  '/usuarios/listarUsuarios'] => $usuarioEndpoint->listarUsuarios(),
    ['POST', '/usuarios/iniciarSesion'] => $usuarioEndpoint->iniciarSesion(),
    ['POST', '/usuarios/cerrarSesion'] => $usuarioEndpoint->cerrarSesion(),
    
    ['POST', '/publicaciones/altaPublicacion'] => $publicacionEndpoint->altaPublicacion(),
    ['DELETE', '/publicaciones/bajaPublicacion'] => $publicacionEndpoint->bajaPublicacion(),
    ['PUT', '/publicaciones/modificarPublicacion'] => $publicacionEndpoint->modificarPublicacion(),
    ['GET', '/publicaciones/listarPublicaciones'] => $publicacionEndpoint->listarPublicaciones(),
    ['GET', '/publicaciones/listarPublicacionesPropias'] => $publicacionEndpoint->listarPublicacionesPropias(),
    ['POST', '/publicaciones/agregarCampoExtra'] => $publicacionEndpoint->agregarCampoExtra(),
    ['DELETE', '/publicaciones/eliminarCampoExtra'] => $publicacionEndpoint->eliminarCampoExtra(),
    ['PUT', '/publicaciones/modificarCampoExtra'] => $publicacionEndpoint->modificarCampoExtra(),
    ['GET', '/publicaciones/listarPublicacionesPendientes'] => $publicacionEndpoint->listarPublicacionesPendientes(),
    ['GET', '/publicaciones/listarPublicacionesPorSeccion'] => $publicacionEndpoint->listarPublicacionesPorSeccion(),
    ['GET', '/publicaciones/listarPublicacionFiltro'] => $publicacionEndpoint->listarPublicacionFiltro((string)$_GET['filtro']),

        default => http_response_code(404)
    };
} catch (Exception $e) {
    http_response_code(500);
    // Registrar el error en un archivo de log
    file_put_contents(__DIR__ . '/error.log', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL, FILE_APPEND);
    echo json_encode(['error' => $e->getMessage()]);
}
?>