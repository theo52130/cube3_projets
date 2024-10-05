<?php
session_start();

// Vérification de la connexion et du rôle
if (!isset($_SESSION['nom']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

require '../includes/config.php';

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
    $nom = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['nom']));
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $adresse = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['adresse']));
    $email_entreprise = !empty($_POST['email_entreprise']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_POST['email_entreprise'])) : null;
    $siret = !empty($_POST['siret']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_POST['siret'])) : null;
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['role']));

    // Validation des mots de passe
    if (!empty($password) && $password !== $confirm_password) {
        $_SESSION['error_message'] = "Les mots de passe ne correspondent pas.";
    } else {
        // Hachage du mot de passe seulement si un nouveau mot de passe est fourni
        $hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

        // Préparer la requête de mise à jour
        $updateSql = "UPDATE comptes SET 
                        nom = ?, 
                        email = ?, 
                        adresse = ?, 
                        email_entreprise = ?, 
                        siret = ?, 
                        role = ? " .
            ($hashed_password ? ", password = ?" : "") .
            " WHERE id = ?";

        $stmt = $conn->prepare($updateSql);

        // Vérifier si la préparation a réussi
        if ($stmt === false) {
            die('Erreur de préparation : ' . htmlspecialchars($conn->error));
        }

        // Dynamically bind parameters
        if ($hashed_password) {
            $stmt->bind_param("sssssssi", $nom, $email, $adresse, $email_entreprise, $siret, $role, $hashed_password, $id);
        } else {
            $stmt->bind_param("ssssssi", $nom, $email, $adresse, $email_entreprise, $siret, $role, $id);
        }

        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            echo "Erreur: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

// Récupérer le message d'erreur
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['error_message']); // Réinitialiser le message d'erreur pour ne pas le montrer à nouveau
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

        <?php if ($error_message): ?>
            <div style="color: red;"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="update.php" method="post">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
            <div id="table-1">
                <div id="col-1">
                    <label>*Nom:</label>
                    <input type="text" name="nom" value="<?php echo htmlspecialchars($row['nom']); ?>" required><br>
                    <label>*Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required><br>
                    <label>*Adresse:</label>
                    <input type="text" name="adresse" value="<?php echo htmlspecialchars($row['adresse']); ?>" required><br>
                </div>

                <div id="col-2">
                    <label>Email Entreprise:</label>
                    <input type="email" name="email_entreprise" value="<?php echo htmlspecialchars($row['email_entreprise'] ?? ''); ?>" <?php echo empty($row['siret']) ? 'readonly' : ''; ?>><br>

                    <label>SIRET:</label>
                    <input type="text" name="siret" value="<?php echo htmlspecialchars($row['siret'] ?? ''); ?>" readonly><br>

                    <label>Mot de passe:</label>
                    <input type="password" name="password" id="password" placeholder="Laissez vide pour ne pas changer" oninput="toggleConfirmPassword()"><br>

                    <div id="confirm-password-container" style="display: none;">
                        <label>Confirmer le mot de passe:</label>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirme ton mot de passe :"><br>
                    </div>
                </div>

                <script>
                    function toggleConfirmPassword() {
                        const passwordInput = document.getElementById('password');
                        const confirmPasswordContainer = document.getElementById('confirm-password-container');

                        // Afficher le champ de confirmation si le mot de passe n'est pas vide
                        confirmPasswordContainer.style.display = passwordInput.value ? 'block' : 'none';
                    }
                </script>

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