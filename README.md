# 📦 Sistema de Agendamento de Cargas - Comercial Souza

[![Status](https://img.shields.io/badge/status-Em%20Produção-brightgreen)]()
[![PHP](https://img.shields.io/badge/PHP-7%2B-blue)]()
[![XAMPP](https://img.shields.io/badge/XAMPP-Apache%20%2B%20MySQL-orange)]()
[![Banco](https://img.shields.io/badge/MySQL-Database-yellowgreen)]()

---

## 📝 Descrição

Contexto: 
O setor de recebimento enfrentava dificuldades para mensurar sua real capacidade operacional, como o número de carretas atendidas, volumes recebidos, quantidade de paletes e o tempo médio por descarga. A falta desses informações impactava diretamente a organização da logística, gerando excesso de horas extras, sobrecarga da equipe e insatisfação de fornecedores devido ao longo tempo de espera.

Ação: 
Desenvolvimento de um sistema Gerenciador de Recebimento, cuja função baseia-se em: agendamento de cargas captando todas as informações necessárias e importantes para registro, lista de agendamentos e Fila de espera.

Resultado:
A solução centralizou as informações, trouxe controle total do fluxo de recebimento e implementou um calendário de agendamento, permitindo que tanto a equipe interna quanto os fornecedores/clientes façam seus agendamentos diretamente, inclusive pelo celular. A implantação trouxe mais organização para a logística, otimizou a preparação da equipe para receber os caminhões.

---

## 🔧 Funcionalidades Principais

✅ Calendário com dias disponíveis para agendamento  
✅ Controle por status (agendado, liberado, recebido, conferido)  
✅ Painel de chamadas para recepção  
✅ Registro e controle de conferência  
✅ Visualizações internas e públicas separadas  
✅ Interface multiusuário com permissões básicas  
✅ Logs de operação e chamadas de motorista  
✅ Configuração de horários e dias disponíveis  
✅ Totalmente em PHP + MySQL + JS

---

## 📁 Estrutura do Projeto

```
recebimento/
├── css/                            # Estilos do sistema
├── img/                            # Imagens (logo, fundos, ícones)
├── js/                             # Scripts JS (eventos, interações)
├── db.php                          # Conexão com o banco MySQL

├── login.php                       # Tela de login
├── valida_login.php               # Validação de usuário
├── logout.php                     # Logout do sistema

├── pagina-principal.php           # Calendário de agendamentos
├── processar_agendamento.php      # Processamento de agendamentos
├── editar-agendamento.php         # Edição de agendamento

├── gerenciamento-calendario.php   # Gerenciamento de dias úteis
├── obter_dias_status.php          # Consulta status de dias
├── verificar_dias.php             # Verifica disponibilidade
├── atualizar_status.php           # Atualiza status dos dias

├── visao-agendamentos.php         # Visualização interna
├── visao-agendamentos-publico.php # Visualização pública
├── visao-recepcao.php             # Visualização da recepção
├── visao-recebimento.php          # Tela para recebimento e registro de entrada

├── chamar_motorista.php           # Chamada do motorista
├── painel-senhas.php              # Painel de senha chamado

├── registrar_conferencia.php      # Registro da conferência
├── get_conferencia.php            # Consulta conferência

├── salvar-visita.php              # Registro de visitas externas
├── pagina-publica.php             # Página externa institucional

├── atualizar_local_recebimento.php # Atualiza local de entrega
├── verificar_chamada.php          # Consulta chamada
├── verificar_disponibilidade.php  # Disponibilidade por horário

├── ver-agendamentos-publico.php   # Visualização pública alternativa
├── teste.php, debug_post.txt, log_status.txt # Logs e testes
```

---

## 🛠️ Como Executar (Ambiente Local)

1. Instale o [XAMPP](https://www.apachefriends.org/index.html)
2. Copie a pasta `recebimento/` para `C:/xampp/htdocs/`
3. Inicie o Apache e o MySQL pelo painel do XAMPP
4. Crie o banco de dados `recebimento` no **phpMyAdmin**
5. Importe o arquivo `.sql` com a estrutura do banco
6. Acesse no navegador:
```
http://localhost/recebimento/login.php
```

---

## 🔐 Usuários e Permissões

- Usuários definidos no `valida_login.php`
- Controle de permissões feito por tipo de usuário
- O sistema diferencia visualizações públicas e privadas

---

## 📸 Capturas de tela e explicações

> As imagens a seguir ilustram as funcionalidades do sistema.

### 1. 🔐 Login (`login.php`)
Tela de autenticação com controle por tipo de perfil.  
![Login](prints/login.png)

### 2. 📅 Calendário de Agendamentos (`pagina-principal.php`)
Interface com dias disponíveis, bloqueados e modal de agendamento.  
![Calendário de Agendamentos](prints/calendario.png)

### 3. 🗂️ Visualização de Agendamentos (`visao-agendamentos.php`)
Área interna para consulta de todos os agendamentos cadastrados.  
![Visualização de Agendamentos](prints/agendamentos.png)

### 4. 🧾 Visualização de Recebimento (`visao-recebimento.php`)
Permite registro e liberação das cargas que chegam no dia.  
![Visualização de Recebimento](prints/recebimento.png)

### 5. 🛎️ Painel da Recepção (`visao-recepcao.php`)
Mostra agendamentos do dia com botão de chamada e conferência.  
![Painel da Recepção](prints/recepcao.png)

### 6. 🌐 Página Pública (`pagina-publica.php`)
Apresenta informações e acesso ao módulo público.  
![Página Pública](prints/publica.png)

### 7. 👁️ Calendário Publico (`pagina-publica.php`)
Permite qualquer visitante consultar dias agendados/livres.  
![Calendário Publico](prints/calendario-publico.png)

### 8. 👁️ Ver Agendamentos Públicos (`visao-agendamentos-publico.php`)
Permite qualquer visitante consultar os agendamentos que ele mesmo fez.  
![Ver Agendamentos Públicos](prints/agendamentos-publicos.png)

### 9. 📤 Redirecionamento por E-mail
O setor de Compras é responsável por encaminhar automaticamente o link de agendamento aos fornecedores, facilitando o processo de marcação de entregas.
![Email](prints/email.png)

---

## 👨‍💻 Autor

**Matheus Cabral**  
Sistema desenvolvido para uso interno da operação logística do Souza Atacado Distribuidor.  

---

## 🤝 Colaboradores

**Alexandre Rodrigues** – Contribuições no layout e experiência visual

---

## 📄 Licença

Projeto de uso interno.  
Livre para adaptar conforme a necessidade da empresa.
