/**
 * calendario.js
 *
 * Script JavaScript responsável por controlar o calendário interativo de agendamentos.
 *
 * FUNCIONALIDADE:
 * - Busca via AJAX os dias bloqueados, parcialmente e totalmente agendados, e disponíveis para o mês/ano atual.
 * - Renderiza o calendário na tela, aplicando estilos e interações conforme o status de cada dia.
 * - Permite navegação entre meses.
 * - Ao clicar em um dia disponível ou parcial, abre o modal para novo agendamento.
 * - Ao abrir o modal, busca a disponibilidade de vagas por tipo de caminhão para a data selecionada.
 * - Desabilita opções de caminhão cujo limite já foi atingido.
 * - Envia o formulário de agendamento via AJAX e atualiza o calendário após sucesso.
 * - Controla modais de conferência e edição de agendamento.
 * - Permite abrir/fechar modais de gerenciamento de limites (se aplicável).
 *
 * DETALHES DO FUNCIONAMENTO:
 * - Utiliza funções assíncronas para buscar dados dos endpoints PHP (verificar_dias.php, verificar_disponibilidade.php).
 * - Manipula o DOM para criar células do calendário, aplicar classes e eventos.
 * - Garante responsividade e feedback visual ao usuário.
 * - Utiliza FormData para envio dos dados do formulário.
 * - Exibe alertas em caso de erro ou sucesso nas operações.
 *
 * REQUISITOS:
 * - Requer elementos HTML com IDs: calendar, monthYear, prevMonth, nextMonth, modal, reservationForm, mensagemErro.
 * - Requer endpoints PHP: verificar_dias.php, verificar_disponibilidade.php, registrar_conferencia.php, etc.
 */

console.log("Arquivo calendario.js carregado");

const calendar = document.getElementById("calendar");
const monthYear = document.getElementById("monthYear");
const prevMonth = document.getElementById("prevMonth");
const nextMonth = document.getElementById("nextMonth");
const modal = document.getElementById("modal");
const form = document.getElementById("reservationForm");
const mensagemErro = document.getElementById("mensagemErro");

let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
let dias = {};
let isSubmitting = false;

// Função para buscar os dados de bloqueios e agendamentos
async function fetchDias() {
    const month = currentMonth + 1;
    const year = currentYear;

    try {
        const response = await fetch(`verificar_dias.php?month=${month}&year=${year}`);
        dias = await response.json();
        console.log("Dias recebidos:", dias);
    } catch (error) {
        console.error("Erro ao buscar os dias:", error);
        dias = { bloqueados: [], parcialmenteAgendados: [], totalmenteAgendados: [], disponiveis: [] };
    }
}

// Função para renderizar o calendário
async function renderCalendar() {
    console.log(`Renderizando o calendário para o mês ${currentMonth + 1} e ano ${currentYear}`);

    await fetchDias();

    monthYear.textContent = new Date(currentYear, currentMonth).toLocaleDateString("pt-BR", {
        month: "long",
        year: "numeric"
    });

    calendar.innerHTML = "";

    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const lastDate = new Date(currentYear, currentMonth + 1, 0).getDate();

    let day = 1;
    const dataAtual = new Date(); // Data atual

    for (let i = 0; i < 6; i++) {
        const row = document.createElement("div");
        row.classList.add("calendar-row");

        for (let j = 0; j < 7; j++) {
            const cell = document.createElement("div");
            cell.classList.add("calendar-cell");

            if (i === 0 && j < firstDay || day > lastDate) {
                cell.classList.add("empty-cell");
            } else {
                const formattedDate = `${currentYear}-${(currentMonth + 1).toString().padStart(2, "0")}-${day.toString().padStart(2, "0")}`;
                cell.textContent = day;
                cell.classList.add("day-cell");
                cell.setAttribute("data-date", formattedDate);

                // Verifica o status do dia e aplica as classes correspondentes
                if (dias.bloqueados.includes(formattedDate)) {
                    cell.classList.add("dia-bloqueado");
                } else if (dias.parcialmenteAgendados.includes(formattedDate)) {
                    cell.classList.add("dia-parcial");
                    // Permitir clique em dias parcialmente agendados
                    cell.addEventListener("click", () => {
                        alert("Este dia já está parcialmente reservado. Você pode agendar, mas verifique o limite de caminhões.");
                        openModal(new Date(formattedDate));
                    });
                } else if (dias.totalmenteAgendados.includes(formattedDate)) {
                    cell.classList.add("dia-total");
                } else {
                    // Bloquear dias passados
                    /*
                    if (new Date(formattedDate) < dataAtual) {
                        cell.classList.add("dia-bloqueado");
                        cell.classList.add("disabled"); // Adiciona a classe disabled
                    } else {
                    */
                        cell.classList.add("dia-disponivel");
                        cell.addEventListener("click", () => openModal(new Date(formattedDate)));
                    /*
                    }
                    */
                }

                day++;
            }

            row.appendChild(cell);
        }

        calendar.appendChild(row);
    }
}

