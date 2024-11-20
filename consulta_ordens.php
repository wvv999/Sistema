<?php
// Ativar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Verifica se está logado
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Inclui o arquivo de configuração do banco de dados
require_once 'config.php';

// Envolva o código da conexão e consulta em um try-catch para debug
try {
    // Cria uma nova instância da classe Database e obtém a conexão
    $db = new Database();
    $conn = $db->getConnection();

    // Consulta para buscar todas as ordens de serviço
    $sql = "SELECT so.id, so.client_id, so.phone1, so.phone2, 
                   so.created_at AS opening_date, so.delivery_date, 
                   so.reported_issue, so.accessories, 
                   so.device_password, so.pattern_password,
                   c.name AS client_name, so.status
            FROM service_orders so
            JOIN clients c ON so.client_id = c.id
            ORDER BY so.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $serviceOrders = $stmt->fetchAll();

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Ordens de Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .body{
            padding:50px;
            box-sizing: border-box;
            margin: 40px;
        }
        .container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .dashboard-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
        }

        .welcome-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .table thead {
            background-color: #f8f9fa;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .view-btn {
            padding: 2px 8px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="bg-light">
    <a href="dashboard.php" class="btn btn-outline-primary" style="position: absolute; top: 20px; left: 20px;">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>


    <div class="container">
        <div class="dashboard-container">
            <div class="welcome-header">
                <h2><i class="bi bi-file-earmark-text"></i> Lista de Ordens de Serviço</h2>
                <div class="user-info">
                    <i class="bi bi-person-circle"></i>
                    Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?>
                </div>
            </div>

            <?php if (count($serviceOrders) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Número da Ordem</th>
                                <th>Telefone 1</th>
                                <th>Telefone 2</th>
                                <th>Data de Abertura</th>
                                <th>Data de Entrega</th>
                                <th>Status</th>
                                <!-- <th>Problema Relatado</th> -->
                                <!-- <th>Acessórios</th> -->
                                <!-- <th>Senha do Dispositivo</th> -->
                                <!-- <th>Senha Padrão</th> -->
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($serviceOrders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['client_name']) ?></td>
                                    <td><?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['phone1']) ?></td>
                                    <td><?= htmlspecialchars($order['phone2']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($order['opening_date'])) ?></td>
                                    <td><?= $order['delivery_date'] ? date('d/m/Y', strtotime($order['delivery_date'])) : '-' ?></td>
                                    <td><?= htmlspecialchars($order['status']) ?></td>
                                    <!-- <td><?= nl2br(htmlspecialchars($order['reported_issue'])) ?></td> -->
                                    <!-- <td><?= htmlspecialchars($order['accessories']) ?></td> -->
                                    <!-- <td><?= htmlspecialchars($order['device_password']) ?></td> -->
                                    <!-- <td><?= htmlspecialchars($order['pattern_password']) ?></td> -->
                                    <td>
                                        <a href="view_order.php?id=<?= $order['id'] ?>" class="btn btn-primary view-btn">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?= htmlspecialchars($order['client_name']) ?></td>
                                    <td><?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['phone1']) ?></td>
                                    <td><?= htmlspecialchars($order['phone2']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($order['opening_date'])) ?></td>
                                    <td><?= $order['delivery_date'] ? date('d/m/Y', strtotime($order['delivery_date'])) : '-' ?></td>
                                    <td><?= OrderStatus::getStatusButton($order['status']) ?></td>
                                    <td>
                                        <a href="view_order.php?id=<?= $order['id'] ?>" class="btn btn-primary view-btn">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning" role="alert">
                    Nenhuma ordem de serviço encontrada.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>