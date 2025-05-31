<?php
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "ameliorec");
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Requêtes statistiques
$totalColis = $conn->query("SELECT COUNT(*) AS total FROM colis")->fetch_assoc()['total'];
$colisLivres = $conn->query("SELECT COUNT(*) AS total FROM colis WHERE etat = 'Délivré'")->fetch_assoc()['total'];
$colisTransits = $conn->query("SELECT COUNT(*) AS total FROM colis WHERE etat = 'En transit'")->fetch_assoc()['total'];
$colisRetours = $conn->query("SELECT COUNT(*) AS total FROM colis WHERE etat = 'Retour'")->fetch_assoc()['total'];
$totalCR = $conn->query("SELECT SUM(CR) AS total FROM colis")->fetch_assoc()['total'];
$totalLivraison = $conn->query("SELECT SUM(livraison) AS total FROM colis")->fetch_assoc()['total'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques Administrateur</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap');
        body {
            height: 100vh;
            background-image: linear-gradient(to right, #01022d, #000);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-family: 'Ubuntu', sans-serif;
        }
        .container-custom {
            width: 90%;
            max-width: 1200px;
            background-image: linear-gradient(to right, #01022de5 30%, #00000050), url('background.jpg');
            background-size: cover;
            background-position: center;
            border-radius: 10px;
            padding: 30px;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        header a {
            color: white;
            font-size: 1.8rem;
            font-weight: bold;
            text-decoration: none;
        }
        header a:hover {
            color: deeppink;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .stat-card {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid deeppink;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card h2 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: deeppink;
        }
        .stat-card p {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="container-custom">
        <header>
            <a href="#">Barid</a>
            <a href="logout.php">Déconnexion</a>
        </header>
        <h1 class="text-center mb-4">Tableau de Bord - Statistiques</h1>
        <div class="stats-grid">
            <div class="stat-card">
                <h2><?= $totalColis ?></h2>
                <p>Total des Colis</p>
            </div>
            <div class="stat-card">
                <h2><?= $colisLivres ?></h2>
                <p>Colis Livrés</p>
            </div>
            <div class="stat-card">
                <h2><?= $colisTransits ?></h2>
                <p>En Transit</p>
            </div>
            <div class="stat-card">
                <h2><?= $colisRetours ?></h2>
                <p>Colis Retournés</p>
            </div>
            <div class="stat-card">
                <h2><?= number_format($totalCR, 2) ?> DA</h2>
                <p>Recettes (CR)</p>
            </div>
            <div class="stat-card">
                <h2><?= number_format($totalLivraison, 2) ?> DA</h2>
                <p>Frais de Livraison</p>
            </div>
        </div>
    </div>
</body>
</html>
