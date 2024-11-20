<?php
// get_filtered_orders.php
header('Content-Type: application/json');

require_once 'config.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Recebe os filtros
    $data = json_decode(file_get_contents('php://input'), true);
    
    $where = ['1=1'];
    $params = [];
    
    // Aplica filtro de status
    if (!empty($data['status'])) {
        $where[] = "status = ?";
        $params[] = $data['status'];
    }
    
    // Aplica filtro de técnico
    if (!empty($data['technician'])) {
        $where[] = "technician_id = ?";
        $params[] = $data['technician'];
    }
    
    // Aplica filtro de data
    if (!empty($data['dateRange'])) {
        $dates = explode(" to ", $data['dateRange']);
        if (count($dates) == 2) {
            $where[] = "created_at BETWEEN ? AND ?";
            $params[] = date('Y-m-d', strtotime($dates[0]));
            $params[] = date('Y-m-d', strtotime($dates[1])) . ' 23:59:59';
        }
    }
    
    // Monta a ordenação
    $orderBy = match($data['sort'] ?? 'date_desc') {
        'date_asc' => 'created_at ASC',
        'status' => 'status ASC, created_at DESC',
        default => 'created_at DESC'
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