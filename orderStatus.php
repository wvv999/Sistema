<?php
class OrderStatus {
    // Mapeamento de status para classes do Bootstrap
    private static $statusClasses = [
        'não iniciada' => 'btn-secondary',
        'em andamento' => 'btn-primary',
        'aguardando peça' => 'btn-warning',
        'aguardando aprovação' => 'btn-info',
        'concluída' => 'btn-success',
        'cancelada' => 'btn-danger'
    ];

    // Mapeamento de status para ícones
    private static $statusIcons = [
        'não iniciada' => 'bi-clock',
        'em andamento' => 'bi-gear',
        'aguardando peça' => 'bi-box',
        'aguardando aprovação' => 'bi-hourglass',
        'concluída' => 'bi-check-circle',
        'cancelada' => 'bi-x-circle'
    ];

    public static function getStatusButton($status) {
        $status = strtolower($status ?? 'não iniciada');
        $buttonClass = self::$statusClasses[$status] ?? 'btn-secondary';
        $icon = self::$statusIcons[$status] ?? 'bi-clock';
        
        return sprintf(
            '<button class="btn btn-sm %s" style="min-width: 140px;">
                <i class="bi %s"></i> %s
            </button>',
            $buttonClass,
            $icon,
            ucfirst($status)
        );
    }
}