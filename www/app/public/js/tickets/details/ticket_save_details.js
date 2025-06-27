//Fonction qui s'assure que les pièces jointes ne soient pas des doublons
function isDuplicateFile(file, fileList) {
    for (let i = 0; i < fileList.length; i++) {
      if (file.name === fileList[i].name && file.size === fileList[i].size) {
        return true;
      }
    }
    return false;
  }
  
  //Gestion des pièces jointes
  // Récupérer l'élément de fichier
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
      // Récupérer le nom et la taille du fichier
      const { name, size } = file;
      // Convertir la taille en kilo-octets et la formater avec deux décimales
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
  