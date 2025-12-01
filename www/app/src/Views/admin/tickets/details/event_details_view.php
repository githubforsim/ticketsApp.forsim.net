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

    <title>Détails Événement #<?= htmlspecialchars($event['evenement_id']) ?></title>
  </head>
  <body>

    <!--Ajout du menu de navigation-->
    <?php include(__DIR__ ."/../../admin_sidebar.php"); ?>

    <div class="content">
    <a class="open_btn" href="/ticketsApp/admin/tickets/details/<?= htmlspecialchars($event['ticket_id']) ?>">Voir Ticket</a>
    <a class="solved_btn" href="/ticketsApp/admin/tickets/chat/<?= htmlspecialchars($event['ticket_id']) ?>">Chat</a>
    
    <div id="ticket_details">
      <div id="details">
        
        <div>
          <label class="details" for="event">ID Événement:</label>
          <?= htmlspecialchars($event['evenement_id']) ?>
        </div>
        
        <div>
          <label class="details" for="ticket">ID Ticket associé:</label>
          <a href="/ticketsApp/admin/tickets/details/<?= htmlspecialchars($event['ticket_id']) ?>">
            <?= htmlspecialchars($event['ticket_id']) ?>
          </a>
        </div>
        
        <div>
          <label class="details" for="titre">Titre du ticket:</label>
          <?= htmlspecialchars($event['titre']) ?>
        </div>
        
        <div>
          <label class="details" for="description">Description:</label>
          <div class="description-content"><?= nl2br(htmlspecialchars($event['description'])) ?></div>
        </div>
        
        <div>
          <label class="details" for="date">Date de l'événement:</label>
          <?= htmlspecialchars($event['date_evenement']) ?>
        </div>

        <div>
          <label class="details" for="statut">Statut de l'événement:</label>
          <?php
          $statut_class = '';
          $statut_text = '';
          switch($event['statut_evenement_id']) {
              case 1:
                  $statut_class = 'status-open';
                  $statut_text = 'Ouvert';
                  break;
              case 2:
                  $statut_class = 'status-solved';
                  $statut_text = 'Résolu';
                  break;
              case 3:
                  $statut_class = 'status-closed';
                  $statut_text = 'Fermé';
                  break;
              case 7:
                  $statut_class = 'status-modified';
                  $statut_text = 'Modifié';
                  break;
              case 8:
                  $statut_class = 'status-message';
                  $statut_text = 'Message';
                  break;
              default:
                  $statut_class = 'status-unknown';
                  $statut_text = 'Statut ' . $event['statut_evenement_id'];
          }
          ?>
          <span class="status-badge <?= $statut_class ?>"><?= $statut_text ?></span>
        </div>

        <div>
          <label class="details" for="user">Utilisateur:</label>
          <?= htmlspecialchars($event['username']) ?>
        </div>

        <?php if (isset($event['produit_id'])): ?>
        <div>
          <label class="details" for="produit">ID Produit:</label>
          <?= htmlspecialchars($event['produit_id']) ?>
        </div>
        <?php endif; ?>

        <!-- Informations sur le ticket associé -->
        <?php if (isset($ticket) && $ticket): ?>
        <hr style="margin: 20px 0; border: 1px solid #ddd;">
        <h3 style="color: #333; margin-bottom: 15px;">Détails du ticket associé</h3>
        
        <div>
          <label class="details" for="ticket_titre">Titre du ticket:</label>
          <?= htmlspecialchars($ticket['titre']) ?>
        </div>
        
        <div>
          <label class="details" for="ticket_desc">Description du ticket:</label>
          <div class="description-content"><?= nl2br(htmlspecialchars($ticket['description'])) ?></div>
        </div>
        
        <div>
          <label class="details" for="ticket_date">Date de création:</label>
          <?= htmlspecialchars($ticket['date_creation']) ?>
        </div>

        <?php if (isset($ticket['valeur'])): ?>
        <div>
          <label class="details" for="ticket_statut">Statut du ticket:</label>
          <?= htmlspecialchars($ticket['valeur']) ?>
        </div>
        <?php endif; ?>

        <div>
          <label class="details" for="ticket_urgence">Urgence:</label>
          <?= htmlspecialchars($ticket['niveau'] ?? 'Non définie') ?>
        </div>

        <div>
          <label class="details" for="ticket_type">Type:</label>
          <?= htmlspecialchars($ticket['nom_type'] ?? 'Non défini') ?>
        </div>

        <div>
          <label class="details" for="ticket_produit">Produit:</label>
          <?= htmlspecialchars($ticket['nom_produit'] ?? 'Non défini') ?>
        </div>

        <div>
          <label class="details" for="ticket_demandeur">Demandeur:</label>
          <?= htmlspecialchars($ticket['username'] ?? 'Non défini') ?>
        </div>
        <?php endif; ?>

        <!-- Pièces jointes -->
        <?php if (isset($attachments) && !empty($attachments)): ?>
        <div id="pieces-jointes">
          <label class="details" for="attachments">Pièces jointes:</label>
          <ul>
            <?php foreach($attachments as $attachment): ?>
            <li>
              <a href="/ticketsApp/<?= htmlspecialchars($attachment['filename']) ?>" target="_blank">
              <?= htmlspecialchars(basename($attachment['filename'])) ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>        
        </div>
        <?php endif; ?>

        <div id="buttons">
        
          
          <div id="close_btn">
            <a href="javascript:history.back()" class="submit">
              <i class="fa fa-arrow-left"></i> Retour
            </a>
          </div>
        </div>
      </div>
    </div>
    </div>

    <style>
      .description-content {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        margin-top: 5px;
        word-wrap: break-word;
        max-width: 100%;
      }

      .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
        color: white;
        display: inline-block;
      }

      .status-open { background: #28a745; }
      .status-solved { background: #17a2b8; }
      .status-closed { background: #dc3545; }
      .status-modified { background: #ffc107; color: #212529; }
      .status-message { background: #6c757d; }
      .status-unknown { background: #343a40; }

      #pieces-jointes ul {
        margin-top: 10px;
      }

      #pieces-jointes li {
        margin-bottom: 8px;
        padding: 5px 0;
        border-bottom: 1px solid #eee;
      }

      #pieces-jointes li:last-child {
        border-bottom: none;
      }

      #pieces-jointes a {
        color: #007bff;
        text-decoration: none;
      }

      #pieces-jointes a:hover {
        text-decoration: underline;
      }

      #buttons {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        flex-wrap: wrap;
      }

      .submit {
        background: #007bff;
        color: white;
        padding: 10px 15px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-weight: bold;
        border: none;
        cursor: pointer;
      }

      .submit:hover {
        background: #0056b3;
        color: white;
        text-decoration: none;
      }

      h3 {
        color: #333;
        margin: 15px 0;
        padding-bottom: 5px;
        border-bottom: 2px solid #007bff;
      }
    </style>
  </body>

<script type="text/javascript" src="/app/public/js/ajax.js" defer></script>

</html>