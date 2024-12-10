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
<?php 
// Mantém todo o código PHP inicial
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
    // Buscar dados da ordem de serviço (mantém a query original)
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

    // Buscar notas técnicas (mantém a query original)
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
    <title>OS #<?php echo str_pad($order['id'], 6, "0", STR_PAD_LEFT); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .main-container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .order-header {
            margin-bottom: 20px;
            padding-left: 50px;
            position: relative;
        }

        .back-button {
            position: absolute;
            left: 0;
            top: 0;
            color: #6c757d;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }

        .back-button:hover {
            color: #000;
        }

        .order-title {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
            color: #212529;
        }

        .order-date {
            color: #6c757d;
            font-size: 14px;
        }

        .order-grid {
            display: grid;
            grid-template-columns: 300px 1fr 300px;
            gap: 20px;
        }

        .panel {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .panel-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #212529;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .client-info .info-group {
            margin-bottom: 15px;
        }

        .info-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .info-value {
            font-size: 14px;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
            color: #212529;
        }

        .reported-issue {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 14px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }

        .tab {
            padding: 8px 16px;
            font-size: 14px;
            color: #6c757d;
            cursor: pointer;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .tab.active {
            background: #0d6efd;
            color: white;
        }

        .notes-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .notes-content {
            flex: 1;
            background: #f8f9fa;
            border-radius: 4px;
            padding: 15px;
            font-size: 14px;
            overflow-y: auto;
            white-space: pre-wrap;
        }

        .notes-input {
            display: flex;
            gap: 10px;
        }

        .notes-input input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 14px;
        }

        .notes-input input:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
        }

        .action-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 10px;
            width: 100%;
            transition: all 0.2s;
        }

        .status-button {
            background: #dc3545;
            color: white;
        }

        .auth-button {
            background: #ffc107;
            color: #000;
        }

        .default-button {
            background: #f8f9fa;
            color: #212529;
            border: 1px solid #dee2e6;
        }

        .action-button:hover {
            transform: translateY(-1px);
        }

        @media (max-width: 1200px) {
            .order-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="order-header">
            <a href="javascript:history.back()" class="back-button">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <h1 class="order-title">
                Ordem <?php echo str_pad($order['id'], STR_PAD_LEFT); ?>
            </h1>
            <div class="order-date">
                Aberta em <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
            </div>
        </div>

        <!-- Grid Principal -->
        <div class="order-grid">
            <!-- Painel Esquerdo - Informações do Cliente -->
            <div class="panel client-info">
                <div class="panel-title">
                    <i class="bi bi-person"></i> Informações do Cliente
                </div>

                <div class="info-group">
                    <div class="info-label">
                        <i class="bi bi-person"></i> Nome do Cliente
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($order['client_name']); ?>
                    </div>
                </div>

                <div class="info-group">
                    <div class="info-label">
                        <i class="bi bi-telephone"></i> Telefones
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($order['phone1']); ?>
                    </div>
                    <?php if ($order['phone2']): ?>
                    <div class="info-value">
                        <?php echo htmlspecialchars($order['phone2']); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="info-group">
                    <div class="info-label">
                        <i class="bi bi-laptop"></i> Equipamento
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($order['device_model']); ?>
                    </div>
                </div>

                <div class="info-group">
                    <div class="info-label">
                        <i class="bi bi-key"></i> Senha
                    </div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($order['device_password'] ?? '-'); ?>
                    </div>
                </div>

                <div class="info-group">
                    <div class="info-label">
                        <i class="bi bi-exclamation-triangle"></i> Defeito Relatado
                    </div>
                    <div class="reported-issue">
                        <?php echo nl2br(htmlspecialchars($order['reported_issue'])); ?>
                    </div>
                </div>
            </div>

            <!-- Painel Central - Notas Técnicas -->
            <div class="panel">
                <div class="tabs">
                    <div class="tab active">
                        <i class="bi bi-card-text"></i> Notas Técnicas
                    </div>
                    <div class="tab">
                        <i class="bi bi-camera"></i> Fotos
                    </div>
                    <div class="tab">
                        <i class="bi bi-tools"></i> Peças
                    </div>
                </div>

                <div class="notes-container">
                    <div class="notes-content" id="technicalNotes">
                        <?php echo nl2br(htmlspecialchars($textareaContent)); ?>
                    </div>
                    <div class="notes-input">
                        <input type="text" 
                               id="newNote" 
                               placeholder="Digite sua nota técnica..."
                               onkeypress="if(event.key === 'Enter') addNote()">
                        <button class="action-button default-button" onclick="addNote()">
                            <i class="bi bi-plus-lg"></i> Adicionar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Painel Direito - Ações -->
            <div class="panel">
                <div class="panel-title">
                    <i class="bi bi-gear"></i> Ações
                </div>

                <button id="statusButton" 
                        class="action-button status-button"
                        data-status="<?php echo $order['status']; ?>"
                        data-order-id="<?php echo $order['id']; ?>">
                    <i class="bi bi-arrow-repeat"></i>
                    <?php echo $order['status']; ?>
                </button>

                <button id="authButton" 
                        class="action-button auth-button"
                        data-auth-status="Autorização"
                        data-order-id="<?php echo $order['id']; ?>">
                    <i class="bi bi-check2-circle"></i>
                    Autorização
                </button>

                <button class="action-button default-button">
                    <i class="bi bi-cart"></i>
                    Compra de Peças
                </button>

                <button class="action-button default-button" onclick="openHistory()">
                    <i class="bi bi-clock-history"></i>
                    Histórico
                </button>

                <button class="action-button default-button" 
                        onclick="window.open('print_service_order.php?id=<?php echo $order['id']; ?>', '_blank')">
                    <i class="bi bi-printer"></i>
                    Imprimir
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Histórico -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Histórico da OS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                            <div class="status-history-list" style="max-height: 400px; overflow-y: auto;">
                                <!-- Status history will be inserted here -->
                            </div>
                        </div>
                        <div class="tab-pane fade" id="notes" role="tabpanel">
                            <div class="notes-history-list" style="max-height: 400px; overflow-y: auto;">
                                <!-- Notes history will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Constantes
        const orderId = <?php echo $_GET['id']; ?>;
        const statusFlow = ['Não iniciada', 'Em andamento', 'Concluída', 'Pronto e avisado', 'Entregue'];
        const authFlow = ['Autorização', 'Solicitado', 'Autorizado'];

        // Elementos
        const statusButton = document.getElementById('statusButton');
        const authButton = document.getElementById('authButton');
        const newNoteInput = document.getElementById('newNote');
        const technicalNotesDiv = document.getElementById('technicalNotes');

        // Funções de Notificação
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            toast.innerHTML = `
                <div class="toast-header">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                    <strong class="me-auto">Notificação</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            `;
            
            document.querySelector('.toast-container').appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        }

        // Função para adicionar nota técnica
        async function addNote() {
            const noteText = newNoteInput.value.trim();
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
                    const today = new Date().toLocaleDateString('pt-BR', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: '2-digit' 
                    });
                    
                    let newNoteText = '';
                    if (!technicalNotesDiv.textContent.includes(today)) {
                        newNoteText = `\n---------------- ${today} ----------------\n\n`;
                    }
                    
                    newNoteText += `${data.username}: ${noteText}\n`;
                    technicalNotesDiv.innerHTML += newNoteText.replace(/\n/g, '<br>');
                    newNoteInput.value = '';
                    technicalNotesDiv.scrollTop = technicalNotesDiv.scrollHeight;
                    
                    showToast('Nota adicionada com sucesso!');
                } else {
                    throw new Error(data.message || 'Erro ao salvar nota');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao salvar nota técnica', 'error');
            }
        }

        // Funções de atualização de status
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
                    button.innerHTML = `<i class="bi bi-arrow-repeat"></i> ${newStatus}`;
                    showToast(`Status atualizado para: ${newStatus}`);
                } else {
                    throw new Error(data.message || 'Erro ao atualizar status');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao atualizar status', 'error');
            }
        }

        // Funções de atualização de autorização
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
                    button.innerHTML = `<i class="bi bi-check2-circle"></i> ${newStatus}`;
                    showToast(`Autorização atualizada para: ${newStatus}`);
                } else {
                    throw new Error(data.message || 'Erro ao atualizar autorização');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao atualizar autorização', 'error');
            }
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Status inicial
            let initialStatus = statusButton.dataset.status;
            statusButton.innerHTML = `<i class="bi bi-arrow-repeat"></i> ${initialStatus}`;

            // Carregar status de autorização inicial
            fetch('get_auth_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orderId: orderId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    authButton.dataset.authStatus = data.authStatus;
                    authButton.innerHTML = `<i class="bi bi-check2-circle"></i> ${data.authStatus}`;
                }
            });
        });

        // Click handlers
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

        // Enter key handler para notas
        newNoteInput.addEventListener('keypress', function(event) {
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
                    body: JSON.stringify({ orderId: orderId })
                });

                const data = await response.json();
                
                if (data.success) {
                    updateHistoryContent(data);
                } else {
                    throw new Error(data.message || 'Erro ao carregar histórico');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao carregar histórico', 'error');
            }
        }

        // Função para atualizar conteúdo do histórico
        function updateHistoryContent(data) {
            const statusContainer = document.querySelector('.status-history-list');
            const notesContainer = document.querySelector('.notes-history-list');

            if (data.statusHistory?.length) {
                statusContainer.innerHTML = data.statusHistory.map(item => `
                    <div class="history-item p-3 mb-2 bg-light rounded">
                        <div class="text-muted small">${item.formatted_date}</div>
                        <div class="fw-bold">${item.username}</div>
                        <div>
                            <i class="bi bi-arrow-right-circle"></i> 
                            Alterou status para: ${JSON.parse(item.details).new_status}
                        </div>
                    </div>
                `).join('');
            } else {
                statusContainer.innerHTML = '<div class="p-3 text-muted">Nenhuma alteração de status encontrada.</div>';
            }

            if (data.notesHistory?.length) {
                notesContainer.innerHTML = data.notesHistory.map(item => `
                    <div class="history-item p-3 mb-2 bg-light rounded">
                        <div class="text-muted small">${item.formatted_date}</div>
                        <div class="fw-bold">${item.username}</div>
                        <div>${item.note}</div>
                    </div>
                `).join('');
            } else {
                notesContainer.innerHTML = '<div class="p-3 text-muted">Nenhuma nota técnica encontrada.</div>';
            }
        }

        // Função para abrir modal de histórico
        function openHistory() {
            const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
            loadOrderHistory();
            historyModal.show();
        }

        // Verificação de notificações
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
                            updateAuthButton('Autorizado');
                        }
                    }
                })
                .catch(error => console.error('Erro ao verificar notificações:', error));
        }

        // Iniciar verificação periódica de notificações
        setInterval(checkNotifications, 5000);
    </script>
</body>
</html>