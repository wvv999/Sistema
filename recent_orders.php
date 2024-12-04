<?php
require_once 'config.php';

class RecentOrders {
    private $conn;

    public function __construct() {
        try {
            $database = new Database();
            $this->conn = $database->getConnection();
        } catch (Exception $e) {
            error_log("Erro ao conectar ao banco de dados: " . $e->getMessage());
            throw $e;
        }
    }

    public function getRecentOrders($limit = 5) {
        try {
            $query = "SELECT 
                        so.id,
                        so.device_model,
                        so.reported_issue,
                        so.created_at,
                        COALESCE(so.status, 'não iniciada') as status,
                        c.name as client_name,
                        so.auth_status
                    FROM service_orders so
                    LEFT JOIN clients c ON so.client_id = c.id
                    ORDER BY so.created_at DESC
                    LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($result)) {
                error_log("Nenhuma ordem encontrada");
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Erro ao buscar ordens recentes: " . $e->getMessage());
            return [];
        }
    }
}