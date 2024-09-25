<?php
require('fpdf/fpdf.php'); // Assurez-vous que le chemin est correct

// Vérifiez si la classe FPDF est bien incluse
if (!class_exists('FPDF')) {
    die('FPDF n\'est pas chargé correctement.');
}

// Création d'une sous-classe de FPDF pour ajouter des fonctionnalités personnalisées
class PDF extends FPDF
{
    // Fonction d'en-tête
    function Header()
    {
        $this->SetFont('DejaVuSansCondensed', 'B', 12);
        $this->Cell(0, 10, 'Liste des Factures et Produits Associés', 0, 1, 'C');
        $this->Ln(5);
    }

    // Fonction de pied de page
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('DejaVuSansCondensed', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Création d'une instance de la classe PDF
$pdf = new PDF();
$pdf->AliasNbPages(); // Définit un alias pour le nombre total de pages
$pdf->AddPage();

// Ajouter une police TrueType (UTF-8)
$pdf->AddFont('DejaVuSansCondensed', '', 'tfpdf/font/unifont/DejaVuSansCondensed-Bold.ttf'); // Assurez-vous que le fichier PHP généré est bien à cet endroit
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
