<?php
// db_connect.php

$host = "localhost";     // ou 127.0.0.1
$user = "root";          // ton utilisateur MySQL
$password = "";          // ton mot de passe MySQL
$dbname = "notly_db";    // le nom de ta base de données

// Créer la connexion
$conn = new mysqli($host, $user, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Définir le charset pour gérer correctement les accents et caractères spéciaux
$conn->set_charset("utf8");
?>
