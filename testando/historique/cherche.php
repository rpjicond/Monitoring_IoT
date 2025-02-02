<?php

// Permitir solicitações de qualquer origem
header("Access-Control-Allow-Origin: *");
// Permitir os métodos de solicitação especificados (GET, POST etc.)
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
// Permitir os cabeçalhos especificados
header("Access-Control-Allow-Headers: Content-Type");

// Se a solicitação for OPTIONS, encerre a execução do script aqui
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

$diretorio = 'modules_historique';
$arquivos = scandir($diretorio);
$numArquivos = count($arquivos) - 2; // Desconta "." e ".."

// Lista apenas os arquivos, sem incluir diretórios
$arquivos = array_filter($arquivos, function($arquivo) use ($diretorio) {
    return is_file($diretorio . '/' . $arquivo);
});

// Concatena os nomes dos arquivos em uma única string separada por vírgula
$nomesArquivos = implode(', ', $arquivos);

// Enviar resposta JSON com número de arquivos e nomes dos arquivos
header('Content-Type: application/json');
echo json_encode(['numArquivos' => $numArquivos, 'nomesArquivos' => $nomesArquivos]);

?>
