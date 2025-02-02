<?php
// Incluir o arquivo de conexão com o banco de dados
include 'db/connexion.php';

// Consulta SQL para recuperar os dados dos módulos
$sql = "SELECT Modules.name, Module_Data.timestamp, Module_Data.value
        FROM Module_Data
        JOIN Modules ON Module_Data.module_id = Modules.id";

// Executar a consulta SQL usando o PDO
try {
    $stmt = $connexion->query($sql);

    // Verificar se a consulta foi bem-sucedida
    if ($stmt) {
        // Inicializar um array para armazenar os dados dos módulos
        $data = array();

        // Loop através dos resultados da consulta
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Organizar os dados verticalmente
            foreach ($row as $key => $value) {
                // Verificar se a chave já existe no array
                if (!isset($data[$key])) {
                    $data[$key] = array(); // Se não existir, inicialize um array vazio
                }
                // Adicionar o valor ao array correspondente à chave
                $data[$key][] = $value;
            }
        }

        // Converter os dados para o formato JSON
        $jsonData = json_encode($data);

        // Escrever os dados JSON em um arquivo
        file_put_contents('grafic.json', $jsonData);

        // Exibir uma mensagem de sucesso
        echo "Dados foram escritos no arquivo grafic.json com sucesso.";
    } else {
        // Se houver um erro na consulta, exibir uma mensagem de erro
        echo "Erro ao executar a consulta SQL.";
    }
} catch (PDOException $e) {
    // Se houver uma exceção, exibir uma mensagem de erro
    echo "Erro ao executar a consulta SQL: " . $e->getMessage();
}

// Fechar a conexão com o banco de dados
$connexion = null;
?>
