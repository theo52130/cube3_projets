<?php
session_start();

if (
    !isset($_SESSION['nom']) ||
    !(
        $_SESSION['role'] == 'admin' ||
        $_SESSION['role'] == 'employer' ||
        $_SESSION['user_id'] == $_SESSION['client_id']
    )
) {
    header("Location: ../public/login.php");
    exit();
}

// Inclusion de la librairie FPDF
include "../assets/fpdf/fpdf.php";
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
        $this->SetCreator("www.tbyInnovations.com");
    }

    // En-tête de la facture
    function hautDePage($compte, $facture)
    {
        // Adresse
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 200);
        $this->SetXY(10, 30);
        $this->Cell(50, 6, "www.tbyInnovations.com", 0, 2, '', false);
        $this->SetFont('Arial', '', 12);
        $this->SetTextColor(0, 0, 0);
        $this->MultiCell(50, 5, "7bis Av. Robert Schuman\n51100\nTel: 03.26.40.04.45", 0, 'L', false);

        // Informations Facture
        $this->SetXY(65, 30);
        $this->SetFillColor(200, 200, 200);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(140, 6, "FACTURE", 1, 2, 'C', true);
        $this->SetFont('Arial', '', 12);
        $this->SetXY(65, 38);
        $this->MultiCell(130, 5, "Facture numero : " . $facture['id'] . "\nDate de creation : " . date("d.m.y", strtotime($facture['date_creation'])), '', 'L', false);
        $this->SetTitle("Facture numéro : " . $facture['id']);

        // Adresse de Facturation
        $this->SetXY(10, 60);
        $this->SetFillColor(200, 200, 200);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(90, 6, "Adresse de facturation", 1, 2, 'C', true);
        $this->SetFont('Arial', '', 12);
        $this->MultiCell(90, 5, "Client: " . $compte['nom'] . "\n" . $compte['adresse'] . "\n" . (!empty($compte['adresse_entreprise']) ? $compte['adresse_entreprise'] . "\n" : ""), 'LRB', 'L', false);
        $position = $this->getY();

        // Adresse de livraison
        $this->SetXY(110, 60);
        $this->SetFillColor(200, 200, 200);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(90, 6, "Adresse de livraison", 1, 2, 'C', true);
        $this->SetFont('Arial', '', 12);
        $this->MultiCell(90, 5, "Livraison e l'adresse de facturation", 'LRB', 'L', false);

        if ($this->getY() > $position) {
            $position = $this->getY();
        }
        $this->SetXY(10, $position + 5);
    }

    // Préparation de la génération de la table
    function tableArticles($produits)
    {
        $prixTotalHorsTaxes = 0;

        // Vérifiez que $produits n'est pas vide
        if (empty($produits)) {
            $this->Cell(0, 10, "Aucun produit trouvé pour cette facture.", 0, 1, 'C');
            return; // Sortir si aucune donnée
        }

        // Tableau contenant les titres des colonnes
        $header = array('Ref', 'Designation', 'Prix Unitaire HT', 'Qte', 'Prix Total HT');
        $w = array(20, 80, 34, 20, 36);
        $al = array('C', 'L', 'C', 'C', 'C');

        // Impression des entêtes de colonnes
        $this->SetFont('Arial', 'B', 12);
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, $al[$i]);
        }
        $this->Ln();

        // Impression des lignes de la table
        $this->SetFont('Arial', '', 12);
        foreach ($produits as $produit) {
            // Vérifiez que les données nécessaires existent
            if (!isset($produit['id'], $produit['description'], $produit['prix_unitaire'], $produit['quantite'])) {
                continue; // Passer à l'itération suivante si les données sont manquantes
            }

            $prixTotal = $produit['prix_unitaire'] * $produit['quantite'];
            $prixTotalHorsTaxes += $prixTotal;

            $this->Cell($w[0], 6, $produit['id'], 1);
            $this->Cell($w[1], 6, $produit['description'], 1);
            $this->Cell($w[2], 6, number_format($produit['prix_unitaire'], 2, ',', ' ') . " euros", 1);
            $this->Cell($w[3], 6, $produit['quantite'], 1);
            $this->Cell($w[4], 6, number_format($prixTotal, 2, ',', ' ') . " euros", 1);
            $this->Ln();
        }

        // On se positionne en dessous de la table pour écrire le total
        $this->SetY($this->GetY() + 5);

        $this->setX(108);
        $this->Cell(54, 6, "Total Hors Taxes", 1, 0, 'L');
        $this->Cell(39, 6, number_format($prixTotalHorsTaxes, 2, ',', ' ') . " euros", 1, 2, 'C');

        $this->setX(108);
        $this->Cell(54, 6, "TVA à " . (TVA * 100) . " %", 1, 0, 'L');
        $totalTVA = $prixTotalHorsTaxes * TVA;
        $this->Cell(39, 6, number_format($totalTVA, 2, ',', ' ') . " euros", 1, 2, 'C');

        $this->setX(108);
        $this->Cell(54, 6, "Total TTC", 1, 0, 'L');
        $this->Cell(39, 6, number_format($prixTotalHorsTaxes + $totalTVA, 2, ',', ' ') . " euros", 1, 2, 'C');
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
        $this->MultiCell(0, 4, "www.tby-Innovations.com\n", 0, 'C', false);
    }
}

require '../includes/config.php';

// Vérifiez si l'ID est défini et valide
if (isset($_POST['id'])) {
    $idFacture = intval($_POST['id']);

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

    // Récupérer les produits associés à la facture
    $queryProduits = "SELECT p.id, p.description, p.prix_unitaire, fp.quantite
                      FROM produits p
                      JOIN factures_produits fp ON p.id = fp.produit_id
                      WHERE fp.facture_id = ?";
    $stmtProduits = $conn->prepare($queryProduits);
    $stmtProduits->bind_param("i", $idFacture);
    $stmtProduits->execute();
    $resultProduits = $stmtProduits->get_result();
    $produits = $resultProduits->fetch_all(MYSQLI_ASSOC);
    $stmtProduits->close();

    // Envoyer les en-têtes HTTP pour afficher le PDF inline
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="facture_' . $facture['id'] . '.pdf"');

    // Génération du PDF
    $pdf = new pdf();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->hautDePage($compte, $facture);
    $pdf->tableArticles($produits);
    $pdf->Output("I", "facture_" . $facture['id'] . ".pdf"); // Changer "D" en "I" pour afficher dans le navigateur
} else {
    die('ID de facture manquant.');
}
