<?php
session_start();

// Vérification de la connexion et du rôle
if (!isset($_SESSION['nom']) || $_SESSION['role'] != 'admin') {
    echo 'error';
    exit();
}

// Connexion à la base de données
require '../config.php';

// Traitement de la demande de suppression
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['id']);
    $deleteSql = "DELETE FROM comptes WHERE id = $id";
    if (mysqli_query($conn, $deleteSql)) {
        echo 'success';
    } else {
        echo 'error';
    }
}
