<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class PublicacionPdfServicio {
    public function generar(DTPublicacion $publicacion, string $nombreSeccion, string $nombreAutor): string {
        $pdf = new FPDF();
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();
        $pdf->SetMargins(15, 15, 15);

        $this->escribirEncabezado($pdf, $publicacion);
        $this->escribirImagen($pdf, $publicacion->getFoto());
        $this->escribirMeta($pdf, $nombreSeccion, $nombreAutor, $publicacion->getFechaCreacion() ?? '');
        $this->escribirSeccion($pdf, 'Areas de habitat', $this->formatearAreas($publicacion->getAreasHabitat()));
        $this->escribirSeccion($pdf, 'Dieta', $publicacion->getDieta());
        $this->escribirSeccion($pdf, 'Horas activas', $publicacion->getHorasActivas());
        $this->escribirCamposExtra($pdf, $publicacion->getCamposExtra());
        $this->escribirPie($pdf);

        return $pdf->Output('S');
    }

    public function nombreArchivo(string $titulo): string {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', trim($titulo));
        $slug = trim($slug, '-');
        return ($slug !== '' ? strtolower($slug) : 'publicacion') . '.pdf';
    }

    private function escribirEncabezado(FPDF $pdf, DTPublicacion $publicacion): void {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(45, 106, 79);
        $pdf->Cell(0, 6, $this->t('NatureHub'), 0, 1);

        $pdf->SetDrawColor(45, 106, 79);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(4);

        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetTextColor(27, 67, 50);
        $pdf->MultiCell(0, 8, $this->t($publicacion->getTitulo()), 0, 'L');

        $pdf->SetFont('Arial', 'I', 12);
        $pdf->SetTextColor(108, 117, 125);
        $pdf->MultiCell(0, 6, $this->t($publicacion->getNombreCientifico()), 0, 'L');
        $pdf->Ln(4);
    }

    private function escribirImagen(FPDF $pdf, ?string $fotoUrl): void {
        $ruta = $this->resolverRutaImagen($fotoUrl);
        if ($ruta === null) {
            return;
        }

        $info = @getimagesize($ruta);
        if ($info === false) {
            return;
        }

        $maxAncho = 120;
        $ancho = $info[0];
        $alto = $info[1];
        $nuevoAlto = ($alto / $ancho) * $maxAncho;

        $y = $pdf->GetY();
        if ($y + $nuevoAlto > 270) {
            $pdf->AddPage();
            $y = $pdf->GetY();
        }

        try {
            $pdf->Image($ruta, 15, $y, $maxAncho, $nuevoAlto);
            $pdf->SetY($y + $nuevoAlto + 6);
        } catch (Exception $e) {
            // Si la imagen no es compatible, se omite sin interrumpir el PDF.
        }
    }

    private function escribirMeta(FPDF $pdf, string $nombreSeccion, string $nombreAutor, string $fecha): void {
        $pdf->SetFillColor(246, 253, 249);
        $pdf->SetDrawColor(216, 243, 220);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(27, 67, 50);

        $yInicio = $pdf->GetY();
        $pdf->Rect(15, $yInicio, 180, 24, 'DF');

        $pdf->SetXY(18, $yInicio + 3);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(108, 117, 125);
        $pdf->Cell(55, 4, $this->t('SECCION'), 0, 0);
        $pdf->Cell(55, 4, $this->t('AUTOR'), 0, 0);
        $pdf->Cell(55, 4, $this->t('PUBLICADO'), 0, 1);

        $pdf->SetX(18);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(27, 67, 50);
        $pdf->Cell(55, 5, $this->t($nombreSeccion), 0, 0);
        $pdf->Cell(55, 5, $this->t($nombreAutor), 0, 0);
        $pdf->Cell(55, 5, $this->t(substr($fecha, 0, 10)), 0, 1);

        $pdf->SetY($yInicio + 28);
    }

    private function escribirSeccion(FPDF $pdf, string $titulo, ?string $contenido): void {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(45, 106, 79);
        $pdf->Cell(0, 7, $this->t($titulo), 0, 1);

        $pdf->SetFont('Arial', '', 11);
        $pdf->SetTextColor(27, 67, 50);
        $pdf->MultiCell(0, 6, $this->t($contenido ?: '-'), 0, 'L');
        $pdf->Ln(3);
    }

    private function escribirCamposExtra(FPDF $pdf, array $camposExtra): void {
        if (empty($camposExtra)) {
            return;
        }

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(45, 106, 79);
        $pdf->Cell(0, 7, $this->t('Informacion adicional'), 0, 1);
        $pdf->Ln(1);

        foreach ($camposExtra as $campo) {
            $etiqueta = is_array($campo) ? ($campo['etiqueta'] ?? '') : ($campo->etiqueta ?? '');
            $valor = is_array($campo) ? ($campo['valor'] ?? '') : ($campo->valor ?? '');

            if ($etiqueta === '' && $valor === '') {
                continue;
            }

            $pdf->SetFillColor(246, 253, 249);
            $pdf->SetDrawColor(216, 243, 220);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor(108, 117, 125);
            $pdf->Cell(55, 6, $this->t($etiqueta), 1, 0, 'L', true);

            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(27, 67, 50);
            $pdf->Cell(125, 6, $this->t($valor), 1, 1, 'L');
        }

        $pdf->Ln(4);
    }

    private function escribirPie(FPDF $pdf): void {
        $pdf->SetDrawColor(224, 237, 230);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(4);

        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(108, 117, 125);
        $pdf->Cell(0, 5, $this->t('Generado desde NatureHub'), 0, 1, 'C');
    }

    private function resolverRutaImagen(?string $fotoUrl): ?string {
        $fotoUrl = trim($fotoUrl ?? '');
        if ($fotoUrl === '') {
            return null;
        }

        if (preg_match('#/uploads/publicaciones/([^/?#]+)#', $fotoUrl, $coincidencia)) {
            $rutaLocal = __DIR__ . '/../uploads/publicaciones/' . $coincidencia[1];
            if (is_file($rutaLocal)) {
                return $rutaLocal;
            }
        }

        if (is_file($fotoUrl)) {
            return $fotoUrl;
        }

        if (filter_var($fotoUrl, FILTER_VALIDATE_URL)) {
            $contenido = @file_get_contents($fotoUrl);
            if ($contenido === false) {
                return null;
            }

            $extension = pathinfo(parse_url($fotoUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
            $temporal = tempnam(sys_get_temp_dir(), 'nh_pdf_') . '.' . $extension;
            if (file_put_contents($temporal, $contenido) !== false) {
                return $temporal;
            }
        }

        return null;
    }

    private function formatearAreas(array $areas): string {
        if (empty($areas)) {
            return '-';
        }
        return implode(', ', array_map('strval', $areas));
    }

    private function t(?string $texto): string {
        $texto = $texto ?? '';
        if ($texto === '') {
            return '';
        }
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
        }
        return utf8_decode($texto);
    }
}
