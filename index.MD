<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
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
    preco FLOAT NOT NULL    
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
| GET | /produto.php | index | all | Exibe todas os produtos na base de dados |
| GET | /produto.php?action=create | create | - | Exibe o formulário de criar produto |
| POST | /produto.php?action=store | store  | create | Recebe os dados do formulário e armazena na base de dados |
| GET  | /produto.php?action=show&id={id} | show | find | Procura o produto {id} na base de dados e exibe os dados desse produto 
| GET | /produto.php?action=edit&id={id} | edit | find | Procura os dados do produto {id} na base de dados e exibe o formulário de editar dados do produto {id}
| POST | /produto.php?action=update | update | edit | Recebe os dados do formulário e salva as alterações no banco
| GET | /produto.php?action=destroy&id={id} | destroy | delete | Exclui um produto
|

Note que as operações `store` e `update` são as únicas duas operações em que dados são enviados via `POST`, pois eles enviam dados preenchidos pelo usuário por meio de um formulário para que sejam salvos no banco de dados. O método `POST` encapsula os dados do formulário no corpo da requisição HTTP e, por isso, permite que possam ser enviados vários dados (inclusive imagens). Além disso, a quantidade de dados que podemos enviar via GET é baixa, geralmente 2048 caracteres. Por esse, motivo os dados preenchidos por meio de formulário sempre são enviados pelo método `POST`.

Outro ponto importante é que as operações `create` e `edit` são responsáveis por apresetar os formulários para criar um novo produto ou para editar um produto existente.

As funções de DAO (Data Access Object) são as funções que efetivamente realizam as consultas com o banco de dados. Então no contexto do DAO, as funções `create` e `edit` executam as instruções SQL de `INSERT` e `UPDATE` no banco de dados. Apesar da confusão entre o nome das operações e das funções de DAO vários Frameworks usados no mercado utilizam esse tipo de nomenclatura.

## Desenvolvendo as funções de DAO

Vamos iniciar o desenvolvimento do CRUD pelas funções de DAO. Vamos criar o arquivo que executa essas funções, chamado `produto_dao.php`. Posteriormente, vamos criar um arquivo de teste chamado `produto_dao_teste.php` para  para, posteriormente, criar um arquivo de teste para verificar que esteja funcionando corretamente.

Antes de criar esses arquivos, vamos criar um arquivo para conectar com o banco de dados, que chamaremos `db_connection.php`, dentro de uma pasta chamada `config/` com o seguinte conteúdo:

```php
<?php // arquivo config/db_conneciton.php

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

```php
<?php // arquivo config/test_connection.php

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

Com essa conexão funcionando podemos iniciar a criação das nossas funções de DAO. Para isso criamos o arquivo `produto_dao.php` no diretório raiz da nossa aplicacação. Nesse arquivo vamos implementar nossa primeira função, chamada `all()`. Esta função vai retornar os dados de todos os produtos na base de dados.

```php
<?php // arquivo produto_dao.php

include_once("config/db_connection.php"); 

/**
 * A função all() retorna todos os produtos encontrados
 * na tabela produto da base de dados.
 * 
 * @return array que contém os produtos e seus dados
 */
function all(){
    $conn = get_connection();
    $sql = 'SELECT id, titulo, descricao, preco FROM produto';
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

```php
<?php // arquivo testes_dao/produto_dao_all.php

include('../produto_dao.php');

$produtos = all();

if (!$produtos){
    echo "A consulta não retornou nenhum produto.";
    exit;    
}

?>

