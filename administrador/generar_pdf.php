<?php
require_once('../orm/dataBase.php');
require_once('../orm/orm.php');
require_once('../orm/libro.php');
require_once('../lib/tcpdf/tcpdf.php'); // Asegúrate de tener TCPDF instalado

// Conexión a la base de datos
$db = new Database();
$cnn = $db->getConnection();
$libroModelo = new libro($cnn);

// Obtener datos para el reporte
$totalLibros = $libroModelo->getCount();
$librosPorCategoria = $libroModelo->getCountByCategory();
$ultimosLibros = $libroModelo->getLastAdded(10);
$librosPorEditorial = $libroModelo->getCountByEditorial();

// Crear nuevo documento PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Información del documento
$pdf->SetCreator('Biblioteca Universitaria');
$pdf->SetAuthor('Sistema de Biblioteca');
$pdf->SetTitle('Reporte de Libros');
$pdf->SetSubject('Reporte PDF de Libros');

// Margenes
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Saltos de página automáticos
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Establecer fuente
$pdf->SetFont('helvetica', '', 10);

// Agregar página
$pdf->AddPage();

// Logo o título
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Reporte de Libros - Biblioteca Universitaria', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1);
$pdf->Ln(5);

// Estadísticas generales
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Estadísticas Generales', 0, 1);
$pdf->SetFont('helvetica', '', 12);

$html = '<table border="0" cellpadding="5">
    <tr>
        <td width="40%"><strong>Total de libros:</strong></td>
        <td width="60%">'.$totalLibros.'</td>
    </tr>
    <tr>
        <td><strong>Categorías diferentes:</strong></td>
        <td>'.count($librosPorCategoria).'</td>
    </tr>
    <tr>
        <td><strong>Editoriales diferentes:</strong></td>
        <td>'.count($librosPorEditorial).'</td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, false, false, '');
$pdf->Ln(10);

// Libros por categoría
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Libros por Categoría', 0, 1);
$pdf->SetFont('helvetica', '', 12);

$html = '<table border="1" cellpadding="5">
    <thead>
        <tr style="background-color:#f2f2f2;">
            <th width="50%"><strong>Categoría</strong></th>
            <th width="25%"><strong>Cantidad</strong></th>
            <th width="25%"><strong>Porcentaje</strong></th>
        </tr>
    </thead>
    <tbody>';

foreach ($librosPorCategoria as $categoria) {
    $porcentaje = round(($categoria['total'] / $totalLibros) * 100, 2);
    $html .= '<tr>
        <td>'.$categoria['categoria'].'</td>
        <td>'.$categoria['total'].'</td>
        <td>'.$porcentaje.'%</td>
    </tr>';
}

$html .= '</tbody></table>';
$pdf->writeHTML($html, true, false, false, false, '');
$pdf->Ln(10);

// Últimos libros añadidos
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Últimos Libros Añadidos', 0, 1);
$pdf->SetFont('helvetica', '', 12);

$html = '<table border="1" cellpadding="5">
    <thead>
        <tr style="background-color:#f2f2f2;">
            <th width="30%"><strong>Título</strong></th>
            <th width="25%"><strong>Autor</strong></th>
            <th width="20%"><strong>Editorial</strong></th>
            <th width="15%"><strong>Año</strong></th>
            <th width="10%"><strong>Ingreso</strong></th>
        </tr>
    </thead>
    <tbody>';

foreach ($ultimosLibros as $libro) {
    $html .= '<tr>
        <td>'.$libro['titulo'].'</td>
        <td>'.$libro['autor'].'</td>
        <td>'.$libro['editorial'].'</td>
        <td>'.($libro['anio_publicacion'] ?? 'N/A').'</td>
        <td>'.date('d/m/Y', strtotime($libro['fecha_ingreso'])).'</td>
    </tr>';
}

$html .= '</tbody></table>';
$pdf->writeHTML($html, true, false, false, false, '');

// Salida del PDF
$pdf->Output('reporte_libros_'.date('Ymd').'.pdf', 'D');
?>