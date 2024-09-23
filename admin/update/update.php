<?php
include '../../db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM comptes WHERE id=$id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $compte = $result->fetch_assoc();
    } else {
        echo "Aucun compte trouvé avec cet ID.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mettre à jour un compte</title>
</head>
<body>
    <h2>Mettre à jour un compte</h2>
    <form method="post" action="update_pross.php">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($compte['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        Nom: <input type="text" name="nom" value="<?php echo htmlspecialchars($compte['nom'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br>
        Email: <input type="text" name="email" value="<?php echo htmlspecialchars($compte['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br>
        Adresse: <input type="text" name="adresse" value="<?php echo htmlspecialchars($compte['adresse'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br>
        Email entreprise: <input type="text" name="email_entreprise" value="<?php echo htmlspecialchars($compte['email_entreprise'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br>
        SIRET: <input type="text" name="siret" value="<?php echo htmlspecialchars($compte['siret'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br>
        <input type="submit" value="Mettre à jour">
    </form>
</body>
</html>
