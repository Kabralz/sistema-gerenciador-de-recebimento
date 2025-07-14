<?php
/**
 * verificar_dias.php
 *
 * Endpoint para consulta dos dias disponíveis, bloqueados, parcialmente e totalmente agendados em um mês.
 *
 * FUNCIONALIDADE:
 * - Recebe mês e ano via parâmetros GET (ou usa o mês/ano atual por padrão).
 * - Busca no banco de dados:
 *   - Dias bloqueados manualmente.
 *   - Limites de agendamento por tipo de caminhão.
 *   - Dias com agendamentos realizados e suas quantidades.
 *   - Se há agendamentos em meses anteriores.
 * - Classifica os dias do mês em:
 *   - Bloqueados (manual, finais de semana, dias passados).
 *   - Parcialmente agendados (ainda há vagas para algum tipo de caminhão).
 *   - Totalmente agendados (todos os limites atingidos).
 *   - Disponíveis (não bloqueados e com vagas).
 * - Retorna um JSON com arrays de dias para cada categoria e se há agendamentos em meses anteriores.
 *
 * DETALHES DO FUNCIONAMENTO:
 * - Considera sábados, domingos e dias anteriores ao atual como bloqueados.
 * - Usa as tabelas 'agendamentos', 'dias_bloqueados' e 'limites_agendamentos'.
 * - O resultado é usado para exibir um calendário de agendamento.
 *
 * REQUISITOS:
 * - Requer o arquivo db.php para conexão com o banco de dados.
 * - Espera que as tabelas estejam corretamente configuradas.
 */

require 'db.php';

// Recuperar os parâmetros de mês e ano
$month = $_GET['month'] ?? date('m'); // Mês atual se não for fornecido
$year = $_GET['year'] ?? date('Y');  // Ano atual se não for fornecido

// Formatar o início e o fim do mês
$startDate = "$year-$month-01";
$endDate = date("Y-m-t", strtotime($startDate)); // Último dia do mês

// Verificar se há agendamentos em meses anteriores
$sqlAgendamentosAnteriores = "SELECT COUNT(*) FROM agendamentos WHERE data_agendamento < :startDate";
$stmtAgendamentosAnteriores = $pdo->prepare($sqlAgendamentosAnteriores);
$stmtAgendamentosAnteriores->execute(['startDate' => $startDate]);
$agendamentosAnteriores = $stmtAgendamentosAnteriores->fetchColumn();

// Recuperar dias bloqueados dentro do mês
$sqlBloqueados = "SELECT data FROM dias_bloqueados WHERE data BETWEEN :startDate AND :endDate";
$stmtBloqueados = $pdo->prepare($sqlBloqueados);
$stmtBloqueados->execute(['startDate' => $startDate, 'endDate' => $endDate]);
$diasBloqueados = $stmtBloqueados->fetchAll(PDO::FETCH_COLUMN);

// Recuperar os limites de agendamentos por tipo de caminhão
$sqlLimites = "SELECT tipo_caminhao, limite FROM limites_agendamentos";
$stmtLimites = $pdo->prepare($sqlLimites);
$stmtLimites->execute();
$limites = $stmtLimites->fetchAll(PDO::FETCH_KEY_PAIR);

// Recuperar dias reservados e suas quantidades dentro do mês
$sqlReservados = "SELECT data_agendamento, tipo_caminhao, COUNT(*) as total_reservas 
                  FROM agendamentos 
                  WHERE data_agendamento BETWEEN :startDate AND :endDate 
                  GROUP BY data_agendamento, tipo_caminhao";
$stmtReservados = $pdo->prepare($sqlReservados);
$stmtReservados->execute(['startDate' => $startDate, 'endDate' => $endDate]);
$diasReservados = $stmtReservados->fetchAll(PDO::FETCH_ASSOC);

// Classificar os dias reservados
$parcialmenteAgendados = [];
$totalmenteAgendados = [];

foreach ($diasReservados as $dia) {
    $dataDia = $dia['data_agendamento'];
    $tipoCaminhao = $dia['tipo_caminhao'];
    $totalReservas = $dia['total_reservas'];

    // Verificar o limite de agendamento para o tipo de caminhão
    if (isset($limites[$tipoCaminhao])) {
        if ($totalReservas < $limites[$tipoCaminhao]) {
            $parcialmenteAgendados[$dataDia][] = $tipoCaminhao;
        } else {
            $totalmenteAgendados[$dataDia][] = $tipoCaminhao;
        }
    }
}

// Determinar todos os dias do mês
$todosOsDias = [];
$periodo = new DatePeriod(
    new DateTime($startDate),
    new DateInterval('P1D'),
    (new DateTime($endDate))->modify('+1 day')
);

$dataAtual = new DateTime();
$dataAtual->setTime(0, 0, 0); // Garante que só a data conta

foreach ($periodo as $dia) {
    $todosOsDias[] = $dia->format('Y-m-d');

    // Bloquear automaticamente os domingos e sábados
    if ($dia->format('w') == 0 || $dia->format('w') == 6) {
        $diasBloqueados[] = $dia->format('Y-m-d');
    }

    // Bloquear dias anteriores ao dia atual (mas não hoje)
    if ($dia < $dataAtual) {
        $diasBloqueados[] = $dia->format('Y-m-d');
    }
}

// Remover duplicatas e reindexar
$diasBloqueados = array_values(array_unique($diasBloqueados));

// Determinar dias disponíveis
$diasDisponiveis = array_values(array_diff($todosOsDias, $diasBloqueados));

// Retornar os dados como JSON
header('Content-Type: application/json');
echo json_encode([
    "bloqueados" => $diasBloqueados,
    "parcialmenteAgendados" => array_keys($parcialmenteAgendados),
    "totalmenteAgendados" => array_keys($totalmenteAgendados),
    "disponiveis" => $diasDisponiveis,
    "agendamentosAnteriores" => $agendamentosAnteriores > 0 // Indica se há agendamentos em meses anteriores
]);
?>
