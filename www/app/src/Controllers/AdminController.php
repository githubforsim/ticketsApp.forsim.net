<?php


require_once __DIR__ . '/../Models/AdminModel.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/routes.php';


/************************************************
 * Contrôleur AdminController gérant les actions d'administration dans l'application.
 *
 * Ce contrôleur est responsable de la gestion des actions d'administration au sein de l'application.
 * Il inclut des fonctionnalités cruciales pour assurer le bon fonctionnement et la sécurité du système.
 * Le contrôleur interagit principalement avec les modèles et les vues appropriés pour accomplir ses tâches.
 *
 * Ce contrôleur inclut les fonctionnalités suivantes :
 * - Gestion des utilisateurs : création, modification, suppression, gestion des rôles et permissions.
 * - Configuration du système : réglages généraux, paramètres de l'application.
 * - Gestion des données : sauvegarde, restauration, importation et exportation de données.
 * - Surveillance et journalisation des activités de l'application.
 * - Gestion des plugins et extensions de l'application.
 * - Rapports et statistiques sur l'utilisation et les performances.
 *
 * La classe AdminController utilise les modèles appropriés tels que UserModel pour gérer les utilisateurs,
 * SettingsModel pour les configurations, LogManager pour la journalisation, etc.
 * Les méthodes de ce contrôleur sont conçues pour orchestrer les actions complexes liées à l'administration,
 * en assurant la sécurité, la cohérence et l'auditabilité des opérations effectuées.
 *
 * Ce contrôleur est essentiel pour maintenir l'intégrité du système, garantir la conformité réglementaire
 * et fournir les outils nécessaires pour gérer efficacement l'application à grande échelle.
 ***********************************************/
class AdminController 
{
    protected $AdminModel;

    /************************************************
     *  Constructeur de la classe.
     *  Initialise AdminModel avec une connexion à la base de données.
     ***********************************************/
    public function __construct() 
    {
        $db = dbConnect();
        $this->AdminModel = new AdminModel($db);
    }

    /************************************************
     *  Fonction permettant de récupérer et de nettoyer 
     *  un champ spécifique du tableau $postData.
     *
     *  @param array $postData Le tableau de données contenant le champ.
     *  @param string $field Le champ à nettoyer.
     *  @return string La valeur nettoyée du champ ou une chaîne vide 
     *  si le champ n'existe pas.
     ***********************************************/
    private function getSanitizedInput($postData, $field)
    {
        if (isset($postData[$field])) {
            // Nettoie la valeur du champ avec le filtre FILTER_SANITIZE_SPECIAL_CHARS
            $sanitizedValue = filter_var($postData[$field], FILTER_SANITIZE_SPECIAL_CHARS);
            return $sanitizedValue;
        }
            
        // Si le champ n'existe pas, retourne une chaîne de caractères vide
        return '';
    }

    /************************************************
     *  Fonction qui permet de passer des données à la vue.
     *
     *  @param string $view Le nom de la vue.
     *  @param array $data Les données à passer à la vue.
     ***********************************************/
    protected function render($view, $data = []) {
        extract($data);
    
        include(__DIR__ . "/../Views/admin/{$view}.php");
        exit;
    }

    /************************************************
     *  Fonction qui permet d'obtenir le nom filtré de la pièce jointe 
     *  et de vérifier l'extension du fichier.
     *
     *  @param array $attachment Les informations sur la pièce jointe.
     *  @return string Le nom du fichier nettoyé.
     *  @throws Exception Si une erreur se produit lors de l'upload ou 
     *  si l'extension du fichier est invalide.
     ***********************************************/
    private function processAttachment($attachment)
    {
        //On vérifie s'il y a eu une erreur lors de l'upload du fichier
        if ($attachment['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('An error occurred with the file upload.');
        }
        //On récupère le nom original du fichier
        $original_file_name = pathinfo($attachment['name'], PATHINFO_FILENAME);

        //On récupère l'extension du fichier'
        $extension = pathinfo($attachment['name'], PATHINFO_EXTENSION);

        // Tableau contenant les extensions autorisées
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'zip', 'rar'];

        //On vérifie si l'extension du fichier est une extension autorisé
        if (!in_array($extension, $allowedExtensions)) {
        throw new Exception('Invalid file extension. Only JPG, PNG, DOCX, ZIP, RAR, and PDF files are allowed.');
        }

        //Générer le nom de la pièce jointe épurée en combinant le nom du fichier d'origine et l'extension
        $attachment_name = $original_file_name . '.' . $extension;
        //On renvoi le nom de fichier nettoyé
        return $attachment_name;
    }

