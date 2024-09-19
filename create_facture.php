<?php
$conn = new mysqli('localhost', 'root', 'theooreo', 'test_cube_trois');

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = $_POST['client_id'];

    // Calculer le total en fonction des produits
    $total = 0;
    foreach ($_POST['produits'] as $produit) {
        $prix_unitaire = (float) $produit['prix_unitaire'];
        $quantite = (int) $produit['quantite'];
        $total += $prix_unitaire * $quantite;
    }

    // Insérer la facture dans la table 'factures'
    $sql_facture = "INSERT INTO factures (client_id, date_creation, total) VALUES ($client_id, NOW(), $total)";
    if ($conn->query($sql_facture)) {
        $facture_id = $conn->insert_id; // Récupérer l'ID de la facture créée

        // Insérer les produits dans la table 'produits'
        foreach ($_POST['produits'] as $produit) {
            $description = $conn->real_escape_string($produit['description']);
            $prix_unitaire = (float) $produit['prix_unitaire'];
            $quantite = (int) $produit['quantite'];
            $sql_produit = "INSERT INTO produits (facture_id, description, prix_unitaire, quantite) 
                            VALUES ($facture_id, '$description', $prix_unitaire, $quantite)";
            $conn->query($sql_produit);
        }

        echo "Facture et produits créés avec succès";
    } else {
        echo "Erreur: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="assets/css/styles.css">
    <title>Créer une Facture</title>
    <script>
        // Ajouter une nouvelle ligne de produit
        function ajouterProduit() {
            const produitsContainer = document.getElementById('produits-container');
            const newProduit = `
                <div class="produit">
                    <input type="text" name="produits[][description]" placeholder="Description du produit" required>
                    <input type="number" name="produits[][prix_unitaire]" placeholder="Prix unitaire (€)" step="0.01" required>
                    <input type="number" name="produits[][quantite]" placeholder="Quantité" min="1" required>
                </div>
            `;
            produitsContainer.insertAdjacentHTML('beforeend', newProduit);
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Créer une Facture</h1>
    <form method="POST">
        <!-- Sélection du client -->
        <label for="client_id">Sélectionner un client :</label>
        <select name="client_id" required>
            <?php
            $clients = $conn->query("SELECT * FROM clients");
            while ($client = $clients->fetch_assoc()) {
                echo "<option value='{$client['id']}'>{$client['nom']}</option>";
            }
            ?>
        </select>

        <!-- Ajout des produits -->
        <h3>Produits</h3>
        <div id="produits-container">
            <div class="produit">
                <input type="text" name="produits[][description]" placeholder="Description du produit" required>
                <input type="number" name="produits[][prix_unitaire]" placeholder="Prix unitaire (€)" step="0.01" required>
                <input type="number" name="produits[][quantite]" placeholder="Quantité" min="1" required>
            </div>
        </div>
        <button type="button" onclick="ajouterProduit()">Ajouter un produit</button>

        <!-- Bouton pour créer la facture -->
        <button type="submit">Créer Facture</button>
    </form>
</div>
</body>
</html>
