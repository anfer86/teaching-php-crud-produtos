<?php

include_once("config/db_connection.php"); 

/**
 * A função all() retorna um array com todos os produtos encontrados na 
 * tabela produto da base de dados. Cada produto também é um array, no formato
 * chave => valor, que contém os dados do produto.
 * 
 * @return array que contém os produtos e seus dados
 */
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

/**
 * A função create($produto) recebe os dados de um produto e insere um registro
 * na tabela `produto` da base de dados.
 * 
 * @param array um produto na forma de array no formato chave => valor.
 * 
 * @return 
 */
function create($produto){        
    $conn = get_connection();
    $sql = 'INSERT INTO produto (titulo, descricao, preco) VALUES (?,?,?)';    
    $stmt = $conn->prepare($sql);    
    $stmt->bind_param(
        "ssd", 
        $produto['titulo'], 
        $produto['descricao'], 
        $produto['preco']);    
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