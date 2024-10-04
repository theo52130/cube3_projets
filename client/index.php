<?php
session_start();

// Vérification de la connexion et du rôle
if (
    !isset($_SESSION['nom']) ||
    !(
        $_SESSION['role'] == 'admin' || $_SESSION['role'] == 'client'
    )
) {
    header("Location: ../login.php");
    exit();
}

// Connexion à la base de données
require '../config.php';

// Requête pour obtenir uniquement les comptes clients
$sql = "SELECT * FROM comptes WHERE id = " . $_SESSION['user_id'];
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <?php require './header.php'; ?>

    <div id="containerAdmin">
        <h2 id="customWelcome">Bienvenue, <?php echo htmlspecialchars($_SESSION['nom']); ?> !</h2>
        <div class="titre-compte-list">
            <h3>Listes de vos comptes : </h3>
        </div>
        <!-- Tableau des comptes clients -->
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Adresse</th>
                    <th>Email Entreprise</th>
                    <th>SIRET</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr id='row-" . htmlspecialchars($row['id']) . "'>";
                        echo "<td>" . htmlspecialchars($row['nom']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['adresse']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email_entreprise'] ?? 'Non spécifié') . "</td>";
                        echo "<td>" . htmlspecialchars($row['siret'] ?? 'Non spécifié') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>Aucun compte trouvé</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <?php require "factures.php"; ?>

    </div>

    <script>
        function deleteRow(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                fetch('delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            'action': 'delete',
                            'id': id
                        })
                    })
                    .then(response => response.text())
                    .then(result => {
                        if (result.trim() === 'success') {
                            document.getElementById('row-' + id).remove();
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
    </script>
    <?php require './footer.php'; ?>
</body>

</html>