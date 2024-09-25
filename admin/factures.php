<?php
// Inclure le fichier de configuration pour la connexion à la base de données
include '../config.php';

// Requête SQL pour récupérer les produits
$sql = "SELECT * FROM produits";
$result = $conn->query($sql);

// Vérifier si la requête a réussi
if ($result === false) {
    die("Erreur de requête : " . $conn->error);
}

$produits = [];
if ($result->num_rows > 0) {
    // Récupérer les résultats dans un tableau associatif
    while ($row = $result->fetch_assoc()) {
        $produits[] = $row;
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
    <title>Liste des Produits</title>
    <style>
        /* Styles CSS pour le tableau */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <div id="pageFactures">
        <h3>Produit</h3>

        <?php if (!empty($produits)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Prix Unitaire</th>
                        <th>Quantité</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $produit): ?>
                        <tr>
                            <td><?= htmlspecialchars($produit['description']); ?></td>
                            <td><?= number_format($produit['prix_unitaire'], 2); ?> €</td>
                            <td><?= htmlspecialchars($produit['quantite']); ?></td>
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