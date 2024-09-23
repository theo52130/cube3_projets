<?php
$conn = new mysqli('localhost', 'root', '', 'test_cube_trois');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $entreprise_id = $_POST['entreprise_id'];

    // V�rifier si une entreprise a �t� s�lectionn�e
    if (empty($entreprise_id)) {
        $entreprise_id = "NULL"; // Si aucune entreprise s�lectionn�e, mettre NULL
    } else {
        $entreprise_id = (int)$entreprise_id; // Si une entreprise est s�lectionn�e, convertir en entier
    }

    // Requ�te SQL d'insertion
    $sql = "INSERT INTO clients (nom, email, entreprise_id) VALUES ('$nom', '$email', $entreprise_id)";
    if ($conn->query($sql)) {
        echo "Client ajout� avec succ�s";
    } else {
        echo "Erreur: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
    <title>Ajouter un Client</title>
</head>

<body>
    <div class="container">
        <h1>Ajouter un Client</h1>
        <form method="POST">
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="email" name="email" placeholder="Email" required>
            <select name="entreprise_id">
                <option value="">S�lectionner une entreprise (facultatif)</option>
                <?php
                // R�cup�rer la liste des entreprises
                $entreprises = $conn->query("SELECT * FROM entreprises");
                while ($entreprise = $entreprises->fetch_assoc()) {
                    echo "<option value='{$entreprise['id']}'>{$entreprise['nom']}</option>";
                }
                ?>
            </select>
            <button type="submit">Ajouter</button>
        </form>
        <a href="../autres/list_entreprises.php">Voir la liste des entreprises</a>
    </div>
</body>

</html>