<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../Models/TicketModel.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/routes.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

/************************************************
 * Contrôleur TicketController gérant les actions liées aux tickets dans l'application.
 *
 * Ce contrôleur gère toutes les actions liées aux tickets, y compris la création,
 * la modification, la suppression, et l'affichage des tickets. Il interagit avec le modèle
 * TicketModel pour exécuter ces actions et avec la vue pour afficher les résultats.
 *
 * Ce contrôleur inclut les fonctionnalités suivantes :
 * - Affichage de la liste des tickets ouverts, fermés, résolus, etc., pour un produit spécifique.
 * - Affichage des détails d'un ticket spécifique (titre, description, statut, etc.).
 * - Ajout de nouveaux tickets dans la base de données.
 * - Modification des détails d'un ticket existant (titre, description).
 * - Gestion des pièces jointes associées à un ticket.
 * - Envoi et récupération de messages dans le contexte d'un ticket spécifique.
 * - Enregistrement des événements et historique des modifications d'un ticket.
 *
 * La classe utilise le modèle TicketModel pour effectuer les opérations CRUD (Create, Read, Update, Delete)
 * sur les tickets. Les méthodes de ce contrôleur orchestrent la logique métier et l'intégration des données
 * avec la vue et le modèle, en assurant que les actions sur les tickets sont sécurisées et cohérentes.
 ***********************************************/
class TicketController 
{
    protected $ticketModel;

    /************************************************
     * Constructeur de la classe TicketManager.
     * Initialise l'objet TicketModel pour gérer les opérations sur les tickets.
     ***********************************************/
    public function __construct() {
        $db = dbConnect(); // On appelle la fonction pour récupérer une instance PDO

        if (!$db) {
            die('Erreur lors de la connexion à la base de données.'); // Message clair en cas d’échec
        }

        $this->ticketModel = new TicketModel($db);
    }

    /************************************************
     * Fonction pour récupérer et nettoyer un champ spécifique du tableau $postData.
     * 
     * @param array $postData Le tableau contenant les données à nettoyer
     * @param string $field Le champ spécifique à récupérer et nettoyer
     * @return string La valeur nettoyée du champ ou une chaîne vide si le champ n'existe pas
     ***********************************************/
    private function getSanitizedInput($postData, $field)
    {
        //On vérifie si le champ existe dans le taleau $postData
        if (isset($postData[$field])) {
            // Nettoie la valeur du champ avec le filtre FILTER_SANITIZE_SPECIAL_CHARS
            $sanitizedValue = filter_var($postData[$field], FILTER_SANITIZE_SPECIAL_CHARS);
            return $sanitizedValue;
        }
    
        //Si le champ n'existe pas , renvoi une chaine de caractère vide
        return '';
    }

    /************************************************
     * Fonction qui permet de passer des données à la vue.
     * 
     * @param string $view Le nom de la vue à charger
     * @param array $data Les données à passer à la vue
     ***********************************************/
    protected function render($view, $data = []) 
    {
        extract($data);

        // Inclure le fichier de vue correspondant avec les données passées
        include(__DIR__ . "/../Views/{$view}.php");
        exit;
    }

