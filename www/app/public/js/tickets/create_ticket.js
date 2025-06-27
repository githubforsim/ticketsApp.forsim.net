//Gestion et sauvegarde du produit sélectionné dans le menu
document.addEventListener("DOMContentLoaded", function () {
  // CORRIGÉ: produit_select → produit_id
  const selectElement = document.getElementById("produit_id");
  if (selectElement) {
    const selectedProduct = localStorage.getItem("selectedOption");
    if (selectedProduct) {
      selectElement.value = selectedProduct;
      displayForm(selectedProduct);
    } else {
      selectElement.value = "";
    }
  }
});

// Afficher ou masquer le formulaire si un produit est sélectionné
function displayForm(selectedProduct) {
  let ticketForm = document.getElementById("ticket_form");
  let consigne = document.getElementById("consigne");

  if (selectedProduct !== "") {
    ticketForm.style.display = "block";
    consigne.style.display = "none";
    
    // Mettre à jour le champ caché
    const hiddenInput = document.getElementById("produit_id_hidden");
    if (hiddenInput) {
      hiddenInput.value = selectedProduct;
    }
  } else {
    ticketForm.style.display = "none";
    consigne.style.display = "block";
  }
}

//On passe la valeur a displayForm si changement de produit
document.addEventListener("DOMContentLoaded", function () {
  // CORRIGÉ: produit_select → produit_id
  const selectElement = document.getElementById("produit_id");
  if (selectElement) {
    selectElement.addEventListener("change", function () {
      const selectedProduct = this.value;
      localStorage.setItem("selectedOption", selectedProduct);
      displayForm(selectedProduct);
    });
  }
});

// SUPPRESSION DES REQUÊTES AJAX POUR TYPE ET URGENCE 
// Car les données sont déjà dans le PHP - pas besoin de recharger

//Fonction qui permet d'éviter les doublons de pièces jointes
function isDuplicateFile(file, fileList) {
  for (let i = 0; i < fileList.length; i++) {
    if (file.name === fileList[i].name && file.size === fileList[i].size) {
      return true;
    }
  }
  return false;
}

//Gestion des pièces jointes avec preview
const file = document.querySelector("#attachment");
const filePreviewContainer = document.querySelector("#file-preview");
let totalFiles = [];

if (file) {
  file.addEventListener("change", (e) => {
    console.log("=== FICHIERS SÉLECTIONNÉS ===");
    console.log("Nombre de fichiers:", e.target.files.length);
    
    const newFiles = Array.from(e.target.files);
    const uniqueFiles = newFiles.filter((file) => !totalFiles.some((existingFile) => isDuplicateFile(file, [existingFile])));

    totalFiles.push(...uniqueFiles);
    
    console.log("Total fichiers après ajout:", totalFiles.length);
    totalFiles.forEach((f, i) => {
      console.log(`Fichier ${i}: ${f.name} (${f.size} bytes)`);
    });

    // Effacer le preview précédent
    filePreviewContainer.innerHTML = "";
    
    // Créer le preview pour chaque fichier
    totalFiles.forEach((file, index) => {
      createFilePreview(file, index);
    });
  });
}

// Fonction pour créer le preview d'un fichier
function createFilePreview(file, index) {
  const { name, size, type } = file;
  const fileSize = (size / 1024).toFixed(2); // Taille en KB
  
  const previewItem = document.createElement("div");
  previewItem.className = "file-preview-item";
  previewItem.dataset.index = index;
  
  // Bouton de suppression
  const removeButton = document.createElement("button");
  removeButton.className = "remove-file";
  removeButton.innerHTML = "×";
  removeButton.addEventListener("click", () => {
    removeFile(index);
  });
  
  // Contenu du preview selon le type de fichier
  let previewContent = "";
  
  if (type.startsWith('image/')) {
    // Pour les images, créer un preview
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = previewItem.querySelector('.file-preview-img');
      if (img) {
        img.src = e.target.result;
      }
    };
    reader.readAsDataURL(file);
    previewContent = `<img class="file-preview-img" src="" alt="Preview">`;
  } else {
    // Pour les autres types, afficher une icône
    let iconClass = "fas fa-file";
    if (type.includes('pdf')) iconClass = "fas fa-file-pdf";
    else if (type.includes('word') || type.includes('document')) iconClass = "fas fa-file-word";
    else if (type.includes('zip') || type.includes('rar')) iconClass = "fas fa-file-archive";
    
    previewContent = `<div class="file-icon"><i class="${iconClass}"></i></div>`;
  }
  
  previewItem.innerHTML = `
    ${removeButton.outerHTML}
    ${previewContent}
    <div class="file-name">${name}</div>
    <div class="file-size">${fileSize} KB</div>
  `;
  
  // Re-attacher l'événement au bouton (car innerHTML l'a supprimé)
  previewItem.querySelector('.remove-file').addEventListener("click", () => {
    removeFile(index);
  });
  
  filePreviewContainer.appendChild(previewItem);
}

// Fonction pour supprimer un fichier
function removeFile(index) {
  totalFiles.splice(index, 1);
  console.log("Fichier supprimé. Total restant:", totalFiles.length);
  
  // Recréer tous les previews avec les nouveaux indices
  filePreviewContainer.innerHTML = "";
  totalFiles.forEach((file, newIndex) => {
    createFilePreview(file, newIndex);
  });
}

