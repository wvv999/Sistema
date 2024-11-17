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
                        id,
                        client_name,
                        device_model,
                        date_created 
                     FROM service_orders 
                     ORDER BY date_created DESC 
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
            $deviceModel = htmlspecialchars($order['device_model']);
            $clientName = htmlspecialchars($order['client_name']);
            $dateCreated = (new DateTime($order['date_created']))->format('d/m/Y H:i');
            
            $html .= sprintf(
                '<li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <code>%s</code> - %s
                        <small class="text-muted d-block">Cliente: %s</small>
                    </div>
                    <small class="text-muted">%s</small>
                </li>',
                $orderNumber,
                $deviceModel,
                $clientName,
                $dateCreated
            );
        }
        return $html ?: '<li class="list-group-item">Nenhuma ordem de serviço recente encontrada.</li>';
    }
}