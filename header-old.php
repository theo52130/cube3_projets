<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Comptabilité - Clients, Entreprises, Factures et Produits</title>
    <link rel="stylesheet" type="text/css" href="./assets/css/styles.css">
</head>

<body>
    <header>
        <h1>TBY Innovations</h1>
        <nav>

            <?php
            if (!isset($_SESSION['nom'])) { ?>
                <a href="login.php">Connexion</a>
                <a href="register.php">Inscription</a>
            <?php
            } else { ?>
                <a href="logout.php">Déconnexion</a>
            <?php
            }
            ?>

            <a href="./admin/">Dashboard</a>

        </nav>
    </header>

</body>

</html>