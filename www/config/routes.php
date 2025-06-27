<?php

// Vérifie si la session n'est pas déjà démarrée, puis la démarre si nécessaire
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclusion des fichiers des contrôleurs
require_once __DIR__ .'/../app/src/Controllers/TicketController.php';
require_once __DIR__ .'/../app/src/Controllers/LoginController.php';
require_once __DIR__ .'/../app/src/Controllers/AdminController.php';

// Instanciation des contrôleurs nécessaires
$ticketController = new TicketController();
$loginController = new LoginController();
$adminController = new AdminController();

// En-têtes CORS permettant l'accès depuis n'importe quelle origine
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: *');

// Désactivation du cache
header('Cache-control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// Récupération de la méthode HTTP utilisée (GET, POST, etc.)
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Récupération des informations de l'URI pour déterminer la ressource demandée
$pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
$request = substr($pathInfo, 1); // Suppression du premier slash
$request = explode('/', $request); // Découpage de l'URI en segments

$requestRessource = array_shift($request); // Extraction de la première partie de la requête
$id = array_shift($request); // Extraction de la deuxième partie de la requête

// Récupération du nom d'utilisateur s'il est défini dans la session
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
} else {
    $username = null; 
}

// ============== AJOUTEZ CES LIGNES DE DEBUG ICI ==============
error_log("=== DEBUG ROUTES ===");
error_log("REQUEST_METHOD: " . $requestMethod);
error_log("PATH_INFO: " . $pathInfo);
error_log("requestRessource: " . $requestRessource);
error_log("id: " . $id);
error_log("request array: " . print_r($request, true));
error_log("username: " . ($username ?? 'null'));
error_log("==================");
// ============================================================

