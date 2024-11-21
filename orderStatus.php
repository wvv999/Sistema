<?php
class OrderStatus {
    // Mapeamento de status para classes do Bootstrap
    private static $statusClasses = [
        'não iniciada' => 'não-iniciada', // cinza
        'em andamento' => 'em-andamento',   // laranja
        'concluída' => 'concluída',      // verde
        'pronto e avisado' => 'pronto-e-avisado',  // azul
        'entregue' => 'entregue'          // verde-azulado (tranquilidade)
    ];

    // Mapeamento de status para ícones
    private static $statusIcons = [
        'não iniciada' => 'bi-clock',
        'em andamento' => 'bi-gear',
        'concluída' => 'bi-check-circle',
        'pronto e avisado' => 'bi-bell',
        'entregue' => 'bi-bag-check'
    ];

    public static function getStatusButton($status) {
        $status = strtolower($status ?? 'não iniciada');
        $buttonClass = self::$statusClasses[$status] ?? 'btn-secondary';
        $icon = self::$statusIcons[$status] ?? 'bi-clock';
        
        // Estilo customizado para o status 'entregue'
        $customStyle = '';
        if ($status === 'entregue') {
            $customStyle = 'background: #2c3e50; color: white;';
        }
        
        return sprintf(
            '<button class="btn btn-sm %s" style="min-width: 140px; %s">
                <i class="bi %s"></i> %s
            </button>',
            $buttonClass,
            $customStyle,
            $icon,
            ucfirst($status)
        );
    }
}