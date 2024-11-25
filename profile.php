<?php
session_start();

// Rediriger l'utilisateur non connecté vers la page d'accueil
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Simuler la base de données (fichier JSON)
$usersFile = 'users.json';

// Charger les utilisateurs
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

// Obtenir l'utilisateur connecté
$currentUser = $_SESSION['user'];
$login = $currentUser['login'];

// Initialiser les données de l'utilisateur
$nom = $currentUser['nom'] ?? '';
$prenom = $currentUser['prenom'] ?? '';
$sexe = $currentUser['sexe'] ?? '';
$date_naissance = $currentUser['date_naissance'] ?? '';

// Mise à jour des données personnelles
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $sexe = $_POST['sexe'];
    $date_naissance = $_POST['date_naissance'];

    // Valider les champs
    if (!empty($nom) && preg_match("/^[a-zA-ZÀ-ÖØ-öø-ÿ'-\s]+$/", $nom) === 0) {
        $error = 'Le nom contient des caractères invalides.';
    } elseif (!empty($prenom) && preg_match("/^[a-zA-ZÀ-ÖØ-öø-ÿ'-\s]+$/", $prenom) === 0) {
        $error = 'Le prénom contient des caractères invalides.';
    } elseif (!empty($date_naissance) && strtotime($date_naissance) >= strtotime('-18 years')) {
        $error = 'Vous devez avoir au moins 18 ans.';
    } else {
        // Mettre à jour l'utilisateur
        $users[$login]['nom'] = $nom;
        $users[$login]['prenom'] = $prenom;
        $users[$login]['sexe'] = $sexe;
        $users[$login]['date_naissance'] = $date_naissance;

        // Sauvegarder les données
        file_put_contents($usersFile, json_encode($users));

        // Mettre à jour la session
        $_SESSION['user'] = $users[$login];

        $success = 'Vos informations ont été mises à jour avec succès.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil utilisateur</title>
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
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <form action="profile.php" method="post">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($nom) ?>" placeholder="Nom">

        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($prenom) ?>" placeholder="Prénom">

        <label for="sexe">Sexe :</label>
        <select id="sexe" name="sexe">
            <option value="">Choisissez</option>
            <option value="Homme" <?= $sexe === 'Homme' ? 'selected' : '' ?>>Homme</option>
            <option value="Femme" <?= $sexe === 'Femme' ? 'selected' : '' ?>>Femme</option>
        </select>

        <label for="date_naissance">Date de naissance :</label>
        <input type="date" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($date_naissance) ?>">

        <button type="submit">Mettre à jour</button>
    </form>
    <p><a href="index.php">Retour à l'accueil</a></p>
</div>

</body>
</html>
