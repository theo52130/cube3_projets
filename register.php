<!DOCTYPE html>
<html>

<head>
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

    <?php
    require('./config.php');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nom = mysqli_real_escape_string($conn, $_POST['nom']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $adresse = mysqli_real_escape_string($conn, $_POST['adresse']);
        $email_entreprise = !empty($_POST['email_entreprise']) ? mysqli_real_escape_string($conn, $_POST['email_entreprise']) : null;
        $siret = !empty($_POST['siret']) ? mysqli_real_escape_string($conn, $_POST['siret']) : null;
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = mysqli_real_escape_string($conn, $_POST['role']);

        // Insertion dans la table 'comptes' avec les nouveaux champs
        $sql = "INSERT INTO comptes (nom, email, adresse, email_entreprise, siret, password, role) 
                VALUES ('$nom', '$email', '$adresse', " . ($email_entreprise ? "'$email_entreprise'" : "NULL") . ", " . ($siret ? "'$siret'" : "NULL") . ", '$password', '$role')";

        if (mysqli_query($conn, $sql)) {
            echo "Inscription réussie!";
        } else {
            echo "Erreur: " . $sql . "<br>" . mysqli_error($conn);
        }
    }
    ?>
</body>

</html>
