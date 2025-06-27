<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifie si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  // Redirige vers la page de connexion si l'utilisateur n'est pas connecté ou n'est pas administrateur
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
    <link rel="stylesheet" href="/ticketsApp/app/public/css/sidebar.css" />
    <link rel="stylesheet" href="/ticketsApp/app/public/css/tickets/closed_ticket.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <title>Tickets fermées</title>
  </head>
  <body>
    <!--Ajout du menu de navigation-->
    <?php include __DIR__ . '/../admin_sidebar.php'; ?>

    <div class="content">
      <h1>Tickets fermées</h1>
      <!--Tableau des tickets fermés-->
      <table class="ClosedTable">
        <thead>
          <tr>
            <th class="num">Numéro</th>
            <th class="title">Titre</th>
            <th class="date">Date de fermeture</th>
            <th class="type">Type</th>
            <th class="urgence">Urgence</th>
            <th class="demandeur">Demandeur</th>
            <th class="details">Détails</th>
          </tr>
        </thead>
        <tbody id="ClosedTableBody">
          <?php if (isset($tickets)): ?>
          <?php foreach ($tickets as $ticket): ?>
            <tr>
              <?php $detailsSaveUrl = '/ticketsApp/admin_closed_details/' . $ticket['ticket_id'] ?>
              <!--htmlspecialchars utilisée pour échapper les caractères spéciaux afin d'éviter les attaques XSS (cross-site scripting) -->
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

    <script type="text/javascript" src="/ticketsApp/app/public/js/admin/closed_ticket.js" defer></script>
  </body>
</html>