<?php
// Inclusion de la librairie TFPDF
include "tfpdf.php";
define("TVA", 0.196);

/**
 * Classe PDF hérite de TFPDF, permet de générer des fichiers PDF
 */
class pdf extends TFPDF
{
    /**
     * Constructeur
     */
    function __construct()
    {
        parent::__construct();
        $this->SetCreator("www.tby-Innovations.com");
        $this->AddFont('Courier', '', 'Courier.php'); // Ajout de la police
        $this->SetFont('Courier', '', 12); // Définir la police par défaut
    }

    // En-tête de la facture
    function hautDePage($compte, $facture)
    {
        $position = 0;
        // Adresse
        $this->SetFont('Courier', 'B', 12);
        $this->SetTextColor(0, 0, 200);
        $this->SetXY(10, 30);
        $this->Cell(50, 6, "www.tbyInnovations.com", 0, 2, '', false);
        $this->SetFont('Courier', '', 12);
        $this->SetTextColor(0, 0, 0);
        $this->MultiCell(50, 5, "Adresse de l'entreprise\nCP VILLE\nTel: 05.00.00.00.00\nFax: 05.00.00.00.01", 0, 'L', false);

        // Informations Facture
        $this->SetXY(65, 30);
        $this->SetFillColor(200, 200, 200);
        $this->SetFont('Courier', 'B', 15);
        $this->Cell(140, 6, "FACTURE", 1, 2, 'C', true);
        $this->SetFont('Courier', '', 12);
        $this->SetXY(65, 38);
        $this->MultiCell(130, 5, "Facture numéro : " . $facture['id'] . "\nDate de création : " . date("d.m.y", strtotime($facture['date_creation'])), '', 'L', false);
        $this->SetTitle("Facture numéro : " . $facture['id']);

        // Adresse de Facturation
        $this->SetXY(10, 60);
        $this->SetFillColor(200, 200, 200);
        $this->SetFont('Courier', 'B', 12);
        $this->Cell(90, 6, "Adresse de facturation", 1, 2, 'C', true);
        $this->SetFont('Courier', '', 12);
        $this->MultiCell(90, 5, "Client: " . $compte['nom'] . "\n" . $compte['adresse'] . "\n" . (!empty($compte['adresse_entreprise']) ? $compte['adresse_entreprise'] . "\n" : "") . "Code Postal Ville", 'LRB', 'L', false);
        $position = $this->getY();

        // Adresse de livraison
        $this->SetXY(110, 60);
        $this->SetFillColor(200, 200, 200);
        $this->SetFont('Courier', 'B', 12);
        $this->Cell(90, 6, "Adresse de livraison", 1, 2, 'C', true);
        $this->SetFont('Courier', '', 12);
        $this->MultiCell(90, 5, "Livraison à l'adresse de facturation", 'LRB', 'L', false);

        if ($this->getY() > $position) {
            $position = $this->getY();
        }
        $this->SetXY(10, $position + 5);
    }

