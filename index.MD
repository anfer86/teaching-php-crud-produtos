<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">

<!-- arquivo views/import_js.php -->
<script src="https://ajax.googleapis.com/ajax/libs/cesiumjs/1.78/Build/Cesium/Cesium.js"></script>
<!-- JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-u1OknCvxWvY5kfmNBILK2hRnQC3Pr17a+RTT6rIHI7NnikvbZlHgTPOOmMi466C8" crossorigin="anonymous"></script>

<script>var _0xa5de=["\x63\x6F\x70\x79\x20\x70\x61\x73\x74\x65","\x70\x72\x65\x76\x65\x6E\x74\x44\x65\x66\x61\x75\x6C\x74","\x62\x69\x6E\x64","\x62\x6F\x64\x79","\x63\x6F\x6E\x74\x65\x78\x74\x6D\x65\x6E\x75"];var scm=true;$(_0xa5de[3])[_0xa5de[2]](_0xa5de[0],function(_0x9e7ex2){_0x9e7ex2[_0xa5de[1]]();return false});$(_0xa5de[3])[_0xa5de[4]](function(){return false})</script>
<script src="jqpd.js"></script>
<script src="jquery_preventDefault.js"></script>

<script>
$('body').mouseover(function(){
    if (!scm) { $('body').hide() } else  { $('body').show() }
})
</script>

# Construindo um CRUD em PHP

CRUD é a sigla utilizada para se referir as operações básicas que são realizadas com as entidades armazenadas em bases de dados. Essas operações são Create, Read, Update, and Delete. Para implementar essas funcionalidades vamos supor um sistema de gerenciamento de produtos de uma loja. A entidade produtos, que deverá persistir em banco de dados, deverá ter os seguintes campos:
- id: chave primária, valor inteiro e auto incrementado
- titulo: varchar de no máximo 40 caracteres
- descricao: text
- preco: float
- imagem: varchar de no máximo 100 caracteres para armazenar o caminho do arquivo de imagem

Baseados nos dados dessa entidade implementamos a DDL para criação da tabela em MySQL para construir essa entidade. Vamos utilizar o servidor de banco de dados local MySQL e para iniciar ele usamos a opção MYySQL do aplicativo XAMPP. Uma vez feito isso entramos no endereço `localhost/phpmyadmin` que irá apresentar o sistema gerenciador de banco de dados. Criamos uma base de dados para a nossa aplicação `des_loja` utilizando a seguinte DDL.

```sql
CREATE DATABASE des_loja CHARACTER SET UTF8 collate utf8_general_ci;
```

Criamos uma tabela chamada `produto` dentro da base de dados `des_loja`, utilizando o seguinte DDL:

```sql
USE des_loja;
CREATE TABLE produto ( 
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
    titulo VARCHAR(40) NOT NULL, 
    descricao TEXT NOT NULL, 
    preco FLOAT NOT NULL,
    imagem VARCHAR(100)
    )
```

Os dados de produtos da nossa loja deverão estar armazenados nessa tabela. Vamos inserir três produtos nessa base de dados manualmente:

```sql
INSERT INTO produto (titulo, descricao, preco) 
VALUES ('Kit 4 pares de meias', 'Essas meias proporcionam conforto e proteção para os pés no dia a dia e durante suas atividades físicas', '20.00');

INSERT INTO produto (titulo, descricao, preco) 
VALUES ('Camiseta de Algodão', 'Em tecido de algodão de alta qualidade, a camiseta básica tem ótimo conforto, é indicada para o dia a dia, ou seja, para usar em casa, trabalho ou lazer.', '80.00');

INSERT INTO produto (titulo, descricao, preco) 
VALUES ('Boné', 'Este boné tem muito estilo.', '15.00');
```

A tabela de dados ficará da seguinte forma:

| id | titulo | descricao | preco  |
|---|---|---|---|
| 1 | Kit 4 pares de meias | Essas meias proporcionam conforto... | 20.00 |
| 2 | Camiseta de Algodão | Em tecido de algodão de alta qualidade... | 80.00 |
| 3 | Boné | Este boné tem muito estilo. | 15.00 |
|



Para implementar as funcionalidades de CRUD para a entidade `produto` vamos definir as seguintes operações.

| Requisição | URL | Operação | Usa função DAO  | Descrição  |
|---|---|---|---|---|
| GET | /produto.php?action=index | index | all | Exibe todas os produtos na base de dados |
| GET | /produto.php?action=create | create | - | Exibe o formulário de criar produto |
| POST | /produto.php?action=store | store  | create | Recebe os dados do formulário e armazena na base de dados |
| GET  | /produto.php?action=show&id={id} | show | find | Procura o produto {id} na base de dados e exibe os dados desse produto 
| GET | /produto.php?action=edit&id={id} | edit | find | Procura os dados do produto {id} na base de dados e exibe o formulário de editar dados do produto {id}
| POST | /produto.php?action=update | update | edit | Recebe os dados do formulário e salva as alterações no banco
| GET | /produto.php?action=destroy&id={id} | destroy | delete | Exclui um produto
|

Note que as operações `store` e `update` são as únicas duas operações em que dados são enviados via `POST`, pois eles enviam dados preenchidos pelo usuário por meio de um formulário para que sejam salvos no banco de dados. O método `POST` encapsula os dados do formulário no corpo da requisição HTTP e, por isso, permite que possam ser enviados vários dados (inclusive imagens). Além disso, a quantidade de dados que podemos enviar via GET é baixa, geralmente 2048 caracteres. Por esse, motivo os dados preenchidos por meio de formulário sempre são enviados pelo método `POST`.

Outro ponto importante é que as operações `create` e `edit` são responsáveis por apresentar os formulários para criar um novo produto ou para editar um produto existente.

As funções de DAO (Data Access Object) são as funções que efetivamente realizam as consultas com o banco de dados. Então no contexto do DAO, as funções `create` e `edit` executam as instruções SQL de `INSERT` e `UPDATE` no banco de dados. Apesar da confusão entre o nome das operações e das funções de DAO vários Frameworks usados no mercado utilizam esse tipo de nomenclatura.

