<?php
// test_ticket.php - Place ce fichier dans ton dossier racine
// Accède via : http://localhost/ticketsApp/test_ticket.php

session_start();

// Simule une session admin pour le test
$_SESSION['username'] = 'Frederic'; // Change par un username qui existe dans ta BDD
$_SESSION['role'] = 'admin';

require_once __DIR__ . '/../../../../../config/database.php';

echo "<h1>🧪 TEST DE CRÉATION TICKET</h1>";
echo "<hr>";

try {
    $db = dbConnect();
    echo "✅ Connexion DB OK<br>";
    
    // Paramètres de test
    $titre = "Test Ticket - " . date('Y-m-d H:i:s');
    $description = "Description de test automatique";
    $username = $_SESSION['username'];
    $produit_id = 1; // CHANGE selon tes produits
    $type_id = 1;    // CHANGE selon tes types  
    $urgence_id = 1; // CHANGE selon tes urgences
    $statut_id = 1;
    $date_creation = date('Y-m-d H:i:s');
    
    echo "<h2>📝 ÉTAPE 1: Création du ticket</h2>";
    echo "Titre: $titre<br>";
    echo "Username: $username<br>";
    echo "Produit ID: $produit_id<br>";
    
    // 1. Créer le ticket
    $query = "INSERT INTO ticket (titre, description, date_creation, statut_id, urgence_id, username, produit_id, type_id) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    $result = $stmt->execute([$titre, $description, $date_creation, $statut_id, $urgence_id, $username, $produit_id, $type_id]);
    
    if ($result) {
        $ticketId = $db->lastInsertId();
        echo "✅ Ticket créé avec ID: <strong>$ticketId</strong><br>";
        
        // 2. Test ajout attachment
        echo "<h2>📎 ÉTAPE 2: Ajout attachment</h2>";
        $filename = "test_file_" . $ticketId . ".txt";
        
        $attachQuery = "INSERT INTO attachments (ticket_id, filename) VALUES (?, ?)";
        $attachStmt = $db->prepare($attachQuery);
        $attachResult = $attachStmt->execute([$ticketId, $filename]);
        
        if ($attachResult) {
            $attachmentId = $db->lastInsertId();
            echo "✅ Attachment ajouté avec ID: <strong>$attachmentId</strong><br>";
            echo "Filename: $filename<br>";
            
            // Vérification
            $checkQuery = "SELECT * FROM attachments WHERE attachment_id = ?";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->execute([$attachmentId]);
            $attachment = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($attachment) {
                echo "✅ Vérification attachment OK:<br>";
                echo "<pre>" . print_r($attachment, true) . "</pre>";
            }
            
        } else {
            echo "❌ Échec ajout attachment<br>";
            $errorInfo = $attachStmt->errorInfo();
            echo "Erreur: <pre>" . print_r($errorInfo, true) . "</pre>";
        }
        
        // 3. Test ajout événement
        echo "<h2>📅 ÉTAPE 3: Ajout événement</h2>";
        
        $eventQuery = "INSERT INTO evenement (ticket_id, username, date_evenement, statut_evenement_id, titre, description, produit_id) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
        $eventStmt = $db->prepare($eventQuery);
        $eventResult = $eventStmt->execute([$ticketId, $username, $date_creation, 1, $titre, $description, $produit_id]);
        
        if ($eventResult) {
            $eventId = $db->lastInsertId();
            echo "✅ Événement ajouté avec ID: <strong>$eventId</strong><br>";
            
            // Vérification
            $checkEventQuery = "SELECT * FROM evenement WHERE evenement_id = ?";
            $checkEventStmt = $db->prepare($checkEventQuery);
            $checkEventStmt->execute([$eventId]);
            $event = $checkEventStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($event) {
                echo "✅ Vérification événement OK:<br>";
                echo "<pre>" . print_r($event, true) . "</pre>";
            }
            
        } else {
            echo "❌ Échec ajout événement<br>";
            $errorInfo = $eventStmt->errorInfo();
            echo "Erreur: <pre>" . print_r($errorInfo, true) . "</pre>";
        }
        
        // 4. Résumé final
        echo "<h2>📊 RÉSUMÉ</h2>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Table</th><th>Status</th><th>ID créé</th></tr>";
        echo "<tr><td>ticket</td><td>✅ OK</td><td>$ticketId</td></tr>";
        echo "<tr><td>attachments</td><td>" . ($attachResult ? "✅ OK" : "❌ FAILED") . "</td><td>" . ($attachResult ? $attachmentId : "N/A") . "</td></tr>";
        echo "<tr><td>evenement</td><td>" . ($eventResult ? "✅ OK" : "❌ FAILED") . "</td><td>" . ($eventResult ? $eventId : "N/A") . "</td></tr>";
        echo "</table>";
        
    } else {
        echo "❌ Échec création ticket<br>";
        $errorInfo = $stmt->errorInfo();
        echo "Erreur: <pre>" . print_r($errorInfo, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>🔍 VÉRIFICATIONS RAPIDES</h2>";

// Vérifications supplémentaires
try {
    // Vérifier les tables
    $tables = ['ticket', 'attachments', 'evenement'];
    
    foreach ($tables as $table) {
        $checkQuery = "SELECT COUNT(*) as count FROM $table";
        $stmt = $db->prepare($checkQuery);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        echo "Table '$table': $count enregistrement(s)<br>";
    }
    
    // Vérifier la structure de attachments
    echo "<h3>Structure table attachments:</h3>";
    $descQuery = "DESCRIBE attachments";
    $stmt = $db->prepare($descQuery);
    $stmt->execute();
    $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($structure, true) . "</pre>";
    
} catch (Exception $e) {
    echo "Erreur vérification: " . $e->getMessage();
}

echo "<hr>";
echo "<p><strong>🔄 Recharge cette page pour faire un nouveau test</strong></p>";
echo "<p><a href='/ticketsApp/'>← Retour à l'app</a></p>";

?>