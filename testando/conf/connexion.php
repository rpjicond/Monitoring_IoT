<?php
// Paramètres de la base de données
$serveur = "127.0.0.1"; // Adresse du serveur MySQL (généralement localhost)
$utilisateur = "root"; // Nom d'utilisateur MySQL
$motdepasse = ""; // Mot de passe MySQL
$base_de_donnees = "iot_monitoring_db"; // Nom de la base de données

// Établir une connexion avec la base de données
$connexion = new mysqli($serveur, $utilisateur, $motdepasse, $base_de_donnees);

// Vérifier la connexion
if ($connexion->connect_error) {
    die("Erreur lors de la connexion à la base de données : " . $connexion->connect_error);
} else {
    echo "Connexion réussie !";
}

// Effectuer des requêtes, insertions, mises à jour, suppressions et autres opérations de base de données ici...

// Fermer la connexion avec la base de données lorsque cela n'est plus nécessaire
$connexion->close();
?>
