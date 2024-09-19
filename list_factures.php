<?php
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', 'theooreo', 'test_cube_trois');

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Requête pour récupérer les factures et les clients associés
$result_factures = $conn->query("
    SELECT factures.id, factures.total, factures.date_creation, factures.etat, clients.nom AS client_nom 
    FROM factures
    JOIN clients ON factures.client_id = clients.id
");
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="assets/css/styles.css">
    <title>Liste des Factures</title>
</head>
<body>
<header>
    <h1>Liste des Factures</h1>
</header>
<div class="container">
    <a href="add_invoice.php">Ajouter une facture</a>
    <table>
        <tr>
            <th>ID</th>
            <th>Client</th>
            <th>Date</th>
            <th>Montant</th>
            <th>Description</th>
        </tr>
        <?php while($factures = $result_factures->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $factures['id']; ?></td>
                <td><?php echo $factures['client_nom']; ?></td>
                <td><?php echo $factures['date_creation']; ?></td>
                <td><?php echo $factures['total']; ?> €</td>
                <td><?php echo $factures['etat']; ?></td>
            </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
