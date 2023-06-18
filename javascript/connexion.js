const signUpButton = document.getElementById('signUp');  // Récupère le bouton s'inscrire par son ID

const signInButton = document.getElementById('signIn');  // Idem pour se connecter

const container = document.getElementById('container');  // Idem pour conteneur

signUpButton.addEventListener('click', () => {  // Réagit lorsque le bouton s'inscrire est cliqué
  // Ajoute la classe 'right-panel-active' au conteneur
  // Active le panneau droit et déplace le contenu vers la droite avec une animation
  container.classList.add('right-panel-active');
});

signInButton.addEventListener('click', () => {  // Idem avec se connecter
// Suit la meme logique que plus haut
  container.classList.remove('right-panel-active');
});