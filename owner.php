<?php
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', 'theooreo', 'test_cube_trois');

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Requête pour récupérer les clients et leurs entreprises associées
$result_clients = $conn->query("
    SELECT clients.id, clients.nom, clients.email, entreprises.nom AS entreprise_nom 
    FROM clients 
    LEFT JOIN entreprises 
    ON clients.entreprise_id = entreprises.id
");

if (!$result_clients) {
    die("Erreur lors de la récupération des clients : " . $conn->error);
}

// Requête pour récupérer la liste des entreprises
$result_entreprises = $conn->query("SELECT * FROM entreprises");

if (!$result_entreprises) {
    die("Erreur lors de la récupération des entreprises : " . $conn->error);
}

// Requête pour récupérer toutes les factures avec les informations des clients
$sql_factures = "
    SELECT 
        factures.id, 
        clients.nom AS client_nom, 
        factures.date_creation, 
        factures.date_livraison, 
        factures.total, 
        factures.etat 
    FROM factures 
    LEFT JOIN clients ON factures.client_id = clients.id
    ORDER BY factures.date_creation DESC
";

$result_factures = $conn->query($sql_factures);

if (!$result_factures) {
    die("Erreur lors de la récupération des factures : " . $conn->error);
}

// Collecter tous les IDs des factures pour récupérer les produits en une seule requête
$facture_ids = [];
while ($facture = $result_factures->fetch_assoc()) {
    $facture_ids[] = $facture['id'];
}

// Remettre le curseur au début du résultat des factures
$result_factures->data_seek(0);

// Préparer et exécuter la requête pour récupérer tous les produits associés aux factures
$produits_par_facture = [];

if (count($facture_ids) > 0) {
    // Créer une liste de placeholders pour la requête préparée
    $placeholders = implode(',', array_fill(0, count($facture_ids), '?'));
    $sql_produits = "SELECT * FROM produits WHERE facture_id IN ($placeholders)";

    $stmt_produits = $conn->prepare($sql_produits);

    if ($stmt_produits) {
        // Générer les types pour bind_param (tous les IDs sont des entiers)
        $types = str_repeat('i', count($facture_ids));

        // Utiliser un tableau pour passer les paramètres dynamiquement
        $stmt_produits->bind_param($types, ...$facture_ids);

        $stmt_produits->execute();
        $result_produits = $stmt_produits->get_result();

        // Organiser les produits par facture_id
        while ($produit = $result_produits->fetch_assoc()) {
            $produits_par_facture[$produit['facture_id']][] = $produit;
        }

        $stmt_produits->close();
    } else {
        die("Erreur lors de la préparation de la requête des produits : " . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Comptabilité - Clients, Entreprises, Factures et Produits</title>
    <link rel="stylesheet" type="text/css" href="./assets/css/styles.css">
</head>

<body>
    <header>
        <h1>Control Tech</h1>
        <nav>
            <a href="">Listes clients</a>
            <a href="add_client.php">Créer Clients</a>
            <a href="list_entreprises.php">Listes Entreprises</a>
            <a href="add_entreprise.php">Créer Entreprises</a>
            <a href="list_factures.php">Listes Factures</a>
            <a href="create_facture.php">Créer Factures</a>
            <a href="download_pdf.php">Télécharger PDF</a>
            <a href="export_csv.php">Télécharger CSV</a>
        </nav>
    </header>
    <div class="container">

        <h2>Bonjour (nom)</h2>

        <!-- Section Clients -->
        <h3>Voici votre / vos comptes clients :</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Entreprise</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($client = $result_clients->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['id']); ?></td>
                        <td><?php echo htmlspecialchars($client['nom']); ?></td>
                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                        <td><?php echo htmlspecialchars($client['entreprise_nom']) ? htmlspecialchars($client['entreprise_nom']) : 'Aucune'; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Section Entreprises -->
        <h3>Voici votre / vos entreprises :</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Adresse</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($entreprise = $result_entreprises->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entreprise['id']); ?></td>
                        <td><?php echo htmlspecialchars($entreprise['nom']); ?></td>
                        <td><?php echo htmlspecialchars($entreprise['adresse']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Section Factures -->
        <h3>Voici vos factures :</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom client</th>
                    <th>Date de création</th>
                    <th>Date de livraison</th>
                    <th>Total €</th>
                    <th>État</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($facture = $result_factures->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($facture['id']); ?></td>
                        <td><?php echo htmlspecialchars($facture['client_nom']); ?></td>
                        <td><?php echo htmlspecialchars($facture['date_creation']); ?></td>
                        <td><?php echo htmlspecialchars($facture['date_livraison']); ?></td>
                        <td><?php echo number_format($facture['total'], 2, ',', ' '); ?> €</td>
                        <td><?php echo htmlspecialchars($facture['etat']); ?></td>
                    </tr>

                    <!-- Afficher les produits associés à la facture -->
                    <?php if (isset($produits_par_facture[$facture['id']])) { ?>
                        <tr>
                            <td colspan="6">
                                <h4>Produits pour la facture <?php echo htmlspecialchars($facture['id']); ?> :</h4>
                                <table class="produits-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Description</th>
                                            <th>Prix unitaire €</th>
                                            <th>Quantité</th>
                                            <th>Sous-total €</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($produits_par_facture[$facture['id']] as $produit) {
                                            $sous_total = $produit['prix_unitaire'] * $produit['quantite'];
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($produit['id']); ?></td>
                                                <td><?php echo htmlspecialchars($produit['description']); ?></td>
                                                <td><?php echo number_format($produit['prix_unitaire'], 2, ',', ' '); ?> €</td>
                                                <td><?php echo htmlspecialchars($produit['quantite']); ?></td>
                                                <td><?php echo number_format($sous_total, 2, ',', ' '); ?> €</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6" class="no-produits">Aucun produit associé à cette facture.</td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>

    </div>
</body>

</html>

<?php
// Fermer la connexion
$conn->close();
?>