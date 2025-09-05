<?php
include 'db_connect.php'; // fichier de connexion à la BDD
include 'session.php';  // gestion de la session
// Récupérer toutes les catégories pour la liste déroulante
$categories = $conn->query("SELECT * FROM categories ORDER BY nom ASC");

// Récupérer les produits si une catégorie est sélectionnée
$categorie_id = $_POST['categorie'] ?? null;
$produits = [];

if ($categorie_id) {
    $stmt = $conn->prepare("SELECT id, nom FROM produits WHERE categorie_id = ?");
    $stmt->bind_param("i", $categorie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $produits[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notly - Main Page</title>
<link rel="stylesheet" href="../css/acceuil.css">
</head>
<body>

<header>
    <div class="logo">Notly</div>
    <button class="btn-logout">Déconnexion</button>
</header>

<div class="main-container">
    <!-- Sidebar gauche -->
    <div class="sidebar">
        <!-- Ici tu peux afficher des cartes dynamiques -->
    </div>

    <!-- Contenu central -->
    <div class="content">
        <h2>Ajouter une note rapidement</h2>

        <form action="insert_note.php" method="POST">
            <!-- 1. Catégorie -->
            <label for="categorie">Catégorie</label>
            <select id="categorie" name="categorie" onchange="this.form.submit()" required>
                <option value="">Sélectionnez une catégorie</option>
                <?php while($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $categorie_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nom']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <!-- 2. Produit -->
            <label for="produit">Produit</label>
            <select id="produit" name="produit" required>
                <option value="">Sélectionnez un produit</option>
                <?php foreach($produits as $prod): ?>
                    <option value="<?= $prod['id'] ?>"><?= htmlspecialchars($prod['nom']) ?></option>
                <?php endforeach; ?>
            </select>

            <!-- 3. Numéro du fournisseur -->
            <label for="fournisseur">Numéro du fournisseur</label>
            <input type="text" id="fournisseur" name="fournisseur" placeholder="Numéro ou nom du fournisseur" required>

            <!-- 4. Prix -->
            <label for="prix">Prix</label>
            <input type="number" id="prix" name="prix" placeholder="Prix en XOF" required>

            <button type="submit">Ajouter la note</button>
        </form>
    </div>
</div>

</body>
</html>

