<?php
session_start();

// Vérification de la connexion
if (!isset($_SESSION['nom']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employer')) {
    header("Location: ../login.php");
    exit();
}

header("Location: ../public/login.php");

// Récupérer les données du compte de l'utilisateur connecté
$id = intval($_SESSION['user_id']);
$sql = "SELECT * FROM comptes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Traitement de la mise à jour
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == 'update') {
    $nom = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['nom'] ?? ''));
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $adresse = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['adresse'] ?? ''));
    $email_entreprise = !empty($_POST['email_entreprise']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_POST['email_entreprise'])) : null;
    $siret = !empty($_POST['siret']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_POST['siret'])) : null;

    // Hash du mot de passe seulement si le champ n'est pas vide
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $row['password'];
    $role = isset($_POST['role']) ? htmlspecialchars(mysqli_real_escape_string($conn, $_POST['role'])) : $row['role'];

    // Validation des champs côté serveur
    if (!preg_match("/^[A-Za-zÀ-ÿ\s'-]+$/", $nom)) {
        $_SESSION['error_message'] = "Nom invalide.";
    } elseif (!$email) {
        $_SESSION['error_message'] = "Email invalide.";
    } elseif ($siret && !preg_match("/^\d{14}$/", $siret)) {
        $_SESSION['error_message'] = "SIRET invalide.";
    } elseif ($role && !in_array($role, ['client', 'employer', 'admin'])) {
        $_SESSION['error_message'] = "Rôle invalide.";
    } elseif (isset($_POST['confirm_password']) && $_POST['password'] !== $_POST['confirm_password']) {
        $_SESSION['error_message'] = "Les mots de passe ne correspondent pas.";
    } else {
        // Préparer la requête de mise à jour
        $updateSql = "UPDATE comptes SET 
                        nom = ?, 
                        email = ?, 
                        adresse = ?, 
                        email_entreprise = ?, 
                        siret = ?, 
                        password = ?, 
                        role = ? 
                      WHERE id = ?";
        $stmt = $conn->prepare($updateSql);

        // Vérifie si la préparation de la requête a réussi
        if ($stmt === false) {
            die('Erreur de préparation : ' . htmlspecialchars($conn->error));
        }

        // Bind des paramètres
        $stmt->bind_param("sssssssi", $nom, $email, $adresse, $email_entreprise, $siret, $password, $role, $id);

        // Exécuter la requête
        if ($stmt->execute()) {
            // Redirection après mise à jour réussie
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Erreur: " . htmlspecialchars($stmt->error);
        }
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
    <title>Détails du Compte</title>
    <link rel="stylesheet" href="../assets/css/account-style.css">
</head>

<body>
    <?php require './header.php'; ?>

    <div id="containerAdmin">
        <h2>Détails du Compte</h2>

        <?php if ($error_message): ?>
            <div style="color: red;"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="account.php" method="post" id="formId">
            <input type="hidden" name="action" value="update">
            <div id="table-1">
                <div id="col-1">
                    <label>*Nom:</label>
                    <input type="text" name="nom" value="<?php echo htmlspecialchars($row['nom']); ?>" required pattern="[A-Za-zÀ-ÿ\s'-]+" title="Nom ne doit contenir que des lettres et des espaces"><br>

                    <label>*Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required><br>

                    <label>Adresse:</label>
                    <input type="text" name="adresse" value="<?php echo htmlspecialchars($row['adresse']); ?>" id="adresse" readonly><br>
                </div>

                <div id="col-2">
                    <label>Email Entreprise:</label>
                    <input placeholder="Impossible de modifier si siret vide" type="email" name="email_entreprise" value="<?php echo htmlspecialchars($row['email_entreprise'] ?? ''); ?>" id="email_entreprise" readonly><br>

                    <label>SIRET:</label>
                    <input placeholder="Impossible de modifier" type="text" name="siret" value="<?php echo htmlspecialchars($row['siret'] ?? ''); ?>" pattern="\d{14}" title="SIRET doit être un numéro de 14 chiffres" id="siret" oninput="checkSiret()" readonly><br>

                    <label>Mot de passe:</label>
                    <input type="password" name="password" id="password" placeholder="Laissez vide pour ne pas changer" pattern=".{4,}" title="Mot de passe doit contenir au moins 6 caractères" oninput="toggleConfirmPassword()"><br>

                    <div id="confirm-password-container" style="display: none;">
                        <label>Confirmer le mot de passe:</label>
                        <input type="password" name="confirm_password" id="confirm_password"><br>
                    </div>
                </div>

                <script>
                    function toggleConfirmPassword() {
                        const passwordInput = document.getElementById('password');
                        const confirmPasswordContainer = document.getElementById('confirm-password-container');

                        // Afficher le champ de confirmation si le mot de passe n'est pas vide
                        if (passwordInput.value) {
                            confirmPasswordContainer.style.display = 'block';
                        } else {
                            confirmPasswordContainer.style.display = 'none';
                        }
                    }

                    function checkSiret() {
                        const siretInput = document.getElementById('siret').value;
                        const emailInput = document.getElementById('email_entreprise');
                        const adresseInput = document.getElementById('adresse');

                        // Si le SIRET est vide, rendre les champs readonly
                        if (siretInput === '') {
                            emailInput.readOnly = true;
                            adresseInput.readOnly = true;
                        } else {
                            emailInput.readOnly = false;
                            adresseInput.readOnly = false;
                        }
                    }

                    // Appel initial pour vérifier l'état des champs
                    checkSiret();
                </script>
            </div>

            <input type="submit" value="Mettre à jour">
        </form>
    </div>
</body>

</html>