    /************************************************
     *  Fonction qui permet la création d'un utilisateur.
     *  Cette fonction traite les données du formulaire,
     *  vérifie l'existence de l'utilisateur, crée le compte
     *  et associe les produits sélectionnés à l'utilisateur.
     *
     *  @param array $postData Les données postées depuis le formulaire.
     ***********************************************/
    public function createUser($postData)
    {
        
        // On récupère les données nettoyées entrées dans le formulaire
        $username = $this->getSanitizedInput($postData, 'username');

        // Décoder les entités HTML pour conserver les apostrophes
        $username = html_entity_decode($username);
        // Maintenant, vous pouvez utiliser $username en toute sécurité avec les apostrophes
        var_dump($username);
        //$username = "'''";
        $mail = $this->getSanitizedInput($postData, 'mail');
        $entreprise = $this->getSanitizedInput($postData, 'entreprise');
        $password = $this->getSanitizedInput($postData, 'pwd');
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $selectedProducts = $_POST['produit_id']; // Tableau des produits sélectionnés
        // Vérifie si l'utilisateur existe déjà
        if ($this->AdminModel->isUsernameExists($username)) {
            header('Location: /ticketsApp/config/routes.php/produits?erreur=1');
            exit();
        }        
    
        $this->AdminModel->createUser($username, $mail, $entreprise, $hashedPassword, $role);
        // Association des produits sélectionnés à l'utilisateur
        $this->AdminModel->addProduitUser($username, $selectedProducts);
        //header('Location: /ticketsApp/config/routes.php/produits');
        exit;
    }

