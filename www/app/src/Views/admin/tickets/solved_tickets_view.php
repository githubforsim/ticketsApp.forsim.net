<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Protection contre l'accès direct - Redirection automatique
$currentUrl = $_SERVER['REQUEST_URI'];
if (strpos($currentUrl, '/app/src/Views/admin/tickets/solved_tickets_view.php') !== false) {
    header('Location: /ticketsApp/admin/tickets/solved', true, 301);
    exit;
}

// Vérifie si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  header('Location: /ticketsApp/?view=login');
  exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/app/public/css/sidebar.css" />
    <link rel="stylesheet" href="/app/public/css/tickets/solved_ticket.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <title>Tickets résolues</title>
  </head>
  <body>
    <!--Ajout du menu de navigation-->
    <?php include __DIR__ . '/../admin_sidebar.php'; ?>

    <div class="content">
      <h1>Tickets en cours</h1>
      <div>
        <a class="open_btn" href="/ticketsApp/admin/tickets/open">Ouvertes</a>
        <a class="solved_btn" href="/ticketsApp/admin/tickets/solved">Résolues</a>
        
        <!--Tableau des tickets résolus-->
        <table class="SolvedTable">
          <thead>
            <tr>
              <th class="num">Numéro</th>
              <th class="title">Titre</th>
              <th class="date">Date de résolution</th>
              <th class="type">Type</th>
              <th class="urgence">Urgence</th>
              <th class="demandeur">Demandeur</th>
              <th class="details">Détails</th>
            </tr>
          </thead>
          <tbody id="SolvedTableBody">
            <?php if (isset($tickets)): ?>
            <?php foreach ($tickets as $ticket): ?>
              <tr>    
                <?php $detailsSaveUrl = '/ticketsApp/admin/tickets/solved-details/' . $ticket['ticket_id'] ?>
                <td class="num"><a class="ticket_id" href="<?= $detailsSaveUrl ?>"><?= htmlspecialchars($ticket['ticket_id']) ?></a></td>
                <td class="title"><?= htmlspecialchars($ticket['titre']) ?></td>
                <td class="date"><?= htmlspecialchars($ticket['date_creation']) ?></td>
                <td class="type"><?= htmlspecialchars($ticket['nom_type']) ?></td>
                <td class="urgence"><?= htmlspecialchars($ticket['niveau']) ?></td>
                <td class="demandeur"><?= htmlspecialchars($ticket['username']) ?></td>
                <td class="details"><a href="<?= $detailsSaveUrl ?>"><i class="fas fa-search"></i></a></td>
              </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <script type="text/javascript" src="/app/public/js/admin/solved_ticket.js" defer></script>
  </body>
</html>