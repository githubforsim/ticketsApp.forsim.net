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

  // Ne remplacer que si on a des options valides
  if (optionsHTML.trim() !== "") {
    // Remplacer le contenu du select par les options sélectionnées
    selectElement.innerHTML = optionsHTML;
  }

  // Initialiser la valeur de l'élément select à partir du localStorage
  var selectedProduct = localStorage.getItem("selectedOption");
  if (selectedProduct) {
    selectElement.value = selectedProduct;
  }
}

// Ne charger les produits via AJAX que si le select est vide (pas déjà chargé côté serveur)
const selectElement = document.getElementById("produit_select");
if (selectElement && selectElement.options.length <= 1) {
  // Seulement l'option "Choisissez un produit" ou vide
  ajaxRequest("GET", "/ticketsApp/produits", getProduitName);
} else {
  // Les produits sont déjà chargés, juste restaurer la sélection du localStorage
  var selectedProduct = localStorage.getItem("selectedOption");
  if (selectedProduct && selectElement) {
    selectElement.value = selectedProduct;
  }
}

// Quand l'utilisateur change l'option sélectionnée, on sauvegarde la nouvelle valeur dans le localStorage
if (selectElement) {
  selectElement.addEventListener("change", function () {
    var selectedOption = this.value;
    localStorage.setItem("selectedOption", selectedOption);
  });
}
