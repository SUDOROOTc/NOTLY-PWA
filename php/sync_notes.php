<?php
header('Content-Type: text/plain'); // On envoie du texte simple, pas du JSON

// Récupérer les données envoyées par FormData
$categorieId = $_POST['categorieId'] ?? null;
$produitId   = $_POST['produitId'] ?? null;
$fournisseur = $_POST['fournisseur'] ?? null;
$prix        = $_POST['prix'] ?? null;
$dateCreation = $_POST['dateCreation'] ?? null;
$dateCreation= date('Y-m-d H:i:s', strtotime($dateCreation)); // Format MySQL

// Vérifier que les champs obligatoires sont bien là
if (!$categorieId || !$produitId) {
    echo "Erreur : données manquantes";
    exit;
}

// Connexion MySQL
$conn = new mysqli("localhost", "root", "", "notly_db");
if ($conn->connect_error) {
    echo "Erreur connexion MySQL : " . $conn->connect_error;
    exit;
}

// Insertion dans la table
$stmt = $conn->prepare("INSERT INTO notes (categorieId, produitId, fournisseur, prix, dateCreation) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisds", $categorieId, $produitId, $fournisseur, $prix, $dateCreation);

if ($stmt->execute()) {
    echo "✅ Note insérée avec succès (ID: " . $stmt->insert_id . ")";
} else {
    echo "❌ Erreur insertion : " . $stmt->error;
}

$stmt->close();
$conn->close();
?>