<?php
include 'configress.php';
session_start();

if (!isset($_SESSION['userwilaya'])) {
    header("Location: connect.php");
    exit();
}

$message = '';
$colis = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recherche par tracking
    if (isset($_POST['search'])) {
        $tracking = intval($_POST['tracking']);
        $sql = "SELECT c.tracking as tracking,
                       c.type_colis, c.quantite, c.type_emballage, c.CR, c.livraison, c.etat,
                       e.num_expediteur, e.nom_expediteur, e.tel_expediteur, e.lieu_expediteur,
                       d.num_destinataire, d.nom_destinataire, d.tel_destinataire, d.lieu_destinataire
                FROM colis c
                JOIN expediteur e ON c.num_expediteur = e.num_expediteur
                JOIN destinataire d ON c.num_destinataire = d.num_destinataire
                WHERE c.tracking = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $tracking);
        $stmt->execute();
        $result = $stmt->get_result();
        $colis = $result->fetch_assoc();
        if (!$colis) {
            $message = "Aucun colis trouvé pour le tracking $tracking";
        }
        $stmt->close();
    }
    // Mise à jour
    elseif (isset($_POST['update'])) {
        $tracking = intval($_POST['tracking']);
        // Préparer les variables pour bind_param
        $type_colis     = trim($_POST['type_colis']);
        $quantite       = intval($_POST['quantite']);
        $type_emballage = trim($_POST['type_emballage']);
        $CR             = floatval($_POST['CR']);
        $livraison      = floatval($_POST['livraison']);
        $etat           = trim($_POST['etat']);
        try {
            $conn->begin_transaction();

            // Mise à jour de l'expéditeur
            $stmt = $conn->prepare(
                "UPDATE expediteur SET nom_expediteur = ?, tel_expediteur = ?, lieu_expediteur = ? WHERE num_expediteur = ?"
            );
            $nom_expediteur = trim($_POST['nom_expediteur']);
            $tel_expediteur = trim($_POST['tel_expediteur']);
            $lieu_expediteur= trim($_POST['lieu_expediteur']);
            $num_exped      = trim($_POST['num_expediteur']);
            $stmt->bind_param("ssss", $nom_expediteur, $tel_expediteur, $lieu_expediteur, $num_exped);
            $stmt->execute();
            $stmt->close();

            // Mise à jour du destinataire
            $stmt = $conn->prepare(
                "UPDATE destinataire SET nom_destinataire = ?, tel_destinataire = ?, lieu_destinataire = ? WHERE num_destinataire = ?"
            );
            $nom_dest      = trim($_POST['nom_destinataire']);
            $tel_dest      = trim($_POST['tel_destinataire']);
            $lieu_dest     = trim($_POST['lieu_destinataire']);
            $num_dest      = trim($_POST['num_destinataire']);
            $stmt->bind_param("ssss", $nom_dest, $tel_dest, $lieu_dest, $num_dest);
            $stmt->execute();
            $stmt->close();

            // Mise à jour du colis
            $stmt = $conn->prepare(
                "UPDATE colis SET type_colis = ?, quantite = ?, type_emballage = ?, CR = ?, livraison = ?, etat = ? WHERE tracking = ?"
            );
            $stmt->bind_param("sisddsi", $type_colis, $quantite, $type_emballage, $CR, $livraison, $etat, $tracking);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $message = "Mise à jour réussie pour le tracking $tracking";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Erreur de mise à jour : " . $e->getMessage();
        }
    }
    // Suppression
    elseif (isset($_POST['delete'])) {
        $tracking = intval($_POST['tracking']);
        try {
            $conn->begin_transaction();
            $stmt = $conn->prepare("DELETE FROM colis WHERE tracking = ?");
            $stmt->bind_param("i", $tracking);
            $stmt->execute();
            $stmt->close();
            $conn->commit();
            $message = "Colis tracking $tracking supprimé.";
            $colis = null;
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Erreur de suppression : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gérer Clients et Colis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap');
        body {
            font-family: "Ubuntu", sans-serif;
            min-height: 100vh;
            background: linear-gradient(to right, #01022d, #000);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
            color: white;
        }
        .container-custom {
            width: 90%;
            max-width: 1000px;
            background: linear-gradient(to right, #01022de5 30%, #00000050), url('Designer.png');
            background-size: cover;
            background-position: center;
            padding: 30px;
            border-radius: 10px;
            color: white;
            margin-top: 20px;
        }
        .container-custom h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 28px;
        }
        .form-control {
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            font-size: 16px;
            border: none;
            padding: 12px;
            margin-bottom: 10px;
        }
        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 0 0 0.2rem rgba(255, 20, 147, 0.25);
            border-color: deeppink;
        }
        .btn {
            border-radius: 30px;
            font-weight: 700;
            padding: 12px 30px;
            font-size: 16px;
        }
        .btn-primary {
            background-color: deeppink;
            border-color: deeppink;
        }
        .btn-primary:hover {
            background-color: #ff1493;
            border-color: #ff1493;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .navbar {
            width: 100%;
            background: transparent;
        }
        .navbar-brand {
            color: white;
            font-size: 28px;
            font-weight: bold;
        }
        .navbar-nav .nav-link {
            color: white;
            font-size: 18px;
            font-weight: 700;
        }
        .navbar-toggler {
            border-color: white;
        }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.85%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        .navbar-nav .nav-link:hover {
            color: deeppink;
        }
        .alert-info {
            background-color: rgba(23, 162, 184, 0.8);
            border-color: #17a2b8;
            color: white;
            border-radius: 15px;
            text-align: center;
            font-weight: 500;
        }
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: deeppink;
            margin-bottom: 15px;
            text-align: center;
        }
        .search-section {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .edit-section {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 25px;
            border-radius: 15px;
        }
        @media (max-width: 768px) {
            .form-control {
                font-size: 14px;
                padding: 10px;
            }
            .btn {
                padding: 10px 20px;
                font-size: 14px;
            }
            .container-custom {
                padding: 20px;
            }
            .navbar-brand {
                font-size: 24px;
            }
            .navbar-nav .nav-link {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="connectress.php">Barid</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="expidress.php">Enregistrement</a></li>
                    <li class="nav-item"><a class="nav-link" href="suivress.php">Suivi</a></li>
                    <li class="nav-item"><a class="nav-link" href="arvress.php">Validation</a></li>
                    <li class="nav-item"><a class="nav-link" href="cptress.php">Compter Montant</a></li>
                    <li class="nav-item"><a class="nav-link" href="gererclient.php">Gestion_enregistrement</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-custom">
        <h2>Gérer Clients et Colis</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Section de recherche -->
        <div class="search-section">
            <h5 class="section-title">Rechercher un Colis</h5>
            <form class="row g-3" method="post">
                <div class="col-md-8">
                    <input type="number" name="tracking" class="form-control" placeholder="Entrez le numéro de tracking" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" name="search" class="btn btn-primary w-100">Rechercher</button>
                </div>
            </form>
        </div>

        <?php if ($colis): ?>
        <!-- Section de modification -->
        <div class="edit-section">
            <h5 class="section-title">Modifier les Informations</h5>
            <form method="post" class="row g-3">
                <input type="hidden" name="tracking" value="<?= htmlspecialchars($colis['tracking']) ?>">
                
                <!-- Expéditeur -->
                <div class="col-12">
                    <h6 style="color: #ffc107; font-weight: 600; margin-bottom: 15px;">Informations de l'Expéditeur</h6>
                </div>
                <input type="hidden" name="num_expediteur" value="<?= htmlspecialchars($colis['num_expediteur']) ?>">
                <div class="col-md-4">
                    <input type="text" name="nom_expediteur" value="<?= htmlspecialchars($colis['nom_expediteur']) ?>" class="form-control" placeholder="Nom expéditeur">
                </div>
                <div class="col-md-4">
                    <input type="text" name="tel_expediteur" value="<?= htmlspecialchars($colis['tel_expediteur']) ?>" class="form-control" placeholder="Téléphone expéditeur">
                </div>
                <div class="col-md-4">
                    <input type="text" name="lieu_expediteur" value="<?= htmlspecialchars($colis['lieu_expediteur']) ?>" class="form-control" placeholder="Lieu expéditeur">
                </div>

                <!-- Destinataire -->
                <div class="col-12 mt-4">
                    <h6 style="color: #ffc107; font-weight: 600; margin-bottom: 15px;">Informations du Destinataire</h6>
                </div>
                <input type="hidden" name="num_destinataire" value="<?= htmlspecialchars($colis['num_destinataire']) ?>">
                <div class="col-md-4">
                    <input type="text" name="nom_destinataire" value="<?= htmlspecialchars($colis['nom_destinataire']) ?>" class="form-control" placeholder="Nom destinataire">
                </div>
                <div class="col-md-4">
                    <input type="text" name="tel_destinataire" value="<?= htmlspecialchars($colis['tel_destinataire']) ?>" class="form-control" placeholder="Téléphone destinataire">
                </div>
                <div class="col-md-4">
                    <input type="text" name="lieu_destinataire" value="<?= htmlspecialchars($colis['lieu_destinataire']) ?>" class="form-control" placeholder="Lieu destinataire">
                </div>

                <!-- Colis -->
                <div class="col-12 mt-4">
                    <h6 style="color: #ffc107; font-weight: 600; margin-bottom: 15px;">Informations du Colis</h6>
                </div>
                <div class="col-md-4">
                    <input type="text" name="type_colis" value="<?= htmlspecialchars($colis['type_colis']) ?>" class="form-control" placeholder="Type de colis">
                </div>
                <div class="col-md-4">
                    <input type="number" name="quantite" value="<?= htmlspecialchars($colis['quantite']) ?>" class="form-control" placeholder="Quantité">
                </div>
                <div class="col-md-4">
                    <input type="text" name="type_emballage" value="<?= htmlspecialchars($colis['type_emballage']) ?>" class="form-control" placeholder="Type d'emballage">
                </div>
                <div class="col-md-4">
                    <input type="number" name="CR" value="<?= htmlspecialchars($colis['CR']) ?>" step="0.01" class="form-control" placeholder="CR">
                </div>
                <div class="col-md-4">
                    <input type="number" name="livraison" value="<?= htmlspecialchars($colis['livraison']) ?>" step="0.01" class="form-control" placeholder="Frais de livraison">
                </div>
                <div class="col-md-4">
                    <select name="etat" class="form-control">
                        <option value="En transit" <?= $colis['etat']==='En transit' ? 'selected' : '' ?>>En transit</option>
                        <option value="Délivré" <?= $colis['etat']==='Délivré' ? 'selected' : '' ?>>Délivré</option>
                        <option value="Retour" <?= $colis['etat']==='Retour' ? 'selected' : '' ?>>Retour</option>
                        <option value="Annulé" <?= $colis['etat']==='Annulé' ? 'selected' : '' ?>>Annulé</option>
                    </select>
                </div>
                
                <div class="col-12 text-center mt-4">
                    <button type="submit" name="update" class="btn btn-success me-3">Mettre à jour</button>
                    <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Confirmer suppression ?');">Supprimer</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>