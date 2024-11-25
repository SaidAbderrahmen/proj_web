<?php

session_start();
include 'Donnees.inc.php';

// Initialisation des favoris
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

// Gestion des favoris via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipe_id'], $_POST['action'])) {
    $recipeId = $_POST['recipe_id'];
    if ($_POST['action'] === 'remove') {
        $_SESSION['favorites'] = array_filter($_SESSION['favorites'], function($id) use ($recipeId) {
            return $id !== $recipeId;
        });
    }

    // Mise à jour du profil utilisateur si connecté
    if (isset($_SESSION['user'])) {
        $usersFile = 'users.json';
        $users = json_decode(file_get_contents($usersFile), true);
        $login = $_SESSION['user']['login'];
        $users[$login]['favorites'] = $_SESSION['favorites'];
        file_put_contents($usersFile, json_encode($users));
        $_SESSION['user']['favorites'] = $_SESSION['favorites'];
    }

    // Redirection pour éviter le rechargement du formulaire
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Récupérer les favoris depuis la session
$favorites = $_SESSION['favorites'] ?? [];

// Récupérer les recettes favorites
$favoriteRecipes = array_filter($Recettes, function($recette) use ($favorites) {
    return in_array($recette['titre'], $favorites);
});

// Trier les recettes par ordre alphabétique
usort($favoriteRecipes, function($a, $b) {
    return strcmp($a['titre'], $b['titre']);
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recettes favorites</title>
    <style>
        /* Style général pour centrer */
        .recettes-container {
            display: flex; /* Utilise Flexbox pour l'alignement */
            flex-wrap: wrap; /* Permet aux éléments de passer à la ligne suivante si nécessaire */
            justify-content: center; /* Centrer les éléments horizontalement */
            gap: 15px; /* Espacement entre les éléments */
        }

        /* Style des cartes */
        .recette {
            width: 300px; /* Taille fixe pour chaque carte de recette */
            text-align: center;
            margin: 15px 10px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .recette img {
            max-width: 80%;
            height: auto;
            margin-bottom: 10px;
        }

      

        /* Bouton favoris */
        .favorite-button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: #ff0000;
        }


    </style></head>
<body>
<?php include 'includes/header.php'; ?>

<h1>Recettes favorites</h1>

<div class="recettes-container">
    <?php if (!empty($favoriteRecipes)): ?>
        <?php foreach ($favoriteRecipes as $recette): ?>
            <div class="recette">
                <a href="details_recette.php?id=<?= urlencode($recette['titre']) ?>">
                    <h3><?= htmlspecialchars($recette['titre']) ?></h3>
                    <?php
                    $imageName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($recette['titre'])) . '.jpg';
                    $imagePath = 'assets/Photos/' . $imageName;
                    $imageSrc = file_exists($imagePath) ? $imagePath : 'assets/Photos/default.jpg';
                    ?>
                    <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($recette['titre']) ?>" />
                </a>
                <p><strong>Ingrédients :</strong> <?= implode(", ", $recette['index']) ?></p>
                <form method="POST" action="">
                    <input type="hidden" name="recipe_id" value="<?= htmlspecialchars($recette['titre']) ?>">
                    <button type="submit" name="action" value="remove" class="favorite-btn remove">❤️</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucune recette favorite pour le moment.</p>
    <?php endif; ?>
</div>

</body>
</html>
