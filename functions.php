<?php
function registerActivity($userId, $description, $orderId = null, $actionType = null, $details = null) {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $query = "INSERT INTO activities (user_id, description, order_id, action_type, details, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($query);
        return $stmt->execute([
            $userId, 
            $description, 
            $orderId, 
            $actionType,
            $details ? json_encode($details) : null
        ]);
        
    } catch (Exception $e) {
        error_log("Erro ao registrar atividade: " . $e->getMessage());
        return false;
    }
}

// Exemplo de uso:
// registerActivity($_SESSION['user_id'], 'Criou nova ordem de serviço #123', 123, 'ordem_servico');
?>