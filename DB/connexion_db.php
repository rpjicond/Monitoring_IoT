<?php

// Fichier de configuration de la base de données
$host = "127.0.0.1"; // Adresse du serveur de base de données
$utilisateur = "root"; // Nom d'utilisateur de la base de données
$mot_de_passe = ""; // Mot de passe de la base de données
$base_de_donnees = "iot_monitoring_db"; // Nom de la base de données

// Connexion à la base de données
$connexion = new mysqli($host, $utilisateur, $mot_de_passe, $base_de_donnees);

// Vérifier la connexion
if ($connexion->connect_error) {
    die("Échec de la connexion : " . $connexion->connect_error);
}
echo "Connexion réussie";


?>
