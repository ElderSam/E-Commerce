<?php 

require_once("vendor/autoload.php");

//Namespaces que vou usar
use \Slim\Slim;
use \Eldersam\Page;
use \Eldersam\PageAdmin;

$app = new Slim(); //chama nova aplicação do Slim

//configura o modo Debug para explicar cada erro 
$app->config('debug', true);

//rota principal
$app->get('/', function() {
	
	$page = new Page(); //cria uma nova página

	$page->setTpl("index"); //mostra o conteúdo de index.html

});


//rota para acessar o Admin
$app->get('/admin/', function() {
	
	//echo "ADMIN";
	$page = new PageAdmin(); //cria uma nova página

	
	$page->setTpl("index"); //mostra o conteúdo de index.html

});

//executa
$app->run();

 ?>