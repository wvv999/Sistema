<?php
// Ativar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Verifica se está logado
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Inclui o arquivo de configuração do banco de dados e o arquivo de status
require_once 'config.php';
require_once 'orderStatus.php';

// Pegar o termo de busca da URL se existir
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Envolva o código da conexão e consulta em um try-catch para debug
try {
    // Cria uma nova instância da classe Database e obtém a conexão
    $db = new Database();
    $conn = $db->getConnection();

    // Prepara a consulta base
    $sql = "SELECT so.id, so.client_id, so.phone1, so.phone2, 
                   so.created_at AS opening_date, so.delivery_date, 
                   so.reported_issue, so.accessories, 
                   so.device_password, so.pattern_password,
                   so.device_model,
                   c.name AS client_name, so.status
            FROM service_orders so
            JOIN clients c ON so.client_id = c.id";

    $params = [];
    
    // Adiciona condição de busca se houver termo de busca
    if (!empty($searchTerm)) {
        $sql .= " WHERE (c.name LIKE ? OR so.id LIKE ? OR so.device_model LIKE ? OR so.reported_issue LIKE ?)";
        $searchPattern = "%$searchTerm%";
        $params = [$searchPattern, $searchPattern, $searchPattern, $searchPattern];
    }
    
    // Adiciona ordenação
    $sql .= " ORDER BY so.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $serviceOrders = $stmt->fetchAll();

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
    die();
}

// Se houver um termo de busca, prepara o script para preencher o campo de pesquisa
if (!empty($searchTerm)) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('orderSearch');
            if (searchInput) {
                searchInput.value = '" . htmlspecialchars($searchTerm) . "';
                searchInput.dispatchEvent(new Event('input'));
            }
        });
    </script>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Ordens de Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>

        html { scrollbar-width: none; } 
        
        .body{
            padding:50px;
            box-sizing: border-box;
            margin: 40px;
        }
        

        .container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .dashboard-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
            overflow: visible;
        }

        .welcome-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .table thead {
            background-color: #f8f9fa;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .view-btn {
            padding: 2px 8px;
            font-size: 0.875rem;
        }

        /* Status button styles */
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

        .order-info {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        .search-box {
            margin-bottom: 20px;
        }

        .order-count {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
        }

        .date-badge {
            background-color: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            color: #666;
        }
    </style>
</head>
<body class="bg-light">
    <a href="dashboard.php" class="btn btn-outline-primary" style="position: absolute; top: 20px; left: 20px;">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>

    <div class="container">
        <div class="dashboard-container">
            <div class="welcome-header">
                <h2><i class="bi bi-file-earmark-text"></i> Lista de Ordens de Serviço</h2>
                <div class="user-info">
                    <i class="bi bi-person-circle"></i>
                    Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?>
                </div>
            </div>

            <div class="search-box">
                <input type="text" class="form-control" id="orderSearch" placeholder="Buscar por cliente, dispositivo ou problema...">
            </div>

            <?php if (count($serviceOrders) > 0): ?>
                <div class="order-count">
                    Total de ordens: <?= count($serviceOrders) ?>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>OS</th>
                                <th>Cliente</th>
                                <th>Dispositivo / Problema</th>
                                <th>Entrada / Saída</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($serviceOrders as $order): 
                                $orderNumber = str_pad($order['id'], STR_PAD_LEFT);
                                $status = $order['status'] ?? 'não iniciada';
                                $statusButton = OrderStatus::getStatusButton($status);
                            ?>
                                <tr class="order-row" onclick="window.location='view_order.php?id=<?= $order['id'] ?>'">
                                    <td><code class="fs-6"><?= $orderNumber ?></code></td>
                                    <td class="order-info"><?= htmlspecialchars($order['client_name']) ?></td>
                                    <td>
                                        <div class="device-info">
                                            <span class="device-model"><?= htmlspecialchars($order['device_model']) ?></span>
                                            <span class="issue-text"><?= htmlspecialchars($order['reported_issue']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="date-badge">
                                                <i class="bi bi-calendar-check"></i>
                                                <?= date('d/m/Y', strtotime($order['opening_date'])) ?>
                                            </span>
                                            <?php if ($order['delivery_date']): ?>
                                                <span class="date-badge">
                                                    <i class="bi bi-calendar2-check"></i>
                                                    <?= date('d/m/Y', strtotime($order['delivery_date'])) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="status-cell"><?= $statusButton ?></td>
                                    <td>
                                        <button class="btn btn-primary view-btn" onclick="event.stopPropagation(); window.location='view_order.php?id=<?= $order['id'] ?>'">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning" role="alert">
                    Nenhuma ordem de serviço encontrada.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const searchInput = document.getElementById('orderSearch');
    
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.order-row');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    </script>
</body>
</html>