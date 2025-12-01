<?php
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }
    // Vérifier si l'utilisateur n'est pas connecté
    if (!isset($_SESSION['username'])) {
        // Rediriger vers la racine avec paramètre de vue login
        header('Location: /ticketsApp/?view=login');
        exit();
    }

    $username = $_SESSION['username'];
    
    // Récupérer l'URL de la page courante
    $currentPage = $_SERVER['REQUEST_URI'];

    // Définir les URLs des onglets - MASQUER LES CHEMINS
    $homeUrl = "/ticketsApp/?view=user";
    $newTicketUrl = "/ticketsApp/tickets/create";
    $openTicketsUrl = "/ticketsApp/tickets/open";
    $solvedTicketsUrl = "/ticketsApp/tickets/solved";
    $closedTicketsUrl = "/ticketsApp/tickets/closed";

    // Fonction pour vérifier si l'URL est active
    function isActive($url, $currentPage) {
        return ($url === $currentPage) ? "active" : "";
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
      <p><?=$username ?></p>
    
    <div class="produit">
    <select id="produit_select" name="produit" required>
          <option value="" disabled>Choisissez un produit</option>
          <?php 
          if (isset($produits) && count($produits) > 0):
              foreach ($produits as $index => $produit): ?>
                  <option value="<?= $produit['produit_id'] ?>" <?= $index === 0 ? 'selected' : '' ?>><?= $produit['nom_produit'] ?></option>
              <?php endforeach; 
          endif; ?>
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
    <li>
        <a href="/ticketsApp/logout"><i class="fas fa-sign-out-alt fa-lg"></i>Déconnexion</a>
    </li>
</ul>

  </div>
  </body>
  <script type="text/javascript" src="/app/public/js/ajax.js" defer></script>
  <script type="text/javascript" src="/app/public/js/sidebar.js" defer></script>
</html>