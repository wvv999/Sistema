<?php
session_start();
require_once 'config.php';

// Verifica se está logado
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$success = $error = '';
$client = null;

// Busca os dados do cliente se ID foi fornecido
if(isset($_GET['id'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $client = $stmt->fetch();
        
        if(!$client) {
            throw new Exception("Cliente não encontrado!");
        }
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

// Processa o formulário de atualização
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    
    $id = $_POST['id'];
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
    $cpf = !empty($cpf) ? $cpf : null;
    $phone1 = preg_replace('/[^0-9]/', '', $_POST['phone1']);
    $phone2 = preg_replace('/[^0-9]/', '', $_POST['phone2']);
    $name = trim($_POST['name']);
    
    try {
        // Verifica se CPF já existe (excluindo o cliente atual)
        if(!empty($cpf)) {
            $check = $db->prepare("SELECT id FROM clients WHERE cpf = ? AND id != ?");
            $check->execute([$cpf, $id]);
            
            if ($check->rowCount() > 0) {
                throw new Exception("Este CPF já está cadastrado para outro cliente!");
            }
        }
        
        // Atualiza os dados do cliente
        $stmt = $db->prepare("UPDATE clients SET name = ?, phone1 = ?, phone2 = ?, cpf = ? WHERE id = ?");
        
        if ($stmt->execute([$name, $phone1, $phone2, $cpf, $id])) {
            $success = "Dados do cliente atualizados com sucesso!";
            // Atualiza os dados exibidos no formulário
            $client = [
                'id' => $id,
                'name' => $name,
                'cpf' => $cpf,
                'phone1' => $phone1,
                'phone2' => $phone2
            ];
        } else {
            throw new Exception("Erro ao atualizar dados do cliente.");
        }
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
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
            max-width: 600px;
            margin: 20px auto;
        }
    </style>
</head>
<body class="bg-light">
    <a href="javascript:history.go(-1)" class="btn btn-outline-primary" style="position: absolute; top: 20px; left: 20px;">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>

    <div class="container">
        <div class="content-container">
            <h2 class="mb-4"><i class="bi bi-pencil"></i> Editar Cliente</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($client): ?>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control bg-light" id="name" name="name" 
                               value="<?php echo htmlspecialchars($client['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cpf" class="form-label">CPF</label>
                        <input type="text" class="form-control bg-light" id="cpf" name="cpf" 
                               value="<?php echo htmlspecialchars($client['cpf']); ?>"
                               maxlength="14" placeholder="000.000.000-00">
                    </div>

                    <div class="mb-3">
                        <label for="phone1" class="form-label">Telefone Principal</label>
                        <input type="text" class="form-control bg-light" id="phone1" name="phone1" 
                               value="<?php echo htmlspecialchars($client['phone1']); ?>"
                               maxlength="15" placeholder="(00) 00000-0000" >
                    </div>

                    <div class="mb-3">
                        <label for="phone2" class="form-label">Telefone Secundário</label>
                        <input type="text" class="form-control bg-light" id="phone2" name="phone2" 
                               value="<?php echo htmlspecialchars($client['phone2']); ?>"
                               maxlength="15" placeholder="(00) 00000-0000">
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script para máscaras de input -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
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
        
        // Aplica as máscaras nos valores iniciais
        maskCPF(document.getElementById('cpf'));
        maskPhone(document.getElementById('phone1'));
        if(document.getElementById('phone2').value) {
            maskPhone(document.getElementById('phone2'));
        }
    });
    </script>
</body>
</html>