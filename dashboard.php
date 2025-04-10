<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projet ACE - Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Gestion des Stagiaires</h1>
        
        <div class="action-buttons">
            <a href="ajouter.php" class="btn">Ajouter un stagiaire</a>
            <a href="modifier.php" class="btn">Modifier un stagiaire</a>
            <a href="supprimer.php" class="btn">Supprimer un stagiaire</a>
        </div>
        
        <h2>Synthèse par département</h2>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Département</th>
                    <th>Nombre de stagiaires</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $departements = ['RH', 'JURIDIQUE', 'INFORMATIQUE', 'MARKETING', 'COMPTABILITE'];
                foreach ($departements as $dep) {
                    $req = $db->prepare("SELECT COUNT(*) as count FROM stagiaires WHERE departement = ?");
                    $req->execute([$dep]);
                    $result = $req->fetch();
                    echo "<tr><td>$dep</td><td>{$result['count']}</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Liste des stagiaires</h2>
        <table class="stagiaires-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénoms</th>
                    <th>Département</th>
                    <th>Jours restants</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $req = $db->query("SELECT id, nom, prenoms, departement, date_fin FROM stagiaires ORDER BY nom");
                while ($stagiaire = $req->fetch()) {
                    $daysRemaining = calculateDaysRemaining($stagiaire['date_fin']);
                    $statusClass = ($daysRemaining === 'Expiré') ? 'contract-expired' : 'contract-active';
                    echo "<tr>
                            <td>{$stagiaire['id']}</td>
                            <td>{$stagiaire['nom']}</td>
                            <td>{$stagiaire['prenoms']}</td>
                            <td>{$stagiaire['departement']}</td>
                            <td><span class='contract-status $statusClass'>$daysRemaining</span></td>
                            <td><a href='modifier_form.php?id={$stagiaire['id']}' class='btn-edit'>Modifier</a></td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>