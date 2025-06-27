<?php


/************************************************
 * @brief La classe TicketModel gère les opérations CRUD sur les tickets dans la base de données.
 ***********************************************/
class TicketModel
{
    private $db; ///< Instance de PDO pour la connexion à la base de données.

    /************************************************
     * @brief Constructeur de la classe TicketModel.
     * @param PDO $db Instance de PDO pour la connexion à la base de données.
     ***********************************************/
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /************************************************
     * @brief Fonction pour enregistrer un événement lié à un ticket.
     * @param int $ticket_id Identifiant du ticket.
     * @param string $username Nom d'utilisateur associé à l'événement.
     * @param int $statut_evenement_id Identifiant du statut de l'événement.
     * @return void
     * @throws Exception Si le ticket spécifié n'est pas trouvé ou si l'insertion de l'événement échoue.
     ***********************************************/
    public function logEvent($ticket_id, $username, $statut_evenement_id)
    {
        date_default_timezone_set('Europe/Paris');
        // Récupérer le titre, la description et le produit_id du ticket
        $query = "SELECT titre, description, produit_id FROM ticket WHERE ticket_id = :ticket_id";
        $statement = $this->db->prepare($query);
        $statement->bindValue(':ticket_id', $ticket_id);
        $statement->execute();
        $ticket = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            throw new Exception('Ticket not found.');
        }

        // Préparer les données pour l'insertion dans la table evenement
        $titre = $ticket['titre'];
        $description = $ticket['description'];
        $produit_id = $ticket['produit_id'];
        $date_evenement = date('Y-m-d H:i:s'); // Utiliser la date et l'heure actuelles

        // Insérer les données dans la table evenement
        $query = "INSERT INTO evenement (ticket_id, username, date_evenement, statut_evenement_id, titre, description, produit_id) 
                VALUES (:ticket_id, :username, :date_evenement, :statut_evenement_id, :titre, :description, :produit_id)";
        $statement = $this->db->prepare($query);
        $statement->bindValue(':ticket_id', $ticket_id);
        $statement->bindValue(':username', $username);
        $statement->bindValue(':date_evenement', $date_evenement);
        $statement->bindValue(':statut_evenement_id', $statut_evenement_id);
        $statement->bindValue(':titre', $titre);
        $statement->bindValue(':description', $description);
        $statement->bindValue(':produit_id', $produit_id);

