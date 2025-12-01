<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$username = $_SESSION['username'];

// Vérifier si l'utilisateur est connecté et a le rôle d'administrateur
if (isset($_SESSION['username']) && $_SESSION['role'] === 'admin') {

  $currentPage = $_SERVER['REQUEST_URI'];
  // Définir les URLs des pages de la barre latérale - MASQUER LES CHEMINS
  $homeUrl = "/ticketsApp/?view=admin";
  $newTicketUrl = "/ticketsApp/admin/tickets/create";
  $openTicketsUrl = "/ticketsApp/admin/tickets/open";
  $closedTicketsUrl = "/ticketsApp/admin/tickets/closed";
  $solvedTicketsUrl = "/ticketsApp/admin/tickets/solved";
  $newUserUrl = "/ticketsApp/admin/users/create";
  $usersUrl = "/ticketsApp/admin/users";
  
  // Fonction pour vérifier si l'URL est active
  function isActive($url, $currentPage) {
    return ($url === $currentPage) ? "active" : "";
  }
} else {
  // Redirige vers la racine avec paramètre login
  header('Location: /ticketsApp/?view=login');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="/app/public/css/sidebar.css?v=2.4" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
  </head>

  <body>
  <div class="sidebar">
    <header>
      <p><?=$username?></p>

      <div class="produit">
      <select id="produit_select" name="produit" required>
          <option value="" disabled>Choisissez un produit</option>
    <?php 
        if (isset($produits) && !empty($produits)) {
            $firstProductSelected = false;
            foreach ($produits as $produit): 
                $selected = !$firstProductSelected ? 'selected' : '';
                $firstProductSelected = true;
    ?>
          <option value="<?= $produit['produit_id'] ?>" <?= $selected ?>><?= htmlspecialchars($produit['nom_produit']) ?></option>
    <?php endforeach; } ?>

        </select>
      </div>
    </header>

    <ul>
      <li class="<?= isActive($homeUrl, $currentPage) ?>">
        <a href="<?= $homeUrl ?>"><i class="fas fa-home fa-lg"></i>Accueil</a>
      </li>
      <li class="<?= isActive($newTicketUrl, $currentPage) ?>">
        <a href="<?= $newTicketUrl ?>"><i class="fas fa-comment-medical fa-lg"></i>Nouveau ticket</a>
    </li>
      <li class="<?php echo (isActive($openTicketsUrl, $currentPage) || isActive($solvedTicketsUrl, $currentPage)) ? 'active' : '' ?>">
        <a href="<?= $openTicketsUrl ?>"><i class="fas fa-comments fa-lg"></i>Tickets en cours</a>
      </li>
      <li class="<?= isActive($closedTicketsUrl, $currentPage) ?>">
        <a href="<?= $closedTicketsUrl ?>"><i class="fas fa-comment-slash fa-lg"></i>Tickets fermées</a>
      </li>
      <li class="<?= isActive($newUserUrl, $currentPage) ?>">
        <a href="<?= $newUserUrl ?>"><i class="fas fa-user fa-lg"></i>Nouvel utilisateur</a>
      </li>
      <li class="<?= isActive($usersUrl, $currentPage) ?>">
        <a href="<?= $usersUrl ?>"><i class="fas fa-users fa-lg"></i>Utilisateurs</a>
      </li>
      <li>
        <a href="/ticketsApp/logout"><i class="fas fa-sign-out-alt fa-lg"></i>Déconnexion</a>    
      </li>
    </ul>
  </div>
  </body>
  <script type="text/javascript" src="/app/public/js/ajax.js" defer></script>
  <script type="text/javascript" src="/app/public/js/admin/sidebar.js" defer></script>
</html>