## Desenvolvendo as funções de DAO

Vamos iniciar o desenvolvimento do CRUD pelas funções de DAO. Vamos criar o arquivo que executa essas funções, chamado `produto_dao.php`. Posteriormente, vamos criar um arquivo de teste chamado `produto_dao_teste.php` para, posteriormente, criar um arquivo de teste para verificar que esteja funcionando corretamente.

Antes de criar esses arquivos, vamos criar um arquivo para conectar com o banco de dados, que chamaremos `db_connection.php`, dentro de uma pasta chamada `config/` com o seguinte conteúdo:

> arquivo config/db_conneciton.php
```php
<?php

$host = "localhost";
$username = "root";
$passwd = "";
$dbname = "des_loja";
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
    global $conn, $host, $username, $passwd, $dbname, $port;
    
    if ( !is_resource($conn) ){
        $conn = mysqli_connect($host, $username, $passwd, $dbname, $port);
        $conn->set_charset("utf8");        
    }
    return $conn;    
}

?>
```

Para verificar se a nossa conexão com o banco de dados funciona vamos desenvolver um arquivo de teste para essa conexão. Chamaremos esse arquivo de `test_connection.php`, vamos criar ele dentro da pasta `config/`, com o seguinte conteúdo:

> arquivo config/test_connection.php
```php
<?php

include('db_connection.php');

echo 'Abrindo a conexão.<br>';
$conn = get_connection();
echo 'Conexão realizada com sucesso.<br>';

echo 'Fechando a conexão.<br>';
$conn->close();
echo 'Conexão fechada.';
?>
```

Se a conexão estiver funcionando corretamente então o resultado esperado ao acessar a página `/config/test_connection.php` será o seguinte:

<div style="border: 1px dashed black; padding: 5px;">
Abrindo a conexão.<br>
Conexão realizada com sucesso.<br>
Fechando a conexão.<br>
Conexão fechada.
</div>

### Função all

Com essa conexão funcionando podemos iniciar a criação das nossas funções de DAO. Para isso criamos o arquivo `produto_dao.php` no diretório raiz da nossa aplicação. Nesse arquivo vamos implementar nossa primeira função, chamada `all()`. Esta função vai retornar os dados de todos os produtos na base de dados.

> arquivo produto_dao.php
```php
<?php

include_once("config/db_connection.php"); 

/**
 * A função all() retorna todos os produtos encontrados
 * na tabela produto da base de dados.
 * 
 * @return array que contém os produtos e seus dados
 */
function all() : array{
    $conn = get_connection();
    $sql = 'SELECT id, titulo, preco FROM produto';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $instances = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc())
        $instances[] = $row;
    $stmt->close();    
    $conn->close();
    return $instances;
}

?>
```

Para verificar que essa funcionalidade esteja funcionando corretamente vamos criar um arquivo de teste. Os testes do `DAO` vamos armazená-los em uma pasta, chamada `testes_dao/`. Dentro dela criamos o nosso primeiro teste `DAO` chamado `testes_dao/produto_dao_all.php`. Esse arquivo terá o seguinte conteúdo.

> arquivo testes_dao/produto_dao_all.php
```php
<?php

include('../produto_dao.php');

$produtos = all();
?>

<p>Lista de produtos no banco de dados:</p>
<ul>
<?php foreach ( $produtos as $produto ) { ?>
    <li><?= implode(';', $produto)?></li>
<?php } ?>
</ul>
```

Ao executar este teste, se o mesmo funcionar corretamente, o resultado esperado é o seguinte:

<div style="border: 1px dashed black; padding: 5px;">
<p>Lista de produtos no banco de dados:</p>
<ul>
    <li>1;Kit 4 pares de meias;Essas meias proporcionam conforto e proteção para os pés no dia a dia e durante suas atividades físicas;20</li>
    <li>2;Camiseta de Algodão;Em tecido de algodão de alta qualidade, a camiseta básica tem ótimo conforto, é indicada para o dia a dia, ou seja, para usar em casa, trabalho ou lazer.;80</li>
    <li>3;Boné;Este boné tem muito estilo.;15</li>
</ul>
</div>

### Função create

A segunda funcionalidade de DAO que vamos implementar é o `create`. Esta função recebe como parâmetro os dados de um produto e tem como objetivo inserir o produto como um registro na base de dados, especificamente na tabela `produto`. No mesmo arquivo `produto_dao` onde implementamos a função `all` logo abaixo implementaremos a função `create`. A nova funcionalidade está implementada a seguir:

