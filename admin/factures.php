<?php
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

// Organiser les factures et produits associés dans un tableau
$factures = [];
while ($row = $result_factures->fetch_assoc()) {
    $facture_id = $row['facture_id'];

    // Si la facture n'a pas encore été ajoutée, on l'ajoute
    if (!isset($factures[$facture_id])) {
        $factures[$facture_id] = [
            'date_creation' => $row['date_creation'],
            'total' => $row['total'],
            'etat' => $row['etat'],
            'client_email' => $row['client_email'],
            'produits' => []
        ];
    }

    // Ajouter les produits à la facture correspondante
    if ($row['produit_description']) {
        $factures[$facture_id]['produits'][] = [
            'description' => $row['produit_description'],
            'prix_unitaire' => $row['prix_unitaire'],
            'quantite' => $row['quantite']
        ];
    }
}

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

        <a href="./createFacture.php" class="btn create-factures-btn">Créer une nouvelle facture</a>
        <form action="../csv-pdf/csv.php" method="POST" style="display: inline;">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($facture_id); ?>">
            <button type="submit" class="btn csv-btn">CSV</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Ref</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>État</th>
                    <th>Options</th>
                    <!-- <th>Actions</th> -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($factures as $facture_id => $facture): ?>
                    <tr id="row-facture-<?php echo htmlspecialchars($facture_id); ?>">
                        <td><?php echo htmlspecialchars($facture_id); ?></td>
                        <td><?php echo htmlspecialchars($facture['client_email']); ?></td>
                        <td><?php echo htmlspecialchars(date("d/m/Y", strtotime($facture['date_creation']))); ?></td>
                        <td><?php echo htmlspecialchars(number_format($facture['total'], 2)); ?> €</td>
                        <td><?php echo htmlspecialchars($facture['etat']); ?></td>
                        <td>
                            <a href="javascript:void(0)" onclick="toggleDetails(<?php echo $facture_id; ?>)" class="btn details-btn">Détails</a>
                            <form action="../csv-pdf/pdf.php" method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($facture_id); ?>">
                                <button type="submit" class="btn pdf-btn">PDF</button>
                            </form>
                        </td>
                        <!-- <td>
                            <button onclick='deleteRow(<?php echo htmlspecialchars($facture_id); ?>, "invoice")' class='btn delete-btn'>Supprimer</button>
                        </td> -->
                    </tr>
                    <tr id="detail-factures-<?php echo htmlspecialchars($facture_id); ?>" class="detail-factures" style="display: none;">
                        <td colspan="7">
                            <h3>Produits Associés :</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Noms</th>
                                        <th>Prix Unitaire</th>
                                        <th>Quantité</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($facture['produits'] as $produit): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($produit['description']); ?></td>
                                            <td><?php echo htmlspecialchars(number_format($produit['prix_unitaire'], 2)); ?> €</td>
                                            <td><?php echo htmlspecialchars($produit['quantite']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function deleteRow(id, type) {
            const action = type === 'account' ? 'delete_account' : 'delete_invoice';

            if (confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                fetch('delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            'action': action,
                            'id': id
                        })
                    })
                    .then(response => response.text())
                    .then(result => {
                        if (result.trim() === 'success') {
                            if (type === 'account') {
                                document.getElementById('row-' + id).remove(); // Supprime le compte
                            } else {
                                document.getElementById('row-facture-' + id).remove(); // Supprime la facture
                                document.getElementById('detail-factures-' + id).remove(); // Supprime les détails
                            }
                        } else {
                            alert('Erreur lors de la suppression');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Erreur lors de la suppression');
                    });
            }
        }

        function toggleDetails(factureId) {
            var detailSection = document.getElementById("detail-factures-" + factureId);
            if (detailSection.style.display === "none") {
                detailSection.style.display = "table-row"; // Utiliser "table-row" pour afficher correctement
            } else {
                detailSection.style.display = "none";
            }
        }
    </script>
</body>

</html>