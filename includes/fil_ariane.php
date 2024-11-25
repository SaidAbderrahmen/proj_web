<?php
include 'Donnees.inc.php';

//genere le fil d'ariane pour un aliment specifique
//le chemin des categories depuis la racine jusqua laliment selectionne
function generateFilAriane($aliment){
    global $Hierarchie;
    $fil_ariane = [];
    while(isset($Hierarchie[$aliment]['super-categorie'])){//verifie si laliment actuel a une super-cat
        //ajouter chaque categorie au debut du tableau, chemin depuis la racine jusqu'à l'aliment courant
        array_unshift($fil_ariane, $aliment);
        $aliment = $Hierarchie[$aliment]['super-categorie'][0]; //premiere super cat, car un aliment peut avoir plusieurs sup-cat
    }
    array_unshift($fil_ariane, 'Aliment'); //ajoute la racine de la hierarchie
    return $fil_ariane;
}
?>
