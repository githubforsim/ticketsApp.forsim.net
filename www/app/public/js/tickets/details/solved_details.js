//requête AJAX au serveur pour marquer un ticket comme "ouvert"
function setOpen(ticketId) {
  ajaxRequest("POST", `/ticketsApp/setOpen/${ticketId}`, function (response) {
    var data = JSON.parse(response);
    if (data.success) {
      // URL propre sans exposition des dossiers
      window.location.href = "/ticketsApp/admin/tickets/open";
    } else {
      console.error(data.error);
    }
  });
}

//requête AJAX au serveur pour marquer un ticket comme "fermé"
function setClose(ticketId) {
  ajaxRequest("POST", `/ticketsApp/setClose/${ticketId}`, function (response) {
    var data = JSON.parse(response);
    if (data.success) {
      // URL propre sans exposition des dossiers
      window.location.href = "/ticketsApp/admin/tickets/closed";
    } else {
      console.error(data.error);
    }
  });
}

//requête AJAX au serveur pour marquer un ticket comme "résolu"
function setSolve(ticketId) {
  ajaxRequest("POST", `/ticketsApp/setSolve/${ticketId}`, function (response) {
    var data = JSON.parse(response);
    if (data.success) {
      // URL propre sans exposition des dossiers
  window.location.href = `/ticketsApp/config/routes.php/setClose/${ticketId}`;
    } else {
      console.error(data.error);
    }
  });
}

// Gestion d'événements pour les boutons
document.addEventListener("DOMContentLoaded", function () {
  // Bouton "Ré-ouvrir"
  var openButton = document.getElementById("open_btn");
  if (openButton) {
    openButton.addEventListener("click", function () {
      var ticketId = this.dataset.ticketId;
      setOpen(ticketId);
    });
  }

  // Bouton "Clore"
  var closeButton = document.getElementById("closing_btn");
  if (closeButton) {
    closeButton.addEventListener("click", function () {
      var ticketId = this.dataset.ticketId;
      setClose(ticketId);
    });
  }

  // Bouton "Résoudre"
  var solveButton = document.getElementById("solve_btn");
  if (solveButton) {
    solveButton.addEventListener("click", function () {
      var ticketId = this.dataset.ticketId;
      setSolve(ticketId);
    });
  }
});