<?php
session_start();
require 'config.php'; // Fichier pour se connecter à la base de données

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Préparation de la requête SQL pour récupérer l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Authentification réussie
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirection selon le rôle
        if ($user['role'] == 'admin') {
            header('Location: admin.php');
        } elseif ($user['role'] == 'user') {
            header('Location: user.php');
        } else {
            header('Location: guest.php');
        }
    } else {
        // Identifiants incorrects
        echo "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
</head>
<body>
<h2>Page de Connexion</h2>
<form id="loginForm" method="POST" action="login.php">
    <label for="username">Nom d'utilisateur:</label><br>
    <input type="text" id="username" name="username" required><br><br>
    <label for="password">Mot de passe:</label><br>
    <input type="password" id="password" name="password" required><br><br>
    <button type="submit">Se connecter</button>
</form>

<script>
    const form = document.getElementById('loginForm');
    form.addEventListener('submit', function(event) {
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        if (username === '' || password === '') {
            alert('Veuillez remplir tous les champs.');
            event.preventDefault();
        }
    });
</script>
</body>
</html>