    /************************************************
     * Fonction qui permet d'obtenir le nom filtré de la pièce jointe et vérifie l'extension du fichier.
     * 
     * @param array $attachment Les informations sur la pièce jointe téléchargée
     * @return string Le nom de fichier nettoyé et vérifié
     * @throws Exception Si une erreur survient lors du téléchargement ou si l'extension du fichier est invalide
     ***********************************************/
    /************************************************
     * Fonction qui ajoute les pièces jointes téléchargées dans le dossier upload et enregistre les chemins dans la base de données.
     * 
     * @param int $ticketId L'identifiant du ticket auquel les pièces jointes sont associées
     * @param array $filesData Les données sur les fichiers téléchargés à partir du formulaire
     * @throws Exception Si la taille du fichier dépasse la limite autorisée ou si une erreur survient lors de l'écriture du fichier
     ***********************************************/

// Méthodes corrigées pour TicketController.php

/************************************************
 * Fonction qui ajoute les pièces jointes téléchargées dans le dossier upload et enregistre les chemins dans la base de données.
 * 
 * @param int $ticketId L'identifiant du ticket auquel les pièces jointes sont associées
 * @param array $filesData Les données sur les fichiers téléchargés à partir du formulaire
 * @throws Exception Si la taille du fichier dépasse la limite autorisée ou si une erreur survient lors de l'écriture du fichier
 ***********************************************/
public function createAttachments($ticketId, $filesData)
{
    $maxFileSize = 10 * 1024 * 1024; // 10MB

    // Vérification complète : aucun fichier envoyé
    if (!isset($filesData['attachment']) || 
        !isset($filesData['attachment']['name']) || 
        empty($filesData['attachment']['name'])) {
        return; // Retour silencieux si pas de fichiers
    }

    // Vérification pour tableaux de fichiers vides
    if (is_array($filesData['attachment']['name'])) {
        $hasValidFiles = false;
        foreach ($filesData['attachment']['name'] as $filename) {
            if (!empty($filename)) {
                $hasValidFiles = true;
                break;
            }
        }
        if (!$hasValidFiles) {
            return; // Retour silencieux si pas de fichiers valides
        }
    }

    $attachments = $filesData['attachment'];
    
    // Si un seul fichier, convertir en tableau pour uniformiser le traitement
    if (!is_array($attachments['name'])) {
        $attachments = [
            'name' => [$attachments['name']],
            'type' => [$attachments['type']],
            'tmp_name' => [$attachments['tmp_name']],
            'error' => [$attachments['error']],
            'size' => [$attachments['size']]
        ];
    }
    
    $filesProcessed = 0;
    
    // CORRECTION: Chemins alternatifs pour le dossier upload
    $possibleUploadDirs = [
        __DIR__ . '/../upload',
        __DIR__ . '/../../upload', 
        __DIR__ . '/../../../upload',
        $_SERVER['DOCUMENT_ROOT'] . '/ticketsApp/upload',
        $_SERVER['DOCUMENT_ROOT'] . '/upload'
    ];
    
    $upload_dir = null;
    
    // Essayer de trouver ou créer un dossier accessible
    foreach ($possibleUploadDirs as $dir) {
        if (is_dir($dir) && is_writable($dir)) {
            $upload_dir = $dir;
            break;
        } elseif (!is_dir($dir)) {
            // Essayer de créer le dossier
            if (@mkdir($dir, 0755, true)) {
                // Vérifier si c'est maintenant accessible en écriture
                if (is_writable($dir)) {
                    $upload_dir = $dir;
                    break;
                } else {
                    // Essayer avec des permissions plus larges
                    @chmod($dir, 0777);
                    if (is_writable($dir)) {
                        $upload_dir = $dir;
                        break;
                    }
                }
            }
        }
    }
    
    // Si aucun dossier n'est trouvé/créé, essayer le dossier temporaire système
    if (!$upload_dir) {
        $temp_dir = sys_get_temp_dir() . '/ticketsApp_uploads';
        if (!is_dir($temp_dir)) {
            if (@mkdir($temp_dir, 0755, true)) {
                $upload_dir = $temp_dir;
            }
        } elseif (is_writable($temp_dir)) {
            $upload_dir = $temp_dir;
        }
    }
    
    // Dernière tentative : utiliser le dossier temporaire direct
    if (!$upload_dir) {
        $upload_dir = sys_get_temp_dir();
    }
    
    // Vérifier une dernière fois
    if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
        throw new Exception('Impossible de créer ou d\'accéder à un dossier de destination accessible en écriture. Contactez l\'administrateur système.');
    }
    
    foreach ($attachments['name'] as $key => $name) {
        // Ignorer les fichiers vides ou avec erreur
        if (empty($name) || $attachments['error'][$key] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        
        $attachment = array(
            'name' => $attachments['name'][$key],
            'type' => $attachments['type'][$key],
            'tmp_name' => $attachments['tmp_name'][$key],
            'error' => $attachments['error'][$key],
            'size' => $attachments['size'][$key]
        );
        
        // Vérifier les erreurs d'upload
        if ($attachment['error'] !== UPLOAD_ERR_OK) {
            throw new Exception($this->getUploadErrorMessage($attachment['error']));
        }
        
        // Vérifier la taille du fichier
        if ($attachment['size'] > $maxFileSize) {
            $sizeMB = round($attachment['size'] / 1024 / 1024, 2);
            throw new Exception("Le fichier '{$attachment['name']}' est trop volumineux ({$sizeMB} MB). Taille maximale autorisée : 10 MB.");
        }
        
        // Vérifier que le fichier temporaire existe
        if (!file_exists($attachment['tmp_name'])) {
            throw new Exception("Le fichier temporaire pour '{$attachment['name']}' n'existe pas.");
        }
        
        try {
            $attachment_name = $this->processAttachment($attachment);
            
            $original_file_name = pathinfo($attachment_name, PATHINFO_FILENAME);
            $extension = pathinfo($attachment_name, PATHINFO_EXTENSION);
            
            // Nom de fichier unique avec timestamp pour éviter les conflits
            $timestamp = time();
            $final_filename = $original_file_name . '_' . $ticketId . '_' . $timestamp . '.' . $extension;
            
            $destination = $upload_dir . DIRECTORY_SEPARATOR . $final_filename;
            
            // Copier le fichier depuis le dossier temporaire
            if (!move_uploaded_file($attachment['tmp_name'], $destination)) {
                // Si move_uploaded_file échoue, essayer copy + unlink
                if (copy($attachment['tmp_name'], $destination)) {
                    @unlink($attachment['tmp_name']);
                } else {
                    throw new Exception("Erreur lors du déplacement du fichier '{$attachment['name']}'.");
                }
            }
            
            // Vérifier que le fichier a été créé
            if (!file_exists($destination)) {
                throw new Exception("Le fichier '{$attachment['name']}' n'a pas pu être sauvegardé.");
            }
            
            // Calculer le chemin relatif pour la base de données
            $root = realpath($_SERVER["DOCUMENT_ROOT"]);
            $relative_path = str_replace($root . DIRECTORY_SEPARATOR, '', $destination);
            $relative_path = str_replace(DIRECTORY_SEPARATOR, '/', $relative_path);
            
            // Si le chemin relatif ne fonctionne pas, utiliser le chemin complet
            if (strpos($relative_path, $root) !== false || !$relative_path) {
                $relative_path = $destination;
            }
            
            // Insérer en base de données
            $this->ticketModel->addAttachment($ticketId, $relative_path);
            $filesProcessed++;
            
        } catch (Exception $e) {
            // Si erreur sur un fichier, supprimer le fichier s'il a été créé
            if (isset($destination) && file_exists($destination)) {
                @unlink($destination);
            }
            throw new Exception("Erreur avec le fichier '{$attachment['name']}': " . $e->getMessage());
        }
    }
}
/************************************************
 * Fonction qui permet d'obtenir le nom filtré de la pièce jointe et vérifie l'extension du fichier.
 * 
 * @param array $attachment Les informations sur la pièce jointe téléchargée
 * @return string Le nom de fichier nettoyé et vérifié
 * @throws Exception Si une erreur survient lors du téléchargement ou si l'extension du fichier est invalide
 ***********************************************/
private function processAttachment($attachment)
{
    // Vérifier s'il y a eu une erreur lors de l'upload du fichier
    if ($attachment['error'] !== UPLOAD_ERR_OK) {
        throw new Exception($this->getUploadErrorMessage($attachment['error']));
    }
    
    // Récupérer le nom original du fichier
    $original_file_name = pathinfo($attachment['name'], PATHINFO_FILENAME);
    
    // Nettoyer le nom de fichier (supprimer les caractères dangereux)
    $original_file_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $original_file_name);
    
