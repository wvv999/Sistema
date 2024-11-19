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
}