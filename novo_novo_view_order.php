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
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Serviço <?php echo $order['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f5f6fa;
            padding: 20px;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        .container {
            max-width: 90vw;
            padding: 10px;
            display: flex;
            gap: 5px;
            justify-content: space-around;
        }

        .left, .right {
            padding: 20px;
            height: 90vh;
            width: 25%;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .mid {
            height: 90vh;
            width: 45%;
            padding: 20px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.9rem;
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .info-value {
            color: #333;
            font-size: 1rem;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-top: 5px;
        }

        .item {
            width: 100%;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }

        .technical-report {
            flex-grow: 1;
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .technical-notes textarea {
            width: 100%;
            height: 100%;
            border: none;
            background: transparent;
            resize: none;
            padding: 10px;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .add-note-form {
            margin-top: 20px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }

        .input-group {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
        }

        #newNote {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            min-height: 40px;
            resize: vertical;
        }

        .menu-section {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 15px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .action-button {
            width: 100%;
            padding: 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: white;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .status-button {
            font-weight: 600;
            justify-content: center;
            color: white;
        }

        .status-nao-iniciada { background-color: #e74c3c; }
        .status-em-andamento { background-color: #f39c12; }
        .status-concluida { background-color: #27ae60; }
        .status-pronto-e-avisado { background-color: #3498db; }
        .status-entregue { background-color: #2c3e50; }

        .auth-button {
            font-weight: 600;
            justify-content: center;
            color: white;
        }

        .auth-autorizacao { background-color: #6c757d; }
        .auth-solicitado { background-color: #ffc107; color: #000; }
        .auth-autorizado { background-color: #28a745; }

        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .toast {
            padding: 15px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <!-- Painel Esquerdo -->
        <div class="left">
            <h4 class="mb-4">Ordem #<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></h4>
            
            <div class="item">
                <div class="info-label"><i class="bi bi-person"></i> Cliente</div>
                <div class="info-value"><?php echo htmlspecialchars($order['client_name']); ?></div>
            </div>

            <div class="item">
                <div class="info-label"><i class="bi bi-laptop"></i> Modelo</div>
                <div class="info-value"><?php echo htmlspecialchars($order['device_model']); ?></div>
            </div>

            <div class="item">
                <div class="info-label"><i class="bi bi-key"></i> Senha</div>
                <div class="info-value"><?php echo htmlspecialchars($order['device_password'] ?? '-'); ?></div>
            </div>

            <div class="item">
                <div class="info-label"><i class="bi bi-exclamation-triangle"></i> Defeito</div>
                <div class="info-value"><?php echo htmlspecialchars($order['reported_issue']); ?></div>
            </div>
        </div>

        <!-- Painel Central -->
        <div class="mid">
            <div class="technical-report">
                <div class="technical-notes">
                    <textarea id="technicalNotes" readonly><?php echo $textareaContent; ?></textarea>
                </div>
                
                <div class="add-note-form">
                    <div class="input-group">
                        <textarea id="newNote" 
                                rows="2"
                                placeholder="Digite sua nota técnica..."
                                data-autoresize></textarea>
                        <button onclick="addNote()" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Adicionar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Painel Direito -->
        <div class="right">
            <div class="item">
                <div class="info-label"><i class="bi bi-telephone"></i> Contatos</div>
                <div class="info-value">
                    <?php echo htmlspecialchars($order['phone1']); ?>
                    <?php if (!empty($order['phone2'])): ?>
                        <br><?php echo htmlspecialchars($order['phone2']); ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="item">
                <div class="info-label"><i class="bi bi-calendar"></i> Datas</div>
                <div class="info-value">
                    <div>Abertura: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
                    <div>Entrega: <?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?></div>
                </div>
            </div>

            <div class="menu-section">
                <div id="statusButton" class="action-button status-button"
                    data-status="<?php echo $order['status']; ?>"
                    data-order-id="<?php echo $order['id']; ?>">
                    <i class="bi bi-gear"></i>
                    <span><?php echo $order['status']; ?></span>
                </div>

                <div id="authButton" class="action-button auth-button"
                    data-auth-status="Autorização"
                    data-order-id="<?php echo $order['id']; ?>">
                    <i class="bi bi-check-circle"></i>
                    <span>Autorização</span>
                </div>
            </div>

            <div class="menu-section">
                <button class="action-button" onclick="loadOrderHistory()">
                    <i class="bi bi-clock-history"></i> Histórico
                </button>
                <button class="action-button" 
                    onclick="window.open('print_service_order.php?id=<?php echo $order['id']; ?>', '_blank')">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
                <button class="action-button btn-success text-white" onclick="history.back()">
                    <i class="bi bi-box-arrow-left"></i> Sair
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Histórico -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Histórico Completo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="historyTabs">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#statusHistory">
                                <i class="bi bi-clock-history"></i> Status
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#notesHistory">
                                <i class="bi bi-card-text"></i> Notas
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="statusHistory">
                            <div class="status-history-list"></div>
                        </div>
                        <div class="tab-pane fade" id="notesHistory">
                            <div class="notes-history-list"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Container para Notificações -->
    <div class="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
(function () {
    document.addEventListener("DOMContentLoaded", function () {
        initEventListeners();
        loadInitialData();
    });

    function initEventListeners() {
        document.querySelectorAll(".delete-btn").forEach(button => {
            button.addEventListener("click", function () {
                const orderId = this.dataset.id;
                if (confirm("Tem certeza que deseja excluir este pedido?")) {
                    deleteOrder(orderId);
                }
            });
        });

        document.querySelectorAll(".update-status").forEach(select => {
            select.addEventListener("change", function () {
                const orderId = this.dataset.id;
                const newStatus = this.value;
                updateOrderStatus(orderId, newStatus);
            });
        });
    }

    function deleteOrder(orderId) {
        fetch(`delete_order.php?id=${orderId}`, {
            method: "POST"
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Pedido excluído com sucesso!");
                document.getElementById(`order-${orderId}`).remove();
            } else {
                alert("Erro ao excluir pedido.");
            }
        })
        .catch(error => console.error("Erro na requisição: ", error));
    }

    function updateOrderStatus(orderId, newStatus) {
        fetch("update_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: orderId, status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Status atualizado com sucesso!");
            } else {
                alert("Erro ao atualizar status.");
            }
        })
        .catch(error => console.error("Erro na requisição: ", error));
    }

    function loadInitialData() {
        fetch("get_orders.php")
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.orders)) {
                renderOrders(data.orders);
            } else {
                console.error("Erro ao carregar pedidos.");
            }
        })
        .catch(error => console.error("Erro ao buscar dados iniciais: ", error));
    }

    function renderOrders(orders) {
        const ordersContainer = document.getElementById("orders-container");
        ordersContainer.innerHTML = "";
        
        orders.forEach(order => {
            const orderRow = document.createElement("tr");
            orderRow.id = `order-${order.id}`;
            orderRow.innerHTML = `
                <td>
                    ${order.id}
                </td>
                <td>
                    ${order.customer_name}
                </td>
                <td>
                    ${order.total_price}
                </td>
                <td>
                    <select class="update-status" data-id="${order.id}">
                        <option value="pending" ${order.status === "pending" ? "selected" : ""}>Pendente</option>
                        <option value="shipped" ${order.status === "shipped" ? "selected" : ""}>Enviado</option>
                        <option value="delivered" ${order.status === "delivered" ? "selected" : ""}>Entregue</option>
                    </select>
                </td>
                <td>
                    <button class="delete-btn" data-id="${order.id}">Excluir</button>
                </td>
            `;
            ordersContainer.appendChild(orderRow);
        });
        
        initEventListeners(); // Reaplica os event listeners nos novos elementos
    }
})();
</script>