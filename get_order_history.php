<?php
date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: application/json');
require_once 'config.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Pegar o ID da ordem da requisição POST
    $data = json_decode(file_get_contents('php://input'), true);
    $orderId = $data['orderId'] ?? null;
    
    if (!$orderId) {
        throw new Exception('ID da ordem não fornecido');
    }
    
    // Buscar histórico de status
    $statusQuery = "SELECT 
        a.id,
        a.action_type,
        a.details,
        a.created_at,
        u.username,
        DATE_FORMAT(a.created_at, '%d/%m/%Y %H:%i') as formatted_date
    FROM activities a
    JOIN users u ON a.user_id = u.id
    WHERE a.order_id = :orderId 
    AND a.action_type = 'status_change'
    ORDER BY a.created_at DESC";
    
    $stmt = $db->prepare($statusQuery);
    $stmt->bindParam(':orderId', $orderId, PDO::PARAM_INT);
    $stmt->execute();
    $statusHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar histórico de notas
    $notesQuery = "SELECT 
        tn.id,
        tn.note,
        tn.created_at,
        u.username,
        DATE_FORMAT(tn.created_at, '%d/%m/%Y %H:%i') as formatted_date
    FROM technical_notes tn
    JOIN users u ON tn.user_id = u.id
    WHERE tn.order_id = :orderId
    ORDER BY tn.created_at DESC";
    
    $stmt = $db->prepare($notesQuery);
    $stmt->bindParam(':orderId', $orderId, PDO::PARAM_INT);
    $stmt->execute();
    $notesHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Log para debug
    error_log('Status History: ' . print_r($statusHistory, true));
    error_log('Notes History: ' . print_r($notesHistory, true));
    
    echo json_encode([
        'success' => true,
        'statusHistory' => $statusHistory,
        'notesHistory' => $notesHistory
    ]);

} catch (Exception $e) {
    error_log('Error in get_order_history.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}