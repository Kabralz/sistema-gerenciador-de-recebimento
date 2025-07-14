<?php
/**
 * login.php
 *
 * Página de login e registro de usuários do sistema de agendamento de recebimentos.
 *
 * FUNCIONALIDADE:
 * - Permite que usuários existentes façam login informando usuário e senha.
 * - Permite registrar novos usuários (caso o modal de registro esteja habilitado).
 * - Valida as credenciais informadas consultando a tabela 'usuarios'.
 * - Para usuários 'admin' e 'recebimento', aceita senha em texto puro; para os demais, utiliza password_verify.
 * - Em caso de sucesso, inicia a sessão e redireciona para a página principal ou operacional.
 * - Em caso de erro, exibe mensagem de erro na tela.
 *
 * DETALHES DO FUNCIONAMENTO:
 * - Utiliza PDO para consultas seguras ao banco de dados.
 * - Armazena informações do usuário na sessão após login bem-sucedido.
 * - Modal de registro permite criar novos usuários, verificando duplicidade.
 * - Interface responsiva e estilizada para desktop e mobile.
 *
 * REQUISITOS:
 * - Requer o arquivo db.php para conexão com o banco de dados.
 * - Espera que a tabela 'usuarios' possua os campos: usuario, senha, nome_completo, tipo.
 */

session_start();
require 'db.php'; // Conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['registrar'])) {
        // Registro de novo usuário
        $novo_usuario = $_POST['novo_usuario'];
        $nova_senha = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);
        $nome_completo = $_POST['nome_completo'];
        $tipo = $_POST['tipo'];

        // Verifica se já existe
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
        $stmt->execute([':usuario' => $novo_usuario]);
        if ($stmt->fetch()) {
            $erro = "Usuário já existe!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, senha, nome_completo, tipo) VALUES (:usuario, :senha, :nome_completo, :tipo)");
            $stmt->execute([
                ':usuario' => $novo_usuario,
                ':senha' => $nova_senha,
                ':nome_completo' => $nome_completo,
                ':tipo' => $tipo
            ]);
            $erro = "Usuário registrado com sucesso!";
        }
    } else {
        $usuario = $_POST['usuario'];
        $senha = $_POST['senha'];

        // Consulta o banco de dados para verificar o usuário
        $sql = "SELECT * FROM usuarios WHERE usuario = :usuario";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':usuario' => $usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $loginValido = false;
        if ($user) {
            if (
                ($usuario === 'admin' && $senha === $user['senha']) ||
                ($usuario === 'recebimento' && $senha === $user['senha'])
            ) {
                $loginValido = true;
            } elseif (password_verify($senha, $user['senha'])) {
                $loginValido = true;
            }
        }

        if ($loginValido) {
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['nomeCompleto'] = $user['nome_completo'];
            $_SESSION['tipoUsuario'] = $user['tipo'];

            // Redireciona conforme o tipo de usuário
            if ($user['usuario'] === 'recebimento') {
                header('Location: visao-recebimento.php');
            } else {
                header('Location: pagina-principal.php?comprador=' . urlencode($user['usuario']));
            }
            exit;
        } else {
            $erro = "Credenciais inválidas. Tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento de Cargas</title>
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('./img/background.svg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #254c90;
        }
    </style>
    <style>
        .login-container {
            text-align: center;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            width: 350px;
            position: relative;
        }
        .login-container img {
            width: 200px; /* ou 200px, ajuste conforme desejar */
            max-width: 90%;
            margin-bottom: 20px;
        }
        .login-container h2 {
            color: #0052a5;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .login-container input {
            width: calc(100% - 20px);
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        .login-container input:focus {
            border-color: #0052a5; /* Altera a cor da borda ao focar */
            outline: none; /* Remove o contorno padrão */
            box-shadow: 0 0 5px rgba(0, 82, 165, 0.5); /* Adiciona um leve brilho */
        }
        .login-container button {
            width: 100%;
            padding: 12px;
            background: #0052a5; /* Cor sólida */
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: background 0.3s ease;
        }
        .login-container button:hover {
            background: #003d7a; /* Cor sólida ao passar o mouse */
        }
        .login-container button:active {
            transform: scale(0.98);
        }

        @media (max-width: 600px) {
            body {
                background-image: none;
                background: #254c90;
            }
            .login-container {
                width: 100%;
                max-width: 320px;
                padding: 16px;
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="./img/Logo.svg" alt="Logo Comercial Souza">
        
        <h2>Agendamento de Cargas</h2>
        
        <?php if (isset($erro)): ?>
            <div style="color: red; margin-bottom: 10px;"><?php echo $erro; ?></div>
        <?php endif; ?>
        <?php if (isset($sucesso)): ?>
            <div style="color: green; margin-bottom: 10px;"><?php echo $sucesso; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="usuario" placeholder="Usuário" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">ENTRAR</button>
        </form>
     <!--   <button type="button" onclick="document.getElementById('modal').style.display='block'">Registrar</button> -->

        <!-- Modal de registro -->
        <div id="modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
            <div style="background:#fff; padding:30px; border-radius:10px; width:300px; margin:auto; position:relative;">
                <span style="position:absolute; top:10px; right:15px; cursor:pointer;" onclick="document.getElementById('modal').style.display='none'">&times;</span>
                <h3>Registrar novo usuário</h3>
                <form method="POST" action="">
                    <input type="text" name="novo_usuario" placeholder="Usuário" required>
                    <input type="password" name="nova_senha" placeholder="Senha" required>
                    <input type="text" name="nome_completo" placeholder="Nome completo" required>
                    <select name="tipo" required>
                        <option value="usuario">Usuário</option>
                        <option value="admin">Admin</option>
                        <option value="operacional">Operacional</option>
                    </select>
                    <button type="submit" name="registrar">Registrar</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
