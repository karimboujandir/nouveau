<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

// Démarre la session
session_start();
//var_dump($_SESSION);

// paramètres de connexion a la bd
$dsn = 'mysql:host=localhost;dbname=coolchat';
$username = 'root';
$password = 'adrar';

// Options de configuration pour la connexion PDO
$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
);

try {
    // connexion à la bd
    $dbh = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // sinon message d'erreur
    die('Une erreur est survenue lors de la connexion à la base de données.');
}
function getUserByEmail($email) {
    global $dbh;
    
    $sql = "SELECT * FROM inscrit WHERE email = ?";
    $stmt = $dbh->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $user;
}
?>
