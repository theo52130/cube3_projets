// Obtenir la modale
var modal = document.getElementById("myModal");

// Obtenir le bouton qui ouvre la modale
var btn = document.getElementById("openFormBtn");

// Obtenir l'élément <span> qui permet de fermer la modale
var span = document.getElementsByClassName("close")[0];

// Quand l'utilisateur clique sur le bouton, on affiche la modale
btn.onclick = function() {
    modal.style.display = "block";
}

// Quand l'utilisateur clique sur <span> (x), on ferme la modale
span.onclick = function() {
    modal.style.display = "none";
}

// Quand l'utilisateur clique en dehors de la modale, on la ferme
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}