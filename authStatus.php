<?php
class auth_Status {
    private static $authClasses = [
        'Autorização' => 'Autorização',
        'Solicitada' => 'Solicitada',
        'Autorizada' => 'Autorizada'
    ];

    private static $authIcons = [
        'Autorização' => 'bi-clock',
        'Solicitada' => 'bi-gear',
        'Autorizada' => 'bi-check-circle'
    ];

    public static function getAuthStatus($auth_status) {
        $auth_status = strtolower($auth_status ?? 'Autorização');
        $buttonClass = self::$auth_status_Classes[$auth_status] ?? 'Autorização';
        $icon = self::$statusIcons[$auth_status] ?? 'bi-clock';
        
        return sprintf(
            '<div class="status-indicator %s">
                <i class="bi %s"></i> %s
            </div>',
            $buttonClass,
            $icon,
            ucfirst($auth_status)
        );
    }
}