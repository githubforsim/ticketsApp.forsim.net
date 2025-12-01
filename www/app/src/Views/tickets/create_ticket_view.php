<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// REDIRECTION COHÉRENTE
if (!isset($_SESSION['username'])) {
    header('Location: /ticketsApp/?view=login');
    exit();
}

// Déterminer les produits disponibles
$availableProducts = [];
if (isset($userProduits) && !empty($userProduits)) {
    $availableProducts = $userProduits;
} elseif (isset($produits) && !empty($produits)) {
    $availableProducts = $produits;
}

// DEBUG
error_log("=== DEBUG VIEW ===");
error_log("userProduits isset: " . (isset($userProduits) ? 'YES' : 'NO'));
error_log("produits isset: " . (isset($produits) ? 'YES' : 'NO'));
error_log("types isset: " . (isset($types) ? 'YES' : 'NO'));
error_log("urgences isset: " . (isset($urgences) ? 'YES' : 'NO'));
error_log("availableProducts count: " . count($availableProducts));
error_log("==================");

$hasMultipleProducts = count($availableProducts) > 1;
$singleProduct = $hasMultipleProducts ? null : ($availableProducts[0] ?? null);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nouveau Ticket</title>
</head>
<body>
    <!--Ajout du menu de navigation-->
    <?php include(__DIR__ . "/../sidebar.php"); ?>

    <div class="content">
        <h1>Créez un Ticket !</h1>

        <?php if ($hasMultipleProducts): ?>
            <div id="consigne">
                <h2>Veuillez sélectionner le produit nécessitant un Ticket.</h2>
            </div>

            <!-- SÉLECTION DE PRODUIT - SEULEMENT SI PLUSIEURS PRODUITS -->
            <div id="produit-selection" style="margin: 20px 0;">
                <label for="produit_id">Sélectionnez un produit :</label>
                <select id="produit_id" name="produit_id" required>
                    <option value="" disabled selected>Choisissez un produit</option>
                    <?php foreach ($availableProducts as $produit): ?>
                        <option value="<?= $produit['produit_id'] ?>"><?= htmlspecialchars($produit['nom_produit']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php else: ?>
            <!-- AFFICHAGE DU PRODUIT UNIQUE -->
            <?php if ($singleProduct): ?>
               
            <?php else: ?>
                <div id="no-products" style="margin: 20px 0; padding: 15px; background: #f8d7da; border-radius: 5px; border: 1px solid #f5c6cb;">
                    <p style="color: #721c24; margin: 0;"><i class="fas fa-exclamation-triangle"></i> Aucun produit disponible pour créer un ticket.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Champs Type et Urgence - TOUJOURS VISIBLES -->
        <?php if (!empty($availableProducts)): ?>
            <div id="type_radio">
                <fieldset>
                    <legend>Type :</legend>
                    <div id="radio_options">
                        <?php if (isset($types) && !empty($types)): ?>
                            <?php foreach ($types as $type): ?>
                                <div>
                                    <input type="radio" 
                                           id="type<?= $type['type_id'] ?>" 
                                           name="type_id" 
                                           value="<?= $type['type_id'] ?>" 
                                           form="ticket_form"
                                           <?= (strtolower($type['nom_type']) === 'bug') ? 'checked' : '' ?>
                                           required>
                                    <label for="type<?= $type['type_id'] ?>"><?= htmlspecialchars($type['nom_type']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: red;">Aucun type disponible</p>
                        <?php endif; ?>
                    </div>
                </fieldset>
            </div>

            <div id="urgence_radio">
                <fieldset>
                    <legend>Urgence :</legend>
                    <div id="urgence_options">
                        <?php if (isset($urgences) && !empty($urgences)): ?>
                            <?php foreach ($urgences as $urgence): ?>
                                <div>
                                    <input type="radio" 
                                           id="urgence<?= $urgence['urgence_id'] ?>" 
                                           name="urgence_id" 
                                           value="<?= $urgence['urgence_id'] ?>" 
                                           form="ticket_form"
                                           <?= (strtolower($urgence['niveau']) === 'normale') ? 'checked' : '' ?>
                                           required>
                                    <label for="urgence<?= $urgence['urgence_id'] ?>"><?= htmlspecialchars($urgence['niveau']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: red;">Aucune urgence disponible</p>
                        <?php endif; ?>
                    </div>
                </fieldset>
            </div>
        
        <!-- Formulaire de création de ticket -->
            <form id="ticket_form" style="<?= $hasMultipleProducts ? 'display: none;' : 'display: block;' ?>" action="/ticketsApp/create" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="produit_id_hidden" name="produit_id" value="<?= $singleProduct ? $singleProduct['produit_id'] : '' ?>">
                <!-- Champ caché pour la redirection -->
                <input type="hidden" name="redirect_url" value="https://ticketsapp.forsim.net/ticketsApp/tickets/open">
                
                <div>
                    <label for="titre">Titre :</label>
                    <input type="text" id="titre" name="titre" required />
                </div>

              <div>
    <label for="description">Description (optionnelle) :</label>
    <textarea id="description" name="description"></textarea>
</div>

                <div class="file-container">
                    <input type="file" name="attachment[]" id="attachment" multiple />
                    <label for="attachment">
                        <i class="fas fa-paperclip"></i> Choisissez un fichier (optionnel)
                    </label>
                    <div class="file-info">
                        <small>Formats autorisés : JPG, JPEG, PNG, PDF, DOC, DOCX, ZIP, RAR</small>
                    </div>
                    <ul class="file-list"></ul>
                    <div id="file-error" class="error-message" style="display: none;"></div>
                </div>

                <div>
                    <input type="submit" value="Créer un ticket" />
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script type="text/javascript" src="/app/public/js/ajax.js" defer></script>
    <script type="text/javascript" src="/app/public/js/tickets/create_ticket.js" defer></script>

    <style>
    /* FORCER L'AFFICHAGE DÈS LE CHARGEMENT */
    #type_radio, #urgence_radio {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    #type_radio fieldset, #urgence_radio fieldset {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    #radio_options, #urgence_options {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 16px !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    #radio_options > div, #urgence_options > div {
        display: flex !important;
        align-items: center !important;
        margin: 5px 0;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    #produit-selection {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #dee2e6;
        margin-bottom: 20px;
    }
    
    #produit-selection select {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    
    #produit-unique {
        background: #e8f5e8;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #c3e6cb;
        margin-bottom: 20px;
    }
    
    #produit-unique h3 {
        margin: 0;
        color: #155724;
        font-size: 18px;
    }
    
    #produit-unique i {
        margin-right: 8px;
    }
    
    fieldset {
        border: 1px solid #ddd;
        padding: 10px;
        margin: 10px 0;
        border-radius: 4px;
    }
    
    legend {
        font-weight: bold;
        padding: 0 10px;
    }
    
    #radio_options > div, #urgence_options > div {
        display: flex !important;
        align-items: center !important;
        margin: 5px 0;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    #type_radio, #urgence_radio {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    .file-container {
        margin: 15px 0;
    }
    
    .file-container label {
        display: inline-block;
        padding: 8px 15px;
        background-color: #007bff;
        color: white;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .file-container label:hover {
        background-color: #0056b3;
    }
    
    .file-info {
        margin: 8px 0;
        color: #666;
    }
    
    .file-list {
        list-style: none;
        padding: 0;
        margin: 10px 0;
    }
    
    .file-item {
        display: flex;
        align-items: center;
        padding: 8px;
        margin: 5px 0;
        border-radius: 4px;
        gap: 10px;
    }
    
    .file-item.valid {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
    }
    
    .file-item.invalid {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
    }
    
    .file-status.valid {
        color: #155724;
        margin-left: auto;
    }
    
    .file-status.invalid {
        color: #721c24;
        margin-left: auto;
        font-weight: bold;
    }
    
    .error-message {
        color: #721c24;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        padding: 10px;
        border-radius: 4px;
        margin: 10px 0;
    }
    
    #attachment {
        display: none;
    }
    </style>
</body>
</html>