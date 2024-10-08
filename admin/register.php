<?php
session_start();

// Vérification de la connexion et du rôle
if (!isset($_SESSION['nom']) || $_SESSION['role'] != 'admin') {
    header("Location: ../public/login.php");
    exit();
}

// Connexion à la base de données
require '../includes/config.php';

// Traitement de la demande de suppression
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['id']);
    $deleteSql = "DELETE FROM comptes WHERE id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    $stmt->close();
}

// Traitement du formulaire d'inscription
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['action'])) {
    // Sécuriser les entrées utilisateur
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $adresse = mysqli_real_escape_string($conn, $_POST['adresse']);
    $email_entreprise = !empty($_POST['email_entreprise']) ? mysqli_real_escape_string($conn, $_POST['email_entreprise']) : null;
    $siret = !empty($_POST['siret']) ? mysqli_real_escape_string($conn, str_replace(' ', '', $_POST['siret'])) : null;
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Vérifie si le SIRET est valide (14 chiffres)
    if ($siret && !preg_match('/^\d{14}$/', $siret)) {
        echo "Le SIRET doit contenir 14 chiffres.";
        exit();
    }

    // Préparer la requête SQL pour éviter l'injection SQL
    $stmt = $conn->prepare("INSERT INTO comptes (nom, email, adresse, email_entreprise, siret, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nom, $email, $adresse, $email_entreprise, $siret, $password, $role);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Erreur: " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Compte</title>
    <link rel="stylesheet" href="../assets/css/register-style.css">
</head>

<body>
    <?php require './header.php'; ?>

    <h2>Création de Compte</h2>
    <form action="register.php" method="post">
        <label>*Prénom - Nom :</label>
        <input type="text" name="nom" required><br>

        <label>*Email :</label>
        <input type="email" name="email" required><br>

        <label>*Adresse (rue, ville) :</label>
        <input type="text" name="adresse" required><br>

        <label>Email Entreprise (obligatoire si entreprise) :</label>
        <input type="email" name="email_entreprise"><br>

        <label>SIRET (obligatoire si entreprise) :</label>
        <input type="text" name="siret"><br>

        <label>*Mot de passe :</label>
        <input type="password" name="password" required>

        <label>*Rôle :</label>
        <select name="role" required>
            <option value="client">Client</option>
            <option value="employe">Employé</option>
            <option value="admin">Admin</option>
        </select><br>

        <input type="submit" value="Créer le compte">
    </form>
</body>

</html>