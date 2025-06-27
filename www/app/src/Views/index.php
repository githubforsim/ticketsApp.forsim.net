<?php
// 1. CORRECTIONS POUR index.php (page d'accueil)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/ticketsApp';
$path = str_replace($basePath, '', $requestUri);
$path = strtok($path, '?');
$path = trim($path, '/');

// Si c'est une route admin masquée, rediriger vers routes.php
if (!empty($path)) {
    switch ($path) {
        // AJOUTER LES ROUTES UTILISATEUR MANQUANTES
        case 'tickets/open':
            $_SERVER['PATH_INFO'] = '/tickets/open';
            include __DIR__ . '/config/routes.php';
            exit;
        
        case 'tickets/solved':
            $_SERVER['PATH_INFO'] = '/tickets/solved';
            include __DIR__ . '/config/routes.php';
            exit;
        
        case 'tickets/closed':
            $_SERVER['PATH_INFO'] = '/tickets/closed';
            include __DIR__ . '/config/routes.php';
            exit;
        
        case 'tickets/create':
            $_SERVER['PATH_INFO'] = '/tickets/create';
            include __DIR__ . '/config/routes.php';
            exit;
        
        // Routes admin existantes...
        case 'admin/tickets/open':
            $_SERVER['PATH_INFO'] = '/admin/tickets/open';
            include __DIR__ . '/config/routes.php';
            exit;
        
        case 'admin/tickets/solved':
            $_SERVER['PATH_INFO'] = '/admin/tickets/solved';
            include __DIR__ . '/config/routes.php';
            exit;
        
        case 'admin/tickets/closed':
            $_SERVER['PATH_INFO'] = '/admin/tickets/closed';
            include __DIR__ . '/config/routes.php';
            exit;
        
        case 'admin/tickets/create':
            $_SERVER['PATH_INFO'] = '/admin/tickets/create';
            include __DIR__ . '/config/routes.php';
            exit;
        
        case 'admin/users':
            $_SERVER['PATH_INFO'] = '/admin/users';
            include __DIR__ . '/config/routes.php';
            exit;
        
        case 'admin/users/create':
            $_SERVER['PATH_INFO'] = '/admin/users/create';
            include __DIR__ . '/config/routes.php';
            exit;
        
        case 'logout':
            $_SERVER['PATH_INFO'] = '/logout';
            include __DIR__ . '/config/routes.php';
            exit;
        
        default:
            // Routes avec ID pour utilisateurs
            if (preg_match('#^tickets/([^/]+)/(\d+)$#', $path, $matches)) {
                $action = $matches[1];
                $id = $matches[2];
                $_SERVER['PATH_INFO'] = "/tickets/{$action}/{$id}";
                include __DIR__ . '/config/routes.php';
                exit;
            }
            
            // Routes avec ID pour admin
            if (preg_match('#^admin/tickets/([^/]+)/(\d+)$#', $path, $matches)) {
                $action = $matches[1];
                $id = $matches[2];
                $_SERVER['PATH_INFO'] = "/admin/tickets/{$action}/{$id}";
                include __DIR__ . '/config/routes.php';
                exit;
            }

            // NOUVELLE ROUTE POUR LES ÉVÉNEMENTS
            if (preg_match('#^admin/events/([^/]+)/(\d+)$#', $path, $matches)) {
                $action = $matches[1];
                $id = $matches[2];
                $_SERVER['PATH_INFO'] = "/admin/events/{$action}/{$id}";
                include __DIR__ . '/config/routes.php';
                exit;
            }
            break;
    }
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
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/ticketsApp/app/public/css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Accueil</title>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <img class="logo" src="/ticketsApp/app/public/img/logo_FORSIM.png" />
        
        <?php
        if (isset($_SESSION['username'])) {
            echo "Bonjour, " . htmlspecialchars($_SESSION['username']) . "! Voici votre historique de ticket.";
        }
        ?>
        <table class="ticketTable">
            <thead>
                <tr>
                    <th class="numTicket">Ticket ID</th>
                    <th class="title">Titre</th>
                    <th class="date">Date de l'action</th>
                    <th class="type">Action</th>
                    <th class="demandeur">Fait par</th>
                    <th class="numEvent">Comparaison</th>
                    <th class="numEvent">Message</th>
                </tr>
            </thead>
            <tbody id="ticketTableBody">
            <?php if (isset($evenements) && !empty($evenements)): ?>
            <?php foreach ($evenements as $evenement): ?>
                <tr>
                    <?php
                    // URLs MASQUÉES POUR L'ACCUEIL
                    $detailsUrl = "/ticketsApp/tickets/details/" . $evenement['ticket_id'];
                    $detailsSaveUrl = "/ticketsApp/tickets/save_details/" . $evenement['ticket_id'];
                    $chatUrl = "/ticketsApp/tickets/message/" . $evenement['ticket_id'];
                    
                    if ($evenement['statut_id'] == 2) {
                        $detailsUrl = "/ticketsApp/tickets/solved_details/" . $evenement['ticket_id'];
                        $detailsSaveUrl = "/ticketsApp/tickets/solved_save_details/" . $evenement['ticket_id'];
                        $chatUrl = "/ticketsApp/tickets/message_solved/" . $evenement['ticket_id'];
                    } elseif ($evenement['statut_id'] == 3) {
                        $detailsUrl = "/ticketsApp/tickets/closed_details/" . $evenement['ticket_id'];
                        $detailsSaveUrl = "/ticketsApp/tickets/closed_save_details/" . $evenement['ticket_id'];
                        $chatUrl = "/ticketsApp/tickets/message_closed/" . $evenement['ticket_id'];
                    }
                    ?>
                    <td class="num"><a class="ticket_id" href="<?= $detailsUrl ?>"><?= htmlspecialchars($evenement['ticket_id']) ?></a></td>
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
    <script type="text/javascript" src="/ticketsApp/app/public/js/ajax.js" defer></script>
    <script type="text/javascript" src="/ticketsApp/app/public/js/accueil.js" defer></script>
</body>
</html>