<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérification des champs
    $errors = [];

    // Vérification du login
    if (empty($_POST['login']) || !preg_match("/^[a-zA-Z0-9]+$/", $_POST['login'])) {
        $errors['login'] = "Le login est obligatoire et doit être composé de lettres et de chiffres uniquement.";
    }

    // Vérification du mot de passe
    if (empty($_POST['password'])) {
        $errors['password'] = "Le mot de passe est obligatoire.";
    } else {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);  // Hashage du mot de passe
    }

    // Vérification du nom
    if (!empty($_POST['nom']) && !preg_match("/^[a-zA-ZàéèôùÀÉÈÔÙ' -]+$/", $_POST['nom'])) {
        $errors['nom'] = "Le nom est invalide (lettres, espaces, apostrophes, tirets uniquement).";
    }

    // Vérification du prénom
    if (!empty($_POST['prenom']) && !preg_match("/^[a-zA-ZàéèôùÀÉÈÔÙ' -]+$/", $_POST['prenom'])) {
        $errors['prenom'] = "Le prénom est invalide (lettres, espaces, apostrophes, tirets uniquement).";
    }

    // Vérification de la date de naissance
    if (!empty($_POST['naissance'])) {
        $date_naissance = new DateTime($_POST['naissance']);
        $today = new DateTime();
        $age = $today->diff($date_naissance)->y;
        if ($age < 18) {
            $errors['naissance'] = "L'utilisateur doit avoir au moins 18 ans.";
        }
    }

    // Si pas d'erreur, traitement de l'inscription
    if (empty($errors)) {
        header('Location: accueil.php?inscription=success'); 
        exit(); 
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <style>
        /* Style général de la page */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        form {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        fieldset {
            border: none;
            padding: 10px;
        }

        legend {
            font-size: 1.2em;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"],
        input[type="date"],
        input[type="radio"],
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .signup-btn {
    color: black;  /* Texte noir */
    padding: 10px;
    background-color: #b5e3f5; 
    border: 2px solid black;  /* Encadré noir */
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
    width: 100%;
    margin-top: 10px;
}


        .signup-btn:hover {
            background-color: #4682B4;
        }

        label {
            font-weight: bold;
        }

        .error {
            color: red;
            font-size: 0.9em;
            margin-top: -8px;
            margin-bottom: 10px;
        }

        /* Pour mobile */
        @media (max-width: 768px) {
            body {
                padding: 20px;
            }
        }

      
        .radio-group {
            display: flex;
            gap: 20px; 
            margin-bottom: 10px; 
        }

        .radio-group label {
            display: inline-block;
        }

        /* Bien aligner la date de naissance */
        .date-group {
            margin-top: 10px; 
        }

    </style>
</head>
<body>

<form method="POST" action="inscription.php">

    <fieldset>
        <legend>Inscription</legend>

        <!-- Login -->
        <label for="login">Login :</label>
        <input type="text" id="login" name="login" value="<?= isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '' ?>" required>
        <?php if (isset($errors['login'])): ?>
            <div class="error"><?= $errors['login'] ?></div>
        <?php endif; ?>

        <!-- Mot de passe -->
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>
        <?php if (isset($errors['password'])): ?>
            <div class="error"><?= $errors['password'] ?></div>
        <?php endif; ?>

        <!-- Nom -->
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" pattern="^[a-zA-ZàéèôùÀÉÈÔÙ' -]+$" title="Le nom doit être composé de lettres, apostrophes, tirets et espaces" value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>" />
        <?php if (isset($errors['nom'])): ?>
            <div class="error"><?= $errors['nom'] ?></div>
        <?php endif; ?>

        <!-- Prénom -->
        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" pattern="^[a-zA-ZàéèôùÀÉÈÔÙ' -]+$" title="Le prénom doit être composé de lettres, apostrophes, tirets et espaces" value="<?= isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : '' ?>" />
        <?php if (isset($errors['prenom'])): ?>
            <div class="error"><?= $errors['prenom'] ?></div>
        <?php endif; ?>

        <!-- Sexe -->
        <label>Sexe :</label>
        <div class="radio-group">
            <label for="femme">
                <input type="radio" id="femme" name="sexe" value="f" <?= isset($_POST['sexe']) && $_POST['sexe'] == 'f' ? 'checked' : '' ?> /> Femme
            </label>
            <label for="homme">
                <input type="radio" id="homme" name="sexe" value="h" <?= isset($_POST['sexe']) && $_POST['sexe'] == 'h' ? 'checked' : '' ?> /> Homme
            </label>
        </div>
        <?php if (isset($errors['sexe'])): ?>
            <div class="error"><?= $errors['sexe'] ?></div>
        <?php endif; ?>

        <!-- Date de naissance -->
        <div class="date-group">
            <label for="naissance">Date de naissance :</label>
            <input type="date" id="naissance" name="naissance" value="<?= isset($_POST['naissance']) ? htmlspecialchars($_POST['naissance']) : '' ?>" required />
        </div>
        <?php if (isset($errors['naissance'])): ?>
            <div class="error"><?= $errors['naissance'] ?></div>
        <?php endif; ?>

    </fieldset>

    <input type="submit" value="S'inscrire" class="signup-btn">
</form>

</body>
</html>