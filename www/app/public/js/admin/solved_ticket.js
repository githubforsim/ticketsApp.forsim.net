//Lorsque la page HTML est chargée
document.addEventListener("DOMContentLoaded", function () {
  // Obtenez l'élément select par son ID
  const selectElement = document.getElementById("produit_select");

  // Récupérez l'option précédemment sélectionnée du localStorage
  const selectedProduct = localStorage.getItem("selectedOption");

  // Si une option a été sélectionnée précédemment
  if (selectedProduct) {
    // Réglez l'option sélectionnée dans le select
    selectElement.value = selectedProduct;

    // NE PAS appeler solvedTickets au chargement car les tickets sont déjà chargés côté serveur
    // solvedTickets(selectedProduct);
  } else {
    // Sinon, réglez le select sur une valeur vide
    selectElement.value = "";
  }
});

//Lors d'un changement d'option du select, stocke la nouvelle valeur dans le storage et affichage des tickets liés à l'option
document.getElementById("produit_select").addEventListener("change", function () {
  var selectedProduct = this.value;
  localStorage.setItem("selectedOption", selectedProduct);
  solvedTickets(selectedProduct);
});

function solvedTickets(selectedProduct) {
  ajaxRequest("GET", `/ticketsApp/config/routes.php/all_solved/${selectedProduct}`, updateTickets);
}

function updateTickets(html) {
  // Analyser la réponse HTTP en utilisant DOMParser
  const parser = new DOMParser();
  const doc = parser.parseFromString(html, "text/html");

  // Sélectionner uniquement les lignes de tableau du document analysé
  const rows = doc.querySelectorAll(".SolvedTable tbody tr");

  // Convertir la NodeList en un tableau et mapper chaque élément à son outerHTML,
  // puis les joindre ensemble en une seule chaîne
  const rowHTML = Array.from(rows)
    .map((row) => row.outerHTML)
    .join("");

  // Trouver le corps du tableau
  const ticketTableBody = document.getElementById("SolvedTableBody");

  // Remplacer le contenu du corps du tableau par le HTML sélectionné
  ticketTableBody.innerHTML = rowHTML;
}
