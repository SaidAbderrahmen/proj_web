<?php

session_start();
include 'Donnees.inc.php';

if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipe_id'], $_POST['action'])) {
    $recipeId = $_POST['recipe_id'];
    if ($_POST['action'] === 'add') {
        if (!in_array($recipeId, $_SESSION['favorites'])) {
            $_SESSION['favorites'][] = $recipeId;
        }
    } elseif ($_POST['action'] === 'remove') {
        $_SESSION['favorites'] = array_filter($_SESSION['favorites'], function($id) use ($recipeId) {
            return $id !== $recipeId;
        });
    }

    if (isset($_SESSION['user'])) {
        $usersFile = 'users.json';
        $users = json_decode(file_get_contents($usersFile), true);
        $login = $_SESSION['user']['login'];
        $users[$login]['favorites'] = $_SESSION['favorites'];
        file_put_contents($usersFile, json_encode($users));
        $_SESSION['user']['favorites'] = $_SESSION['favorites'];
    }

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

if (isset($_GET['id'])) {
    $titreRecette = urldecode($_GET['id']);
    $recetteTrouvee = NULL;
    foreach ($Recettes as $recette) {
        if ($recette['titre'] === $titreRecette) {
            $recetteTrouvee = $recette;
            break;
        }
    }

    if ($recetteTrouvee) {
        $isFavorite = in_array($recetteTrouvee['titre'], $_SESSION['favorites']);
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title><?= htmlspecialchars($recetteTrouvee['titre']) ?></title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }

                .container {
                    max-width: 800px;
                    margin: 20px auto;
                    padding: 20px;
                    background-color: #fff;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                    border-radius: 10px;
                }

                h1 {
                    text-align: center;
                    margin-bottom: 20px;
                }

                .recipe-image {
                    display: block; 
                    margin: 0 auto; 
                    width: 50%;
                    height: auto;
                    border-radius: 10px;
                    margin-bottom: 20px;
                }

                .favorite-btn {
                    font-size: 1.5em;
                    background: none;
                    border: none;
                    cursor: pointer;
                    outline: none;
                }

                .favorite-btn.add {
                    color: black;
                }

                .favorite-btn.remove {
                    color: red;
                }

                .section {
                    margin-bottom: 20px;
                }

                .section h2 {
                    border-bottom: 2px solid #ddd;
                    padding-bottom: 10px;
                }

                ul {
                    list-style-type: disc;
                    padding-left: 20px;
                }

                p {
                    line-height: 1.6;
                }

                @media (max-width: 600px) {
                    .container {
                        padding: 10px;
                    }
                }
            </style>
        </head>
        <body>
        <?php include 'includes/header.php'; ?>

        <div class="container">
            <h1><?= htmlspecialchars($recetteTrouvee['titre']) ?></h1>
            <?php
            $imageName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($recetteTrouvee['titre'])) . '.jpg';
            $imagePath = 'assets/Photos/' . $imageName;
            $imageSrc = file_exists($imagePath) ? $imagePath : 'assets/Photos/default.jpg';
            ?>
            <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($recetteTrouvee['titre']) ?>" class="recipe-image">

            <!-- Bouton favoris -->
            <form action="" method="POST" style="text-align: center; margin-bottom: 20px;">
                <input type="hidden" name="recipe_id" value="<?= htmlspecialchars($recetteTrouvee['titre']) ?>">
                <?php if (!$isFavorite): ?>
                    <button class="favorite-btn add" type="submit" name="action" value="add">🤍</button>
                <?php else: ?>
                    <button class="favorite-btn remove" type="submit" name="action" value="remove">❤️</button>
                <?php endif; ?>
            </form>

            <!-- Ingrédients -->
            <div class="section">
                <h2>Ingrédients :</h2>
                <ul>
                    <?php
                    $ingredients = explode('|', $recetteTrouvee['ingredients']);
                    foreach ($ingredients as $ingredient) {
                        echo "<li>" . htmlspecialchars($ingredient) . "</li>";
                    }
                    ?>
                </ul>
            </div>

            <!-- Préparation -->
            <div class="section">
                <h2>Préparation :</h2>
                <p><?= nl2br(htmlspecialchars($recetteTrouvee['preparation'])) ?></p>
            </div>
        </div>

        </body>
        </html>
        <?php
    } else {
        echo "<p>Recette non trouvée.</p>";
    }
} else {
    echo "<p>Aucune recette sélectionnée.</p>";
}
?>
