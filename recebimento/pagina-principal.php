<?php
/**
 * pagina-principal.php
 *
 * Página principal do sistema de agendamento de recebimentos (restrita a usuários autenticados).
 *
 * FUNCIONALIDADE:
 * - Exibe um calendário interativo para consulta e solicitação de agendamento de recebimento.
 * - Permite ao usuário logado realizar novos agendamentos preenchendo formulário detalhado.
 * - Mostra legenda explicativa sobre os tipos de dias (bloqueado, disponível, parcial, total).
 * - Permite consultar todos os agendamentos já realizados (botão "Ver Agendamentos").
 * - Usuários "admin" e "marlon" podem acessar o gerenciamento de limites diários do calendário.
 * - Possui botão de logout para encerrar a sessão.
 *
 * DETALHES DO FUNCIONAMENTO:
 * - Verifica se o usuário está autenticado via sessão; caso contrário, redireciona para o login.
 * - Utiliza JavaScript para controlar a exibição do calendário, formulários e modais.
 * - O formulário de agendamento envia os dados para processar_agendamento.php.
 * - O calendário é alimentado via AJAX (js/calendario.js) consultando endpoints PHP para saber a disponibilidade de cada dia.
 * - O modal de gerenciamento de limites diários é exibido apenas para usuários autorizados.
 *
 * REQUISITOS:
 * - Requer autenticação de usuário (sessão iniciada).
 * - Requer os arquivos js/calendario.js, processar_agendamento.php, visao-agendamentos.php e gerenciamento-calendario.php.
 * - Utiliza imagens e CSS próprios para layout e identidade visual.
 */

session_start();

// Verifica se o usuário está logado
$usuario = $_SESSION['usuario'] ?? null;

if (!$usuario) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Agendamentos Comercial Souza</title>
  <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
  <link rel="stylesheet" href="css/estilos-calendario.css" />
  <style>
/* 🔹 Estilo Geral */
body {
  margin: 0;
  font-family: 'Segoe UI', Arial, sans-serif;
  background: url('./img/background.png') no-repeat center center fixed;
  background-size: cover;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
  color: #254c90;
}

h1 {
  color: #fff;
}

/* 🔹 Logo */
.logo-topo,
img[alt="Logo Comercial Souza"] {
  max-width: 220px;
  width: 100%;
  height: auto;
  display: block;
  margin: 24px auto 20px;
}

@media (max-width: 600px) {
  .logo-topo,
  img[alt="Logo Comercial Souza"] {
    max-width: 160px;
    margin: 20px auto 16px;
  }
}

/* 🔹 Legenda do Calendário */
.calendar-legend {
  background: #ffffffea;
  padding: 12px 20px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  font-size: 14px;
  width: 100%;
  max-width: 460px;
  border: 1px solid #dbe4f3;
  margin-bottom: 18px;
}

.calendar-legend ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  justify-content: center;
  gap: 18px;
}

.calendar-legend li {
  display: flex;
  align-items: center;
}

.calendar-legend span {
  width: 18px;
  height: 18px;
  border-radius: 5px;
  margin-right: 6px;
}

.legend-blocked { background: gray; }
.legend-available { background: #28a745; }
.legend-partial { background: #fd7e14; }
.legend-full { background: #dc3545; }

/* 🔹 Modal de Gerenciamento */
#modalGerenciamento {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  z-index: 1000;
  justify-content: center;
  align-items: center;
}

#modalGerenciamento .modal-content {
  background: #fff;
  padding: 24px 20px;
  width: 95vw;
  max-width: 440px;
  max-height: 92vh;
  border-radius: 20px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.25);
  display: flex;
  flex-direction: column;
  position: relative;
  overflow-y: auto;
  gap: 14px;
  animation: fadeIn 0.4s ease;
}

#modalGerenciamento h2 {
  font-size: 1.4rem;
  color: #254c90;
  text-align: center;
  font-weight: 700;
  margin-bottom: 8px;
}

#modalGerenciamento .close {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 32px;
  height: 32px;
  background: #f1f5f9;
  border: none;
  border-radius: 50%;
  font-size: 1.4rem;
  color: #254c90;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  transition: background 0.3s, transform 0.3s;
}

#modalGerenciamento .close:hover {
  background: #d9534f;
  color: white;
  transform: rotate(90deg);
}

/* 🔹 Inputs e Seletores */
#modalGerenciamento input,
#modalGerenciamento select {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #d3d9e4;
  border-radius: 8px;
  background: #fff;
  font-size: 1rem;
  box-sizing: border-box;
  transition: border-color 0.25s, box-shadow 0.25s;
}