// Função para abrir o modal
async function openModal(dateObj) {
    modal.style.display = "block";
    const formattedDate = dateObj.toISOString().split("T")[0];
    form.dataset.selectedDate = formattedDate;
    document.getElementById("dataAgendamento").value = formattedDate;

    // Verificar o limite de caminhões para a data selecionada
    await verificarLimiteCaminhoes(formattedDate);
}

// Função para verificar o limite de caminhões
async function verificarLimiteCaminhoes(dataAgendamento) {
    const response = await fetch(`verificar_disponibilidade.php?data=${dataAgendamento}`);
    const disponibilidade = await response.json();

    // Obter o select de tipo de caminhão
    const tipoCaminhaoSelect = document.getElementById("tipoCaminhao");
    tipoCaminhaoSelect.innerHTML = '<option value="">Selecione</option>';

    const tipos = ["truck", "toco", "carreta", "utilitarios"];
    const nomes = {
        truck: "Truck",
        toco: "Toco",
        carreta: "Carreta",
        utilitarios: "Utilitários"
    };

    tipos.forEach(tipo => {
        const option = document.createElement("option");
        option.value = tipo;
        if (disponibilidade[tipo] <= 0) {
            option.disabled = true;
            option.textContent = nomes[tipo] + " (Limite atingido)";
        } else {
            option.textContent = nomes[tipo];
        }
        tipoCaminhaoSelect.appendChild(option);
    });
}

// Envio do formulário
form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const formData = new FormData(form);
    try {
        const response = await fetch(form.action, {
            method: "POST",
            body: formData,
        });

        if (response.ok) {
            alert("Agendamento realizado com sucesso!");
            modal.style.display = "none";
            renderCalendar();
        } else {
            alert("Erro ao realizar o agendamento. Tente novamente.");
        }
    } catch (error) {
        console.error("Erro ao enviar o formulário:", error);
        alert("Erro ao realizar o agendamento. Tente novamente.");
    }
});

// Fechar modal ao clicar fora
window.onclick = (event) => { if (event.target === modal) modal.style.display = "none"; };

// Funções para controlar o Modal de Gerenciamento
window.openGerenciamentoModal = function() {
    document.getElementById("modalGerenciamento").style.display = "flex";
};

window.closeGerenciamentoModal = function() {
    document.getElementById("modalGerenciamento").style.display = "none";
};

// Navegação entre meses
prevMonth.addEventListener("click", () => { 
    currentMonth = (currentMonth - 1 + 12) % 12; 
    if (currentMonth === 11) currentYear--; 
    renderCalendar(); 
});

nextMonth.addEventListener("click", () => { 
    currentMonth = (currentMonth + 1) % 12; 
    if (currentMonth === 0) currentYear++; 
    renderCalendar(); 
});

// Renderiza o calendário inicial
renderCalendar();

function abrirModal(id) {
    document.getElementById('modalConferencia').style.display = 'flex';
    document.getElementById('agendamento_id').value = id;
}
function fecharModal() {
    document.getElementById('modalConferencia').style.display = 'none';
}
const formConferencia = document.getElementById('formConferencia');
if (formConferencia) {
    formConferencia.onsubmit = function(e) {
        e.preventDefault();
        fetch('registrar_conferencia.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                alert('Conferência registrada!');
                fecharModal();
                location.reload();
            } else {
                alert(data.message || 'Erro ao registrar.');
            }
        });
    };
}

function abrirModalEdicao(agendamento) {
    document.getElementById('modalEdicao').style.display = 'flex';
    document.getElementById('edit_id').value = agendamento.id;
    document.getElementById('edit_fornecedor').value = agendamento.fornecedor;
    document.getElementById('edit_placa').value = agendamento.placa;
    // Preencha outros campos conforme necessário
}
function fecharModalEdicao() {
    document.getElementById('modalEdicao').style.display = 'none';
}