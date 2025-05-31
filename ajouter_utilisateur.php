<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header("Location: connectress.php");
    exit();
}
include 'configress.php';
$erreur = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username   = trim($_POST['username']);
    $userwilaya = trim($_POST['userwilaya']);
    $role       = trim($_POST['role']);
    $password   = trim($_POST['password']);

    if (empty($username) || empty($userwilaya) || empty($role) || empty($password)) {
        $erreur = "Tous les champs sont obligatoires.";
    } else {
        $stmt = $conn->prepare("INSERT INTO utilisateur (username, userwilaya, role, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $userwilaya, $role, $password);

        if ($stmt->execute()) {
            $success = "Utilisateur ajouté avec succès.";
        } else {
            $erreur = "Erreur : " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un utilisateur</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap');
        body {
            height: 100vh;
            margin: 0;
            background-image: linear-gradient(to right, #01022d, #000);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            font-family: "Ubuntu", sans-serif;
            color: white;
        }
        .container-custom {
            width: 90%;
            max-width: 700px;
            background-image: linear-gradient(to right, #01022de5 30%, #00000080), url('background.jpg');
            background-size: cover;
            background-position: center;
            border-radius: 10px;
            padding: 40px;
        }
        .form-label {
            font-weight: 500;
        }
        .btn-custom {
            background-color: deeppink;
            color: white;
            border-radius: 50px;
            padding: 10px 25px;
            border: none;
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
    <h2 class="text-center mb-4">Ajouter un nouvel utilisateur</h2>

    <?php if ($erreur): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="mb-3">
            <label for="username" class="form-label">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="userwilaya" class="form-label">Wilaya</label>
            <input type="text" id="userwilaya" name="userwilaya" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Rôle</label>
            <select id="role" name="role" class="form-select" required>
                <option value="">-- Sélectionner un rôle --</option>
                <option value="administrateur">Administrateur</option>
                <option value="agent">Agent</option>
                <option value="remorque">Conducteur</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="text" id="password" name="password" class="form-control" required>
        </div>
        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-custom">Ajouter</button>
            <a href="gestion_utilisateurs.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
