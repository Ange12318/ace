<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header('Location: modifier.php');
    exit;
}

// Afficher les erreurs s'il y en a
session_start();
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

// Récupérer les données du stagiaire
try {
    $stmt = $db->prepare("SELECT * FROM stagiaires WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $stagiaire = $stmt->fetch();
    
    if (!$stagiaire) {
        header('Location: modifier.php');
        exit;
    }
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Gestion des messages de succès
$success = isset($_GET['renewal']) && $_GET['renewal'] === 'success';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projet ACE - Modifier un stagiaire</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Modifier un stagiaire</h1>
        <a href="modifier.php" class="btn-back">Retour à la liste</a>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Le contrat a été renouvelé avec succès pour 3 mois supplémentaires.
            </div>
        <?php endif; ?>
        
        <form action="traitement_modif.php" method="post" enctype="multipart/form-data" class="stagiaire-form">
            <input type="hidden" name="id" value="<?= htmlspecialchars($stagiaire['id']) ?>">
            
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($stagiaire['nom']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="prenoms">Prénoms :</label>
                <input type="text" id="prenoms" name="prenoms" value="<?= htmlspecialchars($stagiaire['prenoms']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="date_naissance">Date de naissance :</label>
                <input type="date" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($stagiaire['date_naissance']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="date_debut">Date début de stage :</label>
                <input type="date" id="date_debut" name="date_debut" value="<?= htmlspecialchars($stagiaire['date_debut']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="date_fin">Date fin de stage :</label>
                <input type="date" id="date_fin" name="date_fin" value="<?= htmlspecialchars($stagiaire['date_fin']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="departement">Département :</label>
                <select id="departement" name="departement" required>
                    <option value="RH" <?= $stagiaire['departement'] === 'RH' ? 'selected' : '' ?>>RH</option>
                    <option value="JURIDIQUE" <?= $stagiaire['departement'] === 'JURIDIQUE' ? 'selected' : '' ?>>Juridique</option>
                    <option value="INFORMATIQUE" <?= $stagiaire['departement'] === 'INFORMATIQUE' ? 'selected' : '' ?>>Informatique</option>
                    <option value="MARKETING" <?= $stagiaire['departement'] === 'MARKETING' ? 'selected' : '' ?>>Marketing</option>
                    <option value="COMPTABILITE" <?= $stagiaire['departement'] === 'COMPTABILITE' ? 'selected' : '' ?>>Comptabilité</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Photo de la CNI actuelle :</label>
                <?php if (!empty($stagiaire['cni'])): ?>
                    <a href="<?= htmlspecialchars($stagiaire['cni']) ?>" target="_blank">Voir le fichier</a>
                <?php else: ?>
                    <span>Aucun fichier</span>
                <?php endif; ?>
                <label for="cni">Nouvelle photo de CNI (image) :</label>
                <input type="file" id="cni" name="cni" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>CV actuel :</label>
                <?php if (!empty($stagiaire['cv'])): ?>
                    <a href="<?= htmlspecialchars($stagiaire['cv']) ?>" target="_blank">Voir le fichier</a>
                <?php else: ?>
                    <span>Aucun fichier</span>
                <?php endif; ?>
                <label for="cv">Nouveau CV (PDF ou DOC) :</label>
                <input type="file" id="cv" name="cv" accept=".pdf,.doc,.docx">
            </div>
            
            <div class="form-group">
                <label>Attestation CMU actuelle :</label>
                <?php if (!empty($stagiaire['cmu'])): ?>
                    <a href="<?= htmlspecialchars($stagiaire['cmu']) ?>" target="_blank">Voir le fichier</a>
                <?php else: ?>
                    <span>Aucun fichier</span>
                <?php endif; ?>
                <label for="cmu">Nouvelle attestation CMU (PDF ou image) :</label>
                <input type="file" id="cmu" name="cmu" accept=".pdf,.jpg,.jpeg,.png">
            </div>
            
            <button type="submit" class="btn-submit">Mettre à jour</button>
        </form>

        <div class="renewal-section">
            <h3>Renouvellement de contrat</h3>
            <p>Jours restants : <strong><?= calculateDaysRemaining($stagiaire['date_fin']) ?> jours</strong></p>
            <p>Nombre de renouvellements : <strong><?= htmlspecialchars($stagiaire['renouvellements'] ?? 0) ?></strong></p>
            <form action="traitement_renouvellement.php" method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars($stagiaire['id']) ?>">
                <button type="submit" class="btn-renew">Renouveler pour 3 mois</button>
            </form>
        </div>
    </div>
</body>
</html>