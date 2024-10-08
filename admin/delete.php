<?php
session_start();

// Vérification de la connexion et du rôle
if (!isset($_SESSION['nom']) || $_SESSION['role'] != 'admin') {
    header("Location: ../public/login.php");
    exit();
}

include "../includes/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = intval($_POST['id']);

    if ($action === 'delete_account') {
        // Suppression d'un compte
        $delete_account_sql = "DELETE FROM comptes WHERE id = ?";
        $stmt = $conn->prepare($delete_account_sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo 'success';
        } else {
            echo 'error';
        }

        $stmt->close();
    } elseif ($action === 'delete_invoice') {
        // Suppression des produits associés à la facture
        $delete_products_sql = "DELETE FROM factures_produits WHERE facture_id = ?";
        $stmt = $conn->prepare($delete_products_sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Suppression de la facture
        $delete_invoice_sql = "DELETE FROM factures WHERE id = ?";
        $stmt = $conn->prepare($delete_invoice_sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo 'success';
        } else {
            echo 'error';
        }

        $stmt->close();
    } else {
        echo 'invalid_action';
    }
}

$conn->close();
