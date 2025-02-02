<?php
session_start();



if (!isset($_SESSION['current_sector'])) {
    // Se não existir na sessão, busca do banco
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT current_sector FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['current_sector'] = $stmt->fetchColumn() ?? 'atendimento';
}

// Debug - remover depois
error_log('Current Sector: ' . $_SESSION['current_sector']);
if (!isset($_SESSION['user_id'])) {
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
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        body {
            padding: 20px;
        }

        body::-webkit-scrollbar {
            display: none;
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
            color: #2c3e50 !important;
            border-color: #2c3e50 !important;
        }

        .nav-button i {
            margin-right: 10px;
        }

        .nav-button:hover {
            background-color: #2c3e50 !important;
            color: white !important;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }


        .search-container {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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

        .btn-view-order {
            transition: transform 0.2s ease;
        }

        .list-group-item {
            border-left: 4px solid transparent;
            transition: border-left-color 0.2s ease;
        }

        .list-group-item:hover {
            border-left-color: #0d6efd;
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

        .não-iniciada {
            background: #e74c3c;
            color: white
        }

        .em-andamento {
            background: #f39c12;
            color: white
        }

        .concluída {
            background: #27ae60;
            color: white
        }

        .pronto-e-avisado {
            background: #3498db;
            color: white
        }

        .entregue {
            background: #2c3e50;
            color: white
        }

        .notification-section {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }


        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }


        .dropdown-header {
            font-weight: bold;
            color: #6c757d;
        }

        .notification-persistent {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 300px;
            z-index: 1060;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .notification-persistent .toast-header {
            border-radius: 8px 8px 0 0;
            padding: 12px;
        }

        .notification-persistent .toast-body {
            padding: 15px;
        }

        .notification-persistent .btn {
            width: 100%;
        }

        .user-info {
            background-color: #f8f9fa;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .user-info:hover {
            background-color: #e9ecef;
        }

        .dropdown-menu {
            padding: 0.5rem;
            min-width: 200px;
        }

        .sector-selection {
            white-space: nowrap;
        }

        .notification-persistent {
            position: fixed;
            bottom: 20px;
            right: 20px;
            min-width: 300px;
            z-index: 1060;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease;
        }

        .notification-persistent .toast-header {
            padding: 12px;
            border-radius: 8px 8px 0 0;
        }

        .notification-persistent .toast-body {
            padding: 15px;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        nume-ordem {
            color: red;
            font-weight: bold;
        }

        modelo {
            font-weight: bold;
        }

        /* @media only screen and (max-width: 1200px) {
        .dashboard-container {
            margin-top: 100px;
        }
        } */
    </style>
</head>

<body class="bg-light">

    <!-- <a href="logout.php" class="btn btn-outline-danger logout-btn">
        <i class="bi bi-box-arrow-right"></i> Sair
    </a> -->

    <div class="container">
        <div class="dashboard-container">
            <div class="welcome-header">
                <h2><i class="bi bi-grid-1x2"></i> Sistema Interno Tele Dil</h2>


                <div class="dropdown">
                    <div class="user-info" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                        <i class="bi bi-chevron-down ms-1"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <!-- <li class="px-3 py-2">

                        <div class="sector-selection">


                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="sector" id="atendimento" value="atendimento">
                            <label class="form-check-label" for="atendimento">Atendimento</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="sector" id="tecnica" value="tecnica">
                            <label class="form-check-label" for="tecnica">Técnica</label>
                        </div>
                        </div>


                        <button id="notifyButton" class="btn btn-warning btn-sm w-100 mt-2">
                            <i class="bi bi-bell"></i> Chamar Setor
                        </button>
                    </li> -->
                        <!-- <li><hr class="dropdown-divider"></li> -->
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
                    </ul>
                </div>
            </div>


            <div class="search-container">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput"
                        placeholder="Número da OS, nome do cliente, modelo ou defeito">
                    <button class="btn btn-primary" type="button" id="searchButton">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <a href="service_order.php" class="btn btn-outline-success w-100 nav-button">
                        <i class="bi bi-file-earmark-text"></i>
                        Nova Ordem de Serviço
                    </a>
                </div>

                <div class="col-md-6">
                    <a href="users.php" class="btn btn-outline-info w-100 nav-button">
                        <i class="bi bi-person-plus"></i>
                        Usuários
                    </a>
                </div>

                <div class="col-md-6">
                    <a href="clientes.php" class="btn btn-outline-success w-100 nav-button">
                        <i class="bi bi-person-lines-fill"></i>
                        Cadastro de Clientes
                    </a>
                </div>

                <div class="col-md-6">
                    <a href="gestao.php" class="btn btn-outline-info w-100 nav-button">
                        <i class="bi bi-gear stats-icon"></i>
                        Gestão
                    </a>
                </div>

            </div>

            <div class="mt-4 p-3 bg-light rounded">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Ordens de Serviço Recentes
                    </h5>
                </div>
                <ul class="list-group recent-orders-list">
                    <?php
                    require_once 'recent_orders.php';
                    require_once 'orderStatus.php';

                    try {
                        $recentOrders = new RecentOrders();
                        $orders = $recentOrders->getRecentOrders(5);

                        if (empty($orders)) {
                            echo '<li class="list-group-item">Nenhuma ordem de serviço recente encontrada.</li>';
                        } else {
                            foreach ($orders as $order) {
                                $orderNumber = htmlspecialchars($order['id'], STR_PAD_LEFT);
                                $clientName = htmlspecialchars($order['client_name']);
                                $device_model = htmlspecialchars(mb_strimwidth($order['device_model'], 0, 50, "..."));
                                $issue = htmlspecialchars(mb_strimwidth($order['reported_issue'], 0, 50, "..."));
                                $createdAt = (new DateTime($order['created_at']))->format("H:i");
                                $status = $order['status'] ?? 'não iniciada';
                                $statusButton = OrderStatus::getStatusButton($status);

                                echo <<<HTML
                                <li class="list-group-item" onclick="window.location='view_order.php?id={$order['id']}'">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <nume-ordem>{$orderNumber}</nume-ordem> - <modelo>{$device_model}</modelo> - <small>{$issue}</small>
                                            <small class="text-muted d-block">Cliente: {$clientName}</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <small class="text-muted">{$createdAt}</small>
                                            {$statusButton}

                                        </div>
                                    </div>
                                </li>
                                HTML;
                            }
                        }
                    } catch (Exception $e) {
                        echo '<li class="list-group-item text-danger">Erro ao carregar ordens de serviço: ' . htmlspecialchars($e->getMessage()) . '</li>';
                        error_log("Erro na dashboard: " . $e->getMessage());
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>





    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Primeiro vamos verificar se conseguimos pegar os elementos
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');

            console.log('Input encontrado:', searchInput);
            console.log('Botão encontrado:', searchButton);

            // Função de busca com logs para debug
            async function searchOrder() {
                console.log('Função searchOrder iniciada');

                const searchValue = searchInput.value.trim();
                console.log('Valor da busca:', searchValue);

                if (!searchValue) {
                    alert('Por favor, digite um número de OS ou nome do cliente');
                    return;
                }

                try {
                    console.log('Iniciando fetch para:', `search_order.php?search=${encodeURIComponent(searchValue)}`);

                    const response = await fetch(`search_order.php?search=${encodeURIComponent(searchValue)}`);
                    const data = await response.json();

                    console.log('Resposta da busca:', data);

                    if (data.success) {
                        if (data.data.length === 1 && !isNaN(searchValue)) {
                            console.log('Redirecionando para view_order.php');
                            window.location.href = `view_order.php?id=${data.data[0].id}`;
                        } else {
                            console.log('Redirecionando para consulta_ordens.php');
                            window.location.href = `consulta_ordens.php?search=${encodeURIComponent(searchValue)}`;
                        }
                    } else {
                        console.log('Nenhum resultado encontrado');
                        window.location.href = `consulta_ordens.php?search=${encodeURIComponent(searchValue)}`;
                    }
                } catch (error) {
                    console.error('Erro na busca:', error);
                    alert('Erro ao realizar a busca');
                }
            }

            // Registrando eventos com confirmação no console
            if (searchButton) {
                console.log('Adicionando evento de click ao botão');
                searchButton.addEventListener('click', () => {
                    console.log('Botão foi clicado!');
                    searchOrder();
                });
            }

            if (searchInput) {
                console.log('Adicionando evento de keydown ao input');
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        console.log('Enter pressionado!');
                        e.preventDefault();
                        searchOrder();
                    }
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>