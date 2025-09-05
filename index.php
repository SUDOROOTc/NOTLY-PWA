<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTLY - Accueil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="manifest" href="pwa/manifest.json">
    <meta name="theme-color" content="#27C7D4">
    <script>
       if ('serviceWorker' in navigator) {
         navigator.serviceWorker.register('js/ServiceWorker.js')
         .then(() => console.log('Service Worker enregistré !'))
         .catch(err => console.log('Erreur SW:', err));
         }
    </script>

</head>
<body>

    <!-- Header simplifié -->
    <header>
        <h1 class="logo">NOTLY</h1>
        <div class="header-buttons">
            <a href="connexion.php" class="btn-login">Se connecter</a>
            <a href="inscription.php" class="btn-register">S’inscrire</a>
        </div>
    </header>

    <!-- Section principale (Hero) -->
    <main>
        <section class="hero">
            <h2>Centralisez, structurez, analysez </h2>
            <p>NOTLY vous permet de prendre les informations  rapidement, de les centraliser et de les synchroniser automatiquement dès que vous êtes en ligne.</p>
            <div class="btn-container">
                <a href="./php/inscription.php" class="btn-primary">Offline</a>
                <a href="./php/acceuil.php" class="btn-secondary">Online</a>
            </div>

        </section>
    </main>

    <!-- Footer -->
    <footer>
        © NOTLY 2025
    </footer>

    <script src="js/indexedDB.js"></script>

</body>
</html>
