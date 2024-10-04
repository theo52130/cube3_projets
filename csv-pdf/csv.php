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
    header("Location: ../login.php");
    exit();
}

// Connexion à la base de données
require_once '../config.php';

// Vérification de l'ID de la facture dans le POST
if (isset($_POST['id'])) {

    $facture_id = intval($_POST['id']);

    // Récupération de la facture en fonction de l'ID
    $query = "
        SELECT f.id AS Ref, c.email AS Email, f.date_creation AS Date, f.total AS Total, f.etat AS État 
        FROM factures f 
        JOIN comptes c ON f.client_id = c.id 
        WHERE f.id = $facture_id";

    $result = mysqli_query($conn, $query);

    // Vérification de la présence des données
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Formatage des données en CSV
        $csv_data = array();
        $csv_data[] = '"Ref","Email","Date","Total","État"';
        $csv_data[] = '"' . implode('","', $row) . '"';

        // Ajout des produits associés à la facture
        $query_produits = "
            SELECT p.description AS Description, fp.quantite AS Quantité, p.prix_unitaire AS Prix
            FROM factures_produits fp
            JOIN produits p ON fp.produit_id = p.id
            WHERE fp.facture_id = $facture_id";

        $result_produits = mysqli_query($conn, $query_produits);

        if (mysqli_num_rows($result_produits) > 0) {
            $csv_data[] = '';
            $csv_data[] = '"Produits Associés"';
            $csv_data[] = '"Description","Quantité","Prix Unitaire"';

            while ($produit = mysqli_fetch_assoc($result_produits)) {
                $csv_data[] = '"' . implode('","', $produit) . '"';
            }
        }

        // Définition de l'en-tête pour télécharger le fichier CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="facture_' . $facture_id . '.csv"');

        // Affichage du CSV
        echo implode("\n", $csv_data);
    } else {
        echo "Facture non trouvée.";
    }
} else {
    echo "ID de facture non spécifié.";
}

// Fermeture de la connexion
mysqli_close($conn);
