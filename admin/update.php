<?php
session_start();

// Vérification de la connexion et du rôle
if (!isset($_SESSION['nom']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

require '../config.php';

// Récupérer les données du compte à mettre à jour
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM comptes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
}

// Traitement de la mise à jour
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == 'update') {
    $id = intval($_POST['id']);
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $adresse = $_POST['adresse'];
    $email_entreprise = !empty($_POST['email_entreprise']) ? $_POST['email_entreprise'] : null;
    $siret = !empty($_POST['siret']) ? $_POST['siret'] : null;
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $role = $_POST['role'];

    // Préparer la requête de mise à jour
    $updateSql = "UPDATE comptes SET 
                    nom = ?, 
                    email = ?, 
                    adresse = ?, 
                    email_entreprise = ?, 
                    siret = ?, 
                    role = ? " . 
                    ($password ? ", password = ?" : "") . 
                  " WHERE id = ?";

    $stmt = $conn->prepare($updateSql);

    // Dynamically bind parameters
    if ($password) {
        $stmt->bind_param("ssssssi", $nom, $email, $adresse, $email_entreprise, $siret, $role, $password, $id);
    } else {
        $stmt->bind_param("ssssssi", $nom, $email, $adresse, $email_entreprise, $siret, $role, $id);
    }

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Erreur: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Modifier le Compte</title>
    <link rel="stylesheet" href="../assets/css/update.css">
</head>

<body>
    <?php require './header.php'; ?>

    <div id="containerAdmin">
        <h2>Modifier le Compte</h2>

        <form action="update.php" method="post">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
            <div id="table-1">
                <div id="col-1">
                    <label>Nom:</label>
                    <input type="text" name="nom" value="<?php echo htmlspecialchars($row['nom']); ?>" required><br>
                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required><br>
                    <label>Adresse:</label>
                    <input type="text" name="adresse" value="<?php echo htmlspecialchars($row['adresse']); ?>"><br>
                </div>

                <div id="col-2">
                    <label>Email Entreprise:</label>
                    <input type="email" name="email_entreprise" value="<?php echo htmlspecialchars($row['email_entreprise'] ?? ''); ?>"><br>
                    <label>SIRET:</label>
                    <input type="text" name="siret" value="<?php echo htmlspecialchars($row['siret'] ?? ''); ?>"><br>
                    <label>Mot de passe:</label>
                    <input type="password" name="password"><br>
                </div>
            </div>
            <label>Rôle:</label>
            <select name="role" required>
                <option value="client" <?php echo $row['role'] == 'client' ? 'selected' : ''; ?>>Client</option>
                <option value="employer" <?php echo $row['role'] == 'employer' ? 'selected' : ''; ?>>Employé</option>
                <option value="admin" <?php echo $row['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select><br>
            <input type="submit" value="Mettre à jour">
        </form>
    </div>
</body>

</html>