    // Récupérer l'extension du fichier
    $extension = strtolower(pathinfo($attachment['name'], PATHINFO_EXTENSION));
    
    // Tableau contenant les extensions autorisées
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'zip', 'rar'];
    
    // Vérifier si l'extension du fichier est autorisée
    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception("Format de fichier non autorisé pour '{$attachment['name']}'. Seuls les fichiers JPG, JPEG, PNG, PDF, DOC, DOCX, ZIP et RAR sont acceptés.");
    }
    
    // Vérifier le type MIME pour plus de sécurité
    $allowedMimeTypes = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'zip' => ['application/zip'],
        'rar' => ['application/x-rar-compressed', 'application/x-rar']
    ];
    
    if (isset($allowedMimeTypes[$extension])) {
        $fileMimeType = mime_content_type($attachment['tmp_name']);
        if (!in_array($fileMimeType, $allowedMimeTypes[$extension])) {
            throw new Exception("Le type de fichier pour '{$attachment['name']}' ne correspond pas à son extension.");
        }
    }
    
    // Générer le nom de la pièce jointe épurée
    $attachment_name = $original_file_name . '.' . $extension;
    
    return $attachment_name;
}

/************************************************
 * Nouvelle méthode pour obtenir des messages d'erreur plus clairs
 ***********************************************/
private function getUploadErrorMessage($errorCode) 
{
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return 'Le fichier dépasse la taille maximale autorisée par PHP.';
        case UPLOAD_ERR_FORM_SIZE:
            return 'Le fichier dépasse la taille maximale autorisée par le formulaire.';
        case UPLOAD_ERR_PARTIAL:
            return 'Le fichier n\'a été que partiellement téléchargé.';
        case UPLOAD_ERR_NO_FILE:
            return 'Aucun fichier n\'a été téléchargé.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Dossier temporaire manquant.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Échec de l\'écriture du fichier sur le disque.';
        case UPLOAD_ERR_EXTENSION:
            return 'Une extension PHP a arrêté le téléchargement du fichier.';
        default:
            return 'Erreur inconnue lors du téléchargement.';
    }
}

/************************************************
 * Fonction qui permet la création d'un ticket dans la base de données.
 * Traite les données du formulaire, crée le ticket et gère les pièces jointes.
 * Envoie également un e-mail de confirmation à l'utilisateur.
 * 
 * @param array $postData Les données du formulaire
 * @param array $filesData Les données sur les fichiers joints téléchargés
 ***********************************************/
