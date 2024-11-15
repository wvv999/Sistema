<?php
// session_start();
// require_once 'config.php';

// Verifica se está logado (proteção da página)
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
class Database {
    private $host = "localhost";
    private $database = "u120179821_database";
    private $user = "u120179821_user";
    private $password = "493827Gp.";
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->database,
                $this->user,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "Erro na conexão: " . $e->getMessage();
        }

        return $this->conn;
    }
}