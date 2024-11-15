<?php
require_once 'config.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Dados do usuário
    $username = "Wesley";
    $password = "1234";
    
    // Hash da senha
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Verifica se o usuário já existe
    $check_query = "SELECT id FROM users WHERE username = :username";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":username", $username);
    $check_stmt->execute();
    
    if($check_stmt->rowCount() > 0) {
        echo "Usuário 'Wesley' já existe no banco de dados.";
    } else {
        // Prepara e executa a query de inserção
        $query = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $stmt = $db->prepare($query);
        
        // Bind dos parâmetros
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hashed_password);
        
        // Executa a inserção
        if($stmt->execute()) {
            echo "Usuário 'Wesley' criado com sucesso!";
        } else {
            echo "Erro ao criar usuário.";
        }
    }
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>