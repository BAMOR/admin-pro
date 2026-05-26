<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';
require_once 'vendor/autoload.php'; // Si usas Composer

use TCPDF;

// Obtener datos
$stmt = $pdo->query("SELECT COUNT(*) as total FROM ventas");
$total_ventas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT SUM(total) as ingresos FROM ventas");
$ingresos = $stmt->fetch(PDO::FETCH_ASSOC)['ingresos'];

$stmt = $pdo->query("
    SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as cantidad, SUM(total) as total_mes
    FROM ventas
    GROUP BY DATE_FORMAT(fecha, '%Y-%m')
    ORDER BY mes
");
$ventas_mes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Reporte de Ventas');
$pdf->SetHeaderData('', 0, 'Reporte de Ventas', '');
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$pdf->AddPage();

$html = '
<h1>Reporte de Ventas</h1>
<p><strong>Total de Ventas:</strong> ' . $total_ventas . '</p>
<p><strong>Ingresos Totales:</strong> $' . number_format($ingresos, 2) . '</p>

<h2>Ventas por Mes</h2>
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>Mes</th>
            <th>Cantidad</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>';

foreach ($ventas_mes as $v) {
    $html .= '
        <tr>
            <td>' . $v['mes'] . '</td>
            <td>' . $v['cantidad'] . '</td>
            <td>$' . number_format($v['total_mes'], 2) . '</td>
        </tr>';
}

$html .= '
    </tbody>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('reporte_ventas.pdf', 'D');