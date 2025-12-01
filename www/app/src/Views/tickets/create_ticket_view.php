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

$hasMultipleProducts = count($availableProducts) > 1;
$singleProduct = $hasMultipleProducts ? null : ($availableProducts[0] ?? null);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/app/public/css/tickets/create_ticket.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
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

        <!-- Formulaire de création de ticket -->
        <?php if (!empty($availableProducts)): ?>
            <form id="ticket_form" style="<?= $hasMultipleProducts ? 'display: none;' : 'display: block;' ?>" action="/ticketsApp/create" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="produit_id_hidden" name="produit_id" value="<?= $singleProduct ? $singleProduct['produit_id'] : '' ?>">
                <!-- Champ caché pour la redirection -->
                <input type="hidden" name="redirect_url" value="https://ticketsapp.forsim.net/ticketsApp/tickets/open">
                
                <div>
                    <label for="titre">Titre :</label>
                    <input type="text" id="titre" name="titre" required />
                </div>

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

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const selectElement = document.getElementById("produit_id");
        const ticketForm = document.getElementById("ticket_form");
        const consigne = document.getElementById("consigne");
        const hiddenInput = document.getElementById("produit_id_hidden");
        const fileInput = document.getElementById("attachment");
        const fileList = document.querySelector(".file-list");
        const fileError = document.getElementById("file-error");
        
        // Extensions autorisées
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'zip', 'rar'];
        
        // Gestion de la sélection de produit (seulement si plusieurs produits)
        if (selectElement) {
            selectElement.addEventListener("change", function () {
                const selectedProduct = this.value;
                console.log("Produit sélectionné:", selectedProduct);
                
                if (selectedProduct !== "") {
                    ticketForm.style.display = "block";
                    consigne.style.display = "none";
                    hiddenInput.value = selectedProduct;
                } else {
                    ticketForm.style.display = "none";
                    consigne.style.display = "block";
                }
            });
        }
        
        // Gestion de la sélection de fichiers
        if (fileInput) {
            fileInput.addEventListener("change", function(e) {
                const files = Array.from(e.target.files);
                fileList.innerHTML = "";
                fileError.style.display = "none";
                
                let hasInvalidFiles = false;
                const validFiles = [];
                
                files.forEach((file, index) => {
                    const fileName = file.name.toLowerCase();
                    const fileExtension = fileName.split('.').pop();
                    
                    const listItem = document.createElement("li");
                    
                    if (allowedExtensions.includes(fileExtension)) {
                        listItem.innerHTML = `
                            <i class="fas fa-file"></i> 
                            <span>${file.name}</span> 
                            <small>(${(file.size / 1024 / 1024).toFixed(2)} MB)</small>
                            <span class="file-status valid"><i class="fas fa-check"></i></span>
                        `;
                        listItem.className = "file-item valid";
                        validFiles.push(file);
                    } else {
                        listItem.innerHTML = `
                            <i class="fas fa-file"></i> 
                            <span>${file.name}</span> 
                            <span class="file-status invalid"><i class="fas fa-times"></i> Format non autorisé</span>
                        `;
                        listItem.className = "file-item invalid";
                        hasInvalidFiles = true;
                    }
                    
                    fileList.appendChild(listItem);
                });
                
                if (hasInvalidFiles) {
                    fileError.textContent = "Certains fichiers ont un format non autorisé. Seuls les fichiers JPG, JPEG, PNG, PDF, DOC, DOCX, ZIP et RAR sont acceptés.";
                    fileError.style.display = "block";
                    
                    // Créer un nouveau FileList avec seulement les fichiers valides
                    const dt = new DataTransfer();
                    validFiles.forEach(file => dt.items.add(file));
                    fileInput.files = dt.files;
                }
            });
        }
        
        // Gestion AJAX du formulaire pour éviter la page JSON
        const form = document.getElementById("ticket_form");
        if (form) {
            form.addEventListener("submit", function (event) {
                event.preventDefault(); // Empêcher la soumission normale
                
                console.log("Soumission AJAX du formulaire...");
                
                // Vérifier s'il y a des fichiers invalides
                const invalidFiles = document.querySelectorAll('.file-item.invalid');
                if (invalidFiles.length > 0) {
                    alert('Veuillez supprimer les fichiers avec des formats non autorisés avant de soumettre le formulaire.');
                    return;
                }
                
                // Créer un FormData avec toutes les données du formulaire
                const formData = new FormData(form);
                
                // Si aucun fichier n'est sélectionné, ne pas inclure le champ file
                const fileInput = document.getElementById("attachment");
                if (fileInput.files.length === 0) {
                    formData.delete("attachment[]");
                }
                
                // Afficher un message de chargement (optionnel)
                const submitBtn = form.querySelector('input[type="submit"]');
                const originalText = submitBtn.value;
                submitBtn.value = "Création en cours...";
                submitBtn.disabled = true;
                
                // Envoyer la requête AJAX
                fetch('/ticketsApp/create', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Réponse:', data);
                    
                    if (data.status === 'success') {
                        // Succès - rediriger vers la page des tickets ouverts
                        console.log("Ticket créé avec succès, redirection...");
                        window.location.href = "https://ticketsapp.forsim.net/ticketsApp/tickets/open";
                    } else {
                        // Erreur - afficher le message d'erreur
                        alert('Erreur: ' + (data.message || 'Une erreur est survenue'));
                        submitBtn.value = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur de connexion. Veuillez réessayer.');
                    submitBtn.value = originalText;
                    submitBtn.disabled = false;
                });
            });
        }
    });
    </script>

    <style>
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
        margin: 5px 0;
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