    /************************************************
     *  Fonction qui ajoute les pièces jointes dans le dossier upload
     *  et enregistre les informations correspondantes dans la base de données.
     *
     *  @param int $ticketId L'identifiant du ticket auquel les pièces jointes sont associées.
     *  @param array $filesData Les données des fichiers envoyés depuis le formulaire.
     *  @throws Exception Si la taille du fichier dépasse la limite autorisée ou s'il y a une erreur de déplacement du fichier.
     ***********************************************/
/*public function createAttachments($ticketId, $filesData)
{
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    $processedFiles = 0;

    //Si il y a des pièces jointes dans le formulaire
    if (isset($filesData['attachment'])) {
        //Pour chacun des fichiers mis en pj
        $attachments = $filesData['attachment'];
        
        // Assurez-vous que le dossier upload existe
        $upload_dir = __DIR__ . '/../upload';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($attachments['name'] as $key => $name) {
            if ($attachments['error'][$key] === UPLOAD_ERR_OK) {
                $attachment = array(
                    'name' => $attachments['name'][$key],
                    'type' => $attachments['type'][$key],
                    'tmp_name' => $attachments['tmp_name'][$key],
                    'error' => $attachments['error'][$key],
                    'size' => $attachments['size'][$key]
                );

                if ($attachment['size'] > $maxFileSize) {
                    throw new Exception('La taille du fichier dépasse la limite autorisée.');
                }

                $attachment_name = $this->processAttachment($attachment);
                $original_file_name = pathinfo($attachment_name, PATHINFO_FILENAME);
                $extension = pathinfo($attachment_name, PATHINFO_EXTENSION);
                $new_filename = $original_file_name . '_' . $ticketId . '.' . $extension;
                $new_destination = $upload_dir . DIRECTORY_SEPARATOR . $new_filename;

                if (move_uploaded_file($attachment['tmp_name'], $new_destination)) {
                    $relative_path = 'app/src/upload/' . $new_filename;
                    $this->AdminModel->addAttachment($ticketId, $relative_path);
                    $processedFiles++;
                } else {
                    throw new Exception('Erreur lors du déplacement du fichier.');
                }
            }
        }
    }
    return $processedFiles;
}*/
/************************************************
     *  Fonction qui récupère tous les produits, types et urgences
     *  et les passe à la vue de création de ticket admin.
     ***********************************************/
    public function getCreateTicketForm()
    {
        // Récupération des produits depuis AdminModel
        $produits = $this->AdminModel->getProduit();
        
        // Récupération des types depuis AdminModel
        $types = $this->AdminModel->getType();
        
        // Récupération des urgences depuis AdminModel
        $urgences = $this->AdminModel->getUrgence();
        
        // Chargement de la vue de création de ticket
        $this->render('tickets/create_ticket_view', [
            'produits' => $produits,
            'types' => $types,
            'urgences' => $urgences
        ]);
    }
    /************************************************
     *  Fonction qui permet la création d'un ticket dans la base de données.
     *  Elle traite les données du formulaire, crée le ticket,
     *  gère les pièces jointes associées et envoie un e-mail de confirmation à l'utilisateur.
     *
     *  @param array $postData Les données postées depuis le formulaire.
     *  @param array $filesData Les données des fichiers envoyés depuis le formulaire.
     ***********************************************/
  public function createTicket($postData, $filesData)
{
    try {
        // Récupération et nettoyage des données du formulaire
        $titre = $this->getSanitizedInput($postData, 'titre');
        $description = $this->getSanitizedInput($postData, 'description');
        $produit_id = $this->getSanitizedInput($postData, 'produit_id');
        $type_id = $this->getSanitizedInput($postData, 'type_id');
        $urgence_id = $this->getSanitizedInput($postData, 'urgence_id');
        $username = $_SESSION['username'];
        
        // Décodage des entités HTML pour conserver les caractères spéciaux
        $titre = html_entity_decode($titre);
        $description = html_entity_decode($description);
        
        // Création du ticket dans la base de données
        $ticketId = $this->AdminModel->createTicket(
            $titre,
            $description,
            date('Y-m-d H:i:s'),  // Date de création actuelle
            1,                    // Statut_id (1 = ouvert)
            $urgence_id,
            $username,
            $produit_id,
            $type_id,
            $filesData            // Passez les données des fichiers directement au modèle
        );

        if ($ticketId) {
            // Journalisation de l'événement de création du ticket
            $this->AdminModel->logEvent($ticketId, $username, 1); // 1 = Ouvert
            
            // Redirection vers la liste des tickets ouverts
            header('Location: /ticketsApp/all_opened');
            exit;
        } else {
            throw new Exception("Échec de la création du ticket");
        }

    } catch (Exception $e) {
        error_log("Erreur création ticket: " . $e->getMessage());
        header('Location: /ticketsApp/create-form-admin?error=' . urlencode($e->getMessage()));
        exit;
    }
}

// La fonction createAttachments n'est plus nécessaire car elle est maintenant gérée par le modèle
// Cette fonction peut être supprimée ou conservée comme méthode utilitaire si nécessaire

    /************************************************
     *  Fonction permettant de sauvegarder les actions
     *  effectuées par l'utilisateur ou l'administrateur.
     *
     *  @param int $ticket_id L'identifiant du ticket associé à l'événement.
     *  @param string $username Le nom d'utilisateur associé à l'événement.
     *  @param int $statut_evenement_id L'identifiant du statut de l'événement.
     *  @throws Exception En cas d'échec de l'enregistrement de l'événement.
     ***********************************************/
    public function logEvent($ticket_id, $username, $statut_evenement_id)
    {
        try {
            $this->AdminModel->logEvent($ticket_id, $username, $statut_evenement_id);
        } catch (Exception $e) {
            // Gérer l'exception en enregistrant l'erreur dans le journal des erreurs
            error_log("Failed to log event: " . $e->getMessage());
            throw new Exception('Failed to log event.');
        }
    }



public function getEventDetails($event_id)
{
    // Récupérer les détails de l'événement
    $event = $this->AdminModel->getEventDetails($event_id);
    
    if (!$event) {
        header('Location: /ticketsApp/admin/tickets/open?error=event_not_found');
        exit;
    }
    
    // Récupérer aussi les détails du ticket associé (peu importe le statut)
    $ticket = $this->AdminModel->getTicketDetails($event['ticket_id']);
    $attachments = $this->AdminModel->getAttachmentsTicket($event['ticket_id']);
    
    $this->render('tickets/details/event_details_view', [
        'event' => $event,
        'ticket' => $ticket,
        'attachments' => $attachments
    ]);
    exit;
}
    /************************************************
     *  Fonction qui affiche la page d'accueil admin avec les produits et l'historique des événements
     *
     *  @param int $produit_id L'identifiant du produit (par défaut 1 si non spécifié)
     ***********************************************/
    public function showAdminHome($produit_id = 1){
        $produits = $this->AdminModel->getProduit();
        $evenements = $this->AdminModel->getAllEventHistory($produit_id);
        $this->render('admin_view', ['evenements' => $evenements, 'produits' => $produits]);
        exit;
    }

