<?php
include 'configress.php';
session_start();

if (!isset($_SESSION['userwilaya'])) {
    header("Location: connect.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->begin_transaction(); // Début de la transaction

        // Récupération et nettoyage des données
        $num_expediteur   = trim($_POST['num_expediteur']);
        $nom_expediteur   = trim($_POST['nom_expediteur']);
        $tel_expediteur   = trim($_POST['tel_expediteur']);
        $lieu_expediteur  = trim($_POST['lieu_expediteur']);

        $num_destinataire = trim($_POST['num_destinataire']);
        $nom_destinataire = trim($_POST['nom_destinataire']);
        $tel_destinataire = trim($_POST['tel_destinataire']);
        $lieu_destinataire= trim($_POST['lieu_destinataire']);

        $type_colis      = trim($_POST['type_colis']);
        $quantite        = intval($_POST['quantite']);
        $type_emballage  = trim($_POST['type_emballage']);
        $CR              = floatval($_POST['CR']);
        $livraison       = floatval($_POST['livraison']);
        $etat            = "En transit";

        // Insertion ou mise à jour de l'expéditeur
        $sql_expediteur = "INSERT INTO expediteur (num_expediteur, nom_expediteur, tel_expediteur, lieu_expediteur)
                            VALUES (?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE
                            nom_expediteur = VALUES(nom_expediteur),
                            tel_expediteur = VALUES(tel_expediteur),
                            lieu_expediteur = VALUES(lieu_expediteur)";
        $stmt = $conn->prepare($sql_expediteur);
        $stmt->bind_param("ssss", $num_expediteur, $nom_expediteur, $tel_expediteur, $lieu_expediteur);
        $stmt->execute();
        $stmt->close();

        // Insertion ou mise à jour du destinataire
        $sql_destinataire = "INSERT INTO destinataire (num_destinataire, nom_destinataire, tel_destinataire, lieu_destinataire)
                               VALUES (?, ?, ?, ?)
                               ON DUPLICATE KEY UPDATE
                               nom_destinataire = VALUES(nom_destinataire),
                               tel_destinataire = VALUES(tel_destinataire),
                               lieu_destinataire = VALUES(lieu_destinataire)";
        $stmt = $conn->prepare($sql_destinataire);
        $stmt->bind_param("ssss", $num_destinataire, $nom_destinataire, $tel_destinataire, $lieu_destinataire);
        $stmt->execute();
        $stmt->close();

        // Insertion du colis
        $sql_colis = "INSERT INTO colis (num_expediteur, num_destinataire, type_colis, quantite, type_emballage, CR, livraison, etat)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_colis);
        $stmt->bind_param("sssisdds", $num_expediteur, $num_destinataire, $type_colis, $quantite, $type_emballage, $CR, $livraison, $etat);

        if ($stmt->execute()) {
            $tracking = $conn->insert_id;
            $stmt->close();
            $conn->commit(); // Validation de la transaction
            echo json_encode(["success" => true, "tracking" => $tracking]);
        } else {
            throw new Exception("Erreur lors de l'insertion du colis.");
        }
    } catch (Exception $e) {
        $conn->rollback(); // Annulation de la transaction en cas d'erreur
        echo json_encode(["error" => $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Expédition Colis</title>
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
        }
        .container-custom {
            width: 90%;
            max-width: 900px;
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
            margin-bottom: 20px;
        }
        .form-control {
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            font-size: 16px;
        }
        #savePDF {
            background-color: deeppink;
            border-radius: 30px;
            color: white;
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
        .navbar-nav .nav-link:hover {
            color: deeppink;
        }
        .btn-login {
            background-color: white;
            color: #01022d;
            font-weight: bold;
            border-radius: 20px;
            padding: 5px 15px;
        }
        @media (max-width: 768px) {
            .form-control {
                font-size: 14px;
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
        <h2>Départ des Colis</h2>
        <form class="row g-3" method="post" id="expeditionForm">
            <div class="col-md-6"><input type="text" class="form-control" placeholder="Numéro carte expéditeur" required name="num_expediteur"></div>
            <div class="col-md-6"><input type="text" class="form-control" placeholder="Nom expéditeur" required name="nom_expediteur"></div>
            <div class="col-md-6"><input type="text" class="form-control" placeholder="Téléphone expéditeur" required name="tel_expediteur"></div>
            <div class="col-md-6"><input type="text" class="form-control" placeholder="Lieu expéditeur" required name="lieu_expediteur"></div>

            <div class="col-md-6"><input type="text" class="form-control" placeholder="Numéro carte destinataire" required name="num_destinataire"></div>
            <div class="col-md-6"><input type="text" class="form-control" placeholder="Nom destinataire" required name="nom_destinataire"></div>
            <div class="col-md-6"><input type="text" class="form-control" placeholder="Téléphone destinataire" required name="tel_destinataire"></div>
            <div class="col-md-6"><input type="text" class="form-control" placeholder="Lieu destinataire" required name="lieu_destinataire"></div>

            <div class="col-md-6"><input type="text" class="form-control" placeholder="Type de colis" required name="type_colis"></div>
            <div class="col-md-6"><input type="number" class="form-control" placeholder="Quantité" required name="quantite"></div>
            <div class="col-md-6"><input type="text" class="form-control" placeholder="Type d'emballage" required name="type_emballage"></div>
            <div class="col-md-6"><input type="number" class="form-control" placeholder="CR" required name="CR"></div>
            <div class="col-12 d-flex justify-content-center">
                <input type="number" class="form-control w-50 mx-auto" placeholder="Frais de livraison" required name="livraison">
            </div>
            <div class="col-12 text-center">
                <button type="submit" id="savePDF" class="btn btn-lg">Enregistrer</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    <script>
    document.getElementById("savePDF").addEventListener("click", async function (event) {
        event.preventDefault();

        const formData = new FormData(document.getElementById("expeditionForm"));

        try {
            let response = await fetch("expidress.php", { method: "POST", body: formData });
            if (!response.ok) throw new Error("Problème de connexion au serveur.");
            let result = await response.json();
            if (result.success) {
                const tracking = result.tracking;
                let qr = new QRious({ value: tracking.toString(), size: 100 });
                let qrDataURL = qr.toDataURL();
                const { jsPDF } = window.jspdf;
                let doc = new jsPDF();
                doc.setFont("helvetica", "bold"); doc.setFontSize(16); doc.text("Informations du Colis", 20, 20);
                doc.setFont("helvetica", "normal"); doc.setFontSize(12);
                const fields = [
                    `Tracking: ${tracking}`,
                    `Expéditeur: ${formData.get("nom_expediteur")} (${formData.get("tel_expediteur")})`,
                    `Lieu Expéditeur: ${formData.get("lieu_expediteur")}`
                ];
                fields.push(
                    `Destinataire: ${formData.get("nom_destinataire")} (${formData.get("tel_destinataire")})`,
                    `Lieu Destinataire: ${formData.get("lieu_destinataire")}`,
                    `Type de Colis: ${formData.get("type_colis")}`,
                    `Quantité: ${formData.get("quantite")}`,
                    `CR: ${formData.get("CR")} DA`,
                    `Livraison: ${formData.get("livraison")} DA`,
                    `État: En transit`,
                    `Date de Création: ${new Date().toLocaleDateString()}`
                );
                let y = 30;
                fields.forEach(text => { doc.text(text, 20, y); y += 10; });
                doc.addImage(qrDataURL, "PNG", 150, 20, 40, 40);
                doc.save(`Colis_${tracking}.pdf`);
            } else {
                alert("Erreur: " + (result.error || "Impossible d'insérer le colis."));
            }
        } catch (error) {
            console.error("Erreur:", error);
            alert("Une erreur s'est produite: " + error.message);
        }
    });
    </script>
</body>
</html>