<?php

session_start();
include 'Donnees.inc.php';
include 'includes/navigation.php';

if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = isset($_SESSION['user']) ? ($_SESSION['user']['favorites'] ?? []) : [];
}

// Gestion des favoris via POST
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

// Récupération de la requête utilisateur
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

$wanted = [];
$unwanted = [];
$unknown = [];

if ($query !== '') {
    // Validation de la syntaxe de la requête
    if (substr_count($query, '"') % 2 !== 0) {
        $error = "Problème de syntaxe dans votre requête : nombre impair de double-quotes";
    } else {
        // Analyse de la requête
        preg_match_all('/"([^"]+)"|(\+\S+)|(-\S+)|(\b\S+\b)/', $query, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (!empty($match[1])) { // Aliment entre guillemets
                $ingredient = $match[1];
                if (isset($Hierarchie[$ingredient])) {
                    $wanted[] = $ingredient;
                } else {
                    $unknown[] = $ingredient;
                }
            } elseif (!empty($match[2])) { // Aliment souhaité avec "+"
                $ingredient = substr($match[2], 1);
                if (isset($Hierarchie[$ingredient])) {
                    $wanted[] = $ingredient;
                } else {
                    $unknown[] = $ingredient;
                }
            } elseif (!empty($match[3])) { // Aliment non souhaité avec "-"
                $ingredient = substr($match[3], 1);
                if (isset($Hierarchie[$ingredient])) {
                    $unwanted[] = $ingredient;
                } else {
                    $unknown[] = $ingredient;
                }
            } elseif (!empty($match[4])) { // Aliment sans préfixe
                $ingredient = $match[4];
                if (isset($Hierarchie[$ingredient])) {
                    $wanted[] = $ingredient;
                } else {
                    $unknown[] = $ingredient;
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche de Recettes</title>
    <!-- Inclure le fichier CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Styles spécifiques à la page de recherche */
        .search-results {
            max-width: 1200px;
            margin: 20px auto;
        }

        .search-summary {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .search-summary h2 {
            margin-top: 0;
        }

        .error-message {
            color: red;
            text-align: center;
            font-weight: bold;
        }

        .recette-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .recette {
            width: 400px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .recette img {
            width: 100%;
            height: auto;
        }

        .recette h3 {
            font-size: 1.2em;
            margin: 10px 0;
            padding: 0 10px;
        }

        .recette p {
            padding: 0 10px;
            font-size: 2em;
            color: black;
        }

        .favorite-btn {
            font-size: 1.5em;
            background: none;
            border: none;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .favorite-btn.add {
            color: black;
        }

        .favorite-btn.remove {
            color: red;
        }

        /* Styles pour le formulaire de recherche */
        .search-form {
            max-width: 600px;
            margin: 20px auto;
            display: flex;
            gap: 10px;
        }

        .search-form input[type="text"] {
            flex: 1;
            padding: 10px;
            font-size: 1em;
        }

        .search-form button {
            padding: 10px 20px;
            font-size: 1em;
            cursor: pointer;
            border: 2px solid black;
            background-color: #fff;
            border-radius: 5px;
        }

        .search-form button:hover {
            background-color: #f0f0f0;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .recette {
                width: 100%;
            }

            .search-form {
                flex-direction: column;
            }

            .search-form input[type="text"],
            .search-form button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <main class="main-content">
        <h1>Recherche de Recettes</h1>



        <?php if (isset($error)): ?>
            <p class="error-message"><?= htmlspecialchars($error) ?></p>
        <?php else: ?>
            <?php if (!empty($query)): ?>
                <div class="search-summary">
                    <h2>Résumé de la requête</h2>
                    <p><strong>Liste des aliments souhaités :</strong> <?= implode(', ', $wanted) ?: 'Aucun' ?></p>
                    <p><strong>Liste des aliments non souhaités :</strong> <?= implode(', ', $unwanted) ?: 'Aucun' ?></p>
                    <?php if (!empty($unknown)): ?>
                        <p><strong>Éléments non reconnus dans la requête :</strong> <?= implode(', ', $unknown) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php
            if (!empty($wanted) || !empty($unwanted)) {
                // Recherche des recettes correspondantes
                $results = [];
                foreach ($Recettes as $id => $recette) {
                    $ingredientsRecette = $recette['index'];
                    $score = 0;
                    $match = true;

                    foreach ($wanted as $ing) {
                        if (!in_array($ing, $ingredientsRecette)) {
                            $match = false;
                            break;
                        } else {
                            $score++;
                        }
                    }

                    foreach ($unwanted as $ing) {
                        if (in_array($ing, $ingredientsRecette)) {
                            $match = false;
                            break;
                        } else {
                            $score++;
                        }
                    }

                    if ($match) {
                        $results[] = [
                            'recette' => $recette,
                            'score' => $score
                        ];
                    }
                }

                // Tri par score décroissant
                usort($results, function($a, $b) {
                    return $b['score'] - $a['score'];
                });

                if (!empty($results)): ?>
                    <h2>Recettes trouvées</h2>
                    <div class="recette-container">
                        <?php foreach ($results as $result): ?>
                            <?php $recette = $result['recette']; ?>
                            <div class="recette">
                                <?php
                                $imageName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($recette['titre'])) . '.jpg';
                                $imagePath = 'assets/Photos/' . $imageName;
                                $imageSrc = file_exists($imagePath) ? $imagePath : 'assets/Photos/default.jpg';
                                ?>
                                <a href="details_recette.php?id=<?= urlencode($recette['titre']) ?>">
                                    <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($recette['titre']) ?>" />
                                    <h3><?= htmlspecialchars($recette['titre']) ?></h3>
                                </a>
                                <p><strong>Score :</strong> <?= $result['score'] ?></p>
                                <p><strong>Ingrédients :</strong> <?= implode(", ", $recette['index']) ?></p>
                                <!-- Bouton favoris -->
                                <form action="" method="POST">
                                    <input type="hidden" name="recipe_id" value="<?= htmlspecialchars($recette['titre']) ?>">
                                    <?php if (!in_array($recette['titre'], $_SESSION['favorites'])) : ?>
                                        <button class="favorite-btn add" type="submit" name="action" value="add">🤍</button>
                                    <?php else : ?>
                                        <button class="favorite-btn remove" type="submit" name="action" value="remove">❤️</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Aucune recette ne correspond à votre requête.</p>
                <?php endif;
            } else {
                if (!empty($query)) {
                    echo '<p>Problème dans votre requête : recherche impossible</p>';
                } else {
                    echo '<p>Veuillez entrer une requête pour rechercher des recettes.</p>';
                }
            }
            ?>
        <?php endif; ?>
    </main>
</div>

</body>
</html>
