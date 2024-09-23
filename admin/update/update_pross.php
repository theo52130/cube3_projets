<?php
include '../../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $email_entreprise = $_POST['email_entreprise'] ?? '';
    $siret = $_POST['siret'] ?? '';

    // Validate and sanitize inputs
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $nom = htmlspecialchars($nom, ENT_QUOTES, 'UTF-8');
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    $adresse = htmlspecialchars($adresse, ENT_QUOTES, 'UTF-8');
    $email_entreprise = !empty($email_entreprise) ? htmlspecialchars($email_entreprise, ENT_QUOTES, 'UTF-8') : NULL;
    $siret = !empty($siret) ? filter_var($siret, FILTER_VALIDATE_INT) : NULL;

    if ($id && $email) {
        $sql = "UPDATE comptes SET nom='$nom', email='$email', adresse='$adresse', email_entreprise=" . ($email_entreprise ? "'$email_entreprise'" : "NULL") . ", siret=" . ($siret ? "'$siret'" : "NULL") . " WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            echo "Compte mis à jour avec succès";
        } else {
            echo "Erreur: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Erreur: Données invalides.";
    }
}
