<?php
// ----------------------------
// Fichier : arrival_depart_validation.php
// ----------------------------

include 'configress.php';
session_start();

// Vérification de la session
if (!isset($_SESSION['userwilaya'])) {
    header("Location: connect.php");
    exit();
}

$date_today = date("Y-m-d H:i:s");
$current_date = date("Y-m-d");
$userWilaya = $_SESSION['userwilaya'];
$message    = "";

// Récupération de la liste des conducteurs pour le <select>
$sql_conducteurs = "SELECT num_conducteur, nom FROM remorque";
$result_conducteurs = $conn->query($sql_conducteurs);
$conducteurs = $result_conducteurs->fetch_all(MYSQLI_ASSOC);

/**
 * Fonction utilitaire : retourne true si une ligne existe déjà dans colis_remorque pour ce tracking
 */
function existe_colis_remorque($conn, $tracking) {
    $stmt_chk = $conn->prepare("SELECT 1 FROM colis_remorque WHERE tracking = ?");
    $stmt_chk->bind_param("i", $tracking);
    $stmt_chk->execute();
    $res = $stmt_chk->get_result();
    return ($res->num_rows > 0);
}

/**
 * Fonction utilitaire : récupère le contenu actuel d’une ligne colis_remorque pour ce tracking
 * (notamment pour vérifier si ville_arrive ou ville_depart sont déjà à jour)
 * Retourne un array associatif ou null si pas de ligne.
 */
function get_colis_remorque_row($conn, $tracking) {
    $stmt = $conn->prepare("
        SELECT num_conducteur, ville_depart, date_depart, ville_arrive, date_arrive
        FROM colis_remorque 
        WHERE tracking = ?
    ");
    $stmt->bind_param("i", $tracking);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        return null;
    }
    return $res->fetch_assoc();
}

/**
 * Étape 1 : l'utilisateur saisit un numéro de suivi.
 * On récupère src_wilaya et dst_wilaya, ainsi que l'état courant dans colis_remorque.
 * À la fin, on décide quel formulaire (validation arrivée/départ) afficher.
 */
$showFormStage2   = false;   // vrai si on doit afficher le second formulaire (validation)
$trackingRecherche = null;   // numéro de suivi saisi
$roleWilaya       = "";      // "expediteur", "destinataire", "inter_arr" (arrivée en transit), "inter_dep" (départ en transit)
$rowRemorque      = null;    // ligne existante dans colis_remorque (si existe)
$srcWilaya        = "";
$dstWilaya        = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['tracking_recherche']) && !isset($_POST['action_stage2'])) {
    // L'utilisateur vient juste d'entrer le numéro de suivi (étape 1).
    $trackingRecherche = intval($_POST['tracking_recherche']);
    
    // 1) Récupérer la wilaya source (expéditeur) et la wilaya destination (destinataire) pour ce colis.
    $sql_info = "
        SELECT 
          e.lieu_expediteur AS src_wilaya, 
          d.lieu_destinataire AS dst_wilaya
        FROM colis c
        JOIN expediteur e ON c.num_expediteur = e.num_expediteur
        JOIN destinataire d ON c.num_destinataire = d.num_destinataire
        WHERE c.tracking = ?
    ";
    $stmt_info = $conn->prepare($sql_info);
    $stmt_info->bind_param("i", $trackingRecherche);
    $stmt_info->execute();
    $result_info = $stmt_info->get_result();

    if ($result_info->num_rows === 0) {
        // Numéro de suivi invalide
        $message = "Le numéro de suivi <b>$trackingRecherche</b> n’existe pas.";
    } else {
        $rowInfo   = $result_info->fetch_assoc();
        $srcWilaya = $rowInfo['src_wilaya'];
        $dstWilaya = $rowInfo['dst_wilaya'];

        // Récupérer la ligne existante dans colis_remorque (si elle existe déjà)
        $rowRemorque = get_colis_remorque_row($conn, $trackingRecherche);

        // Déterminer le rôle de la wilaya courante ($userWilaya)
        if ($userWilaya === $srcWilaya) {
            // on est à la wilaya de l’expéditeur → validation DU DEPART
            $roleWilaya = "expediteur";
            $showFormStage2 = true;
        }
        elseif ($userWilaya === $dstWilaya) {
            // on est à la wilaya du destinataire → validation DE L’ARRIVÉE FINALE
            // il faut absolument que la ligne existe déjà (départ enregistré) sinon on bloque.
            if (!$rowRemorque) {
                $message = "Impossible de valider l’arrivée : aucun départ n’a été enregistré pour le suivi <b>$trackingRecherche</b>.";
                $roleWilaya = "";
                $showFormStage2 = false;
            } else {
                $roleWilaya = "destinataire";
                $showFormStage2 = true;
            }
        }
        else {
            // on est dans une wilaya intermédiaire → deux cas possibles :
            if (!$rowRemorque) {
                // Aucune saisie de départ n’a encore eu lieu : on ne peut que valider ARRIVÉE EN TRANSIT (et pas de conducteur)
                $roleWilaya = "inter_arr"; 
                $showFormStage2 = true;
            } else {
                // Il y a déjà une ligne existante. On regarde si ville_arrive est vide ou différente.
                if (is_null($rowRemorque['ville_arrive']) || $rowRemorque['ville_arrive'] !== $userWilaya) {
                    // n’a **pas encore** été marqué comme arrivé ici → valider ARRIVÉE EN TRANSIT (pas de conducteur)
                    $roleWilaya = "inter_arr";
                    $showFormStage2 = true;
                } else {
                    // ville_arrive = wilaya courante => on est dans le cas où l’on passe à la validation du DEPART depuis cette même wilaya intermédiaire
                    $roleWilaya = "inter_dep";
                    $showFormStage2 = true;
                }
            }
        }
    }
}

