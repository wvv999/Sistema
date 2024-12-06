<?php
// Habilita a exibição de erros (remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Função para limpar cookies com segurança
function clearSecureCookie($name) {
    if (isset($_COOKIE[$name])) {
        setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
}

try {
    // Limpa o token no banco de dados antes de destruir a sessão
    if (isset($_SESSION['user_id'])) {
        require_once 'config.php';
        $database = new Database();
        $db = $database->getConnection();
        
        $update_query = "UPDATE users SET remember_token = NULL WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(":id", $_SESSION['user_id']);
        $update_stmt->execute();
    }

    // Limpa todos os cookies de remember-me
    clearSecureCookie('remember_token');
    clearSecureCookie('remember_user_id');
    clearSecureCookie('remember_user');
    
    // Limpa e destrói a sessão
    $_SESSION = array();
    
    // Destrói o cookie da sessão se existir
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
    
    // Destrói a sessão
    session_destroy();
    
    // Redireciona para a página de login
    header("Location: index.php");
    exit;
    
} catch (PDOException $e) {
    error_log("Erro no banco de dados durante logout: " . $e->getMessage());
    // Mesmo com erro no banco, continua com o logout
    header("Location: index.php");
    exit;
} catch (Exception $e) {
    error_log("Erro durante logout: " . $e->getMessage());
    // Mesmo com erro, tenta redirecionar
    header("Location: index.php");
    exit;
}
?>