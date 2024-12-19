<?php
session_start();
require_once 'config.php';

// Verifica se está logado
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Se houver uma mensagem de sucesso na sessão, pegue-a e limpe
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
} else {
    $success = '';
}

$error = '';
$database = new Database();
$db = $database->getConnection();

// Busca todos os clientes para o select
try {
    $query = "SELECT id, name FROM clients ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $clients = $stmt->fetchAll();
} catch(Exception $e) {
    $error = "Erro ao buscar clientes: " . $e->getMessage();
}

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $db->beginTransaction();

        $client_id = $_POST['client_id'];
        $device_model = $_POST['device_model'];
        $delivery_date = $_POST['delivery_date'];
        $reported_issue = $_POST['reported_issue'];
        $accessories = $_POST['accessories'];
        $device_password = $_POST['device_password'];

        // Busca os telefones do cliente
        $stmt = $db->prepare("SELECT phone1, phone2 FROM clients WHERE id = ?");
        $stmt->execute([$client_id]);
        $client = $stmt->fetch();
        $phone1 = $client['phone1'];
        $phone2 = $client['phone2'];

        // Encontra o próximo ID (começando de 16000)
        $stmt = $db->query("
            SELECT COALESCE(MAX(id) + 1, 16000) as next_id 
            FROM service_orders 
            FOR UPDATE");

        $next_id = $stmt->fetch(PDO::FETCH_ASSOC)['next_id'];

        // Verifica se o ID já não foi usado (mantendo a verificação de segurança)
        $check = $db->prepare("SELECT id FROM service_orders WHERE id = ? LIMIT 1");
        $check->execute([$next_id]);
        if ($check->fetch()) {
            throw new Exception("Erro de concorrência ao gerar ID. Por favor, tente novamente.");
        }

        $stmt = $db->prepare("
            INSERT INTO service_orders (id, client_id, device_model, phone1, phone2, 
                                      delivery_date, reported_issue, accessories, device_password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$next_id, $client_id, $device_model, $phone1, $phone2, 
                           $delivery_date, $reported_issue, $accessories, $device_password])) {
            $db->commit();
            // Redireciona direto para a página de impressão
            header("Location: print_service_order.php?id=" . $next_id);
            exit;
        } else {
            throw new Exception("Erro ao criar ordem de serviço.");
        }
    } catch(Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Ordem de Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body::-webkit-scrollbar{
            display: none;
        }
        * { user-select: none; }
        .container { 
            padding-top: 60px; 
            padding-bottom: 2rem; 
            max-width: 800px; 
        }
        .content-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }
        .form-control {
            font-size: 0.9rem;
        }
        textarea.form-control {
            resize: none;
            height: 80px; /* Aumenta a altura do textarea */
        }
    </style>
</head>
<body class="bg-light">
    <a href="dashboard.php" class="btn btn-outline-primary" style="position: absolute; top: 20px; left: 20px;">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>

    <div class="container">
        <div class="content-container">
            <h2 class="mb-4"><i class="bi bi-file-earmark-text"></i> Nova Ordem de Serviço</h2>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" id="serviceOrderForm">
                <div class="row g-3">
                    <!-- Cliente e Data -->
                    <div class="col-md-8">
                        <label for="client_id" class="form-label">Cliente</label>
                        <select class="form-select form-select-sm" id="client_id" name="client_id" required>
                            <option value="">Selecione um cliente</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['id']; ?>">
                                    <?php echo htmlspecialchars($client['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="delivery_date" class="form-label">Data de Entrega</label>
                        <input type="date" class="form-control form-control-sm" id="delivery_date" name="delivery_date" required>
                    </div>

                    <!-- Modelo, Senha e Acessórios na mesma linha -->
                    <div class="col-md-5">
                        <label for="device_model" class="form-label">Modelo do Aparelho</label>
                        <input type="text" class="form-control form-control-sm" id="device_model" name="device_model" required>
                    </div>

                    <div class="col-md-3">
                        <label for="device_password" class="form-label">Senha</label>
                        <input type="text" class="form-control form-control-sm" id="device_password" name="device_password">
                    </div>

                    <div class="col-md-4">
                        <label for="accessories" class="form-label">Acessórios</label>
                        <input type="text" class="form-control form-control-sm" id="accessories" name="accessories">
                    </div>

                    <!-- Defeito em linha separada -->
                    <div class="col-12">
                        <label for="reported_issue" class="form-label">Defeito Reclamado</label>
                        <textarea class="form-control form-control-sm" id="reported_issue" name="reported_issue" required></textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Criar Ordem de Serviço
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Define a data mínima como hoje
        document.getElementById('delivery_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>