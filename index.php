<?php
// Início do arquivo
session_start();
require_once 'config.php';

// Função para debug - você pode remover depois
function debug_log($message) {
    error_log("[DEBUG] " . $message);
}

// Log do domínio atual
debug_log("Domain: " . $_SERVER['HTTP_HOST']);

// Log do estado atual dos cookies
debug_log("Cookie remember_token: " . (isset($_COOKIE['remember_token']) ? 'presente' : 'ausente'));
debug_log("Cookie remember_user_id: " . (isset($_COOKIE['remember_user_id']) ? 'presente' : 'ausente'));
debug_log("Cookie remember_user: " . (isset($_COOKIE['remember_user']) ? 'presente' : 'ausente'));

function generateRememberToken() {
    return bin2hex(random_bytes(32));
}

function clearRememberCookies() {
    $cookie_options = [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Strict'
    ];
    
    setcookie("remember_token", "", $cookie_options);
    setcookie("remember_user_id", "", $cookie_options);
    setcookie("remember_user", "", $cookie_options);
    
    debug_log("Cookies removidos");
}

debug_log("Iniciando verificação");

// Verifica autologin primeiro
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token']) && isset($_COOKIE['remember_user_id'])) {
    debug_log("Cookies encontrados - Token: " . substr($_COOKIE['remember_token'], 0, 10) . "... ID: " . $_COOKIE['remember_user_id']);
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM users 
                  WHERE id = :id 
                  AND remember_token = :token 
                  AND remember_token_expires > NOW()";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $_COOKIE['remember_user_id']);
        $stmt->bindParam(":token", $_COOKIE['remember_token']);
        $stmt->execute();
        
        debug_log("Consulta executada - Rows: " . $stmt->rowCount());
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            debug_log("Usuário encontrado - Iniciando sessão");
            
            // Configura a sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['current_sector'] = $user['current_sector'] ?? 'atendimento';
            
            // Renova os cookies e o token
            $new_token = generateRememberToken();
            $token_expires = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 dias
            
            $update_query = "UPDATE users SET 
                            remember_token = :token,
                            remember_token_expires = :expires 
                            WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(":token", $new_token);
            $update_stmt->bindParam(":expires", $token_expires);
            $update_stmt->bindParam(":id", $user['id']);
            $update_stmt->execute();
            
            $cookie_options = [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Strict'
            ];
            
            setcookie('remember_token', $new_token, $cookie_options);
            setcookie('remember_user_id', $user['id'], $cookie_options);
            setcookie('remember_user', $user['username'], $cookie_options);
            
            debug_log("Cookies renovados - Redirecionando");
            header("Location: dashboard.php");
            exit();
        } else {
            debug_log("Usuário/token não encontrado ou expirado - Limpando cookies");
            clearRememberCookies();
        }
    } catch(PDOException $e) {
        debug_log("Erro no banco: " . $e->getMessage());
        clearRememberCookies();
    }
}

// Processa o login normal via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    debug_log("Tentativa de login para usuário: " . $username);

    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($password, $row['password'])) {
                debug_log("Login bem sucedido para: " . $username);
                
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['current_sector'] = $row['current_sector'] ?? 'atendimento';
                
                if($remember) {
                    debug_log("Remember-me ativado");
                    $remember_token = generateRememberToken();
                    $token_expires = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 dias
                    
                    $update_query = "UPDATE users SET 
                                    remember_token = :token,
                                    remember_token_expires = :expires 
                                    WHERE id = :id";
                    $update_stmt = $db->prepare($update_query);
                    $update_stmt->bindParam(":token", $remember_token);
                    $update_stmt->bindParam(":expires", $token_expires);
                    $update_stmt->bindParam(":id", $row['id']);
                    $update_stmt->execute();
                    
                    $cookie_options = [
                        'expires' => time() + (86400 * 30),
                        'path' => '/',
                        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ];
                    
                    setcookie('remember_token', $remember_token, $cookie_options);
                    setcookie('remember_user_id', $row['id'], $cookie_options);
                    setcookie('remember_user', $row['username'], $cookie_options);
                    
                    debug_log("Cookies de remember-me configurados");
                }
                
                header("Location: dashboard.php");
                exit();
            } else {
                debug_log("Senha inválida para: " . $username);
                $login_err = "Senha inválida.";
            }
        } else {
            debug_log("Usuário não encontrado: " . $username);
            $login_err = "Usuário não encontrado.";
        }
    } catch(PDOException $e) {
        debug_log("Erro no login: " . $e->getMessage());
        $login_err = "Erro no login. Tente novamente.";
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