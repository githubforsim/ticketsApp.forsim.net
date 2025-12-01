<?php
error_log("[DEBUG ENTRY] method: " . $_SERVER['REQUEST_METHOD'] . ", uri: " . $_SERVER['REQUEST_URI']);
error_log("[DEBUG INDEX] index.php chargé");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Récupérer le chemin demandé après /ticketsApp/
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/ticketsApp';
$path = str_replace($basePath, '', parse_url($requestUri, PHP_URL_PATH));
$path = trim($path, '/');
error_log("[DEBUG PATH] requestUri: $requestUri, basePath: $basePath, path: $path, method: " . $_SERVER['REQUEST_METHOD']);

// Récupérer les paramètres GET
$params = [];
if (isset($_SERVER['QUERY_STRING'])) {
    parse_str($_SERVER['QUERY_STRING'], $params);
}

// routes simples
error_log("[DEBUG INDEX] avant switch path: $path");
switch ($path) {
    case '':
        // Si POST /, router vers routes.php pour le login
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("[DEBUG INDEX] POST / (path vide), route vers routes.php");
            require __DIR__ . '/config/routes.php';
            break;
        }
        // Vérifier si un paramètre view est spécifié
        if (isset($params['view'])) {
            switch ($params['view']) {
                case 'login':
                    error_log("[DEBUG INDEX] require login_view.php");
                    require 'app/src/Views/login_view.php';
                    break;
                case 'password':
                    error_log("[DEBUG INDEX] require password_view.php");
                    require 'app/src/Views/password_view.php';
                    break;
                case 'admin':
                    if (isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                        error_log("[DEBUG INDEX] appel AdminController->showAdminHome()");
                        require __DIR__ . '/config/routes.php';
                        $produitId = isset($_GET['produit']) ? $_GET['produit'] : 1;
                        $adminController->showAdminHome($produitId);
                    } else {
                        error_log("[DEBUG INDEX] require login_view.php (admin non autorisé)");
                        require 'app/src/Views/login_view.php';
                    }
                    break;
                case 'user':
                    if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
                        error_log("[DEBUG INDEX] require index.php (user)");
                        require 'app/src/Views/index.php';
                    } else {
                        error_log("[DEBUG INDEX] require login_view.php (user non connecté)");
                        require 'app/src/Views/login_view.php';
                    }
                    break;
                default:
                    if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
                        if ($_SESSION['role'] === 'admin') {
                            error_log("[DEBUG INDEX] appel AdminController->showAdminHome() (default)");
                            require __DIR__ . '/config/routes.php';
                            $produitId = isset($_GET['produit']) ? $_GET['produit'] : 1;
                            $adminController->showAdminHome($produitId);
                        } elseif ($_SESSION['role'] === 'user') {
                            error_log("[DEBUG INDEX] require index.php (default)");
                            require 'app/src/Views/index.php';
                        }
                    } else {
                        error_log("[DEBUG INDEX] require login_view.php (default)");
                        require 'app/src/Views/login_view.php';
                    }
                    break;
            }
        } else {
            // Comportement par défaut sans paramètre view
            if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
                if ($_SESSION['role'] === 'admin') {
                    error_log("[DEBUG INDEX] appel AdminController->showAdminHome() (no param)");
                    require __DIR__ . '/config/routes.php';
                    $produitId = isset($_GET['produit']) ? $_GET['produit'] : 1;
                    $adminController->showAdminHome($produitId);
                } elseif ($_SESSION['role'] === 'user') {
                    error_log("[DEBUG INDEX] require index.php (no param)");
                    require 'app/src/Views/index.php';
                }
            } else {
                error_log("[DEBUG INDEX] require login_view.php (no param)");
                require 'app/src/Views/login_view.php';
            }
        }
        break;
        case 'login':
            // Pour POST /ticketsApp/login : router vers routes.php
            error_log("[DEBUG INDEX] case login, method: " . $_SERVER['REQUEST_METHOD']);
            require __DIR__ . '/config/routes.php';
            break;
    default:
        error_log("[DEBUG INDEX] default switch, path: $path, method: " . $_SERVER['REQUEST_METHOD']);
        error_log("[DEBUG INDEX] require routes.php");
        $_SERVER['PATH_INFO'] = '/' . $path;
        require __DIR__ . '/config/routes.php'; 
        break;
}