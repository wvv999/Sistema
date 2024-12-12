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
            --primary: #2563eb;
            --secondary: #64748b;
            --success: #16a34a;
            --warning: #ca8a04;
            --danger: #dc2626;
            --background: #f1f5f9;
            --surface: #ffffff;
            --text: #1e293b;
        }

        body {
            background-color: var(--background);
            color: var(--text);
            font-family: system-ui, -apple-system, sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        .workspace {
            display: grid;
            grid-template-columns: 280px 1fr 300px;
            gap: 24px;
            padding: 24px;
            max-width: 1800px;
            margin: 0 auto;
        }

        .panel {
            background: var(--surface);
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            height: calc(100vh - 48px);
            overflow-y: auto;
        }

        .nav-panel {
            padding: 20px;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .main-panel {
            display: flex;
            flex-direction: column;
            gap: 24px;
            padding: 32px;
        }

        .actions-panel {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            border-left: 1px solid #e2e8f0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .order-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text);
        }

        .info-section {
            background: var(--surface);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 0.875rem;
            color: var(--secondary);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .info-value {
            font-size: 1rem;
            color: var(--text);
            font-weight: 500;
        }

        .action-button {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: var(--surface);
            color: var(--text);
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
        }

        .action-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .notes-area {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            width: 100%;
            height: 200px;
            resize: vertical;
            font-family: inherit;
            line-height: 1.5;
        }

        .notes-input {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px;
            margin-top: 16px;
        }

        /* Status badges */
        .badge {
            padding: 6px 12px;
            border-radius: 9999px;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .badge-primary { background: #dbeafe; color: #1e40af; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }

        /* Toast notifications */
        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 100;
        }

        .toast {
            background: var(--surface);
            color: var(--text);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 8px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            display: flex;
            align-items: center;
            gap: 8px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 1200px) {
            .workspace {
                grid-template-columns: 240px 1fr;
            }

            .actions-panel {
                grid-column: span 2;
                height: auto;
            }
        }

        @media (max-width: 768px) {
            .workspace {
                grid-template-columns: 1fr;
                padding: 16px;
            }

            .nav-panel, .actions-panel {
                grid-column: span 1;
            }

            .panel {
                height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="workspace">
        <!-- Painel de Navegação -->
        <div class="panel nav-panel">
            <div class="order-title">
                OS #<?php echo str_pad($order['id'], 6, "0", STR_PAD_LEFT); ?>
            </div>
            
            <button onclick="history.back()" class="action-button">
                <i class="bi bi-arrow-left"></i>
                <span>Voltar</span>
            </button>

            <div class="section-title">
                <i class="bi bi-calendar"></i>
                Datas
            </div>

            <div class="info-item">
                <div class="info-label">Abertura</div>
                <div class="info-value">
                    <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                </div>
            </div>

            <div class="info-item">
                <div class="info-label">Previsão</div>
                <div class="info-value">
                    <?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?>
                </div>
            </div>
        </div>

        <!-- Painel Principal -->
        <div class="panel main-panel">
            <!-- Informações do Cliente -->
            <div class="info-section">
                <div class="section-title">
                    <i class="bi bi-person"></i>
                    Informações do Cliente
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nome</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($order['client_name']); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Telefone Principal</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($order['phone1']); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Telefone Alternativo</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($order['phone2'] ?? '-'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações do Dispositivo -->
            <div class="info-section">
                <div class="section-title">
                    <i class="bi bi-laptop"></i>
                    Informações do Dispositivo
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Modelo</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($order['device_model']); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Senha</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($order['device_password'] ?? '-'); ?>
                        </div>
                    </div>
                </div>
                <div class="info-item mt-4">
                    <div class="info-label">Defeito Relatado</div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($order['reported_issue']); ?>
                    </div>
                </div>
            </div>

            <!-- Laudo Técnico -->
            <div class="info-section">
                <div class="section-title">
                    <i class="bi bi-clipboard-data"></i>
                    Laudo Técnico
                </div>
                <textarea id="technicalNotes" class="notes-area" readonly><?php echo $textareaContent; ?></textarea>
                <div class="notes-input">
                    <textarea id="newNote" class="form-control" rows="2" 
                            placeholder="Digite sua nota técnica..."></textarea>
                    <button onclick="addNote()" class="action-button">
                        <i class="bi bi-plus-lg"></i>
                        Adicionar
                    </button>
                </div>
            </div>
        </div>

        <!-- Painel de Ações -->
        <div class="panel actions-panel">
            <div class="section-title">Ações</div>

            <!-- Status e Autorização -->
            <button id="statusButton" class="action-button"
                    data-status="<?php echo $order['status']; ?>"
                    data-order-id="<?php echo $order['id']; ?>">
                <i class="bi bi-gear"></i>
                <span><?php echo $order['status']; ?></span>
            </button>

            <button id="authButton" class="action-button"
                    data-auth-status="Autorização"
                    data-order-id="<?php echo $order['id']; ?>">
                <i class="bi bi-check-circle"></i>
                <span>Autorização</span>
            </button>

            <hr>

            <!-- Outras Ações -->
            <button class="action-button" data-bs-toggle="modal" data-bs-target="#historyModal">
                <i class="bi bi-clock-history"></i>
                Histórico
            </button>

            <button class="action-button" 
                    onclick="window.open('print_service_order.php?id=<?php echo $order['id']; ?>', '_blank')">
                <i class="bi bi-printer"></i>
                Imprimir
            </button>

            <button class="action-button" style="margin-top: auto; background: var(--success); color: white"
                    onclick="javascript:history.go(-1)">
                <i class="bi bi-check-lg"></i>
                Salvar e Sair
            </button>
        </div>
    </div>

    <!-- Modal de Histórico -->
    [O mesmo modal existente]

    <!-- Container de Toast -->
    <div class="toast-container"></div>

    <!-- Scripts -->
    [Os mesmos scripts existentes]


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