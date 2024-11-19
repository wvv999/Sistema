<?php
session_start();
require_once 'config.php';

// Verifica se está logado
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Verifica se o ID do cliente foi fornecido
if(!isset($_GET['id'])) {
    header("Location: clientes.php");
    exit;
}

$client_id = $_GET['id'];
$client_name = '';
$orders = [];
$error = '';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Busca informações do cliente
    $stmt = $db->prepare("SELECT name FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();
    
    if (!$client) {
        throw new Exception("Cliente não encontrado!");
    }
    
    $client_name = $client['name'];
    
    // Busca todas as ordens do cliente
    $query = "SELECT orders.*, 
                     services.name as service_name,
                     status.name as status_name,
                     status.color as status_color
              FROM orders 
              LEFT JOIN services ON orders.service_id = services.id
              LEFT JOIN status ON orders.status_id = status.id
              WHERE orders.client_id = ? 
              ORDER BY orders.created_at DESC";
              
    $stmt = $db->prepare($query);
    $stmt->execute([$client_id]);
    $orders = $stmt->fetchAll();
    
} catch(Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordens de Serviço - <?php echo htmlspecialchars($client_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
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

        .order-count {
            background-color: #f8f9fa;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.875rem;
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-light">
    <a href="clientes.php" class="btn btn-outline-primary" style="position: absolute; top: 20px; left: 20px;">
        <i class="bi bi-arrow-left"></i> Voltar para Clientes
    </a>

    <div class="container">
        <div class="content-container">
            <div class="header-container">
                <h2>
                    <i class="bi bi-person-circle"></i> 
                    <?php echo htmlspecialchars($client_name); ?>
                </h2>
                <div class="order-count">
                    Total de ordens: <?php echo count($orders); ?>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <div class="alert alert-info">
                    Nenhuma ordem de serviço encontrada para este cliente.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nº da Ordem</th>
                                <th>Serviço</th>
                                <th>Status</th>
                                <th>Data de Criação</th>
                                <th>Valor Total</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                                    <td>
                                        <span class="status-badge" style="background-color: <?php echo htmlspecialchars($order['status_color']); ?>">
                                            <?php echo htmlspecialchars($order['status_name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar-event me-2"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td>
                                        R$ <?php echo number_format($order['total_value'], 2, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <a href="ver_ordem.php?id=<?php echo $order['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Ver Detalhes
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>