    /************************************************
     *  Fonction qui récupère l'historique de tous les événements
     *  associés à un produit spécifique et les affiche dans la vue admin.
     *
     *  @param int $produit_id L'identifiant du produit pour lequel récupérer l'historique des événements.
     ***********************************************/
    public function getAllEventHistory($produit_id){
        $produits = $this->AdminModel->getProduit();
        $evenements = $this->AdminModel->getAllEventHistory($produit_id);
        $this->render('admin_view', ['evenements' => $evenements, 'produits' => $produits]);
        exit;
    }

    /************************************************
     *  Fonction qui récupère tous les tickets ouverts associés à un produit
     *  spécifique et les passe à la vue des tickets ouverts.
     *
     *  @param int $produit_id L'identifiant du produit pour lequel récupérer les tickets ouverts.
     ***********************************************/
    public function getAllOpenTickets($produit_id) {
        $produits = $this->AdminModel->getProduit();
        $tickets = $this->AdminModel->getAllOpenTickets($produit_id);
        $this->render('tickets/open_tickets_view', ['tickets' => $tickets, 'produits' => $produits]);
        exit; 
    }

    /************************************************
     *  Fonction qui récupère tous les tickets résolus associés à un produit
     *  spécifique et les passe à la vue des tickets résolus.
     *
     *  @param int $produit_id L'identifiant du produit pour lequel récupérer les tickets résolus.
     ***********************************************/
    public function getAllSolvedTickets($produit_id) 
    {
        $produits = $this->AdminModel->getProduit();
        $tickets = $this->AdminModel->getAllSolvedTickets($produit_id);
        $this->render('tickets/solved_tickets_view', ['tickets' => $tickets, 'produits' => $produits]);
        exit; 
    }

    /************************************************
     *  Fonction qui récupère tous les tickets fermés associés à un produit
     *  spécifique et les passe à la vue des tickets fermés.
     *
     *  @param int $produit_id L'identifiant du produit pour lequel récupérer les tickets fermés.
     ***********************************************/
    public function getAllClosedTickets($produit_id) 
    {
        $produits = $this->AdminModel->getProduit();
        $tickets = $this->AdminModel->getAllClosedTickets($produit_id);
        $this->render('tickets/closed_tickets_view', ['tickets' => $tickets, 'produits' => $produits]);
        exit; 
    }

    /************************************************
     *  Fonction qui récupère les détails d'un ticket ouvert spécifique
     *  ainsi que tous les événements associés à ce ticket et les passe à la vue.
     *
     *  @param int $ticket_id L'identifiant du ticket pour lequel récupérer les détails et les événements.
     ***********************************************/
    public function getTicketDetailsAndEvents($ticket_id)
    {
        $ticket = $this->AdminModel->getTicketDetails($ticket_id);
        $attachments = $this->AdminModel->getAttachmentsTicket($ticket_id);
        $evenements = $this->AdminModel->getTicketEventHistory($ticket_id);

        $this->render('tickets/details/details_ticket_view', [
            'ticket' => $ticket,
            'attachments' => $attachments,
            'evenements' => $evenements
        ]);
        exit;
    }

    /************************************************
     *  Fonction qui récupère les détails d'un ticket sauvegardé spécifique
     *  ainsi que tous les événements associés à ce ticket et les passe à la vue.
     *
     *  @param int $ticket_id L'identifiant du ticket sauvegardé pour lequel récupérer les détails et les événements.
     ***********************************************/
    public function getTicketSaveDetails($ticket_id)
    {
        $ticket = $this->AdminModel->getTicketDetails($ticket_id);
        $attachments = $this->AdminModel->getAttachmentsTicket($ticket_id);
        $ticketsave = $this->AdminModel->getTicketSaveDetails($ticket_id);

        $this->render('tickets/details/ticket_save_details_view', [
            'ticket' => $ticket,
            'attachments' => $attachments,
            'ticketsave' => $ticketsave
        ]);
        exit;
    }

