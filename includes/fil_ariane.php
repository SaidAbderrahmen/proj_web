<?php

include 'Donnees.inc.php';

// Générer le fil d'Ariane pour un aliment spécifique
function generateFilAriane($aliment){
    global $Hierarchie;
    $fil_ariane = [];
    while($aliment !== 'Aliment' && isset($Hierarchie[$aliment]['super-categorie'][0])) {
        array_unshift($fil_ariane, $aliment);
        $aliment = $Hierarchie[$aliment]['super-categorie'][0];
    }
    array_unshift($fil_ariane, 'Aliment');
    return $fil_ariane;
}
?>
