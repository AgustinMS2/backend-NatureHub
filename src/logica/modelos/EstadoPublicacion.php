<?php
enum EstadoPublicacion: string {
    case BORRADOR = 'BORRADOR';
    case PENDIENTE_REVISION = 'PENDIENTE_REVISION';
    case APROBADA = 'APROBADA';
    case RECHAZADA = 'RECHAZADA';
}
?>
