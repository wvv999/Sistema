<?php
session_start();

// Limpa todas as variáveis da sessão
$_SESSION = array();

// Destrói a sessão
session_destroy();

// Limpa o cookie de "lembrar-me" se existir
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Redireciona para a página de login
header("Location: index.php");
exit;
?>