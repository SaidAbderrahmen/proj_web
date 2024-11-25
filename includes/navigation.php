<?php
include 'Donnees.inc.php';

//obtenir les sous-cat d'un aliment
function getSousCategories($aliment) {
    global $Hierarchie;
    if ($aliment && isset($Hierarchie[$aliment]['sous-categorie'])) { //verif si laliment possede une sous-cat
        return $Hierarchie[$aliment]['sous-categorie'];
    } else {
        return [];  // Retourne un tableau vide si aucune sous-cat
    }
}

// Vérifie si l'aliment est passé en paramètre GET
if (isset($_GET['aliment'])) {
    $aliment = $_GET['aliment']; // Récupère la valeur du paramètre 'aliment'
    $recettes = getRecettesParAliment($aliment); // Appelle la fonction pour récupérer les recettes
} else {
    $recettes = []; // Si pas d'aliment sélectionné, pas de recettes à afficher
}

//recup les recettes associees a chaque aliment
function getRecettesParAliment($aliment) {
    global $Recettes;
    $recettes_par_aliment = [];
    foreach ($Recettes as $id => $recette) {
        // verifie si index existe et est bien un tableau
        if (isset($recette['index']) && is_array($recette['index']) && in_array($aliment, $recette['index'])) {
            $index_lower = array_map('strtolower', $recette['index']);
            $recettes_par_aliment[$id] = $recette;
        }
    }
    return $recettes_par_aliment;
}
?>

