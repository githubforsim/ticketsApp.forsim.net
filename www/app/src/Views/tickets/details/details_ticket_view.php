<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Vérifier si l'utilisateur n'est pas connecté
if (!isset($_SESSION['username'])) {
    // Rediriger vers la page de connexion ou une autre page d'accueil appropriée
    header('Location: /app/src/Views/login_view.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/app/public/css/tickets/details/details_ticket.css?v=2.4" />
    <link rel="stylesheet" href="/app/public/css/home.css?v=2.4">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <title>Détails Tickets</title>
</head>
<body>

    <!-- Ajout du menu de navigation -->
    <?php include(__DIR__ . "/../../sidebar.php"); ?>

    <div class="content">
    <a class="open_btn" href="/ticketsApp/config/routes.php/details/<?= $ticket['ticket_id'] ?>">Ticket</a>
    <a class="solved_btn" href="/ticketsApp/config/routes.php/ticket-message/<?= $ticket['ticket_id'] ?>">Chat</a>

    <?php
    if (is_array($ticket)) {
        extract($ticket);
    } else {
        exit;
    }    
    ?>
    <!-- Affichage des données d'un ticket ouvert -->
    <div id="ticket_details">
      <div id="details">

        <div>
          <label class="details" for="ticket">Numéro :</label>
          <?= htmlspecialchars($ticket['ticket_id']) ?>
        </div>
        <form id="editForm" method="POST" action="/ticketsApp/config/routes.php/change_tickets_details">
          <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket['ticket_id']) ?>">
          <div>
            <label class="details" for="titre">Titre :</label>
            <textarea id="titre" name="titre" class="input-field"><?= htmlspecialchars($ticket['titre']) ?></textarea>
          </div>
          
          <div>
            <label class="details" for="description">Description :</label>
            <textarea id="description" name="description" class="input-field"><?= htmlspecialchars($ticket['description']) ?></textarea>
          </div>
        </form>

        <div>
          <label class="details" for="ticket">Date :</label>
          <?= htmlspecialchars($ticket['date_creation']) ?>
        </div>

        <div>
          <label class="details" for="ticket">Produit :</label>
          <?= htmlspecialchars($ticket['nom_produit']) ?>
        </div>

        <div>
          <label class="details" for="ticket">Type :</label>
          <?= htmlspecialchars($ticket['nom_type']) ?>
        </div>

        <div>
          <label class="details" for="ticket">Urgence :</label>
          <?= htmlspecialchars($ticket['niveau']) ?>
        </div>

        <div>
          <label class="details" for="ticket">Demandeur :</label>
          <?= htmlspecialchars($ticket['username']) ?>
        </div>

        <div id="pieces-jointes">
          <label class="details" for="ticket">Pièces jointes :</label>
          <ul>
            <?php foreach($attachments as $attachment): ?>
            <li>
              <a href="/<?= $attachment['filename'] ?>" target="_blank">
              <?= basename($attachment['filename']) ?>
              </a>
              <button class="delete-attachment" data-id="<?= $attachment['attachment_id'] ?>">Retirer</button>
            </li>
            <?php endforeach; ?>
          </ul>        
        </div>
      </div>
      <div class="file-container">
          <input type="file" name="attachment[]" id="attachment" data-id="<?= $ticket['ticket_id'] ?>"/>
          <label class="attachment" for="attachment">Ajouter un fichier</label>
          <ul class="file-list"></ul>    
      </div>

      <div id="buttons">
          <div id="modif">
            <input type="submit" value="Modification fichier">
          </div>

          <div>
            <input type="submit" value="Modification Texte" form="editForm"/>
          </div>

        <div id="close_btn">
          <a href="/app/src/Views/tickets/open_tickets_view.php" class="submit"><i class="fa fa-times"></i>Annuler</a>
        </div>

      </div>
      </div>
      <table class="ticketTable">
            <thead>
                <tr>
                    <th class="numTicket">Ticket ID</th>
                    <th class="title">Titre</th>
                    <th class="date">Date de l'action</th>
                    <th class="type">Action</th>
                    <th class="demandeur">Fait par</th>
                    <th class="details">Comparaison</th>
                    <th class="numEvent">Message</th>
                </tr>
            </thead>
            <tbody id="ticketTableBody">
            <?php if (isset($evenements) && !empty($evenements)): ?>
            <?php foreach ($evenements as $evenement): ?>
                <tr>
                    <?php
                    // Détermine l'URL de redirection en fonction du statut du ticket
                    $detailsSaveUrl = "/ticketsApp/config/routes.php/ticket_save_details/" . $evenement['ticket_id'];
                    $chatUrl = "/ticketsApp/config/routes.php/ticket-message/" . $evenement['ticket_id'];
                    if ($evenement['statut_id'] == 2) { // Si le statut est "Résolu"
                        $detailsSaveUrl = "/ticketsApp/config/routes.php/ticket_solved_save_details/" . $evenement['ticket_id'];
                    } elseif ($evenement['statut_id'] == 3) { // Si le statut est "Fermé"
                        $detailsSaveUrl = "/ticketsApp/config/routes.php/ticket_closed_save_details/" . $evenement['ticket_id'];
                    }
                    ?>
                    <td class="num"><?= htmlspecialchars($evenement['ticket_id']) ?></td>
                    <td class="title"><?= htmlspecialchars($evenement['titre']) ?></td>
                    <td class="date"><?= htmlspecialchars($evenement['date_evenement']) ?></td>
                    <td class="type"><?= htmlspecialchars($evenement['event_type']) ?></td>
                    <td class="demandeur"><?= htmlspecialchars($evenement['username']) ?></td>
                    <td class="details"><a href="<?= $detailsSaveUrl ?>"><i class="fas fa-balance-scale"></a></td>
                    <td class="action"><a href="<?= $chatUrl ?>"><i class="fas fa-envelope"></i></a></td>
                </tr>
            <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Aucun événement trouvé.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    

    <script type="text/javascript" src="/app/public/js/ajax.js" defer></script>
    <script type="text/javascript" src="/app/public/js/tickets/details/details_ticket.js" defer></script>
    <!-- <script type="text/javascript" src="/app/public/js/tickets/details/events_ticket.js" defer></script> -->
  </body>

</html>
