<?php
require('./config.php');

// Démarrer la session en haut de la page
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Utilisation des requêtes préparées pour éviter les injections SQL
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Préparer la requête
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

            // Redirection après connexion réussie
            header("Location: redirection.php");
            exit();
        } else {
            echo "Mot de passe ou Email incorrect.";
        }
    } else {
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
    <link rel="stylesheet" href="./assets/css/login-style.css">
</head>

<body>

    <h2>Connexion</h2>

    <form action="login.php" method="post">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>

        <label for="password">Mot de passe:</label>
        <div class="password-container">
            <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe">
            <button type="button" id="togglePassword" aria-label="Afficher/Masquer le mot de passe">
                <img src="./assets/img/visibility_close.svg" alt="Afficher le mot de passe" id="eyeIcon">
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