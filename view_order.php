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
        body { 
            background-color: #f0f0f0;
            padding: 20px;
        }
        .order-container {
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header-info {
            background-color: #e7e9f6;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .header-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .header-label {
            color: #000;
            font-weight: bold;
            min-width: 100px;
        }
        .header-value {
            color: #000;
        }
        .tab-bar {
            display: flex;
            gap: 2px;
            margin: 20px 0 10px 0;
            border-bottom: 1px solid #ccc;
        }
        .tab {
            padding: 8px 15px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
            cursor: pointer;
        }
        .tab.active {
            background-color: #fff;
            border-bottom: 1px solid #fff;
            margin-bottom: -1px;
        }
        .side-buttons {
            position: absolute;
            right: 40px;
            top: 150px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 150px;
        }
        .side-button {
            background-color: #e7e9f6;
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .side-button:hover {
            background-color: #d0d4f0;
        }
        .status-button {
            background-color: #fffacd;
            border: 1px solid #ccc;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .status-nao-iniciada { background-color: #fffacd; }
        .status-em-andamento { background-color: #ffd7d7; }
        .status-concluida { background-color: #d4edda; }
        .reported-issue {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            min-height: 100px;
        }
        .technical-history {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            min-height: 200px;
        }
        .bottom-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-end;
        }
        .bottom-button {
            background-color: #4a90e2;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .bottom-button:hover {
            background-color: #357abd;
        }
        .total-value {
            text-align: right;
            margin-top: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="order-container">
        <!-- Cabeçalho com informações principais -->
        <div class="header-info">
            <div class="header-row">
                <div>
                    <span class="header-label">Nome:</span>
                    <span class="header-value"><?php echo htmlspecialchars($order['client_name']); ?></span>
                </div>
                <div>
                    <span class="header-label">Ordem:</span>
                    <span class="header-value"><?php echo $order['id']; ?></span>
                </div>
            </div>
            <div class="header-row">
                <div>
                    <span class="header-label">Telefone1:</span>
                    <span class="header-value"><?php echo htmlspecialchars($order['phone1']); ?></span>
                </div>
                <div>
                    <span class="header-label">Abertura:</span>
                    <span class="header-value"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></span>
                </div>
            </div>
            <div class="header-row">
                <div>
                    <span class="header-label">Telefone2:</span>
                    <span class="header-value"><?php echo htmlspecialchars($order['phone2'] ?? ''); ?></span>
                </div>
                <div>
                    <span class="header-label">Entrega:</span>
                    <span class="header-value"><?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?></span>
                </div>
            </div>
        </div>

        <!-- Defeito Reclamado -->
        <div>
            <strong>Defeito Reclamado</strong>
            <div class="reported-issue">
                <?php echo htmlspecialchars($order['reported_issue']); ?>
            </div>
        </div>

        <!-- Abas -->
        <div class="tab-bar">
            <div class="tab active">Laudo</div>
            <div class="tab">Defeito</div>
            <div class="tab">Equipamentos</div>
            <div class="tab">Cliente</div>
            <div class="tab">Peças e Serviços</div>
            <div class="tab">Movimentação</div>
        </div>

        <!-- Área principal -->
        <div class="technical-history">
            <textarea class="form-control" rows="8" placeholder="Histórico Técnico"></textarea>
        </div>

        <!-- Botões laterais -->
        <div class="side-buttons">
            <div id="statusButton" 
                 class="side-button status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>"
                 data-status="<?php echo $order['status']; ?>"
                 data-order-id="<?php echo $order['id']; ?>">
                <?php echo $order['status']; ?>
            </div>
            <div class="side-button">Autorização</div>
            <div class="side-button">Negociação</div>
            <div class="side-button">Compra de Peças</div>
            <div class="total-value">
                Valor Total<br>
                R$ 0,00
            </div>
        </div>

        <!-- Botões inferiores -->
        <div class="bottom-buttons">
            <button class="bottom-button">Imprimir Histórico da OS</button>
            <button class="bottom-button">Salvar</button>
            <button class="bottom-button">Imprimir</button>
            <button class="bottom-button" onclick="javascript:history.go(-1)">Fechar</button>
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
                    // Atualiza o botão
                    this.textContent = nextStatus;
                    this.dataset.status = nextStatus;
                    
                    // Remove todas as classes de status
                    this.classList.remove('status-nao-iniciada', 'status-em-andamento', 'status-concluida');
                    
                    // Adiciona a nova classe de status
                    this.classList.add('status-' + nextStatus.toLowerCase().replace(' ', '-'));
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