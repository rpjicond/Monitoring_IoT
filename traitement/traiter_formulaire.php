<?php
// Inclui o arquivo de conexão com o banco de dados
include '../DB/connexion_db.php';

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtém os dados do formulário
    $name = $_POST["name"];
    $description = $_POST["description"];
    if(!empty($connexion))
    // Prepara e executa a query SQL para inserir os dados na tabela modules
    $sql = "INSERT INTO modules (name, description) VALUES ('$name', '$description')";
    if ($connexion->query($sql) === TRUE) {
        // Redireciona de volta ao formulário com mensagem de sucesso
        header("Location: formulaire.php?success=1");
        exit();
    } else {
        // Redireciona de volta ao formulário com mensagem de erro
        header("Location: formulaire.php?error=1");
        exit();
    }
} else {
    // Se o formulário não foi submetido, redireciona para a página inicial
    header("Location: formulaire.php");
    exit();
}
?>