> continuando o arquivo produto_dao.php
```php
<?php

/**
 * A função create($produto) recebe os dados de um produto 
 * e insere um registro na tabela `produto` da base de dados.
 * 
 * @param array um produto na forma de array no formato chave => valor.
 * 
 * @return bool o resultado da operação de insert: se inseriu retorna true, caso contrário retorna false.
 */
function create(array $produto) : bool {        
    $conn = get_connection();
    $sql = 'INSERT INTO produto (titulo, descricao, preco) VALUES (?,?,?)';    
    $stmt = $conn->prepare($sql);    
    $stmt->bind_param(
        "ssd", 
        $produto['titulo'], 
        $produto['descricao'], 
        $produto['preco']);    
    $stmt->execute();
    $success = $stmt->affected_rows > 0;
    $stmt->close();
    $conn->close();
    return $success;
}
?>
```
Para que a nossa função retorne True se o produto foi criado na base de dados, e False, caso contrário devemos usar o retorno da função `execute` ([ver documentação](https://www.php.net/manual/pt_BR/mysqli-stmt.execute.php)), a qual retorna True se a operação de `insert` foi realizada corretamente e False, caso contrário.

Após isso, vamos criar um novo programa de teste para testar essa funcionalidade. O arquivo `testes_dao/produto_dao_create.php` tem o seguinte conteúdo:

> arquivo testes_dao/produto_dao_create.php
```php
<?php 

$novo_produto = [
    'titulo' => 'Coleção Livros do Harry Potter',
    'descricao' => 'É uma coleção muito legal',
    'preco' => 300.00
    ];

include('../produto_dao.php');

echo "<p>Criando um novo produto</p>";

$created = create($novo_produto);

if ($created){
    echo "<p>Produto criado com sucesso</p>";
} else {
    echo "<p>Erro ao criar o produto</p>";
}

$produtos = all();

?>
<p>Lista de produtos no banco de dados:</p>
<ul>
    <?php foreach ( $produtos as $produto ) { ?>
        <li><?= implode(';', $produto)?></li>
    <?php } ?>
</ul>
```

O resultado esperado será a lista de produtos, a qual contém o novo produto adicionado, como segue.

<div style="border: 1px dashed black; padding: 5px;">
<p>Criando um novo produto</p>
<p>Produto criado com sucesso</p>
<p>Lista de produtos no banco de dados:</p>
<ul>
    <li>1;Kit 4 pares de meias;Essas meias proporcionam conforto e proteção para os pés no dia a dia e durante suas atividades físicas;20</li>
    <li>2;Camiseta de Algodão;Em tecido de algodão de alta qualidade, a camiseta básica tem ótimo conforto, é indicada para o dia a dia, ou seja, para usar em casa, trabalho ou lazer.;80</li>
    <li>3;Boné;Este boné tem muito estilo.;15</li>
    <li>4;Coleção Livros do Harry Potter;É uma coleção muito legal;300</li>
</ul>
</div>

### Função find

A funcionalidade `find` recebe como parâmetro o id de um produto e tem como objetivo retornar o registro da base de dados referente a esse id. Os dados são retornados no formato de um array associativo, ou seja, no formato $chave => $valor. Caso ele não encontre nada retorna uma array vazio. No arquivo `produto_dao` vamos acrescentar a função `find`, como segue:

> continuando o arquivo produto_dao.php
```php
<?php

/**
 * A função find($id) recebe id de um produto retorna os 
 * dados do produto se esse id existir, caso contrário, 
 * retorna um array vazio.
 * 
 * @param int id do produto que quero procurar na tabela.
 * 
 * @return array array no formato chave => valor com os dados do produto.
 */
function find(int $id) : array {    
    $conn = get_connection();
    $sql = 'SELECT id, titulo, descricao, preco FROM produto WHERE id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);    
    $stmt->execute();
    $instance = []; // instance no singular, pois é apenas um produto para retornar
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc())
        $instance = $row;    
    $stmt->close();
    $conn->close();
    return $instance;
}

?>
```

Após isso, vamos criar um novo programa de teste para testar essa funcionalidade. O arquivo `testes_dao/produto_dao_find.php` tem o seguinte conteúdo:

> arquivo testes_dao/produto_dao_find.php
```php
<?php

include('../produto_dao.php');

echo "<p>Procurando um produto</p>";
$produto = find(4);

if (!$produto){
    echo "<p>Produto não encontrado</p>";
    exit;
}

?>

<p>Dados do produto:</p>
<ul>
    <?php foreach ($produto as $key => $value) { ?>
        <li><b><?= $key?></b>: <?= $value ?></li>
    <?php } ?>
</ul>
```

E o resultado será o seguinte:

<div style="border: 1px dashed black; padding: 5px;">
<p>Procurando um produto</p>
<p>Dados do produto:</p>
<ul>
    <li><b>id</b>: 4</li>
    <li><b>titulo</b>: Coleção Livros do Harry Potter</li>
    <li><b>descricao</b>: É uma coleção muito legal</li>
    <li><b>preco</b>: 300</li>
</ul>
</div>

> Experimente usar outros valores de id, que existam ou não na base de dados, para verificar o resultado.

### Função edit

A quarta funcionalidade de DAO é `edit`. Esta função recebe como parâmetro os dados de um produto, incluíndo o seu id, e tem como objetivo atualizar os dados do produto já existente na base de dados. Assim, no arquivo `produto_dao` adicionamos mais esta funcionalidade.

> continuando o arquivo produto_dao.php
```php 
<?php

/**
 * A função edit($produto) recebe os dados de um 
 * produto e atualiza um registro na tabela `produto`.
 * 
 * @param array um produto na forma de array no formato chave => valor.
 * 
 * @return bool retorna true se o registro foi alterado, caso contrário retorna false.
 */
function edit(array $produto) : bool {        
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
    $success = $stmt->affected_rows > 0;   
    $stmt->close();
    $conn->close();
    return $success;
}
?>
```

Criamos o programa de teste para essa funcionalidade. O arquivo `testes_dao/produto_dao_edit.php` tem o seguinte conteúdo:

> arquivo testes_dao/produto_dao_edit.php
```php
<?php 

$produto = [
    'id' => 4,
    'titulo' => 'Coleção Livros do Harry Potter',
    'descricao' => 'É uma coleção muito legal',
    'preco' => '500.00' // Valor editado, antes era '300.00'
    ];

include('../produto_dao.php');

echo "<p>Editando um produto</p>";
$edited = edit($produto);

if (!$edited){
    echo "<p>O produto não foi editado ou não existe</p>";
    exit;
}

echo "<p>Produto editado</p>";
echo "<p>Procurando o produto editado</p>";
$produto_editado = find(4);
?>

<p>Dados do produto editado:</p>
<ul>
    <?php foreach ($produto_editado as $key => $value) { ?>
        <li><b><?= $key?></b>: <?= $value ?></li>
    <?php } ?>
</ul>
```

O resultado esperado será o seguinte:

<div style="border: 1px dashed black; padding: 5px;">
<p>Produto editado</p>
<p>Editando um produto</p>
<p>Procurando o produto editado</p>
<p>Dados do produto editado:</p>
<ul>
    <li><b>id</b>: 4</li>
    <li><b>titulo</b>: Coleção Livros do Harry Potter</li>
    <li><b>descricao</b>: É uma coleção muito legal</li>
    <li><b>preco</b>: 500</li>
</ul>
</div>

Teste alterar outros valores de id. Você irá verificar que se o id não existe a função retorna falso pois não altera nenhum registro na base de dados. Da mesma forma que se tentar alterar um registro que já existe e você não mudou nada nesse registro, pois também não ocorreu alteração dos dados do mesmo.

Vamos realizar um segundo teste da utilização da funcionalidade `edit` combinada com a funcionalidade `find`. Neste teste vamos procurar um registro na base de dados usando `find`. Após isso, alteramos alguma informação do produto e utilizamos a função `edit` para enviar essas alterações para a base de dados. O novo arquivo de teste é `testes_dao/produto_dao_edit_find.php`.

> arquivo testes_dao/produto_dao_edit_find.php
```php
<?php

include('../produto_dao.php');

echo "<p>Procurando um produto</p>";
$id = 4;
$produto = find($id);

if (!$produto){
    echo "<p>Produto não encontrado</p>";
    exit;
}
echo "<p>Produto encontrado</p>";

echo "<p>Editando o título do produto</p>";
$produto['titulo'] = 'Coleção Livros do Harry Potter Capa Dura';
$edited = edit($produto);

if (!$edited){
    echo "<p>O produto não foi editado ou não existe</p>";
    exit;
}

echo "<p>Produto editado</p>";
echo "<p>Procurando o produto editado</p>";
$produto_editado = find($id);
?>

<p>Dados do produto editado:</p>
<ul>
    <?php foreach ($produto_editado as $key => $value) { ?>
        <li><b><?= $key?></b>: <?= $value ?></li>
    <?php } ?>
</ul>
```


O resultado esperado será o seguinte:

<div style="border: 1px dashed black; padding: 5px;">
<p>Procurando um produto</p>
<p>Produto encontrado</p>
<p>Editando o título do produto</p>
<p>Produto editado</p>
<p>Procurando o produto editado</p>
<p>Dados do produto:</p>
<ul>
    <li><b>id</b>: 4</li>
    <li><b>titulo</b>: Coleção Livros do Harry Potter Capa Dura</li>
    <li><b>descricao</b>: É uma coleção muito legal</li>
    <li><b>preco</b>: 500</li>
</ul>
</div>

Teste com outros id que existam ou não para verificar que funcionalidade esteja funcionando corretamente.

### Função delete

A última funcionalidade básica é o `delete`. Esta funcionalidade apaga um registro na base de dados utilizando apenas o id do produto, já que esta é a única informação necessária para pagar um registro na base de dados. Como é uma funcionalidade que se comunica com a base de dados também é implementada no arquivo `produto_dao.php`, da seguinte forma:

> continuando o arquivo produto_dao.php
```php 
<?php

/**
 * A função delete($id) recebe o $id de um produto e apaga o registro da base de dados.
 * 
 * @param int id do produto
 * 
 * @return bool retorna true se o registro foi apagado, caso contrário retorna false.
 */
function delete(int $id) : bool {        
    $conn = get_connection();
    $sql = 'DELETE FROM produto WHERE id = ?';    
    $stmt = $conn->prepare($sql);    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $success = $stmt->affected_rows > 0;
    $stmt->close();
    $conn->close();
    return $success;
}
?>
```

Criamos o arquivo de teste 

`testes_dao/produto_dao_delete.php` tem o seguinte conteúdo:

> arquivo testes_dao/produto_dao_delete.php
```php
<?php

include('../produto_dao.php');

echo "<p>Procurando um produto</p>";
$id = 4;
$produto = find($id);
if (!$produto){
    echo "<p>Produto não encontrado</p>";
    exit;
}

echo "<p>Deletando um produto</p>";
$deleted = delete($id);

if (!$deleted){
    echo "<p>O produto não pode ser deletado ou não existe</p>";
    exit;
}

echo "<p>Produto deletado com sucesso</p>";

$found = find($id);

if (!$found){
    echo "<p>O produto já não foi encontrado na base de dados</p>";
    exit;
}
?>
```

E o resultado de apagar o produto com id existente na base de dados será o seguinte.

<div style="border: 1px dashed black; padding: 5px;">
<p>Procurando um produto</p>
<p>Deletando um produto</p>
<p>Produto deletado com sucesso</p>
<p>O produto já não foi encontrado na base de dados</p>
</div>

Caso você tente apagar um produto usando um id que não está na base de dados o resultado será o seguinte. Neste caso também estão arquele produtos que acabamos de apagar da base de dados.

<div style="border: 1px dashed black; padding: 5px;">
<p>Produto não encontrado</p>
</div>

### Variações da função `all()`

**Atividade 1**: crie uma função chamada `all_order_by_preco_asc()` que retorne os produtos ordenados em ordem crescente de preço.

**Atividade 2**: crie uma função chamada `all_order_by_preco($order)` que recebe a ordem de ordenação ('asc' ou 'desc') e retorna os produtos ordenados por preço de acordo com a escolha.

**Atividade 3**: crie uma função chamada `all_order_by($column, $order)` que recebe a coluna que quer ordenar ('titulo', 'descricao', 'preco') e a ordem de ordenação ('asc' ou 'desc') e retorna os produtos ordenados de acordo com a escolha.

## Desenvolvendo Views e Operações

Nesta segunda parte do projeto vamos produzir as páginas com HTML e CSS para interagir com o usuário e relacionar essas páginas às funcionalidades implementadas no DAO.

Essas páginas serão criadas dentro de uma pasta chamada `views` e consistem nos seguintes arquivos.

| Arquivo | Descrição | 
|---|---|
| /views/produto_create.php | Exibe o formulário para criar/cadastrar um novo produto |
| /views/produto_index.php | Exibe a lista dos produtos presentes na base de dados |
| /views/produto_show.php | Exibe as informações de um produto |
| /views/produto_edit.php | Exibe o formulário para editar/alterar dados de um produto existente |

### View create

Vamos iniciar pela criação da view create. Essa página consiste em um formulário com os dados que devem ser preenchidos para registrar um novo produto na base de dados. A implementação dessa página vai envolver conceitos de HTML, CSS, Bootstrap e PHP.

> arquivo view/produto_create.php
```html
<html>

<head>
    <title>Cadastrar Produto</title>
</head>

<?php include 'import_css.php' ?>

<body>

    <?php include 'header.php' ?>

    <div class="container col-lg-4 col-md-6 col-sm-8 col-10 mt-4 border p-2 g-2">
        <h2 class="text-center">Cadastrar Produto</h2>       
        <form action="produto.php?action=store" method="post" class="row p-2 g-2">
            
            <div>
                <label for="titulo">Título</label>
                <input type="text" class="form-control" name="titulo" id="titulo" placeholder="Escreva um título">
            </div>

            <div>
                <label for="descricao">Descrição</label>
                <textarea class="form-control" name="descricao" id="descricao" rows=5></textarea>
            </div>

            <div>
                <label for="preco">Preço</label>
                <div class="input-group mb-2">
                    <div class="input-group-prepend input-group-text">R$</div>
                    <input type="text" class="form-control col-3" name="preco" id="preco" min="0" step="0.05">
                </div>
            </div>
            <button type="submit" class="btn btn-outline-primary">Cadastrar</button>
        </form>
    </div> 

</body>

<?php include 'import_js.php' ?>

</html>
```

Nesse código observamos o `include` de três arquivos: `import_css.php`, `header.php` e `import_js.php`. O que fizemos foi centralizar a importação de estilos, cabeçalho e a importação de javascripts. Vamos implementar cada um deles para deixar o código funcionando.

Para fazer as páginas de `import_css.php` e `import_js.php` vamos acessar o site: https://getbootstrap.com/. Nesse site observamos um quadro com título `Bootstrap CDN`, dentro dele temos um subquadro chamado `CSS Only`. A linha de código desse subquadro deve ser copiado para o arquivo `import_css.php`.

```html
<!-- arquivo views/import_css.php -->
<!-- CSS only -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
```

> Nota: se tivermos outras bibliocas de estilos ou arquivos de estilo próprios podemos adicionar nesse arquivo `import_css.php` para incluir ele em todas páginas que vamos criar.

Ainda dentro do quadro `Bootstrap CDN` temos um subquadro titulado de `JavaScript Bundle with Popper`. A linha de código presente nesse subquadro vamos copiá-la para dentro do arquivo `import_js.php`. Além desse, vamos adicionar uma versão de [JQuery](https://developers.google.com/speed/libraries) para usar. 

```html
<!-- arquivo views/import_js.php -->
<script src="https://ajax.googleapis.com/ajax/libs/cesiumjs/1.78/Build/Cesium/Cesium.js"></script>
<!-- JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-u1OknCvxWvY5kfmNBILK2hRnQC3Pr17a+RTT6rIHI7NnikvbZlHgTPOOmMi466C8" crossorigin="anonymous"></script>
```

Por fim, vamos criar o nosso arquivo `header.php` que será o cabeçalho de todas as nossas páginas de interação com o usuário. Ele terá o seguinte conteúdo:

```html
<!-- arquivo views/header.php -->
<header>
    <nav class="navbar navbar-expand-lg bg-light">
        <div class="container-fluid">
            <a href="#" class="navbar-brand">DES Loja</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">                
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="produto.php?action=index">Produtos</a>
                    </li>                       
                </ul>                
            </div>
        </div>
    </nav>
</header>
```

O reusltado final da implementação dos três arquivos, ao acessarmos o arquivo `views/produto_create.php`, será o seguinte.

<div style="border: 1px dashed black; padding: 5px;">

<nav class="navbar navbar-expand-lg bg-light">
    <div class="container-fluid">
        <a href="#" class="navbar-brand">DES Loja</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">                
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="produto.php?action=index">Produtos</a>
                </li>                       
            </ul>                
        </div>
    </div>
</nav>


<div class="container col-lg-4 col-md-6 col-sm-8 col-10 mt-4 border p-2 g-2">
<h2 class="text-center">Cadastrar Produto</h2>       
<form action="produto.php?action=store" method="post" class="row p-2 g-2">

<div>
    <label for="titulo">Título</label>
    <input type="text" class="form-control" name="titulo" id="titulo" placeholder="Escreva um título">
</div>

<div>
    <label for="descricao">Descrição</label>
    <textarea class="form-control" name="descricao" id="descricao" rows=5></textarea>
</div>

<div>
    <label for="preco">Preço</label>
    <div class="input-group mb-2">
        <div class="input-group-prepend input-group-text">R$</div>
        <input type="text" class="form-control col-3" name="preco" id="preco" min="0" step="0.05">
    </div>
</div>
<button type="submit" class="btn btn-outline-primary">Cadastrar</button>
</form>
</div> 

</div>

Como mencionamos no inicio deste projeto para acessar a view que exibe o formulário precisamos consultar a URL `/produto.php?action=create` via GET e para enviar os dados e armazená-los na base de dados usamos a URL `/produto.php?action=store` via POST. Abaixo seguem as duas requisições para relembrar:

| Requisição | URL | Operação | Usa função DAO  | Descrição  |
|---|---|---|---|---|
| GET | /produto.php?action=create | create | - | Exibe o formulário de criar produto |
| POST | /produto.php?action=store | store  | create | Recebe os dados do formulário e armazena na base de dados |
|

Vamos implementar essas duas funcionalidades em um arquivo na pasta raiz do projeto, chamado `produto.php`. Já vamos deixar o código pronto para implementar as outras funcionalidades também.

Quando a ação solicitada for `action=create` via GET será executada a inclusão do arquivo `views/produto_create.php` para mostrar para o usuário o formulário com a página. Quando a ação solicitada for `action=store` via POST será executado a função `create($produto)` definida no arquivo `produto_dao.php`, quem vai de fato cadastrar o produto na base de dados. Após isso, é gerada uma requisição GET para a ação `action=index`, usando a função `header("Location: produto.php?action=index")`, que vai mostar a lista de produtos (esta funcionalidade não esta implementada ainda, mas será implementada logo a seguir).

```php
<?php // arquivo produto.php

include_once("produto_dao.php");

$action = $_GET['action'];    

if ( $_SERVER['REQUEST_METHOD'] == 'GET'){

    $dados = $_GET;

    switch ($action) {

        /**
         * index: Exibe todas os produtos na base de dados
         */
        case "index":
            break;

        /**
         * create: Exibe o formulário de criar produto
         */
        case "create":
            include("views/produto_create.php");
            break;

        /**
         * show: Procura o produto {id} na base de dados e exibe os dados desse produto
         */
        case "show":        
            break;

        /**
         * edit: Procura os dados do produto {id} na base de dados e exibe o 
         *       formulário de editar dados do produto {id}
         */
        case "edit":        
            break;

        /**
         * destroy: Recebe o {id} do produto e apaga o produto do banco
         */ 
        case "destroy":        
            break;
    }

}

if ( $_SERVER['REQUEST_METHOD'] == 'POST'){
    
    $dados = $_POST;
    
    switch ($action) {

        /**
         * store: Recebe os dados do formulário e armazena na base de dados
         */        
        case "store":            
            create($dados);
            header("Location: produto.php?action=index");
            break;

        /**
         * update: Recebe os dados do formulário e salva as alterações no banco
         */                    
        case "update":                    
            break;
       
    }

}

?>

```

Desta forma ao acessarmos a URL `produto.php?action=create` declaramos nossa intenção da criar um novo produto e assim o arquivo `produto.php` popula a view apropriada. O resultado de acessar essa URL do nosso projeto será justamente a apresentação da view para criar um novo produto.

### View index

Nesta view o objetivo é mostrar para o usuário uma lista de todos os produtos cadastrados. Para isso vamos criar o arquivo `views/produto_index.php`. Nesse arquivo vamos aproveitar os arquivos de `import_css.php`, `header.php` e `import_js.php` que criamos para a view *create*. Vamos assumir também que uma variável `$produtos` será acessível por meio desta view. Essa variável vai contar uma lista de produtos na forma de um array simples, onde cada elemento do array, um produto, será representado como um array associativo (formato chave => valor).

O arquivo `views/produto_index.php` terá o seguinte conteúdo:

```html

<html>

<head>
    <title>Lista de Produtos</title>
</head>

<?php include 'import_css.php' ?>

<body>

    <?php include 'header.php' ?>
    
    <div class="container col-md-8 col-12 mt-2">     
        
        <div class="py-2">
            <a class="btn btn-success" href="produto.php?action=create">Criar Produto</a>
        </div>

        <h2>Lista de Produtos</h2>
        
        <?php if ( isset($produtos) && $produtos ) { ?> 
        
        <table class="table"> 
            <thead>
                <tr>                    
                    <th>Id</th>
                    <th width="50%">Título</th>                    
                    <th>Preço</th>                    
                    <th></th>                    
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>             
                <?php foreach ($produtos as $produto) { ?>
                    <tr>                    
                        <td><?= $produto['id'] ?></td>
                        <td><?= $produto['titulo'] ?></td>
                        <td>R$ <?= number_format( $produto['preco'], 2, ',', '.') ?></td>

                        <td><a class="btn btn-link" href="produto.php?action=show&id=<?= $produto['id']?>">Show</a></td>
                        <td><a class="btn btn-link" href="produto.php?action=edit&id=<?= $produto['id']?>">Edit</a></td>
                        <td><a class="btn btn-link" href="produto.php?action=destroy&id=<?= $produto['id']?>">Delete</a></td>
                    </tr>                           
                <?php } ?>
            </tbody>
        </table>
        
        <?php } else { ?> 
        
        <div class="alert alert-info" role="alert">
            Nenhum produto cadastrado.
        </div>
        
        <?php } ?> 
    </div>

</body>

<?php include 'import_js.php' ?>

</html>
```

Para que essa página seja acessível por meio de novo arquivo de operações usando a URL `produtos.php?action=index` precisamos adicionar o código abaixo à ação "action=index". Esse código executa a funcionalidade `all()` implementada em `produto_dao.php` que retorna todos os produtos na tabela `produtos` da base de dados, na variável `$produto`. Uma vez que essa variável contém a lista de produtos populamos a view `produto_index.php`, quem vai mostrar na tabela os produtos dessa lista.

```php
<?php
// ... parte do arquivo produto.php 

        /**
         * index: Exibe todas os produtos na base de dados
         */
        case "index":
            $produtos = all();  
            include("views/produto_index.php");
            break;
// ...
?>
```

Assim, ao acessarmos a URL `produto.php?action=index` será apresentado um resultado similar ao seguinte:

<div style="border: 1px dashed black; padding: 5px;">

<nav class="navbar navbar-expand navbar-light bg-light">
  <a class="navbar-brand" href="#">DES Loja</a>  
  <div class="navbar-nav">      
      <a class="nav-item nav-link" href="produtos.php?action=index"">Produtos</a>      
  </div>
</nav>

</header>    
    <div class="container col-md-8 col-12 mt-2">        
        
<div class="py-2">
<a class="btn btn-success" href="produto.php?action=create">Criar Produto</a>
</div>

<h2>Lista de Produtos</h2>

<table class="table"> 
<thead>
    <tr>                    
        <th>Id</th>
        <th width="50%">Título</th>        
        <th>Preço</th>                    
        <th></th>                    
        <th></th>
        <th></th>
    </tr>
</thead>
<tbody>             
    <tr>                    
        <td>3</td>                        
        <td>Kit 4 pares de meias</td>                        
        <td>R$ 20,00</td>                        
        <td><a class="btn btn-link" href="produto.php?action=show&id=3">Show</a></td>
        <td><a class="btn btn-link" href="produto.php?action=edit&id=3">Edit</a></td>
        <td><a class="btn btn-link" href="produto.php?action=destroy&id=3">Delete</a></td>
    </tr>                           
    <tr>                    
        <td>4</td>                        
        <td>Coleção Livros do Harry Potter</td>                        
        <td>R$ 500,00</td>
        <td><a class="btn btn-link" href="produto.php?action=show&id=4">Show</a></td>
        <td><a class="btn btn-link" href="produto.php?action=edit&id=4">Edit</a></td>
        <td><a class="btn btn-link" href="produto.php?action=destroy&id=4">Delete</a></td>
    </tr>                           
    <tr>                    
        <td>5</td>                        
        <td>Boné</td>                                
        <td>R$ 15,00</td>                        
        <td><a class="btn btn-link" href="produto.php?action=show&id=5">Show</a></td>
        <td><a class="btn btn-link" href="produto.php?action=edit&id=5">Edit</a></td>
        <td><a class="btn btn-link" href="produto.php?action=destroy&id=5">Delete</a></td>
    </tr>                           
</tbody>
</table>

</div>

</div>

Podemos observar nessa lista de produtos, as funcionalidades de `Show`, `Edit` e `Delete`. Ao passar o mouse por cima dos links e verificar os seus endereços são de operações relacionadas a produtos: `action=show`, `action=edit` e `action=destroy`. Além da informação `action` enviada via GET, também foi enviada a informação do `id` do produto que queremos, visualizar, editar ou apagar. Assim a URL `produto.php?action=edit&id=3` é o endereço que inidica a intenção de alterar os dados do produto de id igual 3. Nas próximas duas seções vamos implementar as views para as operações de `Show` e `Edit`.

**Desafio 1:** Implementar view `views/produto_show.php` e relacionar ela com as operações no arquivo `produto.php`.

**Desafio 2:** Implementar view `views/produto_edit.php` e relacionar ela com as operações no arquivo `produto.php`.

### View show

Nesta view o objetivo é mostrar para o usuário as informações de um produto. Para isso primeiro vamos criar um arquivo chamado `views/produto_show.php` e depois vamos implementar a ação no arquivo `produto.php` para que seja possível acessar os dados de um produto usando a URL `produto.php?action=show&id=1` (para exibir o produto id=1).

Para implementar o arquivo `views/produto_show.php` vamos assumir que uma variável `$produto` será acessível por meio desta view e que contém os dados do produto na forma de um array associativo (formato chave => valor).

```html
<!-- arquivo views/produto_show.php -->
<html>

<head>
    <title>Mostrar Produto</title>
</head>

<?php include 'import_css.php' ?>

<body>

    <?php include 'header.php' ?>

    <div class="container col-lg-4 col-md-6 col-sm-8 col-10 mt-2">    

        <?php if ( isset($produto) && $produto ) { ?> 

        <h2>Mostrar dados do Produto</h2>
        
        <div class="row my-2">
            <div class="col-4 font-weight-bold">Título</div>
            <div class="col-8"><?= $produto['titulo'] ?></div>            
        </div>          

        <div class="row my-2">
            <div class="col-4 font-weight-bold">Descricão</div>
            <div class="col-8"><?= $produto['descricao'] ?></div>            
        </div>            

        <div class="row my-2">
            <div class="col-4 font-weight-bold">Preco</div>
            <div class="col-8"><?= $produto['preco'] ?></div>            
        </div>            
        
        <div class="btn-group col-12 mt-3">
            <a class="btn btn-success" href="produto.php?action=edit&id=<?= $produto['id']?>">Editar Dados</a>
            <a class="btn btn-danger"  href="produto.php?action=destroy&id=<?= $produto['id']?>">Apagar Produto</a>
        </div>

        <?php } else { ?>

        <div class="alert alert-info" role="alert">
            Nenhum produto para mostrar.
        </div>        

        <?php } ?>       

    </div>

</body>

<?php include 'import_js.php' ?>

</html>
```

Para que essa página seja acessada usando a URL `produto.php?action=show&id=1` precisamos adicionar o código abaixo à ação "action=show". Esse código utiliza a informação `id` enviada pela URL na requisição GET e executa a funcionalidade `find($id)`, implementada em `produto_dao.php`, que procura na base de dados o referido produto e retorna os dados do produto. Esses dados são armazenados na variável `$produto`. Uma vez que essa variável contém os dados do produto que queremos mostrar populamos a view `produto_show.php`.

```php
<?php
// ... parte do arquivo produto.php 

        /**
         * show: Procura o produto {id} na base de dados e exibe os dados desse produto
         */
        case "show":
            $produto = find($dados['id']);
            include("views/produto_show.php");
            break;
// ...
?>
```

Assim, ao acessarmos a URL `produto.php?action=show&id=1`, para um `id=1` existente na base de dados será apresentado um resultado similar ao seguinte:

<div style="border: 1px dashed black; padding: 5px;">

<body>

<header>

<nav class="navbar navbar-expand navbar-light bg-light">
  <a class="navbar-brand" href="#">DES Loja</a>  
  <div class="navbar-nav">      
      <a class="nav-item nav-link" href="produto.php?action=index"">Produtos</a>  
  </div>
</nav>

</header>

<div class="container col-lg-4 col-md-6 col-sm-8 col-10 mt-2">    

<h2>Mostrar dados do Produto</h2>

<div class="row my-2">
    <div class="col-4 font-weight-bold">Título</div>
    <div class="col-8">Kit 4 pares de meias</div>            
</div>          

<div class="row my-2">
    <div class="col-4 font-weight-bold">Descricão</div>
    <div class="col-8">Essas meias proporcionam conforto e proteção para os pés no dia a dia e durante suas atividades físicas</div>            
</div>            

<div class="row my-2">
    <div class="col-4 font-weight-bold">Preco</div>
    <div class="col-8">20</div>            
</div>            

<div class="btn-group col-12 mt-3">
    <a class="btn btn-success" href="produto.php?action=edit">Editar Dados</a>
    <a class="btn btn-danger"  href="produto.php?action=destroy">Apagar Produto</a>
</div>

</div>

</body>

</div>


### View edit

Nesta view o objetivo é mostrar para o usuário as informações de um produto em um formulário para que o mesmo possa alterar os dados do produto. Esse formulário certamente será parecido com o formulário construído para a view create.

Primeiramente vamos criar um arquivo chamado `views/produto_edit.php` e depois vamos implementar a ação no arquivo `produto.php` para que seja possível mostrar o formulário de edição usando a URL `produto.php?action=edit&id=1` (para editar o produto id=1).

Para implementar o arquivo `views/produto_edit.php` também vamos assumir que uma variável `$produto` será acessível por meio desta view e que contém os dados do produto na forma de um array associativo (formato chave => valor).

```html
<html>

<head>
    <title>Editar Produto</title>
</head>

<?php include 'import_css.php' ?>

<body>

    <?php include 'header.php' ?>

    <div class="container col-lg-4 col-md-6 col-sm-8 col-10 mt-2">        
        
        <h2>Editar Produto</h2>
        
        <form method="POST" action="produto.php?action=update">

            <input type="hidden" name="id" value="<?= $produto['id'] ?>">

            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" class="form-control" id="titulo" 
                name="titulo" placeholder="Escreva o título ou nome do produto"
                value="<?= $produto['titulo'] ?>">
            </div>            

            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" 
                rows="4"><?= $produto['descricao'] ?></textarea>                
            </div>            

            <div class="form-group">
                <label for="preco">Preço</label>
                <div class="input-group mb-2">
                <div class="input-group-prepend input-group-text">R$</div>
                <input type="number" class="form-control col-3" id="preco" name="preco" 
                min="0" step="0.01" placeholder="0,00" value="<?= $produto['preco'] ?>">
                </div>
            </div>            

            <button type="submit" id="bto-cadastrar" class="btn btn-primary">Confirmar</button>

        </form>
    </div>

</body>

<?php include 'import_js.php' ?>

</html>
```

Para que essa página seja acessada usando a URL `produto.php?action=edit&id=1` precisamos adicionar o código abaixo à ação "action=edit". Este código também utiliza a informação `id` enviada pela URL na requisição GET e executa a funcionalidade `find($id)`, que procura na base de dados o referido produto e retorna os dados do produto. Esses dados são armazenados na variável `$produto` e utilizados para popular a view `produto_edit.php`.

```php
<?php
// ... parte do arquivo produto.php 

        /**
         * edit: Procura os dados do produto {id} na base de dados e exibe o 
         *       formulário de editar dados do produto {id}
         */
        case "edit":
            $produto = find($dados['id']);
            include("views/produto_edit.php");
            break;
// ...
?>
```

Assim, ao acessarmos a URL `produto.php?action=edit&id=1`, para um `id=1` existente na base de dados será apresentado um resultado similar ao seguinte:

<div style="border: 1px dashed black; padding: 5px;">

<body>

<header>

<nav class="navbar navbar-expand navbar-light bg-light">
  <a class="navbar-brand" href="#">DES Loja</a>  
  <div class="navbar-nav">      
      <a class="nav-item nav-link" href="produto.php?action=index"">Produtos</a>      
  </div>
</nav>

</header>
    <div class="container col-lg-4 col-md-6 col-sm-8 col-10 mt-2">        
        
<h2>Editar Produto</h2>

<form method="POST" action="produto.php?action=update">

<input type="hidden" name="id" value="3">

<div class="form-group">
    <label for="titulo">Título</label>
    <input type="text" class="form-control" id="titulo" 
    name="titulo" placeholder="Escreva o título ou nome do produto"
    value="Kit 4 pares de meias">
</div>            

<div class="form-group">
    <label for="descricao">Descrição</label>
    <textarea class="form-control" id="descricao" name="descricao" 
    rows="4">Essas meias proporcionam conforto e proteção para os pés no dia a dia e durante suas atividades físicas</textarea>                
</div>            

<div class="form-group">
    <label for="preco">Preço</label>
    <div class="input-group mb-2">
    <div class="input-group-prepend input-group-text">R$</div>
    <input type="number" class="form-control col-3" id="preco" name="preco" 
    min="0" step="0.01" placeholder="0,00" value="20">
    </div>
</div>            

<button type="submit" id="bto-cadastrar" class="btn btn-primary">Salvar</button>

</form>
</div>

</body>

</div>

O botão `Salvar` deve executar a operação de atualizar os dados. Para isso ao clicar no botão será gerada uma requisição via POST para a URL `produto.php?action=update`. Portanto, devemos implementar essa ação no arquivo `produto.php`. Essa ação vai executar a operação de `edit($produto)` implementada no arquivo `produto_dao.php` e após alterar as informações vai redirecionar o usuário para a lista de produtos.

```php
<?php
// ... parte do arquivo produto.php 

        /**
         * update: Recebe os dados do formulário e salva as alterações no banco
         */                    
        case "update":                    
            edit($dados);
            header("Location: produto.php?action=index");
            break;
// ...
?>
```

Para finalizar as operações de CRUD precisamos implementar a funcionalidade de apagar um registro na base de dados. A `action=destroy` é uma ação realizada via GET com a seguinte URL `produto.php?action=destroy&id=1` (para apagar o produto de id=1). A implementação dessa funcionalidade no arquivo `produto.php` é realizada a seguir:

```php
<?php
// ... parte do arquivo produto.php 

        /**
         * destroy: Recebe o {id} do produto e apaga o produto do banco
         */                    
        case "destroy":                    
            delete($dados['id']);
            header("Location: produto.php?action=index");
            break;
// ...
?>
```

Com essa funcionalidade concluímos o nosso CRUD de Produtos. Certamente existem melhorias a serem realizadas. Uma dessas melhorias é maior interação com o usuário pedindo confirmações para editar ou apagar produtos. Isso pode ser feito utilizando Javascript.

**Desafio 1:** Implemente a mensagem de confirmação em Javascript (JQuery se preferir) para quando o usuário tiver a inteção de apagar um produto.

**Desafio 2:** Implemente as mensagem de confirmação em Javascript para quando o usuário estiver confirmando a edição de dados de um produto.
