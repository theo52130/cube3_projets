<?php
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'test_cube_trois');

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Requête pour récupérer les comptes clients
$result_comptes = $conn->query("
    SELECT id, nom, email, adresse, email_entreprise, siret 
    FROM comptes
");

if (!$result_comptes) {
    die("Erreur lors de la récupération des comptes : " . $conn->error);
}

// Requête pour récupérer toutes les factures avec les informations des comptes
$sql_factures = "
    SELECT 
        factures.id, 
        comptes.nom AS compte_nom, 
        factures.date_creation, 
        factures.date_livraison, 
        factures.total, 
        factures.etat 
    FROM factures 
    LEFT JOIN comptes ON factures.comptes_id = comptes.id
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
    $sql_produits = "
        SELECT produits.*, factures_produits.facture_id 
        FROM produits 
        INNER JOIN factures_produits 
        ON produits.id = factures_produits.produit_id 
        WHERE factures_produits.facture_id IN ($placeholders)
    ";

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
    <title>Comptabilité - Comptes, Factures et Produits</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/owner.css">
</head>

<body>
    <header>
        <h1>Control Tech</h1>
        <nav>
            <a href="">Listes clients</a>
            <a href="add_client.php">Créer Clients</a>
            <a href="../autres/list_entreprises.php">Listes Entreprises</a>
            <a href="add_entreprise.php">Créer Entreprises</a>
            <a href="../autres/list_factures.php">Listes Factures</a>
            <a href="create_facture.php">Créer Factures</a>
            <a href="../download_pdf.php">Télécharger PDF</a>
            <a href="../export_csv.php">Télécharger CSV</a>
        </nav>
    </header>
    <div class="container">

        <h2>Bonjour (nom)</h2>

        <!-- Section Comptes -->
        <div id="header-users">
            <h3>Voici vos comptes :</h3>
            <a href="./update/add_compte.php" class="btn add-btn">Add</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Adresse</th>
                    <th>Email entreprise</th>
                    <th>SIRET</th>
                    <th>Options</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($compte = $result_comptes->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($compte['id']); ?></td>
                        <td><?php echo htmlspecialchars($compte['nom']); ?></td>
                        <td><?php echo htmlspecialchars($compte['email']); ?></td>
                        <td><?php echo htmlspecialchars($compte['adresse']); ?></td>
                        <td><?php echo htmlspecialchars($compte['email_entreprise'] ?? 'Aucune', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($compte['siret'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <form method="get" action="./update/update.php">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($compte['id']); ?>">
                                <button type="submit" class="btn update-btn">Update</button>
                            </form>
                            <form method="get" action="./update/delete.php">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($compte['id']); ?>">
                                <button type="submit" class="btn delete-btn">Delete</button>
                            </form>
                        </td>
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
                    <th>Nom du compte</th>
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
                        <td><?php echo htmlspecialchars($facture['compte_nom']); ?></td>
                        <td><?php echo htmlspecialchars($facture['date_creation']); ?></td>
                        <td><?php
                            if ($facture['date_livraison'] !== null) {
                                echo htmlspecialchars($facture['date_livraison'], ENT_QUOTES, 'UTF-8');
                            } else {
                                echo '';
                            } ?>
                        </td>
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
?>-