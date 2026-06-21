<?php
include_once "ParametrosConexion.php";

class Conectar extends mysqli {
    public function __construct() {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            parent::__construct(SERVIDOR, USUARIO, CONTRASENA, BASEDATOS);
            parent::set_charset("utf8mb4");
        } catch (mysqli_sql_exception $e) {
            throw new RuntimeException("Error al conectar a la base de datos: " . $e->getMessage());
        }
    }
}

?>