<?php
// Inclusion de la librairie FPDF
include "fpdf.php";
define("TVA", 0.196);

/**
 * Classe PDF hérite de FPDF, permet de générer des fichiers PDF
 */
class pdf extends FPDF
{
    /**
     * Constructeur
     */
    function __construct()
    {
        parent::__construct();
        $this->SetCreator("Damien BARRERE, www.crac-design.com");
    }

    // En-tête de la facture
    function hautDePage()
    {
        $position = 0;
        // Adresse
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 200);
        $this->SetXY(10, 30);
        $this->Cell(50, 6, "www.Crac-Design.com", 0, 2, '', false);
        $this->SetFont('Arial', '', 12);
        $this->SetTextColor(0, 0, 0);
        $this->MultiCell(50, 5, "Adresse de l'entreprise\nCP VILLE\nTel: 05.00.00.00.00\nFax: 05.00.00.00.01", 0, 'L', false);

        // Informations Facture
        $this->SetXY(60, 30);
        $this->SetFillColor(200, 200, 200);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(140, 6, "FACTURE", 1, 2, 'C', true);
        $this->SetFont('Arial', '', 12);
        $this->SetXY(60, 38);
        $this->MultiCell(130, 5, "Facture n° : Votre numéro de facture\nDate de commande : " . date("m.d.y") . "\nMode de paiement : Carte Bancaire", '', 'L', false);
        $this->SetTitle("Facture n° : Votre numéro de facture");

        // Adresse de Facturation
        $this->SetXY(10, 60);
        $this->SetFillColor(200, 200, 200);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(90, 6, "Adresse de facturation", 1, 2, 'C', true);
        $this->SetFont('Arial', '', 12);
        $this->MultiCell(90, 5, "Client NOM Prénom\nAdresse 1\nAdresse 2\nCode Postal Ville", 'LRB', 'L', false);
        $position = $this->getY();

        // Adresse de livraison
        $this->SetXY(110, 60);
        $this->SetFillColor(200, 200, 200);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(90, 6, "Adresse de livraison", 1, 2, 'C', true);
        $this->SetFont('Arial', '', 12);
        $this->MultiCell(90, 5, "Livraison à l'adresse de facturation", 'LRB', 'L', false);

        if ($this->getY() > $position) {
            $position = $this->getY();
        }
        $this->SetXY(10, $position + 5);
    }

    // Préparation de la génération de la table
    function tableArticles()
    {
        $position = 0;
        $prixTotalHorsTaxes = 0;

        // Création des données qui seront contenues dans la table
        $datas = array();
        for ($ij = 0; $ij < 10; $ij++) {
            $datas[] = array("ABCD", "Désignation de l'article $ij", "10" . chr(128), "2", "20" . chr(128));
            $prixTotalHorsTaxes += 20;
        }

        // Tableau contenant les titres des colonnes
        $header = array('Réf', 'Désignation', 'Prix Unitaire HT', 'Qté', 'Prix Total HT');
        // Tableau contenant la largeur des colonnes
        $w = array(20, 102, 25, 20, 23);
        // Tableau contenant le centrage des colonnes
        $al = array('C', 'L', 'C', 'C', 'C');

        // Génération de la table
        $this->table($header, $w, $al, $datas);

        // On se positionne en dessous de la table pour écrire le total
        $this->SetY($this->GetY() + 5);

        $this->setX(108);
        $this->Cell(74, 6, "Total Hors Taxes", 1, 0, 'L');
        $this->Cell(19, 6, $prixTotalHorsTaxes . chr(128), 1, 2, 'C');

        $this->setX(108);
        $this->Cell(74, 6, "TVA à " . (TVA * 100) . " %", 1, 0, 'L');
        $totalTVA = $prixTotalHorsTaxes * TVA;
        $this->Cell(19, 6, $totalTVA . chr(128), 1, 2, 'C');

        $this->setX(108);
        $this->Cell(74, 6, "Total TTC", 1, 0, 'L');
        $this->Cell(19, 6, ($prixTotalHorsTaxes + $totalTVA) . chr(128), 1, 2, 'C');
    }

    // Méthode manquante pour générer la table des articles
    function table($header, $w, $al, $datas)
    {
        // Impression des entêtes de colonnes
        $this->SetFont('Arial', 'B', 12);
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, $al[$i]);
        }
        $this->Ln();

        // Impression des lignes de la table
        $this->SetFont('Arial', '', 12);
        foreach ($datas as $row) {
            for ($i = 0; $i < count($row); $i++) {
                $this->Cell($w[$i], 6, $row[$i], 1, 0, $al[$i]);
            }
            $this->Ln();
        }
    }

    // Pied de page
    function Footer()
    {
        // Positionnement à 1,5 cm du bas
        $this->SetY(-15);
        // Police Arial italique 8
        $this->SetFont('Arial', 'I', 8);
        // Numéro de page
        $this->Cell(0, 4, 'Page ' . $this->PageNo() . '/{nb}', 0, 2, 'C');
        $this->MultiCell(0, 4, "www.Crac-Design.com\n", 0, 'C', false);
    }
}

// Instanciation de la classe
$pdf = new pdf();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->hautDePage();
$pdf->tableArticles();
$pdf->Output();