    /************************************************
     *  Fonction qui récupère les détails d'un ticket sauvegardé résolu
     *  ainsi que tous les événements associés à ce ticket et les passe à la vue.
     *
     *  @param int $ticket_id L'identifiant du ticket sauvegardé pour lequel récupérer les détails et les événements.
     ***********************************************/
    public function getTicketSolvedSaveDetails($ticket_id)
    {
        $ticket = $this->AdminModel->getSolvedDetails($ticket_id);
        $attachments = $this->AdminModel->getAttachmentsTicket($ticket_id);
        $ticketsave = $this->AdminModel->getTicketSaveDetails($ticket_id);

        $this->render('tickets/details/ticket_save_solved_details_view', [
            'ticket' => $ticket,
            'attachments' => $attachments,
            'ticketsave' => $ticketsave
        ]);
        exit;
    }

    /************************************************
     *  Fonction qui récupère les détails d'un ticket sauvegardé fermé
     *  ainsi que tous les événements associés à ce ticket et les passe à la vue.
     *
     *  @param int $ticket_id L'identifiant du ticket sauvegardé pour lequel récupérer les détails et les événements.
     ***********************************************/
    public function getTicketClosedSaveDetails($ticket_id)
    {
        $ticket = $this->AdminModel->getClosedDetails($ticket_id);
        $attachments = $this->AdminModel->getAttachmentsTicket($ticket_id);
        $ticketsave = $this->AdminModel->getTicketSaveDetails($ticket_id);

        $this->render('tickets/details/ticket_save_closed_details_view', [
            'ticket' => $ticket,
            'attachments' => $attachments,
            'ticketsave' => $ticketsave
        ]);
        exit;
    }

    /************************************************
     *  Fonction qui récupère les détails d'un ticket fermé spécifique
     *  ainsi que tous les événements associés à ce ticket et les passe à la vue.
     *
     *  @param int $ticket_id L'identifiant du ticket fermé pour lequel récupérer les détails et les événements.
     ***********************************************/
    public function getClosedDetails($ticket_id) 
    {
        $ticket = $this->AdminModel->getClosedDetails($ticket_id);
        $attachments = $this->AdminModel->getAttachmentsTicket($ticket_id);
        $evenements = $this->AdminModel->getTicketEventHistory($ticket_id);
        $this->render('tickets/details/closed_details_view', ['ticket' => $ticket, 'attachments' => $attachments, 'evenements' => $evenements]);
        exit; 
    }

    /************************************************
     *  Fonction qui récupère les détails d'un ticket résolu spécifique
     *  ainsi que tous les événements associés à ce ticket et les passe à la vue.
     *
     *  @param int $ticket_id L'identifiant du ticket résolu pour lequel récupérer les détails et les événements.
     ***********************************************/
    public function getSolvedDetails($ticket_id) 
    {
        $ticket = $this->AdminModel->getSolvedDetails($ticket_id);
        $attachments = $this->AdminModel->getAttachmentsTicket($ticket_id);
        $evenements = $this->AdminModel->getTicketEventHistory($ticket_id);
        $this->render('tickets/details/solved_details_view', ['ticket' => $ticket, 'attachments' => $attachments, 'evenements' => $evenements]);       
        exit; 
    }

    /************************************************
     *  Fonction qui récupère les informations des utilisateurs
     *  depuis la base de données et les passe à la vue.
     ***********************************************/
    public function getUsers() {
        $users = $this->AdminModel->getUsers();
        $this->render('users_view', ['users' => $users]);
        exit; 
    }

    /************************************************
     *  Fonction qui récupère les informations d'un utilisateur spécifique
     *  ainsi que les produits associés à cet utilisateur et les passe à la vue.
     *
     *  @param string $username Le nom d'utilisateur de l'utilisateur pour lequel récupérer les informations.
     ***********************************************/
    public function getUserByUsername($username) {
        $user = $this->AdminModel->getUserByUsername($username);
        $produits = $this->AdminModel->getProduitsByUser($username);
        $this->render('user_details_view', ['user' => $user, 'produits' => $produits]);
        exit; 
    }

