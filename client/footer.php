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
    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    main {
        flex: 1;
    }

    footer {
        background-color: #007bff;
        color: #fff;
        padding: 10px 0;
        text-align: center;
        font-size: 14px;
        width: 100%;
        position: relative;
        bottom: 0;
    }

    .footer-content ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-content ul li {
        display: inline;
        margin: 0 10px;
    }

    .footer-content ul li a {
        color: #fff;
        text-decoration: none;
    }

    .footer-content ul li a:hover {
        text-decoration: underline;
    }

    .footer-bottom {
        margin-top: 10px;
        color: #fff;
    }

    .footer-bottom p {
        margin: 0;
    }
</style>