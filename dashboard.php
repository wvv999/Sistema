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

        .sector-selection {
            font-size: 1.1em;
        }

        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
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
                <div class="user-info">
                    <i class="bi bi-person-circle"></i>
                    Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?>
                </div>
            </div>
            <div class="notification-section mb-4">
                <div class="alert alert-info">
                    <div class="d-flex align-items-center justify-content-between">
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
                        <button id="notifyButton" class="btn btn-warning" disabled>
                            <i class="bi bi-bell"></i> Chamar Setor
                        </button>
                    </div>
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

                <!-- <div class="col-md-6">
                    <a href="consulta_ordens.php" class="btn btn-outline-info w-100 nav-button">
                        <i class="bi bi-people"></i>
                        Lista de Ordens
                    </a>
                </div> -->

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
                // Gerenciamento de notificações
                document.addEventListener('DOMContentLoaded', function() {
            const sectorInputs = document.querySelectorAll('input[name="sector"]');
            const notifyButton = document.getElementById('notifyButton');
            
            // Ativa/desativa o botão baseado na seleção do setor
            sectorInputs.forEach(input => {
                input.addEventListener('change', function() {
                    notifyButton.disabled = !this.checked;
                });
            });

            // Configuração do áudio de notificação
            const notificationSound = new Audio("assets/som.mp3");

            // Gerenciamento de notificações
            notifyButton.addEventListener('click', async function() {
                const selectedSector = document.querySelector('input[name="sector"]:checked').value;
                
                try {
                    console.log('Enviando notificação para:', selectedSector);
                    const response = await fetch('send_notification.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            sector: selectedSector,
                            from_user: <?php echo $_SESSION['user_id']; ?>
                        })
                    });
                    console.log('Resposta:', response);
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('Notificação enviada com sucesso!');
                    } else {
                        alert('Erro ao enviar notificação: ' + data.message);
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    alert('Erro ao enviar notificação');
                }
            });

            // Verificar notificações a cada 5 segundos
            setInterval(async function() {
                try {
                    const response = await fetch('check_notifications.php');
                    const data = await response.json();
                    
                    if (data.success && data.hasNotification) {
                        notificationSound.play();
                        // Mostrar notificação na tela
                        showNotification(data.notification);
                    }
                } catch (error) {
                    console.error('Erro ao verificar notificações:', error);
                }
            }, 5000);
        });

        function showNotification(notification) {
            const toast = document.createElement('div');
            toast.className = 'toast show';
            toast.innerHTML = `
                <div class="toast-header">
                    <strong class="me-auto">Nova Chamada</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    Você tem uma nova chamada do setor ${notification.type}
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');

        async function searchOrder() {
            const searchValue = searchInput.value.trim();
            if (!searchValue) {
                alert('Por favor, digite um número de OS ou nome do cliente');
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
                    if (data.data.length === 1) {
                        // Se houver apenas um resultado, vai direto para a ordem
                        window.location.href = `view_order.php?id=${data.data[0].id}`;
                    } else {
                        // Se houver múltiplos resultados, redireciona para consulta_ordens.php com o termo de busca
                        window.location.href = `consulta_ordens.php?search=${encodeURIComponent(searchValue)}`;
                    }
                } else {
                    alert('Nenhuma ordem encontrada com os critérios informados');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao buscar ordem: ' + error.message);
            } finally {
                searchButton.disabled = false;
                searchButton.innerHTML = '<i class="bi bi-search"></i> Buscar';
            }
        }

        searchButton.addEventListener('click', searchOrder);
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') searchOrder();
        });

    </script>
</body>
</html>