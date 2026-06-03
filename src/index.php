<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include __DIR__ . "/logica/endpoints/UsuarioEndpoint.php";
include __DIR__ . "/logica/endpoints/PublicacionEndpoint.php";

include __DIR__ . "/logica/modelos/Usuario.php";
include __DIR__ . "/logica/modelos/Publicacion.php";

include __DIR__ . "/servicios/DTs/DTUsuario.php";
include __DIR__ . "/servicios/DTs/DTPublicacion.php";

$metodo = $_SERVER['REQUEST_METHOD'];
$ruta = $_SERVER['PATH_INFO'] ?? '';

$usuarioEndpoint = new UsuarioEndpoint();
$publicacionEndpoint = new PublicacionEndpoint();

match([$metodo, $ruta]) {
    ['POST', '/usuarios/altaUsuario'] => $usuarioEndpoint->altaUsuario(),
    ['DELETE','/usuarios/bajaUsuario'] => $usuarioEndpoint->bajaUsuario((int)$_GET['id']),
    ['PUT',  '/usuarios/modificarUsuario'] => $usuarioEndpoint->modificarUsuario(),
    ['GET',  '/usuarios/listarUsuarios'] => $usuarioEndpoint->listarUsuarios(),
    ['POST', '/usuarios/iniciarSesion'] => $usuarioEndpoint->iniciarSesion(),
    ['POST', '/usuarios/cerrarSesion'] => $usuarioEndpoint->cerrarSesion(),
    
    ['POST', '/publicaciones/altaPublicacion'] => $publicacionEndpoint->altaPublicacion(),
    ['DELETE', '/publicaciones/bajaPublicacion'] => $publicacionEndpoint->bajaPublicacion(),
    ['PUT', '/publicaciones/modificarPublicacion'] => $publicacionEndpoint->modificarPublicacion(),
    ['GET', '/publicaciones/listarPublicaciones'] => $publicacionEndpoint->listarPublicaciones(),
    ['GET', '/publicaciones/listarPublicacionesPropias'] => $publicacionEndpoint->listarPublicacionesPropias((int)$_GET['id']),
    ['POST', '/publicaciones/agregarCampoExtra'] => $publicacionEndpoint->agregarCampoExtra(),
    ['DELETE', '/publicaciones/eliminarCampoExtra'] => $publicacionEndpoint->eliminarCampoExtra((int)$_GET['id']),
    ['GET', '/publicaciones/listarPublicacionFiltro'] => $publicacionEndpoint->listarPublicacionFiltro((string)$_GET['filtro']),

    default => http_response_code(404)
};
?>