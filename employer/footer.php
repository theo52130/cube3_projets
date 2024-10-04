<footer>
    <div class="footer-content">
        <ul>
            <li><a href="../annexes/mentions-legales.php">Mentions Légales</a></li>
            <li><a href="../annexes/rgpd.php">RGPD</a></li>
            <li><a href="../annexes/cookies.php">Politique de Cookies</a></li>
            <li><a href="../annexes/contact.php">Contact</a></li>
        </ul>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> Votre Société. Tous droits réservés.</p>
    </div>
</footer>

<style>
    footer {
        background-color: #007bff;
        /* Couleur de fond similaire au header */
        color: #fff;
        /* Couleur du texte */
        padding: 10px 0;
        /* Réduire la hauteur du footer */
        text-align: center;
        font-size: 14px;
        /* Taille de police plus petite */
    }

    .footer-content ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-content ul li {
        display: inline;
        margin: 0 10px;
        /* Réduire l'espacement entre les éléments */
    }

    .footer-content ul li a {
        color: #fff;
        /* Couleur du texte */
        text-decoration: none;
    }

    .footer-content ul li a:hover {
        text-decoration: underline;
    }

    .footer-bottom {
        margin-top: 10px;
        /* Réduire l'espacement en haut du texte de bas de page */
        color: #fff;
        /* Assurer que le texte est en blanc */
    }

    .footer-bottom p {
        margin: 0;
    }
</style>