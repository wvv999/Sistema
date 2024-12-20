<?php
date_default_timezone_set('America/Sao_Paulo');

class Database {
    private $host;
    private $database;
    private $user;
    private $password;
    private $conn;

    public function __construct() {
        // Carrega configurações do arquivo .env
        $env = parse_ini_file('.env');
        
        $this->host = $env['DB_HOST'] ?? 'localhost';
        $this->database = $env['DB_NAME'] ?? '';
        $this->user = $env['DB_USER'] ?? '';
        $this->password = $env['DB_PASS'] ?? '';
    }

    public function getConnection() {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->database . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            $this->conn = new PDO($dsn, $this->user, $this->password, $options);
            return $this->conn;
            
        } catch(PDOException $e) {
            // Log do erro real para arquivo de log
            error_log("Erro de conexão DB: " . $e->getMessage());
            
            // Mensagem genérica para o usuário
            throw new Exception("Erro ao conectar com o banco de dados. Tente novamente mais tarde.");
        }
    }
}