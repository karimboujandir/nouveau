<?php

require_once("startsession.php");

if(isset($_POST['ajouterMessage']) && !empty($_POST['ajouterMessage'])) {
    // Insertion dans la table "messages"
    $sql = "INSERT INTO messages (message, id_inscrit) VALUES(:message, :id_inscrit);";
    $stmt = $dbh->prepare($sql);

    // Nettoyez le message
    $message = filter_var($_POST['message'], FILTER_UNSAFE_RAW);

    $stmt->bindParam(':message', $message);
    $stmt->bindParam(':id_inscrit', $_SESSION['user']['id_inscrit']);
    // On exécute la requête SQL
    $stmt->execute();
}

 