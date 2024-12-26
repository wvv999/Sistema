<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config.php';
require_once 'GestaoStats.php';
require_once 'functions.php';

// Busca estatísticas iniciais
try {
    $gestao = new GestaoStats();
    $stats = $gestao->getOrderStats();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão - Tele Dil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        body::-webkit-scrollbar{
            display: none;
        }
        :root {
            --primary-color: #4a6fff;
            --secondary-color: #f8f9fa;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --border-radius: 8px;
        }

        .container { 
            padding-top: 2rem; 
            padding-bottom: 2rem; 
        }
        
        .stats-card {
            transition: all 0.3s ease;
            cursor: pointer;
            margin-bottom: 1rem;
        }
        
        .carde:nth-last-child(2){
            
            background-color: purple;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        .filter-section {
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .order-list {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }

        /* Status colors */
        .não-iniciada { background: #e74c3c; }
        .em-andamento { background: #f39c12; }
        .concluída { background: #27ae60; }
        .pronto-e-avisado { background: #3498db; }
        .entregue { background: #2c3e50; }

        /* Activities Section */
        .activities-section {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 1.5rem;
        }

        .activity-item {
            border: none;
            border-left: 3px solid transparent;
            margin-bottom: 0.5rem;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }

        .activity-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 1rem;
        }

        .activity-info {
            flex: 1;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .activity-item {
            animation: fadeIn 0.3s ease-out forwards;
        }
        .table tbody tr:hover {
            background-color: rgba(0,0,0,0.05);
            transition: background-color 0.2s ease;
        }
        .teste{
            padding: 6px;
            border-radius: 4px !important;
        }
        /* Adicione ao seu CSS existente */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            line-height: 1;
        }

        .table td {
            vertical-align: middle;
        }

        .btn-outline-secondary:hover {
            background-color: #e9ecef;
            color: #000;
            border-color: #ced4da;
        }
        
    </style>
</head>
<body class="bg-light">
    <a href="dashboard.php" class="btn btn-outline-primary back-button">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>

    <div class="container">



        <h2 class="text-center mb-4">Gestão do Sistema</h2>
        
        
        
        <!-- Cards de Estatísticas -->
        <div class="row mb-4 carde">
            <!-- CARD NÃO INICIADAS -->
            <div class="col">
                <div class="card stats-card text-white não-iniciada cursor-pointer" data-status="não iniciada">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="card-title">Não Iniciadas</h6>
                                <h2 class="mb-0"><?php echo $stats['naoIniciadas']; ?></h2>
                            </div>
                            <i class="bi bi-clock stats-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- CARD EM ANDAMENTO -->
            <div class="col">
                <div class="card stats-card em-andamento text-dark cursor-pointer" data-status="em andamento">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="card-title">Em Andamento</h6>
                                <h2 class="mb-0"><?php echo $stats['emAndamento']; ?></h2>
                            </div>
                            <i class="bi bi-gear stats-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- CARD CONCLUÍDAS -->
            <div class="col">
                <div class="card stats-card concluída text-white cursor-pointer" data-status="concluída">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="card-title">Concluídas</h6>
                                <h2 class="mb-0"><?php echo $stats['concluidas']; ?></h2>
                            </div>
                            <i class="bi bi-check2-circle stats-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- CARD PRONTO E AVISADO -->
            <div class="col">
                <div class="card stats-card pronto-e-avisado text-white cursor-pointer" data-status="pronto e avisado">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="card-title">Pronto e Avisado</h6>
                                <h2 class="mb-0"><?php echo $stats['prontoAvisado']; ?></h2>
                            </div>
                            <i class="bi bi-bell stats-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- CARD ENTREGUE -->
            <div class="col">
                <div class="card stats-card entregue text-white cursor-pointer" data-status="entregue">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="card-title">Entregue</h6>
                                <h2 class="mb-0"><?php echo $stats['entregue']; ?></h2>
                            </div>
                            <i class="bi bi-box-seam stats-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Seção de Filtros -->
        <div class="filter-section mb-4">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Pesquisar</label>
                    <input type="text" class="form-control" id="search-input" 
                           placeholder="Pesquisar por nome do cliente, número da ordem, modelo ou defeito...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Período</label>
                    <input type="text" class="form-control" id="date-range" placeholder="Selecione o período">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="status-filter">
                        <option value="">Todos</option>
                        <option value="não iniciada">Não Iniciada</option>
                        <option value="em andamento">Em Andamento</option>
                        <option value="concluída">Concluída</option>
                        <option value="pronto e avisado">Pronto e Avisado</option>
                        <option value="entregue">Entregue</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ordenar por</label>
                    <select class="form-select" id="sort-filter">
                        <option value="date_desc">Mais recente</option>
                        <option value="date_asc">Mais antiga</option>
                        <option value="status">Status</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Lista de Ordens -->
        <div class="order-list">
            <h5 class="mb-3" id="refresh-activities" >Ordens de Serviço</h5>

            <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>OS</th>
                        <th>Cliente</th>
                        <th>Modelo</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body">
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
        <!-- Atividades Recentes -->
        <!-- <div class="activities-section"> -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <!-- <h5 class="mb-0">
                    <i class="bi bi-activity"></i> Atividades Recentes
                </h5> -->
                <div class="d-flex align-items-center gap-2">

                    <!-- <div class="badge bg-secondary" id="activities-count">
                        Carregando...
                    </div> -->
                </div>
            </div>
            
            <!-- <div class="list-group" id="activities-list">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div> -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa o seletor de data
    flatpickr("#date-range", {
        mode: "range",
        locale: "pt",
        dateFormat: "d/m/Y",
        maxDate: "today"
    });

    // Função para carregar ordens
    async function loadOrders(filters = {}) {
        const tableBody = document.getElementById('orders-table-body');
        try {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </td>
                </tr>
            `;
            
            const response = await fetch('get_filtered_orders.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(filters)
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Erro ao carregar ordens');
            }

            if (data.orders.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center">Nenhuma ordem encontrada</td>
                    </tr>
                `;
                return;
            }

            // Adiciona as ordens
            tableBody.innerHTML = data.orders.map(order => `
                <tr style="cursor: pointer" onclick="window.location.href='view_order.php?id=${order.id}'">
                    <td>${order.id}</td>
                    <td>${order.client_name}</td>
                    <td>${order.device_model || '-'}</td>
                    <td>
                        <span class="badge ${getStatusClass(order.status)} teste">
                            ${order.status}
                        </span>
                    </td>
                    <td>${formatDate(order.created_at)}</td>
                    <td>
                        <button onclick="event.stopPropagation(); window.location.href='edit_order.php?id=${order.id}'" 
                                class="btn btn-sm btn-outline-secondary"
                                title="Editar ordem">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        } catch (error) {
            console.error('Erro:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger">
                        Erro ao carregar ordens: ${error.message}
                    </td>
                </tr>
            `;
        }
    }

    // Função auxiliar para formatar a data
    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('pt-BR');
    }

    // Função auxiliar para definir a classe do status
    function getStatusClass(status) {
        const statusClasses = {
            'não iniciada': 'não-iniciada',
            'em andamento': 'em-andamento',
            'concluída': 'concluída',
            'pronto e avisado': 'pronto-e-avisado',
            'entregue': 'entregue'
        };
        return statusClasses[status.toLowerCase()] || 'bg-secondary';
    }

    // Função para carregar atividades
    async function loadActivities() {
        const activitiesList = document.getElementById('activities-list');
        const activitiesCount = document.getElementById('activities-count');
        
        try {
            const response = await fetch('get_recent_activities.php');
            const data = await response.json();
            
            if (data.success && data.activities && data.activities.length > 0) {
                // Atualiza o contador
                activitiesCount.textContent = `${data.activities.length} atividade(s)`;
                
                // Renderiza as atividades
                activitiesList.innerHTML = data.activities.map(activity => `
                    <div class="list-group-item activity-item" 
                         ${activity.order_id ? `onclick="window.location.href='view_order.php?id=${activity.order_id}'"` : ''}
                         style="border-left-color: var(--bs-${activity.color})">
                        <div class="d-flex align-items-start">
                            <div class="activity-icon bg-${activity.color} text-white">
                                <i class="bi ${activity.icon}"></i>
                            </div>
                            <div class="activity-info">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="activity-user">
                                        <i class="bi bi-person-circle"></i> ${activity.user_name}
                                    </span>
                                    <small class="activity-time text-muted">
                                        <i class="bi bi-clock"></i> ${activity.formatted_date}
                                    </small>
                                </div>
                                <div class="activity-description mt-1">
                                    ${activity.description}
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                activitiesList.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox-fill fs-2"></i>
                        <p class="mt-2">Nenhuma atividade recente encontrada</p>
                    </div>
                `;
                activitiesCount.textContent = '0 atividades';
            }
        } catch (error) {
            console.error('Erro:', error);
            activitiesList.innerHTML = `
                <div class="alert alert-danger m-3">
                    <i class="bi bi-exclamation-triangle"></i>
                    Erro ao carregar atividades: ${error.message}
                </div>
            `;
        }
    }

    // Event listeners para os filtros
    document.getElementById('search-input').addEventListener('input', debounce(() => {
        const filterValues = getFilterValues();
        loadOrders(filterValues);
    }, 500));

    document.getElementById('status-filter').addEventListener('change', () => {
        const filterValues = getFilterValues();
        loadOrders(filterValues);
    });

    document.getElementById('sort-filter').addEventListener('change', () => {
        const filterValues = getFilterValues();
        loadOrders(filterValues);
    });

    document.getElementById('date-range').addEventListener('change', () => {
        const filterValues = getFilterValues();
        loadOrders(filterValues);
    });

    // Função para obter valores dos filtros
    function getFilterValues() {
        return {
            search: document.getElementById('search-input').value,
            status: document.getElementById('status-filter').value,
            sort: document.getElementById('sort-filter').value,
            dateRange: document.getElementById('date-range').value
        };
    }

    // Função debounce para limitar requisições
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Botão de atualizar atividades
    document.getElementById('refresh-activities').addEventListener('click', loadActivities);

    // Cards de status
    document.querySelectorAll('.stats-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.stats-card').forEach(c => 
                c.classList.remove('active'));
            this.classList.add('active');
            
            const status = this.dataset.status;
            document.getElementById('status-filter').value = status;
            loadOrders(getFilterValues());
        });
    });

    // Carrega dados iniciais
    loadOrders();
    loadActivities();

    // Atualização automática
    setInterval(() => {
        loadOrders(getFilterValues());
        loadActivities();
    }, 60000); // Atualiza a cada minuto
});
</script>
</body>