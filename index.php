<?php
session_start();
require_once 'config.php';

function generateRememberToken() {
    return bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Modificada a query para incluir current_sector
        $query = "SELECT id, username, password, current_sector FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch();
            
            if(password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['current_sector'] = $row['current_sector'] ?? 'atendimento';
                
                if($remember) {
                    $remember_token = generateRememberToken();
                    
                    $update_query = "UPDATE users SET remember_token = :token WHERE id = :id";
                    $update_stmt = $db->prepare($update_query);
                    $update_stmt->bindParam(":token", $remember_token);
                    $update_stmt->bindParam(":id", $row['id']);
                    $update_stmt->execute();
                    
                    setcookie(
                        "remember_token",
                        $remember_token,
                        [
                            'expires' => time() + (86400 * 30),
                            'path' => '/',
                            'secure' => true,
                            'httponly' => true,
                            'samesite' => 'Strict'
                        ]
                    );
                    setcookie(
                        "remember_user_id",
                        $row['id'],
                        [
                            'expires' => time() + (86400 * 30),
                            'path' => '/',
                            'secure' => true,
                            'httponly' => true,
                            'samesite' => 'Strict'
                        ]
                    );
                }
                
                header("Location: dashboard.php");
                exit();
            } else {
                $login_err = "Senha inválida.";
            }
        } else {
            $login_err = "Usuário não encontrado.";
        }
    } catch(PDOException $e) {
        error_log("Erro no login: " . $e->getMessage());
        $login_err = "Erro no login. Tente novamente.";
    }
} else {
    // Verifica se existe um token de "lembrar-me" válido
    if(!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token']) && isset($_COOKIE['remember_user_id'])) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Modificada para incluir current_sector
            $query = "SELECT id, username, current_sector FROM users WHERE id = :id AND remember_token = :token";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_COOKIE['remember_user_id']);
            $stmt->bindParam(":token", $_COOKIE['remember_token']);
            $stmt->execute();
            
            if($stmt->rowCount() == 1) {
                $row = $stmt->fetch();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['current_sector'] = $row['current_sector'] ?? 'atendimento';
                
                header("Location: dashboard.php");
                exit();
            }
        } catch(PDOException $e) {
            error_log("Erro no remember-me: " . $e->getMessage());
        }
    }
}

$remembered_user = isset($_COOKIE['remember_user']) ? $_COOKIE['remember_user'] : '';