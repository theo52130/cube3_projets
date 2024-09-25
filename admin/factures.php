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
           c.adresse AS client_adresse, c.email_entreprise, c.siret, p.id AS produit_id, p.description, p.prix_unitaire
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
            'prix_unitaire' => $row['prix_unitaire']
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
    <style>
        /* Styles CSS compact */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
            color: #333;
        }

        h1,
        h3 {
            margin: 0 0 10px 0;
            color: #333;
        }

        h1 {
            font-size: 20px;
        }

        h3 {
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #fff;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f0f0f0;
            font-weight: normal;
        }

        table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        table tbody tr:hover {
            background-color: #f5f5f5;
        }

        .produits {
            margin-top: 10px;
        }

        .produits table {
            margin-top: 5px;
        }

        .produits th {
            background-color: #e0e0e0;
        }

        .client-info {
            margin-top: 15px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }

        .client-info h4 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .client-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>

<body>
    <div id="pageFactures">
        <h1>Liste des Factures et Produits Associés</h1>

        <?php foreach ($factures as $facture_id => $facture): ?>
            <table>
                <thead>
                    <tr>
                        <th colspan="4">Facture ID: <?php echo htmlspecialchars($facture_id); ?></th>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <th>Total</th>
                        <th>État</th>
                        <th>Produits</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($facture['date_creation']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($facture['total'], 2)); ?> €</td>
                        <td><?php echo htmlspecialchars($facture['etat']); ?></td>
                        <td>
                            <div class="produits">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Prix Unitaire</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($facture['produits'] as $produit): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($produit['description']); ?></td>
                                                <td><?php echo htmlspecialchars(number_format($produit['prix_unitaire'], 2)); ?> €</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Informations du client -->
            <div class="client-info">
                <h4>Informations Client</h4>
                <p><strong>Nom :</strong> <?php echo htmlspecialchars($facture['client_nom']); ?></p>
                <p><strong>Email :</strong> <?php echo htmlspecialchars($facture['client_email']); ?></p>
                <p><strong>Adresse :</strong> <?php echo htmlspecialchars($facture['client_adresse']); ?></p>
                <?php if (!empty($facture['email_entreprise'])): ?>
                    <p><strong>Email Entreprise :</strong> <?php echo htmlspecialchars($facture['email_entreprise']); ?></p>
                <?php endif; ?>
                <?php if (!empty($facture['siret'])): ?>
                    <p><strong>SIRET :</strong> <?php echo htmlspecialchars($facture['siret']); ?></p>
                <?php endif; ?>
            </div>

        <?php endforeach; ?>

        <h3>Liste des Produits</h3>
        <?php if (!empty($produits)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Prix Unitaire</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $produit): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($produit['description']); ?></td>
                            <td><?php echo number_format($produit['prix_unitaire'], 2); ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun produit trouvé.</p>
        <?php endif; ?>
    </div>
</body>

</html>