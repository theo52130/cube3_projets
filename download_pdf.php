<?php
require 'vendor/autoload.php'; // Utilisation de Dompdf

use Dompdf\Dompdf;

$dompdf = new Dompdf();
$dompdf->loadHtml('<h1>Facture</h1><p>Détails de la facture...</p>');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("facture.pdf");
?>
