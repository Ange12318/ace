<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        // Récupérer les données actuelles du stagiaire
        $stmt = $db->prepare("SELECT date_fin FROM stagiaires WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $stagiaire = $stmt->fetch();
        
        if (!$stagiaire) {
            throw new Exception("Stagiaire non trouvé.");
        }
        
        // Calculer la nouvelle date de fin (3 mois après la date actuelle)
        $newEndDate = new DateTime($stagiaire['date_fin']);
        $newEndDate->add(new DateInterval('P3M')); // Ajoute 3 mois
        
        // Mettre à jour en base de données
        $update = $db->prepare("UPDATE stagiaires SET date_fin = ?, renouvellements = renouvellements + 1 WHERE id = ?");
        $update->execute([$newEndDate->format('Y-m-d'), $_POST['id']]);
        
        // Redirection avec message de succès
        header("Location: modifier_form.php?id={$_POST['id']}&renewal=success");
        exit;
        
    } catch (Exception $e) {
        // Redirection avec message d'erreur
        header("Location: modifier_form.php?id={$_POST['id']}&error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header('Location: modifier.php');
    exit;
}
?>