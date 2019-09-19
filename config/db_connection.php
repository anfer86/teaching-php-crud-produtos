<?php

/**
 * These variables will be used for building mysql connection.
 * More information about these variables here:
 * https://www.php.net/manual/en/mysqli.construct.php
 */

$host = "localhost";
$username = "root";
$passwd = "";
$dbname   = "des_loja";
$port = "3306";

/**
 * Inicia sem nenhuma conexão
 */
$conn = null;

/**
  * A função `get_connection` retorna uma conexão ativa com o banco de dados.
  * Se a conexão for igual a null, isto é, a conexão não estiver ativa, então
  * cria uma nova conexão, caso contrário, retorna a conexão que já está ativa.
  * Isto é importante para que não sejam criadas múltiplas conexões ao banco 
  * de dados para cada usuário que acessa o sistema.  
  *
  * @return mysqli uma conexão com o banco de dados
  */
function get_connection(){
    // Para acessar e alterar variáveis fora da função usamos a diretiva `global`
    global $conn, $host, $username, $passwd, $dbname, $post, $port;
    if ( is_null($conn) ){
        $conn = mysqli_connect($host, $username, $passwd, $dbname, $port);
        $conn->set_charset("utf8");        
    }
    return $conn;    
}

?>