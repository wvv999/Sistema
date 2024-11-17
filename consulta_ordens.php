<?php
// Inclui o arquivo de configuração do banco de dados
require_once 'config.php';

// Cria uma nova instância da classe Database e obtém a conexão
$db = new Database();
$conn = $db->getConnection();

// Consulta para buscar todas as ordens de serviço
$sql = "SELECT so.id, so.client_id, so.phone1, so.phone2, 
               so.opening_date, so.delivery_date, 
               so.reported_issue, so.accessories, 
               so.device_password, so.pattern_password,
               c.name AS client_name
        FROM service_orders so
        JOIN clients c ON so.client_id = c.id
        ORDER BY so.opening_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$serviceOrders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Ordens de Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h2 class="mb-4">Ordens de Serviço</h2>
        <?php if (count($serviceOrders) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Telefone 1</th>
                            <th>Telefone 2</th>
                            <th>Data de Abertura</th>
                            <th>Data de Entrega</th>
                            <th>Problema Relatado</th>
                            <th>Acessórios</th>
                            <th>Senha do Dispositivo</th>
                            <th>Senha Padrão</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($serviceOrders as $order): ?>
                            <tr>
                                <td><?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['client_name']) ?></td>
                                <td><?= htmlspecialchars($order['phone1']) ?></td>
                                <td><?= htmlspecialchars($order['phone2']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($order['opening_date'])) ?></td>
                                <td><?= $order['delivery_date'] ? date('d/m/Y', strtotime($order['delivery_date'])) : '-' ?></td>
                                <td><?= nl2br(htmlspecialchars($order['reported_issue'])) ?></td>
                                <td><?= htmlspecialchars($order['accessories']) ?></td>
                                <td><?= htmlspecialchars($order['device_password']) ?></td>
                                <td><?= htmlspecialchars($order['pattern_password']) ?></td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
