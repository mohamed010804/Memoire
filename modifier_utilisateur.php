<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header("Location: connectress.php");
    exit();
}

include 'configress.php';
if (!isset($conn)) {
    echo "Erreur : la connexion à la base de données n'est pas définie.";
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestion_utilisateurs.php");
    exit();
}

$id = intval($_GET['id']);
$erreur = '';
$success = '';

// Mise à jour utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $userwilaya = trim($_POST['userwilaya']);
    $role = trim($_POST['role']);

    if (empty($username) || empty($userwilaya) || empty($role)) {
        $erreur = "Tous les champs sont obligatoires.";
    } else {
        $stmt = $conn->prepare("UPDATE utilisateur SET username = ?, userwilaya = ?, role = ? WHERE iduser = ?");
        $stmt->bind_param("sssi", $username, $userwilaya, $role, $id);
        if ($stmt->execute()) {
            $success = "Utilisateur mis à jour avec succès.";
        } else {
            $erreur = "Erreur lors de la mise à jour : " . $stmt->error;
        }
        $stmt->close();
    }
}

// Récupération de l'utilisateur
$stmt = $conn->prepare("SELECT username, userwilaya, role FROM utilisateur WHERE iduser = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Utilisateur introuvable.";
    exit();
}
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur</title>
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
            color: white;
            font-family: "Ubuntu", sans-serif;
        }
        .container-custom {
            width: 90%;
            max-width: 600px;
            background-image: linear-gradient(to right, #01022de5 30%, #00000050), url('background.jpg');
            background-size: cover;
            background-position: center;
            border-radius: 10px;
            padding: 30px;
        }
        .btn-custom {
            background-color: deeppink;
            color: white;
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 50px;
            border: none;
        }
        .btn-custom:hover {
            background-color: transparent;
            border: 2px solid deeppink;
            color: deeppink;
        }
        label {
            font-weight: 500;
        }
        .alert {
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <div class="container-custom">
        <h2 class="mb-4 text-center">Modifier l'utilisateur</h2>

        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur</label>
                <input type="text" class="form-control" id="username" name="username" required
                       value="<?= htmlspecialchars($user['username']) ?>">
            </div>
            <div class="mb-3">
                <label for="userwilaya" class="form-label">Wilaya</label>
                <input type="text" class="form-control" id="userwilaya" name="userwilaya" required
                       value="<?= htmlspecialchars($user['userwilaya']) ?>">
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Rôle</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="agents" <?= $user['role'] === 'agents' ? 'selected' : '' ?>>Agents</option>
                    <option value="administrateur" <?= $user['role'] === 'administrateur' ? 'selected' : '' ?>>Conducteur</option>
                    <option value="remorque" <?= $user['role'] === 'remorque' ? 'selected' : '' ?>>Remorque</option>
                </select>
            </div>
            <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-custom">Mettre à jour</button>
                <a href="gestion_utilisateurs.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</body>
</html>