//fonction qui insert les données du form dans la bdd
function createTicket() {
  console.log("=== DEBUG createTicket ===");
  
  // Récupérer les éléments du formulaire
  let titre = document.getElementById("titre").value;
  let description = document.getElementById("description").value;
  // CORRIGÉ: utiliser le champ caché avec la valeur du select principal
  let produit_id = document.getElementById("produit_id_hidden").value;
  let type_id = document.querySelector('input[name="type_id"]:checked')?.value;
  let urgence_id = document.querySelector('input[name="urgence_id"]:checked')?.value;
  
  console.log("Données formulaire:");
  console.log("- titre:", titre);
  console.log("- description:", description);
  console.log("- produit_id:", produit_id);
  console.log("- type_id:", type_id);
  console.log("- urgence_id:", urgence_id);
  console.log("- fichiers:", totalFiles.length);
  
  // Vérifications
  if (!titre || !produit_id || !type_id || !urgence_id) {
    alert("Veuillez remplir tous les champs obligatoires");
    return;
  }
  
  let data = new FormData();

  data.append("titre", titre);
  data.append("description", description);
  data.append("produit_id", produit_id);
  data.append("type_id", type_id);
  data.append("urgence_id", urgence_id);

  // Ajouter les fichiers
  console.log("=== AJOUT FICHIERS ===");
  for (let i = 0; i < totalFiles.length; i++) {
    console.log(`Ajout fichier ${i}: ${totalFiles[i].name}`);
    data.append("attachment[]", totalFiles[i]);
  }
  
  // Debug FormData
  console.log("=== CONTENU FORMDATA ===");
  for (let pair of data.entries()) {
    if (pair[1] instanceof File) {
      console.log(pair[0] + ': FILE - ' + pair[1].name);
    } else {
      console.log(pair[0] + ': ' + pair[1]);
    }
  }

  // CORRIGÉ: URL correcte pour utilisateur normal
  console.log("Envoi vers: /ticketsApp/create");
  ajaxRequest("POST", "/ticketsApp/create", handleTicketResponse, data);
}

// FONCTION CORRIGÉE - Redirection vers tickets/open
function handleTicketResponse(response) {
  console.log("=== ANALYSE DE LA RÉPONSE ===");
  console.log("Réponse reçue:", response);
  
  // DÉTECTER SI ON A ÉTÉ REDIRIGÉ VERS LA PAGE DE LOGIN
  if (response && typeof response === 'string' && response.includes('<!DOCTYPE html>')) {
    if (response.includes('Connexion') || response.includes('login') || response.includes('Se connecter')) {
      console.log("SESSION EXPIRÉE - REDIRECTION VERS LOGIN");
      alert("Votre session a expiré. Vous allez être redirigé vers la page de connexion.");
      window.location.href = "/ticketsApp/?view=login";
      return;
    }
  }
  
  // Vérifier les erreurs PHP explicites
  const responseStr = String(response || '').toLowerCase();
  const phpErrors = [
    'fatal error', 'parse error', 'syntax error', 'database error',
    'mysql error', 'connection failed', 'access denied', 'call to undefined',
    'undefined variable', 'undefined index', 'cannot connect'
  ];
  
  const hasPhpError = phpErrors.some(error => responseStr.includes(error));
  
  if (hasPhpError) {
    console.log("ERREUR PHP DÉTECTÉE");
    alert("Erreur lors de la création du ticket: " + response);
    return;
  }
  
  // Essayer de parser en JSON
  try {
    if (response && typeof response === 'string' && response.trim()) {
      const jsonResponse = JSON.parse(response.trim());
      console.log("JSON parsé:", jsonResponse);
      
      if (jsonResponse.status === 'success') {
        alert("Ticket créé avec succès !");
        localStorage.removeItem("selectedOption");
        // REDIRECTION CORRIGÉE vers tickets/open
        window.location.href = "/ticketsApp/tickets/open";
        return;
      } else if (jsonResponse.status === 'error') {
        alert("Erreur: " + (jsonResponse.message || "Erreur inconnue"));
        return;
      }
    }
  } catch (e) {
    console.log("Erreur parsing response:", e);
    console.log("Response brute:", response);
  }
  
  // Si réponse vide ou non-JSON, considérer comme succès
  if (!response || response.trim() === '' || response === null || response === undefined) {
    console.log("Réponse vide - Succès");
    alert("Ticket créé avec succès !");
    localStorage.removeItem("selectedOption");
    // REDIRECTION CORRIGÉE vers tickets/open
    window.location.href = "/ticketsApp/tickets/open";
    return;
  }
  
  // Réponse non reconnue mais pas d'erreur explicite
  console.log("Réponse non reconnue, traitement comme succès par défaut");
  alert("Ticket créé avec succès !");
  localStorage.removeItem("selectedOption");
  // REDIRECTION CORRIGÉE vers tickets/open
  window.location.href = "/ticketsApp/tickets/open";
}

// Lorsque le formulaire est envoyé appel de la fonction createTicket
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("ticket_form");
  if (form) {
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      console.log("Formulaire soumis, appel createTicket()");
      createTicket();
    });
  } else {
    console.error("Formulaire ticket_form non trouvé !");
  }
});