    /************************************************
     *  Fonction qui récupère tous les messages associés à un ticket ouvert
     *  et les passe à la vue correspondante pour affichage.
     *
     *  @param int $ticket_id L'identifiant du ticket pour lequel récupérer les messages.
     *  @param string $username Le nom d'utilisateur associé au ticket.
     ***********************************************/
    public function getTicketMessages($ticket_id, $username) {
        $ticket = $this->AdminModel->getTicketDetails($ticket_id, $username);
        $allMessages = $this->AdminModel->getAllTicketMessages($ticket_id, $username);
        $this->render('tickets/details/ticket_chat_view', ['ticket' => $ticket, 'messages' => $allMessages]);
    }

    /************************************************
     *  Fonction qui récupère tous les messages associés à un ticket résolu
     *  et les passe à la vue correspondante pour affichage.
     *
     *  @param int $ticket_id L'identifiant du ticket résolu pour lequel récupérer les messages.
     *  @param string $username Le nom d'utilisateur associé au ticket.
     ***********************************************/
    public function getTicketMessagesSolved($ticket_id, $username) {
        $ticket = $this->AdminModel->getSolvedDetails($ticket_id, $username);
        $allMessages = $this->AdminModel->getAllTicketMessages($ticket_id, $username);
        $this->render('tickets/details/ticket_chat_solved_view', ['ticket' => $ticket, 'messages' => $allMessages]);
    }

    /************************************************
     *  Fonction qui récupère tous les messages associés à un ticket fermé
     *  et les passe à la vue correspondante pour affichage.
     *
     *  @param int $ticket_id L'identifiant du ticket fermé pour lequel récupérer les messages.
     *  @param string $username Le nom d'utilisateur associé au ticket.
     ***********************************************/
    public function getTicketMessagesClosed($ticket_id, $username) {
        $ticket = $this->AdminModel->getClosedDetails($ticket_id, $username);
        $allMessages = $this->AdminModel->getAllTicketMessages($ticket_id, $username);
        $this->render('tickets/details/ticket_chat_closed_view', ['ticket' => $ticket, 'messages' => $allMessages]);
    }

    /************************************************
     *  Fonction qui permet d'envoyer un message pour un ticket ouvert.
     *  Valide et nettoie les données, enregistre le message et l'événement associé,
     *  puis redirige vers la page appropriée.
     *
     *  @param array $postData Les données postées contenant l'identifiant du ticket et le message à envoyer.
     *  @param string $username Le nom d'utilisateur de l'expéditeur du message.
     ***********************************************/
     /************************************************
     *  Fonction qui permet d'envoyer un message pour un ticket ouvert.
     *  Valide et nettoie les données, enregistre le message et l'événement associé,
     *  puis redirige vers la page appropriée.
     *
     *  @param array $postData Les données postées contenant l'identifiant du ticket et le message à envoyer.
     *  @param string $username Le nom d'utilisateur de l'expéditeur du message.
     ***********************************************/
    public function sendMessage($postData, $username){
        $username = $_SESSION['username'];
        $statut_evenement_id = 8; // 8 = Message
        // Valide et nettoie les données
        $ticket_id = $this->getSanitizedInput($postData, 'ticket_id');
        $sendMessage = $this->getSanitizedInput($postData, 'message');
        $this->AdminModel->sendMessage($ticket_id, $sendMessage, $username);
        $this->AdminModel->logEvent($ticket_id, $username, $statut_evenement_id);
        header('Location: /ticketsApp/ticket-message-admin/' . $ticket_id);
        exit;
    }

    /************************************************
     *  Fonction qui permet d'envoyer un message pour un ticket résolu.
     *  Valide et nettoie les données, enregistre le message et l'événement associé,
     *  puis redirige vers la page appropriée.
     *
     *  @param array $postData Les données postées contenant l'identifiant du ticket et le message à envoyer.
     *  @param string $username Le nom d'utilisateur de l'expéditeur du message.
     ***********************************************/
    public function sendMessageSolved($postData, $username){
        $username = $_SESSION['username'];
        $statut_evenement_id = 8; // 8 = Message
        // Valide et nettoie les données
        $ticket_id = $this->getSanitizedInput($postData, 'ticket_id');
        $sendMessage = $this->getSanitizedInput($postData, 'message');
        $this->AdminModel->sendMessage($ticket_id, $sendMessage, $username);
        $this->AdminModel->logEvent($ticket_id, $username, $statut_evenement_id);
        header('Location: /ticketsApp/ticket-message-solved-admin/' . $ticket_id);
        exit;
    }

