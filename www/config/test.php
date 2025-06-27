<?php
// Si constants.php est dans le même dossier que test.php
require_once('constants.php');

// Ou si constants.php est dans un dossier parent
// require_once('../constants.php');

try {
    echo "Tentative de connexion à MySQL...<br>";
    $db = new PDO(
        'mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASSWORD
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie !";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>