<?php
session_start();
require_once 'config.php';

// Verificação de autenticação
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Conexão com o banco de dados
$db = (new Database())->getConnection();

// Obter dados da ordem de serviço
$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    die("Ordem de serviço não encontrada.");
}

$stmt = $db->prepare("SELECT * FROM service_orders WHERE id = :id");
$stmt->execute([':id' => $order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Ordem de serviço não encontrada.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Serviço #<?php echo htmlspecialchars($order['id']); ?></title>
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

        .tab-bar {
            display: flex;
            gap: 4px;
            margin: 24px 0 16px 0;
            border-bottom: 2px solid #eee;
            padding-bottom: 2px;
        }

        .tab {
            padding: 10px 20px;
            background-color: var(--secondary-color);
            border: 1px solid #e0e0e0;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .tab.active {
            background-color: #fff;
            border-bottom: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .side-buttons {
            position: absolute;
            right: 24px;
            top: 180px;
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
        }

        .status-nao-iniciada { background-color: #fff3cd; color: #856404; }
        .status-em-andamento { background-color: #cce5ff; color: #004085; }
        .status-concluida { background-color: #d4edda; color: #155724; }

        .bottom-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            justify-content: flex-end;
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
        </div>

        <div class="tab-bar">
            <div class="tab active">Laudo</div>
        </div>

        <div class="side-buttons">
            <div id="statusButton" 
                 class="side-button status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>"
                 data-status="<?php echo $order['status']; ?>"
                 data-order-id="<?php echo $order['id']; ?>">
                <?php echo $order['status']; ?>
            </div>
        </div>

        <div class="bottom-buttons">
            <button class="bottom-button"><i class="bi bi-save"></i> Salvar</button>
            <button class="bottom-button" onclick="javascript:history.go(-1)"><i class="bi bi-x-lg"></i> Fechar</button>
        </div>
    </div>

    <script>
        const statusButton = document.getElementById('statusButton');
        const statusFlow = ['Não iniciada', 'Em andamento', 'Concluída'];

        statusButton.addEventListener('click', async function() {
            const currentStatus = this.dataset.status;
            const nextStatus = statusFlow[(statusFlow.indexOf(currentStatus) + 1) % statusFlow.length];

            try {
                const response = await fetch('update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ orderId: this.dataset.orderId, status: nextStatus })
                });
                const data = await response.json();

                if (data.success) {
                    this.textContent = nextStatus;
                    this.dataset.status = nextStatus;
                    this.className = `side-button status-${nextStatus.toLowerCase().replace(' ', '-')}`;
                } else {
                    alert(data.message || 'Erro ao atualizar status');
                }
            } catch (error) {
                alert('Erro de conexão');
            }
        });
    </script>
</body>
</html>
