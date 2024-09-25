<div id="pageFactures">

    <h3>Produit</h3>

    <?php
    // Connexion à la base de données
    $host = 'localhost'; // L'adresse du serveur de base de données
    $dbname = 'nom_de_ta_base_de_donnees'; // Le nom de ta base de données
    $username = 'ton_nom_utilisateur'; // Ton nom d'utilisateur MySQL
    $password = 'ton_mot_de_passe'; // Ton mot de passe MySQL

    try {
        // Création d'une nouvelle connexion PDO
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Requête SQL pour récupérer les produits
        $sql = "SELECT * FROM produits";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        // Récupération des résultats
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
    ?>


</div>