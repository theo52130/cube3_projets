<?php
// Inclure le fichier de configuration pour la connexion à la base de données
include '../config.php';

// Récupérer les produits
$sql_produits = "SELECT * FROM produits";
$result_produits = $conn->query($sql_produits);

if ($result_produits === false) {
    die("Erreur de requête produits : " . $conn->error);
}

$produits = [];
if ($result_produits->num_rows > 0) {
    while ($row = $result_produits->fetch_assoc()) {
        $produits[$row['id']] = $row;
    }
}

// Récupérer les factures et leurs produits associés avec les informations du client
$sql_factures = "
    SELECT f.id AS facture_id, f.date_creation, f.total, f.etat, c.nom AS client_nom, c.email AS client_email, 
           c.adresse AS client_adresse, c.email_entreprise, c.siret, p.id AS produit_id, p.description, p.prix_unitaire, 
           fp.quantite
    FROM factures f
    LEFT JOIN comptes c ON f.client_id = c.id
    LEFT JOIN factures_produits fp ON f.id = fp.facture_id
    LEFT JOIN produits p ON fp.produit_id = p.id
    ORDER BY f.id, p.id;
";

$result_factures = $conn->query($sql_factures);

if ($result_factures === false) {
    die("Erreur de requête factures : " . $conn->error);
}

$factures = [];
while ($row = $result_factures->fetch_assoc()) {
    $facture_id = $row['facture_id'];
    if (!isset($factures[$facture_id])) {
        $factures[$facture_id] = [
            'date_creation' => $row['date_creation'],
            'total' => $row['total'],
            'etat' => $row['etat'],
            'client_nom' => $row['client_nom'],
            'client_email' => $row['client_email'],
            'client_adresse' => $row['client_adresse'],
            'email_entreprise' => $row['email_entreprise'],
            'siret' => $row['siret'],
            'produits' => []
        ];
    }
    if ($row['produit_id']) {
        $factures[$facture_id]['produits'][] = [
            'description' => $row['description'],
            'prix_unitaire' => $row['prix_unitaire'],
            'quantite' => $row['quantite']
        ];
    }
}

// Fermer la connexion à la base de données
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Factures et Produits</title>
    <link rel="stylesheet" href="../assets/css/factures.css">
</head>

<body>
    <div id="pageFactures">
        <h1>Liste des Factures et Produits Associés</h1>

        <?php foreach ($factures as $facture_id => $facture): ?>
            <table>
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Email</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>État</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($facture_id); ?></td>
                        <td><?php echo htmlspecialchars($facture['client_email']); ?></td>
                        <td><?php echo htmlspecialchars(date("d/m/Y", strtotime($facture['date_creation']))); ?></td>
                        <td><?php echo htmlspecialchars(number_format($facture['total'], 2)); ?> €</td>
                        <td><?php echo htmlspecialchars($facture['etat']); ?></td>
                        <td>

                            <!-- <?php foreach ($facture['produits'] as $produit): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($produit['description']); ?></td>
                                                <td><?php echo htmlspecialchars(number_format($produit['prix_unitaire'], 2)); ?> €</td>
                                                <td><?php echo htmlspecialchars($produit['quantite']); ?></td>
                                                <td><?php echo htmlspecialchars(number_format($produit['prix_unitaire'] * $produit['quantite'], 2)); ?> €</td>
                                            </tr>
                                        <?php endforeach; ?> -->
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endforeach; ?>
    </div>
</body>

</html>