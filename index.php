<?php
// Initialiser la navigation hiérarchique
session_start();

include 'Donnees.inc.php';
include 'includes/navigation.php'; // Fichier pour gérer la navigation et récupérer les recettes
include 'includes/fil_ariane.php'; // Générer le fil d'Ariane
include 'includes/header.php';

// Initialisation des favoris
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
        $_SESSION['favorites'] = array_filter($_SESSION['favorites'], function ($id) use ($recipeId) {
            return $id !== $recipeId;
        });
    }
    // Redirection pour éviter le rechargement du formulaire
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Récupérer l'aliment sélectionné
$aliment = isset($_GET['aliment']) && !empty($_GET['aliment']) ? $_GET['aliment'] : NULL;
$recetteId = isset($_GET['id']) && !empty($_GET['id']) ? $_GET['id'] : NULL;

// Si un aliment est sélectionné, on filtre les recettes, sinon afficher toutes les recettes
global $Recettes;
if ($aliment) {
    $recettes = getRecettesParAliment($aliment);
    $sous_categories = getSousCategories($aliment);
    $fil_ariane = generateFilAriane($aliment);
} else {
    $recettes = $Recettes; // Toutes les recettes disponibles
    $sous_categories = [];
    $fil_ariane = ['Tous les aliments'];
}

// Gestion de la recherche
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$wanted = [];
$unwanted = [];
$unknown = [];
$recognized = ['wanted' => [], 'unwanted' => []];
$searchResults = [];
$error = '';

