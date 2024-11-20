<?php
class GestaoStats {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getOrderStats() {
        $stats = [
            'ordensAbertas' => 0,
            'ordensFinalizadasHoje' => 0,
            'ordensAtrasadas' => 0,
            'tempoMedioResolucao' => 0
        ];
        
        try {
            // Total de ordens abertas
            $query = "SELECT COUNT(*) as total FROM service_orders WHERE status != 'finalizada'";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch();
            $stats['ordensAbertas'] = $result['total'] ?? 0;

            // Ordens finalizadas hoje
            $query = "SELECT COUNT(*) as total FROM service_orders 
                     WHERE status = 'finalizada' 
                     AND DATE(updated_at) = CURDATE()";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch();
            $stats['ordensFinalizadasHoje'] = $result['total'] ?? 0;

            // Ordens atrasadas (assumindo prazo de 7 dias)
            $query = "SELECT COUNT(*) as total FROM service_orders 
                     WHERE status != 'finalizada' 
                     AND DATEDIFF(CURDATE(), created_at) > 7";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch();
            $stats['ordensAtrasadas'] = $result['total'] ?? 0;

            // Tempo médio de resolução (em dias)
            $query = "SELECT AVG(DATEDIFF(updated_at, created_at)) as media 
                     FROM service_orders 
                     WHERE status = 'finalizada'
                     AND updated_at IS NOT NULL";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch();
            $stats['tempoMedioResolucao'] = round($result['media'] ?? 0, 1);

            return $stats;
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar estatísticas: " . $e->getMessage());
            return $stats; // Retorna valores padrão em caso de erro
        }
    }
}