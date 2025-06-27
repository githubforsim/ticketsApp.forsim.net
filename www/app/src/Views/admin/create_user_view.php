
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" href="/ticketsApp/app/public/css/admin/create_user.css" />

    <title>Créer un utilisateur</title>
    <?php include 'admin_sidebar.php'; ?>

</head>

    <body>
        <div class="content">
            <!--Formulaire de création d'utilisateur-->
            <form method="POST" action="/ticketsApp/config/routes.php/create_user">
            <?php
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            // Vérifier si l'utilisateur est connecté et a le rôle d'administrateur
            if (isset($_SESSION['username']) && $_SESSION['role'] === 'admin') {
                ?>
                <h1>Créer un utilisateur</h1>

                <label><b>Nom d'utilisateur</b></label>
                <input type="text" placeholder="Entrer le nom d'utilisateur" name="username" required>

                <label><b>Mail</b></label>
                <input type="text" placeholder="Entrer le mail de l'utilisateur" name="mail" required>
                    
                <label><b>Entreprise</b></label>
                <input type="text" placeholder="Entrer le nom de l'entreprise" name="entreprise" required>

                <label><b>Mot de passe</b></label>
                <input type="password" placeholder="Entrer le mot de passe" name="pwd" required>

                <div class = "produits">
                    <label><b>Produits</b></label>
                    <?php foreach ($produits as $produit): ?>
                        <input type="checkbox" name="produit_id[]" value="<?= $produit['produit_id'] ?>">
                        <label class="checkbox"><?= htmlspecialchars($produit['nom_produit']) ?></label><br>
                    <?php endforeach; ?>
                </div>

                <label><b>Rôle</b></label>
                <input type="radio" name="role" value="admin" required> Admin
                <input type="radio" name="role" value="user" required> Utilisateur





                <input type="submit" id='submit' value='Créer' >
            <?php
                if(isset($_GET['erreur'])){
                    $err = $_GET['erreur'];
                if($err==1 || $err==2)
                    echo "<p style='color:red; text-align : center;'>Ce nom d'utilisateur existe déjà</p>";
                    }
                } else {
                    // L'utilisateur n'est pas connecté ou n'est pas administrateur, redirigez-le vers la page de connexion
                    header('Location: /ticketsApp/app/src/Views/login_view.php');
                    exit;
                }?>
            </form>
        </div>
    </body>
</html>