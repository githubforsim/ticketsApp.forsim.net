<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Vérifie si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    // Redirige vers la page de connexion si l'utilisateur n'est pas connecté ou n'est pas administrateur
    header('Location: /ticketsApp/login_view.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/app/public/css/admin/ticket_chat.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <title>Chat en cours</title>
</head>
<body>
    <!--Ajout du menu de navigation-->
    <?php include(__DIR__ . "/../../admin_sidebar.php"); ?>
    <div class="content">
        <?php
        if (is_array($ticket)) {
            extract($ticket);
        } else {
            exit;
        }
        ?>
        <a class="open_btn" href="/ticketsApp/admin_closed_details/<?= htmlspecialchars($ticket['ticket_id']) ?>">Ticket</a>
        <a class="solved_btn" href="/ticketsApp/ticket-message-closed-admin/<?= htmlspecialchars($ticket['ticket_id']) ?>">Chat</a>
        <div id="ticket_container">
            <div class="ticket">
                <div id="chat-box">
                <!-- Zone d'affichage des messages -->
                <?php if (isset($messages) && is_array($messages) && count($messages) > 0): ?>
                    <?php foreach ($messages as $message): ?>
                        <?php
                        // Détermine si l'utilisateur actuel est l'expéditeur du message
                        $isSender = isset($message['message_sender']) && $message['message_sender'] == $_SESSION['username'];
                        
                        // Détermine le nom d'utilisateur à afficher au-dessus du message
                        $username = $isSender ? $_SESSION['username'] : (isset($message['message_sender']) ? htmlspecialchars($message['message_sender']) : 'Unknown');
                        
                        // Contenu du message
                        $messageSent = isset($message['message_sent']) ? htmlspecialchars($message['message_sent']) : '';
                        $dateSent = isset($message['date_sent']) ? htmlspecialchars($message['date_sent']) : '';
                        ?>
                        <div class="message-wrapper <?= $isSender ? 'sender-wrapper' : 'receiver-wrapper' ?>">
                            <h6 class="username"><?= $username ?></h6>
                            <div class="message <?= $isSender ? 'sender' : 'receiver' ?>">
                                <p><?= $messageSent ?></p>
                            </div>
                            <span class="date"><?= $dateSent ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun message à afficher.</p>
                <?php endif; ?>
                </div>
                <form id="chat-form" method="POST" action="/ticketsApp/message-sent-closed-admin">
                    <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket['ticket_id']) ?>">
                    <input type="text" id="message" name="message" class="input-field" placeholder="Entrez votre message...">
                    <input type="submit" id="send-btn" value="Envoyer">
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/app/public/js/ajax.js" defer></script>
    <script type="text/javascript" src="/app/public/js/admin/chat_message.js" defer></script>
</body>
</html>