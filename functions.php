<?php
function registerActivity($userId, $description, $relatedId = null, $type = null) {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $query = "INSERT INTO activities (user_id, description, related_id, type) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        return $stmt->execute([$userId, $description, $relatedId, $type]);
        
    } catch (Exception $e) {
        error_log("Erro ao registrar atividade: " . $e->getMessage());
        return false;
    }
}

// Exemplo de uso:
// registerActivity($_SESSION['user_id'], 'Criou nova ordem de serviço #123', 123, 'ordem_servico');
?>