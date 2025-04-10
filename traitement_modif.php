<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $errors = [];
    
    // Vérification des champs obligatoires
    $required = ['id', 'nom', 'prenoms', 'date_naissance', 'date_debut', 'date_fin', 'departement'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Le champ $field est obligatoire.";
        }
    }
    
    if (empty($errors)) {
        try {
            // Récupération des données actuelles
            $stmt = $db->prepare("SELECT * FROM stagiaires WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $currentData = $stmt->fetch();
            
            if (!$currentData) {
                throw new Exception("Stagiaire non trouvé.");
            }
            
            // Traitement des fichiers (seulement si un nouveau fichier est uploadé)
            $fileFields = ['cni', 'cv', 'cmu'];
            $updates = [];
            $params = [
                $_POST['nom'],
                $_POST['prenoms'],
                $_POST['date_naissance'],
                $_POST['date_debut'],
                $_POST['date_fin'],
                $_POST['departement'],
                $_POST['id']
            ];
            
            $sql = "UPDATE stagiaires SET nom = ?, prenoms = ?, date_naissance = ?, date_debut = ?, date_fin = ?, departement = ?";
            
            foreach ($fileFields as $field) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES[$field];
                    
                    // Vérification de la taille
                    if ($file['size'] > MAX_FILE_SIZE) {
                        throw new Exception("Le fichier $field est trop volumineux.");
                    }
                    
                    // Détermination du répertoire de destination
                    $subdir = '';
                    if ($field === 'cni') $subdir = 'cni/';
                    if ($field === 'cv') $subdir = 'cv/';
                    if ($field === 'cmu') $subdir = 'cmu/';
                    
                    // Création du répertoire si nécessaire
                    if (!is_dir(UPLOAD_DIR . $subdir)) {
                        mkdir(UPLOAD_DIR . $subdir, 0755, true);
                    }
                    
                    // Génération d'un nom de fichier unique
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $ext;
                    $destination = UPLOAD_DIR . $subdir . $filename;
                    
                    // Déplacement du fichier
                    if (!move_uploaded_file($file['tmp_name'], $destination)) {
                        throw new Exception("Erreur lors de l'upload du fichier $field.");
                    }
                    
                    // Suppression de l'ancien fichier
                    if (!empty($currentData[$field]) && file_exists($currentData[$field])) {
                        unlink($currentData[$field]);
                    }
                    
                    $sql .= ", $field = ?";
                    $params[] = $destination;
                }
            }
            
            $sql .= " WHERE id = ?";
            
            // Mise à jour en base de données
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            // Redirection vers la liste avec un message de succès
            header('Location: modifier.php?success=1');
            exit;
            
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
    
    // Si erreurs, on les affiche
    if (!empty($errors)) {
        session_start();
        $_SESSION['errors'] = $errors;
        header("Location: modifier_form.php?id={$_POST['id']}");
        exit;
    }
} else {
    header('Location: modifier.php');
    exit;
}
?>