<p>Lista de produtos no banco de dados:</p>
<ul>
<?php foreach ( $produtos as $produto ) { ?>
    <li><?= implode($produto, ';')?></li>
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

```php
<?php  // ... continuando o arquivo produto_dao.php

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
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $conn->close();
    return $result;
}
?>
```

Após isso, vamos criar um novo programa de teste para testar essa funcionalidade. O arquivo `testes_dao/produto_dao_create.php` tem o seguinte conteúdo:

```html
// arquivo testes_dao/produto_dao_create.php`

<?php 

$novo_produto = [
    'titulo' => 'Coleção Livros do Harry Potter',
    'descricao' => 'É uma coleção muito legal',
    'preco' => '300.00'
    ];

include('../produto_dao.php');

create($novo_produto);

$produtos = all();

?>

<p>Lista de produtos no banco de dados:</p>
<ul>
<?php foreach ( $produtos as $produto ) { ?>
    <li><?= implode($produto, ';')?></li>
<?php } ?>
</ul>
```

O resultado esperado será a lista de produtos, a qual contém o novo produto adicionado, como segue.

<div style="border: 1px dashed black; padding: 5px;">
<p>Lista de produtos no banco de dados:</p>
<ul>
    <li>1;Kit 4 pares de meias;Essas meias proporcionam conforto e proteção para os pés no dia a dia e durante suas atividades físicas;20</li>
    <li>2;Camiseta de Algodão;Em tecido de algodão de alta qualidade, a camiseta básica tem ótimo conforto, é indicada para o dia a dia, ou seja, para usar em casa, trabalho ou lazer.;80</li>
    <li>3;Boné;Este boné tem muito estilo.;15</li>
    <li>4;Coleção Livros do Harry Potter;É uma coleção muito legal;300</li>
</ul>
</div>

**Desafio 1:** Implemente a função `find($id)` no arquivo `produto_dao.php` e crie um arquivo de teste `testes_dao/produto_dao_find.php` e verifique se está funcionando corretamente para recuperar algum dos produtos na tabela `produto`.

**Desafio 2:** Implemente a função `edit($produto)` no arquivo `produto_dao.php` e crie um arquivo de teste `testes_dao/produto_dao_edit.php` e verifique se está funcionando corretamente. Teste com o seguinte produto:

```php
$produto = [
    'id' => '4',
    'titulo' => 'Coleção Livros do Harry Potter',
    'descricao' => 'É uma coleção muito legal',
    'preco' => '500.00'
    ];
```

### Função find

A funcionalidade `find` recebe como parâmetro o id de um produto e tem como objetivo retornar o registro da base de dados referente a esse id. Os dados são retornados no formato de um array associativo, ou seja, no formato $chave => $valor. Caso ele não encontre nada retorna uma array vazio. No arquivo `produto_dao` vamos acrescentar a função `find`, como segue:

```php
<?php  // ... continuando o arquivo produto_dao.php

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

?>
```

Após isso, vamos criar um novo programa de teste para testar essa funcionalidade. O arquivo `testes_dao/produto_dao_find.php` tem o seguinte conteúdo:

```html
<?php // arquivo testes_dao/produto_dao_find.php

include('../produto_dao.php');

$produto = find("4");

if (!$produto){
    echo 'Produto não encontrado.';
    exit;
}
?>

<p>Dados do produto:</p>
<ul>
    <?php foreach($produto as $key => $value) { ?>
        <li><b><?= $key?></b>: <?= $value ?></li>
    <?php } ?>
</ul>
```

E o resultado será o seguinte:

<div style="border: 1px dashed black; padding: 5px;">
<p>Dados do produto:</p>
<ul>
    <li><b>id</b>: 4</li>
    <li><b>titulo</b>: Coleção Livros do Harry Potter</li>
    <li><b>descricao</b>: É uma coleção muito legal</li>
    <li><b>preco</b>: 300</li>
</ul>
</div>

Experimente usar outros valores de id, que existam ou não na base de dados, para verificar o resultado.

### Função edit

A quarta funcionalidade de DAO é `edit`. Esta função recebe como parâmetro os dados de um produto, incluíndo o seu id, e tem como objetivo atualizar os dados do produto já existente na base de dados. Assim, no arquivo `produto_dao` adicionamos mais esta funcionalidade.

```php 
<?php // ... continuando o arquivo produto_dao.php

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
?>
```

Criamos o programa de teste para essa funcionalidade. O arquivo `testes_dao/produto_dao_edit.php` tem o seguinte conteúdo:

```html
<?php // arquivo testes_dao/produto_dao_edit.php

$produto = [
    'id' => '4',
    'titulo' => 'Coleção Livros do Harry Potter',
    'descricao' => 'É uma coleção muito legal',
    'preco' => '500.00' // Valor editado, antes era '300.00'
    ];

include('../produto_dao.php');

// A função edit retorna falso caso não tenha conseguido editar
if (!edit($produto)){
    echo 'Não ocorreu alteração de dados.';
    exit;
}

// Após alteração procuramos o produto na base de dados
$produto_alterado = find('4');

?>

<p>Dados do produto alterado:</p>
<ul>
    <?php foreach($produto_alterado as $key => $value) { ?>
        <li><b><?= $key?></b>: <?= $value ?></li>
    <?php } ?>
</ul>
```

O resultado esperado será o seguinte:

<div style="border: 1px dashed black; padding: 5px;">
<p>Dados do produto:</p>
<ul>
    <li><b>id</b>: 4</li>
    <li><b>titulo</b>: Coleção Livros do Harry Potter</li>
    <li><b>descricao</b>: É uma coleção muito legal</li>
    <li><b>preco</b>: 500</li>
</ul>
</div>

Teste alterar outros valores de id. Você irá verificar que se o id não existe a função retorna falso pois não altera nenhum registro na base de dados. Da mesma forma que se tentar alterar um registro que já existe e você não mudou nada nesse registro, pois também não ocorreu alteração dos dados do mesmo.

Vamos realizar um segundo teste da utilização da funcionalidade `edit` combinada com a funcionalidade `find`. Neste teste vamos procurar um registro na base de dados usando `find`. Após isso, alteramos alguma informação do produto e utilizamos a função `edit` para enviar essas alterações para a base de dados. O novo arquivo de teste é `testes_dao/produto_dao_edit_find.php`.

```html
<?php // arquivo testes_dao/produto_dao_edit_find.php

include('../produto_dao.php');

$produto = find('4');

if (!$produto){
    echo 'Produto não encontrado.';
    exit;
}

$produto['titulo'] = 'Coleção Livros do Harry Potter Capa Dura';

if (!edit($produto)){
    echo 'Não ocorreu alteração de dados.';
    exit;
}

$produto_alterado = find('4')
?>

<p>Dados do produto alterado:</p>
<ul>
    <?php foreach($produto_alterado as $key => $value) { ?>
        <li><b><?= $key?></b>: <?= $value ?></li>
    <?php } ?>
</ul>
```

O resultado esperado será o seguinte:

<div style="border: 1px dashed black; padding: 5px;">
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

```php 
<?php // ... continuando o arquivo produto_dao.php

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
?>
```

Criamos o arquivo de teste 

`testes_dao/produto_dao_find.php` tem o seguinte conteúdo:

```html
<?php // arquivo testes_dao/produto_dao_delete.php

include('../produto_dao.php');

if (!delete("4")){
    echo 'Não foi possível apagar esse produto.';
    exit;
} else {
    echo 'Produto apagado com sucesso.';
    exit;
}

?>
```

E o resultado de apagar o produto com id existente na base de dados será o seguinte.

<div style="border: 1px dashed black; padding: 5px;">
Produto apagado com sucesso.
</div>

Caso você tente apagar um produto usando um id que não está na base de dados o resultado será o seguinte. Neste caso também estão arquele produtos que acabamos de apagar da base de dados.

<div style="border: 1px dashed black; padding: 5px;">
Não foi possível apagar esse produto.
</div>

**Desafio 1**: crie uma função chamada `all_order_by_preco_asc()` que retorne os produtos ordenados em ordem crescente de preço.

**Desafio 2**: crie uma função chamada `all_order_by_preco($order)` que recebe a ordem de ordenação ('asc' ou 'desc') e retorna os produtos ordenados por preço de acordo com a escolha.

**Desafio 3**: crie uma função chamada `all_order_by($column, $order)` que recebe a coluna que quer ordenar ('titulo', 'descricao', 'preco') e a ordem de ordenação ('asc' ou 'desc') e retorna os produtos ordenados de acordo com a escolha.

## Desenvolvendo as Operações




