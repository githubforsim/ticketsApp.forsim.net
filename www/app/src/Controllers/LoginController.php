<?php
error_log("[DEBUG LOGINCONTROLLER] LoginController.php chargé");


require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/routes.php';


/************************************************
 * Contrôleur LoginController gérant les actions liées à l'authentification des utilisateurs.
 *
 * Ce contrôleur offre des méthodes pour gérer l'authentification des utilisateurs,
 * y compris la connexion, la déconnexion et la gestion des demandes de changement de mot de passe.
 * Il utilise le modèle UserModel pour interagir avec la base de données et exécuter les opérations
 * nécessaires à l'authentification.
 *
 * Les fonctionnalités spécifiques incluent :
 * - Connexion d'un utilisateur avec validation des identifiants et gestion de session.
 * - Déconnexion d'un utilisateur en détruisant la session active.
 * - Gestion des demandes de changement de mot de passe en vérifiant l'existence de l'utilisateur.
 *
 * Ce contrôleur exploite les sessions PHP pour maintenir l'état de connexion des utilisateurs,
 * redirigeant vers différentes vues en fonction du rôle de l'utilisateur (administrateur ou utilisateur standard).
 * Les redirections sont gérées via des en-têtes HTTP pour diriger l'utilisateur vers les pages appropriées
 * après une action d'authentification.
 ***********************************************/
class LoginController 
{
    protected $UserModel;

    /************************************************
     * Constructeur de la classe UserAuth
     ***********************************************/
    public function __construct() 
    {
        // Établir une connexion à la base de données
        $db = dbConnect();
        // Initialiser le modèle UserModel avec la connexion à la base de données
        $this->UserModel = new UserModel($db);
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
            //Nettoyage de la valeur du champ avec le filtre FILTER_SANITIZE_STRING et renvoi de la valeur
            $sanitizedValue = filter_var($postData[$field], FILTER_SANITIZE_STRING);
            return $sanitizedValue;
        }
                
        //Si le champ n'existe pas , renvoi une chaine de caractère vide
        return '';
    }

    /************************************************
     * Fonction de connexion utilisateur.
     * 
     * @param string $username Le nom d'utilisateur à valider et à nettoyer
     * @param string $password Le mot de passe à valider et à nettoyer
     ***********************************************/
    public function login($username, $password) 
    {
        $username = trim($username);
        $password = trim($password);
    
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    
        // DEBUG TEMPORAIRE
            error_log("[DEBUG LOGIN] username: $username, password: $password");

        $user = $this->UserModel->getUserByUsername($username);
            error_log("[DEBUG LOGIN] user: ".print_r($user, true));
    
        if ($user && isset($user['pwd']) && password_verify($password, $user['pwd'])) {
            $_SESSION['username'] = $username;
            $_SESSION['loggedIn'] = true;
    
            $role = $this->UserModel->getUserRole($username);
            $_SESSION['role'] = $role;
    
            $redirect = ($role === 'admin') 
                ? '/?view=admin'
                : '/?view=user';
    
            header("Location: $redirect");
            exit();
        } else {
            error_log("[DEBUG LOGIN] Auth failed for $username");
            header('Location: /ticketsApp/?view=login&erreur=1');
            exit();
        }
    }
    

    /************************************************
     * Fonction de déconnexion utilisateur.
     * 
     * Détruit la session en cours et redirige vers la page de connexion.
     ***********************************************/
    public function logout()
    {
        // Vérifier si une session existe avant de tenter de la démarrer
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Détruire toutes les données de session
        session_destroy();
        
        // S'assurer qu'aucune sortie n'a été envoyée avant la redirection
        if (headers_sent()) {
            echo '<script>window.location.href = "/ticketsApp/";</script>';
        } else {
            // Rediriger vers la page de connexion après la déconnexion
            header('Location: /ticketsApp/');
            exit();
        }
    }

    /************************************************
     * Fonction pour gérer la demande de changement de mot de passe utilisateur.
     * 
     * @param array $postData Le tableau contenant les données POST
     ***********************************************/
public function passwordChangeRequest($postData)
{
    // Récupérer et nettoyer le nom d'utilisateur à partir des données POST
    $username = $this->getSanitizedInput($postData, 'username');

    // Vérifier si l'utilisateur existe dans la base de données
    if (!$this->UserModel->isUsernameExists($username)) {
        // Rediriger vers une page d'erreur via la racine
        header('Location: /ticketsApp/?view=password&erreur=1');
        exit();
    }
    
    // Rediriger vers une page de confirmation via la racine
    header('Location: /ticketsApp/?view=password&succes=1');
    exit();
}
}