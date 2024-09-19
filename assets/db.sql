USE test_cube_trois;

CREATE TABLE entreprises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    adresse TEXT
);

CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    entreprise_id INT,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises (id)
);

CREATE TABLE factures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    date_creation DATE,
    total DECIMAL(10, 2),
    etat ENUM('payée', 'non payée') DEFAULT 'non payée',
    FOREIGN KEY (client_id) REFERENCES clients (id)
);

CREATE TABLE produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    facture_id INT,
    description TEXT,
    prix_unitaire DECIMAL(10, 2),
    quantite INT,
    FOREIGN KEY (facture_id) REFERENCES factures (id)
);

CREATE TABLE factures_produits (
    facture_id INT,
    produit_id INT,
    FOREIGN KEY (facture_id) REFERENCES factures (id),
    FOREIGN KEY (produit_id) REFERENCES produits (id)
);

CREATE TABLE utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL
);

CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL
);

CREATE TABLE utilisateurs_rôles (
    utilisateur_id INT,
    rôle_id INT,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id),
    FOREIGN KEY (rôle_id) REFERENCES roles (id)
);