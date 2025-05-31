<?php
session_start();
include 'configress.php'; // connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['pass'] ?? '';

    $sql = "SELECT * FROM utilisateur WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Comparaison directe sans hash
        if ($password == $user['password']) {
            $_SESSION['iduser'] = $user['iduser'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['userwilaya'] = $user['userwilaya'];
            $_SESSION['role'] = $user['role'];

            // Redirection selon le rôle
            if ($user['role'] === 'administrateur') {
                header("Location: dashboard_admin.php");
                exit();
            } elseif ($user['role'] === 'agent') {
                header("Location: dashboard_agent.php");
                exit();
            } elseif ($user['role'] === 'remorque') {
                header("Location: remorque_dashboard.php");
                exit();
            } else {
                echo "Rôle inconnu.";
            }
        } else {
            echo "<div class='error'>Mot de passe incorrect.</div>";
        }
    } else {
        echo "<div class='error'>Utilisateur introuvable.</div>";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap');
        
        * {
            font-family: "Ubuntu", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(to right, #01022d, #000);
            padding: 10px;
        }
        .container {
            width: 100%;
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
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input {
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            text-align: center;
        }
        input[type="submit"] {
            background-color: deeppink;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        input[type="submit"]:hover {
            background-color: white;
            color: deeppink;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Connexion</h2>
        <form method="POST" >
            <input type="text" name="username" required placeholder="Nom d'utilisateur">
            <input type="password" name="pass" required placeholder="Mot de passe">
            <input type="submit" value="Se connecter">
        </form>
       
    </div>
</body>
</html>
