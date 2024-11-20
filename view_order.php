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

        .order-container {
            background-color: #fff;
            border-radius: var(--border-radius);
            padding: 24px;
            box-shadow: var(--shadow);
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 40px);
        }

        /* Order info styles */
        .order-info {
            background: linear-gradient(145deg, var(--accent-color), #f8f9ff);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            border: 1px solid rgba(0,0,0,0.05);
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

        .main-content {
            display: flex;
            gap: 24px;
            flex: 1;
            margin-bottom: 24px;
        }

        .content-left {
            flex: 1;
        }

        .content-right {
            width: 300px;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-value {
            color: #333;
            margin-bottom: 15px;
            font-size: 1rem;
            padding: 8px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .info-value:hover {
            background: rgba(255, 255, 255, 0.8);
        }
        .device-password, .reported-issue {
            background-color: #f8f9fa;
            padding: 16px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            transition: var(--transition);
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

        /* Side panel styles */
        .side-panel {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .menu-section {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 16px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: var(--border-radius);
            background-color: #fff;
            transition: var(--transition);
        }

        .menu-section:hover {
            box-shadow: var(--shadow);
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
            transition: var(--transition);
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .action-button::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: rgba(0,0,0,0.05);
            transition: var(--transition);
        }

        .action-button:hover::before {
            width: 100%;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Status button styles */
        .status-button {
            font-weight: 600;
            letter-spacing: 0.5px;
            justify-content: center;
        }

        .status-nao-iniciada { background-color: #6c757d; color: white; }
        .status-em-andamento { background-color: #fd7e14; color: white; }
        .status-concluida { background-color: var(--success-color); color: white; }
        .status-pronto-e-avisado { background-color: #0dcaf0; color: white; }
        .status-entregue { background-color: #20c997; color: white; }

        /* Auth button styles */
        .auth-button {
            font-weight: 600;
            letter-spacing: 0.5px;
            justify-content: center;
        }

        .auth-autorizacao { background-color: #6c757d; color: white; }
        .auth-solicitado { background-color: var(--warning-color); color: black; }
        .auth-autorizado { background-color: var(--success-color); color: white; }

        /* Technical notes section */
        .technical-report {
            background-color: #f8f9fa;
            padding: 16px;
            border-radius: var(--border-radius);
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
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
            /* border-top: 1px solid rgba(0,0,0,0.1); */
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
            transition: var(--transition);
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
            transition: var(--transition);
        }

        .add-note-form button:hover {
            transform: translateY(-2px);
        }

        /* Toast notifications */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .toast {
            padding: 12px 20px;
            border-radius: var(--border-radius);
            background: white;
            box-shadow: var(--shadow);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
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

        /* Responsive styles */
        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
            }
            
            .content-right {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .order-container {
                padding: 16px;
            }

            .client-details .row {
                flex-direction: column;
            }

            .client-details .col-md-2 {
                width: 100%;
                margin-bottom: 10px;
            }
        }
</style>
</head>
<body>
    <div class="order-container">
        <!-- Informações do pedido -->
        <div class="order-info">
            <h4 class="mb-3">
                Ordem número: <?php echo str_pad($order['id'], STR_PAD_RIGHT); ?>
            </h4>
            <div class="client-details">
                <div class="row">
                    <div class="col-md-2">
                        <div class="info-label">
                            <i class="bi bi-person"></i> Nome do Cliente
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($order['client_name']); ?></div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">
                            <i class="bi bi-laptop"></i> Modelo
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($order['device_model']); ?></div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">
                            <i class="bi bi-telephone"></i> Contatos
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($order['phone1']); ?></div>
                        <div class="info-value"><?php echo htmlspecialchars($order['phone2'] ?? '-'); ?></div>
                    </div>
                    <!-- <div class="col-md-2">
                        <div class="info-label">
                            <i class="bi bi-telephone-plus"></i> Telefone Secundário
                        </div>
                        <div class="info-value"><?php echo htmlspecialchars($order['phone2'] ?? '-'); ?></div>
                    </div> -->
                    <div class="col-md-2">
                        <div class="info-label">
                            <i class="bi bi-calendar-event"></i> Data de Abertura
                        </div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-label">
                            <i class="bi bi-calendar-check"></i> Data de Entrega
                        </div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo principal -->
        <div class="main-content">
            <!-- Coluna da esquerda -->
            <div class="content-left">
                <div>
                    <div class="section-title">
                        <i class="bi bi-key"></i> Senha do Dispositivo
                    </div>
                    <div class="device-password">
                        <div class="info-value"><?php echo htmlspecialchars($order['device_password'] ?? '-'); ?></div>
                    </div>
                </div>

                <div>
                    <div class="section-title">
                        <i class="bi bi-exclamation-triangle"></i> Defeito Reclamado
                    </div>
                    <div class="reported-issue">
                    <?php echo htmlspecialchars($order['reported_issue']); ?>
                    </div>
                </div>

                <div>
                    <div class="section-title">
                        <i class="bi bi-clipboard-data"></i> Laudo Técnico
                    </div>
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
            </div>

            <!-- Coluna da direita -->
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

                        <div class="action-button" data-bs-toggle="tooltip" title="Gerenciar negociação">
                            <i class="bi bi-currency-dollar"></i>
                            <span>Negociação</span>
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
                        <button class="action-button" data-bs-toggle="tooltip" title="Imprimir ordem de serviço">
                            <i class="bi bi-printer"></i>
                            <span>Imprimir</span>
                        </button>
                        <!-- <button class="action-button" style="background-color:var(--success-color); color: white">
                            <i class="bi bi-save"></i>
                            <span>Salvar</span>
                        </button> -->
                        <button class="action-button" style="background-color:var(--success-color)" onclick="javascript:history.go(-1)">
                            <i class="bi bi-save"></i>
                            <span>Salvar e Voltar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Container para notificações toast -->
    <div class="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicializa todos os tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

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
                        newNoteText = `\n---------------- ${today} ----------------\n\n`;
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
        const statusFlow = ['Não iniciada', 'Em andamento', 'Concluída', 'Pronto e avisado', 'Entregue'];
        
        function updateButtonAppearance(button, status, prefix = 'status') {
            button.className = 'action-button ' + prefix + '-button';
            const statusClass = `${prefix}-${status.toLowerCase().normalize('NFD')
                .replace(/[\u0300-\u036f]/g, "")
                .replace(/ /g, '-')}`;
            button.classList.add(statusClass);
            button.innerHTML = `<i class="bi bi-gear"></i> <span>${status}</span>`;
        }

        statusButton.addEventListener('click', async function() {
            const currentStatus = this.dataset.status;
            const currentIndex = statusFlow.indexOf(currentStatus);
            const nextStatus = statusFlow[(currentIndex + 1) % statusFlow.length];
            
            try {
                const response = await fetch('update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        orderId: this.dataset.orderId,
                        status: nextStatus
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    this.dataset.status = nextStatus;
                    updateButtonAppearance(this, nextStatus);
                    showToast(`Status atualizado para: ${nextStatus}`);
                } else {
                    showToast(data.message || 'Erro ao atualizar status', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao atualizar status', 'error');
            }
        });

        // Gestão de autorização
        const authButton = document.getElementById('authButton');
        const authFlow = ['Autorização', 'Solicitado', 'Autorizado'];

        authButton.addEventListener('click', async function() {
            const currentStatus = this.dataset.authStatus;
            const currentIndex = authFlow.indexOf(currentStatus);
            const nextStatus = authFlow[(currentIndex + 1) % authFlow.length];
            
            try {
                const response = await fetch('update_auth_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        orderId: this.dataset.orderId,
                        authStatus: nextStatus
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    this.dataset.authStatus = nextStatus;
                    updateButtonAppearance(this, nextStatus, 'auth');
                    showToast(`Autorização atualizada para: ${nextStatus}`);
                } else {
                    showToast(data.message || 'Erro ao atualizar autorização', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao atualizar autorização', 'error');
            }
        });

        // Definir estados iniciais
        updateButtonAppearance(statusButton, statusButton.dataset.status);
        updateButtonAppearance(authButton, authButton.dataset.authStatus, 'auth');
    </script>
</body>
</html>