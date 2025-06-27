<?php
/************************************************
 * Classe AdminModel gérant les opérations liées aux utilisateurs et aux tickets dans la base de données.
 *
 * Cette classe fournit des méthodes pour interagir avec la base de données concernant les utilisateurs
 * et les tickets. Elle inclut des fonctionnalités telles que la création, la récupération, la mise à jour
 * et la suppression de données relatives aux utilisateurs et aux tickets. Voici un résumé des fonctionnalités :
 * - Gestion des utilisateurs :
 *   - Récupération des informations d'un utilisateur par son nom d'utilisateur.
 *   - Modification du mot de passe d'un utilisateur.
 *   - Récupération des produits associés à un utilisateur spécifique.
 *   - Récupération de l'adresse email d'un utilisateur.
 * - Gestion des tickets :
 *   - Création d'un nouveau ticket.
 *   - Récupération de tous les tickets ouverts, fermés, résolus, etc., pour un produit donné.
 *   - Récupération des détails d'un ticket spécifique (ouvert, fermé, résolu, etc.).
 *   - Ajout et récupération de pièces jointes à un ticket.
 *   - Envoi et récupération de messages dans le contexte d'un ticket spécifique.
 *   - Mise à jour des détails d'un ticket (titre, description).
 *   - Enregistrement de l'historique des modifications d'un ticket.
 * - Gestion des événements :
 *   - Récupération de l'historique complet des événements pour un produit donné.
 * 
 * Les méthodes de cette classe utilisent des requêtes préparées PDO pour assurer la sécurité
 * des données et prévenir les attaques par injection SQL.
 ***********************************************/
class AdminModel
{
    private $db; /**< Objet PDO pour la connexion à la base de données */

    /************************************************
     * Constructeur de la classe AdminModel.
     *
     * @param PDO $db Objet PDO représentant la connexion à la base de données.
     ***********************************************/
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /************************************************
     * Fonction pour créer un nouvel utilisateur dans la base de données.
     *
     * @param string $username Nom d'utilisateur.
     * @param string $mail Adresse email de l'utilisateur.
     * @param string $entreprise Nom de l'entreprise de l'utilisateur.
     * @param string $hashedPassword Mot de passe haché de l'utilisateur.
     * @param string $role Rôle de l'utilisateur.
     * @return bool Succès ou échec de l'ajout de l'utilisateur.
     * @throws Exception En cas d'échec de l'exécution de la requête SQL.
     ***********************************************/
    public function createUser($username, $mail, $entreprise, $hashedPassword, $role)
    {
        $query = "INSERT INTO user (username, mail, entreprise, pwd, role) VALUES (:username, :mail, :entreprise, :pwd, :role)";
        $statement = $this->db->prepare($query);
        $statement->bindValue(':username', $username);
        $statement->bindValue(':mail', $mail);
        $statement->bindValue(':entreprise', $entreprise);
        $statement->bindValue(':pwd', $hashedPassword);
        $statement->bindValue(':role', $role);
        // Vérification si l'execution est réussite sinon erreur
        if ($statement->execute()) {
            //header('Location: /ticketsApp/config/routes.php/user_details/' . urlencode($username));
            return true;
        } else 
        {
            error_log("Failed to execute SQL query: " . $query);
            throw new Exception('Failed to add attachment to the database.');
        }
    }

    /************************************************
     * Fonction pour associer les produits sélectionnés à un nouvel utilisateur.
     *
     * @param string $username Nom d'utilisateur.
     * @param array $selectedProducts Tableau contenant les IDs des produits sélectionnés.
     * @throws Exception En cas d'échec de l'exécution de la requête SQL.
     ***********************************************/
    public function addProduitUser($username, $selectedProducts)
    {
        foreach ($selectedProducts as $produit_id) {
            $query = "INSERT INTO user_produit (username, produit_id) VALUES (:username, :produit_id)";
            $statement = $this->db->prepare($query);
            $statement->bindValue(':username', $username);
            $statement->bindValue(':produit_id', $produit_id);

            if (!$statement->execute()) {
                error_log("Failed to execute SQL query: " . $query);
                throw new Exception('Failed to associate user with products.');
            }
        }
    }

