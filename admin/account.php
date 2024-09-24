<?php
session_start();

// Vérification de la connexion
if (!isset($_SESSION['nom']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

require '../config.php';

// Récupérer les données du compte de l'utilisateur connecté
$id = intval($_SESSION['user_id']);
$sql = "SELECT * FROM comptes WHERE id = $id";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

// Traitement de la mise à jour
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == 'update') {
    $nom = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['nom'] ?? ''));
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $adresse = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['adresse'] ?? ''));
    $email_entreprise = !empty($_POST['email_entreprise']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_POST['email_entreprise'])) : null;
    $siret = !empty($_POST['siret']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_POST['siret'])) : null;
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $row['password'];
    $role = isset($_POST['role']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_POST['role'])) : $row['role'];

    // Validation des champs côté serveur
    if (!preg_match("/^[A-Za-zÀ-ÿ\s'-]+$/", $nom)) {
        die("Nom invalide.");
    }
    if (!$email) {
        die("Email invalide.");
    }
    if ($siret && !preg_match("/^\d{14}$/", $siret)) {
        die("SIRET invalide.");
    }
    if ($role && !in_array($role, ['client', 'employer', 'admin'])) {
        die("Rôle invalide.");
    }

    $updateSql = "UPDATE comptes SET 
                    nom = '$nom', 
                    email = '$email', 
                    adresse = '$adresse', 
                    email_entreprise = " . ($email_entreprise ? "'$email_entreprise'" : "NULL") . ", 
                    siret = " . ($siret ? "'$siret'" : "NULL") . ", 
                    password = '$password', 
                    role = '$role' 
                  WHERE id = $id";

    if (mysqli_query($conn, $updateSql)) {
        // Redirection après mise à jour réussie
        header("Location: index.php");
        exit();
    } else {
        echo "Erreur: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Détails du Compte</title>
    <link rel="stylesheet" href="../assets/css/account-style.css">
</head>

<body>
    <?php require './header.php'; ?>

    <div id="containerAdmin">
        <h2>Détails du Compte</h2>

        <form action="account.php" method="post" id="formId">
            <input type="hidden" name="action" value="update">
            <div id="table-1">
                <div id="col-1">
                    <label>Nom:</label>
                    <input type="text" name="nom" value="<?php echo htmlspecialchars($row['nom']); ?>" required pattern="[A-Za-zÀ-ÿ\s'-]+" title="Nom ne doit contenir que des lettres et des espaces"><br>
                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required><br>
                    <label>Adresse:</label>
                    <input type="text" name="adresse" value="<?php echo htmlspecialchars($row['adresse']); ?>"><br>
                </div>

                <div id="col-2">
                    <label>Email Entreprise:</label>
                    <input type="email" name="email_entreprise" value="<?php echo htmlspecialchars($row['email_entreprise'] ?? ''); ?>"><br>
                    <label>SIRET:</label>
                    <input type="text" name="siret" value="<?php echo htmlspecialchars($row['siret'] ?? ''); ?>" pattern="\d{14}" title="SIRET doit être un numéro de 14 chiffres"><br>
                    <label>Mot de passe:</label>
                    <input type="password" name="password" placeholder="Laissez vide pour ne pas changer" pattern=".{6,}" title="Mot de passe doit contenir au moins 6 caractères"><br>
                </div>
            </div>
            
            <input type="submit" value="Mettre à jour">
        </form>
    </div>
</body>

</html>
