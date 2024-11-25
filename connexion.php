<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    $usersFile = 'users.json';
    $users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

    if (isset($users[$login])) {
        if (password_verify($password, $users[$login]['password'])) {
            // Fusion des favoris temporaires avec les favoris stockés
            $sessionFavorites = $_SESSION['favorites'] ?? [];
            $storedFavorites = $users[$login]['favorites'] ?? [];
            $mergedFavorites = array_unique(array_merge($sessionFavorites, $storedFavorites));

            // Mise à jour de la session et des données utilisateur
            $_SESSION['user'] = $users[$login];
            $_SESSION['favorites'] = $mergedFavorites;
            $_SESSION['user']['favorites'] = $mergedFavorites;

            // Mise à jour des données dans users.json
            $users[$login]['favorites'] = $mergedFavorites;
            file_put_contents($usersFile, json_encode($users));

            header('Location: index.php');
            exit();
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        $error = "Utilisateur non trouvé.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <!-- Intégration du CSS -->
    <style>
        /* Styles similaires à ceux de style.css */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        form {
            max-width: 400px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
        }
        fieldset {
            border: none;
            margin: 0;
            padding: 0;
        }
        legend {
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        label, input {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }
        input {
            padding: 8px;
            font-size: 1em;
        }
        .signup-btn {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
        }
        .signup-btn:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>

<form method="POST" action="connexion.php">
    <fieldset>
        <legend>Connexion</legend>

        <!-- Login -->
        <label for="login">Login :</label>
        <input type="text" id="login" name="login" required>

        <!-- Mot de passe -->
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>

        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

    </fieldset>

    <input type="submit" value="Se connecter" class="signup-btn">
</form>

</body>
</html>