// Traitement des requêtes en fonction de la méthode HTTP
switch ($requestMethod) {
    
    case 'GET':
        switch ($requestRessource) {
            // Routes GET pour les tickets ouverts
            // Routes admin masquées
            
case 'admin':
    $subRoute = $id;
    $ticketId = array_shift($request);
    
    switch ($subRoute) {
        case 'tickets':
            $action = $ticketId;
            $id = array_shift($request);
            
            switch ($action) {
                case 'create':
                    $adminController->getCreateTicketForm(); // ← Au lieu de getProduit()
                    break;
                case 'open':
                    $produitId = isset($_GET['produit']) ? $_GET['produit'] : 1;
                    $adminController->getAllOpenTickets($produitId);
                    break;
                case 'solved':
                    $produitId = isset($_GET['produit']) ? $_GET['produit'] : 1;
                    $adminController->getAllSolvedTickets($produitId);
                    break;
                case 'closed':
                    $produitId = isset($_GET['produit']) ? $_GET['produit'] : 1;
                    $adminController->getAllClosedTickets($produitId);
                    break;
                case 'details':
                    $adminController->getTicketDetailsAndEvents($id);
                    break;
                case 'save-details':
                    $adminController->getTicketSaveDetails($id);
                    break;
                case 'solved-details':
                    $adminController->getSolvedDetails($id);
                    break;
                case 'solved-save-details':
                    $adminController->getTicketSolvedSaveDetails($id);
                    break;
                case 'closed-details':
                    $adminController->getClosedDetails($id);
                    break;
                case 'closed-save-details':
                    $adminController->getTicketClosedSaveDetails($id);
                    break;
                case 'solved-save-details':
                    $adminController->getTicketSolvedSaveDetails($id);
                    break;
                case 'closed-save-details':
                    $adminController->getTicketClosedSaveDetails($id);
                    break;
                case 'chat':
                    $adminController->getTicketMessages($id, $username);
                    break;
                case 'solved-chat':
                    $adminController->getTicketMessagesSolved($id, $username);
                    break;
                case 'closed-chat':
                    $adminController->getTicketMessagesClosed($id, $username);
                    break;
            }
            break;
            
        // NOUVELLES ROUTES POUR LES ÉVÉNEMENTS
        case 'events':
            $action = $ticketId;
            $id = array_shift($request);
            
            switch ($action) {
                case 'details':
                    // Afficher les détails d'un événement spécifique
                    $adminController->getEventDetails($id);
                    break;
                case 'solved-details':
                    // Afficher les détails d'un événement d'un ticket résolu
                    $adminController->getEventSolvedDetails($id);
                    break;
                case 'closed-details':
                    // Afficher les détails d'un événement d'un ticket fermé
                    $adminController->getEventClosedDetails($id);
                    break;
                case 'compare':
                    // Comparer un événement (fonctionnalité de comparaison)
                    $adminController->compareEvent($id);
                    break;
                case 'history':
                    // Historique complet d'un événement
                    $adminController->getEventHistory($id);
                    break;
            }
            break;
            
        case 'users':
            $action = $ticketId;
            switch ($action) {
                case 'create':
                    $adminController->getProduit();
                    break;
                case '':
                default:
                    $adminController->getUsers();
                    break;
            }
            break;
    }
    break;

case 'tickets':
    $subRoute = $id;
    $ticketId = array_shift($request);
    
    // Debug pour voir ce qui arrive
    error_log("TICKETS ROUTE - subRoute: " . $subRoute . ", ticketId: " . $ticketId);
    
    switch ($subRoute) {
        case 'open':
            // Utiliser la méthode existante getOpenTicket
            $produitId = isset($_GET['produit']) ? $_GET['produit'] : 1;
            error_log("Calling getOpenTicket with produitId: " . $produitId);
            $ticketController->getOpenTicket($produitId);
            break;
            
        case 'solved':
            // Utiliser la méthode existante getSolvedTicket
            $produitId = isset($_GET['produit']) ? $_GET['produit'] : 1;
            error_log("Calling getSolvedTicket with produitId: " . $produitId);
            $ticketController->getSolvedTicket($produitId);
            break;
            
        case 'closed':
            // Utiliser la méthode existante getClosedTicket
            $produitId = isset($_GET['produit']) ? $_GET['produit'] : 1;
            error_log("Calling getClosedTicket with produitId: " . $produitId);
            $ticketController->getClosedTicket($produitId);
            break;
            
        case 'create':
            // Formulaire de création de ticket
            $ticketController->getUrgence();
            break;
            
        case 'details':
            // Utiliser la méthode existante getTicketDetailsAndEvents
            $ticketController->getTicketDetailsAndEvents($ticketId, $username);
            break;
            
        case 'solved_details':
            // Utiliser la méthode existante getSolvedDetails
            $ticketController->getSolvedDetails($ticketId, $username);
            break;
            
        case 'closed_details':
            // Utiliser la méthode existante getClosedDetails
            $ticketController->getClosedDetails($ticketId, $username);
            break;
         case 'save_details':
            // Page de comparaison/sauvegarde pour ticket ouvert
            $ticketController->getTicketSaveDetails($ticketId, $username);
            break;
          case 'solved_save_details':
            // Page de comparaison/sauvegarde pour ticket résolu
            $ticketController->getTicketSolvedSaveDetails($ticketId, $username);
            break;
            
        case 'closed_save_details':
            // Page de comparaison/sauvegarde pour ticket fermé
            $ticketController->getTicketClosedSaveDetails($ticketId, $username);
            break;
            
        case 'message':
            // Messages d'un ticket ouvert
            $ticketController->getTicketMessages($ticketId, $username);
            break;
            
        case 'message_solved':
            // Messages d'un ticket résolu
            $ticketController->getTicketMessagesSolved($ticketId, $username);
            break;
        case 'message_closed':
            // Messages d'un ticket fermé
            $ticketController->getTicketMessagesClosed($ticketId, $username);
            break;
            
        default:
            // Route par défaut ou erreur 404
            error_log("Route tickets non trouvée: " . $subRoute);
            header("HTTP/1.0 404 Not Found");
            echo "Route not found: " . $subRoute;
            break;
    }
    break;
// ================================================================
    
            case 'opened':
                $ticketController->getOpenTicket($id, $username);
                break;
            case 'all_opened':
                $adminController->getAllOpenTickets($id);
                break;
            // Routes GET pour les messages de ticket
            case 'ticket-message':
                $ticketController->getTicketMessages($id, $username);
                break;
            case 'ticket-message-admin':
                $adminController->getTicketMessages($id, $username);
                break;
            case 'ticket-message-solved':
                $ticketController->getTicketMessagesSolved($id, $username);
                break;
            case 'ticket-message-solved-admin':
                $adminController->getTicketMessagesSolved($id, $username);
                break;
            case 'ticket-message-closed':
                $ticketController->getTicketMessagesClosed($id, $username);
                break;
            case 'ticket-message-closed-admin':
                $adminController->getTicketMessagesClosed($id, $username);
                break;
            // Routes GET pour les détails des tickets
            case 'details':
                $ticketController->getTicketDetailsAndEvents($id, $username);
                break;
            case 'admin_details':
                $adminController->getTicketDetailsAndEvents($id);
                break;
            // Routes GET pour les tickets résolus
            case 'solved':
                $ticketController->getSolvedTicket($id, $username);
                break;
            case 'all_solved':
                $adminController->getAllSolvedTickets($id);
                break;
            // Routes GET pour les tickets fermés
            case 'closed':
                $ticketController->getClosedTicket($id, $username);
                break;
            case 'all_closed':
                $adminController->getAllClosedTickets($id);
                break;
            // Routes GET pour les utilisateurs et détails utilisateur
            case 'users':
                $adminController->getUsers();
                break;
/**
 * Gestion du cas 'user_details' dans le routeur
 * 
 * Cette route permet de récupérer les détails d'un utilisateur spécifique et ses produits associés
 * 
 * @param string $_GET['username'] Le nom d'utilisateur recherché passé en paramètre GET
 * @throws None
 * 
 * Processus :
 * 1. Récupère le nom d'utilisateur depuis les paramètres GET
 * 2. Log la requête pour traçage
 * 3. Appelle la méthode getUserByUsername() du contrôleur admin pour obtenir les détails de l'utilisateur
 * 4. Appelle la méthode getProduitsByUser() pour obtenir les produits associés à cet utilisateur
 */
            case 'user_details':
                $username = isset($_GET['username']) ? $_GET['username'] : '';
                error_log("Utilisateur demandé (via GET): " . $username);
                $adminController->getUserByUsername($username);
                $adminController->getProduitsByUser($username);
                break;
                
            // Routes GET pour les détails des tickets fermés et résolus
            case 'update-details':
            $adminController->updateTicketsDetails($_POST);
             break;
            case 'closed_details':
                $ticketController->getClosedDetails($id, $username);
                break;
            case 'admin_closed_details':
                $adminController->getClosedDetails($id);
                break;
            case 'solved_details':
                $ticketController->getSolvedDetails($id, $username);
                break;
            case 'admin_solved_details':
                $adminController->getSolvedDetails($id);
                break;
            // Routes GET pour les détails des enregistrements de sauvegarde de ticket
            case 'ticket_save_details':
                $ticketController->getTicketSaveDetails($id, $username);
                break;
            
            case 'admin_ticket_save_details':
                $adminController->getTicketSaveDetails($id);
                break;
            case 'ticket_solved_save_details':
                $ticketController->getTicketSolvedSaveDetails($id, $username);
                break;
            case 'admin_ticket_solved_save_details':
                $adminController->getTicketSolvedSaveDetails($id);
                break;
            case 'ticket_closed_save_details':
                $ticketController->getTicketClosedSaveDetails($id, $username);
                break;
            case 'admin_ticket_closed_save_details':
                $adminController->getTicketClosedSaveDetails($id);
                break;
            // Routes GET pour l'historique des événements utilisateur et global
            case 'user-event':
                $ticketController->getUserEventHistory($id, $username);
                break;
            case 'all-event':
                $adminController->getAllEventHistory($id);
                break;
            // Routes GET pour les urgences et produits
            case 'urgence':
                $ticketController->getUrgence();
                break;
            case 'produit':
                $ticketController->getProduit();
                break;
            case 'produits':
                $adminController->getProduit();
                break;
            case 'user_produits':
                $ticketController->displayProducts();
                break;
            case 'type':
                $ticketController->getType();
                break;
            case 'pieces-jointes':
                $ticketController->getAttachmentsTicket($id);
                break;
            // Route GET pour la déconnexion de l'utilisateur
            case 'logout':
                $loginController->logout();
                break;
        }
        break;

    case 'POST':
        switch ($requestRessource) {
            // Routes POST pour la création de tickets
            case 'create':
                $ticketController->createTicket($_POST, $_FILES);
                break;
            case 'create-admin':
                $adminController->createTicket($_POST, $_FILES);
                break;
            // Routes POST pour l'envoi de messages de ticket
            case 'message-sent':
                $ticketController->sendMessage($_POST, $username);
                break;
            case 'message-sent-admin':
                $adminController->sendMessage($_POST, $username);
                break;
            case 'message-sent-solved':
                $ticketController->sendMessageSolved($_POST, $username);
                break;
            case 'message-sent-solved-admin':
                $adminController->sendMessageSolved($_POST, $username);
                break;
            case 'message-sent-closed':
                $ticketController->sendMessageClosed($_POST, $username);
                break;
            case 'message-sent-closed-admin':
                $adminController->sendMessageClosed($_POST, $username);
                break;
            // Route POST pour enregistrer des événements
            case 'event':
                $ticketController->logEvent($id, $username);
                break;
            // Routes POST pour changer le statut des tickets (ouvrir, fermer, résoudre)
            case 'setOpen':
                $ticketController->setOpen($id);
                break;
            case 'setClose':
                $ticketController->setClose($id);
                break;
            case 'setSolve':
                $ticketController->setSolve($id);
                break;
            // Routes POST pour la gestion des pièces jointes
            case 'add_attachments':
                $ticketController->ModifAttachment($id, $_FILES);
                break;
            case 'add_attachments_admin':
                $ticketController->createAttachments($id, $_FILES);
                break;
            case 'delete_attachment':
                $ticketController->deleteAttachment($id);
                break;
            // Route POST pour la mise à jour des détails des tickets par l'administrateur
            case 'admin_change_tickets_details':
                $adminController->updateTicketsDetails($_POST);
                break;
            // Route POST pour la mise à jour des détails des tickets par l'utilisateur
            case 'change_tickets_details':
                $ticketController->updateTicketsDetails($_POST);
                break;
            // Route POST pour la connexion de l'utilisateur
            case 'login':
                $loginController->login($_POST['username'], $_POST['pwd']);
                break;
            // Route POST pour la création d'un nouvel utilisateur par l'administrateur
            case 'create_user':
                $adminController->createUser($_POST);
                break;
            // Route POST pour le changement de mot de passe par l'administrateur
            case 'change_password':
                $adminController->updateUserPassword($_POST);
                break;
            // Route POST pour la demande de changement de mot de passe
            case 'change_password':
                $adminController->updateUserPassword($_POST);
                break;
            case 'password_request':
                $loginController->passwordChangeRequest($_POST);
                break;
            // Route de test (à des fins de développement)
            case 'test':
                require_once 'test.php';
                exit();
        }
}
?>