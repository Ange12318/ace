<?php
session_start();
include 'config.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$error = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nom = $_POST['nom'];
        $prenoms = $_POST['prenoms'];
        $date_naissance = $_POST['date_naissance'];
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'];
        $departement = $_POST['departement'];

        $target_dir = UPLOAD_DIR;
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword'];

        $cni = $_POST['old_cni'];
        $cv = $_POST['old_cv'];
        $cmu = $_POST['old_cmu'];

        foreach (['cni', 'cv', 'cmu'] as $file) {
            if (!empty($_FILES[$file]["name"])) {
                if ($_FILES[$file]['size'] > MAX_FILE_SIZE) {
                    $error = "Le fichier $file dépasse la taille maximale de 5 Mo.";
                    break;
                }
                if (!in_array($_FILES[$file]['type'], $allowed_types)) {
                    $error = "Type de fichier non autorisé pour $file.";
                    break;
                }
            }
        }

        if (!$error) {
            if (!empty($_FILES["cni"]["name"])) {
                $cni = $target_dir . time() . '_' . basename($_FILES["cni"]["name"]);
                move_uploaded_file($_FILES["cni"]["tmp_name"], $cni);
            }
            if (!empty($_FILES["cv"]["name"])) {
                $cv = $target_dir . time() . '_' . basename($_FILES["cv"]["name"]);
                move_uploaded_file($_FILES["cv"]["tmp_name"], $cv);
            }
            if (!empty($_FILES["cmu"]["name"])) {
                $cmu = $target_dir . time() . '_' . basename($_FILES["cmu"]["name"]);
                move_uploaded_file($_FILES["cmu"]["tmp_name"], $cmu);
            }

            $stmt = $db->prepare("UPDATE stagiaires SET nom=:nom, prenoms=:prenoms, date_naissance=:date_naissance, 
                                  date_debut=:date_debut, date_fin=:date_fin, departement=:departement, cni=:cni, cv=:cv, cmu=:cmu 
                                  WHERE id=:id");
            $stmt->execute([
                ':nom' => $nom, ':prenoms' => $prenoms, ':date_naissance' => $date_naissance,
                ':date_debut' => $date_debut, ':date_fin' => $date_fin, ':departement' => $departement,
                ':cni' => $cni, ':cv' => $cv, ':cmu' => $cmu, ':id' => $id
            ]);
            $_SESSION['message'] = "Stagiaire modifié avec succès !";
            header("Location: modifier.php");
            exit;
        }
    }

    $stmt = $db->prepare("SELECT * FROM stagiaires WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $stagiaire = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $db->query("SELECT * FROM stagiaires");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier un stagiaire</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header><h1>Modifier un stagiaire</h1></header>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($stagiaire)) { ?>
            <form method="POST" enctype="multipart/form-data" class="form">
                <div class="form-group">
                    <input type="text" name="nom" value="<?php echo $stagiaire['nom']; ?>" required>
                    <small>Nom de famille du stagiaire</small>
                </div>
                <div class="form-group">
                    <input type="text" name="prenoms" value="<?php echo $stagiaire['prenoms']; ?>" required>
                    <small>Prénoms du stagiaire</small>
                </div>
                <div class="form-group">
                    <input type="date" name="date_naissance" value="<?php echo $stagiaire['date_naissance']; ?>" required>
                    <small>Date de naissance du stagiaire</small>
                </div>
                <div class="form-group">
                    <input type="date" name="date_debut" value="<?php echo $stagiaire['date_debut']; ?>" required>
                    <small>Date de début du stage</small>
                </div>
                <div class="form-group">
                    <input type="date" name="date_fin" value="<?php echo $stagiaire['date_fin']; ?>" required>
                    <small>Date de fin du stage</small>
                </div>
                <div class="form-group">
                    <select name="departement" required>
                        <?php foreach (['RH', 'JURIDIQUE', 'INFORMATIQUE', 'MARKETING', 'COMPTABILITE'] as $dep) { ?>
                            <option value="<?php echo $dep; ?>" <?php echo $stagiaire['departement'] == $dep ? 'selected' : ''; ?>><?php echo $dep; ?></option>
                        <?php } ?>
                    </select>
                    <small>Département où le stagiaire est affecté</small>
                </div>
                <div class="form-group">
                    <input type="file" name="cni" accept="image/*"><input type="hidden" name="old_cni" value="<?php echo $stagiaire['cni']; ?>">
                    <small>Photo de la carte d'identité (CNI)</small>
                </div>
                <div class="form-group">
                    <input type="file" name="cv" accept=".pdf,.doc"><input type="hidden" name="old_cv" value="<?php echo $stagiaire['cv']; ?>">
                    <small>Curriculum Vitae (PDF ou DOC)</small>
                </div>
                <div class="form-group">
                    <input type="file" name="cmu" accept=".pdf,image/*"><input type="hidden" name="old_cmu" value="<?php echo $stagiaire['cmu']; ?>">
                    <small>Attestation CMU (PDF ou image)</small>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn">Mettre à jour</button>
                    <a href="modifier.php" class="btn secondary">Retour</a>
                </div>
            </form>
        <?php } else { ?>
            <table>
                <thead>
                    <tr><th>ID</th><th>Nom</th><th>Prénoms</th><th>Département</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nom']; ?></td>
                            <td><?php echo $row['prenoms']; ?></td>
                            <td><?php echo $row['departement']; ?></td>
                            <td>
                                <a href="voir.php?id=<?php echo $row['id']; ?>" class="btn info">Voir</a>
                                <a href="modifier.php?id=<?php echo $row['id']; ?>" class="btn">Modifier</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <a href="dashboard.php" class="btn secondary">Retour</a>
        <?php } ?>
    </div>
</body>
</html>