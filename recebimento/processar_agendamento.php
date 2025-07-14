<?php
/**
 * processar_agendamento.php
 *
 * Script responsável por processar e registrar um novo agendamento de recebimento.
 *
 * FUNCIONALIDADE:
 * - Recebe dados do formulário via POST (data, tipo de caminhão, carga, volumes, motorista, etc).
 * - Verifica se o limite de agendamentos para o tipo de caminhão na data escolhida já foi atingido.
 * - Se houver disponibilidade, insere o novo agendamento na tabela 'agendamentos'.
 * - Retorna mensagem de sucesso ou erro conforme o resultado.
 *
 * DETALHES DO FUNCIONAMENTO:
 * - Utiliza PDO para consultas e inserções seguras no banco de dados.
 * - Faz log dos dados recebidos em arquivo para depuração.
 * - Utiliza função auxiliar para checar o limite de agendamentos por tipo de caminhão e data.
 * - Usa sessão para identificar o comprador responsável pelo agendamento.
 *
 * REQUISITOS:
 * - Requer o arquivo db.php para conexão com o banco de dados.
 * - Espera que as tabelas 'agendamentos' e 'limites_agendamentos' estejam corretamente configuradas.
 */

session_start();
require 'db.php'; // Conexão com o banco de dados

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log dos dados recebidos via POST para depuração
file_put_contents('debug_post.txt', print_r($_POST, true));

// Verificação do limite de agendamentos para o tipo de caminhão no dia
function verificarLimiteAgendamentos($data, $tipoCaminhao, $pdo) {
    $sqlLimite = "SELECT limite FROM limites_agendamentos WHERE tipo_caminhao = :tipoCaminhao";
    $stmtLimite = $pdo->prepare($sqlLimite);
    $stmtLimite->execute([':tipoCaminhao' => $tipoCaminhao]);
    $limite = $stmtLimite->fetchColumn();

    if (!$limite) {
        return 'Erro: Limite de agendamentos não encontrado para esse tipo de caminhão.';
    }

    $sqlContagem = "SELECT COUNT(*) FROM agendamentos WHERE data_agendamento = :dataAgendamento AND tipo_caminhao = :tipoCaminhao";
    $stmtContagem = $pdo->prepare($sqlContagem);
    $stmtContagem->execute([':dataAgendamento' => $data, ':tipoCaminhao' => $tipoCaminhao]);
    $quantidadeAgendamentos = $stmtContagem->fetchColumn();

    if ($quantidadeAgendamentos < $limite) {
        return true;
    } else {
        return "Erro: Limite de agendamentos atingido para o tipo de caminhão '{$tipoCaminhao}' neste dia.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados do formulário
    $dataAgendamento = $_POST['dataAgendamento'];
    $tipoCaminhao = $_POST['tipoCaminhao'];
    $tipoCarga = $_POST['tipoCarga'];
    $tipoMercadoria = $_POST['tipoMercadoria'];
    $fornecedor = $_POST['fornecedor'];
    $nomeResponsavel = $_POST['nome_responsavel'] ?? '';
    $quantidadePaletes = $_POST['quantidadePaletes'];
    $quantidadeVolumes = $_POST['quantidadeVolumes'];
    $placa = $_POST['placa'];
    $nomeMotorista = $_POST['nomeMotorista'];
    $cpfMotorista = $_POST['cpfMotorista'];
    $numeroContato = $_POST['numeroContato'];
    $tipoRecebimento = $_POST['tipoRecebimento'];
    $comprador = $_SESSION['usuario'] ?? '';
    $status = '';

    // Verificar o limite de agendamentos para o tipo de caminhão
    $resultadoLimite = verificarLimiteAgendamentos($dataAgendamento, $tipoCaminhao, $pdo);
    if ($resultadoLimite !== true) {
        echo $resultadoLimite;
        exit();
    }

    // Insere o agendamento no banco de dados
    $sql = "INSERT INTO agendamentos (
        data_agendamento, tipo_caminhao, tipo_carga, tipo_mercadoria, fornecedor, nome_responsavel,
        quantidade_paletes, quantidade_volumes, placa, nome_motorista, cpf_motorista, numero_contato, tipo_recebimento, comprador, status
    ) VALUES (
        :dataAgendamento, :tipoCaminhao, :tipoCarga, :tipoMercadoria, :fornecedor, :nomeResponsavel,
        :quantidadePaletes, :quantidadeVolumes, :placa, :nomeMotorista, :cpfMotorista, :numeroContato, :tipoRecebimento, :comprador, :status
    )";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':dataAgendamento' => $dataAgendamento,
        ':tipoCaminhao' => $tipoCaminhao,
        ':tipoCarga' => $tipoCarga,
        ':tipoMercadoria' => $tipoMercadoria,
        ':fornecedor' => $fornecedor,
        ':nomeResponsavel' => $nomeResponsavel,
        ':quantidadePaletes' => $quantidadePaletes,
        ':quantidadeVolumes' => $quantidadeVolumes,
        ':placa' => $placa,
        ':nomeMotorista' => $nomeMotorista,
        ':cpfMotorista' => $cpfMotorista,
        ':numeroContato' => $numeroContato,
        ':tipoRecebimento' => $tipoRecebimento,
        ':comprador' => $comprador,
        ':status' => $status
    ]);

    echo "Agendamento realizado com sucesso!";
}
?>