/**
 * Étape 2 : l’utilisateur valide effectivement l’arrivée ou le départ.
 * On doit avoir :
 *   - un champ caché 'tracking_recherche' (numéro de suivi)
 *   - un champ caché 'roleWilaya' pour savoir ce qu’on valide
 *   - si on valide un départ (role "expediteur" ou "inter_dep"), on doit avoir $_POST['num_conducteur'].
 */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action_stage2'])) {
    // Données reçues
    $trackingRecherche = intval($_POST['tracking_recherche']);
    $roleWilaya        = $_POST['roleWilaya'];
    $num_conducteur    = $_POST['num_conducteur'] ?? null;

    try {
        $conn->begin_transaction();

        // Recharger lignes importantes (au cas où)
        // (on peut aussi se fier au $roleWilaya reçu côté client, mais on recalcule si on veut être sûr)
        $stmt_info = $conn->prepare("
            SELECT 
              e.lieu_expediteur AS src_wilaya, 
              d.lieu_destinataire AS dst_wilaya
            FROM colis c
            JOIN expediteur e ON c.num_expediteur = e.num_expediteur
            JOIN destinataire d ON c.num_destinataire = d.num_destinataire
            WHERE c.tracking = ?
        ");
        $stmt_info->bind_param("i", $trackingRecherche);
        $stmt_info->execute();
        $result_info = $stmt_info->get_result();
        if ($result_info->num_rows === 0) {
            throw new Exception("Le numéro de suivi <b>$trackingRecherche</b> n’existe pas en base.");
        }
        $rowInfo   = $result_info->fetch_assoc();
        $srcWilaya = $rowInfo['src_wilaya'];
        $dstWilaya = $rowInfo['dst_wilaya'];

        // Recharger la ligne colis_remorque existante
        $rowRemorque = get_colis_remorque_row($conn, $trackingRecherche);

        // Selon $roleWilaya, on effectue l’INSERT ou l’UPDATE nécessaire
        if ($roleWilaya === "expediteur") {
            // Validation du DÉPART (wilaya d’expéditeur)
            if (empty($num_conducteur)) {
                throw new Exception("Vous devez choisir un conducteur pour valider le départ du suivi <b>$trackingRecherche</b>.");
            }
            if (!$rowRemorque) {
                // INSERT initial
                $stmt_ins = $conn->prepare("
                    INSERT INTO colis_remorque 
                      (num_conducteur, tracking, date_depart, ville_depart) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt_ins->bind_param("siss", $num_conducteur, $trackingRecherche, $date_today, $userWilaya);
                $stmt_ins->execute();
                if ($stmt_ins->affected_rows <= 0) {
                    throw new Exception("Erreur lors de l'insertion initiale dans colis_remorque pour le départ (tracking $trackingRecherche).");
                }
            } else {
                // UPDATE existant
                $stmt_upd = $conn->prepare("
                    UPDATE colis_remorque
                    SET ville_depart = ?, date_depart = ?, num_conducteur = ?
                    WHERE tracking = ?
                ");
                $stmt_upd->bind_param("sssi", $userWilaya, $date_today, $num_conducteur, $trackingRecherche);
                $stmt_upd->execute();
                if ($stmt_upd->affected_rows <= 0) {
                    throw new Exception("Erreur lors de la mise à jour du départ pour le suivi <b>$trackingRecherche</b>.");
                }
            }
            $message = "✔ Colis <b>$trackingRecherche</b> validé <b>au départ</b> dans la wilaya <b>$userWilaya</b>.";
        }
        elseif ($roleWilaya === "destinataire") {
            // Validation de l’ARRIVÉE FINALE (wilaya du destinataire)
            if (!$rowRemorque) {
                throw new Exception("Impossible de valider l’arrivée : aucun départ n’a été enregistré pour le suivi <b>$trackingRecherche</b>.");
            }
            $stmt_upd_arr = $conn->prepare("
                UPDATE colis_remorque
                SET ville_arrive = ?, date_arrive = ?
                WHERE tracking = ?
            ");
            $stmt_upd_arr->bind_param("ssi", $userWilaya, $date_today, $trackingRecherche);
            $stmt_upd_arr->execute();
            if ($stmt_upd_arr->affected_rows <= 0) {
                throw new Exception("Erreur lors de la mise à jour de l’arrivée pour le suivi <b>$trackingRecherche</b>.");
            }
            $message = "✔ Colis <b>$trackingRecherche</b> validé <b>à l’arrivée</b> (destination) dans la wilaya <b>$userWilaya</b>.";
        }
        elseif ($roleWilaya === "inter_arr") {
            // Validation de l’ARRIVÉE EN TRANSIT (wilaya intermédiaire, 1re étape)
            if (!$rowRemorque) {
                // INSERT initial avec arrivée en transit (pas logique, mais on lève une erreur)
                throw new Exception("Impossible de valider l’arrivée en transit : aucun départ n’a été enregistré pour le suivi <b>$trackingRecherche</b>.");
            }
            $stmt_arr = $conn->prepare("
                UPDATE colis_remorque
                SET ville_arrive = ?, date_arrive = ?
                WHERE tracking = ?
            ");
            $stmt_arr->bind_param("ssi", $userWilaya, $date_today, $trackingRecherche);
            $stmt_arr->execute();
            if ($stmt_arr->affected_rows <= 0) {
                throw new Exception("Erreur lors de la validation de l’arrivée en transit pour le suivi <b>$trackingRecherche</b>.");
            }
            $message = "✔ Colis <b>$trackingRecherche</b> validé <b>à l’arrivée</b> (transit) dans la wilaya <b>$userWilaya</b>.";
        }
        elseif ($roleWilaya === "inter_dep") {
            // Validation du DÉPART EN TRANSIT (wilaya intermédiaire, 2e étape)
            if (empty($num_conducteur)) {
                throw new Exception("Vous devez choisir un conducteur pour valider le départ du suivi <b>$trackingRecherche</b> depuis cette wilaya intermédiaire.");
            }
            $stmt_dep = $conn->prepare("
                UPDATE colis_remorque
                SET ville_depart = ?, date_depart = ?, num_conducteur = ?
                WHERE tracking = ?
            ");
            $stmt_dep->bind_param("sssi", $userWilaya, $date_today, $num_conducteur, $trackingRecherche);
            $stmt_dep->execute();
            if ($stmt_dep->affected_rows <= 0) {
                throw new Exception("Erreur lors de la validation du départ en transit pour le suivi <b>$trackingRecherche</b>.");
            }
            $message = "✔ Colis <b>$trackingRecherche</b> validé <b>au départ</b> (transit) depuis la wilaya <b>$userWilaya</b>.";
        }
        else {
            throw new Exception("Rôle de wilaya inconnu. Impossible de valider.");
        }

        $conn->commit();
    }
    catch (Exception $e) {
        $conn->rollback();
        $message = $e->getMessage();
    }

    // Après validation, on recharge les listes des colis validés aujourd’hui
}

// Récupération des colis validés aujourd’hui (arrivée) pour affichage
$stmt_arrive = $conn->prepare("
    SELECT c.tracking, cr.date_arrive 
    FROM colis c 
    JOIN colis_remorque cr ON c.tracking = cr.tracking 
    WHERE cr.ville_arrive = ? 
      AND DATE(cr.date_arrive) = ?
");
$stmt_arrive->bind_param("ss", $userWilaya, $current_date);
$stmt_arrive->execute();
$result_arrive = $stmt_arrive->get_result();
$colis_valides_arrive = $result_arrive->fetch_all(MYSQLI_ASSOC);

// Récupération des colis validés aujourd’hui (départ) pour affichage
$stmt_depart = $conn->prepare("
    SELECT c.tracking, cr.date_depart 
    FROM colis c 
    JOIN colis_remorque cr ON c.tracking = cr.tracking 
    WHERE cr.ville_depart = ? 
      AND DATE(cr.date_depart) = ?
");
$stmt_depart->bind_param("ss", $userWilaya, $current_date);
$stmt_depart->execute();
$result_depart = $stmt_depart->get_result();
$colis_valides_depart = $result_depart->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validation des Arrivées / Départs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #01022d, #000);
            color: white;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container-custom {
            width: 90%;
            max-width: 900px;
            background: linear-gradient(to right, #01022de5 30%, #00000050);
            padding: 30px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }
        .table-container {
            max-height: 300px;
            overflow-y: auto;
            width: 100%;
        }
        .form-control {
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            font-size: 16px;
        }
        .btn-custom {
            background-color: deeppink;
            border-radius: 30px;
            color: white;
            font-weight: bold;
        }
        .navbar {
            background: transparent;
            width: 100%;
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
            color:deeppink;
        }
        /* Masquer la zone de sélection du conducteur par défaut */
        #conducteur_select {
            margin-top: 15px;
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
                 <li class="nav-item"><a class="nav-link" href="gererclient.php">gestion_enregistrement</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-custom">
    <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
    <h2>Wilaya actuelle : <?php echo htmlspecialchars($userWilaya); ?></h2>

    <?php if (!$showFormStage2): ?>
        <!-- ===============================
             ÉTAPE 1 : Formulaire de recherche du numéro de suivi
             =============================== -->
        <form method="POST" class="w-75 mx-auto mt-4 text-center">
            <div class="mb-3">
                <label for="tracking_recherche" class="form-label">Numéro de suivi :</label>
                <input type="text"
                       id="tracking_recherche"
                       name="tracking_recherche"
                       class="form-control"
                       required
                       placeholder="Ex. 123" />
            </div>
            <button type="submit" class="btn btn-custom mt-2">Rechercher</button>
        </form>

        <?php if ($message): ?>
            <div class="mt-3 w-75 mx-auto">
                <p class="text-<?php echo (strpos($message, '✔') !== false) ? 'success' : 'danger'; ?> fw-bold">
                    <?php echo $message; ?>
                </p>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- ===============================
             ÉTAPE 2 : Formulaire de validation (départ ou arrivée)
             =============================== -->
        <div class="alert alert-info w-75 mx-auto fw-bold" role="alert" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3);">
            📌 Pour le suivi <b><?php echo htmlspecialchars($trackingRecherche); ?></b> :<br>
            <?php if ($roleWilaya === "expediteur"): ?>
                Vous êtes dans la <b>wilaya de l’expéditeur</b> (<?php echo htmlspecialchars($srcWilaya); ?>) → validation du <b>départ</b> (choix du conducteur obligatoire).
            <?php elseif ($roleWilaya === "destinataire"): ?>
                Vous êtes dans la <b>wilaya du destinataire</b> (<?php echo htmlspecialchars($dstWilaya); ?>) → validation de <b>l’arrivée</b> (pas de conducteur).
            <?php elseif ($roleWilaya === "inter_arr"): ?>
                Vous êtes dans une <b>wilaya intermédiaire</b> (<?php echo htmlspecialchars($userWilaya); ?>) pour valider <b>l’arrivée en transit</b> (pas de conducteur).
            <?php elseif ($roleWilaya === "inter_dep"): ?>
                Vous êtes dans une <b>wilaya intermédiaire</b> (<?php echo htmlspecialchars($userWilaya); ?>) pour valider le <b>départ en transit</b> (choix du conducteur obligatoire).
            <?php endif; ?>
        </div>

        <form method="POST" class="w-75 mx-auto mt-3 text-center">
            <!-- On conserve le numéro de suivi et le rôle dans des champs cachés -->
            <input type="hidden" name="tracking_recherche" value="<?php echo htmlspecialchars($trackingRecherche); ?>">
            <input type="hidden" name="roleWilaya" value="<?php echo htmlspecialchars($roleWilaya); ?>">
            <input type="hidden" name="action_stage2" value="1">

            <!-- Si on doit valider un départ (expéditeur ou inter_dep), on montre le sélecteur de conducteur -->
            <?php if ($roleWilaya === "expediteur" || $roleWilaya === "inter_dep"): ?>
                <div id="conducteur_select" class="mb-3">
                    <label for="num_conducteur" class="form-label">Choisir un conducteur :</label>
                    <select name="num_conducteur" id="num_conducteur" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($conducteurs as $conducteur): ?>
                            <option value="<?php echo htmlspecialchars($conducteur['num_conducteur']); ?>">
                                <?php echo htmlspecialchars($conducteur['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <!-- Bouton de validation -->
            <button type="submit" class="btn btn-custom mt-2">
                <?php
                    if ($roleWilaya === "expediteur" || $roleWilaya === "inter_dep") {
                        echo "Valider le départ";
                    } else {
                        echo "Valider l’arrivée";
                    }
                ?>
            </button>
        </form>

        <?php if ($message): ?>
            <div class="mt-3 w-75 mx-auto">
                <p class="text-<?php echo (strpos($message, '✔') !== false) ? 'success' : 'danger'; ?> fw-bold">
                    <?php echo $message; ?>
                </p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ===============================
         Tableau récapitulatif des colis validés aujourd’hui
         =============================== -->
    <div class="row mt-5 w-100">
        <div class="col-md-6">
            <h4>Arrivées aujourd’hui (<?php echo $current_date; ?>)</h4>
            <div class="table-container">
                <table class="table table-striped table-dark">
                    <thead>
                        <tr>
                            <th>Tracking</th>
                            <th>Date d’arrivée</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($colis_valides_arrive)): ?>
                            <tr><td colspan="2">Aucun colis validé à l’arrivée.</td></tr>
                        <?php else: ?>
                            <?php foreach ($colis_valides_arrive as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['tracking']); ?></td>
                                    <td><?php echo htmlspecialchars($row['date_arrive']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-6">
            <h4>Départs aujourd’hui (<?php echo $current_date; ?>)</h4>
            <div class="table-container">
                <table class="table table-striped table-dark">
                    <thead>
                        <tr>
                            <th>Tracking</th>
                            <th>Date de départ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($colis_valides_depart)): ?>
                            <tr><td colspan="2">Aucun colis validé au départ.</td></tr>
                        <?php else: ?>
                            <?php foreach ($colis_valides_depart as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['tracking']); ?></td>
                                    <td><?php echo htmlspecialchars($row['date_depart']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Pas de JavaScript compliqué pour l’affichage conditionnel :
     tout se fait côté serveur en deux étapes -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
