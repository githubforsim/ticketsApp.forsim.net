<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Récupérer le chemin demandé après /ticketsApp/
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/ticketsApp';
$path = str_replace($basePath, '', parse_url($requestUri, PHP_URL_PATH));
$path = trim($path, '/');

// Récupérer les paramètres GET
$params = [];
if (isset($_SERVER['QUERY_STRING'])) {
    parse_str($_SERVER['QUERY_STRING'], $params);
}

// routes simples
switch ($path) {
    case '':
        // Vérifier si un paramètre view est spécifié
        if (isset($params['view'])) {
            switch ($params['view']) {
                case 'login':
                    // Vue de connexion (avec gestion des erreurs)
                    require 'app/src/Views/login_view.php';
                    break;
                case 'password':
                    // Vue mot de passe oublié
                    require 'app/src/Views/password_view.php';
                    break;
                case 'admin':
                    // Vue admin, vérifier que l'utilisateur est bien admin
                    if (isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                        require 'app/src/Views/admin/admin_view.php';
                    } else {
                        // Redirection vers la page de connexion si non autorisé
                        require 'app/src/Views/login_view.php';
                    }
                    break;
                case 'user':
                    // Vue utilisateur, vérifier que l'utilisateur est connecté
                    if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
                        require 'app/src/Views/index.php';
                    } else {
                        // Redirection vers la page de connexion si non connecté
                        require 'app/src/Views/login_view.php';
                    }
                    break;
                default:
                    // Page par défaut pour les autres valeurs de view
                    if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
                        if ($_SESSION['role'] === 'admin') {
                            require 'app/src/Views/admin/admin_view.php';
                        } elseif ($_SESSION['role'] === 'user') {
                            require 'app/src/Views/index.php';
                        }
                    } else {
                        require 'app/src/Views/login_view.php';
                    }
                    break;
            }
        } else {
            // Comportement par défaut sans paramètre view
            if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
                if ($_SESSION['role'] === 'admin') {
                    require 'app/src/Views/admin/admin_view.php';
                } elseif ($_SESSION['role'] === 'user') {
                    require 'app/src/Views/index.php';
                }
            } else {
                require 'app/src/Views/login_view.php';
            }
        }
        break;
    default:
        $_SERVER['PATH_INFO'] = '/' . $path;
        require __DIR__ . '/config/routes.php'; 
        break;
}