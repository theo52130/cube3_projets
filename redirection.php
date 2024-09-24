<?php
session_start();

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ./admin/index.php");
        exit();
    } elseif ($_SESSION['role'] == 'employer') {
        header("Location: ./employer/index.php");
        exit();
    } elseif ($_SESSION['role'] == 'client') {
        header("Location: ./client/index.php");
        exit();
    } else {
        echo "Rôle non reconnu.";
    }
} else {
    echo "Session non démarrée ou rôle non défini.";
}
