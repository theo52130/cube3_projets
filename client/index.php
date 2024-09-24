<?php
session_start();

// Vérification de la connexion et du rôle
if (!isset($_SESSION['nom']) || $_SESSION['role'] != 'client') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Bienvenue</title>
    <link rel="stylesheet" href="../assets/css/client.css">
</head>

<body>
    <?php require './header.php'; ?>

    <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['nom']); ?>!</h2>
    <p>Votre email est: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
    <p>Votre rôle est: <?php echo htmlspecialchars($_SESSION['role']); ?></p>

    <?php if (isset($_SESSION['email_entreprise'])): ?>
        <p>Votre email d'entreprise est: <?php echo htmlspecialchars($_SESSION['email_entreprise']); ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['siret'])): ?>
        <p>Votre SIRET est: <?php echo htmlspecialchars($_SESSION['siret']); ?></p>
    <?php endif; ?>

    <p>Vous etes clients, merci pour votre confiance.</p>

</body>

</html>
