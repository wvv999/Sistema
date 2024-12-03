<?php
class OrderStatus {
    private static $statusClasses = [
        'não iniciada' => 'não-iniciada',
        'em andamento' => 'em-andamento',
        'concluída' => 'concluída',
        'pronto e avisado' => 'pronto-e-avisado',
        'entregue' => 'entregue'
    ];

    private static $statusIcons = [
        'não iniciada' => 'bi-clock',
        'em andamento' => 'bi-gear',
        'concluída' => 'bi-check-circle',
        'pronto e avisado' => 'bi-bell',
        'entregue' => 'bi-box-seam'
    ];

    public static function getStatusButton($status) {
        $status = strtolower($status ?? 'não iniciada');
        $buttonClass = self::$statusClasses[$status] ?? 'não-iniciada';
        $icon = self::$statusIcons[$status] ?? 'bi-clock';
        
        return sprintf(
            '<div class="status-indicator %s">
                <i class="bi %s"></i> %s
            </div>',
            $buttonClass,
            $icon,
            ucfirst($status)
        );
    }

    public static function updateStatus($orderId, $newStatus) {
        try {
            $database = new Database();
            $db = $database->getConnection();

            // Atualiza o status da ordem de serviço
            $updateQuery = "UPDATE service_orders SET status = ? WHERE id = ?";
            $stmt = $db->prepare($updateQuery);
            $stmt->execute([$newStatus, $orderId]);

            // Envia a notificação para o outro setor
            $sectorQuery = "SELECT current_sector FROM users WHERE id = ?";
            $sectorStmt = $db->prepare($sectorQuery);
            $sectorStmt->execute([$_SESSION['user_id']]);
            $currentSector = $sectorStmt->fetchColumn();

            $targetSector = ($currentSector === 'atendimento') ? 'tecnica' : 'atendimento';

            $notificationQuery = "INSERT INTO notifications (type, from_user_id, order_id, created_at, viewed) 
                                  VALUES (?, ?, ?, NOW(), 0)";
            $notificationStmt = $db->prepare($notificationQuery);
            $notificationStmt->execute([$targetSector, $_SESSION['user_id'], $orderId]);

        } catch (Exception $e) {
            error_log("Erro ao atualizar status da ordem: " . $e->getMessage());
            throw $e;
        }
    }
}