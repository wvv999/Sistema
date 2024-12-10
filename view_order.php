<?php
class Database {
    private $host = "localhost";
    private $db_name = "service_orders";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
        }

        return $this->conn;
    }
}

// Funções de utilidade
function checkAuthentication() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function getUserRole() {
    global $db;
    $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchColumn();
}

function hasPermission($requiredRole) {
    $userRole = getUserRole();
    $roleHierarchy = ['admin' => 3, 'tecnico' => 2, 'atendente' => 1];
    return $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
}

// Configurações globais
date_default_timezone_set('America/Sao_Paulo');
session_start();

// Inicialização da conexão com o banco
$database = new Database();
$db = $database->getConnection();
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
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --info-color: #4895ef;
            --surface-color: #f8f9fa;
            --text-primary: #2b2d42;
            --text-secondary: #8d99ae;
        }

        body {
            background-color: #f0f2f5;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .page-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .order-grid {
            display: grid;
            grid-template-columns: 300px 1fr 300px;
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            height: calc(100vh - 140px);
            overflow-y: auto;
        }

        .panel-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-group {
            margin-bottom: 1.25rem;
        }

        .info-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-value {
            font-size: 0.9375rem;
            color: var(--text-primary);
            padding: 0.5rem;
            background: var(--surface-color);
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }

        .tabs {
            display: flex;
            gap: 1rem;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
        }

        .tab {
            font-size: 0.875rem;
            color: var(--text-secondary);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tab:hover {
            background: var(--surface-color);
            color: var(--text-primary);
        }

        .tab.active {
            background: var(--primary-color);
            color: white;
        }

        .notes-container {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .notes-content {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background: var(--surface-color);
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .note-input {
            display: flex;
            gap: 0.5rem;
        }

        .note-input input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 0.875rem;
        }

        .note-input input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .action-button {
            width: 100%;
            padding: 0.75rem;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            background: white;
            color: var(--text-primary);
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 0.75rem;
        }

        .action-button:hover {
            background: var(--surface-color);
            transform: translateY(-1px);
        }

        .status-button {
            background: var(--primary-color);
            color: white;
            border: none;
        }

        .status-button:hover {
            background: var(--secondary-color);
        }

        .auth-button {
            background: var(--warning-color);
            color: white;
            border: none;
        }

        .auth-button:hover {
            background: #d61d6e;
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            z-index: 1000;
        }

        .toast {
            background: white;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: slideIn 0.3s ease;
            max-width: 300px;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Responsividade */
        @media (max-width: 1200px) {
            .order-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .panel {
                height: auto;
                min-height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="header">
            <div>
                <a href="javascript:history.back()" class="text-decoration-none text-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                <h1 class="header-title">Ordem de Serviço #<?php echo str_pad($order['id'], 6, "0", STR_PAD_LEFT); ?></h1>
                <p class="text-secondary mb-0">Aberta em <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></p>
            </div>
        </div>

        <div class="order-grid">
            <!-- Painel de Informações -->
            <div class="panel">
                <div class="panel-title">
                    <i class="bi bi-person-badge"></i> Informações do Cliente
                </div>

                <div class="info-group">
                    <div class="info-label">
                        <i class="bi bi-person"></i> Nome
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($order['client_name']); ?></div>

                    <div class="info-label">
                        <i class="bi bi-telephone"></i> Contatos
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($order['phone1']); ?></div>
                    <?php if ($order['phone2']): ?>
                        <div class="info-value"><?php echo htmlspecialchars($order['phone2']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="info-group">
                    <div class="info-label">
                        <i class="bi bi-laptop"></i> Equipamento
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($order['device_model']); ?></div>

                    <div class="info-label">
                        <i class="bi bi-key"></i> Senha
                    </div>
                    <div class="info-value"><?php echo htmlspecialchars($order['device_password'] ?? '-'); ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">
                        <i class="bi bi-exclamation-triangle"></i> Defeito Relatado
                    </div>
                    <div class="info-value">
                        <?php echo nl2br(htmlspecialchars($order['reported_issue'])); ?>
                    </div>
                </div>
            </div>

            <!-- Painel Central -->
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
                    
                    <div class="note-input">
                        <input type="text" 
                               id="newNote" 
                               placeholder="Digite sua nota técnica..."
                               onkeypress="if(event.key === 'Enter') addNote()">
                        <button class="action-button" onclick="addNote()">
                            <i class="bi bi-plus-lg"></i> Adicionar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Painel de Ações -->
            <div class="panel">
                <div class="panel-title">
                    <i class="bi bi-gear"></i> Ações
                </div>

                <button id="statusButton" 
                        class="action-button status-button"
                        data-status="<?php echo $order['status']; ?>"
                        data-order-id="<?php echo $order['id']; ?>">
                    <i class="bi bi-arrow-repeat"></i>
                    <span><?php echo $order['status']; ?></span>
                </button>

                <button id="authButton" 
                        class="action-button auth-button"
                        data-auth-status="Autorização"
                        data-order-id="<?php echo $order['id']; ?>">
                    <i class="bi bi-check2-circle"></i>
                    <span>Autorização</span>
                </button>

                <button class="action-button">
                    <i class="bi bi-cart"></i>
                    Compra de Peças
                </button>

                <button class="action-button" onclick="openHistory()">
                    <i class="bi bi-clock-history"></i>
                    Histórico
                </button>

                <button class="action-button" onclick="printOrder()">
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
                    <!-- Conteúdo do histórico será inserido aqui -->
                </div>
            </div>
        </div>
    </div>

    <!-- Container de Toast -->
    <div class="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
// Constantes e Configurações
const orderId = <?php echo $_GET['id']; ?>;
const statusFlow = ['Não iniciada', 'Em andamento', 'Concluída', 'Pronto e avisado', 'Entregue'];
const authFlow = ['Autorização', 'Solicitado', 'Autorizado'];

// Elementos do DOM
const statusButton = document.getElementById('statusButton');
const authButton = document.getElementById('authButton');
const newNoteInput = document.getElementById('newNote');
const technicalNotesDiv = document.getElementById('technicalNotes');

// Sistema de Notificações Toast
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
    toast.innerHTML = `
        <i class="bi bi-${icon}"></i>
        <span>${message}</span>
    `;
    
    document.querySelector('.toast-container').appendChild(toast);
    
    // Remover após 3 segundos
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Gerenciamento de Notas Técnicas
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
            // Formatar data atual
            const today = new Date().toLocaleDateString('pt-BR', { 
                day: '2-digit', 
                month: '2-digit', 
                year: '2-digit' 
            });
            
            // Adicionar separador de data se necessário
            let newNoteText = '';
            if (!technicalNotesDiv.textContent.includes(today)) {
                newNoteText = `\n---------------- ${today} ----------------\n\n`;
            }
            
            // Adicionar nota
            newNoteText += `${data.username}: ${noteText}\n`;
            technicalNotesDiv.innerHTML += newNoteText.replace(/\n/g, '<br>');
            
            // Limpar input e rolar para baixo
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

// Gerenciamento de Status
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
        } else {
            throw new Error(data.message || 'Erro ao atualizar status');
        }
    } catch (error) {
        console.error('Erro ao atualizar status:', error);
        showToast('Erro ao atualizar status', 'error');
    }
}

// Gerenciamento de Autorização
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
            throw new Error(data.message || 'Erro ao atualizar autorização');
        }
    } catch (error) {
        console.error('Erro ao atualizar autorização:', error);
        showToast('Erro ao atualizar autorização', 'error');
    }
}

