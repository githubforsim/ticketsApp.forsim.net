<?php
// 2. CORRECTIONS POUR closed_details_view.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// REDIRECTION COHÉRENTE
if (!isset($_SESSION['username'])) {
    header('Location: /ticketsApp/?view=login');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/app/public/css/tickets/details/closed_details.css?v=2.4" />
    <link rel="stylesheet" href="/app/public/css/home.css?v=2.4">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <title>Détails Tickets</title>
</head>
<body>
    <!--Ajout du menu de navigation-->
    <?php include(__DIR__ . "/../../sidebar.php"); ?>

    <div class="content">
        <!-- URLs MASQUÉES POUR LES BOUTONS DE NAVIGATION -->
        <a class="open_btn" href="/ticketsApp/tickets/closed_details/<?= $ticket['ticket_id'] ?>">Ticket</a>
        <a class="solved_btn" href="/ticketsApp/tickets/message_closed/<?= $ticket['ticket_id'] ?>">Chat</a>
        
        <?php
        if (is_array($ticket)) {
            extract($ticket);
        } else {
            echo "Erreur : les détails du ticket ne peuvent être affichés.";
            exit;
        }    
        ?>
        
        <!--Affichage des données d'un ticket fermé-->
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
                <div id="close_btn">
                    <!-- URL MASQUÉE POUR LE BOUTON FERMER -->
                    <a href="/ticketsApp/tickets/closed" class="submit"><i class="fa fa-times"></i> Fermer</a>
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
                    // URLs MASQUÉES POUR LE TABLEAU D'ÉVÉNEMENTS
                    $detailsSaveUrl = "/ticketsApp/tickets/save_details/" . $evenement['ticket_id'];
                    $chatUrl = "/ticketsApp/tickets/message_closed/" . $evenement['ticket_id'];
                    
                    if ($evenement['statut_id'] == 2) { // Si le statut est "Résolu"
                        $detailsSaveUrl = "/ticketsApp/tickets/solved_save_details/" . $evenement['ticket_id'];
                    } elseif ($evenement['statut_id'] == 3) { // Si le statut est "Fermé"
                        $detailsSaveUrl = "/ticketsApp/tickets/closed_save_details/" . $evenement['ticket_id'];
                    }
                    ?>
                    <td class="num"><?= htmlspecialchars($evenement['ticket_id']) ?></td>
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
                    <td colspan="7">Aucun événement trouvé.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
