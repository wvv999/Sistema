<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config.php';
require_once 'GestaoStats.php';
require_once 'functions.php';

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
        .container { padding-top: 2rem; padding-bottom: 2rem; }
        
        .stats-card {
            transition: transform 0.2s;
            cursor: pointer;
            margin-bottom: 1rem;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        .filter-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .order-list {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .status-badge {
            min-width: 100px;
            text-align: center;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .activities-section {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 1.5rem;
        }
        .cursor-pointer {
            cursor: pointer;
        }

        .stats-card {
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .stats-card.active {
            border: 3px solid #000;
        }


        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 1rem;
            }
            
            .filter-section {
                padding: 1rem;
            }
        }

        .não-iniciada {
            background: #d35d6e;    /* Vermelho opaco/rosado */
        }

        .em-andamento {
            background: #e6b89c;    /* Terracota/pêssego suave */
        }

        .concluída {
            background: #9cc4b2;    /* Verde sage/pastel */
        }

        .pronto-e-avisado {
            background: #869daa;    /* Azul acinzentado */
        }

        .entregue {
            background: #4a6670;    /* Azul escuro petróleo */
        }
    </style>
</head>
<body class="bg-light">
    <a href="dashboard.php" class="btn btn-outline-primary back-button">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>

    <div class="container">
        <h2 class="text-center mb-4">Gestão do Sistema</h2>

        <?php
        try {
            $gestao = new GestaoStats();
            $stats = $gestao->getOrderStats();
        ?>
        <!-- Cards de Estatísticas -->
        <!-- Cards de Estatísticas -->
        <div class="row mb-4">
            <!-- CARD -->
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
            <!-- CARD -->
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
            <!-- CARD -->
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
            <!-- CARD -->
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
            <!-- CARD -->
            <div class="col">
                <div class="card stats-card entregue text-white cursor-pointer" data-status="entregue" >
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
        <?php
        } catch (Exception $e) {
            echo '<div class="alert alert-danger">Erro ao carregar estatísticas: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>

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
            <h5 class="mb-3">Ordens de Serviço</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>OS</th>
                            <th>Cliente</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="orders-table-body">
                        <tr>
                            <td colspan="5" class="text-center">
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
        <div class="activities-section">
            <h5 class="mb-3">Atividades Recentes</h5>
            <div class="list-group" id="activities-list">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
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

        // No event listeners dos filtros, adicione o campo de pesquisa
        document.getElementById('search-input').addEventListener('input', debounce(() => {
            const filterValues = {
                search: document.getElementById('search-input').value,
                status: document.getElementById('status-filter').value,
                sort: document.getElementById('sort-filter').value,
                dateRange: document.getElementById('date-range').value
            };
            loadOrders(filterValues);
        }, 500));

        // Adiciona listeners para os cards de status
        document.querySelectorAll('.stats-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove active class de todos os cards
                document.querySelectorAll('.stats-card').forEach(c => c.classList.remove('active'));
                // Adiciona active class ao card clicado
                this.classList.add('active');
                
                // Atualiza o select de status
                const status = this.dataset.status;
                document.getElementById('status-filter').value = status;
                
                // Carrega as ordens com o filtro
                const filterValues = {
                    search: document.getElementById('search-input').value,
                    status: status,
                    sort: document.getElementById('sort-filter').value,
                    dateRange: document.getElementById('date-range').value
                };
                loadOrders(filterValues);
            });
        });

        // Função debounce para limitar as requisições
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
                    <tr>
                        <td>${order.id}</td>
                        <td>${order.client_name}</td>
                        <td>
                            <span class="badge ${getStatusClass(order.status)}">
                                ${order.status}
                            </span>
                        </td>
                        <td>${formatDate(order.created_at)}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="window.location.href='view_order.php?id=${order.id}'">
                                <i class="bi bi-eye"></i> Ver
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
        
        // Função para carregar atividades
        async function loadActivities() {
            const activitiesList = document.getElementById('activities-list');
            
            try {
                const response = await fetch('get_recent_activities.php');
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Erro ao carregar atividades');
                }

                if (data.activities.length === 0) {
                    activitiesList.innerHTML = `
                        <div class="list-group-item text-center">
                            Nenhuma atividade recente
                        </div>
                    `;
                    return;
                }

                activitiesList.innerHTML = data.activities.map(activity => `
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${activity.description}</h6>
                            <small class="text-muted">${formatDate(activity.created_at)}</small>
                        </div>
                        <small class="text-muted">Por: ${activity.user_name}</small>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Erro:', error);
                activitiesList.innerHTML = `
                    <div class="list-group-item text-danger">
                        Erro ao carregar atividades: ${error.message}
                    </div>
                `;
            }
        }

        // Função auxiliar para formatar a data
        function formatDate(dateString) {
            const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
            return new Date(dateString).toLocaleDateString('pt-BR', options);
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
            return statusClasses[status] || 'bg-secondary';
        }

        // Event listeners para os filtros
        const filters = document.querySelectorAll('#status-filter, #sort-filter');
        filters.forEach(filter => {
            filter.addEventListener('change', () => {
                const filterValues = {
                    status: document.getElementById('status-filter').value,
                    sort: document.getElementById('sort-filter').value,
                    dateRange: document.getElementById('date-range').value
                };
                loadOrders(filterValues);
            });
        });

        document.getElementById('date-range').addEventListener('change', (e) => {
            const filterValues = {
                status: document.getElementById('status-filter').value,
                sort: document.getElementById('sort-filter').value,
                dateRange: e.target.value
            };
            loadOrders(filterValues);
        });

        // Carrega os dados iniciais
        loadOrders();
        loadActivities();

        // Atualiza as estatísticas periodicamente
        function updateStats() {
            fetch('get_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.stats) {
                        document.querySelector('[data-status="não iniciada"] h2').textContent = data.stats.naoIniciadas;
                        document.querySelector('[data-status="em andamento"] h2').textContent = data.stats.emAndamento;
                        document.querySelector('[data-status="concluída"] h2').textContent = data.stats.concluidas;
                        document.querySelector('[data-status="pronto e avisado"] h2').textContent = data.stats.prontoAvisado;
                        document.querySelector('[data-status="entregue"] h2').textContent = data.stats.entregue;
                    }
                })
                .catch(error => console.error('Erro ao atualizar estatísticas:', error));
        }

        // Atualiza estatísticas a cada minuto
        setInterval(updateStats, 60000);

        // Atualiza a lista de ordens e atividades a cada 2 minutos
        setInterval(() => {
            loadOrders({
                status: document.getElementById('status-filter').value,
                sort: document.getElementById('sort-filter').value,
                dateRange: document.getElementById('date-range').value
            });
            loadActivities();
        }, 120000);
    });
</script>
</body>
</html>