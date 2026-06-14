<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

set_exception_handler(function (Throwable $e): void {
    http_response_code(500);
    echo json_encode([
        "error" => "Error interno del servidor",
        "detalle" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
});

include_once __DIR__ . "/logica/modelos/Sesion.php";
include_once __DIR__ . "/logica/modelos/Usuario.php";
include_once __DIR__ . "/logica/modelos/Moderador.php";
include_once __DIR__ . "/logica/modelos/Administrador.php";
include_once __DIR__ . "/logica/modelos/EstadoPublicacion.php";
include_once __DIR__ . "/logica/modelos/TipoCampo.php";
include_once __DIR__ . "/logica/modelos/CampoExtra.php";
include_once __DIR__ . "/logica/modelos/Publicacion.php";

include_once __DIR__ . "/servicios/DTs/DTUsuario.php";
include_once __DIR__ . "/servicios/DTs/DTModerador.php";
include_once __DIR__ . "/servicios/DTs/DTAdministrador.php";
include_once __DIR__ . "/servicios/DTs/DTSesion.php";
include_once __DIR__ . "/servicios/DTs/DTCampoExtra.php";
include_once __DIR__ . "/servicios/DTs/DTPublicacion.php";
include_once __DIR__ . "/servicios/DTs/DTReporte.php";

include_once __DIR__ . "/logica/endpoints/UsuarioEndpoint.php";
include_once __DIR__ . "/logica/endpoints/PublicacionEndpoint.php";

function obtenerRuta(): string {
    $ruta = $_SERVER["PATH_INFO"] ?? "";

    if ($ruta === "") {
        $uri = parse_url($_SERVER["REQUEST_URI"] ?? "", PHP_URL_PATH) ?: "";
        $pos = strpos($uri, "index.php");
        if ($pos !== false) {
            $ruta = substr($uri, $pos + strlen("index.php"));
        }
    }

    $ruta = "/" . trim($ruta, "/");
    return $ruta === "/" ? "" : $ruta;
}

$metodo = $_SERVER["REQUEST_METHOD"];
$ruta = obtenerRuta();

$usuarioEndpoint = new UsuarioEndpoint();
$publicacionEndpoint = new PublicacionEndpoint();

match ([$metodo, $ruta]) {
    ["POST", "/usuarios/altaUsuario"] => $usuarioEndpoint->altaUsuario(),
    ["POST", "/usuarios/altaModerador"] => $usuarioEndpoint->altaModerador(),
    ["DELETE", "/usuarios/bajaUsuario"] => $usuarioEndpoint->bajaUsuario(),
    ["PUT", "/usuarios/modificarUsuario"] => $usuarioEndpoint->modificarUsuario(),
    ["GET", "/usuarios/listarUsuarios"] => $usuarioEndpoint->listarUsuarios(),
    ["POST", "/usuarios/iniciarSesion"] => $usuarioEndpoint->iniciarSesion(),
    ["POST", "/usuarios/cerrarSesion"] => $usuarioEndpoint->cerrarSesion(),

    ["POST", "/publicaciones/altaPublicacion"] => $publicacionEndpoint->altaPublicacion(),
    ["DELETE", "/publicaciones/bajaPublicacion"] => $publicacionEndpoint->bajaPublicacion(),
    ["PUT", "/publicaciones/modificarPublicacion"] => $publicacionEndpoint->modificarPublicacion(),
    ["GET", "/publicaciones/listarPublicaciones"] => $publicacionEndpoint->listarPublicaciones(),
    ["GET", "/publicaciones/listarPublicacionesPropias"] => $publicacionEndpoint->listarPublicacionesPropias(),
    ["POST", "/publicaciones/agregarCampoExtra"] => $publicacionEndpoint->agregarCampoExtra(),
    ["DELETE", "/publicaciones/eliminarCampoExtra"] => $publicacionEndpoint->eliminarCampoExtra((int)($_GET["id"] ?? 0)),
    ["GET", "/publicaciones/listarPublicacionFiltro"] => $publicacionEndpoint->listarPublicacionFiltro((string)($_GET["filtro"] ?? "")),

    default => (function () use ($ruta): void {
        http_response_code(404);
        echo json_encode([
            "error" => "Ruta no encontrada",
            "ruta" => $ruta
        ], JSON_UNESCAPED_UNICODE);
    })()
};
