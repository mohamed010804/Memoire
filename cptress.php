<?php

require 'configress.php';
session_start();

$date_today = date("Y-m-d");

$sql = "SELECT c.tracking, e.nom_expediteur, d.nom_destinataire, c.CR, c.livraison 
        FROM colis c
        JOIN expediteur e ON c.num_expediteur = e.num_expediteur
        JOIN destinataire d ON c.num_destinataire = d.num_destinataire
        LEFT JOIN colis_remorque cr ON c.tracking = cr.tracking
        WHERE c.etat = 'Délivré' AND DATE(cr.date_arrive) = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date_today);
$stmt->execute();
$result = $stmt->get_result();
$colis = $result->fetch_all(MYSQLI_ASSOC);

$total_CR = 0;
$total_livraison = 0;
foreach ($colis as $colisItem) {
    $total_CR += $colisItem['CR'];
    $total_livraison += $colisItem['livraison'];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="mahmoud">
    <title>Bilan Fin de Journée</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to right, #01022d, #000);
            color: white;
            font-family: Arial, sans-serif;
            min-height: 100vh;
        }
        .container-custom {
            background: linear-gradient(to right, #01022de5 30%, #00000050);
            padding: 20px;
            border-radius: 10px;
        }
        .table-responsive {
            max-height: 500px;
            overflow-x: auto;
        }
        table {
            background-color: white;
            color: black;
        }
        th, td {
            text-align: center;
        }
        .navbar-brand {
            font-size: 1.8rem;
            font-weight: bold;
        }
        .navbar {
            background: transparent;
        }
        .navbar-nav .nav-link:hover {
    
        color:deeppink; 
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark  fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="connectress.php">Barid</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="expidress.php">Enregistrement</a></li>
                    <li class="nav-item"><a class="nav-link" href="suivress.php">Suivi</a></li>
                    <li class="nav-item"><a class="nav-link" href="arvress.php">validation</a></li>
                    <li class="nav-item"><a class="nav-link" href="cptress.php">Montant</a></li>
                    <li class="nav-item"><a class="nav-link" href="gererclient.php">gestion_enregistrement</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <div class="container-custom p-4">
            <h2 class="text-center my-4">Bilan Fin de Journée - <?= date("d/m/Y") ?></h2>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Tracking</th>
                            <th>Expéditeur</th>
                            <th>Destinataire</th>
                            <th>Contre Remboursement (DA)</th>
                            <th>Frais Livraison (DA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($colis) > 0): ?>
                            <?php foreach ($colis as $colisItem): ?>
                                <tr>
                                    <td><?= htmlspecialchars($colisItem['tracking']) ?></td>
                                    <td><?= htmlspecialchars($colisItem['nom_expediteur']) ?></td>
                                    <td><?= htmlspecialchars($colisItem['nom_destinataire']) ?></td>
                                    <td><?= number_format($colisItem['CR'], 2) ?> DA</td>
                                    <td><?= number_format($colisItem['livraison'], 2) ?> DA</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5">Aucun colis livré aujourd'hui.</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-warning">
                            <th colspan="3">Total</th>
                            <th><?= number_format($total_CR, 2) ?> DA</th>
                            <th><?= number_format($total_livraison, 2) ?> DA</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>