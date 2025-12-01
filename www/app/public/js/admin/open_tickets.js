//Sauvegarde du produit sélectionné dans la sidebar
document.addEventListener("DOMContentLoaded", function () {
  // Obtenez l'élément select par son ID
  const selectElement = document.getElementById("produit_select");

  // Récupérez l'option précédemment sélectionnée du localStorage
  const selectedProduct = localStorage.getItem("selectedOption");

  // Si une option a été sélectionnée précédemment
  if (selectedProduct) {
    // Réglez l'option sélectionnée dans le select
    selectElement.value = selectedProduct;

    // NE PAS appeler openTickets au chargement car les tickets sont déjà chargés côté serveur
    // openTickets(selectedProduct);
  } else {
    // Sinon, réglez le select sur une valeur vide
    selectElement.value = "";
  }
});

//Lors d'un changement d'option du select, stocke la nouvelle valeur dans le storage et affichage des tickets liés à l'option
document.getElementById("produit_select").addEventListener("change", function () {
  var selectedProduct = this.value;
  localStorage.setItem("selectedOption", selectedProduct);
  openTickets(selectedProduct);
});

// Fonction pour obtenir des tickets ouverts en fonction du produit sélectionné
function openTickets(selectedProduct) {
  ajaxRequest("GET", `/ticketsApp/config/routes.php/all_opened/${selectedProduct}`, updateTickets);
}

// Fonction pour mettre à jour les tickets dans le tableau
function updateTickets(html) {
  // Analysez la réponse HTML en utilisant DOMParser
  const parser = new DOMParser();
  const doc = parser.parseFromString(html, "text/html");

  // Sélectionnez uniquement les lignes du tableau du document analysé
  const rows = doc.querySelectorAll(".ticketTable tbody tr");

  // Convertissez la NodeList en tableau et mappez chaque élément à son outerHTML, puis joinnez-les ensemble en une seule chaîne
  const rowHTML = Array.from(rows)
    .map((row) => row.outerHTML)
    .join("");

  // Trouvez le corps du tableau
  const ticketTableBody = document.getElementById("ticketTableBody");

  // Remplacez le contenu du corps du tableau par le HTML sélectionné
  ticketTableBody.innerHTML = rowHTML;
}
