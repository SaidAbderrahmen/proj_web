<?php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion de Cocktails</title>
    <style>
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #f8f8f8; /* Couleur de fond */
            flex-wrap: wrap;
        }

        .nav-container {
            border: 2px solid black; /* Bordure noire */
            padding: 10px; /* Espacement interne */
            display: inline-block; /* Adapte la taille du cadre au contenu */
        }

        .navbar {
            display: flex;
            gap: 15px; /* Espace entre les liens */
        }

        .navbar a {
            text-decoration: none;
            color: black;
            font-weight: bold;
        }

        /* Style des boutons avec bordure noire */
         .auth-btn, .search-bar button {
            display: inline-block;
            background-color: transparent; 
            color: black; 
            padding: 10px 20px;
            border: 2px solid black; /* Bordure noire */
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: border-color 0.3s;
        }

      
        /* Zone de recherche */
        .search-bar {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .search-bar input {
            padding: 5px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        /* Formulaire de connexion */
        .login {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .connexion-form {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .connexion-form label {
            margin: 0;
         
        }

        .connexion-form input {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            width: 150px;
        }
    </style>
</head>
<body>

<header>
    <!-- Zone de navigation -->
    <div class="nav-container">
        <nav class="navbar">
            <a href="index.php" class="nav-btn">Navigation</a>
            <a href="recettes_favorites.php" class="nav-btn">Recettes ❤️</a>
        </nav>
    </div>

    <!-- Zone de recherche -->
    <form class="search-bar" action="recherche.php" method="GET">
        <span>Rechercher:</span>
        <input type="text" name="query">
        <button type="submit" class="search-bar-btn">🔍</button>
    </form>

    <!-- Zone de connexion -->
    <div class="login">
        <?php if (isset($_SESSION['user'])): ?>
            <span>Bienvenue, <?= htmlspecialchars($_SESSION['user']['login']) ?></span>
            <a href="profile.php" class="auth-btn">Profil</a>
            <a href="deconnexion.php" class="auth-btn">Se déconnecter</a>
        <?php else: ?>
            <form action="connexion.php" method="POST" class="connexion-form">
                <label for="login">Login:</label>
                <input type="text" id="login" name="login" required>
                <label for="password">Mot de passe:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit" class="auth-btn">Connexion</button>
            </form>
            <a href="inscription.php" class="auth-btn">S'inscrire</a>
        <?php endif; ?>
    </div>
</header>
