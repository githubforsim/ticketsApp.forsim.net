<?php
/************************************************
 * Classe UserModel
 * 
 * Modèle pour la gestion des utilisateurs en base de données.
 ***********************************************/
 class UserModel
 {
     private $db;
 
     /**
      * Constructeur de la classe UserModel.
      *
      * @param PDO $db Instance de PDO représentant la connexion à la base de données.
      */
     public function __construct(PDO $db)
     {
         $this->db = $db;
     }
 
     /**
      * Méthode pour récupérer les informations d'un utilisateur par son nom d'utilisateur.
      *
      * @param string $username Nom d'utilisateur de l'utilisateur à récupérer.
      * @return array|false Tableau associatif contenant les informations de l'utilisateur, ou false si non trouvé.
      */
      public function getUserByUsername($username) 
      {
          $query = "SELECT * FROM user WHERE username=:username";
          $stmt = $this->db->prepare($query);
          $stmt->bindValue(':username', $username);
          $stmt->execute();
          return $stmt->fetch(PDO::FETCH_ASSOC);
      }
 
     /**
      * Méthode qui récupère le rôle d'un utilisateur en base de données.
      *
      * @param string $username Nom d'utilisateur de l'utilisateur dont on veut récupérer le rôle.
      * @return string|null Rôle de l'utilisateur, ou null si non trouvé.
      */
      public function getUserRole($username) 
      {
          $query = "SELECT role FROM user WHERE username = :username";
          $stmt = $this->db->prepare($query);
          $stmt->bindValue(':username', $username);
          $stmt->execute();
          return $stmt->fetchColumn();
      }
 
     /**
      * Fonction qui vérifie si un nom d'utilisateur existe déjà en base de données.
      *
      * @param string $username Nom d'utilisateur à vérifier.
      * @return bool Vrai si le nom d'utilisateur existe déjà, faux sinon.
      */
      public function isUsernameExists($username)
      {
          $query = "SELECT COUNT(*) FROM user WHERE username = :username";
          $statement = $this->db->prepare($query);
          $statement->bindValue(':username', $username);
          $statement->execute();
          $count = $statement->fetchColumn();
          return $count > 0;
      }
 }
 