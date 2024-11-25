<?php
include 'Donnees.inc.php';

// Récupération de la requête utilisateur
$query = isset($_GET['query']) ? htmlspecialchars(trim($_GET['query'])) : '';

// Initialisation des listes
$wanted = [];
$unwanted = [];
$unknown = [];
$recognized = ['wanted' => [], 'unwanted' => []];

// Validation de la syntaxe de la requête
if (substr_count($query, '"') % 2 !== 0) {
    die('<p>Problème de syntaxe dans votre requête : nombre impair de double-quotes</p>');
}

// Analyse de la requête
preg_match_all('/"(.*?)"|(\+\S+)|(-\S+)|(\b\S+\b)/', $query, $matches, PREG_SET_ORDER);

foreach ($matches as $match) {
    if (!empty($match[1])) { // Aliment entre double-quotes
        $wanted[] = $match[1];
    } elseif (!empty($match[2])) { // Aliment souhaité avec "+"
        $wanted[] = substr($match[2], 1);
    } elseif (!empty($match[3])) { // Aliment non souhaité avec "-"
        $unwanted[] = substr($match[3], 1);
    } elseif (!empty($match[4])) { // Aliment sans préfixe
        $wanted[] = $match[4];
    }
}

// Classification des ingrédients dans la hiérarchie ou en "inconnus"
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

// Affichage de l'analyse
echo '<h1>Résultats de recherche</h1>';
echo '<h2>Résumé de la requête</h2>';
echo '<p><strong>Liste des aliments souhaités :</strong> ' . implode(', ', $recognized['wanted']) . '</p>';
echo '<p><strong>Liste des aliments non souhaités :</strong> ' . implode(', ', $recognized['unwanted']) . '</p>';
if (!empty($unknown)) {
    echo '<p><strong>Éléments inconnus dans la requête :</strong> ' . implode(', ', $unknown) . '</p>';
}

// Si aucune liste valide n'est fournie
if (empty($recognized['wanted']) && empty($recognized['unwanted'])) {
    die('<p>Problème dans votre requête : recherche impossible</p>');
}

// Recherche des recettes correspondantes
echo '<h2>Recettes trouvées</h2>';
$results = [];
foreach ($Recettes as $id => $recette) {
    $ingredients = $recette['index'];
    $score = 0;
    $match = true;

    // Vérification des aliments souhaités
    foreach ($recognized['wanted'] as $wanted) {
        if (!in_array($wanted, $ingredients)) {
            $match = false;
            break;
        }
        $score++;
    }

    // Vérification des aliments non souhaités
    foreach ($recognized['unwanted'] as $unwanted) {
        if (in_array($unwanted, $ingredients)) {
            $match = false;
            break;
        }
        $score++;
    }

    if ($match) {
        $results[] = [
            'recette' => $recette,
            'score' => $score
        ];
    }
}

// Tri des résultats par score décroissant
usort($results, function ($a, $b) {
    return $b['score'] - $a['score'];
});

// Affichage des recettes
if (!empty($results)) {
    foreach ($results as $result) {
        $recette = $result['recette'];
        echo '<div>';
        echo '<h3>' . htmlspecialchars($recette['titre']) . ' (Score : ' . $result['score'] . ')</h3>';
        echo '<p>Ingrédients : ' . implode(', ', $recette['index']) . '</p>';
        echo '<p><em>' . htmlspecialchars($recette['preparation']) . '</em></p>';
        echo '</div>';
    }
} else {
    echo '<p>Aucune recette ne correspond à votre requête.</p>';
}

session_start(); // Démarrage de la session pour stocker les favoris

// Exemple d'affichage pour chaque recette
foreach ($Recettes as $id => $recette) {
    $isFavorite = isset($_SESSION['favorites']) && in_array($id, $_SESSION['favorites']);

    echo '<div>';
    echo '<h3>' . htmlspecialchars($recette['titre']) . '</h3>';
    echo '<p>Ingrédients : ' . implode(', ', $recette['index']) . '</p>';
    echo '<form method="POST" action="favorites.php">';
    echo '<input type="hidden" name="recipe_id" value="' . $id . '">';
    echo '<button type="submit" name="action" value="' . ($isFavorite ? 'remove' : 'add') . '">';
    echo $isFavorite ? '❤️' : '🤍'; // Cœur plein ou vide
    echo '</button>';
    echo '</form>';
    echo '</div>';
}
?>
