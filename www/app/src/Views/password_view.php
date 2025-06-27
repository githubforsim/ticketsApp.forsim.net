<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" href="/ticketsApp/app/public/css/password_change.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <title>Mot de passe oublié</title>
</head>

    <body>
        <div class="content">

            <div id="change_pwd">
                <a  href="/ticketsApp/app/src/Views/login_view.php"><i class="fas fa-times"></i></a>

                    <h1>Mot de passe oublié</h1>
                    <p>Envoyez un mail à l'adresse frederic.zitta@forsim.net</p>
                    <!--<label><b>Nom d'utilisateur</b></label>
                    <input type="text" placeholder="Entrer le nom d'utilisateur" name="username" required>

                    <div class="button-container">
                        <input type="submit" id='submit' value='Changer de mot de passe' >
                    </div>-->
            </div>
        </div>
    </body>
</html>