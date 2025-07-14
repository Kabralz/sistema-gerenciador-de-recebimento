<?php
/**
 * verificar_disponibilidade.php
 *
 * Endpoint para consulta da disponibilidade de agendamento de caminhões por tipo em uma data específica.
 *
 * FUNCIONALIDADE:
 * - Recebe uma data via parâmetro GET.
 * - Busca no banco de dados o total de agendamentos já realizados para cada tipo de caminhão nessa data.
 * - Busca os limites máximos permitidos para cada tipo de caminhão.
 * - Calcula a disponibilidade restante para cada tipo de caminhão (limite - agendados).
 * - Retorna um JSON com a disponibilidade de cada tipo de caminhão para a data informada.
 *
 * DETALHES DO FUNCIONAMENTO:
 * - Se a data não for informada, retorna um erro em JSON.
 * - Utiliza as tabelas 'agendamentos' (para contar os agendamentos) e 'limites_agendamentos' (para obter os limites).
 * - O resultado é um array associativo: [tipo_caminhao => disponibilidade].
 *
 * REQUISITOS:
 * - Requer o arquivo db.php para conexão com o banco de dados.
 * - Espera que as tabelas 'agendamentos' e 'limites_agendamentos' estejam corretamente configuradas.
 */

require 'db.php';

// Obter a data da requisição
$data = $_GET['data'] ?? null;

if (!$data) {
    echo json_encode(['error' => 'Data não fornecida']);
    exit();
}

// Obter agendamentos para a data específica
$sql = "SELECT data_agendamento, tipo_caminhao, COUNT(*) AS total
        FROM agendamentos
        WHERE data_agendamento = :dataAgendamento
        GROUP BY tipo_caminhao";
$stmt = $pdo->prepare($sql);
$stmt->execute(['dataAgendamento' => $data]);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter limites de caminhões
$sql = "SELECT tipo_caminhao, limite FROM limites_agendamentos";
$stmt = $pdo->query($sql);
$limites = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$disponibilidade = [];

// Inicializar a disponibilidade com os limites
foreach ($limites as $tipo => $limite) {
    $disponibilidade[$tipo] = $limite; // Define a disponibilidade inicial como o limite
}

// Calcular a disponibilidade com base nos agendamentos
foreach ($agendamentos as $agendamento) {
    $tipo = $agendamento['tipo_caminhao'];
    $total = $agendamento['total'];

    // Reduzir a disponibilidade com base nos agendamentos
    if (isset($disponibilidade[$tipo])) {
        $disponibilidade[$tipo] -= $total;
    }
}

// Retornar a disponibilidade como JSON
header('Content-Type: application/json');
echo json_encode($disponibilidade);
?>

