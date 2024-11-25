<?php
include 'Donnees.inc.php';

if (isset($_GET['id'])) {
    $titreRecette = urldecode($_GET['id']);
    // Recherche de la recette
    $recetteTrouvee = NULL;
    foreach ($Recettes as $recette) {
        if ($recette['titre'] === $titreRecette) {
            $recetteTrouvee = $recette;
            break;
        }
    }

    if ($recetteTrouvee) {
        var_dump($recetteTrouvee); // Debugging
        // Affichage des détails
        echo "<h1>" . htmlspecialchars($recetteTrouvee['titre']) . "</h1>";

        // Chemin de l'image
        $imagePath = "assets/Photos/" . str_replace(" ", "_", strtolower($recetteTrouvee['titre'])) . ".jpg";
        $imageSrc = file_exists($imagePath) ? $imagePath : "assets/Photos/default.jpg";
        echo "<img src='" . htmlspecialchars($imageSrc) . "' alt='" . htmlspecialchars($recetteTrouvee['titre']) . "' style='width:200px; height:auto;'>";

        // Affichage des ingrédients
        if (isset($recetteTrouvee['index']) && is_array($recetteTrouvee['index'])) {
            echo "<h2>Ingrédients :</h2>";
            echo "<ul>";
            foreach ($recetteTrouvee['index'] as $ingredient) {
                echo "<li>" . htmlspecialchars($ingredient) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Aucun ingrédient disponible.</p>";
        }

        // Affichage de la préparation
        if (isset($recetteTrouvee['preparation']) && !empty($recetteTrouvee['preparation'])) {
            echo "<h2>Préparation :</h2>";
            echo "<p>" . htmlspecialchars($recetteTrouvee['preparation']) . "</p>";
        } else {
            echo "<h2>Préparation :</h2>";
            echo "<p>Aucune description disponible pour la préparation.</p>";
        }
    } else {
        echo "<p>Recette non trouvée.</p>";
    }
} else {
    echo "<p>Aucune recette sélectionnée.</p>";
}
?>
