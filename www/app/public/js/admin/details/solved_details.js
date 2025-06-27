//requête AJAX au serveur pour marquer un ticket comme "ouvert"
function setOpen(ticketId) {
  ajaxRequest("POST", `/ticketsApp/config/routes.php/setOpen/${ticketId}`, function (response) {
    var data = JSON.parse(response);
    if (data.success) {
      // URL CORRIGÉE - vers la route propre
      window.location.href = "/ticketsApp/admin/tickets/open";
    } else {
      console.error(data.error);
    }
  });
}

//requête AJAX au serveur pour marquer un ticket comme "fermé"
function setClose(ticketId) {
  ajaxRequest("POST", `/ticketsApp/config/routes.php/setClose/${ticketId}`, function (response) {
    var data = JSON.parse(response);
    if (data.success) {
      // Redirige vers l'URL propre
      window.location.href = "/ticketsApp/admin/tickets/closed";
    } else {
      console.error(data.error);
    }
  });
}

//Gestion d'évènement onclick sur les boutons close et open
document.addEventListener("DOMContentLoaded", function () {
  var openButton = document.getElementById("open_btn");
  var closeButton = document.getElementById("closing_btn");
  
  //Si clic sur bouton open appel de la fonction setOpen
  if (openButton) {
    openButton.addEventListener("click", function () {
      var ticketId = this.dataset.ticketId;
      setOpen(ticketId);
    });
  }

  //Si clic sur bouton close appel de la fonction setClose
  if (closeButton) {
    closeButton.addEventListener("click", function () {
      var ticketId = this.dataset.ticketId;
      setClose(ticketId);
    });
  }
});