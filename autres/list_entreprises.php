<?php
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'test_cube_trois');

$result = $conn->query("SELECT * FROM entreprises");
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" type="text/css" href="../assets/css/entreprise.css">
    <title>Liste des Entreprises</title>
</head>

<body>
    <header>

        <a href="../index.php">
            <h1>Control Tech</h1>
        </a>
        <a href="">Listes clients</a>
        <a href="../admin/add_client.php">Creer Clients</a>
        <a href="list_entreprises.php">Listes Entreprise</a>
        <a href="../admin/add_entreprise.php">Creer Entreprise</a>
        <a href="list_factures.php">Listes Factures</a>
        <a href="../admin/create_facture.php">Creer Factures</a>
        <a href="../download_pdf.php">Download PDF</a>
        <a href="../export_csv.php">Download CSV</a>

    </header>
    <div class="container">
        <h1>Liste des Entreprises</h1>
        <button id="openFormBtn" class="button-add-client btn">Ajouter une entreprise</button>

        <!-- Modale pour le formulaire d'ajout d'entreprise -->
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h1>Ajouter une Entreprise</h1>
                <form method="POST" action="../admin/add_entreprise.php">
                    <input type="text" name="nom" placeholder="Nom de l'entreprise" required>
                    <textarea name="adresse" placeholder="Adresse de l'entreprise" required></textarea>
                    <button type="submit">Ajouter l'entreprise</button>
                </form>
            </div>
        </div>

        <table class="list-entreprise">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Adresse</th>
                <th>Options</th>
            </tr>
            <?php while ($entreprise = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $entreprise['id']; ?></td>
                    <td><?php echo $entreprise['nom']; ?></td>
                    <td><?php echo $entreprise['adresse']; ?></td>
                    <td>
                        <button onclick="location.href=''" class="modif-btn btn">
                            Mofifier cette entreprise
                        </button>
                        <button onclick="location.href=''" class="remove-btn btn">
                            Effacer cette entreprise
                        </button>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <script src="../assets/javascript/modal.js"></script>
</body>

</html>