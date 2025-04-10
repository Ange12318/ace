<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($username === 'ACE' && $password === '012345') {
        $_SESSION['loggedin'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Identifiants incorrects";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Connexion - Projet ACE</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-bg">
    <div class="login-container">
        <h1>Connexion</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" class="btn">Se connecter</button>
        </form>
    </div>
</body>
</html>