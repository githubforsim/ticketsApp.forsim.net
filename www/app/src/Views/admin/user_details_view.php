<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Vérifie si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  // Redirige vers la page de connexion si l'utilisateur n'est pas connecté ou n'est pas administrateur
  header('Location: /src/Views/login_view.php');
  exit;
}

// Vérifier que la variable $user existe et n'est pas vide
if (!isset($user) || $user === false) {
    echo "<div style='color:red; text-align:center; margin-top:20px;'>Utilisateur non trouvé ou erreur lors du chargement des données utilisateur.</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" href="/app/public/css/admin/user_details.css?v=2.4" />

    <title>Détails</title>
  </head>

    <body>
        <?php include 'admin_sidebar.php'; ?>
        <div class="content">
            <div id="user_details">
                <div id ="details">
                <div>
                    <label class="details" for="user">Nom d'utilisateur :</label>
                    <?= isset($user['username']) ? htmlspecialchars($user['username']) : '' ?>
                </div>

                <div>
                    <label class="details" for="user">Mail :</label>
                    <?= isset($user['mail']) ? htmlspecialchars($user['mail']) : '' ?>
                </div>

                <div>
                    <label class="details" for="user">Entreprise :</label>
                    <?= isset($user['entreprise']) ? htmlspecialchars($user['entreprise']) : '' ?>
                </div>

                <div>
                    <label class="details" for="user">Rôle :</label>
                    <?= isset($user['role']) ? htmlspecialchars($user['role']) : '' ?>
                </div>

                <div class="produits">
                    <label class="details" for="user">Produits :</label>
                    <?php if (!isset($produits) || empty($produits)): ?>
                        <p>Aucun produit associé à cet utilisateur.</p>
                    <?php else: ?>
                            <?php foreach ($produits as $produit): ?>
                                <?= htmlspecialchars($produit['nom_produit']) ?>
                            <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
             <form method="POST" action="/ticketsApp/change_password" style="text-align: center; margin-top: 30px;">
                    <input type="hidden" name="username" value="<?= isset($user['username']) ? htmlspecialchars($user['username']) : '' ?>">
                    
                    <label for="new_password">Nouveau mot de passe:</label>
                    <input type="password" name="new_password" required>
                    
                    <label for="confirm_password">Confirmer le nouveau mot de passe:</label>
                    <input type="password" name="confirm_password" required>
                    <?php
                      
                        if(isset($_GET['erreur'])){
                            $err = $_GET['erreur'];
                            if($err==1){
                                echo "<p style='color:red; text-align:center; padding:20px;'>Les mots de passe ne correspondent pas</p>";
                            }
                        }
                        if(isset($_GET['success'])){
                            $success = $_GET['success'];
                            if($success==1){
                                echo "<p style='color:green; text-align:center; padding:20px;'>Mot de passe modifié avec succès !</p>";
                            }
                        }
                    ?>
                    
                    <input type="submit" value="Changer le mot de passe">
                </form>
            </div> 
        </div>
    </body>
</html>