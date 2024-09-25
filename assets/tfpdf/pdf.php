<?php
include "tfpdf.php";
define("TVA", 0.196);

class pdf extends tFPDF
{
    function __construct()
    {
        parent::__construct();
        $this->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
        $this->AddFont('DejaVu', 'B', 'DejaVuSansCondensed-Bold.ttf', true);
        $this->AddFont('DejaVu', 'I', 'DejaVuSansCondensed-Oblique.ttf', true); // Add italic font
        $this->SetCreator(utf8_decode("Damien BARRERE, tby-innovations.com"));
        $this->SetFont('DejaVu', '', 12);
    }

    function hautDePage()
    {
        $position = 0;
        $this->SetFont('DejaVu', 'B', 12);
        $this->SetTextColor(0, 0, 200);
        $this->SetXY(10, 30);
        $this->Cell(50, 6, "tby-innovations.com", 0, 2, '', false);
        $this->SetFont('DejaVu', '', 12);
        $this->SetTextColor(0, 0, 0);
        $this->MultiCell(50, 5, utf8_decode("Adresse de l'entreprise\nCP VILLE\nTel: 05.00.00.00.00\nFax: 05.00.00.00.01"), 0, 'L', false);

        $this->SetXY(60, 30);
        $this->SetFillColor(200, 200, 200);
        $this->SetFont('DejaVu', 'B', 15);
        $this->Cell(140, 6, "FACTURE", 1, 2, 'C', true);
        $this->SetFont('DejaVu', '', 12);
        $this->SetXY(60, 38);
        $this->MultiCell(130, 5, utf8_decode("Facture n° : Votre numéro de facture\nDate de commande : " . date("m.d.y") . "\nMode de paiement : Carte Bancaire"), '', 'L', false);
        $this->SetTitle(utf8_decode("Facture n° : Votre numéro de facture"));

        $this->SetXY(10, 60);
        $this->SetFillColor(200, 200, 200);
        $this->SetFont('DejaVu', 'B', 12);
        $this->Cell(90, 6, utf8_decode("Adresse de facturation"), 1, 2, 'C', true);
        $this->SetFont('DejaVu', '', 12);
        $this->MultiCell(90, 5, utf8_decode("Client NOM Prénom\nAdresse 1\nAdresse 2\nCode Postal Ville"), 'LRB', 'L', false);
        $position = $this->getY();

        $this->SetXY(110, 60);
        $this->SetFillColor(200, 200, 200);
        $this->SetFont('DejaVu', 'B', 12);
        $this->Cell(90, 6, utf8_decode("Adresse de livraison"), 1, 2, 'C', true);
        $this->SetFont('DejaVu', '', 12);
        $this->MultiCell(90, 5, utf8_decode("Livraison à l'adresse de facturation"), 'LRB', 'L', false);

        if ($this->getY() > $position) {
            $position = $this->getY();
        }
        $this->SetXY(10, $position + 5);
    }

    function tableArticles()
    {
        $position = 0;
        $prixTotalHorsTaxes = 0;

        $datas = array();
        for ($ij = 0; $ij < 5; $datas[] = array("ABCD", utf8_decode("Désignation de l'article $ij"), "10" . chr(128), "2", "20" . chr(128)), $prixTotalHorsTaxes += 20, $ij++);

        $header = array(utf8_decode('Réf'), utf8_decode('Désignation'), utf8_decode('Prix Unitaire HT'), utf8_decode('Qté'), utf8_decode('Prix Total HT'));
        $w = array(20, 102, 25, 20, 23);
        $al = array('C', 'L', 'C', 'C', 'C');

        $this->table($header, $w, $al, $datas);

        $this->SetY($this->GetY() + 5);

        $this->setX(108);
        $this->Cell(74, 6, utf8_decode("Total Hors Taxes"), 1, 0, 'L');
        $this->Cell(19, 6, $prixTotalHorsTaxes . chr(128), 1, 2, 'C');

        $this->setX(108);
        $this->Cell(74, 6, utf8_decode("TVA à " . (TVA * 100) . " %"), 1, 0, 'L');
        $totalTVA = $prixTotalHorsTaxes * TVA;
        $this->Cell(19, 6, $totalTVA . chr(128), 1, 2, 'C');

        $this->setX(108);
        $this->Cell(74, 6, utf8_decode("Total TTC"), 1, 0, 'L');
        $this->Cell(19, 6, ($prixTotalHorsTaxes + $totalTVA) . chr(128), 1, 2, 'C');
    }

    function table($header, $w, $al, $datas)
    {
        $this->SetFont('DejaVu', 'B', 12);
        for ($i = 0; $i < count($header); $this->Cell($w[$i], 7, $header[$i], 1, 0, $al[$i]), $i++);
        $this->Ln();

        $this->SetFont('DejaVu', '', 12);
        foreach ($datas as $row) {
            for ($i = 0; $i < count($row); $this->Cell($w[$i], 6, $row[$i], 1, 0, $al[$i]), $i++);
            $this->Ln();
        }
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('DejaVu', 'I', 8);
        $this->Cell(0, 4, utf8_decode('Page ' . $this->PageNo() . '/{nb}'), 0, 2, 'C');
        $this->MultiCell(0, 4, utf8_decode("tby-innovations.com\n"), 0, 'C', false);
    }
}

$pdf = new pdf();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->hautDePage();
$pdf->tableArticles();
$pdf->Output();
