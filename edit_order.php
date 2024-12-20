<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Verifica se está logado
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$success = $error = '';
$order = null;

// Busca os dados da ordem se ID foi fornecido
if(isset($_GET['id'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Busca a ordem com dados do cliente
        $query = "SELECT so.*, c.name as client_name, c.phone1, c.phone2 
                  FROM service_orders so 
                  JOIN clients c ON so.client_id = c.id 
                  WHERE so.id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$_GET['id']]);
        $order = $stmt->fetch();
        
        if(!$order) {
            throw new Exception("Ordem de serviço não encontrada!");
        }

        // Busca todos os clientes para o select
        $clientQuery = "SELECT id, name FROM clients ORDER BY name";
        $stmt = $db->prepare($clientQuery);
        $stmt->execute();
        $clients = $stmt->fetchAll();

    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

// Processa o formulário de atualização
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Inicia transação
        $db->beginTransaction();
        
        $id = $_POST['id'];
        $client_id = $_POST['client_id'];
        $device_model = trim($_POST['device_model']);
        $delivery_date = $_POST['delivery_date'];
        $reported_issue = trim($_POST['reported_issue']);
        $accessories = trim($_POST['accessories']);
        $device_password = trim($_POST['device_password']);
        $pattern_password = trim($_POST['pattern_password'] ?? '');
        
        // Atualiza a ordem
        $query = "UPDATE service_orders SET 
                  client_id = ?,
                  device_model = ?,
                  delivery_date = ?,
                  reported_issue = ?,
                  accessories = ?,
                  device_password = ?,
                  pattern_password = ?,
                  last_modified = NOW(),
                  last_modified_by = ?
                  WHERE id = ?";
                  
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            $client_id,
            $device_model,
            $delivery_date,
            $reported_issue,
            $accessories,
            $device_password,
            $pattern_password,
            $_SESSION['user_id'],
            $id
        ]);

        if ($result) {
            // Registra a atividade
            registerActivity(
                $_SESSION['user_id'],
                "Ordem atualizada",
                $id,
                'order_update'
            );
            
            $db->commit();
            $success = "Ordem atualizada com sucesso!";
            
            // Atualiza os dados exibidos
            $stmt = $db->prepare($query);
            $stmt->execute([$_GET['id']]);
            $order = $stmt->fetch();
            
        } else {
            throw new Exception("Erro ao atualizar ordem de serviço.");
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
    <title>Editar Ordem de Serviço</title>
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

        .info-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .current-info {
            color: #6c757d;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        textarea {
            resize: none;
            min-height: 100px;
        }

        .btn-toolbar {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body class="bg-light">
    <a href="gestao.php" class="btn btn-outline-primary" style="position: absolute; top: 20px; left: 20px;">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>

    <div class="container">
        <div class="content-container">
            <h2 class="mb-4">
                <i class="bi bi-pencil-square"></i>
                Editar Ordem de Serviço #<?php echo $order['id']; ?>
            </h2>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($order): ?>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                    
                    <!-- Informações do Cliente -->
                    <div class="info-section">
                        <h5 class="mb-3">Informações do Cliente</h5>
                        
                        <div class="mb-3">
                            <label for="client_id" class="form-label">Cliente</label>
                            <select class="form-select" id="client_id" name="client_id" required>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo $client['id']; ?>" 
                                            <?php echo $client['id'] == $order['client_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($client['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="current-info">
                            <div>Telefone Principal: <?php echo htmlspecialchars($order['phone1']); ?></div>
                            <?php if ($order['phone2']): ?>
                                <div>Telefone Secundário: <?php echo htmlspecialchars($order['phone2']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Informações do Dispositivo -->
                    <div class="info-section">
                        <h5 class="mb-3">Informações do Dispositivo</h5>
                        
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label for="device_model" class="form-label">Modelo do Dispositivo</label>
                                <input type="text" class="form-control" id="device_model" name="device_model" 
                                       value="<?php echo htmlspecialchars($order['device_model']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="delivery_date" class="form-label">Data de Entrega</label>
                                <input type="date" class="form-control" id="delivery_date" name="delivery_date"
                                       value="<?php echo $order['delivery_date']; ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="device_password" class="form-label">Senha do Dispositivo</label>
                                <input type="text" class="form-control" id="device_password" name="device_password"
                                       value="<?php echo htmlspecialchars($order['device_password']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="accessories" class="form-label">Acessórios</label>
                                <textarea class="form-control" id="accessories" name="accessories"><?php echo htmlspecialchars($order['accessories']); ?></textarea>
                            </div>
                        </div>

                        

                        <div class="mb-3">
                            <label for="reported_issue" class="form-label">Defeito Relatado</label>
                            <textarea class="form-control" id="reported_issue" name="reported_issue" 
                                      required><?php echo htmlspecialchars($order['reported_issue']); ?></textarea>
                        </div>

                        
                    </div>

                    <div class="btn-toolbar justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar Alterações
                        </button>
                        <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-eye"></i> Ver Ordem
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>