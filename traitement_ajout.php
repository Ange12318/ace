<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $errors = [];
    
    // Vérification des champs obligatoires
    $required = ['nom', 'prenoms', 'date_naissance', 'date_debut', 'date_fin', 'departement'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Le champ $field est obligatoire.";
        }
    }
    
    // Vérification des fichiers
    $fileFields = ['cni', 'cv', 'cmu'];
    foreach ($fileFields as $field) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Le fichier $field est obligatoire.";
        }
    }
    
    if (empty($errors)) {
        try {
            // Traitement des fichiers
            $uploads = [];
            foreach ($fileFields as $field) {
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
                
                $uploads[$field] = $destination;
            }
            
            // Insertion en base de données
            $stmt = $db->prepare("INSERT INTO stagiaires (nom, prenoms, date_naissance, date_debut, date_fin, departement, cni, cv, cmu) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['nom'],
                $_POST['prenoms'],
                $_POST['date_naissance'],
                $_POST['date_debut'],
                $_POST['date_fin'],
                $_POST['departement'],
                $uploads['cni'],
                $uploads['cv'],
                $uploads['cmu']
            ]);
            
            // Redirection vers le dashboard avec un message de succès
            header('Location: dashboard.php?success=1');
            exit;
            
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
    
    // Si erreurs, on les affiche
    if (!empty($errors)) {
        session_start();
        $_SESSION['errors'] = $errors;
        header('Location: ajouter.php');
        exit;
    }
} else {
    header('Location: ajouter.php');
    exit;
}
?>