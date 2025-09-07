<?php
session_start();
include 'db_connect.php'; // connexion à la base

$success = false; // Flag pour afficher le message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            // Gestion du cookie "Se souvenir de moi"
            if (isset($_POST['remember'])) {
                setcookie("remember_email", $email, time() + (30 * 24 * 60 * 60), "/"); // 30 jours
            } else {
                setcookie("remember_email", "", time() - 3600, "/"); // supprimer le cookie
            }

            $success = true;
            header("refresh:2;url=dashboard.php"); // Redirection après 2 secondes
        } else {
            $error = "⚠️ Mot de passe incorrect.";
        }
    } else {
        $error = "⚠️ Email non trouvé.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion - Notly</title>
  <link rel="stylesheet" href="../css/inscription.css">
</head>
<body>

  <div class="container">
    <h1>Se connecter</h1>

    <!-- Message d'erreur -->
    <?php if (isset($error)) : ?>
      <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>

    <!-- Message de succès -->
    <?php if ($success) : ?>
      <p style="color:green;">✅ Connexion réussie ! Redirection...</p>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="email">Adresse email</label>
      <input type="email" id="email" name="email" value="<?= $_COOKIE['remember_email'] ?? '' ?>" required>

      <label for="password">Mot de passe</label>
      <input type="password" id="password" name="password" required>

      <label>
        <input type="checkbox" name="remember" <?= isset($_COOKIE['remember_email']) ? 'checked' : '' ?>>
        Se souvenir de moi
      </label>

      <button type="submit">Connexion</button>
    </form>

    <div class="link">
      Pas encore inscrit ? <a href="inscription.php">Créer un compte</a>
    </div>
  </div>

</body>
</html>
