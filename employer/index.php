<?php
session_start();

// Vérification de la connexion et du rôle
if (
    !isset($_SESSION['nom']) ||
    !(
        $_SESSION['role'] == 'admin' ||
        $_SESSION['role'] == 'employer'
    )
) {
    header("Location: ../public/login.php");
    exit();
}

// Connexion à la base de données
require '../includes/config.php';

// Requête pour obtenir les données des comptes
$sql = "SELECT * FROM comptes WHERE role = 'client'";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Employer</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/logo/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logo/favicon-16x16.png">
    <link rel="manifest" href="../assets/logo/site.webmanifest">
    <link rel="mask-icon" href="../assets/logo/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <?php require './header.php'; ?>

    <div id="containerAdmin">
        <h2 id="customWelcome">Bienvenue, <?php echo htmlspecialchars($_SESSION['nom']); ?> !</h2>
        <div class="titre-compte-list">
            <h3>Listes des comptes : </h3>
        </div>
        <!-- Tableau des comptes -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
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
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
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