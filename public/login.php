<?php
require('../includes/config.php');

// Démarrer la session en haut de la page
session_start();

// Vérifiez si l'utilisateur est déjà connecté
if (isset($_SESSION['nom']) || isset($_SESSION['user_id'])) {
    header("Location: redirection.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Filtrer et valider les entrées
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    // Vérification que les champs ne sont pas vides
    if (empty($email) || empty($password)) {
        echo "Veuillez remplir tous les champs.";
    } else {
        // Préparer la requête pour récupérer les informations de l'utilisateur
        $stmt = $conn->prepare("SELECT * FROM comptes WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

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

                // Récupérer et stocker le client_id dans la session
                if ($row['role'] === 'client') {
                    $_SESSION['client_id'] = $row['id']; // Client ID
                }

                // Redirection après connexion réussie
                header("Location: redirection.php");
                exit();
            }
        }

        // Message d'erreur générique
        echo "Mot de passe ou Email incorrect.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/logo/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logo/favicon-16x16.png">
    <link rel="manifest" href="../assets/logo/site.webmanifest">
    <link rel="mask-icon" href="../assets/logo/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" href="../assets/css/login-style.css">
</head>

<body>

    <h2>Connexion</h2>

    <form action="login.php" method="post">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" placeholder="Entrez votre email de connexion" required><br>

        <label for="password">Mot de passe:</label>
        <div class="password-container">
            <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe">
            <button type="button" id="togglePassword" aria-label="Afficher/Masquer le mot de passe">
                <img src="../assets/img/visibility_close.svg" alt="Afficher le mot de passe" id="eyeIcon">
            </button>
        </div>

        <input type="submit" value="Se connecter">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const passwordInput = document.getElementById('password');
            const togglePasswordButton = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eyeIcon');

            togglePasswordButton.addEventListener('click', () => {
                // Toggle the type attribute
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.src = './assets/img/visibility_open.svg'; // Change the icon to show the password is visible
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.src = './assets/img/visibility_close.svg'; // Change the icon to hide the password
                }
            });
        });
    </script>

</body>

</html>