<?php
include_once __DIR__ . "/../servicios/Fabrica.php";
include_once __DIR__ . "/../servicios/DTs/DTUsuario.php";
include_once __DIR__ . "/../servicios/DTs/DTModerador.php";
include_once __DIR__ . "/../servicios/DTs/DTAdministrador.php";

class Autenticacion {
    public static function autenticar(array $rolesPermitidos = []): DTUsuario {
        
        $headers = getallheaders();
        $token = $headers['X-Auth-Token'] ?? $headers['x-auth-token'] ?? '';

        if (empty($token)) {
            http_response_code(401);
            echo json_encode(["error" => "No autenticado"]);
            exit();
        }

        $controlador = Fabrica::getInstance()->getIUsuarioController();

        try {
            $dtUsuario = $controlador->validarToken($token);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(["error" => $e->getMessage()]);
            exit();
        }

        if (!empty($rolesPermitidos)) {
            $rol = match(true) {
                $dtUsuario instanceof DTAdministrador => "ADMINISTRADOR",
                $dtUsuario instanceof DTModerador => "MODERADOR",
                default => "USUARIO"
            };

            if (!in_array($rol, $rolesPermitidos, true)) {
                http_response_code(403);
                echo json_encode(["error" => "No tiene permisos para esta acción"]);
                exit();
            }
        }

        return $dtUsuario;
    }
}
?>