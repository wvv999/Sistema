<?php
class RecentOrders {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getRecentOrders($limit = 5) {
        try {
            $query = "SELECT 
                        so.id,
                        so.device_model,
                        so.reported_issue,
                        so.created_at,
                        COALESCE(so.status, 'não iniciada') as status,
                        c.name as client_name
                    FROM service_orders so
                    LEFT JOIN clients c ON so.client_id = c.id
                    ORDER BY so.created_at DESC
                    LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in RecentOrders::getRecentOrders: " . $e->getMessage());
            return [];
        }
    }
}