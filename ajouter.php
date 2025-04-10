<?php
session_start();
include 'config.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'];
    $prenoms = $_POST['prenoms'];
    $date_naissance = $_POST['date_naissance'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $departement = $_POST['departement'];

    $target_dir = UPLOAD_DIR;
    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword'];

    // Validation des fichiers
    foreach (['cni', 'cv', 'cmu'] as $file) {
        if ($_FILES[$file]['size'] > MAX_FILE_SIZE) {
            $error = "Le fichier $file dépasse la taille maximale de 5 Mo.";
            break;
        }
        if (!in_array($_FILES[$file]['type'], $allowed_types)) {
            $error = "Type de fichier non autorisé pour $file.";
            break;
        }
    }

    if (!$error) {
        $cni = $target_dir . time() . '_' . basename($_FILES["cni"]["name"]);
        $cv = $target_dir . time() . '_' . basename($_FILES["cv"]["name"]);
        $cmu = $target_dir . time() . '_' . basename($_FILES["cmu"]["name"]);

        move_uploaded_file($_FILES["cni"]["tmp_name"], $cni);
        move_uploaded_file($_FILES["cv"]["tmp_name"], $cv);
        move_uploaded_file($_FILES["cmu"]["tmp_name"], $cmu);

        $stmt = $db->prepare("INSERT INTO stagiaires (nom, prenoms, date_naissance, date_debut, date_fin, departement, cni, cv, cmu) 
                              VALUES (:nom, :prenoms, :date_naissance, :date_debut, :date_fin, :departement, :cni, :cv, :cmu)");
        $stmt->execute([
            ':nom' => $nom, ':prenoms' => $prenoms, ':date_naissance' => $date_naissance,
            ':date_debut' => $date_debut, ':date_fin' => $date_fin, ':departement' => $departement,
            ':cni' => $cni, ':cv' => $cv, ':cmu' => $cmu
        ]);
        $_SESSION['message'] = "Stagiaire ajouté avec succès !";
        header("Location: dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter un stagiaire</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header><h1>Ajouter un stagiaire</h1></header>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" enctype="multipart/form-data" class="form">
            <div class="form-group">
                <input type="text" name="nom" placeholder="Nom" required>
                <small>Nom de famille du stagiaire</small>
            </div>
            <div class="form-group">
                <input type="text" name="prenoms" placeholder="Prénoms" required>
                <small>Prénoms du stagiaire</small>
            </div>
            <div class="form-group">
                <input type="date" name="date_naissance" required>
                <small>Date de naissance du stagiaire</small>
            </div>
            <div class="form-group">
                <input type="date" name="date_debut" required>
                <small>Date de début du stage</small>
            </div>
            <div class="form-group">
                <input type="date" name="date_fin" required>
                <small>Date de fin du stage</small>
            </div>
            <div class="form-group">
                <select name="departement" required>
                    <option value="RH">RH</option>
                    <option value="JURIDIQUE">JURIDIQUE</option>
                    <option value="INFORMATIQUE">INFORMATIQUE</option>
                    <option value="MARKETING">MARKETING</option>
                    <option value="COMPTABILITE">COMPTABILITE</option>
                </select>
                <small>Département où le stagiaire est affecté</small>
            </div>
            <div class="form-group">
                <input type="file" name="cni" accept="image/*" required>
                <small>Photo de la carte d'identité (CNI)</small>
            </div>
            <div class="form-group">
                <input type="file" name="cv" accept=".pdf,.doc" required>
                <small>Curriculum Vitae (PDF ou DOC)</small>
            </div>
            <div class="form-group">
                <input type="file" name="cmu" accept=".pdf,image/*" required>
                <small>Attestation CMU (PDF ou image)</small>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Ajouter</button>
                <a href="dashboard.php" class="btn secondary">Retour</a>
            </div>
        </form>
    </div>
</body>
</html>