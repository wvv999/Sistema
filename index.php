<?php
// Habilita exibição de erros para debug (remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

function generateRememberToken() {
    return bin2hex(random_bytes(32));
}

// Função para limpar cookies de remember-me
function clearRememberCookies() {
    setcookie("remember_token", "", time() - 3600, "/");
    setcookie("remember_user_id", "", time() - 3600, "/");
    setcookie("remember_user", "", time() - 3600, "/");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    try {
        $database = new Database();
        $db = $database->getConnection();
        
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
                    
                    $update_query = "UPDATE users SET remember_token = :token, token_created_at = CURRENT_TIMESTAMP WHERE id = :id";
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
                    setcookie(
                        "remember_user",
                        $row['username'],
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
            
            $query = "SELECT id, username, current_sector, UNIX_TIMESTAMP(token_created_at) as token_time 
                     FROM users WHERE id = :id AND remember_token = :token AND remember_token IS NOT NULL";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $_COOKIE['remember_user_id']);
            $stmt->bindParam(":token", $_COOKIE['remember_token']);
            $stmt->execute();
            
            if($stmt->rowCount() == 1) {
                $row = $stmt->fetch();
                $token_age = time() - $row['token_time'];
                
                // Se o token tiver mais de 30 dias ou for inválido, limpa tudo
                if($token_age > (86400 * 30)) {
                    clearRememberCookies();
                    
                    // Limpa o token no banco
                    $update_query = "UPDATE users SET remember_token = NULL WHERE id = :id";
                    $update_stmt = $db->prepare($update_query);
                    $update_stmt->bindParam(":id", $row['id']);
                    $update_stmt->execute();
                } else {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['current_sector'] = $row['current_sector'] ?? 'atendimento';
                    
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                // Se não encontrou o token válido, limpa os cookies
                clearRememberCookies();
            }
        } catch(PDOException $e) {
            error_log("Erro no remember-me: " . $e->getMessage());
            // Em caso de erro, limpa os cookies para evitar loops
            clearRememberCookies();
        }
    }
}

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
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        
        .container {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            background-color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="text-center mb-4">Login</h2>
            
            <?php 
            if(!empty($login_err)){
                echo '<div class="alert alert-danger">' . htmlspecialchars($login_err) . '</div>';
            }        
            ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuário</label>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username" 
                           value="<?php echo htmlspecialchars($remembered_user); ?>"
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