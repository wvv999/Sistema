<?php
session_start();

// Limpa todas as variáveis da sessão
$_SESSION = array();

// Destrói o cookie da sessão se existir
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destrói a sessão
session_destroy();

// Limpa todos os cookies de "lembrar-me"
setcookie('remember_token', '', time() - 3600, '/');
setcookie('remember_user_id', '', time() - 3600, '/');
setcookie('remember_user', '', time() - 3600, '/');

// Adiciona os mesmos parâmetros de segurança usados na criação
setcookie('remember_token', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
setcookie('remember_user_id', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
setcookie('remember_user', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Limpa o token no banco de dados
try {
    require_once 'config.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $update_query = "UPDATE users SET remember_token = NULL WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(":id", $_SESSION['user_id'] ?? 0);
    $update_stmt->execute();
} catch(PDOException $e) {
    error_log("Erro ao limpar token no logout: " . $e->getMessage());
}

// Redireciona para a página de login
header("Location: index.php");
exit;
?>