<?php
session_start();
include 'db_connect.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les notes de l'utilisateur
$stmt = $conn->prepare("SELECT * FROM notes WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notes = $result->fetch_all(MYSQLI_ASSOC);

// Calculer la progression des informations utilisateur
$progress = 0;
if (!empty($_SESSION['user_name'])) $progress += 25;
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM notes WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
if ($res['cnt'] > 0) $progress += 75;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Notly</title>
<link rel="stylesheet" href="../css/dashboard.css">
<!-- Chart.js pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<header>
    <h1>Bonjour, <?= htmlspecialchars($_SESSION['user_name']); ?></h1>
</header>

<div class="main-container">
    <div class="progress-section">
        <h2>Progression de vos informations</h2>
        <canvas id="progressChart" width="200" height="200"></canvas>
    </div>

    <div class="table-section">
        <h2>Vos notes</h2>
        <table id="notesTable">
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th>Produit</th>
                    <th>Fournisseur</th>
                    <th>Prix (XOF)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $note): ?>
                    <tr>
                        <td><?= htmlspecialchars($note['categorie']); ?></td>
                        <td><?= htmlspecialchars($note['produit']); ?></td>
                        <td><?= htmlspecialchars($note['fournisseur']); ?></td>
                        <td><?= htmlspecialchars($note['prix']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="chart-section">
        <h2>Évolution des notes</h2>
        <canvas id="notesChart" width="400" height="200"></canvas>
    </div>
</div>

<script>
// Diagramme circulaire de progression
const ctxProgress = document.getElementById('progressChart').getContext('2d');
const progressChart = new Chart(ctxProgress, {
    type: 'doughnut',
    data: {
        labels: ['Complété', 'Restant'],
        datasets: [{
            data: [<?= $progress ?>, <?= 100 - $progress ?>],
            backgroundColor: ['#4CAF50', '#e0e0e0'],
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Graphique d'évolution des notes
const ctxNotes = document.getElementById('notesChart').getContext('2d');
const notesChart = new Chart(ctxNotes, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($notes, 'produit')) ?>,
        datasets: [{
            label: 'Prix des notes',
            data: <?= json_encode(array_column($notes, 'prix')) ?>,
            backgroundColor: '#2196F3'
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>

</body>
</html>
