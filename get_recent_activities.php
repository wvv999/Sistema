<?php
date_default_timezone_set('America/Sao_Paulo');
header('Content-Type: application/json');
require_once 'config.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Busca as atividades mais recentes com detalhes
    $query = "SELECT a.*, 
                     u.username as user_name,
                     so.device_model,
                     c.name as client_name,
                     DATE_FORMAT(a.created_at, '%d/%m/%Y %H:%i') as formatted_date
              FROM activities a 
              LEFT JOIN users u ON a.user_id = u.id 
              LEFT JOIN service_orders so ON a.order_id = so.id
              LEFT JOIN clients c ON so.client_id = c.id
              ORDER BY a.created_at DESC 
              LIMIT 15";
    
    $stmt = $conn->query($query);
    $activities = $stmt->fetchAll();
    
    // Formata as atividades para exibição
    $formattedActivities = [];
    foreach ($activities as $activity) {
        $details = json_decode($activity['details'], true);
        
        // Formata a descrição baseada no tipo de atividade
        switch ($activity['action_type']) {
            case 'status_change':
                $description = sprintf(
                    'Alterou o status da OS #%d de "%s" para "%s" - %s',
                    $activity['order_id'],
                    $details['old_status'],
                    $details['new_status'],
                    $activity['device_model']
                );
                $icon = 'bi-arrow-left-right';
                $color = 'primary';
                break;

            case 'new_note':
                $description = sprintf(
                    'Adicionou uma nota técnica na OS #%d - %s',
                    $activity['order_id'],
                    $activity['device_model']
                );
                $icon = 'bi-pencil-square';
                $color = 'info';
                break;

            case 'new_order':
                $description = sprintf(
                    'Criou nova OS #%d para o cliente %s - %s',
                    $activity['order_id'],
                    $activity['client_name'],
                    $activity['device_model']
                );
                $icon = 'bi-plus-circle';
                $color = 'success';
                break;

            default:
                $description = $activity['description'];
                $icon = 'bi-clock-history';
                $color = 'secondary';
        }

        $formattedActivities[] = [
            'id' => $activity['id'],
            'description' => $description,
            'user_name' => $activity['user_name'],
            'formatted_date' => $activity['formatted_date'],
            'icon' => $icon,
            'color' => $color,
            'order_id' => $activity['order_id']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'activities' => $formattedActivities
    ]);

} catch (Exception $e) {
    error_log("Erro ao buscar atividades: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar atividades recentes'
    ]);
}