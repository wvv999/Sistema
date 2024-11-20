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
        --border-radius: 8px;
        --shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    body { 
        background-color: #f5f6fa;
        padding: 20px;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    .order-info {
        background: linear-gradient(145deg, var(--accent-color), #f8f9ff);
        padding: 20px;
        border-radius: var(--border-radius);
        margin-bottom: 24px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .client-details {
        border-left: 4px solid #0d6efd;
        padding-left: 15px;
        margin-top: 10px;
    }

    .info-label {
        font-weight: bold;
        color: #6c757d;
        margin-bottom: 5px;
        font-size: 0.9rem;
    }

    .info-value {
        color: #333;
        margin-bottom: 15px;
        font-size: 1rem;
    }

    .row {
        --bs-gutter-x: 1.5rem;
        --bs-gutter-y: 0;
        display: flex;
        flex-wrap: wrap;
        margin-top: calc(-1 * var(--bs-gutter-y));
        margin-right: calc(-.5 * var(--bs-gutter-x));
        margin-left: calc(-.5 * var(--bs-gutter-x));
    }

    .col-md-6 {
        flex: 0 0 auto;
        width: 50%;
        padding-right: calc(var(--bs-gutter-x) * .5);
        padding-left: calc(var(--bs-gutter-x) * .5);
    }

    h4 {
        color: #333;
        font-size: 1.2rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    h4 i {
        font-size: 1.1em;
        color: #0d6efd;
    }

    @media (max-width: 768px) {
        .col-md-6 {
            width: 100%;
        }
    }
    .order-container {
        background-color: #fff;
        border-radius: var(--border-radius);
        padding: 24px;
        box-shadow: var(--shadow);
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
    }

    /* Estilo similar ao client-details */
    .header-info {
        background: linear-gradient(145deg, var(--accent-color), #f8f9ff);
        padding: 20px;
        border-radius: var(--border-radius);
        margin-bottom: 24px;
        border: 1px solid rgba(0,0,0,0.05);
        border-left: 4px solid #0d6efd;
    }

    .header-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        padding: 0 10px;
    }

    .header-label {
        font-weight: bold;
        color: #6c757d;
        min-width: 120px;
    }

    .header-value {
        color: #333;
        font-weight: 500;
    }

    .technical-info-container {
        display: flex;
        gap: 24px;
        margin-bottom: 24px;
    }

    .technical-notes {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .reported-issue {
        background-color: #f8f9fa;
        padding: 16px;
        margin-bottom: 16px;
        border-radius: var(--border-radius);
        min-height: 100px;
        border: 1px solid rgba(0,0,0,0.05);
        border-left: 4px solid #0d6efd;
    }

    .section-title {
        font-weight: bold;
        color: #6c757d;
        margin-bottom: 12px;
        font-size: 1.1em;
    }

    .side-panel {
        width: 250px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .action-button {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        background: white;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 500;
    }

    .action-button:hover {
        transform: translateX(-2px);
        box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
    }

    /* Status buttons */
    .status-button {
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        justify-content: center;
    }

    /* Status styles */
    .status-nao-iniciada {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: white !important;
    }

    .status-em-andamento {
        background-color: #fd7e14 !important;
        border-color: #fd7e14 !important;
        color: white !important;
    }

    .status-concluida {
        background-color: #28a745 !important;
        border-color: #28a745 !important;
        color: white !important;
    }

    .status-pronto-e-avisado {
        background-color: #0dcaf0 !important;
        border-color: #0dcaf0 !important;
        color: white !important;
    }

    .status-entregue {
        background-color: #20c997 !important;
        border-color: #20c997 !important;
        color: white !important;
    }

    /* Auth button styles */
    .auth-button {
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        justify-content: center;
    }

    .auth-autorizacao {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: white !important;
    }

    .auth-solicitado {
        background-color: #ffc107 !important;
        border-color: #ffc107 !important;
        color: #000 !important;
    }

    .auth-autorizado {
        background-color: #28a745 !important;
        border-color: #28a745 !important;
        color: white !important;
    }

    /* Form controls */
    textarea.form-control {
        border: 1px solid #e0e0e0;
        padding: 16px;
        border-radius: var(--border-radius);
        min-height: 100px;
        font-family: inherit;
        resize: vertical;
        background-color: #f8f9fa;
        border-left: 4px solid #0d6efd;
    }

    textarea.form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(74, 111, 255, 0.2);
        background-color: #fff;
    }

    /* Bottom buttons */
    .bottom-buttons {
        display: flex;
        gap: 12px;
        margin-top: 24px;
        justify-content: flex-end;
    }

    .bottom-button {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 4px rgba(74, 111, 255, 0.2);
    }

    .bottom-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(74, 111, 255, 0.3);
    }
</style>
</head>
<body>
    <div class="order-container">
    <div class="order-info">
    <h4 class="mb-3">
        <i class="bi bi-person-circle"></i> 
        Informações do Cliente <?php echo str_pad($order['id'], STR_PAD_RIGHT); ?>
    </h4>
    <div class="client-details">
        <div class="row">
            <div class="col-md-6">
                <div class="info-label">Nome do Cliente</div>
                <div class="info-value"><?php echo htmlspecialchars($order['client_name']); ?></div>
            </div>
            <div class="col-md-6">
                <div class="info-label">Ordem</div>
                <div class="info-value">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
            </div>
            <div class="col-md-6">
                <div class="info-label">Telefone Principal</div>
                <div class="info-value"><?php echo htmlspecialchars($order['phone1']); ?></div>
            </div>
            <div class="col-md-6">
                <div class="info-label">Data de Abertura</div>
                <div class="info-value"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
            </div>
            <div class="col-md-6">
                <div class="info-label">Telefone Secundário</div>
                <div class="info-value"><?php echo htmlspecialchars($order['phone2'] ?? '-'); ?></div>
            </div>
            <div class="col-md-6">
                <div class="info-label">Data de Entrega</div>
                <div class="info-value"><?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?></div>
            </div>
            
        </div>
    </div>
</div>

        <div class="technical-info-container">
            <div class="technical-notes">
                <div class="col-md-6">
                    <div class="info-label">Senha do Dispositivo</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['device_password'] ?? '-'); ?></div>
                </div>
                <div>
                    <div class="section-title">Defeito Reclamado</div>
                    <div class="reported-issue"> 
                        <?php echo htmlspecialchars($order['reported_issue']); ?>
                    </div>
                </div>

                <div>
                    <div class="section-title">Laudo Técnico</div>
                    <textarea class="form-control" rows="8" placeholder="Descreva o diagnóstico técnico e os procedimentos realizados..."></textarea>
                </div>

                
            </div>

            <div class="side-panel">
                <div id="statusButton" 
                     class="action-button status-button"
                     data-status="<?php echo $order['status']; ?>"
                     data-order-id="<?php echo $order['id']; ?>">
                    <i class="bi bi-gear"></i>
                    <span><?php echo $order['status']; ?></span>
                </div>

                <div id="authButton" 
                     class="action-button auth-button auth-autorizacao"
                     data-auth-status="Autorização"
                     data-order-id="<?php echo $order['id']; ?>">
                    <i class="bi bi-check-circle"></i>
                    <span>Autorização</span>
                </div>

                <div class="action-button">
                    <i class="bi bi-currency-dollar"></i>
                    <span>Negociação</span>
                </div>

                <div class="action-button">
                    <i class="bi bi-cart"></i>
                    <span>Compra de Peças</span>
                </div>
            </div>
        </div>

        <div class="bottom-buttons">
            <button class="bottom-button">
                <i class="bi bi-printer"></i> Histórico
            </button>
            <button class="bottom-button">
                <i class="bi bi-printer"></i> Imprimir
            </button>
            <button style="background-color:#28a745" class="bottom-button">
                <i class="bi bi-save"></i> Salvar
            </button>
            <button class="bottom-button" onclick="javascript:history.go(-1)">
                <i class="bi bi-x-lg"></i> Fechar
            </button>
        </div>
    </div>

    <script>
        // Status da Ordem
        const statusButton = document.getElementById('statusButton');
        const statusFlow = ['Não iniciada', 'Em andamento', 'Concluída', 'Pronto e avisado', 'Entregue'];
        
        function updateButtonAppearance(button, status, prefix = 'status') {
            // Remover todas as classes de status existentes
            button.className = 'action-button ' + prefix + '-button';
            
            // Adicionar a classe apropriada baseada no status atual
            const statusClass = `${prefix}-${status.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, "").replace(/ /g, '-')}`;
            button.classList.add(statusClass);
            
            // Atualizar o conteúdo do botão
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
                } else {
                    alert('Erro ao atualizar status: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao atualizar status');
            }
        });

        // Autorização
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
                } else {
                    alert('Erro ao atualizar autorização: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao atualizar autorização');
            }
        });

        // Definir estados iniciais
        updateButtonAppearance(statusButton, statusButton.dataset.status);
        updateButtonAppearance(authButton, authButton.dataset.authStatus, 'auth');
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>