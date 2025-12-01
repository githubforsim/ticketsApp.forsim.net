<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Vérifie si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  // Redirige vers la page de connexion si l'utilisateur n'est pas connecté ou n'est pas administrateur
  header('Location: /app/src/Views/login_view.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/app/public/css/admin/details_ticket.css" />
    <link rel="stylesheet" href="/app/public/css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />


    <title>Détails Tickets</title>
  </head>
  <body>

    <!--Ajout du menu de navigation-->
    <?php include(__DIR__ ."/../../admin_sidebar.php"); ?>

    <div class="content">
    <a class="open_btn" href="/ticketsApp/admin_details/<?= $ticket['ticket_id'] ?>">Ticket</a>
    <a class="solved_btn" href="/ticketsApp/ticket-message-admin/<?= $ticket['ticket_id'] ?>">Chat</a>
    <?php
      if (is_array($ticket)) 
      {
          extract($ticket);
      } else 
      {
        exit;
      }    
    ?>
    <div id="ticket_details">
      <div id="details">
        
        <div>
          <label class="details" for="ticket">Numéro du ticket:</label>
          <?= htmlspecialchars($ticket['ticket_id']) ?>
        </div>
        <form id="editForm" method="POST" action="/ticketsApp/admin_change_tickets_details">
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
            <?php foreach($attachments as $attachment):                
            ?>
            <li>
              <a href="/<?= $attachment['filename'] ?>" target="_blank">
              <?= basename($attachment['filename']) ?><!--Récupère seulement le nom du fichier-->
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

          <div id="solve">
            <button id="solve_btn" data-ticket-id="<?= $ticket['ticket_id'] ?>">Résoudre ce ticket</button>
          </div>

          <div id="close">
            <button id="closing_btn" data-ticket-id="<?= $ticket['ticket_id'] ?>">Clore ce ticket</button>
          </div>

          <div id="close_btn">
            <a href="/ticketsApp/admin/tickets/open" class="submit"><i class="fa fa-times"></i> Annuler</a>
          </div>

        </div>
      </div>
      <table class="ticketTable">
            <thead>
                <tr>
                    <th class="numTicket">Ticket ID</th>
                    <th class="numEvent">Event ID</th>
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
                    $detailsSaveUrl = "/ticketsApp/admin_ticket_save_details/" . $evenement['ticket_id'];
                    $chatUrl = "/ticketsApp/ticket-message-admin/" . $evenement['ticket_id'];
                    if ($evenement['statut_id'] == 2) { // Si le statut est "Résolu"
                        $detailsSaveUrl = "/ticketsApp/admin_ticket_solved_save_details/" . $evenement['ticket_id'];
                    } elseif ($evenement['statut_id'] == 3) { // Si le statut est "Fermé"
                        $detailsSaveUrl = "/ticketsApp/admin_ticket_closed_save_details/" . $evenement['ticket_id'];
                    }
                    ?>
                    <td class="numTicket"><?= htmlspecialchars($evenement['ticket_id']) ?></td>
                    <td class="numEvent"><?= htmlspecialchars($evenement['evenement_id']) ?></td>
                    <td class="title"><?= htmlspecialchars($evenement['titre']) ?></td>
                    <td class="date"><?= htmlspecialchars($evenement['date_evenement']) ?></td>
                    <td class="type"><?= htmlspecialchars($evenement['event_type']) ?></td>
                    <td class="demandeur"><?= htmlspecialchars($evenement['username']) ?></td>
                    <td class="details"><a href="<?= $detailsSaveUrl ?>"><i class="fas fa-balance-scale"></i></a></td>
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
  </body>

<script type="text/javascript" src="/app/public/js/ajax.js" defer></script>
<script type="text/javascript" src="/app/public/js/admin/details/details_ticket.js" defer></script>

</html>