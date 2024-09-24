<?php
    require('./config.php');
    
    // Démarrer la session en haut de la page
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];

        // Rechercher le compte correspondant à cet email
        $sql = "SELECT * FROM comptes WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            // Vérification du mot de passe
            if (password_verify($password, $row['password'])) {
                // Connexion réussie, démarrer la session utilisateur
                $_SESSION['user_id'] = $row['id'];  // ID du compte
                $_SESSION['nom'] = $row['nom'];     // Nom de l'utilisateur
                $_SESSION['email'] = $row['email']; // Email de l'utilisateur
                $_SESSION['adresse'] = $row['adresse']; // Adresse de l'utilisateur
                $_SESSION['email_entreprise'] = $row['email_entreprise']; // email entreprise
                $_SESSION['siret'] = $row['siret']; // siret de l'utilisateur
                $_SESSION['role'] = $row['role'];   // Rôle de l'utilisateur

                // Ajouter les champs supplémentaires si ils existent
                if (!empty($row['email_entreprise'])) {
                    $_SESSION['email_entreprise'] = $row['email_entreprise'];
                }
                if (!empty($row['siret'])) {
                    $_SESSION['siret'] = $row['siret'];
                }

                // Redirection après connexion réussie
                header("Location: redirection.php");
                exit();
            } else {
                echo "Mot de passe incorrect.";
            }
        } else {
            echo "Aucun compte trouvé avec cet email.";
        }
    }
    ?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="./assets/css/login-style.css">
</head>

<body>

    <h2>Connexion</h2>

    <form action="login.php" method="post">

        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Mot de passe:</label>
        <input type="password" name="password" required><br>

        <input type="submit" value="Se connecter">

    </form>

    <!-- A RETIRER AVANT PROD, QUE PENDANT LES TESTS !!!! -->
    <script>console.log('Admin : test@admin.com\nMdp : test');</script>
    <script>console.log('Employer : test@employer.com\nMdp : test');</script>
    <script>console.log('Client : test@client.com\nMdp : test');</script>

</body>

</html>
