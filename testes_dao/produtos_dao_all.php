<?php

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