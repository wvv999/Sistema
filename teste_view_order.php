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
                <h1 class="h3 mb-0">Ordem de Serviço #<?php echo str_pad($order['id'], 5, "0", STR_PAD_LEFT); ?></h1>
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
                                <?php foreach ($notes as $note): ?>
                                <div class="technical-note bg-light p-3 rounded mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div>
                                            <span class="badge bg-primary me-2">Técnico</span>
                                            <span class="fw-medium"><?php echo htmlspecialchars($note['username']); ?></span>
                                        </div>
                                        <small class="text-muted"><?php echo $note['formatted_date']; ?></small>
                                    </div>
                                    <p class="mb-0">
                                        <?php echo nl2br(htmlspecialchars($note['note'])); ?>
                                    </p>
                                </div>
                                <?php endforeach; ?>

                                <!-- Add Note Form -->
                                <div class="mt-4">
                                    <div class="input-group">
                                        <textarea id="newNote" class="form-control" rows="2" 
                                                placeholder="Digite sua nota técnica..."></textarea>
                                        <button onclick="addNote()" class="btn btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i>
                                            Adicionar
                                        </button>
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
            <div class="col-3">
                <!-- Action Buttons -->
                <div class="card mb-4">
                    <div class="card-body">
                        <button id="statusButton" 
                                class="btn btn-success w-100 mb-3"
                                data-status="<?php echo $order['status']; ?>"
                                data-order-id="<?php echo $order['id']; ?>">
                            <i class="bi bi-check-circle me-2"></i>
                            <?php echo $order['status']; ?>
                        </button>
                        
                        <button id="authButton" 
                                class="btn btn-outline-primary w-100 mb-3"
                                data-auth-status="Autorização"
                                data-order-id="<?php echo $order['id']; ?>">
                            <i class="bi bi-shield-check me-2"></i>
                            Solicitar Autorização
                        </button>
                        
                        <button class="btn btn-outline-secondary w-100"
                                onclick="window.open('print_service_order.php?id=<?php echo $order['id']; ?>', '_blank')">
                            <i class="bi bi-printer me-2"></i>
                            Imprimir OS
                        </button>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Histórico</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <!-- O histórico será carregado via JavaScript -->
                        </div>
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
            // Add the new note to the list
            const notesContainer = document.querySelector('#notes');
            const noteElement = document.createElement('div');
            noteElement.className = 'technical-note bg-light p-3 rounded mb-3';
            noteElement.innerHTML = `
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        <span class="badge bg-primary me-2">Técnico</span>
                        <span class="fw-medium">${data.username}</span>
                    </div>
                    <small class="text-muted">${new Date().toLocaleDateString()}</small>
                </div>
                <p class="mb-0">${noteText}</p>
            `;
            notesContainer.insertBefore(noteElement, document.querySelector('#notes .mt-4'));
            
            document.getElementById('newNote').value = '';
            showToast('Nota adicionada com sucesso!');
            
            // Refresh order history
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
            button.querySelector('span').textContent = newStatus;
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
                showToast(data.message);
                loadOrderHistory();
            }
        })
        .catch(console.error);
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', () => {
    // Status button click handler
    const statusButton = document.getElementById('statusButton');
    const statusFlow = ['Não iniciada', 'Em andamento', 'Concluída', 'Pronto e avisado', 'Entregue'];
    
    statusButton.addEventListener('click', function() {
        const currentStatus = this.dataset.status;
        const currentIndex = statusFlow.indexOf(currentStatus);
        const nextStatus = statusFlow[(currentIndex + 1) % statusFlow.length];
        updateStatus(this, nextStatus);
    });

    // Initial load
    loadOrderHistory();

    // Set up periodic notification check
    setInterval(checkNotifications, 30000);

    // Enter key handler for notes
    document.getElementById('newNote').addEventListener('keypress', function(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            addNote();
        }
    });
});
    </script>
</body>
</html>