// Atualização Visual dos Botões
function updateButtonAppearance(button, status, prefix = 'status') {
    // Remover classes anteriores
    const classes = [...button.classList];
    classes.forEach(className => {
        if (className.startsWith(prefix) && className !== `${prefix}-button`) {
            button.classList.remove(className);
        }
    });
    
    // Adicionar nova classe
    const statusClass = `${prefix}-${status.toLowerCase().normalize('NFD')
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/ /g, '-')}`;
    button.classList.add(statusClass);
    
    // Atualizar texto
    button.querySelector('span').textContent = status;
}

// Carregamento do Histórico
async function loadOrderHistory() {
    try {
        const response = await fetch('get_order_history.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ orderId: orderId })
        });

        const data = await response.json();
        
        if (data.success) {
            // Atualizar histórico de status
            const statusContainer = document.querySelector('.status-history-list');
            statusContainer.innerHTML = data.statusHistory?.length 
                ? data.statusHistory.map(item => `
                    <div class="history-item">
                        <div class="date">${item.formatted_date}</div>
                        <div class="username">${item.username}</div>
                        <div class="detail">
                            <i class="bi bi-arrow-right-circle"></i> 
                            Alterou status para: ${JSON.parse(item.details).new_status}
                        </div>
                    </div>
                `).join('')
                : '<div class="p-3 text-muted">Nenhuma alteração de status encontrada.</div>';

            // Atualizar histórico de notas
            const notesContainer = document.querySelector('.notes-history-list');
            notesContainer.innerHTML = data.notesHistory?.length
                ? data.notesHistory.map(item => `
                    <div class="history-item">
                        <div class="date">${item.formatted_date}</div>
                        <div class="username">${item.username}</div>
                        <div class="detail">${item.note}</div>
                    </div>
                `).join('')
                : '<div class="p-3 text-muted">Nenhuma nota técnica encontrada.</div>';
        } else {
            throw new Error(data.message || 'Erro ao carregar histórico');
        }
    } catch (error) {
        console.error('Erro ao carregar histórico:', error);
        showToast('Erro ao carregar histórico', 'error');
    }
}

// Verificação de Notificações
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
        .catch(error => console.error('Erro ao verificar notificações:', error));
}

// Funções Auxiliares
function openHistory() {
    const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
    loadOrderHistory();
    historyModal.show();
}

function printOrder() {
    window.open(`print_service_order.php?id=${orderId}`, '_blank');
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips do Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Status inicial
    let initialStatus = statusButton.dataset.status;
    initialStatus = statusFlow.find(status => 
        status.toLowerCase() === initialStatus.toLowerCase()
    ) || initialStatus;
    statusButton.dataset.status = initialStatus;
    updateButtonAppearance(statusButton, initialStatus);

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
            updateButtonAppearance(authButton, data.authStatus, 'auth');
        }
    })
    .catch(error => console.error('Erro:', error));
});

// Click handlers para botões de status e autorização
statusButton.addEventListener('click', function() {
    const currentStatus = statusFlow.find(status => 
        status.toLowerCase() === this.dataset.status.toLowerCase()
    ) || this.dataset.status;
    
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

// Event listener para tecla Enter no campo de nota
newNoteInput.addEventListener('keypress', function(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        addNote();
    }
});

// Verificação periódica de notificações
setInterval(checkNotifications, 5000);
</script>
</body>
</html>