<?php
// get_recent_activities.php
header('Content-Type: application/json');

require_once 'config.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT a.*, u.username as user_name 
              FROM activities a 
              LEFT JOIN users u ON a.user_id = u.id 
              ORDER BY a.created_at DESC 
              LIMIT 10";
    
    $activities = $db->query($query);
    
    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}