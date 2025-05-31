<?php
include 'configress.php';
if (!isset($conn)) {
    echo "Erreur : la connexion à la base de données n'est pas définie.";
    exit();
}

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header("Location: connectress.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM utilisateur WHERE iduser = ?");
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    $stmt->close();
    header('Location: gestion_utilisateurs.php');
    exit();
}

$result = $conn->query("SELECT iduser, username, userwilaya, role FROM utilisateur ORDER BY iduser ASC");
$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $result->free();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gérer les utilisateurs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
       @import url('https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap');

body {
    min-height: 100vh;
    background-image: linear-gradient(to right, #01022d, #000);
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 10px;
    color: white;
    font-family: "Ubuntu", sans-serif;
    margin: 0;
}

.container-custom {
    width: 95%;
    max-width: 1200px;
    min-height: 80vh;
    background-image: linear-gradient(to right, #01022de5 30%, #00000050), url('background.jpg');
    background-size: cover;
    background-position: center;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    box-shadow: 0 0 15px rgba(0,0,0,0.5);
}

header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
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
    font-size: 2.5rem;
    font-weight: 700;
}

.dashboard-content p {
    font-size: 1.1rem;
    margin-top: 15px;
}

.btn-custom {
    background-color: deeppink;
    color: white;
    font-size: 1rem;
    padding: 12px 25px;
    border-radius: 50px;
    border: none;
    margin: 10px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-custom:hover {
    background-color: transparent;
    border: 2px solid deeppink;
    color: deeppink;
}

/* Tableau style pour gestion_utilisateurs */
.table-container {
    overflow-x: auto;
}

.table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
}

.table thead th {
    white-space: nowrap;
}

/* Media Queries */
@media (max-width: 768px) {
    .dashboard-content h1 {
        font-size: 2rem;
    }

    .dashboard-content p {
        font-size: 1rem;
    }

    .btn-custom {
        padding: 10px 20px;
        font-size: 0.95rem;
        margin: 5px;
    }

    header {
        flex-direction: column;
        align-items: flex-start;
    }

    header a {
        font-size: 1.5rem;
    }

    .container-custom {
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .dashboard-content h1 {
        font-size: 1.8rem;
    }

    .dashboard-content p {
        font-size: 0.95rem;
    }

    .btn-custom {
        font-size: 0.9rem;
        padding: 8px 18px;
    }

    .table thead {
        display: none;
    }

    .table, .table tbody, .table tr, .table td {
        display: block;
        width: 100%;
    }

    .table tr {
        margin-bottom: 15px;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        background-color: #212529;
        padding: 10px;
    }

    .table td {
        text-align: right;
        padding-left: 50%;
        position: relative;
    }

    .table td::before {
        content: attr(data-label);
        position: absolute;
        left: 15px;
        width: 45%;
        padding-right: 10px;
        white-space: nowrap;
        text-align: left;
        font-weight: bold;
        color: #dee2e6;
    }
}

    </style>
</head>
<body>
    <div class="container-custom">
        <header>
            <a href="#">Barid Admin</a>
            <a href="logout.php">Déconnexion</a>
        </header>
        <div class="mt-4 d-flex justify-content-between align-items-center">
            <h2>Gestion des utilisateurs</h2>
            <div>
                <a href="dashboard_admin.php" class="btn btn-custom">Retour</a>
                <a href="ajouter_utilisateur.php" class="btn btn-custom">Ajouter un utilisateur</a>
            </div>
        </div>
        <table class="table table-striped table-dark mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom d'utilisateur</th>
                    <th>Wilaya</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['iduser'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($user['userwilaya'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="modifier_utilisateur.php?id=<?= $user['iduser'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                                <a href="gestion_utilisateurs.php?delete_id=<?= $user['iduser'] ?>"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');"
                                   class="btn btn-sm btn-danger">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Aucun utilisateur trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
