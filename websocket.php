<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>messagerie</title>
  <?php include_once('startsession.php'); ?>
  <?php include_once('verif-email.php'); ?>
  <link rel="stylesheet" href="css/globale.css">
  <link rel="stylesheet" href="css/compte.css">
  <link rel="stylesheet" href="css/websocket.css">



  <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
  <script>
    async function ajouterMessage(message, user_id) {
      // Création d'un objet FormData
      var formData = new FormData();

      // Ajout des données au FormData
      formData.append('ajouterMessage', 1); // Clé 'ajouterMessage' avec la valeur 1
      formData.append('message', message); // Clé 'message' avec la valeur du paramètre 'message'
      formData.append('user_id', user_id); // Clé 'user_id' avec la valeur du paramètre 'user_id'

      // Envoi de la requête POST à "./messagerie.php" avec les données du FormData
      await fetch("./messagerie.php", {
          method: "POST",
          body: formData // Corps de la requête avec l'objet FormData
        })
        .then((response) => response.json()) // Convertit la réponse en JSON
        .then((result) => {
          // Les actions à effectuer avec le résultat de la requête
          // (cette partie du code est vide dans le code fourni)
        });

    }

    // Définition de la fonction pour afficher un message dans la balise #messages
    function showMessage(messageHTML) {
      $('#messages').append(messageHTML);
    }

    // Lorsque le document est prêt
    $(document).ready(function() {
      // Création d'une nouvelle instance de WebSocket et connexion à "ws://localhost:8090/php-socket.php"
      var websocket = new WebSocket("ws://192.168.191.121:8090/php-socket.php");

      // Lorsque la connexion WebSocket est établie
      websocket.onopen = function(event) {
        showMessage("<div class='message received'><?php echo $_SESSION['user']['pseudo']; ?> vous pouvez parler!</div>");
      };

      // Lorsque un message est reçu via WebSocket
      websocket.onmessage = function(event) {
        // Analyse des données JSON reçues
        var Data = JSON.parse(event.data);
        // Affichage du message dans la balise #messages avec la classe spécifiée dans Data.message_type
        showMessage("<div class='" + Data.message_type + "'>" + Data.message + "</div>");
        // Effacement du contenu de la balise #message
        $('#message').val('');
      };

      // En cas d'erreur WebSocket
      websocket.onerror = function(event) {
        showMessage("<div class='message received error'><?php echo $_SESSION['user']['pseudo']; ?> un problème en raison d'une erreur quelconque est survenu. </div>");
      };

      // Lorsque la connexion WebSocket est fermée
      websocket.onclose = function(event) {
        showMessage("<div class='message received'>Connexion fermée</div>");
      };

      // Lorsque le formulaire est soumis
      $('#formulaire').on("submit", function(event) {
        event.preventDefault();
        // Création d'un objet JSON contenant les informations de l'utilisateur et le message
        var messageJSON = {
          chat_user: '<?php echo $_SESSION['user']['pseudo']; ?>',
          chat_message: $('#message').val()
        };
        // Envoi des données JSON via WebSocket après les avoir converties en chaîne de caractères
        websocket.send(JSON.stringify(messageJSON));
      });
    });
  </script>
</head>

<body>
  <?php include_once('navbar.php'); ?>
  <div id="chat">
    <form id="formulaire">
      <div id="messages">
        <!-- Contenu des messages -->
      </div>
      <input type="hidden" id="user_id" value="<?= $_SESSION['user']['id_inscrit'] ?>">
      <input type="text" id="message" placeholder="Entrez votre message ici" autocomplete="off">
      <button type="submit" onclick="ajouterMessage(document.querySelector('#message').value, document.querySelector('#user_id').value);">Envoyer</button>
    </form>
  </div>

</body>

</html>