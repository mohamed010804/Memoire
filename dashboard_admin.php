<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header("Location: connectress.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Tableau de bord</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap');
        body {
            height: 100vh;
            background-image: linear-gradient(to right, #01022d, #000);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            color: white;
            font-family: "Ubuntu", sans-serif;
        }
        .container-custom {
            width: 90%;
            max-width: 1200px;
            height: 80vh;
            background-image: linear-gradient(to right, #01022de5 30%, #00000050), url('background.jpg');
            background-size: cover;
            background-position: center;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        .dashboard-content {
            margin-top: 10%;
        }
        .dashboard-content h1 {
            font-size: 2.8rem;
            font-weight: 700;
        }
        .dashboard-content p {
            font-size: 1.2rem;
            margin-top: 15px;
        }
        .btn-custom {
            background-color: deeppink;
            color: white;
            font-size: 1.1rem;
            padding: 12px 25px;
            border-radius: 50px;
            border: none;
            margin: 10px;
        }
        .btn-custom:hover {
            background-color: transparent;
            border: 2px solid deeppink;
            color: deeppink;
        }
    </style>
</head>
<body>
    <div class="container-custom">
        <header>
            <a href="#">Barid Admin</a>
            <a href="logout.php">Déconnexion</a>
        </header>
        <div class="dashboard-content">
            <h1>Tableau de bord Administrateur</h1>
            <p>Bienvenue, <?php echo $_SESSION['username']; ?>. Que souhaitez-vous faire ?</p>
            <div class="d-flex justify-content-center flex-wrap mt-4">
                <a href="gestion_utilisateurs.php" class="btn btn-custom">Gérer les utilisateurs</a>
                <a href="stat.php" class="btn btn-custom">Voir les statistiques</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
