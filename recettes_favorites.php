<?php
session_start();
include 'Donnees.inc.php';

// Récupérer les favoris depuis la session
$favorites = isset($_SESSION['favorites']) ? $_SESSION['favorites'] : [];
$favoriteRecipes = array_filter($Recettes, function($recette) use ($favorites) {
    return in_array($recette['titre'], $favorites);
});

// Gestion des favoris via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipe_id'], $_POST['action'])) {
    $recipeId = $_POST['recipe_id'];
    if ($_POST['action'] === 'remove') {
        $_SESSION['favorites'] = array_filter($_SESSION['favorites'], function($id) use ($recipeId) {
            return $id !== $recipeId;
        });
    }
    // Redirection pour éviter le rechargement du formulaire
    header('Location: recettes_favorites.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recettes favorites</title>
    <style>
        .recette {
            text-align: center;
            margin: 15px 0;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .recette img {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }
        .favorite-btn {
            font-size: 1.5em;
            background: none;
            border: none;
            cursor: pointer;
            color: red; /* Cœur rouge pour les favoris */
        }
    </style>
</head>
<body>
    <h1>Recettes favorites</h1>

    <?php if (!empty($favoriteRecipes)): ?>
        <?php foreach ($favoriteRecipes as $recette): ?>
            <div class="recette">
                <h3><?= htmlspecialchars($recette['titre']) ?></h3>
                <p><strong>Ingrédients :</strong> <?= implode(', ', $recette['index']) ?></p>
                <p><em><?= htmlspecialchars(isset($recette['preparation']) ? $recette['preparation'] : 'Aucune description disponible') ?></em></p>
                <form method="POST" action="recettes_favorites.php">
                    <input type="hidden" name="recipe_id" value="<?= htmlspecialchars($recette['titre']) ?>">
                    <button type="submit" name="action" value="remove" class="favorite-btn">❤️</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucune recette favorite pour le moment.</p>
    <?php endif; ?>
</body>
</html>
