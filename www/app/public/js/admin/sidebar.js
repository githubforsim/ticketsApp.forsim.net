function getProduitName(html) {
  // Analyser la réponse HTTP en utilisant DOMParser
  const parser = new DOMParser();
  const doc = parser.parseFromString(html, "text/html");

  // Sélectionner uniquement les options du select dans le document analysé
  const options = doc.querySelectorAll(".produit select option");

  // Convertir la NodeList en un tableau et mapper chaque élément à son outerHTML,
  // puis les joindre ensemble en une seule chaîne
  const optionsHTML = Array.from(options)
    .map((option) => option.outerHTML)
    .join("");

  const selectElement = document.getElementById("produit_select");

  // Remplacer le contenu du select par les options sélectionnées
  selectElement.innerHTML = optionsHTML;

  // Initialiser la valeur de l'élément select à partir du localStorage
  var selectedProduct = localStorage.getItem("selectedOption");
  if (selectedProduct) {
    selectElement.value = selectedProduct;
  } else {
    selectElement.value = "";
  }
}

ajaxRequest("GET", "/ticketsApp/config/routes.php/produit", getProduitName);

// Quand l'utilisateur change l'option sélectionnée, on sauvegarde la nouvelle valeur dans le localStorage
document.getElementById("produit_select").addEventListener("change", function () {
  var selectedOption = this.value;
  localStorage.setItem("selectedOption", selectedOption);
});
