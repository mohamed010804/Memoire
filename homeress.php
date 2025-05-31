<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Barid</title>
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
        .content {
            margin-top: 10%;
        }
        .content h1 {
            font-size: 3rem;
            font-weight: 700;
        }
        .content p {
            font-size: 1.2rem;
            margin-top: 15px;
        }
        .btn-custom {
            background-color: deeppink;
            color: white;
            font-size: 1.2rem;
            padding: 15px 30px;
            border-radius: 50px;
            border: none;
            margin-top: 20px;
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
            <a href="#">Barid</a>
            <a href="connectress.php">Connexion</a>
        </header>
        <div class="content">
            <h1>Bienvenue sur Barid</h1>
            <p>Envoyez et recevez vos colis facilement grâce à notre plateforme intuitive. Suivi en temps réel, service rapide et sécurisé.</p>
            <a href="client.php" class="btn btn-custom">Suivre un colis</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