// Validation de la requête
if (!empty($query)) {
    if (substr_count($query, '"') % 2 !== 0) {
        $error = "Problème de syntaxe : nombre impair de double-quotes.";
    } else {
        // Analyse de la requête
        preg_match_all('/"(.*?)"|(\+\S+)|(-\S+)|(\b\S+\b)/', $query, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (!empty($match[1])) { // Aliments entre guillemets
                $wanted[] = $match[1];
            } elseif (!empty($match[2])) { // Aliments souhaités avec "+"
                $wanted[] = substr($match[2], 1);
            } elseif (!empty($match[3])) { // Aliments non souhaités avec "-"
                $unwanted[] = substr($match[3], 1);
            } elseif (!empty($match[4])) { // Aliments sans préfixe
                $wanted[] = $match[4];
            }
        }

        // Classification des ingrédients
        foreach ($wanted as $ingredient) {
            if (isset($Hierarchie[$ingredient])) {
                $recognized['wanted'][] = $ingredient;
            } else {
                $unknown[] = $ingredient;
            }
        }

        foreach ($unwanted as $ingredient) {
            if (isset($Hierarchie[$ingredient])) {
                $recognized['unwanted'][] = $ingredient;
            } else {
                $unknown[] = $ingredient;
            }
        }

        // Recherche des recettes correspondantes
        foreach ($Recettes as $id => $recette) {
            $ingredients = $recette['index'];
            $score = 0;
            $match = true;

            foreach ($recognized['wanted'] as $wanted) {
                if (!in_array($wanted, $ingredients)) {
                    $match = false;
                    break;
                }
                $score++;
            }

            foreach ($recognized['unwanted'] as $unwanted) {
                if (in_array($unwanted, $ingredients)) {
                    $match = false;
                    break;
                }
                $score++;
            }

            if ($match) {
                $searchResults[] = [
                    'recette' => $recette,
                    'score' => $score
                ];
            }
        }

        // Trier les résultats par score décroissant
        usort($searchResults, function ($a, $b) {
            return $b['score'] - $a['score'];
        });
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Cocktails</title>
    <style>
        /* Conteneur principal */
        .container {
            display: flex;
            flex-direction: row;
            width: 100%;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 20%;
            min-width: 200px;
            background-color: #f4f4f4;
            padding: 15px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        /* Contenu principal */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background-color: #ffffff;
        }
        
        .container-r {
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

        .recette-container {
            display: flex;
            /* Utilise Flexbox pour l'alignement */
            flex-wrap: wrap;
            /* Permet aux éléments de passer à la ligne suivante si nécessaire */
            justify-content: center;
            /* Centrer les éléments horizontalement */
            gap: 15px;
            /* Espacement entre les éléments */
        }

        /* Style des cartes */
        .recette {
            width: 150px;
            /* Taille fixe pour chaque carte de recette */
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

        h2 {
            text-align: center;
        }

        .breadcrumb a {
            text-decoration: none;
            color: #007BFF;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .sidebar h3,
        .sidebar h4 {
            font-size: 1.2em;
            margin-bottom: 10px;
            color: #333;
        }

        .sidebar ul {
            list-style-type: none;
            padding-left: 0;
        }

        .sidebar ul li {
            margin: 5px 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #007BFF;
            font-weight: bold;
        }

        .sidebar ul li a:hover {
            text-decoration: underline;
        }

        .favorite-btn {
            font-size: 1.5em;
            background: none;
            border: none;
            cursor: pointer;
        }

        .favorite-btn.add {
            color: black;
        }

        .favorite-btn.remove {
            color: red;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Colonne de gauche -->
        <aside class="sidebar">
            <h3>Aliment courant</h3>
            <nav class="breadcrumb">
                <a href="index.php">Aliment</a> /
                <?php if (!empty($fil_ariane)): ?>
                    <?php foreach ($fil_ariane as $element): ?>
                        <a href="?aliment=<?= urlencode($element) ?>"><?= htmlspecialchars($element) ?></a> /
                    <?php endforeach; ?>
                <?php else: ?>
                    Tous les aliments /
                <?php endif; ?>
            </nav>
            <h4>Sous-catégories</h4>
            <ul>
                <?php if (!empty($sous_categories)): ?>
                    <?php foreach ($sous_categories as $sous_categorie): ?>
                        <li><a href="?aliment=<?= urlencode($sous_categorie) ?>"><?= htmlspecialchars($sous_categorie) ?></a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li><a href="?aliment=Fruit">Fruit</a></li>
                    <li><a href="?aliment=Assaisonnement">Assaisonnement</a></li>
                    <li><a href="?aliment=Légume">Légume</a></li>
                    <li><a href="?aliment=Liquide">Liquide</a></li>
                    <li><a href="?aliment=Noix et graine oléagineuse">Noix et graine oléagineuse</a></li>
                    <li><a href="?aliment=Oeuf">Oeuf</a></li>
                    <li><a href="?aliment=Aliments divers">Aliments divers</a></li>
                    <li><a href="?aliment=Produit laitier">Produit laitier</a></li>
                <?php endif; ?>
            </ul>
        </aside>

        <!-- Contenu principal -->
        <main class="main-content">
            <h2>Liste des Recettes</h2>
            <div class="recette-container">
                <?php if ($recetteId): ?>
                    <!-- Affichage des détails d'une recette -->
                    <?php
                    $recette = array_filter($Recettes, function ($r) use ($recetteId) {
                        return $r['titre'] === $recetteId;
                    });
                    $recette = reset($recette);
                    if ($recette):
                        $imageName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($recette['titre'])) . '.jpg';
                        $imagePath = "assets/Photos/" . $imageName;
                        $imageSrc = file_exists($imagePath) ? $imagePath : "assets/Photos/default.jpg";
                        $isFavorite = in_array($recette['titre'], $_SESSION['favorites']);
                        ?>
                        <div class="container-r">
                            <h1><?= htmlspecialchars($recette['titre']) ?></h1>
                            <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($recette['titre']) ?>" class="recipe-image">

                            <!-- Bouton favoris -->
                            <form action="" method="POST" style="text-align: center; margin-bottom: 20px;">
                                <input type="hidden" name="recipe_id" value="<?= htmlspecialchars($recette['titre']) ?>">
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
                                    <?php foreach ($recette['index'] as $ingredient): ?>
                                        <li><?= htmlspecialchars($ingredient) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <!-- Préparation -->
                            <div class="section">
                                <h2>Préparation :</h2>
                                <p><?= nl2br(htmlspecialchars($recette['preparation'])) ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <p>Recette non trouvée.</p>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Affichage de la liste des recettes -->
                    <!-- Affichage de la liste des recettes -->
                    <?php foreach ($recettes as $recette): ?>
                        <div class="recette">
                            <a href="index.php?id=<?= urlencode($recette['titre']) ?>">
                                <img src="<?= file_exists("assets/Photos/" . str_replace(" ", "_", strtolower($recette['titre'])) . ".jpg") ? "assets/Photos/" . str_replace(" ", "_", strtolower($recette['titre'])) . ".jpg" : "assets/Photos/default.jpg" ?>"
                                    alt="<?= htmlspecialchars($recette['titre']) ?>" style="width:250px; height:auto;">
                            </a>
                            <h3><?= htmlspecialchars($recette['titre']) ?></h3>

                            <!-- Affichage des ingrédients -->
                            <p><strong>Ingrédients :</strong></p>
                            <ul>
                                <?php foreach ($recette['index'] as $ingredient): ?>
                                    <li><?= htmlspecialchars($ingredient) ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <!-- Option d'ajout aux favoris -->
                            <form action="" method="POST">
                                <input type="hidden" name="recipe_id" value="<?= htmlspecialchars($recette['titre']) ?>">
                                <?php if (!in_array($recette['titre'], $_SESSION['favorites'])): ?>
                                    <button class="favorite-btn add" type="submit" name="action" value="add">&#129293;</button>
                                <?php else: ?>
                                    <button class="favorite-btn remove" type="submit" name="action" value="remove">&#10084;</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>
            </div>
        </main>
    </div>

</body>

</html>