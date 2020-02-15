<?php 
session_start();

require_once("vendor/autoload.php");

//Namespaces que vou usar
use \Slim\Slim;
use \Eldersam\Page;
use \Eldersam\PageAdmin;
use \Eldersam\Model\User;

$app = new Slim(); //chama nova aplicação do Slim

//configura o modo Debug para explicar cada erro 
$app->config('debug', true);

//rota principal
$app->get('/', function() {
	
	$page = new Page(); //cria uma nova página

	$page->setTpl("index"); //mostra o conteúdo de index.html

});


//rota Admin
$app->get('/admin/', function() {
	
	User::verifyLogin();

	$page = new PageAdmin(); //cria uma nova página
	
	$page->setTpl("index"); //mostra o conteúdo de index.html

});


//rota Login (carrega a tela de login)
$app->get('/admin/login/', function() {
	
	$page = new PageAdmin([
		//desabilita o header e footer padroes
		"header"=>false,
		"footer"=>false
	]);
	
	$page->setTpl("login"); //mostra o conteúdo de index.html

});


$app->post('/admin/login', function(){

	User::login($_POST["login"], $_POST["password"]); //chama o método estático da Classe User

	header("Location: /admin"); //vai para a área de admin (após logado)
	exit;

});

$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");
	exit;
});

//executa
$app->run();

 ?>