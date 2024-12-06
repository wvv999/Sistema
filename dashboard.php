<?php
session_start();

if(!isset($_SESSION['current_sector'])) {
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
            color: #2c3e50 !important;
            border-color: #2c3e50 !important;
        }

        .nav-button i { margin-right: 10px; }

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
        
        .não-iniciada {background: #e74c3c;}
        .em-andamento {background: #f39c12;}
        .concluída {background: #27ae60;}
        .pronto-e-avisado {background: #3498db;}
        .entregue {background: #2c3e50;}

        .notification-section {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            border: 1px solid rgba(0,0,0,0.1);
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
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
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
    </style>
</head>
<body class="bg-light">
    <a href="logout.php" class="btn btn-outline-danger logout-btn">
        <i class="bi bi-box-arrow-right"></i> Sair
    </a>

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
                    <li class="px-3 py-2">

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
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
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
                                $orderNumber = htmlspecialchars($order['id'], STR_PAD_LEFT);
                                $clientName = htmlspecialchars($order['client_name']);
                                $device_model = htmlspecialchars(mb_strimwidth($order['device_model'], 0, 50, "..."));
                                $issue = htmlspecialchars(mb_strimwidth($order['reported_issue'], 0, 50, "..."));
                                $createdAt = (new DateTime($order['created_at']))->format('d/m/Y');
                                $status = $order['status'] ?? 'não iniciada';
                                $statusButton = OrderStatus::getStatusButton($status);
                                
                                echo <<<HTML
                                <li class="list-group-item" onclick="window.location='view_order.php?id={$order['id']}'">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <code>{$orderNumber}</code> - {$device_model} - <small>{$issue}</small>
                                            <small class="text-muted d-block">Cliente: {$clientName}</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <small class="text-muted">{$createdAt}</small>
                                            {$statusButton}
                                            <!-- <button class="btn btn-sm btn-outline-primary btn-view-order" onclick="event.stopPropagation(); window.location='view_order.php?id={$order['id']}'">
                                                <i class="bi bi-eye"></i> Ver
                                            </button> -->
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
    <!-- Antes do fechamento do </body> no dashboard.php -->
    <div id="notification-container"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Cache de elementos DOM
    const elements = {
        searchInput: document.getElementById('searchInput'),
        searchButton: document.getElementById('searchButton'),
        sectorInputs: document.querySelectorAll('input[name="sector"]'),
        notifyButton: document.getElementById('notifyButton'),
        notificationContainer: document.getElementById('notification-container')
    };

    // Configuração do som
    let notificationSound = null;
    function initSound() {
        notificationSound = new Audio('assets/som.mp3');
        // Pré-carrega o som ao iniciar a página
        notificationSound.load();
        // Tenta reproduzir e silenciar imediatamente para "ativar" o áudio
        notificationSound.volume = 0;
        notificationSound.play().then(() => {
            notificationSound.pause();
            notificationSound.volume = 1;
        }).catch(console.error);
    }

    // Gerenciamento de estado das notificações
    const notificationState = {
        processedIds: new Set(JSON.parse(localStorage.getItem('processedNotifications') || '[]')),
        activeNotifications: new Map()
    };

    // Configurações
    const NOTIFICATION_TIMEOUT = 5000;
    const CHECK_INTERVAL = 1000;

    // Funções de gerenciamento de estado
    function saveProcessedNotifications() {
        localStorage.setItem('processedNotifications', 
            JSON.stringify(Array.from(notificationState.processedIds)));
    }

    function clearOldProcessedNotifications() {
        const oneHourAgo = Date.now() - (60 * 60 * 1000);
        const processedArray = Array.from(notificationState.processedIds);
        const recentNotifications = processedArray.filter(id => {
            const timestamp = parseInt(id.split('-')[1]);
            return !isNaN(timestamp) && timestamp > oneHourAgo;
        });
        notificationState.processedIds = new Set(recentNotifications);
        saveProcessedNotifications();
    }

    // Sistema de notificações toast
    function showToast({ message, type = 'success', title = null, orderId = null, notificationId = null, permanent = false }) {
        // Cria um ID único para a notificação com timestamp
        const toastId = notificationId || `notification-${Date.now()}-${Math.random()}`;

        // Verifica se já existe uma notificação ativa com este ID
        if (notificationState.activeNotifications.has(toastId)) {
            return;
        }

        const toast = document.createElement('div');
        toast.className = 'notification-persistent';
        toast.id = `toast-${toastId}`;

        const buttonHtml = orderId ? `
            <button onclick="openOrder('${orderId}', '${toastId}')" 
                    class="btn btn-primary btn-sm mt-2 w-100">
                <i class="bi bi-eye"></i> Visualizar OS
            </button>
        ` : '';

        toast.innerHTML = `
            <div class="toast-header bg-${type} text-white">
                <strong class="me-auto">${title || (type === 'success' ? 'Sucesso' : 'Notificação')}</strong>
                <button type="button" class="btn-close btn-close-white" 
                        onclick="removeNotification('${toastId}')"></button>
            </div>
            <div class="toast-body">
                ${message}
                ${buttonHtml}
            </div>
        `;

        // Adiciona ao gerenciamento de estado
        notificationState.activeNotifications.set(toastId, {
            element: toast,
            timestamp: Date.now()
        });

        document.body.appendChild(toast);
        playNotificationSound();

        if (!permanent) {
            setTimeout(() => removeNotification(toastId), NOTIFICATION_TIMEOUT);
        }

        return toastId;
    }

    // Função global para remover notificação
    window.removeNotification = function(toastId) {
        const notification = notificationState.activeNotifications.get(toastId);
        if (notification) {
            notification.element.remove();
            notificationState.activeNotifications.delete(toastId);
        }
    };

    // Função global para abrir ordem
    window.openOrder = function(orderId, toastId) {
        removeNotification(toastId);
        if (orderId) {
            window.location.href = `view_order.php?id=${orderId}`;
        }
    };

    function playNotificationSound() {
        if (notificationSound) {
            notificationSound.currentTime = 0;
            notificationSound.play().catch(console.error);
        }
    }

    // Sistema de Notificações
    async function checkAllNotifications() {
        const currentSector = document.querySelector('input[name="sector"]:checked')?.value;
        if (!currentSector) return;

        try {
            const [notificationsResponse, authResponse] = await Promise.all([
                fetch('check_notifications.php'),
                fetch('check_auth_notifications.php')
            ]);

            const [notificationsData, authData] = await Promise.all([
                notificationsResponse.json(),
                authResponse.json()
            ]);

            processNotifications(notificationsData, currentSector);
            processAuthNotifications(authData);

        } catch (error) {
            console.error('Erro ao verificar notificações:', error);
        }
    }

    function processNotifications(data, currentSector) {
        if (!data.success || !data.hasNotification) return;

        const notification = data.notification;
        const notificationId = `${notification.type}-${Date.now()}-${notification.id || ''}`;

        // Verifica se esta notificação já foi processada
        if (notificationState.processedIds.has(notificationId)) return;

        // Adiciona ao conjunto de notificações processadas
        notificationState.processedIds.add(notificationId);
        saveProcessedNotifications();

        if (notification.type === currentSector) {
            showToast({
                message: `
                    <div class="d-flex align-items-center">
                        <i class="bi bi-bell me-2"></i>
                        <span>Chamada do setor ${notification.type === 'tecnica' ? 'Técnico' : 'Atendimento'}</span>
                    </div>
                    <small class="text-muted">De: ${notification.from_username}</small>
                `,
                type: 'primary',
                title: 'Nova Chamada',
                notificationId
            });
        } else if (notification.type === 'auth_status_change') {
            showToast({
                message: `OS #${notification.order_id}: ${notification.message}`,
                type: 'warning',
                title: 'Alteração de Autorização',
                orderId: notification.order_id,
                notificationId,
                permanent: true
            });
        }
    }

    function processAuthNotifications(data) {
        if (!data.success || !data.hasNotification) return;

        data.notifications.forEach(notification => {
            const notificationId = `auth-${Date.now()}-${notification.id || ''}`;

            if (notificationState.processedIds.has(notificationId)) return;

            notificationState.processedIds.add(notificationId);
            saveProcessedNotifications();

            showToast({
                message: `OS #${notification.order_id}: ${notification.message}`,
                type: 'warning',
                title: 'Alteração de Autorização',
                orderId: notification.order_id,
                notificationId,
                permanent: true
            });
        });
    }

    // Inicialização
    function initialize() {
        initSound();
        initializeTooltips();
        initializeSectorSelection();
        initializeSearchSystem();
        setInterval(checkAllNotifications, CHECK_INTERVAL);
        setInterval(clearOldProcessedNotifications, 60 * 60 * 1000); // Limpa a cada hora
        addSoundTestButton();
    }

    // [Resto do código permanece igual: initializeTooltips, initializeSectorSelection, 
    // initializeSearchSystem, handleSectorChange, updateNotifyButtonText, handleSearch, 
    // handleSectorNotification, addSoundTestButton]

    // Inicializa a aplicação
    initialize();
});
    </script>
</body>
</html>