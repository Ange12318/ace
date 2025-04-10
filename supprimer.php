<?php
session_start();
include 'config.php';
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $db->prepare("DELETE FROM stagiaires WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $_SESSION['message'] = "Stagiaire supprimé avec succès !";
    header("Location: supprimer.php");
    exit;
}

$stmt = $db->query("SELECT * FROM stagiaires");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Supprimer un stagiaire</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header><h1>Supprimer un stagiaire</h1></header>
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
                            <a href="supprimer.php?id=<?php echo $row['id']; ?>" class="btn delete" onclick="return confirm('Confirmer la suppression ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn secondary">Retour</a>
    </div>
</body>
</html>