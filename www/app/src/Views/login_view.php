<?php error_log('[DEBUG LOGINVIEW] login_view.php chargé'); ?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" href="/ticketsApp/public/css/login.css" />

    <title>Connexion</title>
</head>

    <body>
        <div class="content">
            <!-- zone de connexion -->
            <form method="POST" action="/ticketsApp/login">
                <h1>Connexion</h1>
                <label><b>Nom d'utilisateur</b></label>
                <input type="text" placeholder="Entrer le nom d'utilisateur" name="username" required>

                <label><b>Mot de passe</b></label>
                <input type="password" placeholder="Entrer le mot de passe" name="pwd" required>

                <input type="submit" id='submit' value='Se connecter' >
                <?php error_log('[DEBUG LOGINVIEW] avant lien password_view.php'); ?>
                <a href="/ticketsApp/?view=password"><i class="fas fa-home fa-lg"></i>Mot de passe oublié ?</a>

                <?php
                if(isset($_GET['erreur'])){
                $err = $_GET['erreur'];
                if($err==1 || $err==2)
                echo "<p style='color:red; text-align : center;'>Utilisateur ou mot de passe incorrect</p>";
                }
                ?>
            </form>
        </div>
    <?php error_log('[DEBUG LOGINVIEW] fin login_view.php'); ?>
    </body>
</html>