public function createTicket($postData, $filesData)
{
    // Validation des données d'entrée
    if (empty($postData['titre']) || 
    empty($postData['produit_id']) || empty($postData['type_id']) || 
    empty($postData['urgence_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Tous les champs obligatoires doivent être remplis.']);
        exit;
    }
    
    // Récupérer les données entrées dans le formulaire
    $titre = $this->getSanitizedInput($postData, 'titre');
    $description = $this->getSanitizedInput($postData, 'description');
    $produit_id = $this->getSanitizedInput($postData, 'produit_id');
    $type_id = $this->getSanitizedInput($postData, 'type_id');
    $urgence_id = $this->getSanitizedInput($postData, 'urgence_id');     
    $date_creation = date('d-m-Y');
    $statut_id = 1; // statut : en cours
    $username = $_SESSION['username'];

    // Convertir les entités HTML en caractères correspondants
    $titre = html_entity_decode($titre);
    $description = html_entity_decode($description);

    // Réécrire les apostrophes pour les stocker correctement en base de données
    $titre = str_replace('&#39;', "'", $titre);
    $description = str_replace('&#39;', "'", $description);

    try {
        // Créer d'abord le ticket (SANS les fichiers)
        $ticketId = $this->ticketModel->createTicket($titre, $description, $date_creation, $statut_id, $urgence_id, $username, $produit_id, $type_id, null);

        // Récupérer l'adresse e-mail de l'utilisateur
        $email = $this->ticketModel->getUserMail($username);

        // Vérifier si le ticket a été créé avec succès
        if ($ticketId && $email) {
            
            // Traiter les pièces jointes SÉPARÉMENT après création du ticket
            $hasAttachments = false;
            $attachmentError = null;
            
            if (isset($filesData['attachment']) && 
                isset($filesData['attachment']['name']) && 
                !empty($filesData['attachment']['name'])) {
                
                // Vérifier s'il y a vraiment des fichiers valides
                if (is_array($filesData['attachment']['name'])) {
                    foreach ($filesData['attachment']['name'] as $filename) {
                        if (!empty($filename)) {
                            $hasAttachments = true;
                            break;
                        }
                    }
                } else if (!empty($filesData['attachment']['name'])) {
                    $hasAttachments = true;
                }
            }
            
            // SEULEMENT si il y a des fichiers, les traiter
            if ($hasAttachments) {
                try {
                    $this->createAttachments($ticketId, $filesData);
                } catch (Exception $e) {
                    $attachmentError = $e->getMessage();
                    // On continue même si les fichiers échouent, le ticket est créé
                }
            }

            // Enregistrer l'événement de création
            $statut_evenement_id = 1; // 1 = Opened
            try {
                $this->logEvent($ticketId, $username, $statut_evenement_id);
            } catch (Exception $e) {
                // Log l'erreur mais continue
            }

            // Envoi de l'e-mail
            try {
                $adminEmail = 'frederic.zitta@forsim.net';
                $to = $email;
                $subject = 'Ticket créé avec succès';
                $message = "Cher utilisateur,\n\nVotre ticket a été créé avec succès.\n\nTitre du ticket : $titre\nDescription : $description\n\nMerci de votre demande.\nCordialement,\nVotre équipe de support.";
                $headers = 'From: ' . $adminEmail . "\r\n" .
                        'Reply-To: ' . $adminEmail . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();

                mail($to, $subject, $message, $headers);
            } catch (Exception $e) {
                // L'email a échoué mais on continue
            }

            // Succès - réponse JSON
            header('Content-Type: application/json');
            if ($attachmentError) {
                echo json_encode([
                    'status' => 'warning', 
                    'message' => "Ticket créé avec succès, mais erreur avec les fichiers: $attachmentError"
                ]);
            } else {
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Ticket créé avec succès' . ($hasAttachments ? ' avec fichiers joints' : '')
                ]);
            }
            exit;
        } else {
            throw new Exception('Échec de la création du ticket ou récupération de l\'email utilisateur');
        }
        
    } catch (Exception $e) {
        // Erreur - réponse JSON
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la création du ticket: ' . $e->getMessage()]);
        exit;
    }
}

    /************************************************
     * Fonction permettant de sauvegarder un événement lié à un ticket dans la base de données.
     * 
     * @param int $ticket_id L'identifiant du ticket concerné
     * @param string $username Le nom d'utilisateur associé à l'événement
     * @param int $statut_evenement_id L'identifiant du type d'événement à enregistrer
     * @throws Exception Si une erreur survient lors de l'enregistrement de l'événement
     ***********************************************/
    public function logEvent($ticket_id, $username, $statut_evenement_id)
    {
        try {
            $this->ticketModel->logEvent($ticket_id, $username, $statut_evenement_id);
        } catch (Exception $e) {
            // Gérer l'exception en enregistrant l'erreur dans le journal des erreurs
            error_log("Failed to log event: " . $e->getMessage());
            throw new Exception('Failed to log event.');
        }
    }

    /************************************************
     * Fonction permettant de récupérer l'historique des événements d'un utilisateur ou administrateur concernant un produit spécifique.
     * Charge la vue avec les événements récupérés.
     * 
     * @param int $produit_id L'identifiant du produit concerné
     ***********************************************/
    public function getUserEventHistory($produit_id){
        $username = $_SESSION['username'];
        $evenements = $this->ticketModel->getUserEventHistory($produit_id, $username);
        $this->render('index', ['evenements' => $evenements]);
        $this->render('tickets/details/details_ticket_view', ['evenements' => $evenements]);
        exit;
    }

    /************************************************
     * Fonction qui récupère le contenu de la table urgence depuis le modèle et le passe à la vue.
     * Charge la vue de création de ticket avec les données des urgences.
     ***********************************************/
   public function getUrgence()
{
    $username = $_SESSION['username'];
    
    // Récupérer toutes les données nécessaires
    $urgences = $this->ticketModel->getUrgence();
    $types = $this->ticketModel->getType();
    $produits = $this->ticketModel->getProduit();
    $userProduits = $this->ticketModel->getProduitsByUser($username);
    
    // Debug pour vérifier les données
    error_log("=== DEBUG CREATE TICKET ===");
    error_log("Username: " . $username);
    error_log("Urgences count: " . count($urgences));
    error_log("Types count: " . count($types));
    error_log("User produits count: " . count($userProduits));
    error_log("===========================");
    
    $this->render('tickets/create_ticket_view', [
        'urgences' => $urgences,
        'types' => $types,
        'produits' => $produits,
        'userProduits' => $userProduits
    ]);
    exit; 
}

    /************************************************
     * Fonction qui récupère le contenu de la table produit depuis le modèle et le passe à la vue.
     * Charge la vue latérale avec les données des produits.
     ***********************************************/
    public function getProduit()
    {
        // Récupère les données de la table produit depuis le modèle
        $produits = $this->ticketModel->getProduit();
        // Charge la vue latérale avec les données des produits
        $this->render('sidebar', ['produits' => $produits]);
        exit;
    }

    /************************************************
     * Fonction qui récupère le contenu de la table type depuis le modèle et le passe à la vue.
     * Charge la vue de création de ticket avec les données des types.
     ***********************************************/
    public function getType()
    {
        $types = $this->ticketModel->getType();
        //var_dump($types);
        $this->render('tickets/create_ticket_view', ['types' => $types]);
    exit; 
    }

    /************************************************
     * Fonction qui récupère les données des tickets ouverts créés par l'utilisateur depuis le modèle
     * et les passe à la vue des tickets ouverts.
     * 
     * @param int $produit_id L'identifiant du produit concerné
     ***********************************************/
    public function getOpenTicket($produit_id) {

        $username = $_SESSION['username'];
        $tickets = $this->ticketModel->getOpenTicket($produit_id, $username);
        $this->render('tickets/open_tickets_view', ['tickets' => $tickets]);
        exit; 
    }

    /************************************************
     * Fonction qui récupère les données des tickets résolus créés par l'utilisateur depuis le modèle
     * et les passe à la vue des tickets résolus.
     * 
     * @param int $selectedProduct L'identifiant du produit sélectionné
     ***********************************************/
    public function getSolvedTicket($selectedProduct) 
    {
        $username = $_SESSION['username'];
        $tickets = $this->ticketModel->getSolvedTicket($selectedProduct, $username);
        $this->render('tickets/solved_tickets_view', ['tickets' => $tickets, 'selectedProduct' => $selectedProduct]);
        exit; 
    }

    /************************************************
     * Fonction qui récupère les données des tickets fermés créés par l'utilisateur depuis le modèle
     * et les passe à la vue des tickets fermés.
     * 
     * @param int $produit_id L'identifiant du produit concerné
     ***********************************************/
    public function getClosedTicket($produit_id) 
    {
        $username = $_SESSION['username'];
        $tickets = $this->ticketModel->getClosedTicket($produit_id, $username);
        $this->render('tickets/closed_tickets_view', ['tickets' => $tickets, 'produit_id' => $produit_id]);
        exit; 
    }

    /************************************************
     * Fonction qui récupère les détails d'un ticket ouvert et les événements associés depuis le modèle
     * et les passe à la vue des détails du ticket.
     * 
     * @param int $ticket_id L'identifiant du ticket concerné
     ***********************************************/
    public function getTicketDetailsAndEvents($ticket_id, $username) 
    {
        $username = $_SESSION['username'];
        $ticket = $this->ticketModel->getTicketDetails($ticket_id, $username);
        $attachments = $this->ticketModel->getAttachmentsTicket($ticket_id,);
        $evenements = $this->ticketModel->getTicketEventHistory($ticket_id, $username);
        $this->render('tickets/details/details_ticket_view', ['ticket' => $ticket, 'attachments' => $attachments, 'username'=> $username, 'evenements' => $evenements]);
        exit; 
    }

    /************************************************
     * Fonction qui récupère les détails d'un ticket fermé et les événements associés depuis le modèle
     * et les passe à la vue des détails du ticket fermé.
     * 
     * @param int $ticket_id L'identifiant du ticket concerné
     ***********************************************/
    public function getClosedDetails($ticket_id, $username) 
    {
        $ticket = $this->ticketModel->getClosedDetails($ticket_id, $username);
        $attachments = $this->ticketModel->getAttachmentsTicket($ticket_id);
        $evenements = $this->ticketModel->getTicketEventHistory($ticket_id, $username);
        $this->render('tickets/details/closed_details_view', ['ticket' => $ticket, 'attachments' => $attachments, 'username'=> $username, 'evenements' => $evenements]);
        exit; 
    }


    /************************************************
     * Fonction qui récupère les détails d'un ticket résolu depuis le modèle
     * et les passe à la vue des détails du ticket résolu.
     * 
     * @param int $ticket_id L'identifiant du ticket concerné
     ***********************************************/
    public function getSolvedDetails($ticket_id, $username) 
    {
        $ticket = $this->ticketModel->getSolvedDetails($ticket_id, $username);
        $attachments = $this->ticketModel->getAttachmentsTicket($ticket_id);
        $evenements = $this->ticketModel->getTicketEventHistory($ticket_id, $username);
        $this->render('tickets/details/solved_details_view', ['ticket' => $ticket, 'attachments' => $attachments, 'username'=> $username, 'evenements' => $evenements]);       
        exit; 
    }

    /************************************************
     * Fonction qui récupère les détails d'un ticket_save depuis le modèle
     * et les passe à la vue des détails du ticket_save.
     * 
     * @param int $ticket_id L'identifiant du ticket_save concerné
     ***********************************************/
    public function getTicketSaveDetails($ticket_id, $username)
    {
        $ticket = $this->ticketModel->getTicketDetails($ticket_id, $username);
        $attachments = $this->ticketModel->getAttachmentsTicket($ticket_id);
        $ticketsave = $this->ticketModel->getTicketSaveDetails($ticket_id, $username);

        $this->render('tickets/details/ticket_save_details_view', [
            'ticket' => $ticket,
            'attachments' => $attachments,
            'ticketsave' => $ticketsave
        ]);
        exit;
    }

    /************************************************
     * Fonction qui récupère les détails d'un ticket_save résolu depuis le modèle
     * et les passe à la vue des détails du ticket_save résolu.
     * 
     * @param int $ticket_id L'identifiant du ticket_save résolu concerné
     ***********************************************/
    public function getTicketSolvedSaveDetails($ticket_id, $username)
    {
        $ticket = $this->ticketModel->getSolvedDetails($ticket_id, $username);
        $attachments = $this->ticketModel->getAttachmentsTicket($ticket_id);
        $ticketsave = $this->ticketModel->getTicketSaveDetails($ticket_id, $username);

        $this->render('tickets/details/ticket_save_solved_details_view', [
            'ticket' => $ticket,
            'attachments' => $attachments,
            'ticketsave' => $ticketsave
        ]);
        exit;
    }

    /************************************************
     * Fonction qui récupère les détails d'un ticket_save fermé depuis le modèle
     * et les passe à la vue des détails du ticket_save fermé.
     * 
     * @param int $ticket_id L'identifiant du ticket_save fermé concerné
     ***********************************************/
    public function getTicketClosedSaveDetails($ticket_id, $username)
    {
        $ticket = $this->ticketModel->getClosedDetails($ticket_id, $username);
        $attachments = $this->ticketModel->getAttachmentsTicket($ticket_id);
        $ticketsave = $this->ticketModel->getTicketSaveDetails($ticket_id, $username);

        $this->render('tickets/details/ticket_save_closed_details_view', [
            'ticket' => $ticket,
            'attachments' => $attachments,
            'ticketsave' => $ticketsave
        ]);
        exit;
    }

    /************************************************
     * Fonction qui change le statut du ticket à "ouvert" dans la base de données
     * et gère les erreurs éventuelles.
     * 
     * @param int $ticket_id L'identifiant du ticket à ouvrir
     ***********************************************/
    public function setOpen($ticket_id) 
    {
        $rowsAffected = $this->ticketModel->setOpen($ticket_id);
        
        if ($rowsAffected > 0) {
            // La requête a réussi, créer un événement pour la fermeture du ticket
            $username = $_SESSION['username'];
            $statut_evenement_id = 2; // 1 = Opened, 2 = Re-Opened, 3 = Solved, 4 = Closed, 5 = Attachment added, 6 = Attachment deleted

            try {
                $this->logEvent($ticket_id, $username, $statut_evenement_id);
            } catch (Exception $e) {
                // Gérer l'exception selon les besoins
                error_log("Failed to log event: " . $e->getMessage());
            }

            // La requête a réussi, renvoyez une réponse de succès
            echo json_encode(['success' => true]);
            exit;
        } else {
            // La requête a échoué, renvoyez une erreur
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update ticket status']);
            exit;
        }
    }

    /************************************************
     * Fonction qui change le statut du ticket à "fermé" dans la base de données
     * et gère les erreurs éventuelles.
     * 
     * @param int $ticket_id L'identifiant du ticket à fermer
     ***********************************************/
    public function setClose($ticket_id) 
    {
        $rowsAffected = $this->ticketModel->setClose($ticket_id);
        
        if ($rowsAffected > 0) {
            // La requête a réussi, créer un événement pour la fermeture du ticket
            $username = $_SESSION['username'];
            $statut_evenement_id = 4; // 4 = Closed

            try {
                $this->logEvent($ticket_id, $username, $statut_evenement_id);
            } catch (Exception $e) {
                error_log("Failed to log event: " . $e->getMessage());
            }

            // Renvoie une réponse JSON avec success=true
            echo json_encode(['success' => true]);
            exit;
        } else {
            // La requête a échoué, renvoie une réponse JSON avec une erreur
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update ticket status']);
            exit;
        }
    }


    /************************************************
     * Fonction qui change le statut du ticket à "résolu" dans la base de données
     * et gère les erreurs éventuelles.
     * 
     * @param int $ticket_id L'identifiant du ticket à résoudre
     ***********************************************/
    public function setSolve($ticket_id) 
    {
        
        $rowsAffected = $this->ticketModel->setSolve($ticket_id);
            
        if ($rowsAffected > 0) {
            // La requête a réussi, créer un événement pour la fermeture du ticket
            $username = $_SESSION['username'];
            $statut_evenement_id = 3; // 3 = Solved

            try {
                $this->logEvent($ticket_id, $username, $statut_evenement_id);
            } catch (Exception $e) {
                // Gérer l'exception selon les besoins
                error_log("Failed to log event: " . $e->getMessage());
            }
            // La requête a réussi, renvoyez une réponse JSON avec success=true
            echo json_encode(['success' => true]);
            exit;
        } else {
            // La requête a échoué, renvoyez une réponse JSON avec une erreur
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update ticket status']);
            exit;
        }
    }

    /************************************************
     * Fonction qui récupère tous les messages d'un ticket depuis le modèle
     * et les passe à la vue des messages de chat du ticket.
     * 
     * @param int $ticket_id L'identifiant du ticket concerné
     * @param string $username Le nom d'utilisateur actuel
     ***********************************************/
    public function getTicketMessages($ticket_id, $username) {
        $ticket = $this->ticketModel->getTicketDetails($ticket_id, $username);
        $allMessages = $this->ticketModel->getAllTicketMessages($ticket_id, $username);
        $this->render('tickets/details/ticket_chat_view', ['ticket' => $ticket, 'messages' => $allMessages]);
    }

    /************************************************
     * Fonction qui récupère tous les messages d'un ticket résolu depuis le modèle
     * et les passe à la vue des messages de chat du ticket résolu.
     * 
     * @param int $ticket_id L'identifiant du ticket résolu concerné
     * @param string $username Le nom d'utilisateur actuel
     ***********************************************/
    public function getTicketMessagesSolved($ticket_id, $username) {
        $ticket = $this->ticketModel->getSolvedDetails($ticket_id, $username);
        $allMessages = $this->ticketModel->getAllTicketMessages($ticket_id, $username);
        $this->render('tickets/details/ticket_chat_solved_view', ['ticket' => $ticket, 'messages' => $allMessages]);
    }

    /************************************************
     * Fonction qui récupère tous les messages d'un ticket fermé depuis le modèle
     * et les passe à la vue des messages de chat du ticket fermé.
     * 
     * @param int $ticket_id L'identifiant du ticket fermé concerné
     * @param string $username Le nom d'utilisateur actuel
     ***********************************************/
    public function getTicketMessagesClosed($ticket_id, $username) {
        $ticket = $this->ticketModel->getClosedDetails($ticket_id, $username);
        $allMessages = $this->ticketModel->getAllTicketMessages($ticket_id, $username);
        $this->render('tickets/details/ticket_chat_closed_view', ['ticket' => $ticket, 'messages' => $allMessages]);
    }

    /************************************************
     * Fonction qui permet d'envoyer un message dans un ticket ouvert
     * et gère les erreurs éventuelles.
     * 
     * @param array $postData Les données POST envoyées
     * @param string $username Le nom d'utilisateur actuel
     ***********************************************/
    public function sendMessage($postData, $username){
        $username = $_SESSION['username'];
        $statut_evenement_id = 8; // 8 = Message
        // Valide et nettoie les données
        $ticket_id = $this->getSanitizedInput($postData, 'ticket_id');
        $sendMessage = $this->getSanitizedInput($postData, 'message');
        $this->ticketModel->sendMessage($ticket_id, $sendMessage, $username);
        $this->ticketModel->logEvent($ticket_id, $username, $statut_evenement_id);
        header('Location: /ticketsApp/config/routes.php/ticket-message/' . $ticket_id);
        exit;
    }

    /************************************************
     * Fonction qui permet d'envoyer un message dans un ticket résolu
     * et gère les erreurs éventuelles.
     * 
     * @param array $postData Les données POST envoyées
     * @param string $username Le nom d'utilisateur actuel
     ***********************************************/
    public function sendMessageSolved($postData, $username){
        $username = $_SESSION['username'];
        $statut_evenement_id = 8; // 8 = Message
        // Valide et nettoie les données
        $ticket_id = $this->getSanitizedInput($postData, 'ticket_id');
        $sendMessage = $this->getSanitizedInput($postData, 'message');
        $this->ticketModel->sendMessage($ticket_id, $sendMessage, $username);
        $this->ticketModel->logEvent($ticket_id, $username, $statut_evenement_id);
        header('Location: /ticketsApp/config/routes.php/ticket-message-solved/' . $ticket_id);
        exit;
    }

    /************************************************
     * Fonction qui permet d'envoyer un message dans un ticket fermé
     * et gère les erreurs éventuelles.
     * 
     * @param array $postData Les données POST envoyées
     * @param string $username Le nom d'utilisateur actuel
     ***********************************************/
    public function sendMessageClosed($postData, $username){
        $username = $_SESSION['username'];
        $statut_evenement_id = 8; // 8 = Message
        // Valide et nettoie les données
        $ticket_id = $this->getSanitizedInput($postData, 'ticket_id');
        $sendMessage = $this->getSanitizedInput($postData, 'message');
        $this->ticketModel->sendMessage($ticket_id, $sendMessage, $username);
        $this->ticketModel->logEvent($ticket_id, $username, $statut_evenement_id);
        header('Location: /ticketsApp/config/routes.php/ticket-message-closed/' . $ticket_id);
        exit;
    }

    /************************************************
     * Fonction qui permet de mettre à jour les détails d'un ticket dans la base de données
     * et enregistre un événement pour le changement de texte.
     * 
     * @param array $postData Les données POST envoyées
     ***********************************************/
    public function updateTicketsDetails($postData) {
        $username = $_SESSION['username'];
        $statut_evenement_id = 7; // 7 = Text Changed
        // Valide et nettoie les données
        $ticket_id = $this->getSanitizedInput($postData, 'ticket_id');
        $newTitle = $this->getSanitizedInput($postData, 'titre');
        $newDescription = $this->getSanitizedInput($postData, 'description');
        $this->ticketModel->updateTicketsDetails($ticket_id, $newTitle, $newDescription);
        $this->ticketModel->logEvent($ticket_id, $username, $statut_evenement_id);
        header('Location: /ticketsApp/config/routes.php/opened');
        exit;
    }

    /************************************************
     * Fonction qui supprime une pièce jointe d'un ticket
     * et enregistre un événement pour la suppression de la pièce jointe.
     * 
     * @param int $attachmentId L'identifiant de la pièce jointe à supprimer
     ***********************************************/
    public function deleteAttachment($attachmentId)
    {
        // Récupère le chemin du fichier à partir de la base de données
        $attachment = $this->ticketModel->getAttachment($attachmentId);

        if ($attachment) {
            // Convertit le chemin relatif en chemin absolu
            $file_path = $_SERVER['DOCUMENT_ROOT'] . "/" . $attachment['filename'];

            // Vérifie que le fichier existe avant d'essayer de le supprimer
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            // Supprime l'entrée de la base de données
            $this->ticketModel->deleteAttachment($attachmentId);

            // Créer un événement pour la suppression de la pièce jointe
            $username = $_SESSION['username'];
            $ticket_id = $attachment['ticket_id'];
            $statut_evenement_id = 6; // 6 = Attachment deleted

            try {
                $this->logEvent($ticket_id, $username, $statut_evenement_id);
            } catch (Exception $e) {
                // Gérer l'exception selon les besoins
                error_log("Failed to log event: " . $e->getMessage());
            }

            echo 'Attachment deleted.';
        } else {
            echo 'No attachment found with this ID.';
        }
    }

    /************************************************
     * Fonction qui ajoute une pièce jointe à un ticket existant.
     * 
     * @param int $ticket_id L'identifiant du ticket auquel ajouter la pièce jointe
     * @param array $filesData Les données de fichiers téléchargés
     ***********************************************/
    public function ModifAttachment($ticket_id, $filesData)
    {
        $username = $_SESSION['username'];
        // Récupère l'id du ticket à partir de l'url
        $ticketId = $this->ticketModel->getTicketDetails($ticket_id, $username)['ticket_id'];
        try 
        {
            $this->createAttachments($ticketId, $filesData);
            echo 'Attachment added.';
        } catch (Exception $e)
         {
            echo "Attachment wasn't added.";
        }

    }

    /************************************************
     * Fonction qui récupère toutes les pièces jointes d'un ticket depuis le modèle
     * et renvoie les données en JSON.
     * 
     * @param int $ticket_id L'identifiant du ticket concerné
     ***********************************************/
    public function getAttachmentsTicket($ticket_id)
    {
        $attachments = $this->ticketModel->getAttachmentsTicket($ticket_id);
        header('Content-Type: application/json');
        echo json_encode($attachments);
        exit; 
    }

    /************************************************
     * Fonction qui récupère les produits associés à l'utilisateur actuel depuis le modèle
     * et les passe à la vue pour affichage dans la barre latérale.
     ***********************************************/
    public function displayProducts()
    {
        $username = $_SESSION['username'];
        $produits = $this->ticketModel->getProduitsByUser($username);
        $firstproduit = $this->ticketModel->getFirstProduitByUser($username);
        $this->render('index', ['produits' => $produits, 'firstproduit' => $firstproduit]);
    }
}