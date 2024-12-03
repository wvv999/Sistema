<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if(!isset($_SESSION['current_sector'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT current_sector FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['current_sector'] = $stmt->fetchColumn() ?? 'atendimento';
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
        .container { 
            padding-top: 2rem; 
            padding-bottom: 2rem; 
        }
        
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
            color: #2c3e50 !important;
            border-color: #2c3e50 !important;
            transition: all 0.3s ease;
        }

        .nav-button i { 
            margin-right: 10px; 
        }

        .nav-button:hover {
            background-color: #2c3e50 !important;
            color: white !important;
            transform: translateY(-2px);
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
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
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

        .não-iniciada {background: #e74c3c; color: white;}
        .em-andamento {background: #f39c12; color: white;}
        .concluída {background: #27ae60; color: white;}
        .pronto-e-avisado {background: #3498db; color: white;}
        .entregue {background: #2c3e50; color: white;}

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

        .notification-persistent {
            position: fixed;
            bottom: 20px;
            right: 20px;
            min-width: 300px;
            z-index: 1060;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
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

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .notification-toast {
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease-in-out;
        }

        .notification-toast.show {
            opacity: 1;
            transform: translateX(0);
        }

        .notification-toast.hiding {
            opacity: 0;
            transform: translateX(100%);
        }

        .auth-notification {
            border-left: 4px solid #ffc107;
        }

        .auth-approved-notification {
            border-left: 4px solid #198754;
        }
    </style>
</head>
<body class="bg-light">
    <a href="logout.php" class="btn btn-outline-danger logout-btn">
        <i class="bi bi-box-arrow-right"></i> Sair
    </a>

    <div class="container">
        <div class="dashboard-container">
            <div class="welcome-header">
                <h2><i class="bi bi-grid-1x2"></i> Sistema Interno</h2>
                <div class="dropdown">
                    <div class="user-info" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                        <i class="bi bi-chevron-down ms-1"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-3 py-2">
                            <div class="sector-selection">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="sector" id="atendimento" value="atendimento"
                                        <?php echo ($_SESSION['current_sector'] === 'atendimento') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="atendimento">Atendimento</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="sector" id="tecnica" value="tecnica"
                                        <?php echo ($_SESSION['current_sector'] === 'tecnica') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="tecnica">Técnica</label>
                                </div>
                            </div>
                            <button id="notifyButton" class="btn btn-warning btn-sm w-100 mt-2">
                                <i class="bi bi-bell"></i> Chamar Setor
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="search-container">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" 
                           placeholder="Digite o número da OS ou nome do cliente...">
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
                                $orderNumber = str_pad($order['id'], STR_PAD_LEFT);
                                $clientName = htmlspecialchars($order['client_name']);
                                $device_model = htmlspecialchars(mb_strimwidth($order['device_model'], 0, 50, "..."));
                                $issue = htmlspecialchars(mb_strimwidth($order['reported_issue'], 0, 50, "..."));
                                $createdAt = date('d/m/Y', strtotime($order['created_at']));
                                $status = $order['status'] ?? 'não iniciada';
                                $statusButton = OrderStatus::getStatusButton($status);
                                
                                echo <<<HTML
                                <li class="list-group-item" onclick="window.location='view_order.php?id={$order['id']}'">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <code>{$orderNumber}</code> - {$device_model}
                                            <small class="text-muted d-block">Cliente: {$clientName}</small>
                                            <small class="text-muted">{$issue}</small>
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
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Container para notificações -->
    <div id="notificationContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sons para diferentes tipos de notificação
        const notificationSound = new Audio('assets/som.mp3'); // Som para chamar setor
        const authNotificationSound = new Audio('assets/notification.mp3'); // Som para notificações de autorização
        
        // Função para criar notificação de autorização
        function createNotification(notification) {
            const container = document.getElementById('notificationContainer');
            const toast = document.createElement('div');
            
            const isAuthStatus = notification.type === 'auth_status';
            const statusClass = isAuthStatus ? 'auth-notification' : 'auth-approved-notification';
            const icon = isAuthStatus ? 'exclamation-triangle' : 'check-circle';
            const title = isAuthStatus ? 'Autorização pendente' : 'Autorizado!';
            const bgClass = isAuthStatus ? 'bg-warning' : 'bg-success';
            
            // Tocar som específico para notificações de autorização
            authNotificationSound.currentTime = 0;
            authNotificationSound.play().catch(e => console.log('Erro ao tocar som:', e));
            
            toast.className = `notification-toast ${statusClass} card shadow`;
            toast.innerHTML = `
                <div class="card-header ${bgClass} bg-opacity-10 d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-${icon} me-2"></i>
                        <strong>Ordem #${notification.order_id}</strong>
                    </div>
                    <button type="button" class="btn-close btn-sm" onclick="closeNotification(this.parentElement.parentElement)"></button>
                </div>
                <div class="card-body">
                    <p class="mb-2">${title}</p>
                    <button class="btn btn-primary btn-sm w-100" onclick="window.location.href='view_order.php?id=${notification.order_id}'">
                        <i class="bi bi-eye me-2"></i>Ver Ordem
                    </button>
                </div>
            `;

            container.appendChild(toast);
            requestAnimationFrame(() => toast.classList.add('show'));
            setTimeout(() => closeNotification(toast), 10000);
        }

        function closeNotification(toast) {
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 300);
        }

        // Função para mostrar toast
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `notification-toast ${type === 'success' ? 'border-success' : 'border-danger'} show`;
            toast.innerHTML = `
                <div class="card-header bg-${type} bg-opacity-10">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                    ${message}
                </div>
            `;
            
            document.getElementById('notificationContainer').appendChild(toast);
            setTimeout(() => closeNotification(toast), 3000);
        }

        // Atualização do setor
        const sectorInputs = document.querySelectorAll('input[name="sector"]');
        const notifyButton = document.getElementById('notifyButton');
        
        sectorInputs.forEach(input => {
            input.addEventListener('change', async function() {
                try {
                    const response = await fetch('update_user_sector.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ sector: this.value })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        showToast('Setor atualizado com sucesso!', 'success');
                        const targetSector = this.value === 'atendimento' ? 'Técnica' : 'Atendimento';
                        notifyButton.innerHTML = `<i class="bi bi-bell"></i> Chamar ${targetSector}`;
                        notifyButton.disabled = false;
                    }
                } catch (error) {
                    console.error('Erro ao atualizar setor:', error);
                    showToast('Erro ao atualizar setor', 'error');
                }
            });
        });

        // Chamar setor (função original)
        notifyButton.addEventListener('click', async function() {
            const selectedInput = document.querySelector('input[name="sector"]:checked');
            
            if (!selectedInput) {
                showToast('Selecione um setor primeiro', 'error');
                return;
            }

            const currentSector = selectedInput.value;
            const targetSector = currentSector === 'atendimento' ? 'tecnica' : 'atendimento';
        
            try {
                const response = await fetch('send_notification.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        sector: targetSector,
                        from_user: <?php echo $_SESSION['user_id']; ?>
                    })
                });

                const data = await response.json();
                if (data.success) {
  
                    showToast('Chamada enviada', 'success');
                }
            } catch (error) {
                console.error('Erro ao enviar notificação:', error);
                showToast('Erro ao enviar notificação', 'error');
            }
        });

        // Sistema de busca
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');

        async function searchOrder() {
            const searchValue = searchInput.value.trim();
            if (!searchValue) {
                showToast('Digite um número de OS ou nome do cliente', 'error');
                return;
            }

            try {
                searchButton.disabled = true;
                searchButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Buscando...';

                const response = await fetch(`search_order.php?search=${encodeURIComponent(searchValue)}`);
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Erro ao buscar ordem');
                }

                if (data.success && data.data.length > 0) {
                    const order = data.data[0];
                    window.location.href = `view_order.php?id=${order.id}`;
                } else {
                    showToast('Nenhuma ordem encontrada', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao buscar ordem: ' + error.message, 'error');
            } finally {
                searchButton.disabled = false;
                searchButton.innerHTML = '<i class="bi bi-search"></i> Buscar';
            }
        }

        // Event listeners para busca
        searchButton.addEventListener('click', searchOrder);
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') searchOrder();
        });

        // Verificar notificações a cada 5 segundos
        setInterval(async function checkNotifications() {
            try {
                const response = await fetch('check_notifications.php');
                const data = await response.json();
                
                if (data.success && data.hasNotification) {
                    const notification = data.notification;
                    
                    if (notification.type === 'auth_status' || notification.type === 'auth_approved') {
                        // Som e notificação apenas quando recebe
                        authNotificationSound.currentTime = 0;
                        authNotificationSound.play().catch(e => console.log('Erro ao tocar som:', e));
                        createNotification(notification);
                    } else if (notification.type === document.querySelector('input[name="sector"]:checked')?.value) {
                        // Chamada de setor (sistema original)
                        notificationSound.currentTime = 0;
                        notificationSound.play().catch(console.error);
                        showToast(`Chamada do setor ${notification.from_username}`, 'info');
                    }
                }
            } catch (error) {
                console.error('Erro ao verificar notificações:', error);
            }
        }, 5000);

        // Inicialização
        const currentSector = document.querySelector('input[name="sector"]:checked')?.value;
        if (currentSector) {
            const targetSector = currentSector === 'atendimento' ? 'Técnica' : 'Atendimento';
            notifyButton.innerHTML = `<i class="bi bi-bell"></i> Chamar ${targetSector}`;
        } else {
            notifyButton.disabled = true;
        }
    });
    </script>
</body>
</html>