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
        $html = '';
        foreach ($orders as $order) {
            $orderNumber = str_pad($order['id'], 5, '0', STR_PAD_LEFT);
            $device_model = htmlspecialchars($order['device_model']);
            $issue = htmlspecialchars(mb_strimwidth($order['reported_issue'], 0, 50, "...")); // Limita o tamanho do problema reportado
            $clientName = htmlspecialchars($order['client_name']);
            $createdAt = (new DateTime($order['created_at']))->format('d/m/Y H:i');
            $status = $order['status'] ?? 'Não iniciada';
            
            // Define as classes de estilo baseadas no status
            $statusClasses = [
                'Não iniciada' => 'btn-outline-primary',
                'Em andamento' => 'btn-outline-warning',
                'Concluída' => 'btn-outline-success'
            ];
            $statusClass = $statusClasses[$status] ?? 'btn-outline-primary';
            
            $html .= sprintf(
                '<li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <code>%s</code> - %s
                        <small class="text-muted d-block">Cliente: %s</small>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <small class="text-muted">%s</small>
                        <button class="btn btn-sm %s" onclick="event.stopPropagation();">
                            <i class="bi bi-clock"></i> %s
                        </button>
                    </div>
                </li>',
                $orderNumber,
                $device_model,
                $clientName,
                $createdAt,
                $statusClass,
                htmlspecialchars($status)
            );
        }
        return $html ?: '<li class="list-group-item">Nenhuma ordem de serviço recente encontrada.</li>';
    }
}