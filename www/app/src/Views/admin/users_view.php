<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Vérifie si l'utilisateur est connecté et a le rôle d'administrateur
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
  // Redirige vers la page de connexion si l'utilisateur n'est pas connecté ou n'est pas administrateur
  header('Location: /app/src/Views/login_view.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" href="/app/public/css/admin/users.css?v=2.4" />

    <title>Utilisateurs</title>
    <?php include 'admin_sidebar.php'; ?>

</head>

    <body>
        <div class="content">
        <h1>Utilisateurs</h1>
        <table class="usersTable">
        <thead>
            <tr>
              <th>Utilisateur</th>
              <th>mail</th>
              <th>rôle</th>
              <th>entreprise</th>
            </tr>
        </thead>
        <tbody id="usersTableBody">
            <?php foreach ($users as $user): ?>
            <tr>
            
<td><a class="user_id" href="/ticketsApp/user_details?username=<?= urlencode($user['username']) ?>"><?= htmlspecialchars($user['username']) ?></a></td>
                <td><?php echo htmlspecialchars($user['mail']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td><?php echo htmlspecialchars($user['entreprise']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
        </div>

    </body>
</html>