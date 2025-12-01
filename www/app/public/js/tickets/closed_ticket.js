//Gestion et sauvegarde du produit sélectionné dans le menu
document.addEventListener("DOMContentLoaded", function () {
  const selectElement = document.getElementById("produit_select");
  // Récupère la valeur du produit sélectionné depuis le localStorage
  const selectedProduct = localStorage.getItem("selectedOption");
  // Si une valeur de produit est présente dans le localStorage
  if (selectedProduct) {
    // Définit la valeur du select pour afficher le produit sélectionné
    selectElement.value = selectedProduct;
    // NE PAS appeler closedTickets au chargement car les tickets sont déjà chargés côté serveur
    // closedTickets(selectedProduct);
  } else {
    // Si aucune valeur de produit n'est présente dans le localStorage, définir le select sur une valeur vide
    selectElement.value = "";
  }
});

// Ajoute un écouteur d'événement pour détecter les changements de sélection dans le select du produit
document.getElementById("produit_select").addEventListener("change", function () {
  var selectedProduct = this.value;
  // Enregistre la nouvelle valeur du produit dans le localStorage
  localStorage.setItem("selectedOption", selectedProduct);

  // Appel de la fonction closedTickets avec le nouveau produit sélectionné
  closedTickets(selectedProduct);
});

//Appel de la requete qui récupère les tickets fermés
function closedTickets(selectedProduct) {
  ajaxRequest("GET", `/ticketsApp/config/routes.php/closed/${selectedProduct}`, updateTickets);
}

//Mise à jour du tableau de tickets
function updateTickets(html) {
  // Analyser la réponse HTTP en utilisant DOMParser
  const parser = new DOMParser();
  const doc = parser.parseFromString(html, "text/html");

  // Sélectionne uniquement les lignes de tableau
  const rows = doc.querySelectorAll(".ClosedTable tbody tr");

  // Convertir la NodeList en un tableau et mapper chaque élément à son outerHTML,
  // puis les joindre ensemble en une seule chaîne
  const rowHTML = Array.from(rows)
    .map((row) => row.outerHTML)
    .join("");

  //corps du tableau
  const ticketTableBody = document.getElementById("ClosedTableBody");

  // Remplacer le contenu du corps du tableau par le HTML sélectionné
  ticketTableBody.innerHTML = rowHTML;
}
