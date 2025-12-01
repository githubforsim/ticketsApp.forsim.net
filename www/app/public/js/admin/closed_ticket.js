document.addEventListener("DOMContentLoaded", function () {
  const selectElement = document.getElementById("produit_select");
  const selectedProduct = localStorage.getItem("selectedOption");

  if (selectedProduct) {
    selectElement.value = selectedProduct;
    // NE PAS appeler closedTickets au chargement car les tickets sont déjà chargés côté serveur
    // closedTickets(selectedProduct);
  } else {
    selectElement.value = "";
  }
});

document.getElementById("produit_select").addEventListener("change", function () {
  var selectedProduct = this.value;
  localStorage.setItem("selectedOption", selectedProduct);
  //console.log(selectedProduct);
  ajaxRequest("GET", `/ticketsApp/config/routes.php/closed/${selectedProduct}`, updateTickets);
  // Appeler openTickets avec le produit actuel
  closedTickets(selectedProduct);
});

//Appel de la requete
function closedTickets(selectedProduct) {
  //console.log("closedTickets called with value: " + selectedProduct);

  ajaxRequest("GET", `/ticketsApp/config/routes.php/all_closed/${selectedProduct}`, updateTickets);
}

function updateTickets(html) {
  // Analyser la réponse HTTP en utilisant DOMParser
  const parser = new DOMParser();
  const doc = parser.parseFromString(html, "text/html");

  // Sélectionner uniquement les lignes de tableau du document analysé
  const rows = doc.querySelectorAll(".ClosedTable tbody tr");

  // Convertir la NodeList en un tableau et mapper chaque élément à son outerHTML,
  // puis les joindre ensemble en une seule chaîne
  const rowHTML = Array.from(rows)
    .map((row) => row.outerHTML)
    .join("");

  // Trouver le corps du tableau
  const ticketTableBody = document.getElementById("ClosedTableBody");

  // Remplacer le contenu du corps du tableau par le HTML sélectionné
  ticketTableBody.innerHTML = rowHTML;
}
