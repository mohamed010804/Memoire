<?php
session_start();

// Vérifier si l'utilisateur est connecté et a le rôle agent
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'agent') {
    header('Location: connexion.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Agent</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap');
        * {
            font-family: "Ubuntu", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(to right, #01022d, #000);
            padding: 10px;
        }
        .container {
            width: 90%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
            text-align: center;
        }
        h2 {
            color: white;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }
        .nav {
            list-style: none;
            padding: 0;
            margin: 0 0 20px;
        }
        .nav-item {
            margin: 10px 0;
        }
        .nav-link {
            display: block;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            color: white;
            background-color: deeppink;
            text-decoration: none;
            transition: 0.3s;
            text-align: center;
        }
        .nav-link:hover {
            background-color: white;
            color: deeppink;
        }
        .logout {
            background-color: #ff4d4d;
        }
        .logout:hover {
            background-color: white;
            color: #ff4d4d;
        }
        /* Responsive */
        @media (max-width: 600px) {
            .container { padding: 20px; }
            h2 { font-size: 1.5rem; }
            .nav-link { padding: 10px; font-size: 0.9rem; }
        }
        @media (min-width: 601px) and (max-width: 900px) {
            .container { padding: 25px; }
            h2 { font-size: 1.6rem; }
            .nav-link { padding: 11px; font-size: 1rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Dashboard Agent</h2>
        <ul class="nav">
            <li class="nav-item"><a class="nav-link" href="expidress.php">Enregistrement</a></li>
            <li class="nav-item"><a class="nav-link" href="suivress.php">Suivi</a></li>
            <li class="nav-item"><a class="nav-link" href="arvress.php">Validation</a></li>
            <li class="nav-item"><a class="nav-link" href="cptress.php">Compter Montant</a></li>
            <li class="nav-item"><a class="nav-link" href="gererclient.php">Gestion_enregistrement</a></li>
            <li class="nav-item"><a class="nav-link logout" href="homeress.php">Déconnexion</a></li>
        </ul>
    </div>
</body>
</html>
