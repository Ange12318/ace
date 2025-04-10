<?php
session_start();
include 'config.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id = $_GET['id'];
$stmt = $db->prepare("SELECT * FROM stagiaires WHERE id = :id");
$stmt->execute([':id' => $id]);
$stagiaire = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stagiaire) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Infos Stagiaire</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header><h1>Informations du stagiaire</h1></header>
        <div class="stagiaire-details">
            <p><strong>ID :</strong> <?php echo $stagiaire['id']; ?></p>
            <p><strong>Nom :</strong> <?php echo $stagiaire['nom']; ?></p>
            <p><strong>Prénoms :</strong> <?php echo $stagiaire['prenoms']; ?></p>
            <p><strong>Date de naissance :</strong> <?php echo $stagiaire['date_naissance']; ?></p>
            <p><strong>Date de début :</strong> <?php echo $stagiaire['date_debut']; ?></p>
            <p><strong>Date de fin :</strong> <?php echo $stagiaire['date_fin']; ?></p>
            <p><strong>Département :</strong> <?php echo $stagiaire['departement']; ?></p>
            <p><strong>CNI :</strong> <a href="<?php echo $stagiaire['cni']; ?>" target="_blank">Voir fichier</a></p>
            <p><strong>CV :</strong> <a href="<?php echo $stagiaire['cv']; ?>" target="_blank">Voir fichier</a></p>
            <p><strong>CMU :</strong> <a href="<?php echo $stagiaire['cmu']; ?>" target="_blank">Voir fichier</a></p>
        </div>
        <div class="form-actions">
            <a href="modifier.php?id=<?php echo $stagiaire['id']; ?>" class="btn">Modifier</a>
            <a href="supprimer.php?id=<?php echo $stagiaire['id']; ?>" class="btn delete" onclick="return confirm('Confirmer la suppression ?');">Supprimer</a>
            <a href="dashboard.php" class="btn secondary">Retour au tableau de bord</a>
        </div>
    </div>
</body>
</html>