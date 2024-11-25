<?php

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$usersFile = 'users.json';
$users = json_decode(file_get_contents($usersFile), true);

$login = $_SESSION['user']['login'];
$user = $users[$login];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $errors = [];

    // Nom
    if (!empty($_POST['nom']) && !preg_match("/^[a-zA-ZÀ-ÿ' -]+$/u", $_POST['nom'])) {
        $errors['nom'] = "Le nom est invalide.";
    } else {
        $user['nom'] = $_POST['nom'];
    }

    // Prénom
    if (!empty($_POST['prenom']) && !preg_match("/^[a-zA-ZÀ-ÿ' -]+$/u", $_POST['prenom'])) {
        $errors['prenom'] = "Le prénom est invalide.";
    } else {
        $user['prenom'] = $_POST['prenom'];
    }

    // Sexe
    $user['sexe'] = $_POST['sexe'] ?? '';

    // Date de naissance
    if (!empty($_POST['naissance'])) {
        $date_naissance_dt = new DateTime($_POST['naissance']);
        $today = new DateTime();
        $age = $today->diff($date_naissance_dt)->y;
        if ($age < 18) {
            $errors['naissance'] = "Vous devez avoir au moins 18 ans.";
        } else {
            $user['naissance'] = $_POST['naissance'];
        }
    } else {
        $user['naissance'] = '';
    }

    // Si pas d'erreurs, mise à jour
    if (empty($errors)) {
        $users[$login] = $user;
        file_put_contents($usersFile, json_encode($users));
        $_SESSION['user'] = $user;
        $success = "Profil mis à jour avec succès.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .form-profil {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            width: 400px;
            margin: 20px auto;
        }
        .form-profil input, .form-profil select {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
        }
        .form-profil button {
            padding: 8px 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-profil button:hover {
            background-color: #0056b3;
        }
        .success {
            color: green;
            font-size: 0.9em;
        }
        .error {
            color: red;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

<div class="form-profil">
    <h2>Mon Profil</h2>
    <?php if (isset($success)): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form action="profile.php" method="post">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" placeholder="Nom">

        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" placeholder="Prénom">

        <label for="sexe">Sexe :</label>
        <select id="sexe" name="sexe">
            <option value="">Choisissez</option>
            <option value="h" <?= $user['sexe'] === 'h' ? 'selected' : '' ?>>Homme</option>
            <option value="f" <?= $user['sexe'] === 'f' ? 'selected' : '' ?>>Femme</option>
        </select>

        <label for="naissance">Date de naissance :</label>
        <input type="date" id="naissance" name="naissance" value="<?= htmlspecialchars($user['naissance']) ?>">

        <button type="submit">Mettre à jour</button>
    </form>
    <p><a href="index.php">Retour à l'accueil</a></p>
</div>

</body>
</html>