    /************************************************
     * Fonction pour vérifier si un nom d'utilisateur existe déjà dans la base de données.
     *
     * @param string $username Nom d'utilisateur à vérifier.
     * @return bool True si le nom d'utilisateur existe déjà, sinon False.
     ***********************************************/
    public function isUsernameExists($username)
    {
        $query = "SELECT COUNT(*) FROM user WHERE username = :username";
        $statement = $this->db->prepare($query);
        $statement->bindValue(':username', $username);
        $statement->execute();
        $count = $statement->fetchColumn();
        return $count > 0;
    }

    /************************************************
     * Fonction pour enregistrer un événement associé à un ticket dans la base de données.
     *
     * @param int $ticket_id ID du ticket associé à l'événement.
     * @param string $username Nom d'utilisateur associé à l'événement.
     * @param int $statut_evenement_id ID du statut de l'événement.
     * @throws Exception En cas d'échec de l'exécution de la requête SQL ou si le ticket n'est pas trouvé.
     ***********************************************/
   public function logEvent($ticket_id, $username, $statut_evenement_id)
    {
        try {
            // Vérifiez si un événement similaire existe déjà
            $queryCheck = "SELECT COUNT(*) FROM evenement 
                           WHERE ticket_id = :ticket_id 
                           AND statut_evenement_id = :statut_evenement_id";
            $stmtCheck = $this->db->prepare($queryCheck);
            $stmtCheck->execute([
                ':ticket_id' => $ticket_id,
                ':statut_evenement_id' => $statut_evenement_id
            ]);
            $exists = $stmtCheck->fetchColumn();

            if ($exists > 0) {
                // Si l'événement existe déjà, ne pas l'insérer à nouveau
                return;
            }

            // Récupérer les informations du ticket
            $query = "SELECT titre, description, produit_id FROM ticket WHERE ticket_id = :ticket_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':ticket_id', $ticket_id);
            $stmt->execute();
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ticket) {
                throw new Exception('Ticket non trouvé.');
            }

            // Insérer l'événement
            $queryInsert = "INSERT INTO evenement (ticket_id, username, date_evenement, statut_evenement_id, titre, description, produit_id) 
                            VALUES (:ticket_id, :username, NOW(), :statut_evenement_id, :titre, :description, :produit_id)";
            $stmtInsert = $this->db->prepare($queryInsert);
            $stmtInsert->execute([
                ':ticket_id' => $ticket_id,
                ':username' => $username,
                ':statut_evenement_id' => $statut_evenement_id,
                ':titre' => $ticket['titre'],
                ':description' => $ticket['description'],
                ':produit_id' => $ticket['produit_id']
            ]);
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement de l'événement: " . $e->getMessage());
            throw $e;
        }
    }

    /************************************************
     * Fonction pour ajouter un ticket dans la table ticket.
     *
     * @param string $titre Titre du ticket.
     * @param string $description Description du ticket.
     * @param string $date_creation Date et heure de création du ticket.
     * @param int $statut_id ID du statut du ticket.
     * @param int $urgence_id ID de l'urgence du ticket.
     * @param string $username Nom d'utilisateur associé au ticket.
     * @param int $produit_id ID du produit associé au ticket.
     * @param int $type_id ID du type du ticket.
     * @param array|null $filesData Données des fichiers joints au ticket.
     * @return int|false L'ID du ticket créé ou false en cas d'échec.
     ***********************************************/
  public function createTicket($titre, $description, $date_creation, $statut_id, $urgence_id, $username, $produit_id, $type_id, $filesData = null)
{
    try {
        $this->db->beginTransaction();

        // Insertion du ticket
        $query = "INSERT INTO ticket (titre, description, date_creation, statut_id, urgence_id, username, produit_id, type_id) 
                VALUES (:titre, :description, :date_creation, :statut_id, :urgence_id, :username, :produit_id, :type_id)";
        
        $statement = $this->db->prepare($query);
        $statement->bindValue(':titre', $titre);
        $statement->bindValue(':description', $description);
        $statement->bindValue(':date_creation', $date_creation);
        $statement->bindValue(':statut_id', $statut_id);
        $statement->bindValue(':urgence_id', $urgence_id);
        $statement->bindValue(':username', $username);
        $statement->bindValue(':produit_id', $produit_id);
        $statement->bindValue(':type_id', $type_id);
        
        $statement->execute();
        $ticketId = $this->db->lastInsertId();

        // Log de l'événement de création du ticket
        $this->logEvent($ticketId, $username, 1); // 1 = Création du ticket

        // Traitement des pièces jointes
        if ($filesData && isset($filesData['attachment']) && !empty($filesData['attachment']['name'][0])) {
            $this->handleAttachments($ticketId, $filesData);
        }

        $this->db->commit();
        return $ticketId;

    } catch (Exception $e) {
        $this->db->rollBack();
        error_log("Erreur création ticket: " . $e->getMessage());
        throw $e;
    }
}

