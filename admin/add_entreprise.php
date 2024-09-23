<?php
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'test_cube_trois');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $adresse = $_POST['adresse'];

    $sql = "INSERT INTO entreprises (nom, adresse) VALUES ('$nom', '$adresse')";
    if ($conn->query($sql)) {
        echo "Entreprise ajoutée avec succès";
    } else {
        echo "Erreur: " . $conn->error;
    }

    // Rediriger vers la liste des entreprises après l'ajout
    header('Location: list_entreprises.php');
    exit();
}
