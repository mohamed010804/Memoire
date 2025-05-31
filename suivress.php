<?php

include 'configress.php';
session_start();

$tracking_info = "";
$colis_list = [];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search = trim($_POST['search']);

    if (!empty($search)) {
        $stmt = $conn->prepare("
             SELECT c.tracking, c.type_colis, c.quantite, c.type_emballage, c.CR, c.livraison, c.etat,
           cr.date_depart, cr.date_arrive, cr.ville_depart, cr.ville_arrive,
           r.nom AS conducteur_nom, r.prenom AS conducteur_prenom,
           e.nom_expediteur, e.tel_expediteur, e.lieu_expediteur,
           d.nom_destinataire, d.tel_destinataire, d.lieu_destinataire
            FROM colis c
            LEFT JOIN colis_remorque cr ON c.tracking = cr.tracking
            LEFT JOIN remorque r ON cr.num_conducteur = r.num_conducteur
            LEFT JOIN expediteur e ON c.num_expediteur = e.num_expediteur
            LEFT JOIN destinataire d ON c.num_destinataire = d.num_destinataire
            WHERE c.tracking = ? OR e.tel_expediteur = ? OR d.tel_destinataire = ?
                    ORDER BY c.date_creation DESC
        ");
        $stmt->bind_param("sss", $search, $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $tracking_info = "Résultats trouvés pour : $search";
            $colis_list = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            header("Location: suivress.php");
            exit();
        }
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tracking_number'])) {
    $tracking_number = $_POST['tracking_number'];

    if (isset($_POST['valider'])) {
        $stmt = $conn->prepare("UPDATE colis SET etat = 'Délivré' WHERE tracking = ?");
    } elseif (isset($_POST['retour'])) {
        $stmt = $conn->prepare("UPDATE colis SET etat = 'Retour' WHERE tracking = ?");
    }

    $stmt->bind_param("s", $tracking_number);
    $stmt->execute();
    header("Location: suivres.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi de Colis</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator/qrcode.js"></script>
    <style>
        body {
            background: linear-gradient(to right, #01022d, #000);
            color: white;
            min-height: 100vh;
        }
        .container-custom {
            background: linear-gradient(to right, #01022de5 30%, #00000050);
            background-size: cover;
            background-position: center;
            padding: 20px;
            border-radius: 10px;
        }
        .table-responsive {
            max-height: 500px;
            overflow-x: auto;
          
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
                    <li class="nav-item"><a class="nav-link" href="arvress.php">Validation</a></li>
                    <li class="nav-item"><a class="nav-link" href="cptress.php">Montant</a></li>
                     <li class="nav-item"><a class="nav-link" href="gererclient.php">gestion_enregistrement</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-5 pt-5">
        <div class="container-custom p-4">
            <h2 class="text-center mb-4">Suivi de votre colis</h2>
            <form method="POST" class="text-center mb-3">
                <div class="input-group w-50 mx-auto">
                    <input type="text" name="search" class="form-control" placeholder="Numéro de suivi ou téléphone" required>
                    <button class="btn btn-primary" type="submit">Rechercher</button>
                </div>
            </form>
            <?php if (!empty($tracking_info)): ?>
            <p class="info"><?php echo $tracking_info; ?></p>
            <?php endif; ?>

        <?php if (!empty($colis_list)): ?>
            
            <div class="table-responsive">
                <table class="table table-dark table-striped table-hover text-center">
                    <thead>
                        <tr>
                            <th>Tracking</th>
                            <th> Expéditeur</th>
                            <th>Destinataire</th>
                            <th>Ville Arrivée</th>
                            <th>Date Arrivée</th>
                            <th>Ville Départ</th>
                            <th>Date Départ</th>
                            <th>Type</th>
                            <th>Quantité</th>
                            <th>Emballage</th>
                            <th>CR</th>
                            <th>Livraison</th>
                            <th>État</th>
                            <th>Conducteur</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($colis_list as $colis): ?>
                <tr>
                    <td><?php echo htmlspecialchars($colis['tracking']); ?></td>
                    <td><?php echo $colis['nom_expediteur'] . " (" . $colis['tel_expediteur'] . ") - " . $colis['lieu_expediteur']; ?></td>
                    <td><?php echo $colis['nom_destinataire'] . " (" . $colis['tel_destinataire'] . ") - " . $colis['lieu_destinataire'];?></td>
                    <td><?php echo $colis['ville_arrive']; ?></td>
                    <td><?php echo $colis['date_arrive']; ?></td>
                    <td><?php echo $colis['ville_depart']; ?></td>
                    <td><?php echo $colis['date_depart']; ?></td>
                    <td><?php echo $colis['type_colis']; ?></td>
                    <td><?php echo $colis['quantite']; ?></td>
                    <td><?php echo $colis['type_emballage']; ?></td>
                    <td><?php echo $colis['CR']; ?></td>
                    <td><?php echo $colis['livraison']; ?></td>
                    <td><?php echo$colis['etat']; ?></td>
                    <td><?php echo $colis['conducteur_nom'] . ' ' . $colis['conducteur_prenom']; ?></td>
                    <td>
                    <form method="POST">
                        <input type="hidden" name="tracking_number" value="<?php echo $colis['tracking']; ?>">
                        <?php
                        $enable_actions = ($colis['ville_arrive'] == $colis['lieu_destinataire'] && $colis['etat'] != 'Délivré' && $colis['etat'] != 'Retour');
                        $delivre_disabled = !$enable_actions;
                        $retour_disabled = !$enable_actions;

                        if ($colis['etat'] == 'Délivré' || $colis['etat'] == 'Retour') {
                            $delivre_disabled = true;
                            $retour_disabled = true;
                        }
                        ?>
                        <button type="submit" name="valider" <?php echo $delivre_disabled ? 'disabled' : ''; ?>>✅ Valider</button>
                        <button type="submit" name="retour" <?php echo $retour_disabled ? 'disabled' : ''; ?> onclick="generatePDF(<?php echo htmlspecialchars(json_encode($colis)); ?>)">↩️ Retour</button>
                    </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                    </tbody>
                </table>
        <?php endif; ?>
            </div>
        </div>
        <a href="homeress.php" class="btn btn-outline-light mt-3">Retour</a>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      function generatePDF(colis) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Titre "RETOUR"
        doc.setFont("helvetica", "bold");
        doc.setFontSize(16);
        doc.text("RETOUR", 80, 20); // Centré en haut

        doc.setFont("helvetica", "normal");
        doc.setFontSize(12);
        doc.text(`Numéro de suivi : ${colis.tracking}`, 20, 30);
        doc.text(`Expéditeur : ${colis.nom_destinataire} (📞 ${colis.tel_destinataire}) - ${colis.lieu_destinataire}`, 20, 40);
        doc.text(`Destinataire : ${colis.nom_expediteur} (📞 ${colis.tel_expediteur}) - ${colis.lieu_expediteur}`, 20, 50);
        doc.text(`Lieu actuel : ${colis.wilaya}`, 20, 60);
        doc.text(`Date de création : ${colis.date_creation}`, 20, 70);
        doc.text(`Type de colis : ${colis.type_colis}`, 20, 80);
        doc.text(`Quantité : ${colis.quantite}`, 20, 90);
        doc.text(`CR : ${colis.CR} DA`, 20, 100);
        doc.text(`Prix de livraison : ${colis.livraison} DA`, 20, 110);
        doc.text(`État : ${colis.etat}`, 20, 120);

        // Génération du QR Code
        let qr = qrcode(0, 'L');
        qr.addData(colis.tracking);
        qr.make();
        
        let qrImage = qr.createDataURL(); // Convertir le QR en image

        // Ajout du QR Code en haut à droite
        doc.addImage(qrImage, 'PNG', 150, 70, 40, 40);

        doc.save(`Retour_${colis.tracking}.pdf`);
    }

    </script>
</body>
</html>
