<?php

// Configuration de la base de données
$serveur = "127.0.0.1";
$utilisateur = "root";
$motDePasse = "";
$baseDeDonnees = "iot_monitoring_db";

try {
    // Connexion à la base de données
    $connexion = new PDO("mysql:host=$serveur;dbname=$baseDeDonnees", $utilisateur, $motDePasse);

    // Configuration du mode d'erreur PDO pour l'exception
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Définition du jeu de caractères en UTF-8
    $connexion->exec("SET NAMES utf8");

    echo "Connexion réussie !";
} catch(PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}

?>
