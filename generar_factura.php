<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';
require_once 'vendor/autoload.php';

use TCPDF;

if (!isset($_SESSION['carrito']) || count($_SESSION['carrito']) === 0) {
    header("Location: carrito.php");
    exit();
}

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Calcular total
$total = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// Crear PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Factura de Compra');
$pdf->SetHeaderData('', 0, 'Factura de Compra', '');
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$pdf->AddPage();

$html = '
<h1>Factura de Compra</h1>
<p><strong>Cliente:</strong> ' . $usuario['nombre'] . '</p>
<p><strong>Email:</strong> ' . $usuario['email'] . '</p>
<p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>

<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>';

foreach ($_SESSION['carrito'] as $item) {
    $html .= '
        <tr>
            <td>' . $item['nombre'] . '</td>
            <td>$' . $item['precio'] . '</td>
            <td>' . $item['cantidad'] . '</td>
            <td>$' . number_format($item['precio'] * $item['cantidad'], 2) . '</td>
        </tr>';
}

$html .= '
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong>Total:</strong></td>
            <td><strong>$' . number_format($total, 2) . '</strong></td>
        </tr>
    </tfoot>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('factura.pdf', 'D');