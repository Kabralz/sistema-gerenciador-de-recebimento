<?php
/**
 * registrar_conferencia.php
 *
 * Endpoint para registrar a conferência de recebimento de um agendamento.
 *
 * FUNCIONALIDADE:
 * - Recebe dados do formulário via POST (id do agendamento, paletes, volumes, observações, nome do conferente).
 * - Valida os dados recebidos.
 * - Busca a senha do agendamento.
 * - Insere os dados na tabela 'conferencias_recebimento', incluindo a senha e data/hora da conferência.
 * - Atualiza o status do agendamento para "Recebido".
 * - Calcula o tempo entre a chegada da NF e a conferência, salvando esse tempo no campo 'tempo' do agendamento.
 * - Retorna resposta JSON indicando sucesso ou erro.
 *
 * DETALHES DO FUNCIONAMENTO:
 * - Utiliza PDO para consultas e inserções seguras no banco de dados.
 * - Aceita zero como valor válido para paletes/volumes.
 * - Calcula o tempo decorrido entre chegada_nf e conferência no formato HH:MM.
 *
 * REQUISITOS:
 * - Requer o arquivo db.php para conexão com o banco de dados.
 * - Espera que as tabelas 'agendamentos' e 'conferencias_recebimento' estejam corretamente configuradas.
 */

require 'db.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Recebe os dados do formulário
$agendamento_id = $_POST['agendamento_id'] ?? null;
$paletes_recebidos = $_POST['paletes_recebidos'] ?? null;
$volumes_recebidos = $_POST['volumes_recebidos'] ?? null;
$observacoes = $_POST['observacoes'] ?? null;
$nome_conferente = $_POST['nome_conferente'] ?? null;

// Validação correta: aceita zero, mas não vazio ou nulo
if (
    empty($agendamento_id) ||
    !is_numeric($paletes_recebidos) ||
    !is_numeric($volumes_recebidos) ||
    empty($nome_conferente)
) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    exit;
}

// Buscar a senha do agendamento
$stmtSenha = $pdo->prepare("SELECT senha FROM agendamentos WHERE id = :id");
$stmtSenha->execute([':id' => $agendamento_id]);
$senha = $stmtSenha->fetchColumn();

// Insere na tabela de conferências, incluindo a senha
$sql = "INSERT INTO conferencias_recebimento 
            (agendamento_id, senha, paletes_recebidos, volumes_recebidos, observacoes, nome_conferente, data_conferencia)
        VALUES 
            (:agendamento_id, :senha, :paletes_recebidos, :volumes_recebidos, :observacoes, :nome_conferente, NOW())";
$stmt = $pdo->prepare($sql);
$ok = $stmt->execute([
    ':agendamento_id' => $agendamento_id,
    ':senha' => $senha,
    ':paletes_recebidos' => $paletes_recebidos,
    ':volumes_recebidos' => $volumes_recebidos,
    ':observacoes' => $observacoes,
    ':nome_conferente' => $nome_conferente
]);

if ($ok) {
    // Atualiza o status do agendamento para "Recebido"
    $stmt2 = $pdo->prepare("UPDATE agendamentos SET status = 'Recebido' WHERE id = :id");
    $stmt2->execute([':id' => $agendamento_id]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco.']);
}

// registrar_conferencia.php (exemplo)
$id = $_POST['agendamento_id'];

// Busque os horários
$stmt = $pdo->prepare("SELECT chegada_nf FROM agendamentos WHERE id = ?");
$stmt->execute([$id]);
$chegada_nf = $stmt->fetchColumn();

$data_conferencia = date('Y-m-d H:i:s'); // ou o valor recebido

$inicio = strtotime($chegada_nf);
$fim = strtotime($data_conferencia);

if ($inicio && $fim && $fim > $inicio) {
    $diff = $fim - $inicio;
    $horas = floor($diff / 3600);
    $minutos = floor(($diff % 3600) / 60);
    $tempo = sprintf('%02d:%02d', $horas, $minutos); // formato HH:MM

    // Salva no banco
    $stmt = $pdo->prepare("UPDATE agendamentos SET tempo = ? WHERE id = ?");
    $stmt->execute([$tempo, $id]);
}