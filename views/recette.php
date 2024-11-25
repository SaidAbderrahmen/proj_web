<?php
session_start();

include '../Donnees.inc.php';

// Définition de la fonction getAllRecettes() dans ce fichier
function getAllRecettes() {
    global $Recettes;  // Accéder à la variable globale $Recettes qui est définie dans Donnees.inc.php
    return $Recettes;  // Retourner toutes les recettes
}

// Vérifier si une recette spécifique est demandée via l'ID
if (isset($_GET['id'])) {
    $titreRecette = $_GET['id'];
    // Recherche de la recette
    $recetteTrouvee = null;
    foreach ($Recettes as $recette) {
        if ($recette['titre'] === $titreRecette) {
            $recetteTrouvee = $recette;
            break;
        }
    }

    if ($recetteTrouvee) {
        // Affichage des détails
        echo "<h1>" . htmlspecialchars($recetteTrouvee['titre']) . "</h1>";

        // Chemin de l'image
        $imagePath = "assets/Photos/" . str_replace(" ", "_", strtolower($recetteTrouvee['titre'])) . ".jpg";
        $imageSrc = file_exists($imagePath) ? $imagePath : "assets/Photos/default.jpg";

        // Affichage de la photo avec le lien
        echo "<p>
            <a href='index.php?id=" . urlencode($recetteTrouvee['titre']) . "' class='details-link'>
                <img src='" . htmlspecialchars($imageSrc) . "' alt='" . htmlspecialchars($recetteTrouvee['titre']) . "' class='recipe-image' style='width:100px; height:auto;' />
            </a>
        </p>";

        // Affichage des ingrédients
        if (isset($recetteTrouvee['index']) && is_array($recetteTrouvee['index'])) {
            echo "<h2>Ingrédients :</h2>";
            echo "<p>" . htmlspecialchars(implode(", ", $recetteTrouvee['index'])) . "</p>";
        } else {
            echo "<p>Aucun ingrédient disponible.</p>";
        }

        // Affichage de la préparation
        if (isset($recetteTrouvee['preparation'])) {
            echo "<h2>Préparation :</h2>";
            echo "<p>" . htmlspecialchars($recetteTrouvee['preparation']) . "</p>";
        }
    } else {
        echo "<p>Recette non trouvée.</p>";
    }
} else {
    // Afficher toutes les recettes si aucun id n'est spécifié
    $recettes = getAllRecettes();  // Utiliser la fonction getAllRecettes() pour récupérer toutes les recettes
    echo "<h2>Liste des recettes :</h2>";
    foreach ($recettes as $recette) {
        // Chemin de l'image
        $imagePath = "assets/Photos/" . str_replace(" ", "_", strtolower($recette['titre'])) . ".jpg";
        $imageSrc = file_exists($imagePath) ? $imagePath : "assets/Photos/default.jpg";

        // Affichage de chaque recette sous forme de lien
        echo "<div class='recette'>";
        echo "<p>
            <a href='recette.php?id=" . urlencode($recette['titre']) . "'>
                <img src='" . htmlspecialchars($imageSrc) . "' alt='" . htmlspecialchars($recette['titre']) . "' style='width:100px; height:auto;' />
            </a>
        </p>";
        echo "<h3>" . htmlspecialchars($recette['titre']) . "</h3>";
        echo "</div>";
    }
}
?>