private function handleAttachments($ticketId, $filesData) 
{
    try {
        $maxFileSize = 10 * 1024 * 1024; // 10MB
        $upload_dir = __DIR__ . '/../upload';
        
        // Vérifiez si le dossier upload existe, sinon créez-le
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($filesData['attachment']['name'] as $key => $name) {
            if ($filesData['attachment']['error'][$key] === UPLOAD_ERR_OK) {
                if ($filesData['attachment']['size'][$key] > $maxFileSize) {
                    continue; // Ignorez les fichiers trop volumineux
                }

                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'zip', 'rar'];
                
                if (!in_array($extension, $allowedExtensions)) {
                    continue; // Ignorez les fichiers avec des extensions non autorisées
                }

                // Générer un nom de fichier unique
                $new_filename = uniqid() . '_' . $ticketId . '.' . $extension;
                $destination = $upload_dir . '/' . $new_filename;

                // Déplacez le fichier téléchargé vers le dossier upload
                if (move_uploaded_file($filesData['attachment']['tmp_name'][$key], $destination)) {
                    // Insérez le chemin relatif dans la table attachments
                    $relative_path = 'app/src/upload/' . $new_filename;
                    $queryAttachment = "INSERT INTO attachments (ticket_id, filename) VALUES (:ticket_id, :filename)";
                    $stmtAttachment = $this->db->prepare($queryAttachment);
                    $stmtAttachment->execute([
                        ':ticket_id' => $ticketId,
                        ':filename' => $relative_path
                    ]);
                } else {
                    throw new Exception("Erreur lors du déplacement du fichier : $name");
                }
            }
        }
    } catch (Exception $e) {
        error_log("Erreur lors du traitement des pièces jointes : " . $e->getMessage());
        throw $e;
    }
}

    /************************************************
     * Fonction pour ajouter un ticket dans la table ticket_save.
     *
     * @param int $ticketId ID du ticket à sauvegarder.
     * @param string $titre Titre du ticket.
     * @param string $description Description du ticket.
     * @param string $date_creation Date et heure de création du ticket.
     * @param int $statut_id ID du statut du ticket.
     * @param int $urgence_id ID de l'urgence du ticket.
     * @param string $username Nom d'utilisateur associé au ticket.
     * @param int $produit_id ID du produit associé au ticket.
     * @param int $type_id ID du type du ticket.
     ***********************************************/
    public function createTicketSave($ticketId, $titre, $description, $date_creation, $statut_id, $urgence_id, $username, $produit_id, $type_id)
    {
        $query = "INSERT INTO ticket_save (ticket_id, titre, description, date_creation, statut_id, urgence_id, username, produit_id, type_id) 
                VALUES (:ticket_id, :titre, :description, :date_creation, :statut_id, :urgence_id, :username, :produit_id, :type_id)";
        $statement = $this->db->prepare($query);
        $statement->bindValue(':ticket_id', $ticketId);
        $statement->bindValue(':titre', $titre);
        $statement->bindValue(':description', $description);
        $statement->bindValue(':date_creation', $date_creation);
        $statement->bindValue(':statut_id', $statut_id);
        $statement->bindValue(':urgence_id', $urgence_id);
        $statement->bindValue(':username', $username);
        $statement->bindValue(':produit_id', $produit_id);
        $statement->bindValue(':type_id', $type_id);
        $statement->execute();
    }