#modalGerenciamento input:focus,
#modalGerenciamento select:focus {
  border-color: #254c90;
  box-shadow: 0 0 0 3px rgba(37, 76, 144, 0.2);
  outline: none;
}

#modalGerenciamento label {
  font-weight: 600;
  color: #254c90;
  margin-bottom: 4px;
  display: block;
}

/* 🔹 Botões */
#modalGerenciamento button[type="submit"],
#modalGerenciamento .modal-content button:not(.close) {
  width: 100%;
  padding: 12px 0;
  background: linear-gradient(to right, #254c90, #1e3b75);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s, transform 0.2s;
}

#modalGerenciamento button[type="submit"]:hover,
#modalGerenciamento .modal-content button:not(.close):hover {
  background: linear-gradient(to right, #1a3666, #162d57);
  transform: translateY(-2px);
}

#modalGerenciamento iframe {
  width: 100%;
  min-height: 220px;
  height: 38vh;
  border: none;
  border-radius: 10px;
  background: #f8fafc;
  flex: 1 1 auto;
}

/* 🔹 Calendário Responsivo */
@media (max-width: 600px) {
  .calendar-container {
    width: 98vw;
    padding: 6px 2vw 12px 2vw;
    border-radius: 10px;
  }
  #calendarDays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
  }
  .day-cell,
  #calendarDays > div {
    width: 28px;
    height: 28px;
    min-width: 28px;
    max-width: 32px;
    font-size: 12px;
    border-radius: 4px;
    padding: 0;
    margin: 0;
    box-sizing: border-box;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .calendar-weekdays > div {
    font-size: 12px;
    padding: 2px 0;
  }
}

/* 🔹 Legenda Mobile */
.legend-mobile {
  display: none;
}

@media (max-width: 768px) {
  .calendar-legend {
    display: none;
  }

  .legend-mobile {
    display: block;
    background: rgba(255, 255, 255, 0.95);
    padding: 12px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    margin-bottom: 20px;
    width: 90vw;
    max-width: 440px;
    margin-left: auto;
    margin-right: auto;
  }

  .legend-mobile .legend-items {
    display: flex;
    justify-content: space-around;
    font-size: 12px;
  }

  .legend-mobile span {
    width: 15px;
    height: 15px;
    margin-right: 5px;
    border-radius: 4px;
  }
}

/* 🔹 Animação */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.96);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* 🔹 Placa em caixa alta */
#placa {
  text-transform: uppercase;
}
</style>

</head>
<body>
  <img src="./img/Logo.svg" alt="Logo Comercial Souza">

  <!-- Botão de Logout -->
  <div style="position: absolute; top: 20px; right: 20px;">
    <form action="logout.php" method="POST">
      <button style="
        padding: 10px 15px;
        background-color: #d9534f;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        box-shadow: 1px 1px 5px rgba(0,0,0,0.2);
      ">
        Sair
      </button>
    </form>
  </div>

  <!-- Botão de Gerenciamento (somente para admin e marlon) -->
<?php if ($usuario === 'admin' || $usuario === 'marlon'): ?>
  <div id="botaoGerenciamento" style="margin-bottom: 20px;">
    <button onclick="openGerenciamentoModal()">Limites Diários</button>
  </div>
<?php endif; ?>


  <!-- Modal de Gerenciamento de Calendário -->
<div class="modal" id="modalGerenciamento">
    <div class="modal-content">
        
        <h2>Gerenciar Limites Diários</h2>
        <button class="close" id="closeGerenciamentoBtn" title="Fechar">&times;</button>
        <iframe src="gerenciamento-calendario.php" style="width: 100%; height: 500px; border: none;"></iframe>
    </div>
</div>


  <!-- Legenda do Calendário -->
  <div class="calendar-legend">
    <ul>
        <li><span class="legend-blocked"></span>Bloqueado</li>
        <li><span class="legend-available"></span>Disponível</li>
        <li><span class="legend-partial"></span>Parcialmente Agendado</li>
        <li><span class="legend-full"></span>Totalmente Agendado</li>
    </ul>
  </div>

  <!-- Legenda Mobile (horizontal) -->
<div class="calendar-legend legend-mobile">
  <div class="legend-items">
    <div><span class="legend-blocked"></span> Bloqueado</div>
    <div><span class="legend-available"></span> Disponível</div>
    <div><span class="legend-partial"></span> Parcial</div>
    <div><span class="legend-full"></span> Cheio</div>
  </div>
