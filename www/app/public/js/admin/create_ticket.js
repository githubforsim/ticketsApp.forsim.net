//Gestion et sauvegarde du produit sélectionné dans le menu
document.addEventListener("DOMContentLoaded", function () {
    const selectElement = document.getElementById("produit_select");
    const selectedProduct = localStorage.getItem("selectedOption");
    // Utilisez la sélection précédente si elle existe et affichage du formulaire sinon rien
    if (selectedProduct) {
      selectElement.value = selectedProduct;
      displayForm(selectedProduct);
    } else {
      selectElement.value = "";
    }
  });
  
  // Afficher ou masquer le formulaire si un produit est sélectionné
  function displayForm(selectedProduct) {
    let ticketForm = document.getElementById("ticket_form");
    let consigne = document.getElementById("consigne");
  
    if (selectedProduct !== "") {
      ticketForm.style.display = "block";
      consigne.style.display = "none";
    } else {
      ticketForm.style.display = "none";
      consigne.style.display = "block";
    }
  }
  
  //On passe la valeur a displayForm si changement de produit
  document.getElementById("produit_select").addEventListener("change", function () {
    const selectedProduct = this.value;
    localStorage.setItem("selectedOption", selectedProduct);
    displayForm(selectedProduct);
  });
  
  //Récupération et affichage du type de ticket dans des boutons radios
  ajaxRequest("GET", "/ticketsApp/config/routes.php/type", getTypeTicket);
  function getTypeTicket(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, "text/html");
  
    const rows = doc.querySelectorAll("#radio_options");
  
    const rowHTML = Array.from(rows)
      .map((row) => row.outerHTML)
      .join("");
  
    const radioContainer = document.getElementById("radio_options");
  
    radioContainer.innerHTML = rowHTML;
  }
  
  //Récupération et affichage des niveau d'urgence avec des boutons radio
  ajaxRequest("GET", "/ticketsApp/config/routes.php/urgence", getUrgenceLevel);
  function getUrgenceLevel(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, "text/html");
  
    const rows = doc.querySelectorAll("#urgence_options");
  
    const rowHTML = Array.from(rows)
      .map((row) => row.outerHTML)
      .join("");
  
    const radioContainer = document.getElementById("urgence_options");
  
    radioContainer.innerHTML = rowHTML;
  }
  
  //Fonction qui permet d'éviter les doublons de pièces jointes
  function isDuplicateFile(file, fileList) {
    for (let i = 0; i < fileList.length; i++) {
      if (file.name === fileList[i].name && file.size === fileList[i].size) {
        return true;
      }
    }
    return false;
  }
  
  //Gestion des pièces jointes
  
  const file = document.querySelector("#attachment");
  // Récupérer l'élément de la liste des fichiers
  const fileListElement = document.querySelector(".file-list");
  // Variable globale pour stocker tous les fichiers
  let totalFiles = [];
  
  // Ajouter un écouteur d'événement "change" à l'élément de fichier
  file.addEventListener("change", (e) => {
    // Récupérer les nouveaux fichiers sélectionnés
    const newFiles = Array.from(e.target.files);
    // Filtrer les fichiers uniques qui ne sont pas déjà présents dans la liste totale des fichiers
    const uniqueFiles = newFiles.filter((file) => !totalFiles.some((existingFile) => isDuplicateFile(file, [existingFile])));
  
    // Ajouter les fichiers uniques à la liste totale des fichiers
    totalFiles.push(...uniqueFiles);
  
    // Effacer le contenu existant de la liste des fichiers
    fileListElement.innerHTML = "";
  
    // Parcourir chaque fichier dans la liste totale des fichiers
    totalFiles.forEach((file) => {
      const { name, size } = file;
      // Conversion de la taille en kilo-octets et la formater avec deux décimales
      const fileSize = (size / 1000).toFixed(2);
  
      // Créer un nouvel élément de liste pour le fichier
      const listItemElement = document.createElement("li");
      // Ajouter le nom et la taille du fichier à l'élément de liste
      listItemElement.innerHTML = `${name} - ${fileSize}KB <button class="remove-button">&nbsp;</button>`;
  
      // Ajouter un écouteur d'événement "click" au bouton de suppression
      listItemElement.querySelector("button.remove-button").addEventListener("click", () => {
        // Filtrer le fichier actuel de la liste totale des fichiers
        totalFiles = totalFiles.filter((f) => f !== file);
        // Supprimer l'élément de liste correspondant du DOM
        listItemElement.remove();
      });
  
      // Ajouter l'élément de liste à la liste des fichiers
      fileListElement.appendChild(listItemElement);
    });
  });
  
  //fonction qui insert les données du form dans la bdd
  function createTicket() {
    //Récuperer les éléments du formulaire
    let titre = document.getElementById("titre").value;
    let description = document.getElementById("description").value;
    let produit_id = document.getElementById("produit_select").value;
    let type_id = document.querySelector('input[name="type_id"]:checked').value;
    let urgence_id = document.querySelector('input[name="urgence_id"]:checked').value;
    let data = new FormData();
  
    data.append("titre", titre);
    data.append("description", description);
    data.append("produit_id", produit_id);
    data.append("type_id", type_id);
    data.append("urgence_id", urgence_id);
  
    //Parcourir chaque fichier
    for (let i = 0; i < totalFiles.length; i++) {
      data.append("attachment[]", totalFiles[i]);
    }
  
    //On apelle la requête
    ajaxRequest("POST", "/ticketsApp/config/routes.php/create-admin", handleTicketResponse, data);
  }
  
  // fonction qui renvoi à la page d'accueil lorsqu'un ticket est créé
  function handleTicketResponse() {
    window.location.href = "/ticketsApp/app/src/Views/admin/tickets/open_tickets_view.php";
  }
  
  // Lorsque le formulaire est envoyé appel de la fonction createTicket
  document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("ticket_form").addEventListener("submit", function (event) {
      event.preventDefault();
      createTicket();
    });
  });
  