// JavaScript pour que le scroller soit directement en bas au chargement de la page
document.addEventListener("DOMContentLoaded", function() {
    var chatBox = document.getElementById("chat-box");
    chatBox.scrollTop = chatBox.scrollHeight;
  });