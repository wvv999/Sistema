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
}