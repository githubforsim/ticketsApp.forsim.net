<?php

require_once('constants.php');

/************************************************
 * Fonction pour établir une connexion à la base de données
 ***********************************************/
function dbConnect()
{
    try {
        $db = new PDO(
            'mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASSWORD
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $exception) {
        // Afficher l’erreur pour debug
        die('Erreur de connexion PDO : ' . $exception->getMessage());
    }

    return $db;
}


/************************************************
 * Fonction pour exécuter une requête sur la base de données
 * @param PDO $db Connexion PDO à utiliser pour la requête
 * @param string $request Requête SQL à exécuter
 * @return array|bool Résultat de la requête ou false en cas d'erreur
 ***********************************************/
function dbRequest($db, $request)
{
    try {
        // Préparation et exécution de la requête SQL
        $statement = $db->prepare($request);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $exception) {
        // En cas d'erreur lors de l'exécution de la requête, un message est enregistré dans les logs
        error_log('Erreur de requête : ' . $exception->getMessage());
        return false; // Retourne false pour indiquer une erreur de requête
    }
    return $result; // Retourne le résultat de la requête sous forme de tableau associatif
}
