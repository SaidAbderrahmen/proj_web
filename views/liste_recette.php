<?php
include 'Donnees.inc.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Recettes</title>
    <style>
        /* Style général pour centrer */
        body {
            margin: 0; /* Supprimer les marges par défaut */
            padding: 0;
            box-sizing: border-box;
        }

        /* Conteneur principal pour tout centrer */
        .recettes-container {
            display: flex; /* Activer Flexbox */
            justify-content: center; /* Centrer horizontalement */
            align-items: center; /* Centrer verticalement */
            height: 100vh; /* Hauteur totale de l'écran */
            text-align: center; /* Aligner le texte */
        }

        /* Ajout d'un style pour les recettes */
        .recipe-list {
            display: flex; /* Activer Flexbox */
            flex-wrap: wrap; /* Passer à la ligne si nécessaire */
            gap: 20px; /* Espacement entre les cartes */
            justify-content: center; /* Centrer horizontalement */
        }

        /* Style d'une carte individuelle */
        .recipe-card {
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 10px;
            width: 250px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Images dans les cartes */
        .recipe-card img {
            width: 100%;
            border-radius: 5px;
        }

        /* Bouton favoris */
        .favorite-button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: #ff0000;
        }

        body, html {
        margin: 0;
        padding: 0;
        }

    </style>
</head>
<body>
    <!-- Conteneur principal -->
    <div class="recettes-container">
        <div>
            <h2>Liste des cocktails pour l'aliment : <?= htmlspecialchars($aliment) ?></h2>

            <?php
            $recettes = getRecettesParAliment($aliment); // Assurez-vous que cette fonction existe dans `Donnees.inc.php`

            if (!empty($recettes) && is_array($recettes)) : ?>
                <div class="recipe-list">
                    <?php foreach ($recettes as $recette) : ?>
                        <div class="recipe-card">
                            <h3><?= htmlspecialchars($recette['titre']) ?></h3>
                            <?php
                            $imagePath = "assets/Photos/" . str_replace(" ", "_", strtolower($recette['titre'])) . ".jpg";
                            $imageSrc = file_exists($imagePath) ? $imagePath : 'assets/Photos/default.jpg';
                            ?>
                            <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($recette['titre']) ?>" />
                            <p><strong>Ingrédients :</strong> <?= implode(", ", $recette['index']) ?></p>
                            <button class="favorite-button" onclick="toggleFavorite('<?= htmlspecialchars($recette['titre']) ?>')">♡</button>
                            <?php
                            $recettes = getRecettesParAliment($aliment);
                            var_dump($recettes); // Debugging
                            ?>

                            <a href="details_recette.php?id=<?= urlencode($recette['titre']) ?>">Voir les détails</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p>Aucune recette disponible.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleFavorite(titre) {
            alert(titre + " ajouté/retiré des favoris.");
        }
    </script>
</body>
</html>
