<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli('localhost', 'root', '', 'test_cube_trois');

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $comptes_id = $_POST['comptes_id'];

    // Calculer le total en fonction des produits
    $total = 0;
    foreach ($_POST['produits'] as $produit_id) {
        $result = $conn->query("SELECT prix_unitaire FROM produits WHERE id = $produit_id");
        $produit = $result->fetch_assoc();
        $total += $produit['prix_unitaire'];
    }

    // Insérer la facture dans la table 'factures'
    $sql_facture = "INSERT INTO factures (comptes_id, date_creation, total) VALUES ($comptes_id, NOW(), $total)";
    if ($conn->query($sql_facture)) {
        $facture_id = $conn->insert_id; // Récupérer l'ID de la facture créée

        // Lier les produits à la facture dans la table 'factures_produits'
        foreach ($_POST['produits'] as $produit_id) {
            $sql_facture_produit = "INSERT INTO factures_produits (facture_id, produit_id) 
                                    VALUES ($facture_id, $produit_id)";
            $conn->query($sql_facture_produit);
        }

        // Redirection après la création de la facture
        header("Location: facture_success.php?facture_id=$facture_id");
        exit();
    } else {
        echo "Erreur: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
    <title>Créer une Facture</title>
    <script>
        function ajouterProduit() {
            const produitsContainer = document.getElementById('produits-container');
            const newProduit = `
                <div class="produit">
                    <select name="produits[]" required>
                        <?php
                        $produits = $conn->query("SELECT * FROM produits");
                        while ($produit = $produits->fetch_assoc()) {
                            echo "<option value='{$produit['id']}'>{$produit['description']} - {$produit['prix_unitaire']}€</option>";
                        }
                        ?>
                    </select>
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

            <label for="comptes_id">Sélectionner un client :</label>
            <select name="comptes_id" required>
                <?php
                $comptes = $conn->query("SELECT * FROM comptes");
                while ($compte = $comptes->fetch_assoc()) {
                    echo "<option value='{$compte['id']}'>{$compte['nom']}</option>";
                }
                ?>
            </select>

            <h3>Produits</h3>
            <div id="produits-container">
                <div class="produit">
                    <select name="produits[]" required>
                        <?php
                        $produits = $conn->query("SELECT * FROM produits");
                        while ($produit = $produits->fetch_assoc()) {
                            echo "<option value='{$produit['id']}'>{$produit['description']} - {$produit['prix_unitaire']}€</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <button type="button" onclick="ajouterProduit()">Ajouter un produit</button>

            <button type="submit">Créer Facture</button>
        </form>
    </div>
</body>

</html>