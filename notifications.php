<?php
class NotificationSystem {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createNotification($type, $from_user_id) {
        $query = "INSERT INTO notifications (type, from_user_id, created_at) 
                  VALUES (?, ?, NOW())";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$type, $from_user_id]);
    }

    public function getActiveUsers() {
        // Busca usuários ativos nos últimos 5 minutos
        $query = "SELECT id, username FROM users 
                  WHERE last_activity > NOW() - INTERVAL 5 MINUTE";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUserActivity($user_id) {
        $query = "UPDATE users SET last_activity = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id]);
    }
}