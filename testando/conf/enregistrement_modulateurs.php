<?php
// Inclure le fichier de connexion à la base de données
include 'db/connexion.php';

$displayModal = false;
$message = "";

// Vérifie si le formulaire a été envoyé
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérifie si tous les champs du formulaire ont été remplis
    if (isset($_POST["moduleName"]) && isset($_POST["moduleDescription"]) && isset($_POST["moduleType"])) {
        // Récupère les données du formulaire
        $nomModule = $_POST["moduleName"];
        $descriptionModule = $_POST["moduleDescription"];
        $typeModule = $_POST["moduleType"];

        // Définit la date et l'heure actuelles
        $date = date('Y-m-d H:i:s');

        try {
            // Requête SQL pour insérer les données dans la table modules
            if (!empty($connexion)) {
                $requete = $connexion->prepare("INSERT INTO modules (name, description, type, created_at) VALUES (:name, :description, :type, :created_at)");
                $requete->bindParam(":name", $nomModule);
                $requete->bindParam(":description", $descriptionModule);
                $requete->bindParam(":type", $typeModule);
                $requete->bindParam(":created_at", $date);
                $requete->execute();

                // Obtenir l'ID du module nouvellement inséré
                $module_id = $connexion->lastInsertId();

                // Insertion du module_id dans la table Module_Data
                $requete_data = $connexion->prepare("INSERT INTO Module_Data (module_id) VALUES (:module_id)");
                $requete_data->bindParam(":module_id", $module_id);
                $requete_data->execute();

                $message = "Module enregistré avec succès !";
                $displayModal = true;

                header("Location: ../index.php?success=true");
                exit;
            }
        } catch (PDOException $e) {
            // Affiche le message d'erreur et le numéro de ligne où l'exception s'est produite
            echo "Erreur à la ligne " . $e->getLine() . ": " . $e->getMessage();
        }

    } else {
        echo "Veuillez remplir tous les champs du formulaire.";
    }
} else {
    echo "Ce script ne peut être accédé que par la méthode POST.";
}
?>
