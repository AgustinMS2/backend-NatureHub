<?php
enum EstadoPublicacion: string {
    case PENDIENTE_REVISION = 'PENDIENTE_REVISION';
    case APROBADA = 'APROBADA';
    case RECHAZADA = 'RECHAZADA';
}
?>