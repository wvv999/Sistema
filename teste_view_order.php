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
            COALESCE(so.status, 'Não iniciada') as status,
            COALESCE(so.auth_status, 'Autorização') as auth_status
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
            $textareaContent .= "\n---------------- " . $note['formatted_date'] . " ----------------\n\n";             
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
    <title>Ordem de Serviço #<?php echo $order['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f5f6fa;
        }
        
        .progress-step {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            position: relative;
        }

        .progress-label {
            position: absolute;
            bottom: -25px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            font-size: 0.85rem;
        }

        .progress-line {
            flex: 1;
            height: 3px;
            background: #dee2e6;
        }

        .progress-line.active {
            background: #0d6efd;
        }

        .timeline-item {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 4px;
            top: 8px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #0d6efd;
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            left: 9px;
            top: 20px;
            width: 2px;
            height: calc(100% + 10px);
            background: #dee2e6;
        }

        .timeline-item:last-child::after {
            display: none;
        }

        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: none;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            padding: 0.5rem 1rem;
            margin-right: 1rem;
            position: relative;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            background: none;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #0d6efd;
        }

        .technical-note {
            border-left: 3px solid #0d6efd;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
        }

        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .toast {
            padding: 12px 20px;
            border-radius: 8px;
            background: white;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: slideIn 0.3s ease;
        }

        .technical-report {
            background-color: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
        }

        .technical-notes {
            background: white;
            border-radius: 8px;
            padding: 16px;
        }

        .technical-notes textarea {
            border: none;
            background: transparent;
            width: 100%;
            resize: none;
            padding: 0;
            margin-bottom: 10px;
            font-size: 0.9rem;
            font-family: inherit;
            line-height: 1.5;
        }

        .technical-notes textarea:focus {
            outline: none;
            box-shadow: none;
        }

        .add-note-form {
            padding-top: 16px;
            margin-top: 16px;
        }

        .add-note-form .input-group {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px;
            align-items: start;
        }

        .add-note-form textarea {
            min-height: 38px;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background-color: white;
            resize: none;
            line-height: 20px;
            transition: all 0.3s ease;
        }

        .add-note-form textarea:focus {
            border-color: #4a6fff;
            box-shadow: 0 0 0 3px rgba(74, 111, 255, 0.1);
        }

        .add-note-form button {
            height: 38px;
            white-space: nowrap;
            padding: 0 16px;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: all 0.3s ease;
        }

        .add-note-form button:hover {
            transform: translateY(-2px);
        }

        /* Status button styles */
        .status-nao-iniciada { background-color: #e74c3c; color: white; }
        .status-em-andamento { background-color: #f39c12; color: white; }
        .status-concluida { background-color: #27ae60; color: white; }
        .status-pronto-e-avisado { background-color: #3498db; color: white; }
        .status-entregue { background-color: #2c3e50; color: white; }

        /* Auth button styles */
        .auth-button {
            font-weight: 600;
            letter-spacing: 0.5px;
            justify-content: center;
        }

        .auth-autorizacao { background-color: #6c757d; color: white; }
        .auth-solicitado { background-color: #ffc107; color: black; }
        .auth-autorizado { background-color: #28a745; color: white; }

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
        .content-right {
            width: 300px;
        }
        .status-button {
            font-weight: 600;
            letter-spacing: 0.5px;
            justify-content: center;
        }

        /* Status button styles */
        .status-nao-iniciada { background-color: #e74c3c; color: white; }
        .status-em-andamento { background-color: #f39c12; color: white; }
        .status-concluida { background-color: #27ae60; color: white; }
        .status-pronto-e-avisado { background-color: #3498db; color: white; }
        .status-entregue { background-color: #2c3e50; color: white; }

        /* Auth button styles */
        .auth-button {
            font-weight: 600;
            letter-spacing: 0.5px;
            justify-content: center;
        }

        .auth-autorizacao { background-color: #6c757d; color: white; }
        .auth-solicitado { background-color: var(--warning-color); color: black; }
        .auth-autorizado { background-color: var(--success-color); color: white; }
    </style>
</head>
<body class="p-4">
    <!-- Header -->
    <div class="container-fluid mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="javascript:history.go(-1)" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="h3 mb-0">Ordem <?php echo str_pad($order['id'], 5, "0", STR_PAD_LEFT); ?></h1>
                <small class="text-muted">Aberta em <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></small>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-3">
                <!-- Client Info -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informações do Cliente</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2 text-muted mb-2">
                                <i class="bi bi-person"></i>
                                <small>Nome do Cliente</small>
                            </div>
                            <div class="ps-4"><?php echo htmlspecialchars($order['client_name']); ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2 text-muted mb-2">
                                <i class="bi bi-telephone"></i>
                                <small>Telefones</small>
                            </div>
                            <div class="ps-4"><?php echo htmlspecialchars($order['phone1']); ?></div>
                            <?php if ($order['phone2']): ?>
                                <div class="ps-4"><?php echo htmlspecialchars($order['phone2']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2 text-muted mb-2">
                                <i class="bi bi-laptop"></i>
                                <small>Equipamento</small>
                            </div>
                            <div class="ps-4"><?php echo htmlspecialchars($order['device_model']); ?></div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2 text-muted mb-2">
                                <i class="bi bi-key"></i>
                                <small>Senha</small>
                            </div>
                            <div class="ps-4"><?php echo htmlspecialchars($order['device_password'] ?? '-'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Reported Issue -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Defeito Relatado</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['reported_issue'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Center Column -->
            <div class="col-6">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#notes">
                                    <i class="bi bi-file-text me-2"></i>Notas Técnicas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#photos">
                                    <i class="bi bi-camera me-2"></i>Fotos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#parts">
                                    <i class="bi bi-gear me-2"></i>Peças
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Notes Tab -->
                            <div class="tab-pane fade show active" id="notes">
                                <div class="technical-report">
                                    <div class="technical-notes">
                                        <textarea id="technicalNotes" rows="6" readonly><?php echo $textareaContent; ?></textarea>
                                        
                                        <div class="add-note-form">
                                            <div class="input-group">
                                                <textarea id="newNote" 
                                                        rows="1"
                                                        placeholder="Digite sua nota técnica..."
                                                        data-autoresize></textarea>
                                                <button onclick="addNote()" class="btn btn-primary">
                                                    <i class="bi bi-plus-circle"></i> Adicionar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Photos Tab -->
                            <div class="tab-pane fade" id="photos">
                                <div class="row g-3">
                                    <div class="col-4">
                                        <div class="border rounded d-flex align-items-center justify-content-center" 
                                             style="height: 200px; cursor: pointer;"
                                             onclick="document.getElementById('photoInput').click()">
                                            <i class="bi bi-plus-lg fs-2 text-muted"></i>
                                        </div>
                                        <input type="file" id="photoInput" hidden accept="image/*">
                                    </div>
                                </div>
                            </div>

                            <!-- Parts Tab -->
                            <div class="tab-pane fade" id="parts">
                                <div class="border rounded p-3 mb-3">
                                    <h6 class="mb-2">Fonte de Alimentação Dell</h6>
                                    <div class="d-flex justify-content-between">
                                        <span class="badge bg-warning">Aguardando aprovação</span>
                                        <span class="fw-medium">R$ 249,90</span>
                                    </div>
                                </div>
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-plus-lg me-2"></i>Adicionar Peça
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="content-right">
                <div class="side-panel">
                    <!-- Status e Ações -->
                    <div class="menu-section">
                        <div id="statusButton" 
                            class="action-button status-button"
                            data-status="<?php echo $order['status']; ?>"
                            data-order-id="<?php echo $order['id']; ?>"
                            data-bs-toggle="tooltip"
                            title="Clique para alterar o status">
                            <i class="bi bi-gear"></i>
                            <span><?php echo $order['status']; ?></span>
                        </div>

                        <div id="authButton" 
                            class="action-button auth-button auth-autorizacao"
                            data-auth-status="Autorização"
                            data-order-id="<?php echo $order['id']; ?>"
                            data-bs-toggle="tooltip"
                            title="Clique para alterar a autorização">
                            <i class="bi bi-check-circle"></i>
                            <span>Autorização</span>
                        </div>

                        <div class="action-button" data-bs-toggle="tooltip" title="Gerenciar peças">
                            <i class="bi bi-cart"></i>
                            <span>Compra de Peças</span>
                        </div>
                    </div>

                    <!-- Ações da OS -->
                    <div class="menu-section">
                        <button class="action-button" data-bs-toggle="tooltip" title="Ver histórico completo">
                            <i class="bi bi-clock-history"></i>
                            <span>Histórico</span>
                        </button>
                        <button class="action-button" data-bs-toggle="tooltip" title="Imprimir ordem de serviço" 
                                onclick="window.open('print_service_order.php?id=<?php echo $order['id']; ?>', '_blank')">
                            <i class="bi bi-printer"></i>
                            <span>Imprimir</span>
                        </button>
                        <button class="action-button" style="background-color:var(--success-color); color: white" onclick="javascript:history.go(-1)">
                            <i class="bi bi-box-arrow-right"></i>
                        
                            <span>Salvar e Sair</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const orderId = <?php echo $_GET['id']; ?>;
        const statusFlow = ['Não iniciada', 'Em andamento', 'Concluída', 'Pronto e avisado', 'Entregue'];
        const authFlow = ['Autorização', 'Solicitado', 'Autorizado'];

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Toast notification system
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

        // Button appearance update
        function updateButtonAppearance(button, status, prefix = 'status') {
            const classes = [...button.classList];
            classes.forEach(className => {
                if (className.startsWith(prefix) || className === 'btn-outline-primary' || className === 'btn-success') {
                    button.classList.remove(className);
                }
            });
            
            button.classList.add('btn');
            const statusClass = `${prefix}-${status.toLowerCase().normalize('NFD')
                .replace(/[\u0300-\u036f]/g, "")
                .replace(/ /g, '-')}`;
            button.classList.add(statusClass);
            button.querySelector('span').textContent = status;
        }

        // Technical notes functionality
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
                        orderId: orderId,
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
                        newNoteText = `\n---------------- ${today} ----------------\n\n`;
                    }
                    
                    newNoteText += `${data.username}: ${noteText}\n`;
                    technicalNotes.value += newNoteText;
                    document.getElementById('newNote').value = '';
                    technicalNotes.scrollTop = technicalNotes.scrollHeight;
                    
                    showToast('Nota adicionada com sucesso!');
                    loadOrderHistory();
                } else {
                    showToast(data.message || 'Erro ao salvar nota', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao salvar nota técnica', 'error');
            }
        }

        // Status management
        async function updateStatus(button, newStatus) {
            try {
                const response = await fetch('update_status.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        orderId: button.dataset.orderId,
                        status: newStatus
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    button.dataset.status = newStatus;
                    updateButtonAppearance(button, newStatus, 'status');
                    showToast(`Status atualizado para: ${newStatus}`);
                    loadOrderHistory();
                } else {
                    showToast(data.message || 'Erro ao atualizar status', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao atualizar status', 'error');
            }
        }

        // Auth status management
        async function updateAuthStatus(button, newStatus) {
            try {
                const response = await fetch('update_auth_status.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json'
                    },
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
                    loadOrderHistory();
                } else {
                    showToast(data.message || 'Erro ao atualizar autorização', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao atualizar autorização', 'error');
            }
        }

        // Order history management
        async function loadOrderHistory() {
            try {
                const response = await fetch('get_order_history.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        orderId: orderId
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    const timelineContainer = document.querySelector('.timeline');
                    timelineContainer.innerHTML = '';

                    // Combine and sort status and notes history
                    const allHistory = [
                        ...(data.statusHistory || []).map(item => ({
                            ...item,
                            type: 'status',
                            timestamp: new Date(item.created_at)
                        })),
                        ...(data.notesHistory || []).map(item => ({
                            ...item,
                            type: 'note',
                            timestamp: new Date(item.created_at)
                        }))
                    ].sort((a, b) => b.timestamp - a.timestamp);

                    allHistory.forEach(item => {
                        const historyItem = document.createElement('div');
                        historyItem.className = 'timeline-item mb-4';
                        
                        if (item.type === 'status') {
                            const details = JSON.parse(item.details);
                            historyItem.innerHTML = `
                                <h6 class="fw-medium mb-1">Status atualizado</h6>
                                <p class="mb-1 text-muted">${details.new_status}</p>
                                <small class="text-muted">${item.formatted_date}</small>
                            `;
                        } else {
                            historyItem.innerHTML = `
                                <h6 class="fw-medium mb-1">Nota técnica</h6>
                                <p class="mb-1 text-muted">${item.note}</p>
                                <small class="text-muted">${item.formatted_date}</small>
                            `;
                        }
                        
                        timelineContainer.appendChild(historyItem);
                    });
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao carregar histórico', 'error');
            }
        }

        // Notification check
        function checkNotifications() {
            fetch('check_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.hasNotification) {
                        if (data.notification.type === 'auth_status') {
                            showToast(`Autorização solicitada para a OS #${data.notification.order_id} por ${data.notification.from_username}`);
                        } else if (data.notification.type === 'auth_approved') {
                            showToast(`Autorização aprovada para a OS #${data.notification.order_id} por ${data.notification.from_username}`);
                            const authButton = document.getElementById('authButton');
                            authButton.dataset.authStatus = 'Autorizado';
                            updateButtonAppearance(authButton, 'Autorizado', 'auth');
                        }
                        loadOrderHistory();
                    }
                })
                .catch(console.error);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            // Status button click handler
            const statusButton = document.getElementById('statusButton');
            statusButton.addEventListener('click', function() {
                const currentStatus = this.dataset.status;
                const currentIndex = statusFlow.indexOf(currentStatus);
                const nextStatus = statusFlow[(currentIndex + 1) % statusFlow.length];
                updateStatus(this, nextStatus);
            });

            // Auth button click handler
            const authButton = document.getElementById('authButton');
            authButton.addEventListener('click', function() {
                const currentStatus = this.dataset.authStatus;
                const currentIndex = authFlow.indexOf(currentStatus);
                const nextStatus = authFlow[(currentIndex + 1) % authFlow.length];
                updateAuthStatus(this, nextStatus);
            });

            // Auto-resize textarea
            document.querySelectorAll('[data-autoresize]').forEach(function(element) {
                element.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            });

            // Enter key handler for notes
            document.getElementById('newNote').addEventListener('keypress', function(event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    addNote();
                }
            });

            // Initial load
            loadOrderHistory();

            // Set up periodic notification check
            setInterval(checkNotifications, 30000);
        });
    </script>
</body>
</html>