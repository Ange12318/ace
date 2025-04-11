<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projet ACE - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header class="dashboard-header">
            <h1>Gestion des Stagiaires</h1>
            <a href="logout.php" class="btn btn-logout">Déconnexion</a>
        </header>
        
        <div class="action-buttons">
            <a href="ajouter.php" class="btn btn-primary">Ajouter un stagiaire</a>
            <a href="modifier.php" class="btn btn-primary">Modifier un stagiaire</a>
            <a href="supprimer.php" class="btn btn-primary">Supprimer un stagiaire</a>
        </div>
        
        <section class="dashboard-section">
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
        </section>

        <section class="dashboard-section">
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
                                <td><a href='modifier_form.php?id={$stagiaire['id']}' class='btn btn-edit'>Modifier</a></td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>