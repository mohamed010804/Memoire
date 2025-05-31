<?php
session_start();

// Vérification de la session administrateur
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'administrateur') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "ameliorec");
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Vérification de la présence du paramètre ID (tracking)
if (!isset($_GET['id'])) {
    echo "Colis introuvable.";
    exit();
}

$tracking = $_GET['id'];

// Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_colis = $_POST['type_colis'];
    $quantite = $_POST['quantite'];
    $type_emballage = $_POST['type_emballage'];
    $CR = $_POST['CR'];
    $livraison = $_POST['livraison'];
    $etat = $_POST['etat'];

    $update = $conn->prepare("UPDATE colis SET type_colis=?, quantite=?, type_emballage=?, CR=?, livraison=?, etat=? WHERE tracking=?");
    $update->bind_param("sisssss", $type_colis, $quantite, $type_emballage, $CR, $livraison, $etat, $tracking);

    if ($update->execute()) {
        header("Location: gestion_colis.php?modification=success");
        exit();
    } else {
        echo "Erreur lors de la mise à jour.";
    }
}

// Récupération des infos du colis
$stmt = $conn->prepare("
    SELECT type_colis, quantite, type_emballage, CR, livraison, etat
    FROM colis WHERE tracking = ?
");
$stmt->bind_param("s", $tracking);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Colis non trouvé.";
    exit();
}

$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un colis</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to right, #01022d, #000);
            color: white;
            font-family: 'Ubuntu', sans-serif;
            padding: 40px;
        }
        .container {
            background: rgba(0,0,0,0.6);
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 0 20px deeppink;
        }
        h2 {
            text-align: center;
            color: deeppink;
            margin-bottom: 30px;
        }
        label {
            font-weight: bold;
        }
        .btn-submit {
            background-color: deeppink;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: bold;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            background: transparent;
            color: deeppink;
            border: 2px solid deeppink;
        }
        a.back {
            color: white;
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
        }
        a.back:hover {
            color: deeppink;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="gestion_colis.php" class="back">← Retour</a>
        <h2>Modifier le colis n° <?= htmlspecialchars($tracking) ?></h2>
        <form method="post">
            <div class="mb-3">
                <label for="type_colis">Type de colis</label>
                <input type="text" class="form-control" name="type_colis" required value="<?= htmlspecialchars($data['type_colis']) ?>">
            </div>
            <div class="mb-3">
                <label for="quantite">Quantité</label>
                <input type="number" class="form-control" name="quantite" required min="1" value="<?= htmlspecialchars($data['quantite']) ?>">
            </div>
            <div class="mb-3">
                <label for="type_emballage">Type d'emballage</label>
                <input type="text" class="form-control" name="type_emballage" required value="<?= htmlspecialchars($data['type_emballage']) ?>">
            </div>
            <div class="mb-3">
                <label for="CR">Coût de revient (DA)</label>
                <input type="number" class="form-control" name="CR" required min="0" value="<?= htmlspecialchars($data['CR']) ?>">
            </div>
            <div class="mb-3">
                <label for="livraison">Frais de livraison (DA)</label>
                <input type="number" class="form-control" name="livraison" required min="0" value="<?= htmlspecialchars($data['livraison']) ?>">
            </div>
            <div class="mb-3">
                <label for="etat">État du colis</label>
                <select name="etat" class="form-control" required>
                    <option value="en transit" <?= $data['etat'] === 'en transit' ? 'selected' : '' ?>>En transit</option>
                    <option value="livré" <?= $data['etat'] === 'livré' ? 'selected' : '' ?>>Livré</option>
                    <option value="retourné" <?= $data['etat'] === 'retourné' ? 'selected' : '' ?>>Retourné</option>
                </select>
            </div>
            <button type="submit" class="btn btn-submit w-100">Enregistrer les modifications</button>
        </form>
    </div>
</body>
</html>

<?php $conn->close(); ?>