        if (!$statement->execute()) {
            error_log("Failed to log event: " . $query);
            throw new Exception('Failed to log event.');
        }
    }

    /************************************************
     * @brief Fonction qui permet de créer un nouveau ticket dans la base de données.
     * @param string $titre Titre du ticket.
     * @param string $description Description du ticket.
     * @param string $date_creation Date de création du ticket (optionnel, sera remplacée par la date actuelle si non fournie).
     * @param int $statut_id Identifiant du statut du ticket.
     * @param int $urgence_id Identifiant de l'urgence du ticket.
     * @param string $username Nom d'utilisateur associé au ticket.
     * @param int $produit_id Identifiant du produit associé au ticket.
     * @param int $type_id Identifiant du type de ticket.
     * @param array|null $filesData Données des fichiers joints (optionnel).
     * @return int|false Identifiant du ticket créé si réussi, sinon retourne false.
     ***********************************************/
       public function createTicket($titre, $description, $date_creation, $statut_id, $urgence_id, $username, $produit_id, $type_id, $filesData = null)
    {
        date_default_timezone_set('Europe/Paris');
        // Utiliser la date et l'heure actuelles
        $date_creation = date('Y-m-d H:i:s'); 
        
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
        
        if ($statement->execute()) {
            $ticketId = $this->db->lastInsertId();

            // SUPPRESSION : Plus d'appel automatique à createAttachments
            // Les fichiers seront traités séparément par le contrôleur SEULEMENT s'ils existent

            // Appel de la fonction createTicketSave pour sauvegarder le ticket dans ticket_save
            $this->createTicketSave($ticketId, $titre, $description, $date_creation, $statut_id, $urgence_id, $username, $produit_id, $type_id);
            
            // Renvoi de l'ID du ticket créé
            return $ticketId;
        } else {
            return false; // Si échec renvoi false
        }
    }

    /************************************************
     * @brief Fonction pour créer une sauvegarde d'un ticket dans la table ticket_save.
     * @param int $ticketId Identifiant du ticket.
     * @param string $titre Titre du ticket.
     * @param string $description Description du ticket.
     * @param string $date_creation Date de création du ticket.
     * @param int $statut_id Identifiant du statut du ticket.
     * @param int $urgence_id Identifiant de l'urgence du ticket.
     * @param string $username Nom d'utilisateur associé au ticket.
     * @param int $produit_id Identifiant du produit associé au ticket.
     * @param int $type_id Identifiant du type de ticket.
     * @return void
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
     * @brief Exemple de méthode pour ajouter une pièce jointe à un ticket dans la base de données.
     * @param int $ticketId Identifiant du ticket.
     * @param string $filename Nom du fichier de la pièce jointe.
     * @return bool Vrai si l'ajout de la pièce jointe est réussi, sinon lance une exception.
     * @throws Exception Si l'ajout de la pièce jointe échoue.
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
     * @brief Fonction pour récupérer l'historique des événements associés à un utilisateur pour un produit spécifique.
     * @param int $produit_id Identifiant du produit.
     * @param string $username Nom d'utilisateur.
     * @return array Tableau associatif contenant les événements récupérés.
     ***********************************************/
    public function getUserEventHistory($produit_id, $username)
    {
        $query = "SELECT e.evenement_id, e.ticket_id, DATE_FORMAT(e.date_evenement, '%d/%m/%Y %H:%i:%s') as date_evenement, e.username, se.event_type, e.titre, p.nom_produit, e.description, t.statut_id 
                FROM evenement e
                INNER JOIN statut_evenement se ON e.statut_evenement_id = se.statut_evenement_id
                INNER JOIN ticket t ON e.ticket_id = t.ticket_id
                INNER JOIN produit p ON p.produit_id = t.produit_id
                WHERE p.produit_id = :produit_id AND t.username = :username
                ORDER BY e.date_evenement DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':produit_id' => $produit_id, ':username' => $username]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /************************************************
     * @brief Fonction pour récupérer l'historique des événements associés à un ticket spécifique.
     * @param int $ticket_id Identifiant du ticket.
     * @param string $username Nom d'utilisateur.
     * @return array Tableau associatif contenant les événements récupérés.
     ***********************************************/
    public function getTicketEventHistory($ticket_id, $username)
    {
        $query = "SELECT e.evenement_id, e.ticket_id, DATE_FORMAT(e.date_evenement, '%d/%m/%Y %H:%i:%s') as date_evenement, e.username, se.event_type, e.titre, p.nom_produit, e.description, t.statut_id 
                FROM evenement e
                INNER JOIN statut_evenement se ON e.statut_evenement_id = se.statut_evenement_id
                INNER JOIN ticket t ON e.ticket_id = t.ticket_id
                INNER JOIN produit p ON p.produit_id = t.produit_id
                WHERE e.ticket_id = :ticket_id AND t.username = :username
                ORDER BY e.date_evenement DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':ticket_id' => $ticket_id,
            ':username' => $username
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /************************************************
     * @brief Fonction pour récupérer les tickets ouverts par un utilisateur pour un produit spécifique.
     * @param int $produit_id Identifiant du produit.
     * @param string $username Nom d'utilisateur.
     * @return array Tableau associatif contenant les tickets récupérés.
     ***********************************************/
    public function getOpenTicket($produit_id, $username)
    {
        $query = "SELECT t.ticket_id, t.titre, DATE_FORMAT(t.date_creation, '%d/%m/%Y %H:%i:%s') as date_creation, s.valeur, p.nom_produit, ty.nom_type, u.niveau, us.username FROM ticket t
        INNER JOIN statut s ON s.statut_id = t.statut_id
        INNER JOIN urgence u ON u.urgence_id = t.urgence_id
        INNER JOIN user us ON us.username = t.username
        INNER JOIN produit p ON p.produit_id = t.produit_id
        INNER JOIN type ty ON ty.type_id = t.type_id
        WHERE s.statut_id = 1 AND  p.produit_id = :produit_id AND us.username = :username
        ORDER BY t.ticket_id";                ;
        $stmt = $this->db->prepare($query);
        $stmt->execute([':produit_id' => $produit_id, ':username' => $username]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /************************************************
     * @brief Fonction pour récupérer toutes les données de la table urgence.
     * @return array Tableau associatif contenant les données des urgences.
     ***********************************************/
    public function getUrgence() 
    {
        $query = "SELECT * FROM urgence";
        $statement = $this->db->prepare($query);
        $statement->execute();
        $tickets = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $tickets;
    }

    /************************************************
     * @brief Fonction pour récupérer toutes les données de la table produit.
     * @return array Tableau associatif contenant les données des produits.
     ***********************************************/
    public function getProduit() 
    {
        $query = "SELECT * FROM produit";
        $statement = $this->db->prepare($query);
        $statement->execute();
        $tickets = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $tickets;
    }

     /************************************************
     * @brief Fonction pour récupérer toutes les données de la table type.
     * @return array Tableau associatif contenant les données des types de tickets.
     ***********************************************/
    public function getType() 
    {
        $query = "SELECT * FROM type";
        $statement = $this->db->prepare($query);
        $statement->execute();
        $tickets = $statement->fetchAll(PDO::FETCH_ASSOC);
        //var_dump($tickets);
        return $tickets;
    }

    /************************************************
     * @brief Fonction pour récupérer les données des tickets résolus par l'utilisateur en fonction du produit sélectionné.
     * @param int $produit_id Identifiant du produit.
     * @param string $username Nom d'utilisateur.
     * @return array Tableau associatif contenant les données des tickets résolus.
     ***********************************************/
    public function getSolvedTicket($produit_id, $username)
    {
        $query = "SELECT t.ticket_id, t.titre, DATE_FORMAT(t.date_creation, '%d/%m/%Y %H:%i:%s') as date_creation, s.valeur, p.nom_produit, ty.nom_type, u.niveau, us.username FROM ticket t
        INNER JOIN statut s ON s.statut_id = t.statut_id
        INNER JOIN urgence u ON u.urgence_id = t.urgence_id
        INNER JOIN user us ON us.username = t.username
        INNER JOIN produit p ON p.produit_id = t.produit_id
        INNER JOIN type ty ON ty.type_id = t.type_id
        WHERE s.statut_id = 2 AND  p.produit_id = :produit_id AND us.username = :username
        ORDER BY t.ticket_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':produit_id' => $produit_id, ':username' => $username]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /************************************************
     * @brief Fonction pour récupérer les données des tickets fermés par l'utilisateur en fonction du produit sélectionné.
     * @param int $produit_id Identifiant du produit.
     * @param string $username Nom d'utilisateur.
     * @return array Tableau associatif contenant les données des tickets fermés.
     ***********************************************/
    public function getClosedTicket($produit_id, $username)
    {
        $query = "SELECT t.ticket_id, t.titre, DATE_FORMAT(t.date_creation, '%d/%m/%Y %H:%i:%s') as date_creation, s.valeur, p.nom_produit, ty.nom_type, u.niveau, us.username FROM ticket t
        INNER JOIN statut s ON s.statut_id = t.statut_id
        INNER JOIN urgence u ON u.urgence_id = t.urgence_id
        INNER JOIN user us ON us.username = t.username
        INNER JOIN produit p ON p.produit_id = t.produit_id
        INNER JOIN type ty ON ty.type_id = t.type_id
        WHERE s.statut_id = 3 AND  p.produit_id = :produit_id AND us.username = :username
        ORDER BY t.ticket_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':produit_id' => $produit_id, ':username' => $username]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /************************************************
     * @brief Fonction pour récupérer les données d'un seul ticket ouvert.
     * @param int $ticket_id Identifiant du ticket.
     * @param string $username Nom d'utilisateur.
     * @return array|false Tableau associatif contenant les données du ticket ouvert ou false si non trouvé.
     ***********************************************/
    public function getTicketDetails($ticket_id, $username)
    {
        $query = "SELECT t.ticket_id, t.titre, t.description, DATE_FORMAT(t.date_creation, '%d/%m/%Y %H:%i:%s') as date_creation, s.valeur, p.nom_produit, ty.nom_type, u.niveau, us.username FROM ticket t
        INNER JOIN statut s ON s.statut_id = t.statut_id
        INNER JOIN urgence u ON u.urgence_id = t.urgence_id
        INNER JOIN user us ON us.username = t.username
        INNER JOIN produit p ON p.produit_id = t.produit_id
        INNER JOIN type ty ON ty.type_id = t.type_id
        WHERE s.statut_id = 1 AND t.ticket_id = :ticket_id AND us.username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':ticket_id' => $ticket_id, ':username' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
     }

    /************************************************
     * @brief Fonction pour récupérer les données d'un seul ticket sauvegardé.
     * @param int $ticket_id Identifiant du ticket.
     * @param string $username Nom d'utilisateur.
     * @return array Tableau associatif contenant les données du ticket sauvegardé.
     ***********************************************/
    public function getTicketSaveDetails($ticket_id, $username)
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
            ) AND t.ticket_id = :ticket_id;
              AND us.username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':ticket_id' => $ticket_id, ':username' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: []; // Retourne un tableau vide si aucun résultat
    }

    /************************************************
     * @brief Fonction pour récupérer les données d'un seul ticket fermé.
     * @param int $ticket_id Identifiant du ticket.
     * @param string $username Nom d'utilisateur.
     * @return array|false Tableau associatif contenant les données du ticket fermé ou false si non trouvé.
     ***********************************************/
    public function getClosedDetails($ticket_id, $username)
    {
        $query = "SELECT t.ticket_id, t.titre, t.description, DATE_FORMAT(t.date_creation, '%d/%m/%Y %H:%i:%s') as date_creation, s.valeur, p.nom_produit, ty.nom_type, u.niveau, us.username FROM ticket t
        INNER JOIN statut s ON s.statut_id = t.statut_id
        INNER JOIN urgence u ON u.urgence_id = t.urgence_id
        INNER JOIN user us ON us.username = t.username
        INNER JOIN produit p ON p.produit_id = t.produit_id
        INNER JOIN type ty ON ty.type_id = t.type_id
        WHERE s.statut_id = 3 AND t.ticket_id = :ticket_id AND us.username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':ticket_id' => $ticket_id, ':username' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
     }

    /************************************************
     * @brief Fonction pour récupérer les données d'un seul ticket résolu.
     * @param int $ticket_id Identifiant du ticket.
     * @param string $username Nom d'utilisateur.
     * @return array|false Tableau associatif contenant les données du ticket résolu ou false si non trouvé.
     ***********************************************/
    public function getSolvedDetails($ticket_id, $username)
    {
        $query = "SELECT t.ticket_id, t.titre, t.description, DATE_FORMAT(t.date_creation, '%d/%m/%Y %H:%i:%s') as date_creation, s.valeur, p.nom_produit, ty.nom_type, u.niveau, us.username FROM ticket t
        INNER JOIN statut s ON s.statut_id = t.statut_id
        INNER JOIN urgence u ON u.urgence_id = t.urgence_id
        INNER JOIN user us ON us.username = t.username
        INNER JOIN produit p ON p.produit_id = t.produit_id
        INNER JOIN type ty ON ty.type_id = t.type_id
        WHERE s.statut_id = 2 AND t.ticket_id = :ticket_id AND us.username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':ticket_id' => $ticket_id, ':username' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    /************************************************
     * @brief Fonction pour changer le statut d'un ticket en "ouvert".
     * @param int $ticket_id Identifiant du ticket.
     * @return int|false Nombre de lignes affectées par la mise à jour ou false si le ticket n'est pas trouvé.
     ***********************************************/
    public function setOpen($ticket_id)
    {
        date_default_timezone_set('Europe/Paris');
        $username = $_SESSION['username'];
        $statut_id = 1;
        // Récupérer l'état actuel du ticket
        $query = "SELECT * FROM ticket WHERE ticket_id = :ticket_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':ticket_id' => $ticket_id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket) {
            // Sauvegarder l'état actuel du ticket dans ticket_save
            $this->createTicketSave(
                $ticket['ticket_id'],
                $ticket['titre'],
                $ticket['description'],
                $ticket['date_creation'],
                $statut_id,
                $ticket['urgence_id'],
                $username,
                $ticket['produit_id'],
                $ticket['type_id']
            );

            // Mettre à jour le statut du ticket
            $query = "UPDATE ticket SET statut_id = 1, date_creation = :current_time WHERE ticket_id = :ticket_id";
            $stmt = $this->db->prepare($query);
            $current_time = date('Y-m-d H:i:s'); // Récupère la date et l'heure actuelles
            $stmt->execute([
                ':current_time' => $current_time,
                ':ticket_id' => $ticket_id
            ]);

            return $stmt->rowCount();
        } else {
            error_log("Ticket with ID $ticket_id not found.");
            return false;
        }
    }

    /************************************************
     * @brief Fonction pour changer le statut d'un ticket en "fermé".
     * @param int $ticket_id Identifiant du ticket.
     * @return int|false Nombre de lignes affectées par la mise à jour ou false si le ticket n'est pas trouvé.
     ***********************************************/
    public function setClose($ticket_id)
    {
        date_default_timezone_set('Europe/Paris');
        $username = $_SESSION['username'];
        $statut_id = 3;
        // Récupérer l'état actuel du ticket
        $query = "SELECT * FROM ticket WHERE ticket_id = :ticket_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':ticket_id' => $ticket_id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket) {
            // Sauvegarder l'état actuel du ticket dans ticket_save
            $this->createTicketSave(
                $ticket['ticket_id'],
                $ticket['titre'],
                $ticket['description'],
                $ticket['date_creation'],
                $statut_id,
                $ticket['urgence_id'],
                $username,
                $ticket['produit_id'],
                $ticket['type_id']
            );

            // Mettre à jour le statut du ticket
            $query = "UPDATE ticket SET statut_id = 3, date_creation = :current_time WHERE ticket_id = :ticket_id";
            $stmt = $this->db->prepare($query);
            $current_time = date('Y-m-d H:i:s'); // Récupère la date et l'heure actuelles
            $stmt->execute([
                ':current_time' => $current_time,
                ':ticket_id' => $ticket_id
            ]);

            return $stmt->rowCount();
        } else {
            error_log("Ticket with ID $ticket_id not found.");
            return false;
        }
    }

    /************************************************
     * @brief Fonction pour changer le statut d'un ticket en "résolu".
     * @param int $ticket_id Identifiant du ticket.
     * @return int|false Nombre de lignes affectées par la mise à jour ou false si le ticket n'est pas trouvé.
     ***********************************************/
    public function setSolve($ticket_id)
    {
        date_default_timezone_set('Europe/Paris');
        $username = $_SESSION['username'];
        $statut_id = 2;
        // Récupérer l'état actuel du ticket
        $query = "SELECT * FROM ticket WHERE ticket_id = :ticket_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':ticket_id' => $ticket_id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket) {
            // Sauvegarder l'état actuel du ticket dans ticket_save
            $this->createTicketSave(
                $ticket['ticket_id'],
                $ticket['titre'],
                $ticket['description'],
                $ticket['date_creation'],
                $statut_id,
                $ticket['urgence_id'],
                $username,
                $ticket['produit_id'],
                $ticket['type_id']
            );

            // Mettre à jour le statut du ticket
            $query = "UPDATE ticket SET statut_id = 2, date_creation = :current_time WHERE ticket_id = :ticket_id";
            $stmt = $this->db->prepare($query);
            $current_time = date('Y-m-d H:i:s');
            $stmt->execute([
                ':current_time' => $current_time,
                ':ticket_id' => $ticket_id
            ]);

            return $stmt->rowCount();
        } else {
            error_log("Ticket with ID $ticket_id not found.");
            return false;
        }
    }


    /************************************************
     * @brief Fonction pour récupérer les pièces jointes d'un ticket.
     * @param int $ticket_id Identifiant du ticket.
     * @return array Tableau associatif contenant les données des pièces jointes.
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
     * @brief Fonction pour récupérer une pièce jointe spécifique par son ID.
     * @param int $attachmentId Identifiant de la pièce jointe.
     * @return array Tableau associatif contenant les données de la pièce jointe.
     ***********************************************/
    public function getAttachment($attachmentId)
    {
        $query = "SELECT * FROM attachments WHERE attachment_id=:attachment_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':attachment_id', $attachmentId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /************************************************
     * @brief Fonction pour supprimer une pièce jointe par son ID.
     * @param int $attachmentId Identifiant de la pièce jointe.
     * @return bool true si la suppression est réussie, sinon lance une exception.
     ***********************************************/
    public function deleteAttachment($attachmentId) 
    {
        $query = "DELETE FROM attachments WHERE attachment_id=:attachment_id";
        $stmt = $this->db->prepare($query);
        $result = $stmt->bindValue(':attachment_id', $attachmentId);
        $stmt->execute();        
        if (!$result) {
            throw new Exception('There was an error deleting the attachment.');
        }
        
        return true;
    }

    /************************************************
     * @brief Méthode pour modifier le titre ou la description d'un ticket.
     * @param int $ticket_id Identifiant du ticket.
     * @param string $newTitle Nouveau titre du ticket.
     * @param string $newDescription Nouvelle description du ticket.
     * @return bool true si la mise à jour est réussie, sinon false.
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
            $query = "UPDATE ticket SET titre = :newTitle, description = :newDescription, date_creation = :date_creation WHERE ticket_id = :ticket_id";
            $statement = $this->db->prepare($query);
            $statement->bindValue(':ticket_id', $ticket_id);
            $statement->bindValue(':newTitle', $newTitle);
            $statement->bindValue(':newDescription', $newDescription);
            $statement->bindValue(':date_creation', $date_creation);
        
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
     * @brief Fonction pour récupérer tous les messages d'un ticket spécifique.
     * @param int $ticket_id Identifiant du ticket.
     * @param string $username Nom d'utilisateur pour lequel récupérer les messages.
     * @return array Tableau associatif contenant les messages triés par date d'envoi.
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
     * @brief Fonction pour envoyer un message pour un ticket spécifique.
     * @param int $ticket_id Identifiant du ticket.
     * @param string $message Contenu du message à envoyer.
     * @param string $username Nom d'utilisateur de l'expéditeur.
     * @throws Exception Si le ticket n'est pas trouvé.
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
    
        $receiver = $NOM_ADMIN;
    
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
     * @brief Fonction pour récupérer les produits associés à un utilisateur spécifique.
     * @param string $username Nom d'utilisateur pour lequel récupérer les produits.
     * @return array Tableau associatif contenant les données des produits.
     ***********************************************/
    public function getProduitsByUser($username)
    {
        $query = "SELECT p.* FROM produit p INNER JOIN user_produit up ON p.produit_id = up.produit_id WHERE up.username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':username' => $username]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFirstProduitByUser($username)
    {
        $query = "SELECT p.* 
                FROM produit p 
                INNER JOIN user_produit up ON p.produit_id = up.produit_id 
                WHERE up.username = :username 
                ORDER BY p.produit_id ASC 
                LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':username' => $username]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /************************************************
     * @brief Fonction pour récupérer l'adresse e-mail d'un utilisateur.
     * @param string $username Nom d'utilisateur pour lequel récupérer l'adresse e-mail.
     * @return string Adresse e-mail de l'utilisateur, ou chaîne vide si non trouvée.
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
}