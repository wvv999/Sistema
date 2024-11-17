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
            
            $query = "SELECT o.id, o.device_model, o.created_at, c.name as client_name 
                     FROM orders o 
                     LEFT JOIN clients c ON o.client_id = c.id 
                     ORDER BY o.created_at DESC 
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
            $deviceInfo = htmlspecialchars($order['device_model']);
            $clientName = htmlspecialchars($order['client_name']);
            $createdAt = (new DateTime($order['created_at']))->format('d/m/Y H:i');
            
            $html .= sprintf(
                '<li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <code>%s</code> - %s
                        <small class="text-muted d-block">Cliente: %s</small>
                    </div>
                    <small class="text-muted">%s</small>
                </li>',
                $orderNumber,
                $deviceInfo,
                $clientName,
                $createdAt
            );
        }
        return $html;
    }
}