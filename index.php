<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    // Aqui você implementaria sua lógica de validação
    // Este é apenas um exemplo, você deve usar práticas seguras de autenticação
    if ($username && $password) {
        // Se "lembrar-me" estiver marcado, cria um cookie que dura 30 dias
        if ($remember) {
            setcookie("remember_user", $username, time() + (86400 * 30), "/");
        }
        
        // Aqui você faria a validação real com seu banco de dados
        $_SESSION['user'] = $username;
        header("Location: dashboard.php");
        exit();
    }
}

// Verifica se existe um cookie de "lembrar-me"
$remembered_user = isset($_COOKIE['remember_user']) ? $_COOKIE['remember_user'] : '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./styles/index.css">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="text-center mb-4">Loginnnn</h2>
            <h1>Teste</h1>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuário</label>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username" 
                           value="<?php echo $remembered_user; ?>"
                           required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           required>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" 
                           class="form-check-input" 
                           id="remember" 
                           name="remember"
                           <?php echo $remembered_user ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="remember">Lembrar de mim</label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>