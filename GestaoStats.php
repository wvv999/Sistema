<?php
class GestaoStats {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getOrderStats() {
        $stats = [
            'naoIniciadas' => 0,
            'emAndamento' => 0,
            'concluidas' => 0,
            'prontoAvisado' => 0,
            'entregue' => 0
        ];
        
        try {
            // Não iniciadas
            $query = "SELECT COUNT(*) as total FROM service_orders WHERE status = 'não iniciada'";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch();
            $stats['naoIniciadas'] = $result['total'] ?? 0;

            // Em andamento
            $query = "SELECT COUNT(*) as total FROM service_orders WHERE status = 'em andamento'";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch();
            $stats['emAndamento'] = $result['total'] ?? 0;

            // Concluídas
            $query = "SELECT COUNT(*) as total FROM service_orders WHERE status = 'concluída'";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch();
            $stats['concluidas'] = $result['total'] ?? 0;

            // Pronto e Avisado
            $query = "SELECT COUNT(*) as total FROM service_orders WHERE status = 'pronto e avisado'";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch();
            $stats['prontoAvisado'] = $result['total'] ?? 0;

            // Entregue
            $query = "SELECT COUNT(*) as total FROM service_orders WHERE status = 'entregue'";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch();
            $stats['entregue'] = $result['total'] ?? 0;

            return $stats;
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar estatísticas: " . $e->getMessage());
            return $stats;
        }
    }
}