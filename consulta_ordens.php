<?php
// [Previous PHP code remains the same until the HTML]
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- [Previous head content remains the same] -->
    <style>
        /* [Previous styles remain the same] */
        
        .order-row {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .order-row:hover {
            background-color: #f8f9fa !important;
            transform: translateX(5px);
        }

        .status-cell {
            min-width: 160px;
        }

        .device-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .device-model {
            font-weight: 500;
        }

        .issue-text {
            font-size: 0.85em;
            color: #666;
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .order-count {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
        }

        .date-badge {
            background-color: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            color: #666;
        }
    </style>
</head>
<body class="bg-light">
    <!-- [Previous navigation buttons remain the same] -->

    <div class="container">
        <div class="dashboard-container">
            <div class="welcome-header">
                <h2><i class="bi bi-file-earmark-text"></i> Lista de Ordens de Serviço</h2>
                <div class="user-info">
                    <i class="bi bi-person-circle"></i>
                    Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?>
                </div>
            </div>

            <div class="search-box">
                <input type="text" class="form-control" id="orderSearch" placeholder="Buscar por cliente, dispositivo ou problema...">
            </div>

            <?php if (count($serviceOrders) > 0): ?>
                <div class="order-count">
                    Total de ordens: <?= count($serviceOrders) ?>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>OS</th>
                                <th>Cliente</th>
                                <th>Dispositivo / Problema</th>
                                <th>Datas</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($serviceOrders as $order): 
                                $orderNumber = str_pad($order['id'], STR_PAD_LEFT);
                                $status = $order['status'] ?? 'não iniciada';
                                $statusButton = OrderStatus::getStatusButton($status);
                            ?>
                                <tr class="order-row" onclick="window.location='view_order.php?id=<?= $order['id'] ?>'">
                                    <td><code class="fs-6"><?= $orderNumber ?></code></td>
                                    <td class="order-info"><?= htmlspecialchars($order['client_name']) ?></td>
                                    <td>
                                        <div class="device-info">
                                            <span class="device-model"><?= htmlspecialchars($order['device_model']) ?></span>
                                            <span class="issue-text"><?= htmlspecialchars($order['reported_issue']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="date-badge">
                                                <i class="bi bi-calendar-check"></i>
                                                <?= date('d/m/Y', strtotime($order['opening_date'])) ?>
                                            </span>
                                            <?php if ($order['delivery_date']): ?>
                                                <span class="date-badge">
                                                    <i class="bi bi-calendar2-check"></i>
                                                    <?= date('d/m/Y', strtotime($order['delivery_date'])) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="status-cell"><?= $statusButton ?></td>
                                    <td>
                                        <button class="btn btn-primary view-btn" onclick="event.stopPropagation(); window.location='view_order.php?id=<?= $order['id'] ?>'">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
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
    <script>
    const searchInput = document.getElementById('orderSearch');
    
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.order-row');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    </script>
</body>
</html>