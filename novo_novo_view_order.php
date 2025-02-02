<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
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
} catch (Exception $e) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Serviço <?php echo $order['id'] ?> </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4a6fff;
            --secondary-color: #f8f9fa;
            --accent-color: #e7e9f6;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --border-radius: 8px;
            --shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        body {
            background-color: #f5f6fa;
            padding: 20px;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        body::-webkit-scrollbar {
            display: none;
        }

        .container {
            max-width: 90vw;
            padding: 10px;
            display: flex;
            gap: 5px;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        .left,
        .right {
            padding: 20px;
            height: auto;
            width: 25%;
            max-width: 25%;
            min-width: 300px;
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            display: flex;
            flex: 1 1 25%;
            flex-direction: column;
            gap: 20px;
        }

        .mid {
            height: auto;
            width: 45%;
            max-width: 45%;
            min-width: 300px;
            padding: 20px;
            flex: 1 1 40%;
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var (--shadow);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .info-label {
            max-width: fit-content;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
            font-size: 0.9rem;
            display: flex;
            gap: 6px;
        }

        .info-value {
            max-width: 300px;
            min-height: max-content;
            text-wrap: wrap;
            color: #333;
            margin-bottom: 15px;
            font-size: 1rem;
            border-radius: var(--border-radius);
            text-align: left;
        }

        .item {
            width: 100%;
            height: fit-content;
            background-color: #f5f6fa;
            border-radius: var(--border-radius);
            padding: 8px;
        }

        .side-panel {
            width: 100%;
            height: 100%;
            padding-right: 20px;
            display: flex;
            flex-direction: column;
            gap: 24px;
            justify-content: space-between;
        }

        .menu-section {
            width: 100%;
            align-items: center;
            justify-content: center;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: var(--border-radius);
            background-color: #fff;
            padding: 16px;
        }

        .action-button {
            width: 100%;
            padding: 12px;
            border-radius: var(--border-radius);
            border: 1px solid #dee2e6;
            background: white;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: 500;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }

        .action-button::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .action-button:hover::before {
            width: 100%;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .status-button {
            font-weight: 600;
            letter-spacing: 0.5px;
            justify-content: center;
        }

        .status-nao-iniciada {
            background-color: #e74c3c;
            color: white;
        }

        .status-em-andamento {
            background-color: #f39c12;
            color: white;
        }

        .status-concluida {
            background-color: #27ae60;
            color: white;
        }

        .status-pronto-e-avisado {
            background-color: #3498db;
            color: white;
        }

        .status-entregue {
            background-color: #2c3e50;
            color: white;
        }

        .auth-button {
            width: 100%;
            font-weight: 600;
            letter-spacing: 0.5px;
            justify-content: center;
        }

        .auth-autorizacao {
            background-color: #6c757d;
            color: white;
        }

        .auth-solicitado {
            background-color: var(--warning-color);
            color: black;
        }

        .auth-autorizado {
            background-color: var(--success-color);
            color: white;
        }

        .order-info {
            background: linear-gradient(145deg, var(--accent-color), #f8f9ff);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .order-info::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--primary-color);
            border-radius: var(--border-radius) 0 0 var(--border-radius);
        }

        .client-details {
            padding-left: 15px;
            margin-top: 10px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
        }

        .device-password,
        .reported-issue {
            height: 105px;
            width: 100%;
            background-color: #f8f9fa;
            padding: 16px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .device-password::before,
        .reported-issue::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--primary-color);
            border-radius: var(--border-radius) 0 0 var(--border-radius);
        }

        .device-password:hover,
        .reported-issue:hover {
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .section-title {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 12px;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: var(--primary-color);
        }

        .technical-report {
            background-color: #f8f9fa;
            padding: 16px;
            border-radius: var(--border-radius);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            flex: 1;
        }

        .technical-report::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--primary-color);
            border-radius: var(--border-radius) 0 0 var(--border-radius);
        }

        .technical-notes {
            background: white;
            border-radius: var(--border-radius);
            padding: 16px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .technical-notes textarea {
            border: none;
            background: transparent;
            width: 100%;
            height: 100%;
            resize: none;
            padding: 0;
            margin-bottom: 10px;
            font-size: 0.9rem;
            font-family: inherit;
            line-height: 1.5;
            flex: 1;
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
            border-radius: var(--border-radius);
            background-color: white;
            resize: none;
            line-height: 20px;
        }

        .add-note-form textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 111, 255, 0.1);
        }

        .add-note-form button {
            height: 38px;
            white-space: nowrap;
            padding: 0 16px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .add-note-form button:hover {
            transform: translateY(-2px);
            transition: var(--transition);
        }

        .history-item {
            padding: 15px;
            border-radius: var(--border-radius);
            background: var(--secondary-color);
            margin-bottom: 10px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .history-item:hover {
            background: var(--accent-color);
            transition: var(--transition);
        }

        .history-item .date {
            color: #6c757d;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .history-item .username {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .history-item .detail {
            color: #495057;
        }

        #historyTabs .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
        }

        #historyTabs .nav-link i {
            font-size: 1.1em;
        }

        .status-history-list,
        .notes-history-list {
            padding: 10px;
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

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .left,
            .right,
            .mid {
                width: 100%;
                max-width: 100%;
                min-width: 100%;
            }
        }
    </style>
</head>

<body>
    <a href="javascript:history.go(-1)" class="btn btn-outline-primary" style="position: absolute; top: 20px; left: 20px;">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>

    <div class="container text-center">
        <div class="left">
            <h4 class="mb-3">Ordem número: <?php echo str_pad($order['id'], STR_PAD_RIGHT); ?></h4>

            <div class="item">
                <div class="info-label"><i class="bi bi-person"></i> Nome do Cliente</div>
                <div class="info-value"><?php echo htmlspecialchars($order['client_name']); ?></div>
            </div>

            <div class="item">
                <div class="info-label"><i class="bi bi-laptop"></i> Modelo</div>
                <div class="info-value"><?php echo htmlspecialchars($order['device_model']); ?></div>
            </div>

            <div class="item">
                <div class="info-label"><i class="bi bi-key"></i> Senha do Dispositivo</div>
                <div class="info-value"><?php echo htmlspecialchars($order['device_password'] ?? '-'); ?></div>
            </div>

            <div class="item">
                <div class="info-label"><i class="bi bi-exclamation-triangle"></i> Defeito Reclamado</div>
                <div class="info-value"><?php echo htmlspecialchars($order['reported_issue']); ?></div>
            </div>
        </div>

        <div class="mid">
            <div class="section-title"><i class="bi bi-clipboard-data"></i> Laudo Técnico</div>
            <div class="technical-report">
                <div class="technical-notes">
                    <textarea id="technicalNotes" readonly><?php echo $textareaContent; ?></textarea>
                    <div class="add-note-form">
                        <div class="input-group">
                            <textarea id="newNote" rows="1" placeholder="Digite sua nota técnica..." data-autoresize></textarea>
                            <button onclick="addNote()" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Adicionar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="right">
            <div class="item">
                <div class="info-label"><i class="bi bi-telephone"></i> Contatos</div>
                <div class="info-value">
                    <?php echo htmlspecialchars($order['phone1']); ?> |
                    <?php echo htmlspecialchars($order['phone2'] ?? '-'); ?>
                </div>
            </div>

            <div class="item">
                <div class="info-label"><i class="bi bi-calendar-event"></i> Data de Abertura</div>
                <div class="info-value"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                <div class="info-label"><i class="bi bi-calendar-check"></i> Data de Entrega</div>
                <div class="info-value"><?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?></div>
            </div>

            <div class="side-panel">
                <div class="menu-section">
                    <div id="statusButton" class="action-button status-button" data-status="<?php echo $order['status']; ?>" data-order-id="<?php echo $order['id']; ?>" data-bs-toggle="tooltip" title="Clique para alterar o status">
                        <i class="bi bi-gear"></i>
                        <span><?php echo $order['status']; ?></span>
                    </div>

                    <div id="authButton" class="action-button auth-button auth-autorizacao" data-auth-status="Autorização" data-order-id="<?php echo $order['id']; ?>" data-bs-toggle="tooltip" title="Clique para alterar a autorização">
                        <i class="bi bi-check-circle"></i>
                        <span>Autorização</span>
                    </div>
                </div>

                <div class="menu-section">
                    <button class="action-button" data-bs-toggle="modal" data-bs-target="#historyModal">
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

    <!-- Modal -->
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

    <!-- Container para notificações toast -->
    <div class="toast-container"></div>

    <!-- Adicione o JavaScript do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicializa todos os tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Auto-resize textarea
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
                        newNoteText = `\n ${today} \n\n`;
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

        // Gestão de status e autorização
        const statusButton = document.getElementById('statusButton');
        const authButton = document.getElementById('authButton');

        // Arrays de status possíveis
        const statusFlow = ['Não iniciada', 'Em andamento', 'Concluída', 'Pronto e avisado', 'Entregue'];
        const authFlow = ['Autorização', 'Solicitado', 'Autorizado'];

        function updateButtonAppearance(button, status, prefix = 'status') {
            const classes = [...button.classList];
            classes.forEach(className => {
                if (className !== 'action-button') {
                    button.classList.remove(className);
                }
            });

            button.classList.add(`${prefix}-button`);
            const statusClass = `${prefix}-${status.toLowerCase().normalize('NFD')
                .replace(/[\u0300-\u036f]/g, "")
                .replace(/ /g, '-')}`;
            button.classList.add(statusClass);
            button.querySelector('span').textContent = status;
        }

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
                    updateButtonAppearance(button, newStatus);
                    showToast(`Status atualizado para: ${newStatus}`);
                } else {
                    showToast(data.message || 'Erro ao atualizar status', 'error');
                }
            } catch (error) {
                console.error('Erro ao atualizar status:', error);
                showToast('Erro ao atualizar status', 'error');
            }
        }

        // Atualiza o botão de autorização com o status atual ao carregar a página
        async function updateAuthButtonOnLoad() {
            try {
                const response = await fetch('get_auth_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        orderId: authButton.dataset.orderId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    authButton.dataset.authStatus = data.authStatus;
                    updateButtonAppearance(authButton, data.authStatus, 'auth');
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }

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
                } else {
                    showToast(data.message || 'Erro ao atualizar autorização', 'error');
                }
            } catch (error) {
                console.error('Erro ao atualizar autorização:', error);
                showToast('Erro ao atualizar autorização', 'error');
            }
        }

        // Event listeners
        statusButton.addEventListener('click', function() {
            let currentStatus = this.dataset.status;
            currentStatus = statusFlow.find(status =>
                status.toLowerCase() === currentStatus.toLowerCase()
            ) || currentStatus;

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

        // Inicialização dos botões
        document.addEventListener('DOMContentLoaded', function() {
            // Status inicial
            let initialStatus = statusButton.dataset.status;
            initialStatus = statusFlow.find(status =>
                status.toLowerCase() === initialStatus.toLowerCase()
            ) || initialStatus;
            statusButton.dataset.status = initialStatus;
            statusButton.innerHTML = '<i class="bi bi-gear"></i> <span>' + initialStatus + '</span>';
            updateButtonAppearance(statusButton, initialStatus);

            // Auth inicial
            updateAuthButtonOnLoad();
        });

        // Atualizar a função loadOrderHistory
        async function loadOrderHistory() {
            try {
                const response = await fetch('get_order_history.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
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
                } else {
                    console.error('Erro nos dados:', data);
                    showToast('Erro ao carregar histórico: ' + (data.message || 'Erro desconhecido'), 'error');
                }
            } catch (error) {
                console.error('Erro ao carregar histórico:', error);
                showToast('Erro ao carregar histórico: ' + error.message, 'error');
            }
        }

        // Adicionar evento ao botão de histórico
        document.querySelector('button[data-bs-target="#historyModal"]').addEventListener('click', function() {
            loadOrderHistory(); // Carrega o histórico
        });
    </script>
</body>

</html>