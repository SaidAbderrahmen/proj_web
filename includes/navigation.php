<?php

include 'Donnees.inc.php';

// Obtenir les sous-catégories d'un aliment
function getSousCategories($aliment) {
    global $Hierarchie;
    if ($aliment && isset($Hierarchie[$aliment]['sous-categorie'])) {
        return $Hierarchie[$aliment]['sous-categorie'];
    } else {
        return [];  // Retourne un tableau vide si aucune sous-catégorie
    }
}

// Obtenir tous les descendants d'un aliment (y compris l'aliment lui-même)
function getAllDescendants($aliment) {
    global $Hierarchie;
    $descendants = [$aliment];
    if (isset($Hierarchie[$aliment]['sous-categorie'])) {
        foreach ($Hierarchie[$aliment]['sous-categorie'] as $sousCategorie) {
            $descendants = array_merge($descendants, getAllDescendants($sousCategorie));
        }
    }
    return $descendants;
}

// Récupérer les recettes associées à un aliment (en tenant compte des sous-catégories)
function getRecettesParAliment($aliment) {
    global $Recettes;
    $recettes_par_aliment = [];
    $ingredients = getAllDescendants($aliment);
    foreach ($Recettes as $id => $recette) {
        if (isset($recette['index']) && array_intersect($ingredients, $recette['index'])) {
            $recettes_par_aliment[$id] = $recette;
        }
    }
    return $recettes_par_aliment;
}
?>
