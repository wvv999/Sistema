<?php
session_start();
require_once 'config.php';

// Verifica se está logado
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Verifica se o ID da ordem foi fornecido
if(!isset($_GET['id'])) {
    header("Location: clientes.php");
    exit;
}

$order_id = $_GET['id'];
$order = null;
$error = '';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Busca os detalhes da ordem e informações do cliente
    $query = "SELECT so.*, c.name as client_name, c.cpf as client_cpf 
              FROM service_orders so
              INNER JOIN clients c ON so.client_id = c.id
              WHERE so.id = ?";
              
    $stmt = $db->prepare($query);
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception("Ordem de serviço não encontrada!");
    }
    
} catch(Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Serviço #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        
        .content-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }

        .order-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .info-label {
            font-weight: bold;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .info-value {
            margin-bottom: 15px;
        }

        .device-details, .client-details {
            border-left: 4px solid #0d6efd;
            padding-left: 15px;
            margin-bottom: 20px;
        }

        .reported-issue {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            
            .content-container {
                box-shadow: none !important;
            }
            
            .order-info {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-light">
    <?php if ($error): ?>
        <div class="container">
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <a href="clientes.php" class="btn btn-primary">Voltar para Clientes</a>
        </div>
    <?php exit; endif; ?>

    <div class="container">
        <div class="content-container">
            <!-- Cabeçalho -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-file-text"></i> 
                    Ordem de Serviço #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
                </h2>
                <div class="no-print">
                    <button onclick="window.print()" class="btn btn-secondary me-2">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                    <a href="ordens_cliente.php?id=<?php echo $order['client_id']; ?>" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

            <!-- Informações do Cliente -->
            <div class="order-info">
                <h4 class="mb-3">
                    <i class="bi bi-person-circle"></i> 
                    Informações do Cliente
                </h4>
                <div class="client-details">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-label">Nome do Cliente</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['client_name']); ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">CPF</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['client_cpf']); ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Telefone Principal</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['phone1']); ?></div>
                        </div>
                        <?php if ($order['phone2']): ?>
                        <div class="col-md-6">
                            <div class="info-label">Telefone Secundário</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['phone2']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Informações do Dispositivo -->
                <h4 class="mb-3">
                    <i class="bi bi-phone"></i> 
                    Informações do Dispositivo
                </h4>
                <div class="device-details">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-label">Modelo do Dispositivo</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['device_model']); ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Data de Entrega</div>
                            <div class="info-value">
                                <?php echo date('d/m/Y', strtotime($order['delivery_date'])); ?>
                            </div>
                        </div>
                        <?php if ($order['device_password']): ?>
                        <div class="col-md-6">
                            <div class="info-label">Senha do Dispositivo</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['device_password']); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($order['pattern_password']): ?>
                        <div class="col-md-6">
                            <div class="info-label">Senha Padrão</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['pattern_password']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Problema Relatado -->
                    <div class="info-label">Problema Relatado</div>
                    <div class="reported-issue">
                        <?php echo nl2br(htmlspecialchars($order['reported_issue'])); ?>
                    </div>

                    <?php if ($order['accessories']): ?>
                    <!-- Acessórios -->
                    <div class="mt-3">
                        <div class="info-label">Acessórios</div>
                        <div class="reported-issue">
                            <?php echo nl2br(htmlspecialchars($order['accessories'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Data de Criação -->
                <div class="mt-4 text-muted">
                    <small>
                        <i class="bi bi-clock-history"></i> 
                        Ordem criada em: <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>