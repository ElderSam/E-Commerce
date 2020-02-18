<?php

use \Eldersam\PageAdmin;
use \Eldersam\Model\User;

/* --------------------------------- ROTAS ADMIN ----------------------------------------------------- */
/*rota Admin ---------------------------------------------------------------- */
$app->get('/admin/', function() {
	
	User::verifyLogin();

	$page = new PageAdmin(); //cria uma nova página
	
	$page->setTpl("index"); //mostra o conteúdo de index.html

});


/* rota página de Login -------------------------------------*/
$app->get('/admin/login/', function() {
	
	$page = new PageAdmin([
		//desabilita o header e footer padroes
		"header"=>false,
		"footer"=>false
	]);
	
	$page->setTpl("login"); //mostra o conteúdo de login.html

});

/* rota Logar (envia formulário para o Banco de dados) -------------------------------------*/
$app->post('/admin/login', function(){

	User::login($_POST["login"], $_POST["password"]); //chama o método estático da Classe User

	header("Location: /admin"); //vai para a área de admin (após logado)
	exit;

});

/* rota Deslogar (sair) -------------------------------------*/
$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");
	exit;
});


/* --------------------------------- FIM ROTAS ADMIN ----------------------------------------------------- */
