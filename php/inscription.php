<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";   // ⚠️ Mets ton user MySQL si ce n'est pas root
$password = "";       // ⚠️ Mets ton mot de passe MySQL si tu en as mis un
$dbname = "database_notly"; // ⚠️ Mets le nom de ta base de données

// Crée la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifie la connexion
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Gestion du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    // Vérifier que les mots de passe correspondent
    if ($password !== $confirm_password) {
        $error = "⚠️ Les mots de passe ne correspondent pas.";
    } else {
        // Hasher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Préparer la requête
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);

        try {
            if ($stmt->execute()) {
                // Rediriger vers la page de connexion
                header("Location: connexion.php?success=1");
                exit();
            } else {
                $error = "❌ Erreur lors de l'inscription.";
            }
        } catch (Exception $e) {
            $error = "⚠️ Cet email est déjà utilisé.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription - Notly</title>
  <link rel="stylesheet" href="../css/inscription.css">
</head>

<body>
  <div class="container">
    <h1>Créer un compte</h1>

    <!-- Affichage des erreurs -->
    <?php if (isset($error)): ?>
      <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="name">Nom complet</label>
      <input type="text" id="name" name="name" required>

      <label for="email">Adresse email</label>
      <input type="email" id="email" name="email" required>

      <label for="password">Mot de passe</label>
      <input type="password" id="password" name="password" required>

      <label for="confirm-password">Confirmer le mot de passe</label>
      <input type="password" id="confirm-password" name="confirm-password" required>

      <button type="submit">S’inscrire</button>
    </form>

    <div class="link">
      Déjà inscrit ? <a href="connexion.php">Se connecter</a>
    </div>
  </div>
</body>
</html>
