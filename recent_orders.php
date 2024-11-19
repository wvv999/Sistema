<?php
require_once 'config.php';

class RecentOrders {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getRecentOrders($limit = 5) {
        try {
            $conn = $this->db->getConnection();
            
            $query = "SELECT 
                        so.id,
                        so.device_model,
                        so.reported_issue,
                        so.delivery_date,
                        so.created_at,
                        so.status,
                        c.name as client_name
                     FROM service_orders so
                     JOIN clients c ON so.client_id = c.id 
                     ORDER BY so.created_at DESC 
                     LIMIT :limit";
            
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erro ao buscar ordens recentes: " . $e->getMessage());
            return [];
        }
    }
    
    public function formatOrders($orders) {
        if (empty($orders)) {
            return '<li class="list-group-item">Nenhuma ordem de serviço recente encontrada.</li>';
        }

        $html = '';
        foreach ($orders as $order) {
            $orderNumber = str_pad($order['id'], STR_PAD_LEFT);
            $device_model = htmlspecialchars(mb_strimwidth($order['device_model'], 0, 50, "..."));
            $issue = htmlspecialchars(mb_strimwidth($order['reported_issue'], 0, 50, "..."));
            $clientName = htmlspecialchars($order['client_name']);
            $createdAt = (new DateTime($order['created_at']))->format('d/m/Y');
            $status = $order['status'] ?? 'Não iniciada';
            
            // Define as classes de estilo baseadas no status
            $statusClasses = [
                'Não iniciada' => 'btn-outline-primary',
                'Em andamento' => 'btn-outline-warning',
                'Concluída' => 'btn-outline-success'
            ];
            $statusClass = $statusClasses[$status] ?? 'btn-outline-primary';
            
            $html .= <<<HTML
            <li class="list-group-item" onclick="window.location='view_order.php?id={$order['id']}'">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <code>{$orderNumber}</code> - {$device_model} - <small>{$issue}</small>
                        <small class="text-muted d-block">Cliente: {$clientName}</small>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <small class="text-muted">{$createdAt}</small>

                        <!-- Indicador da situação -->
                        <button class="btn btn-sm {$statusClass} btn-view-order" onclick="event.stopPropagation();">
                            <i class="bi bi-clock"></i> {$status}
                        </button>

                        <!-- botão de ver -->
                        <button class="btn btn-sm btn-outline-primary btn-view-order" onclick="event.stopPropagation(); window.location='view_order.php?id={$order['id']}'">
                            <i class="bi bi-eye"></i> Ver
                        </button>
                    </div>
                </div>
            </li>
            HTML;
        }
        
        return $html;
    }
}