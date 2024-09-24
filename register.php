<?php
require('./config.php');

// Traitement du formulaire d'inscription
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sécuriser les entrées utilisateur
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $adresse = mysqli_real_escape_string($conn, $_POST['adresse']);
    $email_entreprise = !empty($_POST['email_entreprise']) ? mysqli_real_escape_string($conn, $_POST['email_entreprise']) : null;
    $siret = !empty($_POST['siret']) ? mysqli_real_escape_string($conn, $_POST['siret']) : null;
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Préparer la requête SQL pour éviter l'injection SQL
    $stmt = $conn->prepare("INSERT INTO comptes (nom, email, adresse, email_entreprise, siret, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nom, $email, $adresse, $email_entreprise, $siret, $password, $role);

    if ($stmt->execute()) {
        // Redirection après inscription réussie
        echo '<script>
            if (window.history.length > 2) {
                window.history.go(-2);
            } else {
                window.location.href = "redirection.php";
            }
        </script>';
        exit();
    } else {
        // Afficher un message d'erreur sans divulguer des détails techniques
        echo "Erreur lors de l'inscription. Veuillez réessayer.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Compte</title>
    <link rel="stylesheet" href="./assets/css/register-style.css">
</head>

<body>

    <h2>Inscription Compte</h2>
    <form action="register.php" method="post">
        <label>Nom:</label>
        <input type="text" name="nom" required><br>
        
        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Adresse:</label>
        <input type="text" name="adresse" required><br>

        <label>Email Entreprise (optionnel):</label>
        <input type="email" name="email_entreprise"><br>

        <label>SIRET (optionnel):</label>
        <input type="text" name="siret"><br>

        <label>Mot de passe:</label>
        <input type="password" name="password" required><br>

        <label>Rôle:</label>
        <select name="role" required>
            <option value="client">Client</option>
            <option value="employer">Employé</option>
            <option value="admin">Admin</option>
        </select><br>

        <input type="submit" value="S'inscrire">
    </form>

</body>

</html>
