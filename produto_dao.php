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
 * @return boolean o resultado da operação de insert: se inseriu retorna true, caso contrário retorna false.
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
    $success = $stmt->execute();    
    $stmt->close();
    $conn->close();
    return $success;
}

/**
 * A função find($id) recebe id de um produto retorna os dados do produto
 * se esse id existir, caso contrário, retorna um array vazio 
 * 
 * @param integer id do produto que quero procurar na tabela.
 * 
 * @return array array no formato chave => valor com os dados do produto.
 */
function find($id){    
    $conn = get_connection();
    $sql = 'SELECT id, titulo, descricao, preco FROM produto WHERE id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);    
    $stmt->execute();
    $instance = [];
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc())
        $instance = $row;    
    $stmt->close();
    $conn->close();
    return $instance;
}

/**
 * A função edit($produto) recebe os dados de um produto e atualiza um registro
 * na tabela `produto`.
 * 
 * @param array um produto na forma de array no formato chave => valor.
 * 
 * @return boolean retorna true se o registro foi alterado, caso contrário retorna false.
 */
function edit($produto){        
    $conn = get_connection();
    $sql = 'UPDATE produto SET titulo = ?, descricao = ?, preco = ? WHERE id = ?';    
    $stmt = $conn->prepare($sql);    
    $stmt->bind_param(
        "ssdi", 
        $produto['titulo'], 
        $produto['descricao'], 
        $produto['preco'],
        $produto['id']);
    $stmt->execute();
    $result = $stmt->affected_rows > 0;
    $stmt->close();
    $conn->close();
    return $result;
}

/**
 * A função delete($id) recebe o $id de um produto e apaga o registro da base de dados.
 * 
 * @param integer id do produto
 * 
 * @return boolean retorna true se o registro foi apagado, caso contrário retorna false.
 */
function delete($id){        
    $conn = get_connection();
    $sql = 'DELETE FROM produto WHERE id = ?';    
    $stmt = $conn->prepare($sql);    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->affected_rows > 0;
    $stmt->close();
    $conn->close();
    return $result;
}

function all_order_by_preco_asc(){    
    $conn = get_connection();
    $sql = 'SELECT id, titulo, descricao, preco 
            FROM produto ORDER BY preco ASC';
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

function all_order_by_preco($order){    
    $conn = get_connection();
    $sql = 'SELECT id, titulo, descricao, preco 
            FROM produto ORDER BY preco ' . $order;
    $stmt = $conn->prepare($sql);
    $instances = [];
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc())
        $instances[] = $row;    
    $stmt->close();    
    $conn->close();    
    return $result;
}

function all_order_by($column, $order){    
    $conn = get_connection();
    $sql = 'SELECT id, titulo, descricao, preco 
            FROM produto 
            ORDER BY ' . $column . ' ' . $order;
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

?>