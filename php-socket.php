<?php
define('HOST_NAME', "192.168.191.121");
define('PORT', "8090");
$null = NULL;

require_once("class.chathandler.php");
$chatHandler = new ChatHandler();

// Création d'une ressource de socket
$socketResource = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
// Définition de l'option SO_REUSEADDR pour permettre la réutilisation de l'adresse
socket_set_option($socketResource, SOL_SOCKET, SO_REUSEADDR, 1);
// Association de la ressource de socket à l'adresse IP et au port spécifiés
socket_bind($socketResource, 0, PORT);
// Mise en écoute de la ressource de socket pour les connexions entrantes
socket_listen($socketResource);

// Tableau des sockets clients, initialisé avec la ressource de socket principale
$clientSocketArray = array($socketResource);

// Boucle principale pour écouter les connexions et les messages
while (true) {
	$newSocketArray = $clientSocketArray;
	// Utilisation de la fonction socket_select pour surveiller les sockets en lecture
	socket_select($newSocketArray, $null, $null, 0, 10);

	// Vérification si une nouvelle connexion est établie sur la ressource de socket principale
	if (in_array($socketResource, $newSocketArray)) {
		// Accepter la nouvelle connexion
		$newSocket = socket_accept($socketResource);
		// Ajouter le nouveau socket au tableau des sockets clients
		$clientSocketArray[] = $newSocket;

		// Lecture de l'en-tête de la requête WebSocket
		$header = socket_read($newSocket, 1024);
		// Effectuer le handshake pour établir la connexion WebSocket
		$chatHandler->doHandshake($header, $newSocket, HOST_NAME, PORT);

		// Récupérer l'adresse IP du client connecté
		socket_getpeername($newSocket, $client_ip_address);
		// Créer un message de confirmation de connexion pour le client
		$connectionACK = $chatHandler->newConnectionACK($client_ip_address);

		// Envoyer le message de confirmation de connexion au client
		$chatHandler->send($connectionACK);

		// Supprimer le socket principal du tableau des nouveaux sockets
		$newSocketIndex = array_search($socketResource, $newSocketArray);
		unset($newSocketArray[$newSocketIndex]);
	}

	// Parcourir les nouveaux sockets pour vérifier les messages reçus
	foreach ($newSocketArray as $newSocketArrayResource) {
		while (socket_recv($newSocketArrayResource, $socketData, 1024, 0) >= 1) {
			// Décodage du message reçu depuis le socket
			$socketMessage = $chatHandler->unseal($socketData);
			// Décodage du message JSON
			$messageObj = json_decode($socketMessage);
	
			// Création du message à afficher dans la boîte de chat
			$chat_box_message = $chatHandler->createChatBoxMessage($messageObj->chat_user, $messageObj->chat_message);
			// Envoi du message à tous les clients connectés
			$chatHandler->send($chat_box_message);

			// Sortir de la boucle interne (break 2) pour éviter de traiter d'autres messages dans ce tour de boucle
			break 2;
		}

		// Lecture des données du socket avec la fonction socket_read
		$socketData = @socket_read($newSocketArrayResource, 1024, PHP_NORMAL_READ);
		// Vérification si la lecture a échoué (socket fermé)
		if ($socketData === false) {
			// Récupérer l'adresse IP du client déconnecté
			socket_getpeername($newSocketArrayResource, $client_ip_address);
			// Créer un message de déconnexion pour le client
			$connectionACK = $chatHandler->connectionDisconnectACK($client_ip_address);
			// Envoyer le message de déconnexion à tous les clients connectés
			$chatHandler->send($connectionACK);

			// Recherche de l'index du socket déconnecté dans le tableau des sockets clients
			$newSocketIndex = array_search($newSocketArrayResource, $clientSocketArray);
			// Supprimer le socket déconnecté du tableau des sockets clients
			unset($clientSocketArray[$newSocketIndex]);
		}
	}
}
socket_close($socketResource);
