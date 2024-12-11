<?php 
session_start(); 
require_once 'config.php';  

if(!isset($_SESSION['user_id'])) {     
    header("Location: index.php");     
    exit; 
}  

if(!isset($_GET['id'])) {     
    header("Location: dashboard.php");     
    exit; 
}  

$database = new Database(); 
$db = $database->getConnection();  

try {
    // Primeiro, buscar os dados da ordem de serviço
    $query = "SELECT 
            so.*,
            c.name as client_name,
            c.phone1,
            c.phone2,
            so.device_password,
            COALESCE(so.status, 'Não iniciada') as status
          FROM service_orders so 
          INNER JOIN clients c ON so.client_id = c.id 
          WHERE so.id = :id";

    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $_GET['id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {         
        header("Location: dashboard.php");         
        exit;     
    }

    // Depois, buscar as notas técnicas
    $notesQuery = "SELECT tn.*, u.username, 
                   DATE_FORMAT(tn.created_at, '%d/%m/%y') as formatted_date,
                   DATE_FORMAT(tn.created_at, '%Y-%m-%d') as note_date
                   FROM technical_notes tn 
                   JOIN users u ON tn.user_id = u.id 
                   WHERE tn.order_id = :order_id 
                   ORDER BY tn.created_at ASC";  

    $stmt = $db->prepare($notesQuery);     
    $stmt->execute([':order_id' => $_GET['id']]);     
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);      

    $textareaContent = '';     
    $currentDate = '';      

    foreach ($notes as $note) {         
        if ($currentDate != $note['note_date']) {             
            $textareaContent .= "\n " . $note['formatted_date'] . " \n\n";       
            $currentDate = $note['note_date'];         
        }         
        $textareaContent .= "{$note['username']}: {$note['note']}\n";     
    }      

} catch(Exception $e) {         
    header("Location: dashboard.php");         
    exit;     
} 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Serviço <?php echo $order['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --bs-primary: #4a6fff;
            --bs-secondary: #6c757d;
            --bs-success: #28a745;
            --bs-info: #17a2b8;
            --bs-warning: #ffc107;
            --bs-danger: #dc3545;
        }

        body {
            background-color: #f8f9fa;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }

        .order-header {
            background: linear-gradient(135deg, var(--bs-primary) 0%, #3d5afe 100%);
            color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            background: rgba(255, 255, 255, 0.2);
        }

        .info-label {
            color: var(--bs-secondary);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-value {
            font-weight: 500;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 0.375rem;
        }

        .action-button {
            width: 100%;
            text-align: left;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0,0,0,0.1);
            background: white;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .notes-area {
            border: none;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            min-height: 200px;
            resize: none;
            width: 100%;
            font-family: inherit;
        }

        .status-history {
            max-height: 300px;
            overflow-y: auto;
        }

        /* Status styles */
        .status-nao-iniciada { background-color: var(--bs-danger); color: white; }
        .status-em-andamento { background-color: var(--bs-warning); color: black; }
        .status-concluida { background-color: var(--bs-success); color: white; }
        .status-pronto-e-avisado { background-color: var(--bs-info); color: white; }
        .status-entregue { background-color: var(--bs-secondary); color: white; }

        /* Auth styles */
        .auth-autorizacao { background-color: var(--bs-secondary); color: white; }
        .auth-solicitado { background-color: var(--bs-warning); color: black; }
        .auth-autorizado { background-color: var(--bs-success); color: white; }

        /* Toast notifications */
        .toast-container {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            z-index: 1050;
        }

        .toast {
            background: white;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        .history-item {
            padding: 1rem;
            border-radius: 0.5rem;
            background: #f8f9fa;
            margin-bottom: 0.5rem;
        }

        .history-item .date {
            color: var(--bs-secondary);
            font-size: 0.875rem;
        }

        .history-item .username {
            font-weight: 600;
            margin: 0.25rem 0;
        }

        .history-item .detail {
            color: var(--bs-secondary);
        }

        @media (max-width: 768px) {
            .order-header {
                padding: 1.5rem;
            }
            
            .container-fluid {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Botão Voltar -->
        <div class="row mb-4">
            <div class="col">
                <button onclick="history.back()" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </button>
            </div>
        </div>

        <!-- Cabeçalho da OS -->
        <div class="order-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">Ordem de Serviço #<?php echo str_pad($order['id'], 6, "0", STR_PAD_LEFT); ?></h4>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="status-badge">
                        <?php echo $order['status']; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="row">
            <!-- Informações do Cliente e Dispositivo -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Informações do Cliente</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-person"></i> Nome
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($order['client_name']); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-telephone"></i> Telefones
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($order['phone1']); ?><br>
                                    <?php echo htmlspecialchars($order['phone2'] ?? '-'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Informações do Dispositivo</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-laptop"></i> Modelo
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($order['device_model']); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">
                                    <i class="bi bi-key"></i> Senha
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($order['device_password'] ?? '-'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="info-label">
                                    <i class="bi bi-exclamation-triangle"></i> Defeito Relatado
                                </div>
                                <div class="info-value">
                                    <?php echo htmlspecialchars($order['reported_issue']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Laudo Técnico -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-clipboard-data"></i> Laudo Técnico
                        </h5>
                        <textarea id="technicalNotes" class="notes-area mb-3" readonly><?php echo $textareaContent; ?></textarea>
                        <div class="input-group">
                            <textarea id="newNote" class="form-control" rows="2" 
                                    placeholder="Digite sua nota técnica..." data-autoresize></textarea>
                            <button onclick="addNote()" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> Adicionar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Ações -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Ações</h5>
                        
                        <button id="statusButton" class="action-button" 
                                data-status="<?php echo $order['status']; ?>"
                                data-order-id="<?php echo $order['id']; ?>">
                            <i class="bi bi-gear-fill"></i>
                            <span><?php echo $order['status']; ?></span>
                        </button>

                        <button id="authButton" class="action-button"
                                data-auth-status="Autorização"
                                data-order-id="<?php echo $order['id']; ?>">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Autorização</span>
                        </button>

                        <button class="action-button" onclick="window.open('print_service_order.php?id=<?php echo $order['id']; ?>', '_blank')">
                            <i class="bi bi-printer-fill"></i>
                            <span>Imprimir</span>
                        </button>

                        <button class="action-button" data-bs-toggle="modal" data-bs-target="#historyModal">
                            <i class="bi bi-clock-history"></i>
                            <span>Histórico</span>
                        </button>

                        <button class="action-button text-success" onclick="javascript:history.go(-1)">
                            <i class="bi bi-save2-fill"></i>
                            <span>Salvar e Sair</span>
                        </button>
                    </div>
                </div>

                <!-- Datas -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Datas</h5>
                        <div class="info-label">
                            <i class="bi bi-calendar-event"></i> Abertura
                        </div>
                        <div class="info-value">
                            <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                        </div>
                        <div class="info-label">
                            <i class="bi bi-calendar-check"></i> Entrega Prevista
                        </div>
                        <div class="info-value">
                            <?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Histórico -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">Histórico da Ordem de Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="historyTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="status-tab" data-bs-toggle="tab" data-bs-target="#status" type="button" role="tab">
                                <i class="bi bi-clock-history"></i> Status
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab">
                                <i class="bi bi-card-text"></i> Notas Técnicas
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="historyTabContent">
                        <div class="tab-pane fade show active" id="status" role="tabpanel">
                            <div class="status-history-list">
                                <!-- Status history will be inserted here -->
                            </div>
                        </div>
                        <div class="tab-pane fade" id="notes" role="tabpanel">
                            <div class="notes-history-list">
                                <!-- Notes history will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Container para notificações toast -->
    <div class="toast-container"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicialização dos tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Auto-resize para textareas
        document.querySelectorAll('[data-autoresize]').forEach(function(element) {
            element.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });

        // Sistema de notificações toast
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            document.querySelector('.toast-container').appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Função para adicionar nota técnica
        async function addNote() {
            const noteText = document.getElementById('newNote').value.trim();
            if (!noteText) {
                showToast('Por favor, digite uma nota técnica.', 'error');
                return;
            }

            try {
                const response = await fetch('save_technical_note.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        orderId: <?php echo $_GET['id']; ?>,
                        note: noteText
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    const technicalNotes = document.getElementById('technicalNotes');
                    const today = new Date().toLocaleDateString('pt-BR', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: '2-digit' 
                    });
                    
                    let newNoteText = '';
                    if (!technicalNotes.value.includes(today)) {
                        newNoteText = `\n${today}\n\n`;
                    }
                    
                    newNoteText += `${data.username}: ${noteText}\n`;
                    technicalNotes.value += newNoteText;
                    document.getElementById('newNote').value = '';
                    technicalNotes.scrollTop = technicalNotes.scrollHeight;
                    
                    showToast('Nota adicionada com sucesso!');
                } else {
                    showToast(data.message || 'Erro ao salvar nota', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao salvar nota técnica', 'error');
            }
        }

        // Event listener para tecla Enter no campo de nota
        document.getElementById('newNote').addEventListener('keypress', function(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                addNote();
            }
        });

        // Função para carregar histórico
        async function loadOrderHistory() {
            try {
                const response = await fetch('get_order_history.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        orderId: <?php echo $_GET['id']; ?>
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Atualizar histórico de status
                    const statusContainer = document.querySelector('.status-history-list');
                    if (data.statusHistory && data.statusHistory.length > 0) {
                        statusContainer.innerHTML = data.statusHistory.map(item => `
                            <div class="history-item">
                                <div class="date">${item.formatted_date}</div>
                                <div class="username">${item.username}</div>
                                <div class="detail">
                                    <i class="bi bi-arrow-right-circle"></i> 
                                    Alterou status para: ${JSON.parse(item.details).new_status}
                                </div>
                            </div>
                        `).join('');
                    } else {
                        statusContainer.innerHTML = '<div class="p-3 text-muted">Nenhuma alteração de status encontrada.</div>';
                    }

                    // Atualizar histórico de notas
                    const notesContainer = document.querySelector('.notes-history-list');
                    if (data.notesHistory && data.notesHistory.length > 0) {
                        notesContainer.innerHTML = data.notesHistory.map(item => `
                            <div class="history-item">
                                <div class="date">${item.formatted_date}</div>
                                <div class="username">${item.username}</div>
                                <div class="detail">${item.note}</div>
                            </div>
                        `).join('');
                    } else {
                        notesContainer.innerHTML = '<div class="p-3 text-muted">Nenhuma nota técnica encontrada.</div>';
                    }
                }
            } catch (error) {
                console.error('Erro ao carregar histórico:', error);
                showToast('Erro ao carregar histórico', 'error');
            }
        }

        // Status e Autorização
        const statusButton = document.getElementById('statusButton');
        const authButton = document.getElementById('authButton');
        const statusFlow = ['Não iniciada', 'Em andamento', 'Concluída', 'Pronto e avisado', 'Entregue'];
        const authFlow = ['Autorização', 'Solicitado', 'Autorizado'];

        function updateButtonAppearance(button, status, prefix = 'status') {
            button.className = 'action-button';
            button.classList.add(`${prefix}-${status.toLowerCase().normalize('NFD')
                .replace(/[\u0300-\u036f]/g, "")
                .replace(/ /g, '-')}`);
            button.querySelector('span').textContent = status;
        }

        async function updateStatus(button, newStatus) {
            try {
                const response = await fetch('update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        orderId: button.dataset.orderId,
                        status: newStatus
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    button.dataset.status = newStatus;
                    updateButtonAppearance(button, newStatus);
                    showToast(`Status atualizado para: ${newStatus}`);
                } else {
                    showToast(data.message || 'Erro ao atualizar status', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao atualizar status', 'error');
            }
        }

        async function updateAuthStatus(button, newStatus) {
            try {
                const response = await fetch('update_auth_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        orderId: button.dataset.orderId,
                        authStatus: newStatus
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    button.dataset.authStatus = newStatus;
                    updateButtonAppearance(button, newStatus, 'auth');
                    showToast(`Autorização atualizada para: ${newStatus}`);
                } else {
                    showToast(data.message || 'Erro ao atualizar autorização', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao atualizar autorização', 'error');
            }
        }

        // Event listeners para botões de status e autorização
        statusButton.addEventListener('click', function() {
            const currentStatus = this.dataset.status;
            const currentIndex = statusFlow.indexOf(currentStatus);
            const nextStatus = statusFlow[(currentIndex + 1) % statusFlow.length];
            updateStatus(this, nextStatus);
        });

        authButton.addEventListener('click', function() {
            const currentStatus = this.dataset.authStatus;
            const currentIndex = authFlow.indexOf(currentStatus);
            const nextStatus = authFlow[(currentIndex + 1) % authFlow.length];
            updateAuthStatus(this, nextStatus);
        });

        // Verificação periódica de notificações
        function checkNotifications() {
            fetch('check_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.hasNotification) {
                        const notification = data.notification;
                        if (notification.type === 'auth_status') {
                            showToast(`Autorização solicitada para a OS #${notification.order_id} por ${notification.from_username}`);
                        } else if (notification.type === 'auth_approved') {
                            showToast(`Autorização aprovada para a OS #${notification.order_id} por ${notification.from_username}`);
                            authButton.dataset.authStatus = 'Autorizado';
                            updateButtonAppearance(authButton, 'Autorizado', 'auth');
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro ao verificar notificações:', error);
                });
        }

        // Iniciar verificação de notificações
        setInterval(checkNotifications, 5000);

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            const initialStatus = statusButton.dataset.status;
            updateButtonAppearance(statusButton, initialStatus);
            
            // Carregar status de autorização inicial
            fetch('get_auth_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    orderId: authButton.dataset.orderId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    authButton.dataset.authStatus = data.authStatus;
                    updateButtonAppearance(authButton, data.authStatus, 'auth');
                }
            })
            .catch(error => console.error('Erro:', error));
        });
    </script>
</body>
</html>