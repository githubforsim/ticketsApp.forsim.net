<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Vérifie si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    // Redirige vers la racine avec paramètre login au lieu du chemin complet
    header('Location: /ticketsApp/?view=login');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/app/public/css/tickets/open_ticket.css?v=2.4">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Espace Administration</title>
</head>
<body>

    <!-- Ajout du menu de navigation -->
    <?php include 'admin_sidebar.php'; ?>

    <div class="content">
        <img src="/app/src/upload/logo_FORSIM124.png" alt="Logo Forsim" style="height: 50px; margin-bottom: 20px;" />
        <h1>Bienvenue <?= htmlspecialchars($_SESSION['username']) ?> sur l'espace administration !</h1>

        <!-- Contenu pour afficher les tickets ouverts -->
        <h2>Historique d'évènements</h2>

        <!-- Tableau des tickets ouverts -->
        <table class="ticketTable">
            <thead>
                <tr>
                    <th class="numTicket">Ticket ID</th>
                    <th class="numEvent">Event ID</th>
                    <th class="title">Titre</th>
                    <th class="date">Date de l'action</th>
                    <th class="type">Action</th>
                    <th class="demandeur">Fait par</th>
                    <th class="comparaison">Comparaison</th>
                    <th class="message">Message</th>
                </tr>
            </thead>
            <tbody id="ticketTableBody">
            <?php if (isset($evenements) && !empty($evenements)): ?>
            <?php foreach ($evenements as $evenement): ?>
                <tr>
                    <?php
                    // Utiliser les nouvelles routes masquées
                    $detailsUrl = "/ticketsApp/admin/tickets/details/" . $evenement['ticket_id'];
                    $detailsSaveUrl = "/ticketsApp/admin/tickets/save-details/" . $evenement['ticket_id'];
                    $chatUrl = "/ticketsApp/admin/tickets/chat/" . $evenement['ticket_id'];
                    
                    // URL pour Event ID - utilise l'ID de l'événement au lieu du ticket
                    $eventSaveUrl = "/ticketsApp/admin/tickets/save-details/" . $evenement['evenement_id'];
                    $eventDetailsUrl = "/ticketsApp/admin/events/details/" . $evenement['evenement_id'];

                    if ($evenement['statut_id'] == 2) { // Si le statut est "Résolu"
                        $detailsUrl = "/ticketsApp/admin/tickets/solved-details/" . $evenement['ticket_id'];
                        $detailsSaveUrl = "/ticketsApp/admin/tickets/solved-save-details/" . $evenement['ticket_id'];
                       $eventDetailsUrl = "/ticketsApp/admin/events/details/" . $evenement['evenement_id'];
                        $chatUrl = "/ticketsApp/admin/tickets/solved-chat/" . $evenement['ticket_id'];
                    } elseif ($evenement['statut_id'] == 3) { // Si le statut est "Fermé"
                        $detailsUrl = "/ticketsApp/admin/tickets/closed-details/" . $evenement['ticket_id'];
                        $detailsSaveUrl = "/ticketsApp/admin/tickets/closed-save-details/" . $evenement['ticket_id'];
                        $eventSaveUrl = "/ticketsApp/admin/tickets/closed-save-details/" . $evenement['evenement_id'];
                        $chatUrl = "/ticketsApp/admin/tickets/closed-chat/" . $evenement['ticket_id'];
                    }
                    ?>
                    <td class="num"><a class="ticket_id" href="<?= $detailsUrl ?>"><?= htmlspecialchars($evenement['ticket_id']) ?></a></td>
                    <td class="num"><a class="event_id" href="<?= $eventDetailsUrl ?>"><?= htmlspecialchars($evenement['evenement_id']) ?></a></td>
                    <td class="title"><?= htmlspecialchars($evenement['titre']) ?></td>
                    <td class="date"><?= htmlspecialchars($evenement['date_evenement']) ?></td>
                    <td class="type"><?= htmlspecialchars($evenement['event_type']) ?></td>
                    <td class="demandeur"><?= htmlspecialchars($evenement['username']) ?></td>
                    <td class="action"><a href="<?= $detailsSaveUrl ?>"><i class="fas fa-balance-scale"></i></a></td>
                    <td class="action"><a href="<?= $chatUrl ?>"><i class="fas fa-envelope"></i></a></td>
                </tr>
            <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">Aucun événement trouvé.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script type="text/javascript" src="/app/public/js/ajax.js" defer></script>
    <script type="text/javascript" src="/app/public/js/admin/admin_view.js" defer></script>
</body>
</html>