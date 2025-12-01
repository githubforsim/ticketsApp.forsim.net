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
    <link rel="stylesheet" href="/app/public/css/tickets/create_ticket.css?v=2.6" />
    <title>Nouveau ticket</title>
    <style>
    /* FORCER L'AFFICHAGE DES OPTIONS AVEC SPÉCIFICITÉ MAXIMALE */
    body #type_radio, 
    body #urgence_radio,
    body div#type_radio,
    body div#urgence_radio {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        height: auto !important;
        overflow: visible !important;
    }
    
    body #radio_options, 
    body #urgence_options,
    body div#radio_options,
    body div#urgence_options {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 16px !important;
        visibility: visible !important;
        opacity: 1 !important;
        height: auto !important;
        overflow: visible !important;
    }
    
    body #radio_options input,
    body #radio_options label,
    body #urgence_options input,
    body #urgence_options label {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    </style>
  </head>
  <body>
    <!--Ajout du menu de navigation-->
    <?php include __DIR__ . '/../admin_sidebar.php'; ?>

    <div class="content">
      <h1>Créez un Ticket !</h1>

      <div id="consigne">
        <h2>Veuillez sélectionner le produit nécessitant un Ticket.</h2>
      </div>

      <!-- Affichage des erreurs s'il y en a -->
      <?php if (isset($_GET['error'])): ?>
        <div class="error-message">
          <p><?= htmlspecialchars($_GET['error']) ?></p>
        </div>
      <?php endif; ?>

      <!-- Formulaire de création de ticket -->
      <form id="ticket_form" method="POST" action="/ticketsApp/create-admin" enctype="multipart/form-data">
        <div>
          <label for="titre">Titre :</label>
          <input type="text" id="titre" name="titre" required />
        </div>

        <div>
          <label for="produit_id">Produit :</label>
          <select id="produit_id" name="produit_id" required>
            <option value="">Sélectionnez un produit</option>
            <?php if (isset($produits)) : ?>
              <?php foreach ($produits as $produit): ?>
                <option value="<?= $produit['produit_id'] ?>"><?= htmlspecialchars($produit['nom_produit']) ?></option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <div id="type_radio">
          <label for="type">Type :</label>
          <div id="radio_options">
            <?php if (isset($types)) : ?>
              <?php foreach ($types as $type): ?>
                <input type="radio" id="type<?= $type['type_id'] ?>" name="type_id" value="<?= $type['type_id'] ?>" required>
                <label for="type<?= $type['type_id'] ?>"><?= htmlspecialchars($type['nom_type']) ?></label>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <div id="urgence_radio">
          <label for="urgence">Urgence :</label>
          <div id="urgence_options">
            <?php if (isset($urgences)) : ?>
              <?php foreach ($urgences as $urgence): ?>
                <input type="radio" id="urgence<?= $urgence['urgence_id'] ?>" name="urgence_id" value="<?= $urgence['urgence_id'] ?>" required>
                <label for="urgence<?= $urgence['urgence_id'] ?>"><?= htmlspecialchars($urgence['niveau']) ?></label>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <div>
          <label for="description">Description :</label>
          <textarea id="description" name="description" required></textarea>
        </div>

        <div class="file-container">
          <input type="file" name="attachment[]" id="attachment" multiple />
          <label for="attachment" class="file-label">
            <i class="fas fa-upload"></i> Choisissez un ou plusieurs fichiers
          </label>
          <ul class="file-list"></ul>
          <p class="file-info">Formats acceptés : JPG, PNG, PDF, DOC, DOCX, ZIP, RAR (max 10MB)</p>
        </div>

        <div>
          <input type="submit" value="Créer un ticket" class="submit-btn" />
        </div>
      </form>
    </div>

  </body>
  <script type="text/javascript" src="/app/public/js/ajax.js" defer></script>
  <script type="text/javascript" src="/app/public/js/admin/create_ticket.js" defer></script>
</html>