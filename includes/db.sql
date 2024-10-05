CREATE DATABASE IF NOT EXISTS test_cube_trois;

USE test_cube_trois;

CREATE TABLE comptes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    adresse VARCHAR(255) NOT NULL,
    email_entreprise VARCHAR(255),
    siret BIGINT,
    password VARCHAR(255) NOT NULL,
    role ENUM('client', 'employer', 'admin') NOT NULL
);

CREATE TABLE factures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    date_creation DATE NOT NULL DEFAULT CURRENT_DATE,
    quantite_produits INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    etat ENUM('payée', 'non payée') DEFAULT 'non payée',
    FOREIGN KEY (client_id) REFERENCES comptes (id) ON DELETE CASCADE
);

CREATE TABLE produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    prix_unitaire DECIMAL(10, 2) NOT NULL
);

CREATE TABLE factures_produits (
    facture_id INT,
    produit_id INT,
    PRIMARY KEY (facture_id, produit_id),
    FOREIGN KEY (facture_id) REFERENCES factures (id) ON DELETE CASCADE,
    FOREIGN KEY (produit_id) REFERENCES produits (id) ON DELETE CASCADE
);