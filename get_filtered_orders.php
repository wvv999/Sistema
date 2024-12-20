<?php
date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: application/json');

require_once 'config.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Recebe os filtros
    $data = json_decode(file_get_contents('php://input'), true);
    
    $where = ['1=1'];
    $params = [];
    
    // Aplica filtro de pesquisa
    if (!empty($data['search'])) {
        $searchTerm = '%' . $data['search'] . '%';
        $where[] = "(c.name LIKE ? OR so.id LIKE ? OR so.device_model LIKE ? OR so.reported_issue LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Aplica filtro de status
    if (!empty($data['status'])) {
        $where[] = "so.status = ?";
        $params[] = $data['status'];
    }
    
    // Aplica filtro de data
    if (!empty($data['dateRange'])) {
        $dates = explode(" to ", $data['dateRange']);
        if (count($dates) == 2) {
            $where[] = "so.created_at BETWEEN ? AND ?";
            $params[] = date('Y-m-d', strtotime($dates[0]));
            $params[] = date('Y-m-d', strtotime($dates[1])) . ' 23:59:59';
        }
    }
    
    // Monta a ordenação
    $orderBy = match($data['sort'] ?? 'date_desc') {
        'date_asc' => 'so.created_at ASC',
        'status' => 'so.status ASC, so.created_at DESC',
        default => 'so.created_at DESC'
    };
    
    $query = "SELECT so.*, c.name as client_name 
              FROM service_orders so 
              LEFT JOIN clients c ON so.client_id = c.id 
              WHERE " . implode(" AND ", $where) . " 
              ORDER BY " . $orderBy;
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'orders' => $orders
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}