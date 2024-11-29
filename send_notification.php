<?php
session_start();
require_once 'config.php';
require_once 'notifications.php'; // Certifique-se que este arquivo existe
header('Content-Type: application/json');

// Log para debug
error_log('Requisição recebida em send_notification.php');

if (!isset($_SESSION['user_id'])) {
    error_log('Usuário não autenticado');
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    error_log('Dados recebidos: ' . print_r($data, true));
    
    if (!isset($data['sector'])) {
        throw new Exception('Setor não especificado');
    }

    $database = new Database();
    $db = $database->getConnection();
    
    $notification = new NotificationSystem($db);
    $result = $notification->createNotification(
        $data['sector'],
        $_SESSION['user_id']
    );
    
    error_log('Notificação criada com sucesso');
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log('Erro ao criar notificação: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}