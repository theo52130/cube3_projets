<?php

// Optionally define the filesystem path to your system fonts
// otherwise tFPDF will use [path to tFPDF]/font/unifont/ directory
// define("_SYSTEM_TTFONTS", "C:/Windows/Fonts/");

// require('tfpdf.php');

// $pdf = new tFPDF();
// $pdf->AddPage();

// // Add a Unicode font (uses UTF-8)
// $pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
// $pdf->SetFont('DejaVu', '', 14);

// // Load a UTF-8 string from a file and print it
// $txt = file_get_contents('HelloWorld.txt');
// $pdf->Write(8, $txt);

// // Select a standard font (uses windows-1252)
// $pdf->SetFont('Arial', '', 14);
// $pdf->Ln(10);
// $pdf->Write(5, 'The file size of this PDF is only 13 KB.');

// $pdf->Output();


require('tfpdf.php'); // Assurez-vous que le chemin est correct

// Vérifiez si la classe tFPDF est bien incluse
if (!class_exists('tFPDF')) {
    die('tFPDF n\'est pas chargé correctement.');
}

// Création d'une instance de la classe PDF
$pdf = new TFPDF();
$pdf->AliasNbPages(); // Définit un alias pour le nombre total de pages
$pdf->AddPage();

// Ajouter la police TrueType (UTF-8)
$pdf->AddFont('DejaVuSansCondensed', '', 'DejaVuSansCondensed.php'); // Assurez-vous que ce fichier existe
$pdf->SetFont('DejaVuSansCondensed', '', 12);

// Données des factures et produits (exemple statique pour démonstration)
$factures = [
    [
        'id' => 1,
        'date_creation' => '2024-09-01',
        'total' => 120.00,
        'etat' => 'payée',
        'client_nom' => 'Jean Dupont',
        'client_email' => 'jean.dupont@example.com',
        'client_adresse' => '123 Rue Exemple',
        'email_entreprise' => 'contact@exemple.com',
        'siret' => '12345678901234',
        'produits' => [
            ['description' => 'Produit A', 'prix_unitaire' => 40.00],
            ['description' => 'Produit B', 'prix_unitaire' => 30.00],
            ['description' => 'Produit C', 'prix_unitaire' => 50.00]
        ]
    ],
    // Ajoutez d'autres factures ici
];

// Ajouter les informations de factures au PDF
foreach ($factures as $facture) {
    $pdf->SetFont('DejaVuSansCondensed', 'B', 12);
    $pdf->Cell(0, 10, 'Facture ID: ' . $facture['id'], 0, 1);
    $pdf->SetFont('DejaVuSansCondensed', '', 12);
    $pdf->Cell(0, 10, 'Date de Creation: ' . $facture['date_creation'], 0, 1);
    $pdf->Cell(0, 10, 'Total: ' . number_format($facture['total'], 2) . ' EUR', 0, 1);
    $pdf->Cell(0, 10, 'Etat: ' . $facture['etat'], 0, 1);
    $pdf->Cell(0, 10, 'Nom Client: ' . $facture['client_nom'], 0, 1);
    $pdf->Cell(0, 10, 'Email Client: ' . $facture['client_email'], 0, 1);
    $pdf->Cell(0, 10, 'Adresse Client: ' . $facture['client_adresse'], 0, 1);
    if (!empty($facture['email_entreprise'])) {
        $pdf->Cell(0, 10, 'Email Entreprise: ' . $facture['email_entreprise'], 0, 1);
    }
    if (!empty($facture['siret'])) {
        $pdf->Cell(0, 10, 'SIRET: ' . $facture['siret'], 0, 1);
    }

    $pdf->Ln(5); // Espacement

    // Ajouter les produits associés
    $pdf->SetFont('DejaVuSansCondensed', 'B', 12);
    $pdf->Cell(0, 10, 'Produits:', 0, 1);
    $pdf->SetFont('DejaVuSansCondensed', '', 12);

    foreach ($facture['produits'] as $produit) {
        $pdf->Cell(0, 10, $produit['description'] . ' - ' . number_format($produit['prix_unitaire'], 2) . ' EUR', 0, 1);
    }

    $pdf->Ln(10); // Espacement avant la prochaine facture
}

// Générer le fichier PDF
$pdf->Output();
