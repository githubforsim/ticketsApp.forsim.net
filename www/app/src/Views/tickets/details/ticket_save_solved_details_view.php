<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Vérifier si l'utilisateur n'est pas connecté
if (!isset($_SESSION['username'])) {
    // Rediriger vers la page de connexion ou une autre page d'accueil appropriée
    header('Location: /src/Views/login_view.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/app/public/css/admin/ticket_save_details.css?v=2.4" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <title>Détails Tickets</title>
  </head>
  <body>

    <!--Ajout du menu de navigation-->
    <?php include(__DIR__ . "/../../sidebar.php"); ?>

    <div class="content">

    <?php
      if (is_array($ticket)) 
      {
          extract($ticket);
      } else 
      {
        exit;
      }    
    ?>

      <div id="ticket_container">
        

        <div class="ticket">
          <div id="details">
          <?php if (!empty($ticketsave)): ?>
            <div>
              <label class="details" for="ticket">Ticket avant ses changements :</label>
            </div>
            <div>
              <label class="details" for="ticket">Numéro :</label>
              <?= htmlspecialchars($ticketsave['ticket_id']) ?>
            </div>

            <div>
              <label class="details" for="ticket">Titre :</label>
              <?= htmlspecialchars($ticketsave['titre']) ?>
            </div>

            <div>
              <label class="details" for="ticket">Date :</label>
              <?= htmlspecialchars($ticketsave['date_creation']) ?>
            </div>

            <div>
              <label class="details" for="ticket">Produit :</label>
              <?= htmlspecialchars($ticketsave['nom_produit']) ?>
            </div>

            <div>
              <label class="details" for="ticket">Type :</label>
              <?= htmlspecialchars($ticketsave['nom_type']) ?>
            </div>

            <div>
              <label class="details" for="ticket">Urgence :</label>
              <?= htmlspecialchars($ticketsave['niveau']) ?>
            </div>

            <div>
              <label class="details" for="ticket">Statut :</label>
              <?= htmlspecialchars($ticketsave['valeur']) ?>
            </div>

            <div>
              <label class="details" for="ticket">Demandeur :</label>
              <?= htmlspecialchars($ticketsave['username']) ?>
            </div>

            <div>
              <label class="details" for="ticket">Description :</label>
              <?= htmlspecialchars($ticketsave['description']) ?>
            </div>

            <?php else: ?>
                <p>Aucune comparaison ne peut être fait actuellement.</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="ticket">
          <div id="details">
            <div>
              <label class="details" for="ticket">Ticket après ses changements :</label>
            </div>
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
              <label class="details" for="ticket">Statut :</label>
              <?= htmlspecialchars($ticket['valeur']) ?>
            </div>

            <div>
              <label class="details" for="ticket">Demandeur :</label>
              <?= htmlspecialchars($ticket['username']) ?>
            </div>

            <div>
              <label class="details" for="ticket">Description :</label>
              <?= htmlspecialchars($ticket['description']) ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>

  <script type="text/javascript" src="/public/js/ajax.js" defer></script>
  <script type="text/javascript" src="/public/js/tickets/details/ticket_save_details.js" defer></script>

</html>
