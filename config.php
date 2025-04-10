<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'base_ace');

// Connexion à MySQL
try {
    $db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Configuration des uploads
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Fonction pour calculer les jours restants
function calculateDaysRemaining($endDate) {
    if (empty($endDate)) return 'N/A';
    
    $today = new DateTime();
    $end = new DateTime($endDate);
    $interval = $today->diff($end);
    
    if ($interval->invert) {
        return 'Expiré';
    } else {
        return $interval->days;
    }
}
?>