/************************************************
     * Fonction pour récupérer toutes les données de la table type.
     * @return array Tableau associatif contenant les données des types de tickets.
     ***********************************************/
    public function getType() 
    {
        $query = "SELECT * FROM type";
        $statement = $this->db->prepare($query);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /************************************************
     * Fonction pour récupérer toutes les données de la table urgence.
     * @return array Tableau associatif contenant les données des urgences.
     ***********************************************/
    public function getUrgence() 
    {
        $query = "SELECT * FROM urgence";
        $statement = $this->db->prepare($query);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    /************************************************
     * Fonction pour ajouter une pièce jointe à un ticket dans la base de données.
     *
     * @param int $ticketId ID du ticket auquel la pièce jointe est ajoutée.
     * @param string $filename Nom du fichier de la pièce jointe.
     * @return bool True si l'ajout de la pièce jointe est réussi, sinon lance une exception.
     * @throws Exception En cas d'échec de l'exécution de la requête SQL pour l'ajout de la pièce jointe.
     ***********************************************/
    public function addAttachment($ticketId, $filename)
    {
        $query = "INSERT INTO attachments (ticket_id, filename) VALUES (:ticket_id, :filename)";
        $statement = $this->db->prepare($query);
        $statement->bindValue(':ticket_id', $ticketId);
        $statement->bindValue(':filename', $filename);

        // Vérification si l'exécution est réussite sinon erreur
        if ($statement->execute()) {
            // Créer un événement pour l'ajout de la pièce jointe
            $username = $_SESSION['username'];
            $statut_evenement_id = 5; // 5 = Attachment added

            try {
                $this->logEvent($ticketId, $username, $statut_evenement_id);
            } catch (Exception $e) {
                // Gérer l'exception selon les besoins
                error_log("Failed to log event: " . $e->getMessage());
            }

            return true;
        } else {
            error_log("Failed to execute SQL query: " . $query);
            throw new Exception('Failed to add attachment to the database.');
        }
    }

    /************************************************
     * Fonction pour récupérer l'historique de tous les événements associés à un produit spécifique.
     *
     * @param int $produit_id ID du produit pour lequel on souhaite récupérer l'historique des événements.
     * @return array Tableau associatif contenant les événements.
     ***********************************************/
    public function getAllEventHistory($produit_id)
    {
        $query = "SELECT e.evenement_id, e.ticket_id, DATE_FORMAT(e.date_evenement, '%d/%m/%Y %H:%i:%s') as date_evenement, e.username, se.event_type, e.titre, e.description, p.nom_produit, t.statut_id 
                FROM evenement e
                INNER JOIN statut_evenement se ON e.statut_evenement_id = se.statut_evenement_id
                INNER JOIN ticket t ON e.ticket_id = t.ticket_id
                INNER JOIN produit p ON p.produit_id = t.produit_id
                WHERE p.produit_id = :produit_id
                ORDER BY e.date_evenement DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':produit_id' => $produit_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /************************************************
     * Fonction pour récupérer l'historique des événements associés à un ticket spécifique.
     *
     * @param int $ticket_id ID du ticket pour lequel on souhaite récupérer l'historique des événements.
     * @return array Tableau associatif contenant l'historique des événements du ticket.
     ***********************************************/
    public function getTicketEventHistory($ticket_id)
    {
        $query = "SELECT e.evenement_id, e.ticket_id, DATE_FORMAT(e.date_evenement, '%d/%m/%Y %H:%i:%s') as date_evenement, e.username, se.event_type, e.titre, p.nom_produit, e.description, t.statut_id 
                FROM evenement e
                INNER JOIN statut_evenement se ON e.statut_evenement_id = se.statut_evenement_id
                INNER JOIN ticket t ON e.ticket_id = t.ticket_id
                INNER JOIN produit p ON p.produit_id = t.produit_id
                WHERE e.ticket_id = :ticket_id
                ORDER BY e.date_evenement DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':ticket_id' => $ticket_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /************************************************
     * Fonction pour récupérer les données de tous les tickets ouverts pour un produit spécifique.
     *
     * @param int $produit_id ID du produit pour lequel on souhaite récupérer les tickets ouverts.
     * @return array Tableau associatif contenant les données des tickets ouverts.
     ***********************************************/
    public function getAllOpenTickets($produit_id)
    {
        $query = "SELECT t.ticket_id, t.titre, DATE_FORMAT(t.date_creation, '%d/%m/%Y %H:%i:%s') as date_creation, s.valeur, p.nom_produit, ty.nom_type, u.niveau, us.username FROM ticket t
        INNER JOIN statut s ON s.statut_id = t.statut_id
        INNER JOIN urgence u ON u.urgence_id = t.urgence_id
        INNER JOIN user us ON us.username = t.username
        INNER JOIN produit p ON p.produit_id = t.produit_id
        INNER JOIN type ty ON ty.type_id = t.type_id
        WHERE s.statut_id = 1 AND  p.produit_id = :produit_id 
        ORDER BY t.ticket_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':produit_id' => $produit_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /************************************************
     * Fonction pour récupérer les données de tous les tickets résolus pour un produit spécifique.
     *
     * @param int $produit_id ID du produit pour lequel on souhaite récupérer les tickets résolus.
     * @return array Tableau associatif contenant les données des tickets résolus.
     ***********************************************/
    public function getAllSolvedTickets($produit_id)
    {
        $query = "SELECT t.ticket_id, t.titre, DATE_FORMAT(t.date_creation, '%d/%m/%Y %H:%i:%s') as date_creation, s.valeur, p.nom_produit, ty.nom_type, u.niveau, us.username FROM ticket t
        INNER JOIN statut s ON s.statut_id = t.statut_id
        INNER JOIN urgence u ON u.urgence_id = t.urgence_id
        INNER JOIN user us ON us.username = t.username
        INNER JOIN produit p ON p.produit_id = t.produit_id
        INNER JOIN type ty ON ty.type_id = t.type_id
        WHERE s.statut_id = 2 AND  p.produit_id = :produit_id
        ORDER BY t.ticket_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':produit_id' => $produit_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /************************************************
     * Fonction pour récupérer les données des tickets fermés pour un produit spécifique.
     *
     * @param int $produit_id ID du produit pour lequel on souhaite récupérer les tickets fermés.
     * @return array Tableau associatif contenant les données des tickets fermés.
     ***********************************************/
    public function getAllClosedTickets($produit_id)
    {
        $query = "SELECT t.ticket_id, t.titre, DATE_FORMAT(t.date_creation, '%d/%m/%Y %H:%i:%s') as date_creation, s.valeur, p.nom_produit, ty.nom_type, u.niveau, us.username FROM ticket t
        INNER JOIN statut s ON s.statut_id = t.statut_id
        INNER JOIN urgence u ON u.urgence_id = t.urgence_id
        INNER JOIN user us ON us.username = t.username
        INNER JOIN produit p ON p.produit_id = t.produit_id
        INNER JOIN type ty ON ty.type_id = t.type_id
        WHERE s.statut_id = 3 AND  p.produit_id = :produit_id
        ORDER BY t.ticket_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':produit_id' => $produit_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /************************************************
     * Fonction pour récupérer les pièces jointes d'un ticket en fonction de son ID.
     *
     * @param int $ticket_id ID du ticket pour lequel on souhaite récupérer les pièces jointes.
     * @return array Tableau associatif contenant les pièces jointes du ticket.
     ***********************************************/
    public function getAttachmentsTicket($ticket_id)
    {
        $query = "SELECT * FROM attachments WHERE ticket_id=:ticket_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':ticket_id', $ticket_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /************************************************
     * Fonction pour récupérer les détails d'un ticket ouvert avec la date la plus récente.
     *
     * @param int $ticket_id ID du ticket pour lequel on souhaite récupérer les détails.
     * @return array|null Tableau associatif contenant les détails du ticket ou null si le ticket n'est pas trouvé.
     ***********************************************/
    public function getTicketDetails($ticket_id)
    {
        $query = "SELECT t.ticket_id, t.titre, t.description, DATE_FORMAT(t.date_creation, '%d/%m/%Y %H:%i:%s') as date_creation, s.valeur, p.nom_produit, ty.nom_type, u.niveau, us.username 
            FROM ticket t
            INNER JOIN statut s ON s.statut_id = t.statut_id
            INNER JOIN urgence u ON u.urgence_id = t.urgence_id
            INNER JOIN user us ON us.username = t.username
            INNER JOIN produit p ON p.produit_id = t.produit_id
            INNER JOIN type ty ON ty.type_id = t.type_id
            WHERE t.statut_id = 1 AND t.ticket_id = :ticket_id
            ORDER BY t.date_creation DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':ticket_id' => $ticket_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    /************************************************
     * Fonction pour récupérer les détails d'un ticket sauvegardé avec la dernière sauvegarde.
     *
     * @param int $ticket_id ID du ticket pour lequel on souhaite récupérer les détails de la sauvegarde.
     * @return array Tableau associatif contenant les détails du ticket sauvegardé.
     ***********************************************/
    public function getTicketSaveDetails($ticket_id)
    {
        $query = "SELECT t.ticket_id, t.titre, t.description, DATE_FORMAT(t.date_creation, '%d/%m/%Y %H:%i:%s') as date_creation, 
                s.valeur, p.nom_produit, ty.nom_type, u.niveau, us.username
            FROM ticket_save t
            INNER JOIN statut s ON s.statut_id = t.statut_id
            INNER JOIN urgence u ON u.urgence_id = t.urgence_id
            INNER JOIN user us ON us.username = t.username
            INNER JOIN produit p ON p.produit_id = t.produit_id
            INNER JOIN type ty ON ty.type_id = t.type_id
            WHERE t.ticket_save_id = (
                SELECT MAX(t2.ticket_save_id)
                FROM ticket_save t2
                WHERE t2.ticket_id = t.ticket_id
                AND t2.ticket_save_id < (
                    SELECT MAX(t3.ticket_save_id)
                    FROM ticket_save t3
                    WHERE t3.ticket_id = t.ticket_id
                )
            ) AND t.ticket_id = :ticket_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':ticket_id' => $ticket_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: []; // Retourne un tableau vide si aucun résultat
    }

    /************************************************
     * Fonction pour récupérer les détails d'un ticket fermé spécifique.
     *
     * @param int $ticket_id ID du ticket pour lequel on souhaite récupérer les détails du ticket fermé.
     * @return array|null Tableau associatif contenant les détails du ticket fermé ou null si le ticket n'est pas trouvé.
     ***********************************************/
    public function getClosedDetails($ticket_id)
    {
        $query = "SELECT t.ticket_id, t.titre, t.description, DATE_FORMAT(t.date_creation, '%d/%m/%Y %H:%i:%s') as date_creation, s.valeur, p.nom_produit, ty.nom_type, u.niveau, us.username FROM ticket t
        INNER JOIN statut s ON s.statut_id = t.statut_id
        INNER JOIN urgence u ON u.urgence_id = t.urgence_id
        INNER JOIN user us ON us.username = t.username
        INNER JOIN produit p ON p.produit_id = t.produit_id
        INNER JOIN type ty ON ty.type_id = t.type_id
        WHERE s.statut_id = 3 AND t.ticket_id = :ticket_id
        ORDER BY t.date_creation DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':ticket_id' => $ticket_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    /************************************************
     * Fonction pour récupérer les détails d'un ticket résolu spécifique.
     *
     * @param int $ticket_id ID du ticket pour lequel on souhaite récupérer les détails du ticket résolu.
     * @return array|null Tableau associatif contenant les détails du ticket résolu ou null si le ticket n'est pas trouvé.
     ***********************************************/
    public function getSolvedDetails($ticket_id)
    {
        $query = "SELECT t.ticket_id, t.titre, t.description, DATE_FORMAT(t.date_creation, '%d/%m/%Y %H:%i:%s') as date_creation, s.valeur, p.nom_produit, ty.nom_type, u.niveau, us.username FROM ticket t
        INNER JOIN statut s ON s.statut_id = t.statut_id
        INNER JOIN urgence u ON u.urgence_id = t.urgence_id
        INNER JOIN user us ON us.username = t.username
        INNER JOIN produit p ON p.produit_id = t.produit_id
        INNER JOIN type ty ON ty.type_id = t.type_id
        WHERE s.statut_id = 2 AND t.ticket_id = :ticket_id
        ORDER BY t.date_creation DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':ticket_id' => $ticket_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    
        /************************************************
     * Méthode qui récupère les informations de tous les utilisateurs.
     *
     * @return array Tableau associatif contenant les informations de tous les utilisateurs.
     ***********************************************/
    public function getUsers() 
    {
        $query = "SELECT * FROM user";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /************************************************
     * Méthode qui récupère les informations de tous les produits.
     *
     * @return array Tableau associatif contenant les informations de tous les produits.
     ***********************************************/
    public function getProduit() 
    {
        $query = "SELECT * FROM produit";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /************************************************
     * Fonction qui récupère tous les messages d'un ticket spécifique pour un utilisateur donné.
     *
     * @param int $ticket_id ID du ticket pour lequel on souhaite récupérer les messages.
     * @param string $username Nom d'utilisateur pour lequel les messages sont récupérés.
     * @return array Tableau associatif contenant les messages du ticket spécifié.
     ***********************************************/
    public function getAllTicketMessages($ticket_id, $username) {
        $query = $this->db->prepare('
            SELECT message_sent, message_sender, message_receiver, date_sent 
            FROM chat_messages 
            WHERE ticket_id = :ticket_id AND (message_sender = :username OR message_receiver = :username)
            ORDER BY date_sent ASC
        ');
        $query->bindValue(':ticket_id', $ticket_id, PDO::PARAM_INT);
        $query->bindValue(':username', $username, PDO::PARAM_STR);
        $query->execute();
    
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /************************************************
     * Fonction qui permet d'envoyer un message pour un ticket spécifique.
     *
     * @param int $ticket_id ID du ticket auquel le message est envoyé.
     * @param string $message Contenu du message à envoyer.
     * @param string $username Nom d'utilisateur qui envoie le message.
     * @throws Exception Si le ticket spécifié n'est pas trouvé.
     ***********************************************/
    public function sendMessage($ticket_id, $message, $username) {
        date_default_timezone_set('Europe/Paris');
        $date_sent = date('Y-m-d H:i:s');
    
        // Récupérer le créateur du ticket
        $query = $this->db->prepare('
            SELECT username 
            FROM ticket 
            WHERE ticket_id = :ticket_id
        ');
        $query->bindValue(':ticket_id', $ticket_id, PDO::PARAM_INT);
        $query->execute();
        $ticket = $query->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticket) {
            throw new Exception("Ticket not found");
        }
    
        $receiver = $ticket['username'];
    
        // Insérer le message dans la table chat_message
        $insertQuery = $this->db->prepare('
            INSERT INTO chat_messages (message_sender, message_receiver, message_sent, date_sent, ticket_id) 
            VALUES (:message_sender, :message_receiver, :message_sent, :date_sent, :ticket_id)
        ');
        $insertQuery->bindValue(':message_sender', $username, PDO::PARAM_STR);
        $insertQuery->bindValue(':message_receiver', $receiver, PDO::PARAM_STR);
        $insertQuery->bindValue(':message_sent', $message, PDO::PARAM_STR);
        $insertQuery->bindValue(':date_sent', $date_sent, PDO::PARAM_STR);
        $insertQuery->bindValue(':ticket_id', $ticket_id, PDO::PARAM_INT);
        $insertQuery->execute();
    }

    /************************************************
     * Méthode pour mettre à jour le titre ou la description d'un ticket spécifique.
     *
     * @param int $ticket_id ID du ticket à mettre à jour.
     * @param string $newTitle Nouveau titre du ticket.
     * @param string $newDescription Nouvelle description du ticket.
     * @return bool Retourne true si la mise à jour est réussie, sinon false.
     ***********************************************/
    public function updateTicketsDetails($ticket_id, $newTitle, $newDescription)
    {
        date_default_timezone_set('Europe/Paris');
        $date_creation = date('Y-m-d H:i:s');
        $username = $_SESSION['username'];
        // Récupérer l'état actuel du ticket
        $query = "SELECT * FROM ticket WHERE ticket_id = :ticket_id";
        $statement = $this->db->prepare($query);
        $statement->bindValue(':ticket_id', $ticket_id);
        $statement->execute();
        $ticket = $statement->fetch(PDO::FETCH_ASSOC);

        if ($ticket) {
            // Sauvegarder l'état actuel du ticket dans ticket_save
            

            // Mettre à jour le ticket avec les nouvelles valeurs
            $query = "UPDATE ticket SET titre = :newTitle, description = :newDescription WHERE ticket_id = :ticket_id";
            $statement = $this->db->prepare($query);
            $statement->bindValue(':ticket_id', $ticket_id);
            $statement->bindValue(':newTitle', $newTitle);
            $statement->bindValue(':newDescription', $newDescription);
        
            $this->createTicketSave(
                $ticket['ticket_id'],
                $newTitle,
                $newDescription,
                $date_creation,
                $ticket['statut_id'],
                $ticket['urgence_id'],
                $username,
                $ticket['produit_id'],
                $ticket['type_id']
            );
            if ($statement->execute()) {
                return true;
            } else {
                error_log("Failed to execute SQL query: " . $query);
                return false;
            }
        } else {
            error_log("Ticket with ID $ticket_id not found.");
            return false;
        }
    }

        /************************************************
     * Méthode pour récupérer les informations d'un utilisateur par son nom d'utilisateur.
     *
     * @param string $username Nom d'utilisateur de l'utilisateur à récupérer.
     * @return array|null Tableau associatif contenant les informations de l'utilisateur, ou null si non trouvé.
     ***********************************************/
    public function getUserByUsername($username) 
    {
        // Débogage
        error_log("Recherche de l'utilisateur: " . $username);
        
        $query = "SELECT u.username, u.mail, u.entreprise, u.pwd, u.role FROM user u 
                 WHERE username = :username";
        // Prépare la requête SQL pour éviter les injections SQL
        $stmt = $this->db->prepare($query);
        // Associe la valeur de $username au paramètre :username de manière sécurisée
        $stmt->bindValue(':username', $username); 
        // Exécute la requête SQL préparée avec les paramètres liés
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log d'erreur si aucun utilisateur n'est trouvé
        if (!$result) {
            error_log("Aucun utilisateur trouvé pour: " . $username . " dans la base de données");
        }
        
        // Retourne les infos de l'utilisateur ou null si non trouvé
        return $result; 
        }

    /************************************************
     * Méthode pour modifier le mot de passe d'un utilisateur en base de données.
     *
     * @param string $username Nom d'utilisateur de l'utilisateur dont le mot de passe est modifié.
     * @param string $newPassword Nouveau mot de passe à définir pour l'utilisateur.
     * @return bool Retourne true si la mise à jour est réussie, sinon false.
     ***********************************************/
    public function updateUserPassword($username, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
        $query = "UPDATE user SET pwd = :hashedPassword WHERE username = :username";
        $statement = $this->db->prepare($query);
        $statement->bindValue(':hashedPassword', $hashedPassword);
        $statement->bindValue(':username', $username);
    
        if ($statement->execute()) {
            return true;
        } else {
            error_log("Failed to execute SQL query: " . $query);
            return false;
        }
    }

    /************************************************
     * Fonction qui récupère les produits associés à un utilisateur spécifique.
     *
     * @param string $username Nom d'utilisateur de l'utilisateur pour lequel récupérer les produits.
     * @return array Tableau associatif contenant les produits associés à l'utilisateur.
     ***********************************************/
    public function getProduitsByUser($username)
    {
        $query = "SELECT p.* FROM produit p INNER JOIN user_produit up ON p.produit_id = up.produit_id WHERE up.username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':username' => $username]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /************************************************
     * Fonction qui récupère l'adresse email d'un utilisateur.
     *
     * @param string $username Nom d'utilisateur de l'utilisateur pour lequel récupérer l'adresse email.
     * @return string Adresse email de l'utilisateur, ou chaîne vide si non trouvé.
     ***********************************************/
    public function getUserMail($username)
    {
        $query = "SELECT mail FROM user WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':username' => $username]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData && isset($userData['mail'])) {
            return $userData['mail']; //Récupération du mail en chaîne de caract plutôt qu'en tableau
        } else {
            return ''; 
        }
    }
public function getEventDetails($event_id)
{
    $sql = "SELECT e.*, t.titre, t.description, u.username 
            FROM evenement e 
            LEFT JOIN ticket t ON e.ticket_id = t.ticket_id 
            LEFT JOIN user u ON e.username = u.username 
            WHERE e.evenement_id = :event_id";
    
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}