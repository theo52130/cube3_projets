<?php
include '../../db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Validate the ID
    $id = filter_var($id, FILTER_VALIDATE_INT);

    if ($id) {
        $sql = "DELETE FROM comptes WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            echo "Compte supprimé avec succès";
        } else {
            echo "Erreur: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "ID invalide.";
    }
} else {
    echo "Aucun ID fourni.";
}
?>
