<?php 

require_once("vendor/autoload.php");

$app = new \Slim\Slim(); //chama nova aplicação do Slim

//configura o modo Debug para explicar cada erro 
$app->config('debug', true);

//rota principal
$app->get('/', function() {
    
	//echo "OK";
	$sql = new Eldersam\DB\Sql();

	$results = $sql->select("SELECT * FROM tb_users");

	echo json_encode($results);

});

//executa
$app->run();

 ?>