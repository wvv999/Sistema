<?php
session_start();
require_once 'config.php';

// Verifica se está logado
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$success = $error = '';

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    
    // Remove caracteres especiais do CPF e telefones
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
    $cpf = !empty($cpf) ? $cpf : null;
    $phone1 = preg_replace('/[^0-9]/', '', $_POST['phone1']);
    $phone2 = preg_replace('/[^0-9]/', '', $_POST['phone2']);
    $name = trim($_POST['name']);
    
    try {
        // Verifica se CPF já existe
        if(!empty($cpf)) {
            $check = $db->prepare("SELECT id FROM clients WHERE cpf = ?");
            $check->execute([$cpf]);
            
            if ($check->rowCount() > 0) {
                throw new Exception("Este CPF já está cadastrado!");
            }
        }
        
        // Insere o novo cliente
        $stmt = $db->prepare("INSERT INTO clients (name, phone1, phone2, cpf) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $phone1, $phone2, $cpf])) {
            $_SESSION['success_message'] = "Cliente cadastrado com sucesso!";
            $success = $_SESSION['success_message'];
            unset($_SESSION['success_message']);
        } else {
            throw new Exception("Erro ao cadastrar cliente.");
        }
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

// Busca todos os clientes para exibir na tabela
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM clients ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $clients = $stmt->fetchAll();
} catch(Exception $e) {
    $error = "Erro ao buscar clientes: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body::-webkit-scrollbar{
            display: none;
        }
        .container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        
        .content-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .client-count {
            background-color: #f8f9fa;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .table > tbody > tr > td {
            vertical-align: middle;
        }
    </style>
</head>
<body class="bg-light">
    <a href="javascript:history.go(-1)" class="btn btn-outline-primary" style="position: absolute; top: 20px; left: 20px;">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>

    <div class="container">
        <div class="content-container">
            <div class="header-container">
                <h2><i class="bi bi-people"></i> Gerenciar Clientes</h2>
                <div class="client-count">
                    Total de clientes: <?php echo count($clients); ?>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Formulário de Cadastro -->
            <form method="POST" class="mb-4" id="clientForm">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control bg-light" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cpf" class="form-label">CPF</label>
                            <input type="text" class="form-control bg-light" id="cpf" name="cpf" 
                                maxlength="14" placeholder="000.000.000-00">
                        </div>

                        <div class="mb-3">
                            <label for="phone1" class="form-label">Telefone Principal</label>
                            <input type="text" class="form-control bg-light" id="phone1" name="phone1" 
                                maxlength="15" placeholder="(00) 00000-0000" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone2" class="form-label">Telefone Secundário</label>
                            <input type="text" class="form-control bg-light" id="phone2" name="phone2" 
                                maxlength="15" placeholder="(00) 00000-0000">
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> Cadastrar Cliente
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Tabela de Clientes -->
            <h3 class="mt-4 mb-3">Clientes Cadastrados</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Telefone Principal</th>
                            <th>Telefone Secundário</th>
                            <th>Data de Cadastro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td>
                                    <i class="bi bi-person-circle me-2"></i>
                                    <a href="ordens_cliente.php?id=<?php echo $client['id']; ?>" class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($client['name']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($client['cpf']); ?></td>
                                <td><?php echo htmlspecialchars($client['phone1']); ?></td>
                                <td><?php echo htmlspecialchars($client['phone2']); ?></td>
                                <td>
                                    <i class="bi bi-calendar-event me-2"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($client['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script para máscaras de input e alertas -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Máscaras para os inputs
        function maskCPF(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            input.value = value;
        }

        function maskPhone(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            input.value = value;
        }

        // Aplica as máscaras
        document.getElementById('cpf').addEventListener('input', function() {
            maskCPF(this);
        });

        document.getElementById('phone1').addEventListener('input', function() {
            maskPhone(this);
        });

        document.getElementById('phone2').addEventListener('input', function() {
            maskPhone(this);
        });

        // Remove alertas após 3 segundos
        const alertSuccess = document.querySelector('.alert-success');
        if (alertSuccess) {
            setTimeout(() => {
                alertSuccess.remove();
            }, 3000);
        }

        // Limpa o formulário após cadastro bem-sucedido
        if (alertSuccess) {
            document.getElementById('clientForm').reset();
        }
    });
    </script>
</body>
</html>