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
                c.phone2 
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
        .container { padding-top: 2rem; }
        .order-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .status-button {
            padding: 10px 20px;
            border-radius: 20px;
            border: none;
            color: #000;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .status-nao-iniciada { background-color: #FFF3CD; }
        .status-em-andamento { background-color: #F8D7DA; }
        .status-concluida { background-color: #D4EDDA; }
        dl {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
            margin: 20px 0;
        }
        dt {
            font-weight: bold;
            text-align: right;
            padding-right: 10px;
        }
        dd {
            margin: 0;
            padding: 0 0 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="order-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Ordem de Serviço #<?php echo $order['id']; ?></h2>
                <button id="statusButton" 
                        class="status-button status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>"
                        data-status="<?php echo $order['status']; ?>"
                        data-order-id="<?php echo $order['id']; ?>">
                    <?php echo $order['status']; ?>
                </button>
            </div>

            <dl>
                <dt>Cliente:</dt>
                <dd><?php echo htmlspecialchars($order['client_name']); ?></dd>

                <dt>Telefones:</dt>
                <dd>
                    <?php 
                    echo htmlspecialchars($order['phone1']);
                    if ($order['phone2']) {
                        echo ' / ' . htmlspecialchars($order['phone2']);
                    }
                    ?>
                </dd>

                <dt>Data de Entrega:</dt>
                <dd><?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?></dd>

                <dt>Defeito Relatado:</dt>
                <dd><?php echo htmlspecialchars($order['reported_issue']); ?></dd>

                <dt>Acessórios:</dt>
                <dd><?php echo htmlspecialchars($order['accessories'] ?? 'Nenhum'); ?></dd>

                <dt>Senha do Aparelho:</dt>
                <dd><?php echo htmlspecialchars($order['device_password'] ?? 'Não informada'); ?></dd>

                <dt>Padrão de Desenho:</dt>
                <dd><?php echo htmlspecialchars($order['pattern_password'] ?? 'Não informado'); ?></dd>
            </dl>

            <div class="mt-4">
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
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