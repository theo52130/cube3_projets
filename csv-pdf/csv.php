<?php
session_start();

// Vérification de la connexion et du rôle
if (!isset($_SESSION['nom']) || $_SESSION['role'] != 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include "../includes/config.php";

$sql_factures = "
    SELECT 
        f.id AS facture_id, 
        f.date_creation, 
        f.total, 
        f.etat, 
        c.email AS client_email, 
        p.description AS produit_description, 
        p.prix_unitaire, 
        fp.quantite
    FROM factures f
    LEFT JOIN comptes c ON f.client_id = c.id
    LEFT JOIN factures_produits fp ON f.id = fp.facture_id
    LEFT JOIN produits p ON fp.produit_id = p.id
    ORDER BY f.id, p.id;
";

$result_factures = $conn->query($sql_factures);

if ($result_factures === false) {
    die("Erreur de requête : " . $conn->error);
}

// Ouvrir le fichier CSV pour l'écriture
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=factures.csv');

$output = fopen('php://output', 'w');

// Écrire les en-têtes CSV
fputcsv($output, ['Ref Facture', 'Email Client', 'Date', 'Total', 'État', 'Produit', 'Prix Unitaire', 'Quantité']);

// Variable pour stocker l'ID de la dernière facture afin de gérer l'affichage
$last_facture_id = null;

while ($row = $result_factures->fetch_assoc()) {
    // Si c'est une nouvelle facture, écrire les détails de la facture
    if ($last_facture_id !== $row['facture_id']) {
        fputcsv($output, [
            $row['facture_id'],
            $row['client_email'],
            date("d/m/Y", strtotime($row['date_creation'])),
            number_format($row['total'], 2),
            $row['etat'],
            '', // Produit vide pour la première ligne de la facture
            '', // Prix Unitaire vide pour la première ligne de la facture
            '', // Quantité vide pour la première ligne de la facture
        ]);
        $last_facture_id = $row['facture_id'];
    }

    // Écrire les produits associés à la facture
    fputcsv($output, [
        '', // Facture vide pour les lignes de produit
        '', // Email Client vide pour les lignes de produit
        '', // Date vide pour les lignes de produit
        '', // Total vide pour les lignes de produit
        '', // État vide pour les lignes de produit
        $row['produit_description'],
        number_format($row['prix_unitaire'], 2),
        $row['quantite']
    ]);
}

fclose($output);
$conn->close();
exit();
