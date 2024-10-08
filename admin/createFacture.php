<?php
session_start();

// Vérification de la connexion et du rôle
if (!isset($_SESSION['nom']) || $_SESSION['role'] != 'admin') {
    header("Location: ../public/login.php");
    exit();
}

// Connexion à la base de données
require '../includes/config.php';

// Récupérer les clients
$clientsQuery = "SELECT id, nom FROM comptes WHERE role = 'client'";
$clientsResult = mysqli_query($conn, $clientsQuery);
$clients = mysqli_fetch_all($clientsResult, MYSQLI_ASSOC);

// Récupérer les produits
$produitsQuery = "SELECT id, description, prix_unitaire FROM produits";
$produitsResult = mysqli_query($conn, $produitsQuery);
$produits = mysqli_fetch_all($produitsResult, MYSQLI_ASSOC);

// Traitement du formulaire
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = intval($_POST['client']);
    $total = floatval($_POST['montant']);
    $produits_data = json_decode($_POST['produits'], true);

    // Préparer la requête d'insertion de la facture
    $stmt = $conn->prepare("INSERT INTO factures (client_id, total) VALUES (?, ?)");
    $stmt->bind_param("id", $client_id, $total);

    if ($stmt->execute()) {
        $facture_id = $stmt->insert_id; // Récupérer l'ID de la facture créée

        // Insérer les produits dans la table factures_produits
        $insertProduitStmt = $conn->prepare("INSERT INTO factures_produits (facture_id, produit_id, quantite) VALUES (?, ?, ?)");
        $success = true; // Variable pour suivre le succès de l'insertion des produits

        foreach ($produits_data as $produit) {
            $insertProduitStmt->bind_param("iii", $facture_id, $produit['id'], $produit['quantite']);
            if (!$insertProduitStmt->execute()) {
                $success = false; // Un produit a échoué à s'insérer
                $message = "Erreur lors de l'ajout des produits à la facture : " . $insertProduitStmt->error;
                break; // Sortir de la boucle si une erreur est rencontrée
            }
        }

        // Vérifier si tous les produits ont été insérés avec succès
        if ($success) {
            header("Location: index.php");
            exit();
        }
    } else {
        $message = "Erreur lors de la création de la facture : " . $stmt->error;
    }

    $stmt->close();
    $insertProduitStmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une Facture</title>
    <link rel="stylesheet" href="../assets/css/create-facture.css">
</head>

<body>
    <?php require './header.php'; ?>

    <h2>Créer une Facture</h2>
    <?php if ($message): ?>
        <div class="error-message"><?= $message ?></div>
    <?php endif; ?>

    <form id="invoice-form" method="post">
        <label>*Client :</label>
        <select name="client" required>
            <option value="">*Sélectionner un client</option>
            <?php foreach ($clients as $client): ?>
                <option value="<?= $client['id'] ?>"><?= $client['nom'] ?></option>
            <?php endforeach; ?>
        </select><br>

        <div id="produits-container">
            <div class="produit">
                <label>*Produit :</label>
                <select name="produit[]" required>
                    <option value="">*Sélectionner un produit</option>
                    <?php foreach ($produits as $produit): ?>
                        <option value="<?= $produit['id'] ?>" data-price="<?= $produit['prix_unitaire'] ?>"><?= $produit['description'] ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="quantite[]" placeholder="Qté" value="1" min="1" required>
                <button type="button" class="remove-produit">Retirer</button>
            </div>
        </div>

        <button type="button" id="add-produit">Ajouter un produit</button>

        <h2>Total: <span id="total">0.00</span> €</h2>
        <input type="hidden" name="montant" id="montant" value="0">
        <input type="hidden" name="produits" id="produits-data" value="">
        <input type="submit" value="Créer la Facture" class="btn-submit">
    </form>

    <script>
        // Script pour gérer l'ajout et la suppression des produits
        document.getElementById('add-produit').addEventListener('click', function() {
            const container = document.getElementById('produits-container');
            const newProduit = document.createElement('div');
            newProduit.classList.add('produit');
            newProduit.innerHTML = `
                <label>*Produit :</label>
                <select name="produit[]" required>
                    <option value="">*Sélectionner un produit</option>
                    <?php foreach ($produits as $produit): ?>
                        <option value="<?= $produit['id'] ?>" data-price="<?= $produit['prix_unitaire'] ?>"><?= $produit['description'] ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="quantite[]" placeholder="Qté" value="1" min="1" required>
                <button type="button" class="remove-produit">Retirer</button>
            `;
            container.appendChild(newProduit);
        });

        function updateTotal() {
            const produitSelects = document.querySelectorAll('select[name="produit[]"]');
            const quantites = document.querySelectorAll('input[name="quantite[]"]');
            let total = 0;

            produitSelects.forEach((select, index) => {
                const quantite = quantites[index].value;
                if (select.value) {
                    const price = parseFloat(select.options[select.selectedIndex].dataset.price);
                    total += price * quantite;
                }
            });

            document.getElementById('montant').value = total.toFixed(2);
            document.getElementById('total').textContent = total.toFixed(2);
        }

        document.getElementById('produits-container').addEventListener('change', updateTotal);

        document.getElementById('produits-container').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-produit')) {
                e.target.parentElement.remove();
                updateTotal();
            }
        });

        document.getElementById('invoice-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const produits = [];
            const produitSelects = document.querySelectorAll('select[name="produit[]"]');
            const quantites = document.querySelectorAll('input[name="quantite[]"]');

            produitSelects.forEach((select, index) => {
                const id = select.value;
                const quantite = quantites[index].value;
                if (id) {
                    produits.push({
                        id,
                        quantite
                    });
                }
            });

            formData.append('produits', JSON.stringify(produits));

            // Utiliser fetch pour soumettre le formulaire via AJAX
            fetch(this.action, {
                    method: 'POST',
                    body: formData
                }).then(response => response.text())
                .then(data => {
                    // Gérer la réponse du serveur
                    console.log(data);
                    // Rediriger ou afficher un message de succès
                    window.location.href = 'index.php'; // Remplacer par la page de succès
                }).catch(error => {
                    console.error('Erreur:', error);
                    // Afficher un message d'erreur
                    alert('Erreur lors de la soumission du formulaire.');
                });
        });
    </script>
</body>

</html>