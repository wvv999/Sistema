<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .container { padding-top: 2rem; padding-bottom: 2rem; }
        
        .dashboard-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }

        .welcome-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .nav-button {
            margin-bottom: 15px;
            padding: 15px;
            text-align: left;
            font-size: 1.1em;
        }

        .nav-button i { margin-right: 10px; }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .user-info {
            background-color: #f8f9fa;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
        }

        .search-container {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .recent-orders-list .list-group-item {
            transition: all 0.2s ease;
            cursor: pointer;
            border-left: 4px solid transparent;
        }

        .recent-orders-list .list-group-item:hover {
            background-color: #f8f9fa;
            border-left-color: #0d6efd;
        }

        .clickable-order {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .clickable-order:hover {
            color: inherit;
        }

        /* Removido o opacity para manter o botão sempre visível */
        .btn-view-order {
            transition: transform 0.2s ease;
        }

        .btn-view-order:hover {
            transform: scale(1.05);
        }

        .list-group-item {
            border-left: 4px solid transparent;
            transition: border-left-color 0.2s ease;
        }

        .list-group-item:hover {
            border-left-color: #0d6efd;
        }
    </style>
</head>
<body class="bg-light">
    <!-- ... Resto do código permanece igual até a seção de ordens recentes ... -->

            <div class="mt-4 p-3 bg-light rounded">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Ordens de Serviço Recentes
                    </h5>
                </div>
                <ul class="list-group recent-orders-list">
                    <?php
                    require_once 'recent_orders.php';
                    $recentOrders = new RecentOrders();
                    $orders = $recentOrders->getRecentOrders(5);
                    
                    foreach ($orders as $order) {
                        $orderNumber = str_pad($order['id'], 5, '0', STR_PAD_LEFT);
                        $issue = htmlspecialchars(mb_strimwidth($order['reported_issue'], 0, 50, "..."));
                        $clientName = htmlspecialchars($order['client_name']);
                        $createdAt = (new DateTime($order['created_at']))->format('d/m/Y H:i');
                        
                        echo <<<HTML
                        <li class="list-group-item" onclick="window.location='view_order.php?id={$order['id']}'">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <code>{$orderNumber}</code> - {$issue}
                                    <small class="text-muted d-block">Cliente: {$clientName}</small>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <small class="text-muted">{$createdAt}</small>
                                    <button class="btn btn-sm btn-outline-primary btn-view-order" onclick="event.stopPropagation(); window.location='view_order.php?id={$order['id']}'">
                                        <i class="bi bi-eye"></i> Ver
                                    </button>
                                </div>
                            </div>
                        </li>
                        HTML;
                    }

                    if (empty($orders)) {
                        echo '<li class="list-group-item">Nenhuma ordem de serviço recente encontrada.</li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- ... Resto do código do script permanece igual ... -->
</body>
</html>