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
    <title>Ordem de Serviço #<?php echo $order['id']; ?></title>
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
            user-select: none;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
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

        .header-info {
            background: linear-gradient(145deg, var(--accent-color), #f8f9ff);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding: 0 10px;
        }

        .header-label {
            color: #555;
            font-weight: 600;
            min-width: 120px;
        }

        .header-value {
            color: #333;
            font-weight: 500;
        }

        .technical-history {
            width: 75%;
        }

        .technical-history textarea {
            border: 1px solid #e0e0e0;
            padding: 16px;
            border-radius: var(--border-radius);
            min-height: 200px;
            width: 100%;
            font-family: inherit;
            resize: vertical;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
        }

        .reported-issue {
            border: 1px solid #e0e0e0;
            padding: 16px;
            margin: 16px 0;
            border-radius: var(--border-radius);
            min-height: 100px;
            background-color: #fff;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
        }

        .side-buttons {
            position: absolute;
            right: 24px;
            top: 200px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 180px;
        }

        .side-button {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            padding: 12px;
            text-align: center;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .side-button:hover {
            transform: translateX(-2px);
            box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
        }

        .status-button {
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        /* Estilos específicos para cada status */
        .status-nao-iniciada { 
            background-color: #ffc107 !important;
            border-color: #ffeeba !important;
            color: #856404 !important;
        }

        .status-em-andamento { 
            background-color: #007bff !important;
            border-color: #b8daff !important;
            color: #ffffff !important;
        }

        .status-concluida { 
            background-color: #28a745 !important;
            border-color: #c3e6cb !important;
            color: #ffffff !important;
        }

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
            box-shadow: 0 2px 4px rgba(74, 111, 255, 0.2);
        }

        .bottom-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 111, 255, 0.3);
        }

        .section-title {
            width: 75%;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="order-container">
        <div class="header-info">
            <div class="header-row">
                <div>
                    <span class="header-label">Nome:</span>
                    <span class="header-value"><?php echo htmlspecialchars($order['client_name']); ?></span>
                </div>
                <div>
                    <span class="header-label">Ordem:</span>
                    <span class="header-value">#<?php echo $order['id']; ?></span>
                </div>
            </div>
            <div class="header-row">
                <div>
                    <span class="header-label">Telefone 1:</span>
                    <span class="header-value"><?php echo htmlspecialchars($order['phone1']); ?></span>
                </div>
                <div>
                    <span class="header-label">Abertura:</span>
                    <span class="header-value"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></span>
                </div>
            </div>
            <div class="header-row">
                <div>
                    <span class="header-label">Telefone 2:</span>
                    <span class="header-value"><?php echo htmlspecialchars($order['phone2'] ?? '-'); ?></span>
                </div>
                <div>
                    <span class="header-label">Entrega:</span>
                    <span class="header-value"><?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?></span>
                </div>
                <div>
                    <span class="header-label">Senha:</span>
                    <span class="header-value"><?php echo htmlspecialchars($order['device_password']); ?></span>
                </div>
            </div>
        </div>

        <div class="section-title">Defeito Reclamado</div>
        <div class="reported-issue"> 
            <?php echo htmlspecialchars($order['reported_issue']); ?>
        </div>

        <div class="section-title">Laudo Técnico</div>
        <div class="technical-history">
            <textarea class="form-control" rows="8" placeholder="Histórico Técnico"></textarea>
        </div>

        <div class="side-buttons">
            <div id="statusButton" 
                 class="side-button status-button status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>"
                 data-status="<?php echo $order['status']; ?>"
                 data-order-id="<?php echo $order['id']; ?>">
                <?php echo $order['status']; ?>
            </div>
            <div class="side-button">
                <i class="bi bi-file-text"></i> Autorização
            </div>
            <div class="side-button">
                <i class="bi bi-currency-dollar"></i> Negociação
            </div>
            <div class="side-button">
                <i class="bi bi-cart"></i> Compra de Peças
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
        const statusButton = document.getElementById('statusButton');
        const statusFlow = ['Não iniciada', 'Em andamento', 'Concluída'];
        
        // Função para atualizar a aparência do botão
        function updateButtonAppearance(status) {
            // Remover todas as classes de status existentes
            statusButton.classList.remove('status-nao-iniciada', 'status-em-andamento', 'status-concluida');
            
            // Adicionar a classe apropriada baseada no status atual
            const statusClass = `status-${status.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, "").replace(/ /g, '-')}`;
            statusButton.classList.add(statusClass);
            
            // Atualizar o texto do botão
            statusButton.textContent = status;
        }

        // Definir o status inicial
        updateButtonAppearance(statusButton.dataset.status);

        statusButton.addEventListener('click', async function() {
            const currentStatus = this.dataset.status;
            const currentIndex = statusFlow.indexOf(currentStatus);
            const nextStatus = statusFlow[(currentIndex + 1) % statusFlow.length];
            
            try {
                const response = await fetch('update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        orderId: this.dataset.orderId,
                        status: nextStatus
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Atualizar o status no dataset
                    this.dataset.status = nextStatus;
                    
                    // Atualizar a aparência do botão
                    updateButtonAppearance(nextStatus);
                } else {
                    alert('Erro ao atualizar status: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao atualizar status');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>