    // Préparation de la génération de la table
    function tableArticles($produits)
    {
        $position = 0;
        $prixTotalHorsTaxes = 0;

        // Vérifiez que $produits n'est pas vide
        if (empty($produits)) {
            $this->Cell(0, 10, "Aucun produit trouvé pour cette facture.", 0, 1, 'C');
            return; // Sortir si aucune donnée
        }

        // Tableau contenant les titres des colonnes
        $header = array('Ref', 'Désignation', 'Prix Unitaire HT', 'Qte', 'Prix Total HT');
        $w = array(20, 88, 34, 20, 28);
        $al = array('C', 'L', 'C', 'C', 'C');

        // Impression des entêtes de colonnes
        $this->SetFont('Courier', 'B', 12);
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, $al[$i]);
        }
        $this->Ln();

        // Impression des lignes de la table
        $this->SetFont('Courier', '', 12);
        foreach ($produits as $produit) {
            // Vérifiez que les données nécessaires existent
            if (!isset($produit['id'], $produit['description'], $produit['prix_unitaire'], $produit['quantite'])) {
                continue; // Passer à l'itération suivante si les données sont manquantes
            }

            $prixTotal = $produit['prix_unitaire'] * $produit['quantite'];
            $prixTotalHorsTaxes += $prixTotal;

            $this->Cell($w[0], 6, $produit['id'], 1);
            $this->Cell($w[1], 6, $produit['description'], 1);
            $this->Cell($w[2], 6, number_format($produit['prix_unitaire'], 2, ',', ' ') . " €", 1);
            $this->Cell($w[3], 6, $produit['quantite'], 1);
            $this->Cell($w[4], 6, number_format($prixTotal, 2, ',', ' ') . " €", 1);
            $this->Ln();
        }

        // On se positionne en dessous de la table pour écrire le total
        $this->SetY($this->GetY() + 5);

        $this->setX(108);
        $this->Cell(74, 6, "Total Hors Taxes", 1, 0, 'L');
        $this->Cell(19, 6, number_format($prixTotalHorsTaxes, 2, ',', ' ') . " €", 1, 2, 'C');

        $this->setX(108);
        $this->Cell(74, 6, "TVA à " . (TVA * 100) . " %", 1, 0, 'L');
        $totalTVA = $prixTotalHorsTaxes * TVA;
        $this->Cell(19, 6, number_format($totalTVA, 2, ',', ' ') . " €", 1, 2, 'C');

        $this->setX(108);
        $this->Cell(74, 6, "Total TTC", 1, 0, 'L');
        $this->Cell(19, 6, number_format($prixTotalHorsTaxes + $totalTVA, 2, ',', ' ') . " €", 1, 2, 'C');
    }

    // Méthode pour générer la table des articles
    function table($header, $w, $al, $datas)
    {
        // Impression des entêtes de colonnes
        $this->SetFont('Courier', 'B', 12);
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, $al[$i]);
        }
        $this->Ln();

        // Impression des lignes de la table
        $this->SetFont('Courier', '', 12);
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
        // Police Courier italique 8
        $this->SetFont('Courier', 'I', 8);
        // Numéro de page
        $this->Cell(0, 4, 'Page ' . $this->PageNo() . '/{nb}', 0, 2, 'C');
        $this->MultiCell(0, 4, "www.tby-Innovations.com\n", 0, 'C', false);
    }
}

require '../../config.php';

// Vérifiez si l'ID est défini et valide
if (isset($_GET['id'])) {
    $idFacture = intval($_GET['id']);

    // Récupérer les informations de la facture
    $queryFacture = "SELECT * FROM factures WHERE id = ?";
    $stmtFacture = $conn->prepare($queryFacture);
    $stmtFacture->bind_param("i", $idFacture);
    $stmtFacture->execute();
    $resultFacture = $stmtFacture->get_result();
    $facture = $resultFacture->fetch_assoc();
    $stmtFacture->close();

    if (!$facture) {
        die('Aucune facture trouvée.');
    }

    // Récupérer les informations du client
    $queryClient = "SELECT * FROM comptes WHERE id = ?";
    $stmtClient = $conn->prepare($queryClient);
    $stmtClient->bind_param("i", $facture['client_id']);
    $stmtClient->execute();
    $resultClient = $stmtClient->get_result();
    $compte = $resultClient->fetch_assoc();
    $stmtClient->close();

    // Vérifiez que le compte a été trouvé
    if (!$compte) {
        die('Aucun client trouvé.');
    }

    // Récupérer les produits associés à la facture
    $queryProduits = "
        SELECT p.*, fp.quantite 
        FROM produits p
        JOIN factures_produits fp ON p.id = fp.produit_id
        WHERE fp.facture_id = ?";
    $stmtProduits = $conn->prepare($queryProduits);
    $stmtProduits->bind_param("i", $idFacture);
    $stmtProduits->execute();
    $resultProduits = $stmtProduits->get_result();
    $produits = $resultProduits->fetch_all(MYSQLI_ASSOC);
    $stmtProduits->close();

    // Vérifiez que des produits ont été trouvés
    if (empty($produits)) {
        die('Aucun produit trouvé.');
    }
}

// Instanciation de la classe
$pdf = new pdf();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->hautDePage($compte, $facture);
$pdf->tableArticles($produits);
$pdf->Output();