    /************************************************
     *  Fonction qui permet d'envoyer un message pour un ticket fermé.
     *  Valide et nettoie les données, enregistre le message et l'événement associé,
     *  puis redirige vers la page appropriée.
     *
     *  @param array $postData Les données postées contenant l'identifiant du ticket et le message à envoyer.
     *  @param string $username Le nom d'utilisateur de l'expéditeur du message.
     ***********************************************/
    public function sendMessageClosed($postData, $username){
        $username = $_SESSION['username'];
        $statut_evenement_id = 8; // 8 = Message
        // Valide et nettoie les données
        $ticket_id = $this->getSanitizedInput($postData, 'ticket_id');
        $sendMessage = $this->getSanitizedInput($postData, 'message');
        $this->AdminModel->sendMessage($ticket_id, $sendMessage, $username);
        $this->AdminModel->logEvent($ticket_id, $username, $statut_evenement_id);
        header('Location: /ticketsApp/ticket-message-closed-admin/' . $ticket_id);
        exit;
    }

    /************************************************
     *  Fonction qui permet de mettre à jour les détails d'un ticket ouvert.
     *  Valide et nettoie les données, met à jour le titre et la description du ticket,
     *  enregistre l'événement associé, puis redirige vers la page appropriée.
     *
     *  @param array $postData Les données postées contenant l'identifiant du ticket, le nouveau titre et la nouvelle description.
     ***********************************************/
    public function updateTicketsDetails($postData) {

        $username = $_SESSION['username'];
        $statut_evenement_id = 7; // 7 = Text Changed
        // Valide et nettoie les données
        $ticket_id = $this->getSanitizedInput($postData, 'ticket_id');
        $newTitle = $this->getSanitizedInput($postData, 'titre');
        $newDescription = $this->getSanitizedInput($postData, 'description');
        $this->AdminModel->updateTicketsDetails($ticket_id, $newTitle, $newDescription);
        $this->AdminModel->logEvent($ticket_id, $username, $statut_evenement_id);
        header('Location: /ticketsApp/all_opened');
        exit;
    }

    /************************************************
     *  Fonction qui permet de mettre à jour le mot de passe d'un utilisateur.
     *  Valide et nettoie les données, vérifie la correspondance du nouveau mot de passe et de sa confirmation,
     *  met à jour le mot de passe en base de données, puis redirige vers la page appropriée.
     *
     *  @param array $postData Les données postées contenant le nom d'utilisateur, le nouveau mot de passe et la confirmation du mot de passe.
     ***********************************************/
public function updateUserPassword($postData) {
    $username = $this->getSanitizedInput($postData, 'username');
    $newPassword = $this->getSanitizedInput($postData, 'new_password');
    $confirmPassword = $this->getSanitizedInput($postData, 'confirm_password');
    
    if ($newPassword !== $confirmPassword) {
        $user = $this->AdminModel->getUserByUsername($username);
        $produits = $this->AdminModel->getProduitsByUser($username);
        $_GET['erreur'] = 1;
        $this->render('user_details_view', ['user' => $user, 'produits' => $produits]);
    } else {
        $this->AdminModel->updateUserPassword($username, $newPassword);
        $user = $this->AdminModel->getUserByUsername($username);
        $produits = $this->AdminModel->getProduitsByUser($username);
        $_GET['success'] = 1;
        $this->render('user_details_view', ['user' => $user, 'produits' => $produits]);
    }
}

    /************************************************
     *  Fonction qui récupère tous les produits et les passe à la vue pour affichage.
     ***********************************************/
    public function getProduit()
    {
        // Récupérez les produits depuis votre modèle (par exemple, en utilisant une fonction getUsers dans votre modèle)
        $produits = $this->AdminModel->getProduit();

        // Chargez la vue 'create_user_view.php' en passant les produits comme données supplémentaires
        $this->render('create_user_view', ['produits' => $produits]);
    }
}