</div>

  <!-- Calendário -->
  <div class="calendar-container">
    <div class="calendar-header">
      <button id="prevMonth" class="nav-button">&lt;</button>
      <div class="month-year" id="monthYear"></div>
      <button id="nextMonth" class="nav-button">&gt;</button>
    </div>
    <div class="calendar-weekdays">
      <div>Dom</div>
      <div>Seg</div>
      <div>Ter</div>
      <div>Qua</div>
      <div>Qui</div>
      <div>Sex</div>
      <div>Sáb</div>
    </div>

    <div id="calendar">
        <div id="calendarDays"></div>
    </div>
  </div>

  <!-- Modal de Agendamento -->
<div class="modal" id="modal">
  <div class="modal-content">
    <button class="close" id="closeModalBtn" title="Fechar">&times;</button>
    
    <h2>Informações do Transporte</h2>
    <form id="reservationForm" action="processar_agendamento.php" method="POST">
      <input type="hidden" name="dataAgendamento" id="dataAgendamento">
      <input type="hidden" name="nome_responsavel" value="<?php echo htmlspecialchars($usuario); ?>">
      <div class="form-grid">
        <div>
          <label for="tipoCaminhao">Tipo do Caminhão</label>
          <select id="tipoCaminhao" name="tipoCaminhao" required>
            <option value="">Selecione</option>
            <option value="truck">Truck</option>
            <option value="toco">Toco</option>
            <option value="carreta">Carreta</option>
          </select>

          <label for="tipoCarga">Tipo de Carga</label>
          <select id="tipoCarga" name="tipoCarga" required>
            <option value="">Selecione</option>
            <option value="Batida">Batida</option>
            <option value="Paletizada">Paletizada</option>
            <option value="Mix Produtos">Mix Produtos</option>
          </select>

          <label for="tipoMercadoria">Tipo de Mercadoria</label>
          <input type="text" id="tipoMercadoria" name="tipoMercadoria" required>

          <label for="fornecedor">Fornecedor</label>
          <input type="text" id="fornecedor" name="fornecedor" required>
        </div>
        <div>
          <label for="quantidadePaletes">Quantidade de Paletes</label>
          <input type="number" id="quantidadePaletes" name="quantidadePaletes" min="0" required>

          <label for="quantidadeVolumes">Quantidade de Volumes</label>
          <input type="number" id="quantidadeVolumes" name="quantidadeVolumes" min="0" required>

          <label for="placa">Placa</label>
          <input type="text" id="placa" name="placa" pattern="[A-Z]{3}[0-9][A-Z0-9][0-9]{2}" required>
        </div>
      </div>

      <label for="nomeMotorista">Nome do Motorista</label>
      <input type="text" id="nomeMotorista" name="nomeMotorista" required>

      <label for="cpfMotorista">CPF do Motorista</label>
      <input type="text" id="cpfMotorista" name="cpfMotorista" pattern="\d{11}" maxlength="11" required>

      <label for="numeroContato">Número de Contato</label>
      <input type="tel" id="numeroContato" name="numeroContato" required>

      <label for="tipoRecebimento">Tipo de Recebimento</label>
      <select id="tipoRecebimento" name="tipoRecebimento" required>
        <option value="">Selecione</option>
        <option value="Porte Pequeno">Porte Pequeno</option>
        <option value="Porte Médio">Porte Médio</option>
        <option value="Porte Grande">Porte Grande</option>
      </select>

      <button type="submit">Confirmar Agendamento</button>
    </form>
  </div>
</div>

   <!-- Botão para Visão dos Agendamentos -->
  <div style="margin-top: 20px; text-align: center;">
    <button onclick="window.location.href='visao-agendamentos.php'" style="
      padding: 10px 20px;
      background-color: #28a745;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.2);
      transition: background-color 0.3s ease;
    " onmouseover="this.style.backgroundColor='#28a745'" onmouseout="this.style.backgroundColor='#28a745'">
      Ver Agendamentos
    </button>
  </div>
  
  <!-- Passando usuário para o JS -->
  <script>
    const usuario = "<?php echo $usuario; ?>";
  </script>
  <script src="js/calendario.js"></script>
  <script>
    document.getElementById('placa').addEventListener('input', function() {
  this.value = this.value.toUpperCase();
});
  </script>
  <script>
document.addEventListener('DOMContentLoaded', function() {
  // Fecha o modal ao clicar no X
  var modal = document.getElementById('modal');
  var closeBtn = modal.querySelector('.close');
  closeBtn.addEventListener('click', function() {
    modal.style.display = 'none';
  });
});
  </script>
  <script>
// Fecha o modal de gerenciamento ao clicar no X
document.getElementById('closeGerenciamentoBtn').onclick = function() {
  document.getElementById('modalGerenciamento').style.display = 'none';
};
  </script>
</body>
</html>
