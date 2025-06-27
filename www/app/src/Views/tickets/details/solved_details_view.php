<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Vérifier si l'utilisateur n'est pas connecté
if (!isset($_SESSION['username'])) {
    // Rediriger vers la page de connexion ou une autre page d'accueil appropriée
    header('Location: /ticketsApp/app/src/Views/login_view.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/ticketsApp/app/public/css/tickets/details/solved_details.css" />
    <link rel="stylesheet" href="/ticketsApp/app/public/css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />


    <title>Détails Tickets</title>
  </head>
  <body>

    <!--Ajout du menu de navigation-->
    <?php include(__DIR__ . "/../../sidebar.php"); ?>

    <div class="content">
    <a class="open_btn" href="/ticketsApp/config/routes.php/solved_details/<?= $ticket['ticket_id'] ?>">Ticket</a>
    <a class="solved_btn" href="/ticketsApp/config/routes.php/ticket-message-solved/<?= $ticket['ticket_id'] ?>">Chat</a>
    <?php
      if (is_array($ticket)) {
        extract($ticket);
      } else {
        // Gérer le cas où $ticket n'est pas un tableau
        echo "Erreur : les détails du ticket ne sont pas un tableau.";
        exit;
      }    
    ?>
    <!--Affichage des données d'un ticket résolu-->
    <div id="ticket_details">
      <div id="details">
        <div>
          <label class="details" for="ticket">Numéro :</label>
          <?= htmlspecialchars($ticket['ticket_id']) ?>
        </div>
        
        <div>
          <label class="details" for="ticket">Titre :</label>
          <?= htmlspecialchars($ticket['titre']) ?>
        </div>

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

        <div>
          <label class="details" for="ticket">Description :</label>
          <?= htmlspecialchars($ticket['description']) ?>
        </div>

        <div>
          <label class="details" for="ticket">Pièces jointes :</label>
          <ul>
            <?php foreach($attachments as $attachment): ?>
            <li>
              <a href="/<?= $attachment['filename'] ?>" target="_blank">
              <?= basename($attachment['filename']) ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>        
        </div>
      </div>

      <div id="buttons">
        
        <div id="open">
          <button id="open_btn" data-ticket-id="<?= $ticket['ticket_id'] ?>">Ré-ouvrir ce ticket</button>
        </div>
        
        <div id="close">
          <button id="closing_btn" data-ticket-id="<?= $ticket['ticket_id'] ?>">Clore ce ticket</button>
        </div>
        
        <div id="close_btn">
          <a href="/ticketsApp/app/src/Views/tickets/solved_tickets_view.php" class="submit"><i class="fa fa-times"></i> Fermer</a>
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
                    <th class="type">Comparaison</th>
                    <th class="type">Message</th>
                </tr>
            </thead>
            <tbody id="ticketTableBody">
            <?php if (isset($evenements) && !empty($evenements)): ?>
            <?php foreach ($evenements as $evenement): ?>
                <tr>
                <?php
                    // Détermine l'URL de redirection en fonction du statut du ticket
                    $detailsSaveUrl = "/ticketsApp/config/routes.php/ticket_save_details/" . $evenement['ticket_id'];
                    $chatUrl = "/ticketsApp/config/routes.php/ticket-message-solved/" . $evenement['ticket_id'];
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
  </body>

<script type="text/javascript" src="/ticketsApp/app/public/js/ajax.js" defer></script>
<script type="text/javascript" src="/ticketsApp/app/public/js/tickets/details/solved_details.js" defer></script>

</html>