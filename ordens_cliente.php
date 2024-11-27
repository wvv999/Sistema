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
    
    // Busca todas as ordens de serviço do cliente
    $query = "SELECT so.*, c.name as client_name 
              FROM service_orders so
              INNER JOIN clients c ON so.client_id = c.id
              WHERE so.client_id = ? 
              ORDER BY so.created_at DESC";
              
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
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .order-count {
            background-color: #f8f9fa;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
        }

        /* Status styles */
        .status-indicator {
            min-width: 140px;
            text-align: center;
            font-size: 0.85em;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .status-indicator i {
            font-size: 0.9em;
        }

        .não-iniciada {background: #e74c3c; color: white;}
        .em-andamento {background: #f39c12; color: white;}
        .concluída {background: #27ae60; color: white;}
        .pronto-e-avisado {background: #3498db; color: white;}
        .entregue {background: #2c3e50; color: white;}

        .device-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .device-model {
            font-weight: 500;
        }

        .issue-text {
            font-size: 0.85em;
            color: #666;
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .date-badge {
            background-color: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            color: #666;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .order-row {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .order-row:hover {
            background-color: #f8f9fa !important;
        }

        .status-cell {
            min-width: 160px;
        }

        .phone-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .phone-badge {
            background-color: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            color: #666;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .issue-text {
                max-width: 150px;
            }
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
                                <th>OS</th>
                                <th>Dispositivo / Problema</th>
                                <th>Telefones</th>
                                <th>Data Entrada</th>
                                <th>Data Entrega</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): 
                                $status = $order['status'] ?? 'não iniciada';
                                $statusButton = OrderStatus::getStatusButton($status);
                            ?>
                                <tr class="order-row" onclick="window.location='view_order.php?id=<?= $order['id'] ?>'">
                                    <td><code class="fs-6"><?= str_pad($order['id'], STR_PAD_LEFT) ?></code></td>
                                    <td>
                                        <div class="device-info">
                                            <span class="device-model"><?= htmlspecialchars($order['device_model']) ?></span>
                                            <span class="issue-text"><?= htmlspecialchars($order['reported_issue']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="phone-info">
                                            <span class="phone-badge">
                                                <i class="bi bi-telephone"></i>
                                                <?= htmlspecialchars($order['phone1']) ?>
                                            </span>
                                            <?php if ($order['phone2']): ?>
                                            <span class="phone-badge">
                                                <i class="bi bi-telephone"></i>
                                                <?= htmlspecialchars($order['phone2']) ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="date-badge">
                                            <i class="bi bi-calendar-check"></i>
                                            <?= date('d/m/Y', strtotime($order['created_at'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="date-badge">
                                            <i class="bi bi-calendar-event"></i>
                                            <?= date('d/m/Y', strtotime($order['delivery_date'])) ?>
                                        </span>
                                    </td>
                                    <td class="status-cell"><?= $statusButton ?></td>
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