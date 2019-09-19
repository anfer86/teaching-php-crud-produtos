<?php

include_once("config/db_connection.php"); 

function all(){
    $conn = get_connection();
    $sql = 'SELECT id, titulo, descricao, preco FROM produto';
    $stmt = $conn->prepare($sql);
    $instances = [];
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc())
        $instances[] = $row;    
    $stmt->close();    
    $conn->close();
    return $instances;
}


function add($dados){        
    $conn = get_connection();
    $sql = 'INSERT INTO usuario (nome, email, senha) VALUES (?,?,?)';    
    $stmt = $conn->prepare($sql);    
    $stmt->bind_param(
        "sss", 
        $dados['nome'], 
        $dados['email'], 
        $dados['senha']);    
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $conn->close();
    return $result;
}

function edit($usuario){        
    $conn = get_connection();
    if (!empty($usuario['nova_senha'])){
        $sql = 'UPDATE usuario SET nome = ?, email = ?, senha = ? WHERE id = ?';    
        $stmt = $conn->prepare($sql);    
        $stmt->bind_param(
            "sssi", 
            $usuario['nome'], 
            $usuario['email'], 
            $usuario['senha'],
            $usuario['id']);
    } else {
        $sql = 'UPDATE usuario SET nome = ?, email = ? WHERE id = ?';    
        $stmt = $conn->prepare($sql);    
        $stmt->bind_param(
            "ssi", 
            $usuario['nome'], 
            $usuario['email'],            
            $usuario['id']);    
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $conn->close();
    return $result;
}

function find($usuario_id){    
    $conn = get_connection();
    $sql = 'SELECT id, nome, email FROM usuario WHERE id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);    
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $result;
}

function login($email, $senha){
    $conn = get_connection();
    $sql = 'SELECT id, email, nome FROM usuario WHERE email = ? AND senha = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $senha);    
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $result;
}

?>