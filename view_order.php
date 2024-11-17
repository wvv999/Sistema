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

        .side-button:hover {
            transform: translateX(-2px);
            box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
        }

        .status-button {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .status-nao-iniciada { 
            background-color: #fff3cd; 
            border-color: #ffeeba;
            color: #856404;
        }

        .status-em-andamento { 
            background-color: #cce5ff; 
            border-color: #b8daff;
            color: #004085;
        }

        .status-concluida { 
            background-color: #d4edda; 
            border-color: #c3e6cb;
            color: #155724;
        }

        .reported-issue {
            border: 1px solid #e0e0e0;
            padding: 16px;
            margin: 16px 0;
            border-radius: var(--border-radius);
            background-color: #fff;
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
        }

        .highlight {
            background-color: #e0ffe0 !important;
            transition: background-color 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="order-container">
        <div class="header-info">
            <div class="header-row">
                <div>
                    <span class="header-label">Nome:</span>
                    <span class="header-value"><?php echo htmlspecialchars($order['client_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div>
                    <span class="header-label">Ordem:</span>
                    <span class="header-value">#<?php echo htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
        </div>

        <div class="tab-bar">
            <div class="tab active">Laudo</div>
        </div>

        <div class="reported-issue">
            <?php echo htmlspecialchars($order['reported_issue'], ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <div class="side-buttons">
            <div id="statusButton" 
                 class="side-button status-button status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>"
                 data-status="<?php echo htmlspecialchars($order['status']); ?>"
                 data-order-id="<?php echo htmlspecialchars($order['id']); ?>">
                <?php echo htmlspecialchars($order['status']); ?>
            </div>
        </div>

        <div class="bottom-buttons">
            <button class="bottom-button">
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
                    this.textContent = nextStatus;
                    this.dataset.status = nextStatus;
                    this.classList.add('highlight');
                    setTimeout(() => this.classList.remove('highlight'), 500);
                } else {
                    alert('Erro ao atualizar status: ' + data.message);
                }
            } catch (error) {
                alert('Erro ao atualizar status');
            }
        });
    </script>
</body>
</html>
