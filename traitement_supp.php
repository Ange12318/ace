<?php
require_once 'config.php';

if (isset($_GET['id'])) {
    try {
        // Récupération des informations du stagiaire pour supprimer les fichiers
        $stmt = $db->prepare("SELECT cni, cv, cmu FROM stagiaires WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $stagiaire = $stmt->fetch();
        
        if ($stagiaire) {
            // Suppression des fichiers
            $fileFields = ['cni', 'cv', 'cmu'];
            foreach ($fileFields as $field) {
                if (!empty($stagiaire[$field]) && file_exists($stagiaire[$field])) {
                    unlink($stagiaire[$field]);
                }
            }
            
            // Suppression de la base de données
            $stmt = $db->prepare("DELETE FROM stagiaires WHERE id = ?");
            $stmt->execute([$_GET['id']]);
        }
        
        // Redirection avec message de succès
        header('Location: supprimer.php?success=1');
        exit;
        
    } catch (Exception $e) {
        // Redirection avec message d'erreur
        header('Location: supprimer.php?error=' . urlencode($e->getMessage()));
        exit;
    }
} else {
    header('Location: supprimer.php');
    exit;
}
?>